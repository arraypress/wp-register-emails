<?php
/**
 * WordPress Email Builder
 *
 * Core email composition and sending functionality for WordPress with
 * template support and placeholder replacement.
 *
 * @package     ArrayPress\RegisterEmails
 * @copyright   Copyright (c) 2025, ArrayPress Limited
 * @license     GPL2+
 * @version     1.0.0
 * @author      David Sherlock
 */

namespace ArrayPress\RegisterEmails\Core;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

use ArrayPress\RegisterEmails\Parts;
use ArrayPress\RegisterEmails\Templates;

/**
 * Main Email class for composing and sending emails
 *
 * Provides a fluent interface for building and sending HTML emails with
 * template support and placeholder replacement.
 *
 * @since 1.0.0
 */
class Email {

	// =========================================================================
	// PROPERTIES
	// =========================================================================

	/**
	 * Email recipient(s)
	 *
	 * @since 1.0.0
	 * @var string|array
	 */
	private $to = '';

	/**
	 * Email subject line
	 *
	 * @since 1.0.0
	 * @var string
	 */
	private string $subject = '';

	/**
	 * Email content/body
	 *
	 * @since 1.0.0
	 * @var string
	 */
	private string $content = '';

	/**
	 * Email headers
	 *
	 * @since 1.0.0
	 * @var array
	 */
	private array $headers = [];

	/**
	 * File attachments
	 *
	 * @since 1.0.0
	 * @var array
	 */
	private array $attachments = [];

	/**
	 * Template name to use
	 *
	 * @since 1.0.0
	 * @var string
	 */
	private string $template;

	/**
	 * Placeholder replacements
	 *
	 * @since 1.0.0
	 * @var array
	 */
	private array $replacements = [];

	/**
	 * Email context for filters
	 *
	 * @since 1.0.0
	 * @var array
	 */
	private array $context = [];

	/**
	 * From email address
	 *
	 * @since 1.0.0
	 * @var string
	 */
	private string $from_email = '';

	/**
	 * From name
	 *
	 * @since 1.0.0
	 * @var string
	 */
	private string $from_name = '';

	/**
	 * Hook prefix for filters and actions
	 *
	 * @since 1.0.0
	 * @var string
	 */
	private string $hook_prefix = 'register_emails';

	/**
	 * Email visual config
	 *
	 * @since 1.0.0
	 * @var array
	 */
	private array $visual_config = [];

	// =========================================================================
	// CONSTRUCTOR & INITIALIZATION
	// =========================================================================

	/**
	 * Constructor
	 *
	 * @param array $config       {
	 *                            Optional. Configuration array.
	 *
	 * @type string $from_name    From name. Default site name.
	 * @type string $from_email   From email. Default admin email.
	 * @type string $template     Default template. Default 'default'.
	 * @type string $hook_prefix  Prefix for filters/actions. Default 'register_emails'.
	 *                            }
	 *
	 * @since 1.0.0
	 */
	public function __construct( array $config = [] ) {
		// Store visual config
		$this->visual_config = $config;

		// Set defaults
		$this->from_email = $config['from_email'] ?? get_option( 'admin_email' );
		$this->from_name  = $config['from_name'] ?? get_bloginfo( 'name' );
		$this->template   = $config['template'] ?? 'default';

		if ( ! empty( $config['hook_prefix'] ) ) {
			$this->hook_prefix = sanitize_key( $config['hook_prefix'] );
		}

		$this->setup_headers();
	}

	/**
	 * Setup default headers
	 *
	 * @return void
	 * @since  1.0.0
	 * @access private
	 */
	private function setup_headers(): void {
		// Set from header
		$this->set_from( $this->from_email, $this->from_name );

		// Set content type
		$this->headers[] = 'Content-Type: text/html; charset=' . get_bloginfo( 'charset' );
	}

	// =========================================================================
	// CONTENT METHODS
	// =========================================================================

	/**
	 * Set email recipient(s)
	 *
	 * @param string|array $to Single email address or array of addresses
	 *
	 * @return self
	 * @since 1.0.0
	 */
	public function to( $to ): self {
		$this->to = $to;

		return $this;
	}

	/**
	 * Set email subject
	 *
	 * @param string $subject Email subject line
	 *
	 * @return self
	 * @since 1.0.0
	 */
	public function subject( string $subject ): self {
		$this->subject = $subject;

		return $this;
	}

	/**
	 * Set email content
	 *
	 * @param string $content Email body content
	 *
	 * @return self
	 * @since 1.0.0
	 */
	public function content( string $content ): self {
		$this->content = $content;

		return $this;
	}

	/**
	 * Set email template
	 *
	 * @param string $template Template name
	 *
	 * @return self
	 * @since 1.0.0
	 */
	public function template( string $template ): self {
		$this->template = $template;

		return $this;
	}

	// =========================================================================
	// HEADER METHODS
	// =========================================================================

	/**
	 * Set from address and name
	 *
	 * @param string $email From email address
	 * @param string $name  From name
	 *
	 * @return self
	 * @since 1.0.0
	 */
	public function from( string $email, string $name = '' ): self {
		$this->set_from( $email, $name );

		return $this;
	}

	/**
	 * Set reply-to address
	 *
	 * @param string $email Reply-to email
	 * @param string $name  Reply-to name
	 *
	 * @return self
	 * @since 1.0.0
	 */
	public function reply_to( string $email, string $name = '' ): self {
		$reply_to = ! empty( $name )
			? sprintf( 'Reply-To: %s <%s>', $name, $email )
			: sprintf( 'Reply-To: %s', $email );

		$this->headers[] = $reply_to;

		return $this;
	}

