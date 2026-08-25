<?php
/**
 * Processor
 *
 * @package     ArrayPress\RegisterEmails
 * @copyright   Copyright (c) 2026, ArrayPress Limited
 * @license     GPL2+
 * @since       2.0.0
 */

declare( strict_types=1 );

namespace ArrayPress\RegisterEmails\Render;

use ArrayPress\RegisterEmails\Tag;
use ArrayPress\RegisterEmails\Tags;
use ArrayPress\RegisterEmails\Utils\Runtime;
use WP_Error;

/**
 * Filling an email's tags in.
 *
 * Two rules, and the second one is the reason this class was rewritten.
 *
 * **A tag that fails is not a blank.** It used to be caught, logged and
 * replaced with an empty string, in two places, so an email went out with
 * nothing where the order total should have been and the only record was a
 * line in a file nobody reads. Failures are collected and handed back, and
 * the caller decides — Email refuses to send.
 *
 * **A tag nobody registered does not go out as itself.** `{customer_name}`
 * left in the body reached the customer looking exactly like that. A tag the
 * registry does not know is removed, and named in the same report.
 */
final class Processor {

	/**
	 * The groups whose tags apply.
	 *
	 * @var string[]
	 */
	private array $groups;

	/**
	 * What went wrong, by tag.
	 *
	 * @var array<string, string>
	 */
	private array $problems = [];

	/**
	 * Tags that were used and are not registered.
	 *
	 * @var string[]
	 */
	private array $unknown = [];

	/**
	 * Construct.
	 *
	 * @param string[] $groups The groups whose tags apply.
	 */
	public function __construct( array $groups ) {
		$this->groups = array_values( array_filter( array_map( 'strval', $groups ) ) );
	}

	/**
	 * Fill in what the content asks for.
	 *
	 * @param string $content The email, with tags in it.
	 * @param mixed  $data    Whatever the email is about.
	 *
	 * @return string
	 */
	public function process( string $content, mixed $data ): string {
		$this->problems = [];
		$this->unknown  = [];

		$tags = Tags::groups( $this->groups );

		/**
		 * Change an email before its tags are filled in.
		 *
		 * @param string   $content The email.
		 * @param string[] $groups  The groups whose tags apply.
		 * @param mixed    $data    Whatever the email is about.
		 *
		 * @since 2.0.0
		 */
		$content = (string) apply_filters( Runtime::hook( 'before_tags' ), $content, $this->groups, $data );

		$replacements = [];

		foreach ( $this->used_in( $content ) as $name ) {
			$tag = $tags[ $name ] ?? null;

			if ( null === $tag ) {
				$this->unknown[]              = $name;
				$replacements[ '{' . $name . '}' ] = '';

				continue;
			}

			$replacements[ $tag->placeholder() ] = $this->one( $tag, $data );
		}

		if ( [] !== $replacements ) {
			$content = strtr( $content, $replacements );
		}

		/**
		 * Change an email after its tags are filled in.
		 *
		 * @param string   $content The email.
		 * @param string[] $groups  The groups whose tags apply.
		 * @param mixed    $data    Whatever the email is about.
		 *
		 * @since 2.0.0
		 */
		return (string) apply_filters( Runtime::hook( 'after_tags' ), $content, $this->groups, $data );
	}

	/**
	 * Fill the tags in with samples, for a preview.
	 *
	 * @param string $content The email, with tags in it.
	 *
	 * @return string
	 */
	public function preview( string $content ): string {
		$tags         = Tags::groups( $this->groups );
		$replacements = [];

		foreach ( $this->used_in( $content ) as $name ) {
			$tag = $tags[ $name ] ?? null;

			$replacements[ '{' . $name . '}' ] = null === $tag ? '' : $tag->preview();
		}

		return [] === $replacements ? $content : strtr( $content, $replacements );
	}

	/**
	 * Whether everything went in.
	 *
	 * @return bool
	 */
	public function is_complete(): bool {
		return [] === $this->problems;
	}

	/**
	 * What went wrong, by tag.
	 *
	 * @return array<string, string>
	 */
	public function problems(): array {
		return $this->problems;
	}

	/**
	 * Tags the content used that nobody registered.
	 *
	 * Not a failure — an email may legitimately mention a tag from a group it
	 * did not name — but worth being able to see, because the usual cause is
	 * a typo and the usual symptom is a gap in the sentence.
	 *
	 * @return string[]
	 */
	public function unknown(): array {
		return $this->unknown;
	}

	/**
	 * Fill in one tag.
	 *
	 * @param Tag   $tag  The tag.
	 * @param mixed $data Whatever the email is about.
	 *
	 * @return string
	 */
	private function one( Tag $tag, mixed $data ): string {
		$rendered = $tag->render( $data );

		if ( $rendered instanceof WP_Error ) {
			$this->problems[ $tag->name() ] = $rendered->get_error_message();

			return '';
		}

		/**
		 * Change what one tag was filled in with.
		 *
		 * The hook name carries this build's prefix, so two plugins each
		 * bundling a prefixed copy do not filter each other's emails — the
		 * name used to be `email_template_tag_{name}` for everybody.
		 *
		 * @param string   $rendered What it came out as.
		 * @param mixed    $data     Whatever the email is about.
		 * @param string[] $groups   The groups whose tags apply.
		 *
		 * @since 2.0.0
		 */
		return (string) apply_filters(
			Runtime::hook( 'tag_' . $tag->name() ),
			$rendered,
			$data,
			$this->groups
		);
	}

	/**
	 * The tag names a piece of content actually uses.
	 *
	 * Read out of the content rather than looped over the registry: a site
	 * with four hundred registered tags used to run four hundred
	 * str_contains() over every email, and an email that used a tag nobody
	 * registered was never noticed at all.
	 *
	 * @param string $content The email.
	 *
	 * @return string[]
	 */
	private function used_in( string $content ): array {
		if ( ! preg_match_all( '/\{([a-z0-9_]+)\}/i', $content, $found ) ) {
			return [];
		}

		return array_values( array_unique( $found[1] ) );
	}
}
