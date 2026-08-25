<?php
/**
 * Tag, component and sending tests.
 *
 * @package ArrayPress\RegisterEmails
 */

declare( strict_types=1 );

namespace ArrayPress\RegisterEmails\Tests;

use ArrayPress\RegisterEmails\Email;
use ArrayPress\RegisterEmails\Emails;
use ArrayPress\RegisterEmails\Render\Components;
use ArrayPress\RegisterEmails\Render\Processor;
use ArrayPress\RegisterEmails\Tags;
use ArrayPress\RegisterEmails\Templates;
use ArrayPress\RegisterEmails\Utils\Runtime;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use WP_Error;

/**
 * What goes out, and what does not.
 *
 * Most of these are about the second. A transactional email is the one piece
 * of a plugin that reaches a customer, cannot be taken back, and is read
 * carefully — so a gap in a sentence where the order total should be is worse
 * than an email that never arrives, and both are worse than an error somebody
 * can see.
 */
final class EmailTest extends TestCase {

	/**
	 * Reset the stubbed globals and the registries.
	 */
	/**
	 * A directory of templates a test wrote, if it wrote one.
	 *
	 * @var string
	 */
	private string $templates_dir = '';

	/**
	 * Reset the stubbed globals and the registries.
	 */
	protected function setUp(): void {
		re_reset_globals();
	}

	/**
	 * Put the shipped templates back.
	 */
	protected function tearDown(): void {
		if ( '' === $this->templates_dir ) {
			return;
		}

		Templates::set_templates_dir( dirname( __DIR__ ) . '/templates/' );

		foreach ( (array) glob( $this->templates_dir . '*.html' ) as $file ) {
			unlink( (string) $file );
		}

		$this->templates_dir = '';
	}

	/**
	 * Register the usual tags.
	 *
	 * @return void
	 */
	private function tags(): void {
		Tags::add( 'shop', 'customer_name', [
			'label'    => 'Customer name',
			'callback' => static fn( $order ): string => (string) ( $order->name ?? '' ),
			'preview'  => 'Jane Doe',
		] );

		Tags::add( 'shop', 'order_total', [
			'label'    => 'Order total',
			'callback' => static fn( $order ): string => '£' . number_format( (float) ( $order->total ?? 0 ), 2 ),
		] );
	}

	/**
	 * An order to send about.
	 *
	 * @return object
	 */
	private function order(): object {
		return (object) [ 'name' => 'Jane Doe', 'total' => 99.5 ];
	}

	/* ---------------------------------------------------------------------
	 * Tags
	 * ------------------------------------------------------------------ */

	/**
	 * A tag is filled in with what its callback returns.
	 */
	public function test_a_tag_is_filled_in(): void {
		$this->tags();

		$html = Email::make()
			->content( 'Hello {customer_name}, that comes to {order_total}.' )
			->tags( 'shop' )
			->about( $this->order() )
			->html();

		$this->assertStringContainsString( 'Hello Jane Doe, that comes to £99.50.', $html );
	}

	/**
	 * A tag nobody registered does not go out looking like a tag.
	 *
	 * `Hi {customer_name}` reaching a customer exactly like that is the most
	 * recognisable email bug there is, and it used to be the behaviour: the
	 * processor looked for tags it knew and left everything else alone.
	 */
	public function test_an_unknown_tag_is_removed_rather_than_sent(): void {
		$this->tags();

		$processor = new Processor( [ 'shop' ] );

		$html = $processor->process( 'Hello {customer_naem}, welcome.', $this->order() );

		$this->assertStringNotContainsString( '{customer_naem}', $html );
		$this->assertSame( [ 'customer_naem' ], $processor->unknown() );
	}

