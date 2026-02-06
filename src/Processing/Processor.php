<?php
/**
 * Email Tag Processor
 *
 * Processes email content by replacing tags with rendered content.
 *
 * @package     ArrayPress\EmailTemplates
 * @copyright   Copyright (c) 2025, ArrayPress Limited
 * @license     GPL2+
 * @version     1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\RegisterEmails\Processing;

use ArrayPress\RegisterEmails\Registry\Registry;
use Exception;

/**
 * Class Processor
 *
 * Handles the processing of email content by replacing registered tags
 * with their rendered output.
 *
 * @since 1.0.0
 */
class Processor {

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
	 * Process content with tags from a single group
	 *
	 * Replaces all {tag_name} placeholders with rendered content.
	 *
	 * @param string $content   Content containing tags
	 * @param string $tag_group Tag group prefix
	 * @param mixed  $data      Data for tag rendering
	 *
	 * @return string Processed content
	 * @since 1.0.0
	 */
	public function process( string $content, string $tag_group, $data ): string {
		return $this->process_groups( $content, [ $tag_group ], $data );
	}

	/**
	 * Process content with tags from multiple groups
	 *
	 * Merges tags from all groups (deduplicating by name) and replaces
	 * all {tag_name} placeholders with rendered content.
	 *
	 * @param string   $content    Content containing tags
	 * @param string[] $tag_groups Array of tag group prefixes
	 * @param mixed    $data       Data for tag rendering
	 *
	 * @return string Processed content
	 * @since 1.0.0
	 */
	public function process_groups( string $content, array $tag_groups, $data ): string {
		$tags = $this->registry->get_tags_for_groups( $tag_groups );

		if ( empty( $tags ) ) {
			return $content;
		}

		$group_key = implode( ',', $tag_groups );

		// Apply filter before processing
		$content = apply_filters( 'email_template_before_process_tags', $content, $group_key, $data );

		// Build all replacements first for better performance
		$replacements = [];

		foreach ( $tags as $tag_name => $tag ) {
			$placeholder = '{' . $tag_name . '}';

			if ( str_contains( $content, $placeholder ) ) {
				try {
					$replacement = $tag->render( $data );

					// Apply filter to individual tag replacement
					$replacement = apply_filters(
						"email_template_tag_{$tag_name}",
						$replacement,
						$data,
						$tag_groups
					);

					$replacements[ $placeholder ] = $replacement;

				} catch ( Exception $e ) {
					// Log error and use empty string
					error_log( sprintf(
						'[Email Template] Tag "%s" rendering failed: %s',
						$tag_name,
						$e->getMessage()
					) );

					$replacements[ $placeholder ] = '';
				}
			}
		}

		// Single pass replacement for efficiency
		if ( ! empty( $replacements ) ) {
			$content = strtr( $content, $replacements );
		}

		// Apply filter after processing
		return apply_filters( 'email_template_after_process_tags', $content, $group_key, $data );
	}

	/**
	 * Process content with preview/sample data from a single group
	 *
	 * Replaces tags with their preview content for testing.
	 *
	 * @param string $content   Content containing tags
	 * @param string $tag_group Tag group prefix
	 *
	 * @return string Processed content with preview data
	 * @since 1.0.0
	 */
	public function process_preview( string $content, string $tag_group ): string {
		return $this->process_preview_groups( $content, [ $tag_group ] );
	}

	/**
	 * Process content with preview/sample data from multiple groups
	 *
	 * Merges tags from all groups (deduplicating by name) and replaces
	 * all {tag_name} placeholders with preview content.
	 *
	 * @param string   $content    Content containing tags
	 * @param string[] $tag_groups Array of tag group prefixes
	 *
	 * @return string Processed content with preview data
	 * @since 1.0.0
	 */
	public function process_preview_groups( string $content, array $tag_groups ): string {
		$tags = $this->registry->get_tags_for_groups( $tag_groups );

		if ( empty( $tags ) ) {
			return $content;
		}

		$group_key = implode( ',', $tag_groups );

		// Apply filter before processing
		$content = apply_filters( 'email_template_before_process_preview', $content, $group_key );

		// Build all replacements first
		$replacements = [];

		foreach ( $tags as $tag_name => $tag ) {
			$placeholder = '{' . $tag_name . '}';

			if ( str_contains( $content, $placeholder ) ) {
				try {
					$replacement = $tag->get_preview();

					// Apply filter to preview replacement
					$replacement = apply_filters(
						"email_template_preview_{$tag_name}",
						$replacement,
						$tag_groups
					);

					$replacements[ $placeholder ] = $replacement;

				} catch ( Exception $e ) {
					// Log error and use placeholder text
					error_log( sprintf(
						'[Email Template] Tag "%s" preview failed: %s',
						$tag_name,
						$e->getMessage()
					) );

					$replacements[ $placeholder ] = '[' . $tag_name . ']';
				}
			}
		}

		// Single pass replacement
		if ( ! empty( $replacements ) ) {
			$content = strtr( $content, $replacements );
		}

		// Apply filter after processing
		return apply_filters( 'email_template_after_process_preview', $content, $group_key );
	}

	/**
	 * Process content with automatic mode detection
	 *
	 * Processes content using either real data or preview mode based on parameters.
	 *
	 * @param string   $content    Content containing tags
	 * @param string[] $tag_groups Tag group prefixes
	 * @param mixed    $data       Data for tag rendering (null for preview mode)
	 * @param bool     $preview    Force preview mode regardless of data
	 *
	 * @return string Processed content
	 * @since 1.0.0
	 */
	public function process_auto( string $content, array $tag_groups, $data = null, bool $preview = false ): string {
		if ( $preview && $data === null ) {
			return $this->process_preview_groups( $content, $tag_groups );
		}

		if ( $data !== null ) {
			return $this->process_groups( $content, $tag_groups, $data );
		}

		// No data and not preview mode - return content unchanged
		return $content;
	}

	/**
	 * Get all available tag placeholders for a group
	 *
	 * Returns an array of all placeholder strings for documentation or validation.
	 *
	 * @param string $tag_group Tag group prefix
	 *
	 * @return array Array of placeholder strings
	 * @since 1.0.0
	 */
	public function get_placeholders( string $tag_group ): array {
		$tags = $this->registry->get_tags( $tag_group );

		if ( empty( $tags ) ) {
			return [];
		}

		$placeholders = [];
		foreach ( $tags as $tag_name => $tag ) {
			$placeholders[] = '{' . $tag_name . '}';
		}

		return $placeholders;
	}

	/**
	 * Get all available tag placeholders across multiple groups
	 *
	 * @param string[] $tag_groups Tag group prefixes
	 *
	 * @return array Array of placeholder strings
	 * @since 1.0.0
	 */
	public function get_placeholders_for_groups( array $tag_groups ): array {
		$tags = $this->registry->get_tags_for_groups( $tag_groups );

		if ( empty( $tags ) ) {
			return [];
		}

		$placeholders = [];
		foreach ( $tags as $tag_name => $tag ) {
			$placeholders[] = '{' . $tag_name . '}';
		}

		return $placeholders;
	}

}