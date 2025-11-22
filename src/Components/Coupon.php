<?php
/**
 * Coupon Component
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
 * Coupon Component
 *
 * Simple coupon display block.
 *
 * @since 1.0.0
 */
class Coupon extends Component {

	/**
	 * Render coupon component
	 *
	 * @param array $args        {
	 *
	 * @type string $code        Coupon code (required)
	 * @type string $description Discount description
	 * @type string $expiry      Expiry date/text
	 * @type string $background  Background color (optional)
	 * @type string $border      Border color (optional)
	 * @type string $color       Text color (optional)
	 *                           }
	 *
	 * @return string Coupon HTML
	 * @since 1.0.0
	 */
	public static function render( array $args = [] ): string {
		$code        = $args['code'] ?? '';
		$description = $args['description'] ?? '';
		$expiry      = $args['expiry'] ?? '';
		$background  = $args['background'] ?? '#fef3c7';
		$border      = $args['border'] ?? '#f59e0b';
		$color       = $args['color'] ?? '#111';

		if ( empty( $code ) ) {
			return '';
		}

		$html = sprintf(
			'<div class="component component-coupon" style="background: %s; border: 2px dashed %s; border-radius: 8px; padding: 24px; margin: 20px 0; text-align: center;">',
			esc_attr( $background ),
			esc_attr( $border )
		);

		if ( ! empty( $description ) ) {
			$html .= sprintf(
				'<div class="coupon-description" style="font-size: 16px; color: #374151; margin-bottom: 16px;">%s</div>',
				esc_html( $description )
			);
		}

		$html .= sprintf(
			'<div class="coupon-code" style="background: white; display: inline-block; padding: 12px 24px; border-radius: 6px; font-family: monospace; font-size: 20px; font-weight: bold; letter-spacing: 2px; color: %s;">%s</div>',
			esc_attr( $color ),
			esc_html( $code )
		);

		if ( ! empty( $expiry ) ) {
			$html .= sprintf(
				'<div class="coupon-expiry" style="font-size: 14px; color: #6b7280; margin-top: 12px;">%s</div>',
				esc_html( $expiry )
			);
		}

		$html .= '</div>';

		return $html;
	}

}