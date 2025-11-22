<?php
/**
 * Logo Component
 *
 * @package     ArrayPress\RegisterEmails
 * @copyright   Copyright (c) 2025, ArrayPress Limited
 * @license     GPL2+
 * @version     1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\RegisterEmails\Parts;

use ArrayPress\RegisterEmails\Abstracts\Component;

/**
 * Logo Component
 *
 * Simple logo display with fixed sizing.
 *
 * @since 1.0.0
 */
class Logo extends Component {

	/**
	 * Render logo component
	 *
	 * @param array $args  {
	 *
	 * @type string $url   Logo URL (required)
	 * @type string $alt   Alt text
	 * @type string $align Alignment (left|center|right)
	 *                     }
	 *
	 * @return string Logo HTML
	 * @since 1.0.0
	 */
	public static function render( array $args = [] ): string {
		$url   = $args['url'] ?? '';
		$alt   = $args['alt'] ?? get_bloginfo( 'name' );
		$align = $args['align'] ?? 'left';

		if ( empty( $url ) ) {
			return '';
		}

		$img = sprintf(
			'<img src="%s" alt="%s" style="height: 50px; max-width: 200px; height: auto;">',
			esc_url( $url ),
			esc_attr( $alt )
		);

		if ( $align === 'center' || $align === 'right' ) {
			$img = sprintf( '<div style="text-align: %s;">%s</div>', esc_attr( $align ), $img );
		}

		return $img;
	}

}