<?php
/**
 * Email Template Helper Functions
 *
 * Global functions for email template and tag registration and sending.
 * These functions serve as the primary API for the library.
 *
 * @package     ArrayPress\EmailTemplates
 * @copyright   Copyright (c) 2025, ArrayPress Limited
 * @license     GPL2+
 * @version     1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

use ArrayPress\RegisterEmails\Registry\Registry;
use ArrayPress\RegisterEmails\Managers\TemplateManager;

if ( ! function_exists( 'register_email_tag' ) ) {
	/**
	 * Register an email tag
	 *
	 * Registers a tag that can be used in email templates for dynamic content replacement.
	 * Supports multiple tag types including text, HTML, callbacks, and component types.
	 *
	 * @param string  $prefix      Tag prefix/namespace (primary group)
	 * @param string  $name        Tag name (will be used as {tag_name} in templates)
	 * @param array   $config      {
	 *                             Tag configuration array
	 *
	 * @type string   $type        Tag type: 'text', 'html', 'callback', 'button', 'table',
	 *                             'alert', 'divider', 'heading', 'key_value_list',
	 *                             'product_list', 'downloads_list'. Required.
	 * @type string   $label       Human-readable label for UI. Required.
	 * @type string   $description Description for help text. Optional.
	 * @type callable $callback    Main callback that returns content based on type. Required for most types.
	 * @type array    $options     Component-specific options. Optional.
	 * @type array    $groups      Additional groups this tag belongs to. Optional.
	 * @type mixed    $preview     Preview value or callback for testing. Optional.
	 *                             }
	 *
	 * @return bool True on success, false on failure
	 * @since 1.0.0
	 */
	function register_email_tag( string $prefix, string $name, array $config = [] ): bool {
		try {
			$registry = Registry::get_instance();
			$registry->register_tag( $prefix, $name, $config );

			return true;
		} catch ( Exception $e ) {
			error_log( sprintf(
				'Failed to register email tag "%s_%s": %s',
				$prefix,
				$name,
				$e->getMessage()
			) );

			return false;
		}
	}
}

if ( ! function_exists( 'register_email_template' ) ) {
	/**
	 * Register an email template
	 *
	 * Registers an email template that defines structure, available tags, and settings.
	 *
	 * @param string  $prefix            Template prefix/namespace
	 * @param string  $template_name     Template identifier
	 * @param array   $config            {
	 *                                   Template configuration array
	 *
	 * @type string   $name              Human-readable name. Optional.
	 * @type string   $description       Template description. Optional.
	 * @type string   $template          Visual template from Email class. Default 'default'.
	 * @type string   $tag_group         Tag group to use. Default same as prefix.
	 * @type callable $settings_callback Callback to retrieve user settings. Optional.
	 * @type array    $default_settings  Default subject/message if no settings. Optional.
	 * @type array    $visual_config     Visual configuration (colors, logo, etc). Optional.
	 * @type string   $capability        Required capability to send. Default 'manage_options'.
	 *                                   }
	 *
	 * @return bool True on success, false on failure
	 * @since 1.0.0
	 */
	function register_email_template( string $prefix, string $template_name, array $config = [] ): bool {
		try {
			$registry = Registry::get_instance();
			$registry->register_template( $prefix, $template_name, $config );

			return true;
		} catch ( Exception $e ) {
			error_log( sprintf(
				'Failed to register email template "%s_%s": %s',
				$prefix,
				$template_name,
				$e->getMessage()
			) );

			return false;
		}
	}
}

if ( ! function_exists( 'send_email_template' ) ) {
	/**
	 * Send an email using a registered template
	 *
	 * Sends an email using a registered template with tag processing and visual formatting.
	 *
	 * @param string $prefix        Template prefix
	 * @param string $template_name Template name
	 * @param array  $args          {
	 *                              Email arguments
	 *
	 * @type string  $to            Email recipient(s). Required.
	 * @type string  $subject       Email subject. Optional, uses template default.
	 * @type string  $message       Email message. Optional, uses template default.
	 * @type mixed   $data          Data object/array for tag processing. Optional.
	 * @type array   $attachments   File attachments. Optional.
	 * @type array   $headers       Additional headers. Optional.
	 * @type array   $replacements  Additional placeholder replacements. Optional.
	 * @type string  $context       Send context: 'system', 'manual', 'test'. Default 'system'.
	 *                              }
	 *
	 * @return bool True if sent successfully, false otherwise
	 * @since 1.0.0
	 */
	function send_email_template( string $prefix, string $template_name, array $args = [] ): bool {
		$registry = Registry::get_instance();
		$manager  = new TemplateManager( $registry );

		return $manager->send( $prefix, $template_name, $args );
	}
}

if ( ! function_exists( 'preview_email_template' ) ) {
	/**
	 * Preview an email template with sample data
	 *
	 * Generates a preview of an email template using either provided data or preview callbacks.
	 *
	 * @param string     $prefix        Template prefix
	 * @param string     $template_name Template name
	 * @param mixed|null $sample_data   Sample data for preview. Optional.
	 *
	 * @return string|false HTML preview or false on error
	 * @since 1.0.0
	 */
	function preview_email_template( string $prefix, string $template_name, $sample_data = null ) {
		$registry = Registry::get_instance();
		$manager  = new TemplateManager( $registry );

		$args = $sample_data ? [ 'data' => $sample_data ] : [ 'preview' => true ];

		return $manager->render( $prefix, $template_name, $args );
	}
}

if ( ! function_exists( 'get_email_preview_html' ) ) {
	/**
	 * Get complete preview HTML with wrapper
	 *
	 * @param string $prefix        Template prefix
	 * @param string $template_name Template name
	 * @param mixed  $sample_data   Sample data for preview
	 *
	 * @return string Complete HTML document ready for display
	 * @since 1.0.0
	 */
	function get_email_preview_html( string $prefix, string $template_name, $sample_data = null ): string {
		$registry = Registry::get_instance();
		$manager  = new TemplateManager( $registry );

		return $manager->get_preview_html( $prefix, $template_name, $sample_data );
	}
}

if ( ! function_exists( 'get_email_template_tags' ) ) {
	/**
	 * Get available tags for a template or tag group
	 *
	 * Returns information about all tags available for a specific template or tag group.
	 *
	 * @param string      $prefix        Tag group prefix or template prefix
	 * @param string|null $template_name Optional template name for template-specific tags
	 *
	 * @return array Array of tag information with keys: name, label, description, type, preview
	 * @since 1.0.0
	 */
	function get_email_template_tags( string $prefix, ?string $template_name = null ): array {
		$registry = Registry::get_instance();

		return $registry->get_template_tags( $prefix, $template_name );
	}
}