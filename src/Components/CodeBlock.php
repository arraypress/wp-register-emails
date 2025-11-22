<?php
/**
 * Code Block Component
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
 * Code Block Component
 *
 * Display code or license keys.
 *
 * @since 1.0.0
 */
class CodeBlock extends Component {

	/**
	 * Render code block component
	 *
	 * @param array $args       {
	 *
	 * @type string $code       Code to display (required)
	 * @type string $label      Optional label above code
	 * @type string $background Background color
	 * @type string $color      Text color
	 *                          }
	 *
	 * @return string Code block HTML
	 * @since 1.0.0
	 */
	public static function render( array $args = [] ): string {
		$code       = $args['code'] ?? '';
		$label      = $args['label'] ?? '';
		$background = $args['background'] ?? '#f3f4f6';
		$color      = $args['color'] ?? '#111';

		if ( empty( $code ) ) {
			return '';
		}

		$html = '<div class="component component-code-block" style="margin: 20px 0; text-align: center;">';

		if ( ! empty( $label ) ) {
			$html .= sprintf(
				'<div class="code-label" style="font-size: 14px; color: #6b7280; margin-bottom: 8px;">%s</div>',
				esc_html( $label )
			);
		}

		$html .= sprintf(
			'<div class="code-content" style="background: %s; padding: 16px 24px; border-radius: 8px; display: inline-block; font-family: monospace; font-size: 18px; font-weight: 600; letter-spacing: 1px; color: %s; border: 2px dashed #d1d5db;">%s</div>',
			esc_attr( $background ),
			esc_attr( $color ),
			esc_html( $code )
		);

		$html .= '</div>';

		return $html;
	}

}