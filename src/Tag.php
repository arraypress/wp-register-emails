<?php
/**
 * Tag
 *
 * @package     ArrayPress\RegisterEmails
 * @copyright   Copyright (c) 2026, ArrayPress Limited
 * @license     GPL2+
 * @since       2.0.0
 */

declare( strict_types=1 );

namespace ArrayPress\RegisterEmails;

use ArrayPress\RegisterEmails\Render\Components;
use InvalidArgumentException;
use Throwable;
use WP_Error;

/**
 * One thing that can go in an email and be filled in later.
 *
 * `{customer_name}` is a tag; so is `{invoice}`, which happens to render a
 * table rather than a word. The difference is only what the callback gives
 * back and which component draws it.
 */
final class Tag {

	/**
	 * Its name, without braces.
	 *
	 * @var string
	 */
	private string $name;

	/**
	 * Its configuration.
	 *
	 * @var array<string, mixed>
	 */
	private array $config;

	/**
	 * Construct.
	 *
	 * @param string               $name   Its name.
	 * @param array<string, mixed> $config Its configuration.
	 */
	public function __construct( string $name, array $config ) {
		$this->name   = trim( $name, '{} ' );
		$this->config = array_merge(
			[
				'type'        => 'text',
				'label'       => ucfirst( str_replace( '_', ' ', $this->name ) ),
				'description' => '',
				'callback'    => null,
				'options'     => [],
				'preview'     => null,
				'groups'      => [],
			],
			$config
		);
	}

	/**
	 * Its name.
	 *
	 * @return string
	 */
	public function name(): string {
		return $this->name;
	}

	/**
	 * How it is written in an email.
	 *
	 * @return string
	 */
	public function placeholder(): string {
		return '{' . $this->name . '}';
	}

	/**
	 * One configuration value.
	 *
	 * @param string $key      The key.
	 * @param mixed  $fallback Returned when it is not set.
	 *
	 * @return mixed
	 */
	public function get( string $key, mixed $fallback = null ): mixed {
		return $this->config[ $key ] ?? $fallback;
	}

	/**
	 * Whether it draws a component rather than putting in a word.
	 *
	 * @return bool
	 */
	public function is_component(): bool {
		return Components::has( (string) $this->config['type'] );
	}

	/**
	 * Fill it in.
	 *
	 * A tag that fails comes back as a WP_Error rather than as an empty
	 * string. It used to be caught, written to the error log and replaced
	 * with nothing — so an email went to a customer with a blank where the
	 * order total should have been, and the only record was a line in a file
	 * nobody reads. The caller decides what to do about it; what it must not
	 * do is happen quietly.
	 *
	 * @param mixed $data Whatever the email is about.
	 *
	 * @return string|WP_Error
	 */
	public function render( mixed $data ): string|WP_Error {
		try {
			return $this->is_component() ? $this->component( $data ) : $this->text( $data );
		} catch ( Throwable $thrown ) {
			return new WP_Error(
				'tag_failed',
				sprintf(
					/* translators: 1: a merge tag's name, 2: what went wrong. */
					__( 'The %1$s tag could not be filled in: %2$s', 'arraypress' ),
					$this->placeholder(),
					$thrown->getMessage()
				),
				[ 'tag' => $this->name ]
			);
		}
	}

	/**
	 * What it looks like with nothing real behind it.
	 *
	 * For the preview on a settings screen, where there is no order to show.
	 *
	 * @return string
	 */
	public function preview(): string {
		$preview = $this->config['preview'];

		if ( is_callable( $preview ) ) {
			$preview = $preview();
		}

		if ( is_string( $preview ) ) {
			return $preview;
		}

		if ( is_array( $preview ) && $this->is_component() ) {
			return Components::render( (string) $this->config['type'], array_merge( (array) $this->config['options'], $preview ) );
		}

		// Nothing was given, so the tag shows as itself. Better than a blank,
		// which reads as a tag that is broken rather than one nobody wrote a
		// sample for.
		return $this->is_component() ? '' : $this->placeholder();
	}

	/**
	 * How the field kit's email editor wants a tag described.
	 *
	 * The kit draws the chooser and this owns the tags, so this is the seam
	 * between them and it is one method rather than a shape each side hopes
	 * the other agrees about.
	 *
	 * @return array{name: string, tag: string, description: string}
	 */
	public function for_editor(): array {
		return [
			'name'        => (string) $this->config['label'],
			'tag'         => $this->placeholder(),
			'description' => (string) $this->config['description'],
		];
	}

	/**
	 * Fill in a tag that is a word.
	 *
	 * @param mixed $data Whatever the email is about.
	 *
	 * @return string
	 */
	private function text( mixed $data ): string {
		if ( ! is_callable( $this->config['callback'] ) ) {
			return '';
		}

		$value = call_user_func( $this->config['callback'], $data );

		return is_scalar( $value ) ? (string) $value : '';
	}

	/**
	 * Fill in a tag that is a component.
	 *
	 * @param mixed $data Whatever the email is about.
	 *
	 * @return string
	 */
	private function component( mixed $data ): string {
		$type = (string) $this->config['type'];
		$args = (array) $this->config['options'];

		if ( is_callable( $this->config['callback'] ) ) {
			$given = call_user_func( $this->config['callback'], $data );

			// Nothing to show. A basket with no items is not an error, and
			// an empty table is worse than no table.
			if ( null === $given || false === $given ) {
				return '';
			}

			if ( is_array( $given ) ) {
				$args = array_merge( $args, $given );
			} elseif ( is_scalar( $given ) ) {
				$key = Components::primary_key( $type );

				// A component whose main argument is a list has no obvious
				// single value, and putting the string in anyway is what made
				// an order-items table foreach over a string and render half
				// a table with a warning in the middle of it.
				if ( '' === $key ) {
					throw new InvalidArgumentException(
						sprintf(
							/* translators: 1: a component type, 2: a merge tag's name. */
							esc_html__( 'The %1$s component needs an array of values; the %2$s callback returned a single one.', 'arraypress' ),
							esc_html( $type ),
							esc_html( $this->placeholder() )
						)
					);
				}

				$args[ $key ] = $given;
			}
		}

		return Components::render( $type, $args );
	}
}