	/**
	 * A tag whose callback throws is reported, not swallowed.
	 *
	 * It used to be caught, written to the error log and replaced with an
	 * empty string — twice, in two places — so the email went out with a gap
	 * in it and the only record was a line in a file nobody reads.
	 */
	public function test_a_tag_that_throws_is_reported(): void {
		Tags::add( 'shop', 'order_total', [
			'callback' => static function (): string {
				throw new RuntimeException( 'the order has no currency' );
			},
		] );

		$processor = new Processor( [ 'shop' ] );

		$processor->process( 'That comes to {order_total}.', null );

		$this->assertFalse( $processor->is_complete() );
		$this->assertStringContainsString( 'the order has no currency', $processor->problems()['order_total'] );
	}

	/**
	 * And the email is not sent.
	 *
	 * Sending nothing is recoverable. Sending an order confirmation with a
	 * blank where the total should be is not.
	 */
	public function test_an_email_with_a_failed_tag_does_not_send(): void {
		Tags::add( 'shop', 'order_total', [
			'callback' => static function (): string {
				throw new RuntimeException( 'no currency' );
			},
		] );

		$sent = Email::make()
			->to( 'customer@example.test' )
			->subject( 'Your order' )
			->content( 'That comes to {order_total}.' )
			->tags( 'shop' )
			->send();

		$this->assertInstanceOf( WP_Error::class, $sent );
		$this->assertSame( [], $GLOBALS['re_mail'], 'It sent anyway.' );
	}

	/**
	 * A tag with nothing to show renders as nothing, which is not a failure.
	 *
	 * A basket with no items is not an error, and an empty table is worse
	 * than no table.
	 */
	public function test_a_tag_with_nothing_to_show_is_not_a_failure(): void {
		Tags::add( 'shop', 'items', [
			'type'     => 'order_items',
			'callback' => static fn(): bool => false,
		] );

		$processor = new Processor( [ 'shop' ] );

		$this->assertSame( 'Your items: ', $processor->process( 'Your items: {items}', null ) );
		$this->assertTrue( $processor->is_complete() );
	}

	/**
	 * A tag is only asked for if the content uses it.
	 *
	 * A site with four hundred registered tags used to run four hundred
	 * str_contains() over every email.
	 */
	public function test_only_the_tags_the_content_uses_are_asked_for(): void {
		$asked = [];

		foreach ( [ 'a', 'b', 'c' ] as $name ) {
			Tags::add( 'shop', $name, [
				'callback' => static function () use ( $name, &$asked ): string {
					$asked[] = $name;

					return $name;
				},
			] );
		}

		( new Processor( [ 'shop' ] ) )->process( 'Only {b} here.', null );

		$this->assertSame( [ 'b' ], $asked );
	}

	/**
	 * A later group's tag wins a name an earlier one used.
	 *
	 * The useful way round: a plugin's own {site_name} overrides the general
	 * one rather than being shadowed by it.
	 */
	public function test_a_later_group_wins_a_shared_name(): void {
		Tags::add( 'general', 'site_name', [ 'callback' => static fn(): string => 'The site' ] );
		Tags::add( 'shop', 'site_name', [ 'callback' => static fn(): string => 'The shop' ] );

		$this->assertSame( 'The shop', ( new Processor( [ 'general', 'shop' ] ) )->process( '{site_name}', null ) );
	}

	/* ---------------------------------------------------------------------
	 * Components
	 * ------------------------------------------------------------------ */

	/**
	 * A tag whose type is a component draws the component.
	 */
	public function test_a_component_tag_draws_the_component(): void {
		Tags::add( 'shop', 'view_order', [
			'type'     => 'button',
			'callback' => static fn(): array => [ 'text' => 'View your order', 'url' => 'https://example.test/o/1' ],
		] );

		$html = ( new Processor( [ 'shop' ] ) )->process( '{view_order}', null );

		$this->assertStringContainsString( 'View your order', $html );
		$this->assertStringContainsString( 'https://example.test/o/1', $html );
	}

