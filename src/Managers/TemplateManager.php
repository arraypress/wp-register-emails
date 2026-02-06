<?php
/**
 * Template Manager
 *
 * Handles template rendering and email sending with centralized logic.
 *
 * @package     ArrayPress\EmailTemplates
 * @copyright   Copyright (c) 2025, ArrayPress Limited
 * @license     GPL2+
 * @version     1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\RegisterEmails\Managers;

use ArrayPress\RegisterEmails\Core\Email;
use ArrayPress\RegisterEmails\Processing\Processor;
use ArrayPress\RegisterEmails\Registry\Registry;
use Exception;

/**
 * Class TemplateManager
 *
 * Centralizes template processing logic to avoid duplication between
 * send and preview functions.
 *
 * @since 1.0.0
 */
class TemplateManager {

	/**
	 * Registry instance
	 *
	 * @since 1.0.0
	 * @var Registry
	 */
	private Registry $registry;

	/**
	 * Constructor
	 *
	 * @param Registry $registry Registry instance
	 *
	 * @since 1.0.0
	 */
	public function __construct( Registry $registry ) {
		$this->registry = $registry;
	}

	/**
	 * Render a template with data
	 *
	 * Processes the template and returns the complete HTML without sending.
	 *
	 * @param string $prefix        Template prefix
	 * @param string $template_name Template name
	 * @param array  $args          {
	 *                              Rendering arguments
	 *
	 * @type string  $subject       Email subject (optional, uses template default)
	 * @type string  $message       Email message (optional, uses template default)
	 * @type mixed   $data          Data object/array for tag processing
	 * @type array   $replacements  Additional replacements
	 * @type bool    $preview       Whether this is a preview (uses preview data)
	 *                              }
	 *
	 * @return string|false Rendered HTML or false on error
	 * @since 1.0.0
	 */
	public function render( string $prefix, string $template_name, array $args = [] ) {
		try {
			$template = $this->registry->get_template( $prefix, $template_name );

			if ( ! $template ) {
				error_log( sprintf(
					'[Email Template] Template not found: %s_%s',
					$prefix,
					$template_name
				) );

				return false;
			}

			// Process template content
			$processed = $this->process_template_content( $template, $args );

			if ( $processed === false ) {
				return false;
			}

			// Create Email instance with visual config and hook prefix
			$config                = $template->get_visual_config();
			$config['hook_prefix'] = $prefix;
			$email                 = new Email( $config );

			// Set content
			$email->subject( $processed['subject'] );
			$email->content( $processed['message'] );

			// Add all replacements
			$email->replacements( $processed['replacements'] );

			// Add subject to replacements for HTML title tag
			$email->replacements( [ '{subject}' => strip_tags( $processed['subject'] ) ] );

			// Additional replacements if provided
			if ( ! empty( $args['replacements'] ) ) {
				$email->replacements( $args['replacements'] );
			}

			// Return the complete HTML
			return $email->get_html();

		} catch ( Exception $e ) {
			error_log( sprintf(
				'[Email Template] Exception in render: %s',
				$e->getMessage()
			) );

			return false;
		}
	}

