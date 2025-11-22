<?php
/**
 * Button Component
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
 * Button Component
 *
 * Email-safe button with minimal customization.
 *
 * @since 1.0.0
 */
class Button extends Component {

	/**
	 * Valid alignments
	 *
	 * @since 1.0.0
	 * @var array
	 */
	private const VALID_ALIGNMENTS = [ 'left', 'center', 'right' ];

	/**
	 * Render button component
	 *
	 * @param array $args       {
	 *
	 * @type string $text       Button text (required)
	 * @type string $url        Button URL
	 * @type string $align      Alignment (left|center|right)
	 * @type string $background Background color (optional)
	 * @type string $color      Text color (optional)
	 * @type string $style      Style variant (primary|secondary|outline) (optional)
	 *                          }
	 *
	 * @return string Button HTML
	 * @since 1.0.0
	 */
	public static function render( array $args = [] ): string {
		$text       = $args['text'] ?? '';
		$url        = $args['url'] ?? '#';
		$align      = $args['align'] ?? 'left';
		$background = $args['background'] ?? '#667eea';
		$color      = $args['color'] ?? '#ffffff';
		$style      = $args['style'] ?? 'primary';

		if ( empty( $text ) ) {
			return '';
		}

		if ( ! in_array( $align, self::VALID_ALIGNMENTS, true ) ) {
			$align = 'left';
		}

		// Check if this is a white/outline style button
		$button_classes = 'component-button';
		if ( strtolower( $background ) === '#ffffff' || strtolower( $background ) === 'white' || strtolower( $background ) === '#fff' ) {
			$button_classes .= ' button-outline';
		} elseif ( $style === 'secondary' ) {
			$button_classes .= ' button-secondary';
		} else {
			$button_classes .= ' button-primary';
		}

		$button = sprintf(
			'<a href="%s" class="%s" style="display: inline-block; background: %s; color: %s; padding: 14px 28px; border-radius: 8px; text-decoration: none; font-weight: 600;">%s</a>',
			esc_url( $url ),
			esc_attr( $button_classes ),
			esc_attr( $background ),
			esc_attr( $color ),
			esc_html( $text )
		);

		if ( $align === 'center' ) {
			$alignment = 'text-align: center;';
		} elseif ( $align === 'right' ) {
			$alignment = 'text-align: right;';
		} else {
			$alignment = '';
		}

		return sprintf(
			'<div class="component component-button-wrapper button-align-%s" style="%s margin: 24px 0;">%s</div>',
			esc_attr( $align ),
			$alignment,
			$button
		);
	}

}