	/**
	 * Add CC recipient
	 *
	 * @param string $email CC email address
	 *
	 * @return self
	 * @since 1.0.0
	 */
	public function cc( string $email ): self {
		$this->headers[] = sprintf( 'Cc: %s', $email );

		return $this;
	}

	/**
	 * Add BCC recipient
	 *
	 * @param string $email BCC email address
	 *
	 * @return self
	 * @since 1.0.0
	 */
	public function bcc( string $email ): self {
		$this->headers[] = sprintf( 'Bcc: %s', $email );

		return $this;
	}

	/**
	 * Add a custom email header
	 *
	 * @param string $header Complete header string
	 *
	 * @return self
	 * @since 1.0.0
	 */
	public function add_header( string $header ): self {
		$this->headers[] = $header;

		return $this;
	}

	// =========================================================================
	// ATTACHMENT METHODS
	// =========================================================================

	/**
	 * Add file attachment
	 *
	 * @param string $file Full path to file
	 *
	 * @return self
	 * @since 1.0.0
	 */
	public function attach( string $file ): self {
		if ( file_exists( $file ) ) {
			$this->attachments[] = $file;
		}

		return $this;
	}

	// =========================================================================
	// CONFIGURATION METHODS
	// =========================================================================

	/**
	 * Set placeholder replacements
	 *
	 * @param array $replacements Key-value pairs of placeholders and values
	 *
	 * @return self
	 * @since 1.0.0
	 */
	public function replacements( array $replacements ): self {
		$this->replacements = array_merge( $this->replacements, $replacements );

		return $this;
	}

	/**
	 * Set email context for filters
	 *
	 * @param array $context Context data
	 *
	 * @return self
	 * @since 1.0.0
	 */
	public function context( array $context ): self {
		$this->context = array_merge( $this->context, $context );

		return $this;
	}

	// =========================================================================
	// SENDING METHODS
	// =========================================================================

	/**
	 * Send the email
	 *
	 * @return bool True if sent successfully
	 * @since 1.0.0
	 */
	public function send(): bool {
		// Prepare email components
		$to      = $this->prepare_recipients();
		$subject = strtr( $this->subject, $this->replacements );
		$message = $this->prepare_message();

		// Filter email data before sending
		$email_data = apply_filters( $this->hook_prefix . '_before_send', [
			'to'          => $to,
			'subject'     => $subject,
			'message'     => $message,
			'headers'     => $this->headers,
			'attachments' => $this->attachments,
		], $this->context );

		// Send email
		$sent = wp_mail(
			$email_data['to'],
			$email_data['subject'],
			$email_data['message'],
			$email_data['headers'],
			$email_data['attachments']
		);

		// Fire action after sending
		do_action( $this->hook_prefix . '_after_send', $sent, $this->context );

		return $sent;
	}

	/**
	 * Get the built email HTML without sending
	 *
	 * @return string Complete email HTML
	 * @since 1.0.0
	 */
	public function get_html(): string {
		return $this->prepare_message();
	}

	// =========================================================================
	// PRIVATE HELPER METHODS
	// =========================================================================

	/**
	 * Set from header
	 *
	 * @param string $email From email
	 * @param string $name  From name
	 *
	 * @return void
	 * @since  1.0.0
	 * @access private
	 */
	private function set_from( string $email, string $name = '' ): void {
		// Remove any existing From headers
		$this->headers = array_filter( $this->headers, function ( $header ) {
			return stripos( $header, 'From:' ) !== 0;
		} );

		$from = ! empty( $name )
			? sprintf( 'From: %s <%s>', $name, $email )
			: sprintf( 'From: %s', $email );

		$this->headers[] = $from;
	}

	/**
	 * Prepare email recipients
	 *
	 * @return string|array Sanitized email address(es)
	 * @since  1.0.0
	 * @access private
	 */
	private function prepare_recipients() {
		if ( is_array( $this->to ) ) {
			return array_map( 'sanitize_email', $this->to );
		}

		return sanitize_email( $this->to );
	}

	/**
	 * Prepare email message with template and replacements
	 *
	 * @return string Prepared email message
	 * @since  1.0.0
	 * @access private
	 */
	private function prepare_message(): string {
		$template = Templates::get( $this->template );

		// Build visual replacements
		$visual_replacements = [];

		// Add colors from visual config if present
		if ( isset( $this->visual_config['colors'] ) ) {
			foreach ( $this->visual_config['colors'] as $key => $color ) {
				$visual_replacements[ '{color_' . $key . '}' ] = $color;
			}
		}

		// Logo
		if ( ! empty( $this->visual_config['logo'] ) ) {
			$visual_replacements['{logo}'] = Parts\Logo::render( [
				'url' => $this->visual_config['logo']
			] );
		} else {
			$visual_replacements['{logo}'] = '';
		}

		// Footer (handles all footer content)
		$footer_args = [];

		if ( isset( $this->visual_config['footer'] ) ) {
			$footer_args = [
				'text'         => $this->visual_config['footer']['text'] ?? '',
				'links'        => $this->visual_config['footer']['links'] ?? [],
				'social_links' => $this->visual_config['footer']['social_links'] ?? []
			];
		}

		if ( ! empty( array_filter( $footer_args ) ) ) {
			$visual_replacements['{footer}'] = Parts\Footer::render( $footer_args );
		} else {
			$visual_replacements['{footer}'] = '';
		}

		// Merge all replacements
		$all_replacements = array_merge( $visual_replacements, $this->replacements );

		// Replace content
		$message = str_replace( '{content}', $this->content, $template );

		// Replace all placeholders
		$message = strtr( $message, $all_replacements );

		if ( apply_filters( $this->hook_prefix . '_process_shortcodes', true, $this->context ) ) {
			$message = do_shortcode( $message );
		}

		return apply_filters( $this->hook_prefix . '_message', $message, $this->context );
	}

}