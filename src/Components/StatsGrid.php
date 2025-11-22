<?php
/**
 * Stats Grid Component
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
 * Stats Grid Component
 *
 * Simple statistics display grid.
 *
 * @since 1.0.0
 */
class StatsGrid extends Component {

	/**
	 * Render stats grid component
	 *
	 * @param array $args  {
	 *
	 * @type array  $stats Stats array (required)
	 *                     }
	 *
	 * @return string Stats grid HTML
	 * @since 1.0.0
	 */
	public static function render( array $args = [] ): string {
		$stats = $args['stats'] ?? $args;

		if ( empty( $stats ) || ! is_array( $stats ) ) {
			return '';
		}

		$html = '<div class="component component-stats-grid" style="margin: 20px 0;">';
		$html .= '<table class="stats-table" width="100%" cellpadding="0" cellspacing="0"><tr>';

		$count   = 0;
		$columns = 3;

		foreach ( $stats as $stat ) {
			if ( $count > 0 && $count % $columns === 0 ) {
				$html .= '</tr><tr>';
			}

			$html .= '<td class="stat-cell" width="33%" style="padding: 20px; text-align: center; border: 1px solid #e5e7eb;">';

			if ( isset( $stat['value'] ) ) {
				$html .= sprintf( '<div class="stat-value" style="font-size: 32px; font-weight: bold; color: #111;">%s</div>', esc_html( $stat['value'] ) );
			}

			if ( isset( $stat['label'] ) ) {
				$html .= sprintf( '<div class="stat-label" style="font-size: 14px; color: #6b7280; margin-top: 8px;">%s</div>', esc_html( $stat['label'] ) );
			}

			$html .= '</td>';
			$count ++;
		}

		$html .= '</tr></table></div>';

		return $html;
	}

}