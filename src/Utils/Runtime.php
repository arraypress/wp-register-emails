<?php
/**
 * Runtime Key Derivation
 *
 * @package     ArrayPress\RegisterEmails
 * @copyright   Copyright (c) 2026, ArrayPress Limited
 * @license     GPL2+
 * @since       2.0.0
 */

declare( strict_types=1 );

namespace ArrayPress\RegisterEmails\Utils;

/**
 * Every runtime string this library registers, derived from its own namespace.
 *
 * Strauss rewrites class namespaces and leaves string literals alone. Two
 * plugins each bundling a prefixed copy of this library therefore get
 * distinct classes but would otherwise register identical script handles and
 * identical transient keys.
 *
 * That is not merely untidy. The filters this library fires are named after
 * the tag they are filtering, so two plugins each carrying a prefixed copy
 * would fire the same `..._tag_customer_name` filter — and one plugin's
 * callback would rewrite the other plugin's email.
 *
 * The derivation exploits the one thing Strauss does rewrite: this file's
 * namespace. In a prefixed build `__NAMESPACE__` begins with the consumer's
 * prefix ("MyPlugin\ArrayPress\RegisterEmails\Utils"), unique per plugin
 * by construction, so every key comes out distinct with no configuration.
 */
final class Runtime {

	/**
	 * This library's own identifier, used when running unprefixed.
	 */
	private const LIBRARY = 'emails';

	/**
	 * The per-build prefix.
	 *
	 * "emails" for a plain Composer install — development, or a single
	 * consumer that does not use Strauss — and "{prefix}-emails" for a
	 * prefixed build.
	 *
	 * @return string
	 */
	public static function prefix(): string {
		return self::prefix_for( __NAMESPACE__ );
	}

	/**
	 * The prefix a given namespace would produce.
	 *
	 * Split out so the rule can be tested. prefix() reads __NAMESPACE__,
	 * which cannot be changed at runtime — the only way to exercise the
	 * prefixed case was to eval() a copy of this file under another
	 * namespace, and eval() refuses the `declare( strict_types=1 )` at the
	 * top of it on PHP 8.3 and 8.4. That test passed on 8.5 alone, which is
	 * a test that reports the local PHP version rather than the code.
	 *
	 * @param string $under The namespace this class is compiled under, prefixed or not.
	 *
	 * @return string
	 */
	public static function prefix_for( string $under ): string {
		$root = explode( '\\', $under )[0] ?? '';

		if ( '' === $root || 'ArrayPress' === $root ) {
			return self::LIBRARY;
		}

		return self::slug( $root ) . '-' . self::LIBRARY;
	}

	/**
	 * A script or style handle for this build.
	 *
	 * @param string $suffix Optional handle suffix.
	 *
	 * @return string
	 */
	public static function handle( string $suffix = '' ): string {
		return '' === $suffix ? self::prefix() : self::prefix() . '-' . $suffix;
	}

	/**
	 * An option or transient key for this build.
	 *
	 * @param string $suffix Optional key suffix.
	 *
	 * @return string
	 */
	public static function key( string $suffix = '' ): string {
		$base = str_replace( '-', '_', self::prefix() );

		return '' === $suffix ? $base : $base . '_' . $suffix;
	}

	/**
	 * A hook name for this build.
	 *
	 * @param string $suffix What the hook is for.
	 *
	 * @return string
	 */
	public static function hook( string $suffix ): string {
		return self::key( $suffix );
	}

	/**
	 * Reduce a namespace segment to a lowercase slug.
	 *
	 * Not sanitize_title(): this runs from `__NAMESPACE__` at class-load
	 * time, which can precede WordPress being fully loaded.
	 *
	 * @param string $value Value to slug.
	 *
	 * @return string
	 */
	private static function slug( string $value ): string {
		$value = preg_replace( '/[^A-Za-z0-9]+/', '-', $value ) ?? '';

		return strtolower( trim( $value, '-' ) );
	}
}
