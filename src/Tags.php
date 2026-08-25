<?php
/**
 * Tags
 *
 * @package     ArrayPress\RegisterEmails
 * @copyright   Copyright (c) 2026, ArrayPress Limited
 * @license     GPL2+
 * @since       2.0.0
 */

declare( strict_types=1 );

namespace ArrayPress\RegisterEmails;

/**
 * Every merge tag anybody has registered, grouped by who registered it.
 *
 * This library owns the registry. That is worth stating because two other
 * things in the set touch merge tags and neither of them owns any:
 *
 * - **wp-field-kit's email editor** draws the chooser. It is handed a list of
 *   tags to show and knows nothing about what any of them mean. `for_editor()`
 *   below produces exactly the shape it takes, so the seam is one method
 *   rather than two shapes each side hopes the other agrees about.
 *
 * - **edd-register-email-tags** registers into Easy Digital Downloads' own
 *   tag system, which is EDD's and predates all of this. It is an adapter,
 *   not a second registry.
 *
 * A group is usually a plugin — `shop`, `crm` — and an email says which
 * groups its tags come from. Several groups can be used at once, because an
 * order confirmation wants the shop's tags and the site's.
 */
final class Tags {

	/**
	 * The tags, by group and name.
	 *
	 * @var array<string, array<string, Tag>>
	 */
	private static array $tags = [];

	/**
	 * Register a tag.
	 *
	 * @param string               $group  Whose it is.
	 * @param string               $name   Its name, without braces.
	 * @param array<string, mixed> $config Its configuration.
	 *
	 * @return Tag|null Null when it has no name.
	 */
	public static function add( string $group, string $name, array $config ): ?Tag {
		$group = sanitize_key( $group );
		$name  = sanitize_key( trim( $name, '{} ' ) );

		if ( '' === $group || '' === $name ) {
			return null;
		}

		$tag = new Tag( $name, $config );

		self::$tags[ $group ][ $name ] = $tag;

		return $tag;
	}

	/**
	 * One group's tags.
	 *
	 * @param string $group The group.
	 *
	 * @return array<string, Tag>
	 */
	public static function group( string $group ): array {
		return self::$tags[ sanitize_key( $group ) ] ?? [];
	}

	/**
	 * Several groups' tags, as one set.
	 *
	 * A later group wins a name a earlier one already used, which is the
	 * useful way round: a plugin's own `{site_name}` overrides the general
	 * one rather than being shadowed by it.
	 *
	 * @param string[] $groups The groups.
	 *
	 * @return array<string, Tag>
	 */
	public static function groups( array $groups ): array {
		$tags = [];

		foreach ( $groups as $group ) {
			$tags = array_merge( $tags, self::group( (string) $group ) );
		}

		return $tags;
	}

	/**
	 * One tag.
	 *
	 * @param string $group The group.
	 * @param string $name  Its name.
	 *
	 * @return Tag|null
	 */
	public static function get( string $group, string $name ): ?Tag {
		return self::$tags[ sanitize_key( $group ) ][ sanitize_key( trim( $name, '{} ' ) ) ] ?? null;
	}

	/**
	 * Whether a tag is registered.
	 *
	 * @param string $group The group.
	 * @param string $name  Its name.
	 *
	 * @return bool
	 */
	public static function has( string $group, string $name ): bool {
		return null !== self::get( $group, $name );
	}

	/**
	 * The groups there are.
	 *
	 * @return string[]
	 */
	public static function all_groups(): array {
		return array_keys( self::$tags );
	}

	/**
	 * The tags of some groups, in the shape the field kit's editor takes.
	 *
	 *     'body' => [
	 *         'type' => 'email_editor',
	 *         'tags' => email_tags_for_editor( [ 'shop' ] ),
	 *     ],
	 *
	 * @param string[] $groups The groups.
	 *
	 * @return array<int, array{name: string, tag: string, description: string}>
	 */
	public static function for_editor( array $groups ): array {
		return array_values(
			array_map(
				static fn( Tag $tag ): array => $tag->for_editor(),
				self::groups( $groups )
			)
		);
	}

	/**
	 * Forget a group, or all of them.
	 *
	 * @param string $group The group, or empty for all.
	 *
	 * @return void
	 */
	public static function forget( string $group = '' ): void {
		if ( '' === $group ) {
			self::$tags = [];

			return;
		}

		unset( self::$tags[ sanitize_key( $group ) ] );
	}
}
