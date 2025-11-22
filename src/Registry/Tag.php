<?php
/**
 * Email Template Tag
 *
 * Represents a single email tag configuration with its rendering logic.
 * Supports direct component types and unified callback handling.
 *
 * @package     ArrayPress\RegisterEmails
 * @copyright   Copyright (c) 2025, ArrayPress Limited
 * @license     GPL2+
 * @version     1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\RegisterEmails\Registry;

use ArrayPress\RegisterEmails\Managers\ComponentManager;
use Exception;
use InvalidArgumentException;

/**
 * Class Tag
 *
 * Encapsulates email tag configuration and rendering logic.
 * Supports text, HTML, component types, and custom callbacks.
 *
 * @since 1.0.0
 */
class Tag {

	/**
	 * Component types that map to Components Manager
	 *
	 * @since 1.0.0
	 * @var array
	 */
	private const COMPONENT_TYPES = [
		'button',
		'table',
		'alert',
		'divider',
		'product_list',
		'downloads_list',
		'key_value_list',
		'info_box',
		'code_block',
		'coupon',
		'progress_bar',
		'testimonial',
		'stats_grid',
		'order_items',
		'spacer',
		'shipping_tracker',
		'subscription_status',
		'event_details',
		'activity_log',
		'reward_balance',
		'raw_html'
	];

	/**
	 * Tag name identifier
	 *
	 * Unique identifier for this tag within its context.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	private string $name;

	/**
	 * Tag type
	 *
	 * Determines rendering method: text, html, callback, or component type.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	private string $type;

	/**
	 * Human-readable label
	 *
	 * Display name for UI and documentation.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	private string $label;

	/**
	 * Tag description
	 *
	 * Help text describing tag purpose and usage.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	private string $description;

	/**
	 * Rendering callback
	 *
	 * Function to generate tag content dynamically.
	 *
	 * @since 1.0.0
	 * @var callable|null
	 */
	private $callback;

	/**
	 * Component rendering options
	 *
	 * Default options passed to component renderer.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	private array $options;

	/**
	 * Preview callback or value
	 *
	 * Generates sample output for testing and preview.
	 *
	 * @since 1.0.0
	 * @var mixed
	 */
	private $preview;

	/**
	 * Additional tag groups
	 *
	 * Groups this tag belongs to beyond primary registration.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	private array $groups;

	/**
	 * Constructor
	 *
	 * @param string  $name        Tag identifier
	 * @param array   $config      {
	 *                             Tag configuration array
	 *
	 * @type string   $type        Tag type (text|html|callback|component)
	 * @type string   $label       Display name
	 * @type string   $description Help text
	 * @type callable $callback    Rendering function
	 * @type array    $options     Component options
	 * @type mixed    $preview     Preview content or callback
	 * @type array    $groups      Additional groups
	 *                             }
	 *
	 * @throws InvalidArgumentException If configuration is invalid
	 * @since 1.0.0
	 */
	public function __construct( string $name, array $config ) {
		$this->name = $name;

		$defaults = [
			'type'        => 'text',
			'label'       => ucfirst( str_replace( '_', ' ', $name ) ),
			'description' => '',
			'callback'    => null,
			'options'     => [],
			'preview'     => null,
			'groups'      => [],
		];

		$config = wp_parse_args( $config, $defaults );

		// Validate type
		$valid_types = array_merge( [ 'text', 'html', 'callback' ], self::COMPONENT_TYPES );
		if ( ! in_array( $config['type'], $valid_types, true ) ) {
			throw new InvalidArgumentException(
				sprintf( 'Invalid type "%s" for tag "%s"', $config['type'], $name )
			);
		}

		$this->type        = $config['type'];
		$this->label       = $config['label'];
		$this->description = $config['description'];
		$this->callback    = $config['callback'];
		$this->options     = $config['options'];
		$this->preview     = $config['preview'];
		$this->groups      = (array) $config['groups'];

		// Validate callback requirement (divider doesn't need one)
		if ( $this->type !== 'divider' && ! $this->callback ) {
			throw new InvalidArgumentException(
				sprintf( 'Callback required for tag "%s" of type "%s"', $name, $this->type )
			);
		}
	}

	/**
	 * Render the tag with given data
	 *
	 * Processes the tag based on its type and returns rendered content.
	 *
	 * @param mixed $data Data object/array to process
	 *
	 * @return string Rendered tag content
	 * @since 1.0.0
	 */
	public function render( $data ): string {
		try {
			if ( in_array( $this->type, self::COMPONENT_TYPES, true ) ) {
				return $this->render_component( $data );
			}

			if ( $this->callback && is_callable( $this->callback ) ) {
				$content = call_user_func( $this->callback, $data );

				return is_string( $content ) ? $content : '';
			}

			return '';

		} catch ( Exception $e ) {
			error_log( sprintf(
				'Email tag "%s" rendering failed: %s',
				$this->name,
				$e->getMessage()
			) );

			return '';
		}
	}