	/**
	 * A callback returning a string fills the component's main argument.
	 *
	 * Which argument that is used to live in a table of twenty-one entries,
	 * kept by hand, in a different file from the components. Each component
	 * says for itself now.
	 */
	public function test_a_string_fills_the_components_main_argument(): void {
		Tags::add( 'shop', 'warning', [
			'type'     => 'alert',
			'callback' => static fn(): string => 'Your card is about to expire.',
		] );

		$this->assertStringContainsString(
			'Your card is about to expire.',
			( new Processor( [ 'shop' ] ) )->process( '{warning}', null )
		);
	}

	/**
	 * Every component says what its main argument is, and means it.
	 *
	 * The three lists that had to agree are one list now, and this is what
	 * stops it drifting back: a component added without a primary key, or
	 * with one that is not an argument it reads, fails here.
	 */
	public function test_every_component_takes_a_string_or_refuses_one(): void {
		$types = Components::types();

		$this->assertGreaterThan( 15, count( $types ) );

		$scalar = 0;
		$lists  = 0;

		foreach ( $types as $type ) {
			$key = Components::primary_key( $type );

			if ( '' === $key ) {
				++$lists;

				continue;
			}

			++$scalar;

			// The component reads the argument it named, without complaining
			// about its type — which is what "primary key" has to mean for
			// the string a callback returns to be usable.
			$rendered = Components::render( $type, [ $key => 'Something identifiable' ] );

			$this->assertIsString( $rendered, sprintf( '%s did not render.', $type ) );
		}

		$this->assertGreaterThan( 5, $scalar, 'Nothing takes a bare string any more.' );
		$this->assertGreaterThan( 5, $lists, 'Nothing is declared as taking a list.' );
	}

	/**
	 * A component whose argument is a list refuses a bare string.
	 *
	 * It used to be given one anyway — there was a table saying order_items'
	 * main argument was `items`, so a callback returning a string put a word
	 * where a list of order lines belonged, and the component iterated over
	 * it. Half a table, a PHP warning in the middle of the email, and the
	 * email still sent.
	 */
	public function test_a_list_component_refuses_a_bare_string(): void {
		Tags::add( 'shop', 'items', [
			'type'     => 'order_items',
			'callback' => static fn(): string => 'A widget, a gadget',
		] );

		$processor = new Processor( [ 'shop' ] );

		$html = $processor->process( '{items}', null );

		$this->assertFalse( $processor->is_complete() );
		$this->assertStringContainsString( 'needs an array of values', $processor->problems()['items'] );
		$this->assertSame( '', $html );
	}

	/**
	 * A component's type is derived from its class name.
	 */
	public function test_a_components_type_comes_from_its_class(): void {
		$this->assertTrue( Components::has( 'code_block' ) );
		$this->assertTrue( Components::has( 'order_items' ) );
		$this->assertTrue( Components::has( 'alert' ) );
		$this->assertFalse( Components::has( 'CodeBlock' ) );
	}

	/**
	 * A plugin can add one of its own.
	 */
	public function test_a_plugin_can_add_a_component(): void {
		add_filter(
			Runtime::hook( 'components' ),
			static function ( array $map ): array {
				$map['gift_note'] = GiftNote::class;

				return $map;
			}
		);

		Components::flush();

		$this->assertTrue( Components::has( 'gift_note' ) );
		$this->assertSame( 'note', Components::primary_key( 'gift_note' ) );
		$this->assertSame( '<p>Happy birthday</p>', Components::render( 'gift_note', [ 'note' => 'Happy birthday' ] ) );
	}

	/**
	 * And a class that is not a component is refused rather than fataling.
	 */
	public function test_something_that_is_not_a_component_is_refused(): void {
		add_filter(
			Runtime::hook( 'components' ),
			static function ( array $map ): array {
				$map['nonsense'] = 'DateTimeImmutable';

				return $map;
			}
		);

		Components::flush();

		$this->assertFalse( Components::has( 'nonsense' ) );
	}

	/* ---------------------------------------------------------------------
	 * Headers
	 * ------------------------------------------------------------------ */

