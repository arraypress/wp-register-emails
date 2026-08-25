<?php
/**
 * Component
 *
 * @package     ArrayPress\RegisterEmails
 * @copyright   Copyright (c) 2026, ArrayPress Limited
 * @license     GPL2+
 * @since       2.0.0
 */

declare( strict_types=1 );

namespace ArrayPress\RegisterEmails\Interfaces;

/**
 * What every email component has to be able to do.
 */
interface Component {

	/**
	 * Draw it.
	 *
	 * @param array<string, mixed> $args What to draw.
	 *
	 * @return string
	 */
	public static function render( array $args = [] ): string;

	/**
	 * What a tag's callback is giving when it returns a string.
	 *
	 * A tag whose callback returns an array is handing over every argument.
	 * One that returns a string is handing over the obvious one — the text of
	 * a button, the message of an alert — and this is where each component
	 * says which of its arguments that is.
	 *
	 * Empty when there is no obvious one, which is the honest answer for
	 * anything whose main argument is a list. Tag then refuses a callback
	 * that returns a bare string, rather than putting it in and leaving the
	 * component to iterate over a word.
	 *
	 * @return string
	 */
	public static function primary_key(): string;
}
