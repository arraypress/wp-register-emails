<?php
/**
 * Email Template Configuration
 *
 * Represents a complete email template with its settings and tag associations.
 *
 * @package     ArrayPress\RegisterEmails
 * @copyright   Copyright (c) 2025, ArrayPress Limited
 * @license     GPL2+
 * @version     1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\RegisterEmails\Registry;

/**
 * Class Template
 *
 * Encapsulates email template configuration including visual settings,
 * tag groups, and content retrieval callbacks.
 *
 * @since 1.0.0
 */
class Template {

	/**
	 * Template identifier
	 *
	 * Unique identifier for this template within its prefix namespace.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	private string $name;

	/**
	 * Human-readable template name
	 *
	 * Display name shown in UI and admin interfaces.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	private string $label;

	/**
	 * Template description
	 *
	 * Help text describing the template's purpose and usage.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	private string $description;

	/**
	 * Visual template name
	 *
	 * References which HTML template to use (default, plain, etc).
	 *
	 * @since 1.0.0
	 * @var string
	 */
	private string $template;

	/**
	 * Tag group prefixes
	 *
	 * Determines which sets of registered tags are available for this template.
	 * Multiple groups allow combining tags from different registrations.
	 *
	 * @since 1.0.0
	 * @var string[]
	 */
	private array $tag_groups;

	/**
	 * Settings retrieval callback
	 *
	 * Optional callback to retrieve user-configured settings.
	 * Should return array with subject, message, enabled, title, subtitle.
	 *
	 * @since 1.0.0
	 * @var callable|null
	 */
	private $settings_callback;

	/**
	 * Default settings
	 *
	 * Fallback settings used when no user configuration exists.
	 *
	 * @since 1.0.0
	 * @var array {
	 * @type string $subject  Email subject line
	 * @type string $message  Email body content
	 * @type bool   $enabled  Whether template is enabled
	 * @type string $title    Email header title
	 * @type string $subtitle Email header subtitle
	 *                        }
	 */
	private array $default_settings;

	/**
	 * Visual configuration
	 *
	 * Template visual settings including colors, logo, and footer.
	 *
	 * @since 1.0.0
	 * @var array {
	 * @type string $logo         Logo URL
	 * @type int    $logo_height  Logo height in pixels
	 * @type string $footer_text  Footer text content
	 * @type array  $social_links Social media links
	 * @type array  $colors       Color scheme
	 *                            }
	 */
	private array $visual_config;

	/**
	 * Required capability
	 *
	 * WordPress capability required to send this template manually.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	private string $capability;

	/**
	 * Constructor
	 *
	 * @param string         $name              Template identifier
	 * @param array          $config            {
	 *                                          Template configuration array
	 *
	 * @type string          $label             Display name. Default: Humanized $name
	 * @type string          $description       Template description. Default: empty
	 * @type string          $template          Visual template. Default: 'default'
	 * @type string|string[] $tag_groups        Tag group(s). Default: empty. Accepts 'tag_group' (string) or
	 *       'tag_groups' (array).
	 * @type callable        $settings_callback Settings retrieval function. Default: null
	 * @type array           $default_settings  Default settings. Default: see property
	 * @type array           $visual_config     Visual configuration. Default: see get_default_visual_config()
	 * @type string          $capability        Required capability. Default: 'manage_options'
	 *                                          }
	 *
	 * @since 1.0.0
	 */
	public function __construct( string $name, array $config ) {
		$this->name = $name;

		// Set defaults
		$defaults = [
			'label'             => ucfirst( str_replace( '_', ' ', $name ) ),
			'description'       => '',
			'template'          => 'default',
			'tag_groups'        => [],
			'tag_group'         => '',
			'settings_callback' => null,
			'default_settings'  => [
				'subject'  => '',
				'message'  => '',
				'enabled'  => true,
				'title'    => '',
				'subtitle' => '',
			],
			'visual_config'     => [],
			'capability'        => 'manage_options',
		];

		$config = wp_parse_args( $config, $defaults );

		// Resolve tag_groups from either key, with backwards compatibility
		$tag_groups = $config['tag_groups'];

		if ( empty( $tag_groups ) && ! empty( $config['tag_group'] ) ) {
			$tag_groups = (array) $config['tag_group'];
		}

		// Merge visual config with defaults
		$config['visual_config'] = wp_parse_args( $config['visual_config'], $this->get_default_visual_config() );

		// Set properties
		$this->label             = $config['label'];
		$this->description       = $config['description'];
		$this->template          = $config['template'];
		$this->tag_groups        = array_values( array_unique( array_filter( array_map( 'sanitize_key', (array) $tag_groups ) ) ) );
		$this->settings_callback = $config['settings_callback'];
		$this->default_settings  = $config['default_settings'];
		$this->visual_config     = $config['visual_config'];
		$this->capability        = $config['capability'];
	}

