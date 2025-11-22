<?php
/**
 * Reward Balance Component
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
 * Reward Balance Component
 *
 * Displays loyalty points, credits, or reward balance information.
 *
 * @since 1.0.0
 */
class RewardBalance extends Component {

	/**
	 * Render reward balance component
	 *
	 * @param array $args            {
	 *
	 * @type int    $current_balance Current balance
	 * @type string $currency_label  Label (points, credits, coins, etc.)
	 * @type array  $recent_activity Recent transactions
	 * @type int    $expiring_soon   Amount expiring soon
	 * @type string $expiry_date     Expiration date
	 * @type string $redeem_url      URL to redeem rewards
	 * @type array  $tier_info       Current tier information
	 *                               }
	 *
	 * @return string Reward balance HTML
	 * @since 1.0.0
	 */
	public static function render( array $args = [] ): string {
		$current_balance = isset( $args['current_balance'] ) ? (int) $args['current_balance'] : 0;
		$currency_label  = $args['currency_label'] ?? 'points';
		$recent_activity = $args['recent_activity'] ?? [];
		$expiring_soon   = isset( $args['expiring_soon'] ) ? (int) $args['expiring_soon'] : 0;
		$expiry_date     = $args['expiry_date'] ?? '';
		$redeem_url      = $args['redeem_url'] ?? '';
		$tier_info       = $args['tier_info'] ?? [];

		$html = '<div class="component component-reward-balance reward-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px; padding: 24px; margin: 24px 0; color: white;">';

		// Balance display
		$html .= '<div class="reward-balance-display" style="text-align: center; padding: 20px 0;">';
		$html .= '<div class="reward-label" style="font-size: 14px; opacity: 0.9; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 1px;">Current Balance</div>';
		$html .= sprintf(
			'<div class="reward-amount" style="font-size: 48px; font-weight: 700; margin-bottom: 4px;">%s</div>',
			number_format( $current_balance )
		);
		$html .= sprintf(
			'<div class="reward-currency" style="font-size: 16px; opacity: 0.9;">%s</div>',
			esc_html( $currency_label )
		);

		// Tier info (optional)
		if ( ! empty( $tier_info ) ) {
			$tier_name      = $tier_info['name'] ?? '';
			$tier_progress  = $tier_info['progress'] ?? 0;
			$next_tier      = $tier_info['next_tier'] ?? '';
			$points_to_next = $tier_info['points_to_next'] ?? 0;

			if ( $tier_name ) {
				$html .= sprintf(
					'<div class="reward-tier" style="margin-top: 16px; padding-top: 16px; border-top: 1px solid rgba(255,255,255,0.3);">
						<span class="tier-badge" style="background: rgba(255,255,255,0.2); padding: 4px 12px; border-radius: 9999px; font-size: 12px;">%s</span>',
					esc_html( strtoupper( $tier_name ) )
				);

				if ( $next_tier && $points_to_next > 0 ) {
					$html .= sprintf(
						'<div class="tier-progress" style="font-size: 12px; margin-top: 8px; opacity: 0.9;">%s %s to %s</div>',
						number_format( $points_to_next ),
						esc_html( $currency_label ),
						esc_html( $next_tier )
					);
				}

				$html .= '</div>';
			}
		}

		$html .= '</div>';

		// Expiring soon warning
		if ( $expiring_soon > 0 && $expiry_date ) {
			$html .= sprintf(
				'<div class="reward-expiry-warning" style="background: rgba(255,255,255,0.2); border-radius: 8px; padding: 12px; margin-bottom: 20px; text-align: center;">
					⚠️ <strong>%s %s</strong> expiring on %s
				</div>',
				number_format( $expiring_soon ),
				esc_html( $currency_label ),
				esc_html( $expiry_date )
			);
		}

		$html .= '</div>';

		// Recent activity section (outside gradient box)
		if ( ! empty( $recent_activity ) ) {
			$html .= '<div class="component reward-activity" style="background: white; border: 1px solid #e5e7eb; border-radius: 8px; padding: 20px; margin: 24px 0;">';
			$html .= '<h3 class="activity-title" style="margin: 0 0 16px; font-size: 16px; font-weight: 600; color: #111827;">Recent Activity</h3>';

			$html .= '<table class="activity-table" style="width: 100%;">';

			foreach ( $recent_activity as $index => $activity ) {
				$description = $activity['description'] ?? '';
				$amount      = $activity['amount'] ?? '';
				$date        = $activity['date'] ?? '';
				$is_last     = ( $index === count( $recent_activity ) - 1 );

				// Determine color based on amount (+ or -)
				$amount_color = '#111827';
				$amount_class = 'activity-amount';
				if ( is_string( $amount ) ) {
					if ( strpos( $amount, '+' ) === 0 ) {
						$amount_color = '#10b981';
						$amount_class .= ' amount-positive';
					} elseif ( strpos( $amount, '-' ) === 0 ) {
						$amount_color = '#ef4444';
						$amount_class .= ' amount-negative';
					}
				}

				$html .= sprintf(
					'<tr class="activity-row %s" style="%s">',
					$is_last ? 'activity-row-last' : '',
					$is_last ? '' : 'border-bottom: 1px solid #f3f4f6;'
				);

				$html .= sprintf(
					'<td class="activity-description" style="padding: 12px 0; color: #374151; font-size: 14px;">%s',
					esc_html( $description )
				);

				if ( $date ) {
					$html .= sprintf(
						'<div class="activity-date" style="font-size: 12px; color: #9ca3af; margin-top: 2px;">%s</div>',
						esc_html( $date )
					);
				}

				$html .= '</td>';

				$html .= sprintf(
					'<td class="%s" style="padding: 12px 0; text-align: right; font-weight: 600; color: %s; font-size: 14px;">%s</td>',
					esc_attr( $amount_class ),
					$amount_color,
					esc_html( $amount )
				);

				$html .= '</tr>';
			}

			$html .= '</table></div>';
		}

		// Redeem button
		if ( $redeem_url ) {
			$html .= sprintf(
				'<div class="reward-redeem-wrapper" style="text-align: center; margin-top: 24px;">
					<a href="%s" class="component-button button-redeem" style="display: inline-block; background: #667eea; color: white; padding: 14px 32px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 16px;">Redeem Rewards</a>
				</div>',
				esc_url( $redeem_url )
			);
		}

		return $html;
	}

}