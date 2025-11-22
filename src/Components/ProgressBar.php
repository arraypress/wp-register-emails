<?php
/**
 * Progress Bar Component
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
 * Progress Bar Component
 *
 * Simple progress indicator.
 *
 * @since 1.0.0
 */
class ProgressBar extends Component {

	/**
	 * Render progress bar component
	 *
	 * @param array $args    {
	 *
	 * @type int    $current Current value (required)
	 * @type int    $total   Total value (required)
	 * @type string $label   Optional label
	 * @type string $color   Progress bar color (optional, default: #667eea)
	 *                       }
	 *
	 * @return string Progress bar HTML
	 * @since 1.0.0
	 */
	public static function render( array $args = [] ): string {
		$current = (int) ( $args['current'] ?? 0 );
		$total   = (int) ( $args['total'] ?? 100 );
		$label   = $args['label'] ?? '';
		$color   = $args['color'] ?? '#667eea';

		if ( $total <= 0 ) {
			return '';
		}

		$percentage = min( 100, round( ( $current / $total ) * 100 ) );

		$html = '<div class="component component-progress-bar" style="margin: 20px 0;">';

		if ( ! empty( $label ) ) {
			$html .= sprintf(
				'<div class="progress-label" style="font-size: 14px; color: #374151; margin-bottom: 8px;">%s</div>',
				esc_html( $label )
			);
		}

		$html .= '<div class="progress-track" style="background: #e5e7eb; border-radius: 9999px; height: 24px; overflow: hidden;">';
		$html .= sprintf(
			'<div class="progress-fill" style="background: %s; height: 100%%; width: %d%%; border-radius: 9999px;"></div>',
			esc_attr( $color ),
			$percentage
		);
		$html .= '</div>';

		$html .= sprintf(
			'<div class="progress-percentage" style="text-align: right; font-size: 12px; color: #6b7280; margin-top: 4px;">%d%%</div>',
			$percentage
		);

		$html .= '</div>';

		return $html;
	}

}