<?php
/**
 * Downloads List Component
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
 * Downloads List Component
 *
 * Product-style download list with thumbnails and file links.
 *
 * @since 1.0.0
 */
class DownloadsList extends Component {

	/**
	 * Render downloads list component
	 *
	 * @param array $args      {
	 *
	 * @type array  $downloads Array of download products with files
	 *                         }
	 *
	 * @return string Downloads list HTML
	 * @since 1.0.0
	 */
	public static function render( array $args = [] ): string {
		$downloads = $args['downloads'] ?? $args;

		if ( empty( $downloads ) || ! is_array( $downloads ) ) {
			return '';
		}

		$html = '<div class="component component-downloads-list" style="background: #f9fafb; border-radius: 8px; padding: 20px; margin: 24px 0;">';

		$total = count( $downloads );
		$index = 0;

		foreach ( $downloads as $product ) {
			$index ++;
			$is_last = ( $index === $total );

			// Only add border if not the last item AND there's more than one item
			$border_style = ( ! $is_last && $total > 1 ) ? 'border-bottom: 1px solid #e5e7eb;' : '';

			$html .= sprintf( '<div class="download-item %s" style="%s padding: 16px 0;">',
				$is_last ? 'download-item-last' : '',
				$border_style
			);

			if ( ! empty( $product['name'] ) ) {
				$html .= sprintf( '<strong class="download-name" style="font-size: 15px; color: #111827;">%s</strong>', esc_html( $product['name'] ) );
			}

			if ( ! empty( $product['description'] ) ) {
				$html .= sprintf( '<div class="download-description" style="color: #6b7280; font-size: 13px; margin: 4px 0 8px;">%s</div>', esc_html( $product['description'] ) );
			}

			// Files list
			if ( ! empty( $product['files'] ) && is_array( $product['files'] ) ) {
				foreach ( $product['files'] as $file ) {
					$name = $file['name'] ?? 'Download';
					$url  = $file['url'] ?? '#';
					$size = ! empty( $file['size'] ) ? ' (' . esc_html( $file['size'] ) . ')' : '';

					$html .= sprintf(
						'<div class="download-file" style="margin: 4px 0;">
                      <a href="%s" class="download-link" style="color: #2563eb; text-decoration: none; font-size: 14px;">%s%s</a>
                   </div>',
						esc_url( $url ),
						esc_html( $name ),
						$size
					);
				}
			}

			$html .= '</div>';
		}

		$html .= '</div>';

		return $html;
	}

}