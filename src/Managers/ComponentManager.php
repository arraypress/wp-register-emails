<?php
/**
 * Components Facade
 *
 * @package     ArrayPress\RegisterEmails
 * @copyright   Copyright (c) 2025, ArrayPress Limited
 * @license     GPL2+
 * @version     1.0.0
 * @author      David Sherlock
 */

namespace ArrayPress\RegisterEmails\Managers;

use ArrayPress\RegisterEmails\Components;

/**
 * Components Facade
 *
 * Main entry point for component rendering. Maps component types to their classes.
 *
 * @since 1.0.0
 */
class ComponentManager {

	/**
	 * Component class map
	 *
	 * @since 1.0.0
	 * @var array
	 */
	private static array $component_map = [
		'alert'               => Components\Alert::class,
		'button'              => Components\Button::class,
		'code_block'          => Components\CodeBlock::class,
		'coupon'              => Components\Coupon::class,
		'divider'             => Components\Divider::class,
		'downloads_list'      => Components\DownloadsList::class,
		'info_box'            => Components\InfoBox::class,
		'key_value_list'      => Components\KeyValueList::class,
		'product_list'        => Components\ProductList::class,
		'progress_bar'        => Components\ProgressBar::class,
		'stats_grid'          => Components\StatsGrid::class,
		'table'               => Components\Table::class,
		'testimonial'         => Components\Testimonial::class,
		'order_items'         => Components\OrderItems::class,
		'spacer'              => Components\Spacer::class,
		'shipping_tracker'    => Components\ShippingTracker::class,
		'subscription_status' => Components\SubscriptionStatus::class,
		'event_details'       => Components\EventDetails::class,
		'activity_log'        => Components\ActivityLog::class,
		'reward_balance'      => Components\RewardBalance::class,
		'raw_html'            => Components\RawHtml::class
	];

	/**
	 * Call component directly by name
	 *
	 * @param string $component Component name
	 * @param array  $args      Component arguments
	 *
	 * @return string Component HTML or empty string
	 * @since 1.0.0
	 */
	public static function render( string $component, array $args = [] ): string {
		if ( ! isset( self::$component_map[ $component ] ) ) {
			return '';
		}

		$class = self::$component_map[ $component ];

		if ( class_exists( $class ) && method_exists( $class, 'render' ) ) {
			return $class::render( $args );
		}

		return '';
	}

	/**
	 * Check if component exists
	 *
	 * @param string $component Component name
	 *
	 * @return bool
	 * @since 1.0.0
	 */
	public static function exists( string $component ): bool {
		return isset( self::$component_map[ $component ] );
	}

	/**
	 * Get available components
	 *
	 * @return array Component names
	 * @since 1.0.0
	 */
	public static function get_available(): array {
		return array_keys( self::$component_map );
	}

}