	/**
	 * A header value never carries a line break.
	 *
	 * Not because it would inject one — PHPMailer refuses a custom header
	 * containing CR or LF and strips them out of names and subjects, so an
	 * injection does not land. It refuses by throwing, which wp_mail()
	 * swallows and reports as a plain false: the email silently does not go.
	 *
	 * Cleaning the value means it sends, with a name that has had a stray
	 * newline turned into a space.
	 */
	public function test_a_header_value_never_carries_a_line_break(): void {
		Email::make()
			->to( 'customer@example.test' )
			->subject( "Your order\r\nBcc: attacker@evil.test" )
			->reply_to( 'jane@example.test', "Jane Doe\r\nBcc: attacker@evil.test" )
			->header( 'X-Order-Ref', "123\r\nBcc: attacker@evil.test" )
			->content( 'Thanks.' )
			->send();

		$mail = $GLOBALS['re_mail'][0];

		foreach ( array_merge( [ $mail['subject'] ], $mail['headers'] ) as $line ) {
			$this->assertDoesNotMatchRegularExpression(
				'/[\r\n]/',
				(string) $line,
				sprintf( 'A line break survived into %s.', $line )
			);
		}

		$this->assertStringContainsString( 'Reply-To: Jane Doe Bcc: attacker@evil.test <jane@example.test>', implode( "\x00", $mail['headers'] ) );
	}

	/**
	 * An address that is not one is left out rather than mangled in.
	 */
	public function test_an_address_that_is_not_one_is_left_out(): void {
		Email::make()
			->to( 'customer@example.test' )
			->cc( 'not an address' )
			->content( 'Thanks.' )
			->send();

		$this->assertSame(
			[ 'Content-Type: text/html; charset=UTF-8' ],
			$GLOBALS['re_mail'][0]['headers']
		);
	}

	/**
	 * An email with nobody to go to is refused.
	 */
	public function test_an_email_with_no_recipient_is_refused(): void {
		$this->assertInstanceOf( WP_Error::class, Email::make()->content( 'Hello' )->send() );
	}

	/* ---------------------------------------------------------------------
	 * Templates and registration
	 * ------------------------------------------------------------------ */

	/**
	 * The body goes inside the visual template.
	 */
	public function test_the_body_goes_inside_the_template(): void {
		$html = Email::make()->subject( 'Your order' )->content( '<p>Thanks.</p>' )->html();

		$this->assertStringContainsString( '<p>Thanks.</p>', $html );
		$this->assertStringContainsString( '<html', strtolower( $html ) );
	}

	/**
	 * A template placeholder nobody filled in does not reach the reader.
	 *
	 * `{color_primary}` in the middle of an email is the sort of thing that
	 * gets screenshotted. The shipped templates use only placeholders this
	 * library fills, so the case that matters is a template a theme
	 * overrode — which is what this uses.
	 */
	public function test_an_unfilled_template_placeholder_is_removed(): void {
		$this->with_template( '<div>{content}{something_nobody_fills}</div>' );

		$html = Email::make()->subject( 'Your order' )->content( '<p>Thanks.</p>' )->html();

		$this->assertStringContainsString( '<p>Thanks.</p>', $html );
		$this->assertStringNotContainsString( '{something_nobody_fills}', $html );
	}

	/**
	 * The template knows about the site, in the site's own timezone.
	 *
	 * A copyright line that says last year for the first few hours of the new
	 * one is a small thing that looks like nobody is home.
	 */
	public function test_the_template_knows_about_the_site(): void {
		$this->with_template( '<div>{site_name} — {year} — {content}</div>' );

		$html = Email::make()->content( 'Thanks.' )->html();

		$this->assertStringContainsString( 'Example Shop', $html );
		$this->assertStringContainsString( gmdate( 'Y' ), $html );

		// And it asked the site for the year rather than the server. On a
		// site running in UTC the two agree, so the value cannot tell them
		// apart — which is exactly the case where the bug hides.
		$this->assertContains( 'Y', $GLOBALS['re_clock'] );
	}

