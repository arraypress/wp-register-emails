<?php
/**
 * Subscription Status Component
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
 * Subscription Status Component
 *
 * Displays subscription plan details and billing information.
 *
 * @since 1.0.0
 */
class SubscriptionStatus extends Component {

	/**
	 * Render subscription status component
	 *
	 * @param array $args               {
	 *
	 * @type string $plan               Plan name
	 * @type string $status             Status (Active, Cancelled, Past Due, etc.)
	 * @type string $next_billing_date  Next billing date
	 * @type int    $amount             Amount in cents
	 * @type string $currency           Currency code
	 * @type string $interval           Billing interval (month, year, etc.)
	 * @type string $cancel_url         Cancellation URL
	 * @type string $update_payment_url Update payment method URL
	 * @type array  $features           Optional list of plan features
	 *                                  }
	 *
	 * @return string Subscription status HTML
	 * @since 1.0.0
	 */
	public static function render( array $args = [] ): string {
		$plan               = $args['plan'] ?? '';
		$status             = $args['status'] ?? 'Active';
		$next_billing_date  = $args['next_billing_date'] ?? '';
		$amount             = isset( $args['amount'] ) ? (int) $args['amount'] : 0;
		$currency           = $args['currency'] ?? 'USD';
		$interval           = $args['interval'] ?? 'month';
		$cancel_url         = $args['cancel_url'] ?? '';
		$update_payment_url = $args['update_payment_url'] ?? '';
		$features           = $args['features'] ?? [];

		// Determine status color
		$status_colors = [
			'Active'    => '#10b981',
			'Trial'     => '#3b82f6',
			'Past Due'  => '#f59e0b',
			'Cancelled' => '#ef4444',
			'Expired'   => '#6b7280',
		];
		$status_color  = $status_colors[ $status ] ?? '#6b7280';

		$html = '<div class="component component-subscription-status" style="background: #ffffff; border: 1px solid #e5e7eb; border-radius: 8px; padding: 24px; margin: 24px 0;">';

		// Header with plan name and status
		$html .= '<div class="subscription-header" style="border-bottom: 1px solid #f3f4f6; padding-bottom: 16px; margin-bottom: 16px;">';

		if ( $plan ) {
			$html .= sprintf(
				'<h3 class="subscription-plan-name" style="margin: 0 0 8px; font-size: 20px; font-weight: 600; color: #111827;">%s</h3>',
				esc_html( $plan )
			);
		}

		$status_class = 'status-' . strtolower( str_replace( ' ', '-', $status ) );
		$html         .= sprintf(
			'<span class="subscription-status-badge %s" style="display: inline-block; background: %s; color: white; padding: 4px 12px; border-radius: 9999px; font-size: 12px; font-weight: 500; text-transform: uppercase;">%s</span>',
			esc_attr( $status_class ),
			$status_color,
			esc_html( $status )
		);

		$html .= '</div>';

		// Billing information
		$html .= '<div class="subscription-billing" style="margin-bottom: 20px;">';

		// Format amount using wp-currencies if available
		if ( function_exists( 'format_currency' ) && $amount > 0 ) {
			$amount_display = format_currency( $amount, $currency );
		} elseif ( $amount > 0 ) {
			$amount_display = '$' . number_format( $amount / 100, 2 );
		} else {
			$amount_display = 'Free';
		}

		// Price and interval
		if ( $amount > 0 ) {
			$interval_text = [
				'day'   => 'per day',
				'week'  => 'per week',
				'month' => 'per month',
				'year'  => 'per year',
			];

			$html .= sprintf(
				'<div class="subscription-price" style="font-size: 24px; font-weight: 700; color: #111827; margin-bottom: 4px;">%s <span class="subscription-interval" style="font-size: 16px; font-weight: 400; color: #6b7280;">%s</span></div>',
				$amount_display,
				$interval_text[ $interval ] ?? $interval
			);
		} else {
			$html .= sprintf(
				'<div class="subscription-price" style="font-size: 24px; font-weight: 700; color: #111827; margin-bottom: 4px;">%s</div>',
				$amount_display
			);
		}

		// Next billing date
		if ( $next_billing_date && in_array( $status, [ 'Active', 'Trial' ], true ) ) {
			$html .= sprintf(
				'<div class="subscription-next-billing" style="font-size: 14px; color: #6b7280;">Next billing date: <strong>%s</strong></div>',
				esc_html( $next_billing_date )
			);
		}

		$html .= '</div>';

		// Features list (optional)
		if ( ! empty( $features ) ) {
			$html .= '<div class="subscription-features" style="background: #f9fafb; border-radius: 6px; padding: 16px; margin-bottom: 20px;">';
			$html .= '<div class="features-title" style="font-size: 12px; text-transform: uppercase; color: #6b7280; margin-bottom: 12px; font-weight: 600;">Included Features</div>';
			$html .= '<ul class="features-list" style="margin: 0; padding: 0; list-style: none;">';

			foreach ( $features as $feature ) {
				$html .= sprintf(
					'<li class="feature-item" style="padding: 4px 0; color: #374151;">
						<span class="feature-check" style="color: #10b981; margin-right: 8px;">✓</span>%s
					</li>',
					esc_html( $feature )
				);
			}

			$html .= '</ul></div>';
		}

		// Action buttons
		if ( $update_payment_url || $cancel_url ) {
			$html .= '<div class="subscription-actions" style="display: flex; gap: 12px; padding-top: 16px; border-top: 1px solid #f3f4f6;">';

			if ( $update_payment_url && in_array( $status, [ 'Active', 'Past Due' ], true ) ) {
				$html .= sprintf(
					'<a href="%s" class="component-button button-update-payment" style="display: inline-block; background: #2563eb; color: white; padding: 10px 20px; border-radius: 6px; text-decoration: none; font-size: 14px; font-weight: 500;">Update Payment</a>',
					esc_url( $update_payment_url )
				);
			}

			if ( $cancel_url && $status === 'Active' ) {
				$html .= sprintf(
					'<a href="%s" class="component-button button-cancel button-outline" style="display: inline-block; background: white; color: #6b7280; border: 1px solid #e5e7eb; padding: 10px 20px; border-radius: 6px; text-decoration: none; font-size: 14px; font-weight: 500;">Cancel Subscription</a>',
					esc_url( $cancel_url )
				);
			}

			$html .= '</div>';
		}

		$html .= '</div>';

		return $html;
	}

}