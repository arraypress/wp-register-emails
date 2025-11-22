<?php
/**
 * Email Template Registry
 *
 * Centralized registry for managing email template and tag registrations.
 * Implements a singleton pattern to ensure consistent access across the application.
 *
 * @package     ArrayPress\EmailTemplates
 * @copyright   Copyright (c) 2025, ArrayPress Limited
 * @license     GPL2+
 * @version     1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\RegisterEmails\Registry;

use Exception;
use InvalidArgumentException;

/**
 * Class Registry
 *
 * Singleton registry for managing email templates and tags across the application.
 * Supports multiple tag groups and template associations.
 *
 * @since 1.0.0
 */
class Registry {

	/**
	 * Singleton instance
	 *
	 * @since 1.0.0
	 * @var self|null
	 */
	private static ?self $instance = null;

	/**
	 * Registered tags organized by prefix
	 *
	 * @since 1.0.0
	 * @var array<string, array<string, Tag>>
	 */
	private array $tags = [];

	/**
	 * Registered templates organized by prefix
	 *
	 * @since 1.0.0
	 * @var array<string, array<string, Template>>
	 */
	private array $templates = [];

	/**
	 * Private constructor
	 *
	 * @since 1.0.0
	 */
	private function __construct() {
	}

	/**
	 * Prevent cloning
	 *
	 * @return void
	 * @since 1.0.0
	 */
	private function __clone() {
	}

	/**
	 * Prevent unserialization
	 *
	 * @return void
	 * @throws Exception When attempting to unserialize
	 * @since 1.0.0
	 */
	public function __wakeup() {
		throw new Exception( "Cannot unserialize singleton" );
	}