	/**
	 * And so does the footer, which fills its own.
	 */
	public function test_the_footer_knows_about_the_site(): void {
		$footer = \ArrayPress\RegisterEmails\Parts\Footer::render(
			[ 'text' => '&copy; {year} {site_name}' ]
		);

		$this->assertStringContainsString( gmdate( 'Y' ), $footer );
		$this->assertStringContainsString( 'Example Shop', $footer );
		$this->assertContains( 'Y', $GLOBALS['re_clock'] );
	}

	/**
	 * Use a template of this test's own, as a theme override would.
	 *
	 * @param string $html The template.
	 *
	 * @return void
	 */
	private function with_template( string $html ): void {
		$directory = sys_get_temp_dir() . '/re-templates/';

		if ( ! is_dir( $directory ) ) {
			mkdir( $directory, 0o777, true );
		}

		file_put_contents( $directory . 'default.html', $html );

		\ArrayPress\RegisterEmails\Templates::set_templates_dir( $directory );

		$this->templates_dir = $directory;
	}

	/**
	 * A registered email is sent by name.
	 */
	public function test_a_registered_email_is_sent_by_name(): void {
		$this->tags();

		Emails::add( 'shop', 'order_confirmation', [
			'subject' => 'Order confirmed',
			'content' => 'Thanks {customer_name}.',
		] );

		$sent = Emails::send( 'shop', 'order_confirmation', [
			'to'   => 'customer@example.test',
			'data' => $this->order(),
		] );

		$this->assertTrue( $sent );
		$this->assertStringContainsString( 'Thanks Jane Doe.', $GLOBALS['re_mail'][0]['message'] );
	}

	/**
	 * What the site owner configured beats what the plugin author wrote.
	 */
	public function test_the_site_owners_wording_wins(): void {
		$this->tags();

		Emails::add( 'shop', 'order_confirmation', [
			'subject'  => 'Order confirmed',
			'content'  => 'Thanks {customer_name}.',
			'settings' => static fn(): array => [
				'subject' => 'Your order is on its way',
				'content' => 'We are packing it now, {customer_name}.',
			],
		] );

		Emails::send( 'shop', 'order_confirmation', [ 'to' => 'customer@example.test', 'data' => $this->order() ] );

		$this->assertSame( 'Your order is on its way', $GLOBALS['re_mail'][0]['subject'] );
		$this->assertStringContainsString( 'We are packing it now, Jane Doe.', $GLOBALS['re_mail'][0]['message'] );
	}

	/**
	 * An email the site owner turned off is not sent, and is not an error.
	 *
	 * It is a setting. Reporting it as a failure fills a log with somebody's
	 * preference.
	 */
	public function test_an_email_turned_off_is_not_sent_and_not_an_error(): void {
		Emails::add( 'shop', 'order_confirmation', [
			'content'  => 'Thanks.',
			'settings' => static fn(): array => [ 'enabled' => false ],
		] );

		$this->assertTrue( Emails::send( 'shop', 'order_confirmation', [ 'to' => 'customer@example.test' ] ) );
		$this->assertSame( [], $GLOBALS['re_mail'] );
	}

	/**
	 * An email nobody registered is an error that says so.
	 */
	public function test_an_unregistered_email_says_so(): void {
		$sent = Emails::send( 'shop', 'nothing_like_this', [ 'to' => 'customer@example.test' ] );

		$this->assertInstanceOf( WP_Error::class, $sent );
		$this->assertStringContainsString( 'nothing_like_this', $sent->get_error_message() );
	}

	/* ---------------------------------------------------------------------
	 * The seam with the field kit
	 * ------------------------------------------------------------------ */

