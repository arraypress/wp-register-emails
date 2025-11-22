<?php
/**
 * Alert Component
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
 * Alert Component
 *
 * Fixed-style alert boxes with semantic types.
 *
 * @since 1.0.0
 */
class Alert extends Component {

	/**
	 * Alert colors by type
	 *
	 * @since 1.0.0
	 * @var array
	 */
	private const COLORS = [
		'info'    => '#3b82f6',
		'success' => '#10b981',
		'warning' => '#f59e0b',
		'error'   => '#ef4444',
	];

	/**
	 * Render alert component
	 *
	 * @param array $args    {
	 *
	 * @type string $message Alert message (required)
	 * @type string $type    Type (info|success|warning|error)
	 *                       }
	 *
	 * @return string Alert HTML
	 * @since 1.0.0
	 */
	public static function render( array $args = [] ): string {
		$message = $args['message'] ?? '';
		$type    = $args['type'] ?? 'info';

		if ( empty( $message ) ) {
			return '';
		}

		$color    = self::COLORS[ $type ] ?? self::COLORS['info'];
		$bg_color = self::hex_to_rgba( $color, 0.1 );

		return sprintf(
			'<div class="component component-alert alert-%s" style="background: %s; border-left: 4px solid %s; color: #1f2937; padding: 16px; margin: 24px 0; border-radius: 4px;">%s</div>',
			esc_attr( $type ),
			$bg_color,
			$color,
			wp_kses_post( $message )
		);
	}

}