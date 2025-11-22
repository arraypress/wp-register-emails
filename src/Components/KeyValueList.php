<?php
/**
 * Key-Value List Component
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
 * Key-Value List Component
 *
 * Simple definition list for key-value pairs.
 *
 * @since 1.0.0
 */
class KeyValueList extends Component {

	/**
	 * Render key-value list component
	 *
	 * @param array $args  {
	 *
	 * @type array  $items Key-value pairs (required)
	 *                     }
	 *
	 * @return string Key-value list HTML
	 * @since 1.0.0
	 */
	public static function render( array $args = [] ): string {
		$items = $args['items'] ?? $args;

		if ( empty( $items ) || ! is_array( $items ) ) {
			return '';
		}

		$html = '<div class="component component-key-value-list" style="margin: 24px 0;">';

		foreach ( $items as $key => $value ) {
			$html .= sprintf(
				'<div class="kv-item" style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #f3f4f6;">
                <span class="kv-key" style="color: #6b7280;">%s</span>
                <strong class="kv-value" style="color: #111827;">%s</strong>
             </div>',
				esc_html( (string) $key ),
				esc_html( (string) $value )
			);
		}

		$html .= '</div>';

		return $html;
	}

}