	/**
	 * The tags come out in the shape the kit's editor takes.
	 *
	 * This library owns the tags and the kit draws the chooser, so this is
	 * the whole seam between them — one method rather than two shapes each
	 * side hopes the other agrees about.
	 */
	public function test_the_tags_come_out_in_the_editors_shape(): void {
		Tags::add( 'shop', 'customer_name', [
			'label'       => 'Customer name',
			'description' => 'Who the order belongs to',
		] );

		$this->assertSame(
			[
				[
					'name'        => 'Customer name',
					'tag'         => '{customer_name}',
					'description' => 'Who the order belongs to',
				],
			],
			Tags::for_editor( [ 'shop' ] )
		);
	}

	/**
	 * A preview uses the samples rather than asking for real data.
	 */
	public function test_a_preview_uses_the_samples(): void {
		$this->tags();

		$html = Email::make()->content( 'Hello {customer_name}.' )->tags( 'shop' )->preview();

		$this->assertStringContainsString( 'Hello Jane Doe.', $html );
	}

	/**
	 * A tag with no sample shows as itself rather than as a gap.
	 *
	 * A blank in a preview reads as a tag that is broken; the tag's own name
	 * reads as one nobody wrote a sample for, which is what it is.
	 */
	public function test_a_tag_with_no_sample_shows_as_itself(): void {
		Tags::add( 'shop', 'order_id', [ 'callback' => static fn(): string => '1234' ] );

		$this->assertStringContainsString(
			'{order_id}',
			( new Processor( [ 'shop' ] ) )->preview( 'Order {order_id}' )
		);
	}

	/**
	 * The filters carry this build's own prefix.
	 *
	 * They were named `email_template_tag_{name}` for everybody, so two
	 * plugins each bundling a prefixed copy would filter each other's emails
	 * — one plugin's callback rewriting the other's order confirmation.
	 */
	public function test_the_filters_are_named_per_build(): void {
		$this->tags();

		add_filter( Runtime::hook( 'tag_customer_name' ), static fn(): string => 'Somebody else' );

		$this->assertStringContainsString(
			'Somebody else',
			( new Processor( [ 'shop' ] ) )->process( '{customer_name}', $this->order() )
		);

		// And that the name is derived rather than being that string.
		// Unprefixed the two are the same, so asserting the value proves
		// nothing — this loads the same class under the namespace Strauss
		// would give it and checks the answer moved.
		$prefixed = $this->as_prefixed_build();

		$this->assertSame( 'emails_tag_customer_name', Runtime::hook( 'tag_customer_name' ) );
		$this->assertSame( 'myplugin_emails_tag_customer_name', $prefixed::hook( 'tag_customer_name' ) );
	}

	/**
	 * Load Runtime again under the namespace a prefixed build would give it.
	 *
	 * Strauss rewrites the namespace and nothing else, so this is exactly
	 * what a second plugin bundling the library would be running.
	 *
	 * @return string The class name.
	 */
	private function as_prefixed_build(): string {
		$prefixed = 'MyPlugin\\ArrayPress\\RegisterEmails\\Utils\\Runtime';

		if ( ! class_exists( $prefixed ) ) {
			$source = (string) file_get_contents( dirname( __DIR__ ) . '/src/Utils/Runtime.php' );

			$source = str_replace(
				'namespace ArrayPress\\RegisterEmails\\Utils;',
				'namespace MyPlugin\\ArrayPress\\RegisterEmails\\Utils;',
				$source
			);

			// phpcs:ignore Squiz.PHP.Eval.Discouraged -- loading one class a second time under a different namespace is the thing being tested; there is no other way to have two of it.
			eval( '?>' . $source );
		}

		return $prefixed;
	}
}

/**
 * A component a plugin might add.
 */
final class GiftNote extends \ArrayPress\RegisterEmails\Abstracts\Component {

	/**
	 * Draw it.
	 *
	 * @param array<string, mixed> $args What to draw.
	 *
	 * @return string
	 */
	public static function render( array $args = [] ): string {
		return sprintf( '<p>%s</p>', esc_html( (string) ( $args['note'] ?? '' ) ) );
	}

	/**
	 * What a string callback is giving.
	 *
	 * @return string
	 */
	public static function primary_key(): string {
		return 'note';
	}
}
