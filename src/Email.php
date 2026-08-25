<?php
/**
 * Email
 *
 * @package     ArrayPress\RegisterEmails
 * @copyright   Copyright (c) 2026, ArrayPress Limited
 * @license     GPL2+
 * @since       2.0.0
 */

declare( strict_types=1 );

namespace ArrayPress\RegisterEmails;

use ArrayPress\RegisterEmails\Render\Processor;
use ArrayPress\RegisterEmails\Utils\Runtime;
use WP_Error;

/**
 * One email, built and sent.
 *
 * Two things here are not obvious and both are about not sending something
 * wrong.
 *
 * **A header value never contains a line break.** Every part that goes into a
 * header is cleaned of CR and LF as it is set. On a current WordPress this is
 * not an injection — PHPMailer refuses a custom header containing one and
 * strips them out of names and subjects — but "PHPMailer refuses it" means
 * `wp_mail()` returns false and the email silently does not go, which is a
 * worse outcome than a Reply-To name with a stray newline taken out of it.
 *
 * **An email with a tag that failed does not send.** The tag layer used to
 * catch the failure, write it to the error log and put an empty string in,
 * so a customer received an order confirmation with a blank where the total
 * should be. Sending nothing is recoverable; sending that is not.
 */
final class Email {

	/**
	 * Who it goes to.
	 *
	 * @var string[]
	 */
	private array $to = [];

	/**
	 * Its subject.
	 *
	 * @var string
	 */
	private string $subject = '';

	/**
	 * Its body, before the tags are filled in.
	 *
	 * @var string
	 */
	private string $content = '';

	/**
	 * Which visual template wraps it.
	 *
	 * @var string
	 */
	private string $template = 'default';

	/**
	 * Extra headers.
	 *
	 * @var string[]
	 */
	private array $headers = [];

	/**
	 * Files to attach.
	 *
	 * @var string[]
	 */
	private array $attachments = [];

	/**
	 * The tag groups that apply.
	 *
	 * @var string[]
	 */
	private array $groups = [];

	/**
	 * Whatever the email is about.
	 *
	 * @var mixed
	 */
	private mixed $data = null;

	/**
	 * What goes in the template's own placeholders.
	 *
	 * @var array<string, string>
	 */
	private array $context = [];

	/**
	 * The last run's tag problems.
	 *
	 * @var array<string, string>
	 */
	private array $problems = [];

	/**
	 * Start an email.
	 *
	 * @return self
	 */
	public static function make(): self {
		return new self();
	}

	/**
	 * Who it goes to.
	 *
	 * @param string|string[] $to One address or several.
	 *
	 * @return self
	 */
	public function to( string|array $to ): self {
		foreach ( (array) $to as $address ) {
			$address = sanitize_email( (string) $address );

			if ( '' !== $address ) {
				$this->to[] = $address;
			}
		}

		return $this;
	}

	/**
	 * What it says it is about.
	 *
	 * @param string $subject The subject.
	 *
	 * @return self
	 */
	public function subject( string $subject ): self {
		$this->subject = self::one_line( $subject );

		return $this;
	}

	/**
	 * Its body.
	 *
	 * @param string $content The body, with tags in it.
	 *
	 * @return self
	 */
	public function content( string $content ): self {
		$this->content = $content;

		return $this;
	}

	/**
	 * Which visual template wraps it.
	 *
	 * @param string $template The template.
	 *
	 * @return self
	 */
	public function template( string $template ): self {
		$this->template = $template;

		return $this;
	}

	/**
	 * Which tag groups apply.
	 *
	 * @param string|string[] $groups One group or several.
	 *
	 * @return self
	 */
	public function tags( string|array $groups ): self {
		$this->groups = array_values( array_unique( array_merge( $this->groups, (array) $groups ) ) );

		return $this;
	}

	/**
	 * Whatever the email is about, handed to every tag's callback.
	 *
	 * @param mixed $data The order, the customer, the subscription.
	 *
	 * @return self
	 */
	public function about( mixed $data ): self {
		$this->data = $data;

		return $this;
	}

	/**
	 * What goes in the template's own placeholders.
	 *
	 * @param array<string, string> $context title, subtitle, footer and the rest.
	 *
	 * @return self
	 */
	public function context( array $context ): self {
		$this->context = array_merge( $this->context, $context );

		return $this;
	}

	/**
	 * Who it comes from.
	 *
	 * @param string $email The address.
	 * @param string $name  The name.
	 *
	 * @return self
	 */
	public function from( string $email, string $name = '' ): self {
		return $this->address_header( 'From', $email, $name );
	}

	/**
	 * Where a reply goes.
	 *
	 * @param string $email The address.
	 * @param string $name  The name.
	 *
	 * @return self
	 */
	public function reply_to( string $email, string $name = '' ): self {
		return $this->address_header( 'Reply-To', $email, $name );
	}

	/**
	 * Who else gets it.
	 *
	 * @param string $email The address.
	 *
	 * @return self
	 */
	public function cc( string $email ): self {
		return $this->address_header( 'Cc', $email );
	}

	/**
	 * Who else gets it, quietly.
	 *
	 * @param string $email The address.
	 *
	 * @return self
	 */
	public function bcc( string $email ): self {
		return $this->address_header( 'Bcc', $email );
	}

