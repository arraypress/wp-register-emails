<?php
/**
 * Email Templates Manager
 *
 * @package     ArrayPress\RegisterEmails
 * @copyright   Copyright (c) 2025, ArrayPress Limited
 * @license     GPL2+
 * @version     1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\RegisterEmails;

/**
 * Class Templates
 *
 * Manages email templates loaded from files with theme override support.
 *
 * @since 1.0.0
 */
class Templates {

	/**
	 * Base templates directory
	 *
	 * @since 1.0.0
	 * @var string
	 */
	private static string $templates_dir = '';

	/**
	 * Template metadata cache
	 *
	 * @since 1.0.0
	 * @var array
	 */
	private static array $template_meta = [];

	/**
	 * Get template HTML
	 *
	 * Retrieves template with theme override support.
	 *
	 * @param string $template Template name
	 *
	 * @return string Template HTML with placeholders
	 * @since 1.0.0
	 */
	public static function get( string $template = 'default' ): string {
		// Check theme override first
		$html = self::get_theme_template( $template );

		if ( ! $html ) {
			// Load from plugin templates directory
			$file = self::get_templates_dir() . $template . '.html';
			if ( file_exists( $file ) ) {
				$html = file_get_contents( $file );
			}
		}

		// Fall back to default if not found
		if ( ! $html ) {
			$default = self::get_templates_dir() . 'default.html';
			$html    = file_exists( $default ) ? file_get_contents( $default ) : self::fallback_template();
		}

		/**
		 * Filter template HTML
		 *
		 * @param string $html     Template HTML
		 * @param string $template Template name
		 *
		 * @since 1.0.0
		 */
		return apply_filters( 'register_emails_template', $html, $template );
	}

	/**
	 * Get templates directory path
	 *
	 * @return string Full path to templates directory
	 * @since  1.0.0
	 * @access private
	 */
	private static function get_templates_dir(): string {
		if ( empty( self::$templates_dir ) ) {
			self::$templates_dir = dirname( __FILE__, 2 ) . '/templates/';

		}

		return self::$templates_dir;
	}

	/**
	 * Set custom templates directory
	 *
	 * @param string $dir Templates directory path
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public static function set_templates_dir( string $dir ): void {
		self::$templates_dir = trailingslashit( $dir );
		self::$template_meta = []; // Clear cache
	}

	/**
	 * Get theme template override
	 *
	 * Checks for custom templates in theme directories.
	 *
	 * @param string $template Template name
	 *
	 * @return string|false Template HTML or false if not found
	 * @since  1.0.0
	 * @access private
	 */
	private static function get_theme_template( string $template ) {
		$locations = [
			get_stylesheet_directory() . '/register-emails/' . $template . '.html',
			get_template_directory() . '/register-emails/' . $template . '.html',
		];

		foreach ( $locations as $file ) {
			if ( file_exists( $file ) ) {
				return file_get_contents( $file );
			}
		}

		return false;
	}

	/**
	 * Get available templates
	 *
	 * Returns list of all available template names.
	 *
	 * @return array Template names
	 * @since 1.0.0
	 */
	public static function get_available_templates(): array {
		$templates = [];

		// Get plugin templates
		$dir = self::get_templates_dir();
		if ( is_dir( $dir ) ) {
			$files = glob( $dir . '*.html' );
			foreach ( $files as $file ) {
				$templates[] = basename( $file, '.html' );
			}
		}

		// Check for theme templates
		$theme_dir = get_stylesheet_directory() . '/register-emails/';
		if ( is_dir( $theme_dir ) ) {
			$files = glob( $theme_dir . '*.html' );
			foreach ( $files as $file ) {
				$name = basename( $file, '.html' );
				if ( ! in_array( $name, $templates, true ) ) {
					$templates[] = $name;
				}
			}
		}

		return $templates;
	}

	/**
	 * Get template metadata
	 *
	 * Extracts template description from HTML comments.
	 *
	 * @param string $template Template name
	 *
	 * @return array Template metadata
	 * @since 1.0.0
	 */
	public static function get_template_meta( string $template ): array {
		if ( isset( self::$template_meta[ $template ] ) ) {
			return self::$template_meta[ $template ];
		}

		$defaults = [
			'name'        => ucfirst( str_replace( [ '-', '_' ], ' ', $template ) ),
			'description' => '',
			'supports'    => [],
		];

		// Try to get template content
		$file = self::get_templates_dir() . $template . '.html';
		if ( ! file_exists( $file ) ) {
			return $defaults;
		}

		$content = file_get_contents( $file );

		// Extract metadata from HTML comments
		if ( preg_match( '/<!--\s*Template:\s*(.+?)\s*-->/i', $content, $matches ) ) {
			$defaults['name'] = trim( $matches[1] );
		}

		if ( preg_match( '/<!--\s*Description:\s*(.+?)\s*-->/i', $content, $matches ) ) {
			$defaults['description'] = trim( $matches[1] );
		}

		if ( preg_match( '/<!--\s*Supports:\s*(.+?)\s*-->/i', $content, $matches ) ) {
			$defaults['supports'] = array_map( 'trim', explode( ',', $matches[1] ) );
		}

		self::$template_meta[ $template ] = $defaults;

		return $defaults;
	}

	/**
	 * Validate template exists
	 *
	 * @param string $template Template name
	 *
	 * @return bool True if exists
	 * @since 1.0.0
	 */
	public static function template_exists( string $template ): bool {
		return in_array( $template, self::get_available_templates(), true );
	}

	// In ArrayPress\RegisterEmails\Templates

	/**
	 * Get template options for select fields.
	 *
	 * Returns an associative array of template slug => label
	 * suitable for use as dropdown options in settings UIs.
	 *
	 * @return array Associative array of template_slug => Template Label.
	 * @since 1.0.0
	 */
	public static function get_options(): array {
		$options   = [];
		$templates = self::get_available_templates();

		foreach ( $templates as $slug ) {
			$meta             = self::get_template_meta( $slug );
			$options[ $slug ] = $meta['name'];
		}

		if ( empty( $options ) ) {
			$options['default'] = __( 'Default', 'register-emails' );
		}

		/**
		 * Filter the available email template options.
		 *
		 * @param array $options Associative array of template_slug => label.
		 *
		 * @since 1.0.0
		 *
		 */
		return apply_filters( 'register_emails_template_options', $options );
	}

	/**
	 * Minimal fallback template
	 *
	 * Used when no template files are found.
	 *
	 * @return string Basic HTML template
	 * @since  1.0.0
	 * @access private
	 */
	private static function fallback_template(): string {
		return '<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{subject}</title>
</head>
<body style="margin: 0; padding: 20px; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, sans-serif;">
<div style="max-width: 600px; margin: 0 auto; background: white; padding: 40px;">
<h1 style="margin: 0 0 20px; color: #111;">{title}</h1>
{content}
{footer}
</div>
</body>
</html>';
	}

}