<?php
/**
 * Footer Component
 *
 * @package     ArrayPress\RegisterEmails
 * @copyright   Copyright (c) 2025, ArrayPress Limited
 * @license     GPL2+
 * @version     1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\RegisterEmails\Parts;

use ArrayPress\RegisterEmails\Abstracts\Component;

/**
 * Footer Component
 *
 * Renders footer with links, social links, and text.
 *
 * @since 1.0.0
 */
class Footer extends Component {

	/**
	 * Render footer component
	 *
	 * @param array $args         {
	 *
	 * @type string $text         Footer text
	 * @type array  $links        Regular footer links (Terms, Privacy, etc.)
	 * @type array  $social_links Social network => URL pairs
	 *                            }
	 *
	 * @return string Footer HTML
	 * @since 1.0.0
	 */
	public static function render( array $args = [] ): string {
		$text         = $args['text'] ?? '';
		$links        = $args['links'] ?? [];
		$social_links = $args['social_links'] ?? [];

		if ( empty( $text ) && empty( $links ) && empty( $social_links ) ) {
			return '';
		}

		$html = '<div style="margin-top: 48px; padding-top: 24px; border-top: 1px solid #e5e7eb; text-align: center; font-size: 14px; color: #6b7280;">';

		// Regular footer links (Terms • Privacy • Account)
		if ( ! empty( $links ) ) {
			$link_items = [];
			foreach ( $links as $label => $url ) {
				$url          = str_replace( '{site_url}', home_url(), $url );
				$link_items[] = sprintf(
					'<a href="%s" style="color: #6b7280; text-decoration: none;">%s</a>',
					esc_url( $url ),
					esc_html( $label )
				);
			}
			$html .= '<div style="margin-bottom: 16px;">' . implode( ' • ', $link_items ) . '</div>';
		}

		// Social links
		if ( ! empty( $social_links ) ) {
			$html .= '<div style="margin-bottom: 16px;">';
			foreach ( $social_links as $network => $url ) {
				$html .= sprintf(
					'<a href="%s" style="color: #6b7280; text-decoration: none; margin: 0 12px;">%s</a>',
					esc_url( $url ),
					esc_html( $network )
				);
			}
			$html .= '</div>';
		}

		// Footer text
		if ( ! empty( $text ) ) {
			$text = str_replace(
				[ '{year}', '{site_name}' ],
				[ date( 'Y' ), get_bloginfo( 'name' ) ],
				$text
			);
			$html .= '<p style="margin: 0;">' . wp_kses_post( $text ) . '</p>';
		}

		$html .= '</div>';

		return $html;
	}

}