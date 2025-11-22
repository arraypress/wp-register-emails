<?php
/**
 * Raw HTML Component
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
 * Raw HTML Component
 *
 * Escape hatch for injecting custom HTML when other components don't fit.
 *
 * @since 1.0.0
 */
class RawHtml extends Component {

	/**
	 * Render raw HTML component
	 *
	 * @param array $args    {
	 *
	 * @type string $content Raw HTML content
	 * @type bool   $escape  Whether to escape HTML (default false)
	 *                       }
	 *
	 * @return string HTML content
	 * @since 1.0.0
	 */
	public static function render( array $args = [] ): string {
		$content = $args['content'] ?? '';
		$escape  = $args['escape'] ?? false;

		if ( empty( $content ) ) {
			return '';
		}

		// If escape is true, show the HTML as text (useful for showing code examples)
		if ( $escape ) {
			return sprintf(
				'<pre class="component component-raw-html raw-html-escaped" style="background: #f3f4f6; padding: 16px; border-radius: 6px; overflow-x: auto; font-family: monospace; font-size: 14px; color: #374151;">%s</pre>',
				esc_html( $content )
			);
		}

		// Wrap raw HTML in a div with class for potential targeting
		// Allow specific HTML tags and attributes for email safety
		return sprintf(
			'<div class="component component-raw-html">%s</div>',
			wp_kses_post( $content )
		);
	}

}