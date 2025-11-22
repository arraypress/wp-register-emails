<?php
/**
 * Activity Log Component
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
 * Activity Log Component
 *
 * Displays account activity or recent actions in a timeline format.
 *
 * @since 1.0.0
 */
class ActivityLog extends Component {

	/**
	 * Render activity log component
	 *
	 * @param array $args    {
	 *
	 * @type string $title   Section title (optional)
	 * @type array  $items   Array of activity items
	 * @type bool   $show_ip Show IP addresses (default false)
	 * @type int    $limit   Limit number of items shown (default all)
	 *                       }
	 *
	 * Each item in $items array should contain:
	 * - action: The action performed
	 * - time: When it occurred
	 * - ip: IP address (optional)
	 * - device: Device info (optional)
	 * - location: Location (optional)
	 * - type: Type for coloring (info|warning|error|success)
	 *
	 * @return string Activity log HTML
	 * @since 1.0.0
	 */
	public static function render( array $args = [] ): string {
		$title   = $args['title'] ?? 'Recent Activity';
		$items   = $args['items'] ?? [];
		$show_ip = $args['show_ip'] ?? false;
		$limit   = isset( $args['limit'] ) ? (int) $args['limit'] : 0;

		if ( empty( $items ) ) {
			return '';
		}

		// Apply limit if set
		if ( $limit > 0 && count( $items ) > $limit ) {
			$items = array_slice( $items, 0, $limit );
		}

		$html = '<div class="component component-activity-log" style="background: #ffffff; border: 1px solid #e5e7eb; border-radius: 8px; padding: 20px; margin: 24px 0;">';

		// Title
		if ( $title ) {
			$html .= sprintf(
				'<h3 class="component-title" style="margin: 0 0 20px; font-size: 16px; font-weight: 600; color: #111827;">%s</h3>',
				esc_html( $title )
			);
		}

		// Activity items
		foreach ( $items as $index => $item ) {
			$action   = $item['action'] ?? '';
			$time     = $item['time'] ?? '';
			$ip       = $item['ip'] ?? '';
			$device   = $item['device'] ?? '';
			$location = $item['location'] ?? '';
			$type     = $item['type'] ?? 'info';

			// Determine icon and color based on type
			$type_config = [
				'info'    => [ 'icon' => '🔵', 'color' => '#3b82f6' ],
				'warning' => [ 'icon' => '🟡', 'color' => '#f59e0b' ],
				'error'   => [ 'icon' => '🔴', 'color' => '#ef4444' ],
				'success' => [ 'icon' => '🟢', 'color' => '#10b981' ],
			];

			$config  = $type_config[ $type ] ?? $type_config['info'];
			$is_last = ( $index === count( $items ) - 1 );

			$html .= sprintf(
				'<div class="activity-item activity-item-%s %s" style="display: flex; padding: %s; %s">',
				$type,
				$is_last ? 'activity-item-last' : '',
				$is_last ? '12px 0 0' : '12px 0',
				$is_last ? '' : 'border-bottom: 1px solid #f3f4f6;'
			);

			// Icon column
			$html .= sprintf(
				'<div class="activity-icon" style="width: 24px; margin-right: 12px; font-size: 12px;">%s</div>',
				$config['icon']
			);

			// Content column
			$html .= '<div class="activity-content" style="flex: 1;">';

			// Action
			$html .= sprintf(
				'<div class="activity-action" style="font-size: 14px; color: #111827; font-weight: 500; margin-bottom: 4px;">%s</div>',
				esc_html( $action )
			);

			// Metadata row
			$meta_parts = [];

			if ( $time ) {
				$meta_parts[] = sprintf( '<span class="activity-time" style="color: #6b7280;">%s</span>', esc_html( $time ) );
			}

			if ( $device ) {
				$meta_parts[] = sprintf( '<span class="activity-device" style="color: #6b7280;">%s</span>', esc_html( $device ) );
			}

			if ( $location ) {
				$meta_parts[] = sprintf( '<span class="activity-location" style="color: #6b7280;">%s</span>', esc_html( $location ) );
			}

			if ( $show_ip && $ip ) {
				$meta_parts[] = sprintf( '<span class="activity-ip" style="color: #9ca3af; font-family: monospace; font-size: 12px;">%s</span>', esc_html( $ip ) );
			}

			if ( ! empty( $meta_parts ) ) {
				$html .= sprintf(
					'<div class="activity-meta" style="font-size: 12px;">%s</div>',
					implode( ' • ', $meta_parts )
				);
			}

			$html .= '</div></div>';
		}

		$html .= '</div>';

		return $html;
	}

}