	/**
	 * A header of your own.
	 *
	 * @param string $name  Its name.
	 * @param string $value Its value.
	 *
	 * @return self
	 */
	public function header( string $name, string $value ): self {
		$name = self::one_line( $name );

		if ( '' === $name ) {
			return $this;
		}

		$this->headers[] = sprintf( '%s: %s', $name, self::one_line( $value ) );

		return $this;
	}

	/**
	 * A file to send with it.
	 *
	 * @param string $path The file.
	 *
	 * @return self
	 */
	public function attach( string $path ): self {
		if ( is_readable( $path ) ) {
			$this->attachments[] = $path;
		}

		return $this;
	}

	/**
	 * The finished email.
	 *
	 * @return string
	 */
	public function html(): string {
		$processor = new Processor( $this->groups );

		$body = $processor->process( $this->content, $this->data );

		$this->problems = $processor->problems();

		return $this->wrap( $body, $processor );
	}

	/**
	 * The finished email, with samples where the real values would be.
	 *
	 * @return string
	 */
	public function preview(): string {
		$processor = new Processor( $this->groups );

		return $this->wrap( $processor->preview( $this->content ), $processor );
	}

	/**
	 * What went wrong filling the tags in.
	 *
	 * @return array<string, string>
	 */
	public function problems(): array {
		return $this->problems;
	}

	/**
	 * Send it.
	 *
	 * @return true|WP_Error
	 */
	public function send(): true|WP_Error {
		if ( [] === $this->to ) {
			return new WP_Error( 'no_recipient', __( 'The email has nobody to go to.', 'arraypress' ) );
		}

		$html = $this->html();

		// A tag that could not be filled in leaves a gap in a sentence, and
		// the sentence is usually the one with the amount in it.
		if ( [] !== $this->problems ) {
			return new WP_Error(
				'tags_failed',
				sprintf(
					/* translators: %s: what went wrong, one per tag. */
					__( 'The email was not sent because some of it could not be filled in: %s', 'arraypress' ),
					implode( ' ', $this->problems )
				),
				[ 'problems' => $this->problems ]
			);
		}

		$headers = array_merge( [ 'Content-Type: text/html; charset=' . get_bloginfo( 'charset' ) ], $this->headers );

		$sent = wp_mail( $this->to, $this->subject, $html, $headers, $this->attachments );

		/**
		 * Fires after an email has been through wp_mail().
		 *
		 * @param bool     $sent    Whether it went.
		 * @param string[] $to      Who to.
		 * @param string   $subject What it says it is about.
		 *
		 * @since 2.0.0
		 */
		do_action( Runtime::hook( 'sent' ), $sent, $this->to, $this->subject );

		return $sent
			? true
			: new WP_Error( 'send_failed', __( 'WordPress could not send the email.', 'arraypress' ) );
	}

	/**
	 * Put the body inside the visual template.
	 *
	 * @param string    $body      The body, with its tags filled in.
	 * @param Processor $processor The processor, for the groups it used.
	 *
	 * @return string
	 */
	private function wrap( string $body, Processor $processor ): string {
		$shell = Templates::get( $this->template );

		$replacements = array_merge(
			self::site_context(),
			[
				'title'    => $this->context['title'] ?? $this->subject,
				'subtitle' => '',
				'footer'   => '',
				'logo'     => '',
				'subject'  => $this->subject,
			],
			$this->context,
			[ 'content' => $body ]
		);

		$shell = strtr(
			$shell,
			array_combine(
				array_map( static fn( string $key ): string => '{' . $key . '}', array_keys( $replacements ) ),
				array_map( 'strval', $replacements )
			)
		);

		// Anything the template asked for and nobody filled in. A template
		// placeholder left in the markup shows up as `{colour_primary}` in
		// the middle of the email.
		return (string) preg_replace( '/\{[a-z0-9_]+\}/i', '', $shell );
	}

	/**
	 * What every email knows about the site it came from.
	 *
	 * @return array<string, string>
	 */
	private static function site_context(): array {
		return [
			'site_name'   => (string) get_bloginfo( 'name' ),
			'site_url'    => home_url(),
			'admin_email' => (string) get_option( 'admin_email' ),

			// The site's year, not the server's. A footer that says 2025 for
			// the first few hours of the new year is a small thing that looks
			// like nobody is home.
			'year'        => (string) current_time( 'Y' ),
			'date'        => (string) current_time( (string) get_option( 'date_format' ) ),
			'time'        => (string) current_time( (string) get_option( 'time_format' ) ),

			'color_primary' => '#2271b1',
		];
	}

	/**
	 * Add a header naming somebody.
	 *
	 * @param string $header Which header.
	 * @param string $email  The address.
	 * @param string $name   Their name.
	 *
	 * @return self
	 */
	private function address_header( string $header, string $email, string $name = '' ): self {
		$email = sanitize_email( $email );

		if ( '' === $email ) {
			return $this;
		}

		$name = self::one_line( $name );

		$this->headers[] = '' === $name
			? sprintf( '%s: %s', $header, $email )
			: sprintf( '%s: %s <%s>', $header, $name, $email );

		return $this;
	}

	/**
	 * A string with no line breaks in it.
	 *
	 * Everything that goes into a header goes through this. See the note at
	 * the top: the point is that the email sends, not that an injection is
	 * being stopped — PHPMailer already stops that, by refusing the whole
	 * message.
	 *
	 * @param string $value The value.
	 *
	 * @return string
	 */
	private static function one_line( string $value ): string {
		return trim( (string) preg_replace( '/[\r\n]+/', ' ', $value ) );
	}
}
