<?php
/**
 * Order Items Component
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
 * Order Items Component
 *
 * Professional invoice-style table for order items with automatic calculations.
 * Supports all Stripe currencies via wp-currencies library.
 *
 * @since 1.0.0
 */
class OrderItems extends Component {

	/**
	 * Render order items table
	 *
	 * @param array $args     {
	 *
	 * @type array  $items    Array of line items with 'name', 'quantity', 'price' (in cents)
	 * @type int    $subtotal Subtotal amount in cents (auto-calculated if not provided)
	 * @type int    $tax      Tax amount in cents (optional)
	 * @type int    $discount Discount amount in cents (optional)
	 * @type int    $shipping Shipping amount in cents (optional)
	 * @type int    $total    Total amount in cents (auto-calculated if not provided)
	 * @type string $currency Currency code (default 'USD')
	 *                        }
	 *
	 * @return string Order items table HTML
	 * @since 1.0.0
	 */
	public static function render( array $args = [] ): string {
		$items    = $args['items'] ?? [];
		$currency = $args['currency'] ?? 'USD';

		if ( empty( $items ) ) {
			return '';
		}

		// Start table with better styling
		$html = '<table class="component component-order-items" style="width: 100%; border-collapse: collapse; margin: 24px 0; font-size: 14px;">';

		// Header row with proper alignment
		$html .= '
			<thead class="order-header">
				<tr style="border-bottom: 2px solid #e5e7eb;">
					<th class="order-col-item" style="padding: 12px 8px; text-align: left; font-weight: 600; color: #374151;">Item</th>
					<th class="order-col-qty" style="padding: 12px 8px; text-align: center; font-weight: 600; color: #374151;">Qty</th>
					<th class="order-col-price" style="padding: 12px 8px; text-align: right; font-weight: 600; color: #374151;">Price</th>
					<th class="order-col-total" style="padding: 12px 8px; text-align: right; font-weight: 600; color: #374151;">Total</th>
				</tr>
			</thead>
			<tbody class="order-items">';

		// Calculate subtotal from items
		$calculated_subtotal = 0;

		// Item rows
		foreach ( $items as $item ) {
			$name     = $item['name'] ?? '';
			$quantity = (int) ( $item['quantity'] ?? 1 );
			$price    = (int) ( $item['price'] ?? 0 ); // Price in cents
			$total    = $quantity * $price;

			$calculated_subtotal += $total;

			$html .= sprintf(
				'<tr class="order-item-row" style="border-bottom: 1px solid #f3f4f6;">
					<td class="order-item-name" style="padding: 12px 8px; color: #111827;">%s</td>
					<td class="order-item-qty" style="padding: 12px 8px; text-align: center; color: #6b7280;">%s</td>
					<td class="order-item-price" style="padding: 12px 8px; text-align: right; color: #6b7280;">%s</td>
					<td class="order-item-total" style="padding: 12px 8px; text-align: right; color: #111827; font-weight: 500;">%s</td>
				</tr>',
				esc_html( $name ),
				esc_html( (string) $quantity ),
				format_currency( $price, $currency ),
				format_currency( $total, $currency )
			);
		}

		$html .= '</tbody>';

		// Summary section
		$html .= '<tfoot class="order-summary">';

		// Subtotal
		$subtotal = isset( $args['subtotal'] ) ? (int) $args['subtotal'] : $calculated_subtotal;

		$html .= sprintf(
			'<tr class="order-subtotal-row">
				<td colspan="3" class="order-label" style="padding: 8px 8px 4px; text-align: right; color: #6b7280; font-size: 13px;">Subtotal:</td>
				<td class="order-value" style="padding: 8px 8px 4px; text-align: right; color: #374151;">%s</td>
			</tr>',
			format_currency( $subtotal, $currency )
		);

		// Track running total for calculations
		$running_total = $subtotal;

		// Optional discount
		if ( ! empty( $args['discount'] ) && $args['discount'] > 0 ) {
			$discount = (int) $args['discount'];

			$html          .= sprintf(
				'<tr class="order-discount-row">
					<td colspan="3" class="order-label" style="padding: 4px 8px; text-align: right; color: #6b7280; font-size: 13px;">Discount:</td>
					<td class="order-value order-discount" style="padding: 4px 8px; text-align: right; color: #059669;">-%s</td>
				</tr>',
				format_currency( $discount, $currency )
			);
			$running_total -= $discount;
		}

		// Optional shipping
		if ( ! empty( $args['shipping'] ) && $args['shipping'] > 0 ) {
			$shipping = (int) $args['shipping'];

			$html          .= sprintf(
				'<tr class="order-shipping-row">
					<td colspan="3" class="order-label" style="padding: 4px 8px; text-align: right; color: #6b7280; font-size: 13px;">Shipping:</td>
					<td class="order-value" style="padding: 4px 8px; text-align: right; color: #374151;">%s</td>
				</tr>',
				format_currency( $shipping, $currency )
			);
			$running_total += $shipping;
		}

		// Optional tax
		if ( ! empty( $args['tax'] ) && $args['tax'] > 0 ) {
			$tax           = (int) $args['tax'];
			$html          .= sprintf(
				'<tr class="order-tax-row">
					<td colspan="3" class="order-label" style="padding: 4px 8px; text-align: right; color: #6b7280; font-size: 13px;">Tax:</td>
					<td class="order-value" style="padding: 4px 8px; text-align: right; color: #374151;">%s</td>
				</tr>',
				format_currency( $tax, $currency )
			);
			$running_total += $tax;
		}

		// Total row with emphasis
		$total = isset( $args['total'] ) ? (int) $args['total'] : $running_total;

		$html .= sprintf(
			'<tr class="order-total-row" style="border-top: 2px solid #e5e7eb;">
				<td colspan="3" class="order-total-label" style="padding: 12px 8px 8px; text-align: right; font-weight: 700; color: #111827; font-size: 16px;">Total:</td>
				<td class="order-total-value" style="padding: 12px 8px 8px; text-align: right; font-weight: 700; color: #111827; font-size: 16px;">%s</td>
			</tr>',
			format_currency( $total, $currency )
		);

		$html .= '</tfoot></table>';

		return $html;
	}

}