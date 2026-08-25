<?php
/**
 * Spacer Component
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
 * Spacer Component
 *
 * Adds precise vertical spacing between elements.
 *
 * @since 1.0.0
 */
class Spacer extends Component {

	/**
	 * Render spacer component
	 *
	 * @param array $args   {
	 *
	 * @type int    $height Height in pixels (default 20)
	 *                      }
	 *
	 * @return string Spacer HTML
	 * @since 1.0.0
	 */
	public static function render( array $args = [] ): string {
		$height = isset( $args['height'] ) ? (int) $args['height'] : 20;

		// Use table for better email client support
		return sprintf(
			'<table class="component component-spacer spacer-%d" width="100%%" cellpadding="0" cellspacing="0" border="0">
             <tr>
                <td style="font-size: 0; line-height: 0; height: %dpx;">&nbsp;</td>
             </tr>
          </table>',
			$height,
			$height
		);
	}


	/**
	 * What a tag's callback is understood to be giving when it returns a
	 * string rather than an array.
	 *
	 * Declared here rather than in a table somewhere else: this class is the
	 * only thing that knows, and a table of twenty-one of these is twenty-one
	 * chances to add a component and forget one of them.
	 *
	 * @return string
	 */
	public static function primary_key(): string {
		return 'height';
	}
}
