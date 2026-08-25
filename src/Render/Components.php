<?php
/**
 * Components
 *
 * @package     ArrayPress\RegisterEmails
 * @copyright   Copyright (c) 2026, ArrayPress Limited
 * @license     GPL2+
 * @since       2.0.0
 */

declare( strict_types=1 );

namespace ArrayPress\RegisterEmails\Render;

use ArrayPress\RegisterEmails\Interfaces\Component;
use ArrayPress\RegisterEmails\Utils\Runtime;

/**
 * The blocks an email can be built out of.
 *
 * One list. There used to be three, all hand-maintained and all required to
 * agree: a map of type to class, a list of which types were components at
 * all, and a table of each one's main argument. Adding a component meant
 * editing three places, and forgetting one gave a component that rendered as
 * nothing, or a tag whose callback output was ignored, or a string that went
 * into the wrong argument — none of which say anything, because an email
 * that is missing a block still sends.
 *
 * Now the classes are the list, and each says what its main argument is.
 */
final class Components {

	/**
	 * The components this library ships.
	 *
	 * Written out rather than found by scanning the directory: a class map is
	 * built at install time and a directory scan on every request is a
	 * hundred stat calls to learn something that cannot change.
	 *
	 * @var array<int, class-string<Component>>
	 */
	private const SHIPPED = [
		\ArrayPress\RegisterEmails\Components\ActivityLog::class,
		\ArrayPress\RegisterEmails\Components\Alert::class,
		\ArrayPress\RegisterEmails\Components\Button::class,
		\ArrayPress\RegisterEmails\Components\CodeBlock::class,
		\ArrayPress\RegisterEmails\Components\Coupon::class,
		\ArrayPress\RegisterEmails\Components\Divider::class,
		\ArrayPress\RegisterEmails\Components\DownloadsList::class,
		\ArrayPress\RegisterEmails\Components\EventDetails::class,
		\ArrayPress\RegisterEmails\Components\InfoBox::class,
		\ArrayPress\RegisterEmails\Components\KeyValueList::class,
		\ArrayPress\RegisterEmails\Components\OrderItems::class,
		\ArrayPress\RegisterEmails\Components\ProductList::class,
		\ArrayPress\RegisterEmails\Components\ProgressBar::class,
		\ArrayPress\RegisterEmails\Components\RawHtml::class,
		\ArrayPress\RegisterEmails\Components\RewardBalance::class,
		\ArrayPress\RegisterEmails\Components\ShippingTracker::class,
		\ArrayPress\RegisterEmails\Components\Spacer::class,
		\ArrayPress\RegisterEmails\Components\StatsGrid::class,
		\ArrayPress\RegisterEmails\Components\SubscriptionStatus::class,
		\ArrayPress\RegisterEmails\Components\Table::class,
		\ArrayPress\RegisterEmails\Components\Testimonial::class,
	];

	/**
	 * Type to class, worked out once.
	 *
	 * @var array<string, class-string<Component>>|null
	 */
	private static ?array $map = null;

	/**
	 * Every component, by type.
	 *
	 * @return array<string, class-string<Component>>
	 */
	public static function all(): array {
		if ( null === self::$map ) {
			$map = [];

			foreach ( self::SHIPPED as $component ) {
				$map[ self::type_of( $component ) ] = $component;
			}

			/**
			 * Add a component of your own.
			 *
			 * The class has to implement the Component interface: it is asked
			 * to render and asked what its main argument is called.
			 *
			 * @param array<string, class-string<Component>> $map Type to class.
			 *
			 * @since 2.0.0
			 */
			$map = (array) apply_filters( Runtime::hook( 'components' ), $map );

			self::$map = array_filter(
				$map,
				static fn( $candidate ): bool => is_string( $candidate ) && is_subclass_of( $candidate, Component::class )
			);
		}

		return self::$map;
	}

	/**
	 * The types there are.
	 *
	 * @return string[]
	 */
	public static function types(): array {
		return array_keys( self::all() );
	}

	/**
	 * Whether a type is a component.
	 *
	 * @param string $type The type.
	 *
	 * @return bool
	 */
	public static function has( string $type ): bool {
		return isset( self::all()[ $type ] );
	}

	/**
	 * Draw one.
	 *
	 * @param string               $type The type.
	 * @param array<string, mixed> $args What to draw.
	 *
	 * @return string
	 */
	public static function render( string $type, array $args = [] ): string {
		$class = self::all()[ $type ] ?? null;

		return null === $class ? '' : $class::render( $args );
	}

	/**
	 * What a component's main argument is called.
	 *
	 * @param string $type The type.
	 *
	 * @return string
	 */
	public static function primary_key( string $type ): string {
		$class = self::all()[ $type ] ?? null;

		return null === $class ? 'content' : $class::primary_key();
	}

	/**
	 * Forget the map. For tests, and for a filter added after first use.
	 *
	 * @return void
	 */
	public static function flush(): void {
		self::$map = null;
	}

	/**
	 * The type a component class answers to.
	 *
	 * `CodeBlock` is `code_block`, `Alert` is `alert`. Derived rather than
	 * declared, so a component cannot be registered under one name and
	 * documented under another.
	 *
	 * @param string $component The class.
	 *
	 * @return string
	 */
	private static function type_of( string $component ): string {
		$short = substr( (string) strrchr( '\\' . $component, '\\' ), 1 );

		return strtolower( (string) preg_replace( '/(?<!^)([A-Z])/', '_$1', $short ) );
	}
}
