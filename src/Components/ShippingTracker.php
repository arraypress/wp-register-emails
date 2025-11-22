<?php
/**
 * Shipping Tracker Component
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
 * Shipping Tracker Component
 *
 * Displays shipment tracking information with progress steps.
 *
 * @since 1.0.0
 */
class ShippingTracker extends Component {

	/**
	 * Render shipping tracker component
	 *
	 * @param array $args               {
	 *
	 * @type string $carrier            Carrier name
	 * @type string $tracking_number    Tracking number
	 * @type string $status             Current status
	 * @type string $estimated_delivery Estimated delivery date
	 * @type string $tracking_url       Link to carrier tracking page
	 * @type array  $steps              Progress steps array
	 *                                  }
	 *
	 * @return string Shipping tracker HTML
	 * @since 1.0.0
	 */
	public static function render( array $args = [] ): string {
		$carrier            = $args['carrier'] ?? '';
		$tracking_number    = $args['tracking_number'] ?? '';
		$status             = $args['status'] ?? '';
		$estimated_delivery = $args['estimated_delivery'] ?? '';
		$tracking_url       = $args['tracking_url'] ?? '';
		$steps              = $args['steps'] ?? [];

		$html = '<div class="component component-shipping-tracker" style="background: #f9fafb; border-radius: 8px; padding: 24px; margin: 24px 0;">';

		// Header with carrier and tracking number
		if ( $carrier || $tracking_number ) {
			$html .= '<div class="tracker-header" style="margin-bottom: 20px;">';

			if ( $carrier ) {
				$html .= sprintf(
					'<div class="tracker-carrier" style="font-size: 14px; color: #6b7280; margin-bottom: 4px;">%s</div>',
					esc_html( $carrier )
				);
			}

			if ( $tracking_number ) {
				if ( $tracking_url ) {
					$html .= sprintf(
						'<a href="%s" class="tracker-number-link" style="font-size: 18px; font-weight: 600; color: #2563eb; text-decoration: none;">%s</a>',
						esc_url( $tracking_url ),
						esc_html( $tracking_number )
					);
				} else {
					$html .= sprintf(
						'<div class="tracker-number" style="font-size: 18px; font-weight: 600; color: #111827;">%s</div>',
						esc_html( $tracking_number )
					);
				}
			}

			$html .= '</div>';
		}

		// Current status
		if ( $status ) {
			$html .= sprintf(
				'<div class="tracker-status" style="background: white; border-left: 4px solid #10b981; padding: 12px 16px; margin-bottom: 20px; border-radius: 4px;">
					<div class="status-label" style="font-size: 14px; color: #6b7280;">Current Status</div>
					<div class="status-value" style="font-size: 16px; font-weight: 600; color: #111827; margin-top: 4px;">%s</div>',
				esc_html( $status )
			);

			if ( $estimated_delivery ) {
				$html .= sprintf(
					'<div class="status-delivery" style="font-size: 14px; color: #6b7280; margin-top: 8px;">Est. Delivery: %s</div>',
					esc_html( $estimated_delivery )
				);
			}

			$html .= '</div>';
		}

		// Progress steps
		if ( ! empty( $steps ) ) {
			$html .= '<div class="tracker-steps" style="margin-top: 20px;">';

			$total_steps = count( $steps );
			foreach ( $steps as $index => $step ) {
				$is_completed = $step['completed'] ?? false;
				$is_last      = ( $index === $total_steps - 1 );

				$html .= sprintf(
					'<div class="tracker-step %s %s" style="display: flex; align-items: flex-start; margin-bottom: %s;">',
					$is_completed ? 'step-completed' : 'step-pending',
					$is_last ? 'step-last' : '',
					$is_last ? '0' : '16px'
				);

				// Step indicator
				$indicator_bg = $is_completed ? '#10b981' : '#e5e7eb';
				$html         .= sprintf(
					'<div class="step-indicator %s" style="width: 24px; height: 24px; background: %s; border-radius: 50%%; margin-right: 12px; flex-shrink: 0; position: relative;">',
					$is_completed ? 'indicator-completed' : 'indicator-pending',
					$indicator_bg
				);

				// Checkmark for completed steps
				if ( $is_completed ) {
					$html .= '<div class="step-checkmark" style="color: white; text-align: center; line-height: 24px; font-size: 14px;">✓</div>';
				}

				// Connecting line (except for last item)
				if ( ! $is_last ) {
					$line_bg = $is_completed ? '#10b981' : '#e5e7eb';
					$html    .= sprintf(
						'<div class="step-line" style="position: absolute; top: 24px; left: 11px; width: 2px; height: 40px; background: %s;"></div>',
						$line_bg
					);
				}

				$html .= '</div>';

				// Step label
				$text_color  = $is_completed ? '#111827' : '#9ca3af';
				$font_weight = $is_completed ? '500' : 'normal';

				$html .= sprintf(
					'<div class="step-content" style="flex: 1;">
						<div class="step-label" style="font-size: 14px; color: %s; font-weight: %s;">%s</div>',
					$text_color,
					$font_weight,
					esc_html( $step['label'] ?? '' )
				);

				// Optional timestamp
				if ( ! empty( $step['timestamp'] ) ) {
					$html .= sprintf(
						'<div class="step-timestamp" style="font-size: 12px; color: #9ca3af; margin-top: 2px;">%s</div>',
						esc_html( $step['timestamp'] )
					);
				}

				$html .= '</div></div>';
			}

			$html .= '</div>';
		}

		// Track shipment button
		if ( $tracking_url ) {
			$html .= sprintf(
				'<div class="tracker-button-wrapper" style="margin-top: 24px;">
					<a href="%s" class="component-button button-track" style="display: inline-block; background: #2563eb; color: white; padding: 12px 24px; border-radius: 6px; text-decoration: none; font-weight: 500;">Track Shipment</a>
				</div>',
				esc_url( $tracking_url )
			);
		}

		$html .= '</div>';

		return $html;
	}

}