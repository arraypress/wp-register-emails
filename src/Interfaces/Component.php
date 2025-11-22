<?php
/**
 * Component Interface
 *
 * @package     ArrayPress\RegisterEmails
 * @copyright   Copyright (c) 2025, ArrayPress Limited
 * @license     GPL2+
 * @version     1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\RegisterEmails\Interfaces;

/**
 * ComponentInterface
 *
 * Contract for all email components.
 *
 * @since 1.0.0
 */
interface Component {

	/**
	 * Render the component
	 *
	 * @return string Component HTML
	 * @since 1.0.0
	 */
	public static function render(): string;

}