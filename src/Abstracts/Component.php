<?php
/**
 * Abstract Component Base Class
 *
 * @package     ArrayPress\RegisterEmails
 * @copyright   Copyright (c) 2025, ArrayPress Limited
 * @license     GPL2+
 * @version     1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\RegisterEmails\Abstracts;

use ArrayPress\RegisterEmails\Interfaces\Component as ComponentInterface;

/**
 * AbstractComponent
 *
 * Base class for email components with common utilities.
 *
 * @since 1.0.0
 */
abstract class Component implements ComponentInterface {

	/**
	 * Convert hex color to rgba
	 *
	 * @param string $hex   Hex color code
	 * @param float  $alpha Alpha value (0-1)
	 *
	 * @return string RGBA color string
	 * @since 1.0.0
	 */
	protected static function hex_to_rgba( string $hex, float $alpha = 1 ): string {
		$hex = ltrim( $hex, '#' );

		if ( strlen( $hex ) === 3 ) {
			$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
		}

		$r = hexdec( substr( $hex, 0, 2 ) );
		$g = hexdec( substr( $hex, 2, 2 ) );
		$b = hexdec( substr( $hex, 4, 2 ) );

		return sprintf( 'rgba(%d, %d, %d, %s)', $r, $g, $b, $alpha );
	}

}