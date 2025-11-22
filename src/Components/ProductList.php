<?php
/**
 * Product List Component
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
 * Product List Component
 *
 * Simple product listing with optional images.
 *
 * @since 1.0.0
 */
class ProductList extends Component {

	/**
	 * Render product list component
	 *
	 * @param array $args     {
	 *
	 * @type array  $products Product data array (required)
	 *                        }
	 *
	 * Each product can contain:
	 * - name: Product name
	 * - description: Product description
	 * - price: Product price
	 * - image: Product image URL
	 * - quantity: Quantity (for order emails)
	 * - url: Product URL (makes name clickable)
	 * - button_text: Button text (default: 'View Product')
	 * - button_url: Button URL (can be different from product URL)
	 *
	 * @return string Product list HTML
	 * @since 1.0.0
	 */
	public static function render( array $args = [] ): string {
		$products = $args['products'] ?? $args;

		if ( empty( $products ) || ! is_array( $products ) ) {
			return '';
		}

		$html = '<div class="component component-product-list" style="margin: 24px 0;">';

		foreach ( $products as $product ) {
			$html .= '<div class="product-item" style="border: 1px solid #e5e7eb; border-radius: 8px; padding: 16px; margin-bottom: 12px;">';
			$html .= '<table width="100%"><tr>';

			// Image (optionally linked)
			if ( ! empty( $product['image'] ) ) {
				$image_html = sprintf(
					'<img src="%s" class="product-image" style="width: 60px; height: 60px; object-fit: cover; border-radius: 4px;">',
					esc_url( $product['image'] )
				);

				// Wrap image in link if URL provided
				if ( ! empty( $product['url'] ) ) {
					$image_html = sprintf(
						'<a href="%s" style="text-decoration: none;">%s</a>',
						esc_url( $product['url'] ),
						$image_html
					);
				}

				$html .= sprintf(
					'<td class="product-image-cell" style="width: 80px; vertical-align: top;">%s</td>',
					$image_html
				);
			}

			// Details
			$html .= '<td class="product-details" style="vertical-align: top; padding-left: 12px;">';

			if ( ! empty( $product['name'] ) ) {
				$name_html = esc_html( $product['name'] );

				// Make name clickable if URL provided
				if ( ! empty( $product['url'] ) ) {
					$name_html = sprintf(
						'<a href="%s" class="product-link" style="color: #111827; text-decoration: none;">%s</a>',
						esc_url( $product['url'] ),
						$name_html
					);
				}

				$html .= sprintf( '<strong class="product-name" style="font-size: 16px; color: #111827;">%s</strong>', $name_html );
			}

			if ( ! empty( $product['description'] ) ) {
				$html .= sprintf( '<div class="product-description" style="color: #6b7280; font-size: 14px; margin-top: 4px;">%s</div>', esc_html( $product['description'] ) );
			}

			if ( ! empty( $product['quantity'] ) ) {
				$html .= sprintf( '<div class="product-quantity" style="color: #6b7280; font-size: 14px; margin-top: 4px;">Quantity: %s</div>', esc_html( $product['quantity'] ) );
			}

			// Add button if button_url is provided
			if ( ! empty( $product['button_url'] ) ) {
				$button_text = $product['button_text'] ?? 'View Product';
				$html        .= sprintf(
					'<div style="margin-top: 8px;">
                   <a href="%s" class="component-button product-button" style="display: inline-block; background: #2563eb; color: white; padding: 8px 16px; border-radius: 4px; text-decoration: none; font-size: 13px; font-weight: 500;">%s</a>
                </div>',
					esc_url( $product['button_url'] ),
					esc_html( $button_text )
				);
			}

			$html .= '</td>';

			// Price
			if ( ! empty( $product['price'] ) ) {
				$html .= sprintf(
					'<td class="product-price-cell" style="text-align: right; vertical-align: top;">
                   <strong class="product-price" style="font-size: 18px; color: #111827;">%s</strong>
                </td>',
					esc_html( $product['price'] )
				);
			}

			$html .= '</tr></table>';
			$html .= '</div>';
		}

		$html .= '</div>';

		return $html;
	}

}