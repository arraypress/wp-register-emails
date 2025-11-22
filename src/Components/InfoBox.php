<?php
/**
 * Info Box Component
 *
 * @package     ArrayPress\RegisterEmails
 * @copyright   Copyright (c) 2025, ArrayPress Limited
 * @license     GPL2+
 * @version     1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\RegisterEmails\Components;

use ArrayPress\RegisterEmails\Abstracts\Component;

/**
 * Info Box Component
 *
 * Simple information box with optional icon.
 *
 * @since 1.0.0
 */
class InfoBox extends Component {

	/**
	 * Render info box component
	 *
	 * @param array $args       {
	 *
	 * @type string $title      Box title
	 * @type string $content    Box content (required)
	 * @type string $icon       Optional emoji/icon
	 * @type string $background Background color
	 * @type string $color      Text color
	 *                          }
	 *
	 * @return string Info box HTML
	 * @since 1.0.0
	 */
	public static function render( array $args = [] ): string {
		$title      = $args['title'] ?? '';
		$content    = $args['content'] ?? '';
		$icon       = $args['icon'] ?? '';
		$background = $args['background'] ?? '#f3f4f6';
		$color      = $args['color'] ?? '#374151';

		if ( empty( $title ) && empty( $content ) ) {
			return '';
		}

		$html = sprintf(
			'<div class="component component-info-box" style="background: %s; padding: 20px; border-radius: 8px; margin: 20px 0;">',
			esc_attr( $background )
		);

		if ( ! empty( $icon ) ) {
			$html .= sprintf( '<span class="info-box-icon" style="font-size: 24px; margin-bottom: 10px; display: block;">%s</span>', esc_html( $icon ) );
		}

		if ( ! empty( $title ) ) {
			$html .= sprintf( '<h3 class="info-box-title" style="margin: 0 0 10px; font-size: 16px; color: %s;">%s</h3>', esc_attr( $color ), esc_html( $title ) );
		}

		if ( ! empty( $content ) ) {
			$html .= sprintf( '<div class="info-box-content" style="color: %s;">%s</div>', esc_attr( $color ), wp_kses_post( $content ) );
		}

		$html .= '</div>';

		return $html;
	}

}