	/**
	 * Render a component type tag
	 *
	 * Processes callback and delegates to Component Manager.
	 *
	 * @param mixed $data Data for component
	 *
	 * @return string Rendered component HTML
	 * @since  1.0.0
	 * @access private
	 */
	private function render_component( $data ): string {
		if ( ! ComponentManager::exists( $this->type ) ) {
			return '';
		}

		$component_data = $this->options;

		if ( $this->callback && is_callable( $this->callback ) ) {
			$result = call_user_func( $this->callback, $data );

			if ( $result === null || $result === false ) {
				return '';
			}

			if ( is_array( $result ) ) {
				$component_data = array_merge( $component_data, $result );
			} elseif ( is_string( $result ) ) {
				$key                    = $this->get_primary_key();
				$component_data[ $key ] = $result;
			}
		}

		return ComponentManager::render( $this->type, $component_data );
	}

	/**
	 * Get primary data key for component
	 *
	 * Maps component types to their primary data field.
	 *
	 * @return string Primary key name
	 * @since  1.0.0
	 * @access private
	 */
	private function get_primary_key(): string {
		$primary_keys = [
			'button'              => 'text',
			'alert'               => 'message',
			'code_block'          => 'code',
			'coupon'              => 'code',
			'testimonial'         => 'quote',
			'info_box'            => 'content',
			'product_list'        => 'products',
			'downloads_list'      => 'downloads',
			'key_value_list'      => 'items',
			'stats_grid'          => 'stats',
			'table'               => 'data',
			'order_items'         => 'items',
			'spacer'              => 'height',
			'shipping_tracker'    => 'tracking_number',
			'subscription_status' => 'plan',
			'event_details'       => 'title',
			'activity_log'        => 'items',
			'reward_balance'      => 'current_balance',
			'raw_html'            => 'content',
		];

		return $primary_keys[ $this->type ] ?? 'content';
	}

	/**
	 * Get preview/sample content
	 *
	 * Returns sample content for testing and preview purposes.
	 *
	 * @return string Preview content
	 * @since 1.0.0
	 */
	public function get_preview(): string {
		if ( $this->preview !== null ) {
			if ( is_callable( $this->preview ) ) {
				return call_user_func( $this->preview );
			}

			return (string) $this->preview;
		}

		if ( in_array( $this->type, self::COMPONENT_TYPES, true ) ) {
			return $this->get_component_preview();
		}

		return sprintf( '[%s]', $this->label );
	}