	/**
	 * Get default visual configuration
	 *
	 * Provides smart defaults for visual settings including site icon as logo.
	 *
	 * @return array Default visual configuration
	 * @since  1.0.0
	 * @access private
	 */
	private function get_default_visual_config(): array {
		return [
			'logo'   => get_site_icon_url() ?: '',
			'footer' => [
				'text'         => '© {year} {site_name}. All rights reserved.',
				'links'        => [
					'Account' => '{site_url}/account',
					'Terms'   => '{site_url}/terms',
					'Privacy' => '{site_url}/privacy'
				],
				'social_links' => []
			],
			'colors' => [
				'primary' => '#667eea',
				'success' => '#10b981',
				'error'   => '#ef4444',
				'warning' => '#f59e0b',
				'info'    => '#3b82f6',
			]
		];
	}

	/**
	 * Get template settings
	 *
	 * Retrieves user-configured settings via callback or returns defaults.
	 * Settings are merged with defaults to ensure all keys exist.
	 *
	 * @return array Template settings with subject, message, enabled, title, subtitle
	 * @since 1.0.0
	 */
	public function get_settings(): array {
		if ( $this->settings_callback && is_callable( $this->settings_callback ) ) {
			$settings = call_user_func( $this->settings_callback );

			return wp_parse_args( $settings, $this->default_settings );
		}

		return $this->default_settings;
	}

	/**
	 * Get visual configuration
	 *
	 * Returns complete visual configuration including template name.
	 *
	 * @return array Visual configuration for rendering
	 * @since 1.0.0
	 */
	public function get_visual_config(): array {
		$config             = $this->visual_config;
		$config['template'] = $this->template;

		return $config;
	}

	/**
	 * Check if template is enabled
	 *
	 * @return bool True if template is enabled in settings
	 * @since 1.0.0
	 */
	public function is_enabled(): bool {
		$settings = $this->get_settings();

		return ! empty( $settings['enabled'] );
	}

	/**
	 * Check if user can send this template
	 *
	 * Validates user has required capability for manual/test sends.
	 *
	 * @param int|null $user_id User ID to check or null for current user
	 *
	 * @return bool True if user has required capability
	 * @since 1.0.0
	 */
	public function can_send( ?int $user_id = null ): bool {
		if ( $user_id === null ) {
			return current_user_can( $this->capability );
		}

		return user_can( $user_id, $this->capability );
	}

	/**
	 * Get template name
	 *
	 * @return string Template identifier
	 * @since 1.0.0
	 */
	public function get_name(): string {
		return $this->name;
	}

	/**
	 * Get template label
	 *
	 * @return string Human-readable template name
	 * @since 1.0.0
	 */
	public function get_label(): string {
		return $this->label;
	}

	/**
	 * Get template description
	 *
	 * @return string Template description text
	 * @since 1.0.0
	 */
	public function get_description(): string {
		return $this->description;
	}

	/**
	 * Get visual template name
	 *
	 * @return string Template identifier (default, plain, etc)
	 * @since 1.0.0
	 */
	public function get_template(): string {
		return $this->template;
	}

	/**
	 * Get tag groups
	 *
	 * @return string[] Tag group prefixes for this template
	 * @since 1.0.0
	 */
	public function get_tag_groups(): array {
		return $this->tag_groups;
	}

	/**
	 * Get tag group (backwards compatibility)
	 *
	 * Returns the first tag group for code that expects a single string.
	 *
	 * @return string First tag group prefix or empty string
	 * @since      1.0.0
	 * @deprecated Use get_tag_groups() instead
	 */
	public function get_tag_group(): string {
		return $this->tag_groups[0] ?? '';
	}

	/**
	 * Get required capability
	 *
	 * @return string WordPress capability required to send
	 * @since 1.0.0
	 */
	public function get_capability(): string {
		return $this->capability;
	}

}