	/**
	 * Get registry instance
	 *
	 * @return self Registry instance
	 * @since 1.0.0
	 */
	public static function get_instance(): self {
		if ( self::$instance === null ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Register an email tag
	 *
	 * Tags can be registered to multiple groups for reuse across templates.
	 *
	 * @param string $prefix Plugin/namespace prefix (primary group)
	 * @param string $name   Tag name
	 * @param array  $config Tag configuration including optional 'groups' array
	 *
	 * @return Tag Registered tag instance
	 * @throws InvalidArgumentException If tag already registered
	 * @since 1.0.0
	 */
	public function register_tag( string $prefix, string $name, array $config ): Tag {
		$prefix = sanitize_key( $prefix );
		$name   = sanitize_key( $name );

		// Initialize prefix array if needed
		if ( ! isset( $this->tags[ $prefix ] ) ) {
			$this->tags[ $prefix ] = [];
		}

		// Check if already registered in primary group
		if ( isset( $this->tags[ $prefix ][ $name ] ) ) {
			throw new InvalidArgumentException(
				sprintf( 'Tag "%s_%s" is already registered', $prefix, $name )
			);
		}

		// Create tag instance
		$tag = new Tag( $name, $config );

		// Register in primary group
		$this->tags[ $prefix ][ $name ] = $tag;

		// Register in additional groups if specified
		$groups = $tag->get_groups();
		foreach ( $groups as $group ) {
			$group = sanitize_key( $group );

			if ( ! isset( $this->tags[ $group ] ) ) {
				$this->tags[ $group ] = [];
			}

			// Use reference to same tag object
			$this->tags[ $group ][ $name ] = $tag;
		}

		/**
		 * Fires after a tag is registered
		 *
		 * @param Tag    $tag    The registered tag instance
		 * @param string $prefix The primary prefix used
		 * @param string $name   The tag name
		 * @param array  $groups Additional groups
		 *
		 * @since 1.0.0
		 */
		do_action( 'email_template_tag_registered', $tag, $prefix, $name, $groups );

		return $tag;
	}

	/**
	 * Get registered tags for a prefix/group
	 *
	 * @param string $prefix Plugin/namespace prefix or group name
	 *
	 * @return array<string, Tag> Array of tag name => Tag instance
	 * @since 1.0.0
	 */
	public function get_tags( string $prefix ): array {
		$prefix = sanitize_key( $prefix );

		return $this->tags[ $prefix ] ?? [];
	}

	/**
	 * Get a specific tag
	 *
	 * @param string $prefix Tag prefix/group
	 * @param string $name   Tag name
	 *
	 * @return Tag|null Tag instance or null if not found
	 * @since 1.0.0
	 */
	public function get_tag( string $prefix, string $name ): ?Tag {
		$prefix = sanitize_key( $prefix );
		$name   = sanitize_key( $name );

		return $this->tags[ $prefix ][ $name ] ?? null;
	}

	/**
	 * Check if a tag exists
	 *
	 * @param string $prefix Tag prefix/group
	 * @param string $name   Tag name
	 *
	 * @return bool True if tag exists
	 * @since 1.0.0
	 */
	public function has_tag( string $prefix, string $name ): bool {
		$prefix = sanitize_key( $prefix );
		$name   = sanitize_key( $name );

		return isset( $this->tags[ $prefix ][ $name ] );
	}

	/**
	 * Get all tags across all prefixes
	 *
	 * @return array<string, array<string, Tag>> All registered tags
	 * @since 1.0.0
	 */
	public function get_all_tags(): array {
		return $this->tags;
	}

	/**
	 * Register an email template
	 *
	 * @param string $prefix        Plugin/namespace prefix
	 * @param string $template_name Template identifier
	 * @param array  $config        Template configuration
	 *
	 * @return Template Registered template instance
	 * @throws InvalidArgumentException If template already registered
	 * @since 1.0.0
	 */
	public function register_template( string $prefix, string $template_name, array $config ): Template {
		$prefix        = sanitize_key( $prefix );
		$template_name = sanitize_key( $template_name );

		// Initialize prefix array if needed
		if ( ! isset( $this->templates[ $prefix ] ) ) {
			$this->templates[ $prefix ] = [];
		}

		// Check if already registered
		if ( isset( $this->templates[ $prefix ][ $template_name ] ) ) {
			throw new InvalidArgumentException(
				sprintf( 'Template "%s_%s" is already registered', $prefix, $template_name )
			);
		}

		// Default tag group to prefix if not specified
		if ( ! isset( $config['tag_group'] ) ) {
			$config['tag_group'] = $prefix;
		}

		// Create and store template
		$template                                     = new Template( $template_name, $config );
		$this->templates[ $prefix ][ $template_name ] = $template;

		/**
		 * Fires after a template is registered
		 *
		 * @param Template $template The registered template instance
		 * @param string   $prefix   The prefix used
		 * @param string   $name     The template name
		 *
		 * @since 1.0.0
		 */
		do_action( 'email_template_registered', $template, $prefix, $template_name );

		return $template;
	}

	/**
	 * Get registered templates for a prefix
	 *
	 * @param string $prefix Plugin/namespace prefix
	 *
	 * @return array<string, Template> Array of template name => Template instance
	 * @since 1.0.0
	 */
	public function get_templates( string $prefix ): array {
		$prefix = sanitize_key( $prefix );

		return $this->templates[ $prefix ] ?? [];
	}

	/**
	 * Get a specific template
	 *
	 * @param string $prefix        Template prefix
	 * @param string $template_name Template name
	 *
	 * @return Template|null Template instance or null if not found
	 * @since 1.0.0
	 */
	public function get_template( string $prefix, string $template_name ): ?Template {
		$prefix        = sanitize_key( $prefix );
		$template_name = sanitize_key( $template_name );

		return $this->templates[ $prefix ][ $template_name ] ?? null;
	}

	/**
	 * Check if a template exists
	 *
	 * @param string $prefix        Template prefix
	 * @param string $template_name Template name
	 *
	 * @return bool True if template exists
	 * @since 1.0.0
	 */
	public function has_template( string $prefix, string $template_name ): bool {
		$prefix        = sanitize_key( $prefix );
		$template_name = sanitize_key( $template_name );

		return isset( $this->templates[ $prefix ][ $template_name ] );
	}

	/**
	 * Get all templates across all prefixes
	 *
	 * @return array<string, array<string, Template>> All registered templates
	 * @since 1.0.0
	 */
	public function get_all_templates(): array {
		return $this->templates;
	}

	/**
	 * Get tags available for a specific template
	 *
	 * Returns all tags registered to the template's tag group.
	 *
	 * @param string      $prefix        Template prefix
	 * @param string|null $template_name Template name (null for all in prefix)
	 *
	 * @return array Array of tag information
	 * @since 1.0.0
	 */
	public function get_template_tags( string $prefix, ?string $template_name = null ): array {
		if ( $template_name !== null ) {
			$template = $this->get_template( $prefix, $template_name );
			if ( ! $template ) {
				return [];
			}

			$tag_group = $template->get_tag_group();
		} else {
			$tag_group = $prefix;
		}

		$tags = $this->get_tags( $tag_group );

		return array_map( function ( $tag ) {
			return [
				'name'        => $tag->get_name(),
				'label'       => $tag->get_label(),
				'description' => $tag->get_description(),
				'type'        => $tag->get_type(),
				'preview'     => $tag->get_preview(),
			];
		}, $tags );
	}

	/**
	 * Get all prefixes that have registered tags
	 *
	 * @return array List of prefixes with tags
	 * @since 1.0.0
	 */
	public function get_tag_prefixes(): array {
		return array_keys( $this->tags );
	}

	/**
	 * Get all prefixes that have registered templates
	 *
	 * @return array List of prefixes with templates
	 * @since 1.0.0
	 */
	public function get_template_prefixes(): array {
		return array_keys( $this->templates );
	}

	/**
	 * Reset the singleton instance
	 *
	 * @return void
	 * @internal For testing purposes only
	 * @since    1.0.0
	 */
	public static function reset(): void {
		self::$instance = null;
	}

}