	/**
	 * Get component preview
	 *
	 * Generates default preview for component types.
	 *
	 * @return string Component preview HTML
	 * @since  1.0.0
	 * @access private
	 */
	private function get_component_preview(): string {
		$preview_data = [
			'button'              => [ 'text' => 'Preview Button', 'url' => '#' ],
			'alert'               => [ 'message' => 'This is a preview alert message', 'type' => 'info' ],
			'heading'             => [ 'text' => 'Preview Heading', 'level' => 2 ],
			'divider'             => [],
			'logo'                => [ 'url' => get_site_icon_url() ?: '' ],
			'footer'              => [ 'text' => '© {year} {site_name}' ],
			'table'               => [
				'data' => [
					[ 'Header 1', 'Header 2' ],
					[ 'Row 1 Col 1', 'Row 1 Col 2' ]
				]
			],
			'coupon'              => [
				'code'        => 'SAVE20',
				'description' => '20% off your next purchase',
				'expiry'      => 'Expires in 30 days'
			],
			'progress_bar'        => [
				'current' => 60,
				'total'   => 100,
				'label'   => 'Processing'
			],
			'testimonial'         => [
				'quote'  => 'This product exceeded expectations!',
				'author' => 'Jane Doe',
				'role'   => 'CEO, Example Corp'
			],
			'order_items'         => [
				'items'    => [
					[ 'name' => 'Sample Product', 'quantity' => 1, 'price' => 9900 ],
					[ 'name' => 'Another Item', 'quantity' => 2, 'price' => 4950 ],
				],
				'tax'      => 1485,
				'currency' => 'USD'
			],
			'product_list'        => [
				'products' => [
					[
						'name'        => 'Sample Product',
						'description' => 'Product description here',
						'price'       => '$99.00',
						'image'       => get_site_icon_url() ?: ''
					]
				]
			],
			'downloads_list'      => [
				'downloads' => [
					[
						'name'        => 'Download Package',
						'description' => 'Version 1.0',
						'files'       => [
							[ 'name' => 'file.zip', 'url' => '#', 'size' => '2.3 MB' ]
						]
					]
				]
			],
			'key_value_list'      => [
				'items' => [
					'Label 1' => 'Value 1',
					'Label 2' => 'Value 2',
					'Label 3' => 'Value 3'
				]
			],
			'info_box'            => [
				'title'   => 'Information',
				'content' => 'This is an informational message box.'
			],
			'code_block'          => [
				'label' => 'Your Code',
				'code'  => 'ABCD-EFGH-IJKL'
			],
			'stats_grid'          => [
				'stats' => [
					[ 'value' => '100+', 'label' => 'Users' ],
					[ 'value' => '99%', 'label' => 'Uptime' ],
					[ 'value' => '24/7', 'label' => 'Support' ]
				]
			],
			'spacer'              => [
				'height' => 40
			],
			'shipping_tracker'    => [
				'carrier'            => 'FedEx',
				'tracking_number'    => '1234567890',
				'status'             => 'In Transit',
				'estimated_delivery' => 'Tomorrow',
				'steps'              => [
					[ 'label' => 'Order Placed', 'completed' => true ],
					[ 'label' => 'Shipped', 'completed' => true ],
					[ 'label' => 'In Transit', 'completed' => false ],
					[ 'label' => 'Delivered', 'completed' => false ],
				]
			],
			'subscription_status' => [
				'plan'              => 'Premium Plan',
				'status'            => 'Active',
				'amount'            => 9900,
				'currency'          => 'USD',
				'interval'          => 'month',
				'next_billing_date' => date( 'F j, Y', strtotime( '+30 days' ) )
			],
			'event_details'       => [
				'title'    => 'Team Meeting',
				'date'     => date( 'F j, Y', strtotime( '+7 days' ) ),
				'time'     => '2:00 PM EST',
				'duration' => '1 hour',
				'location' => 'Zoom Meeting Room',
				'join_url' => '#'
			],
			'activity_log'        => [
				'items' => [
					[ 'action' => 'Login from new device', 'time' => '2 hours ago', 'type' => 'info' ],
					[ 'action' => 'Password changed', 'time' => '3 days ago', 'type' => 'warning' ],
					[ 'action' => 'Profile updated', 'time' => '1 week ago', 'type' => 'success' ]
				]
			],
			'reward_balance'      => [
				'current_balance' => 1250,
				'currency_label'  => 'points',
				'recent_activity' => [
					[ 'description' => 'Purchase bonus', 'amount' => '+100', 'date' => '2 days ago' ],
					[ 'description' => 'Redeemed reward', 'amount' => '-500', 'date' => '1 week ago' ]
				]
			],
			'raw_html'            => [
				'content' => '<p style="color: #666;">Custom HTML content preview</p>'
			]
		];

		$data = array_merge( $this->options, $preview_data[ $this->type ] ?? [] );

		return ComponentManager::render( $this->type, $data );
	}

	/**
	 * Get tag name
	 *
	 * @return string Tag identifier
	 * @since 1.0.0
	 */
	public function get_name(): string {
		return $this->name;
	}

	/**
	 * Get tag type
	 *
	 * @return string Tag type
	 * @since 1.0.0
	 */
	public function get_type(): string {
		return $this->type;
	}

	/**
	 * Get tag label
	 *
	 * @return string Human-readable label
	 * @since 1.0.0
	 */
	public function get_label(): string {
		return $this->label;
	}

	/**
	 * Get tag description
	 *
	 * @return string Tag description
	 * @since 1.0.0
	 */
	public function get_description(): string {
		return $this->description;
	}

	/**
	 * Get additional groups
	 *
	 * @return array Group names
	 * @since 1.0.0
	 */
	public function get_groups(): array {
		return $this->groups;
	}

	/**
	 * Check if tag is component type
	 *
	 * @return bool True if component type
	 * @since 1.0.0
	 */
	public function is_component(): bool {
		return in_array( $this->type, self::COMPONENT_TYPES, true );
	}

	/**
	 * Get component options
	 *
	 * @return array Component options
	 * @since 1.0.0
	 */
	public function get_options(): array {
		return $this->options;
	}

	/**
	 * Check if tag has callback
	 *
	 * @return bool True if it has callback
	 * @since 1.0.0
	 */
	public function has_callback(): bool {
		return $this->callback !== null && is_callable( $this->callback );
	}

}