	/**
	 * Send an email using a template
	 *
	 * @param string $prefix        Template prefix
	 * @param string $template_name Template name
	 * @param array  $args          {
	 *                              Email arguments
	 *
	 * @type string  $to            Email recipient (required)
	 * @type string  $subject       Email subject (optional, uses template default)
	 * @type string  $message       Email message (optional, uses template default)
	 * @type mixed   $data          Data object/array for tag processing
	 * @type array   $attachments   File attachments
	 * @type array   $headers       Additional headers
	 * @type array   $replacements  Additional replacements
	 * @type string  $context       Send context: 'system', 'manual', 'test' (default: 'system')
	 *                              }
	 *
	 * @return bool True if sent successfully
	 * @since 1.0.0
	 */
	public function send( string $prefix, string $template_name, array $args = [] ): bool {
		try {
			// Validate recipient
			if ( empty( $args['to'] ) ) {
				error_log( '[Email Template] No recipient specified' );

				return false;
			}

			// Get template
			$template = $this->registry->get_template( $prefix, $template_name );

			if ( ! $template ) {
				error_log( sprintf(
					'[Email Template] Template not found: %s_%s',
					$prefix,
					$template_name
				) );

				return false;
			}

			// Check if enabled
			if ( ! $template->is_enabled() ) {
				if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
					error_log( '[Email Template] Template is disabled' );
				}

				return false;
			}

			// Check capability for manual/test sends
			$context = $args['context'] ?? 'system';
			if ( in_array( $context, [ 'manual', 'test' ], true ) ) {
				if ( ! $template->can_send() ) {
					error_log( sprintf(
						'[Email Template] User lacks capability for %s send. Required: %s',
						$context,
						$template->get_capability()
					) );

					return false;
				}
			}

			// Process template content
			$processed = $this->process_template_content( $template, $args );

			if ( $processed === false ) {
				return false;
			}

			// Create Email instance with visual config and hook prefix
			$config                = $template->get_visual_config();
			$config['hook_prefix'] = $prefix;
			$email                 = new Email( $config );

			// Set recipient and content
			$email->to( $args['to'] );
			$email->subject( $processed['subject'] );
			$email->content( $processed['message'] );

			// Add all replacements
			$email->replacements( $processed['replacements'] );

			// Add subject to replacements for HTML title tag
			$email->replacements( [ '{subject}' => strip_tags( $processed['subject'] ) ] );

			// Additional replacements if provided
			if ( ! empty( $args['replacements'] ) ) {
				$email->replacements( $args['replacements'] );
			}

			// Add attachments if provided
			if ( ! empty( $args['attachments'] ) ) {
				foreach ( (array) $args['attachments'] as $attachment ) {
					$email->attach( $attachment );
				}
			}

			// Add additional headers if provided
			if ( ! empty( $args['headers'] ) ) {
				foreach ( (array) $args['headers'] as $header ) {
					$email->add_header( $header );
				}
			}

			// Apply filters before sending
			$email = apply_filters( 'email_template_before_send', $email, $template, $args );
			$email = apply_filters( "email_template_{$prefix}_{$template_name}_before_send", $email, $args );

			// Send the email
			$sent = $email->send();

			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( sprintf(
					'[Email Template] %s send %s_%s: %s',
					ucfirst( $context ),
					$prefix,
					$template_name,
					$sent ? 'SUCCESS' : 'FAILED'
				) );
			}

			// Apply actions after sending
			do_action( 'email_template_after_send', $sent, $template, $args );
			do_action( "email_template_{$prefix}_{$template_name}_after_send", $sent, $args );

