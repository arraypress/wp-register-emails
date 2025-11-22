<?php
/**
 * Divider Component
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
 * Divider Component
 *
 * Simple horizontal rule for content separation.
 *
 * @since 1.0.0
 */
class Divider extends Component {

	/**
	 * Render divider component
	 *
	 * @param array $args Not used, kept for consistency
	 *
	 * @return string Divider HTML
	 * @since 1.0.0
	 */
	public static function render( array $args = [] ): string {
		return '<hr class="component component-divider" style="border: 0; border-top: 1px solid #e5e7eb; margin: 32px 0;">';
	}

}