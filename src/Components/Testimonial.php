<?php
/**
 * Testimonial Component
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
 * Testimonial Component
 *
 * Simple quote block with author.
 *
 * @since 1.0.0
 */
class Testimonial extends Component {

	/**
	 * Render testimonial component
	 *
	 * @param array $args   {
	 *
	 * @type string $quote  Quote text (required)
	 * @type string $author Author name
	 * @type string $role   Author role/company
	 * @type string $border Border color
	 * @type string $color  Text color
	 *                      }
	 *
	 * @return string Testimonial HTML
	 * @since 1.0.0
	 */
	public static function render( array $args = [] ): string {
		$quote  = $args['quote'] ?? '';
		$author = $args['author'] ?? '';
		$role   = $args['role'] ?? '';
		$border = $args['border'] ?? '#667eea';
		$color  = $args['color'] ?? '#374151';

		if ( empty( $quote ) ) {
			return '';
		}

		$html = sprintf(
			'<div class="component component-testimonial" style="margin: 20px 0; border-left: 4px solid %s; padding-left: 20px;">',
			esc_attr( $border )
		);

		$html .= sprintf(
			'<blockquote class="testimonial-quote" style="margin: 0 0 16px; font-size: 16px; line-height: 1.6; color: %s; font-style: italic;">"%s"</blockquote>',
			esc_attr( $color ),
			esc_html( $quote )
		);

		if ( ! empty( $author ) ) {
			$html .= '<div class="testimonial-attribution">';
			$html .= sprintf( '<div class="testimonial-author" style="font-weight: 600; color: #111;">%s</div>', esc_html( $author ) );
			if ( ! empty( $role ) ) {
				$html .= sprintf( '<div class="testimonial-role" style="font-size: 14px; color: #6b7280;">%s</div>', esc_html( $role ) );
			}
			$html .= '</div>';
		}

		$html .= '</div>';

		return $html;
	}

}