			return $sent;

		} catch ( Exception $e ) {
			error_log( sprintf(
				'[Email Template] Exception in send: %s',
				$e->getMessage()
			) );

			return false;
		}
	}

	/**
	 * Process template content with replacements and tags
	 *
	 * @param object $template Template instance
	 * @param array  $args     Processing arguments
	 *
	 * @return array|false Processed content array or false on error
	 * @since  1.0.0
	 * @access private
	 */
	private function process_template_content( $template, array $args = [] ) {
		// Get settings with defaults
		$settings = $template->get_settings();

		// Build default replacements
		$default_replacements = $this->get_default_replacements();

		// Apply replacements to ALL settings fields (title, subtitle, subject, message)
		foreach ( [ 'subject', 'message', 'title', 'subtitle' ] as $field ) {
			if ( isset( $settings[ $field ] ) ) {
				$settings[ $field ] = strtr( $settings[ $field ], $default_replacements );
			}
		}

		// Add the processed title and subtitle to replacements
		$default_replacements['{title}']    = $settings['title'] ?? '';
		$default_replacements['{subtitle}'] = $settings['subtitle'] ?? '';

		// Determine final subject and message
		$subject = $args['subject'] ?? $settings['subject'] ?? '';
		$message = $args['message'] ?? $settings['message'] ?? '';

		// Get tag groups from template
		$tag_groups = $template->get_tag_groups();

		// Process tags if data provided or in preview mode
		$processor = new Processor( $this->registry );

		if ( ! empty( $args['data'] ) ) {
			$subject = $processor->process_groups( $subject, $tag_groups, $args['data'] );
			$message = $processor->process_groups( $message, $tag_groups, $args['data'] );
		} elseif ( ! empty( $args['preview'] ) ) {
			$subject = $processor->process_preview_groups( $subject, $tag_groups );
			$message = $processor->process_preview_groups( $message, $tag_groups );
		}

		return [
			'subject'      => $subject,
			'message'      => $message,
			'replacements' => $default_replacements
		];
	}

	/**
	 * Get preview HTML
	 *
	 * Returns the rendered email template for preview purposes. Accepts either
	 * content overrides (subject/message/title/subtitle from the editor) which
	 * keep preview mode active for tag processing, or real data (an order object)
	 * which disables preview mode and processes tags with real values.
	 *
	 * @param string     $prefix        Template prefix
	 * @param string     $template_name Template name
	 * @param array|null $overrides     Content overrides or real data object.
	 *                                  Arrays with only string values and keys matching
	 *                                  'subject', 'message', 'title', or 'subtitle' are
	 *                                  treated as content overrides (preview mode stays on).
	 *                                  All other values are treated as real data for tag
	 *                                  processing (preview mode is disabled).
	 *
	 * @return string Complete HTML document
	 * @since 1.0.0
	 */
	public function get_preview_html( string $prefix, string $template_name, $overrides = null ): string {
		$args = [ 'preview' => true ];

		if ( $overrides !== null ) {
			// Check if this is content overrides (from the editor UI) vs real data
			$override_keys = [ 'subject', 'message', 'title', 'subtitle' ];

			if ( is_array( $overrides ) && ! empty( $overrides ) && $this->is_content_overrides( $overrides, $override_keys ) ) {
				// Content overrides: merge directly into args, keep preview mode
				foreach ( $override_keys as $key ) {
					if ( isset( $overrides[ $key ] ) && $overrides[ $key ] !== '' ) {
						$args[ $key ] = $overrides[ $key ];
					}
				}
			} else {
				// Real data object: disable preview, process with real values
				$args['data'] = $overrides;
				unset( $args['preview'] );
			}
		}

		$html = $this->render( $prefix, $template_name, $args );

		return $html === false ? '' : $html;
	}

	/**
	 * Check if an array contains only content override keys.
	 *
	 * Returns true if every key in the array matches one of the allowed
	 * override keys, indicating this is an editor content override rather
	 * than a real data object for tag processing.
	 *
	 * @param array $data           The array to check.
	 * @param array $override_keys  Allowed override key names.
	 *
	 * @return bool True if all keys match override keys.
	 * @since  1.0.0
	 * @access private
	 */
	private function is_content_overrides( array $data, array $override_keys ): bool {
		foreach ( array_keys( $data ) as $key ) {
			if ( ! in_array( $key, $override_keys, true ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Get default replacements
	 *
	 * Builds the default placeholder replacements that are always available.
	 * Does not include title/subtitle as those are processed separately.
	 *
	 * @return array Default replacements
	 * @since  1.0.0
	 * @access private
	 */
	private function get_default_replacements(): array {
		return [
			'{site_name}'   => get_bloginfo( 'name' ),
			'{site_url}'    => home_url(),
			'{admin_email}' => get_option( 'admin_email' ),
			'{year}'        => date( 'Y' ),
			'{date}'        => current_time( get_option( 'date_format' ) ),
			'{time}'        => current_time( get_option( 'time_format' ) ),
		];
	}

}