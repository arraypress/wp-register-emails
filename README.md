# WordPress Email Registration System

A declarative email system for WordPress that combines dynamic tags, component-based templates, and a simple registration API. Build professional transactional emails with 21+ components and automatic tag processing.

## Features

* **Tag-Based System**: Register dynamic placeholders that get replaced with real data
* **21+ Email Components**: Buttons, tables, invoices, shipping trackers, subscription status, and more
* **7 Professional Templates**: Default, invoice, notification, dark mode, and more
* **Smart Currency Handling**: Automatic formatting for all Stripe currencies
* **Theme Override Support**: Customize templates via your WordPress theme

## Installation

```bash
composer require arraypress/wp-register-emails
```

## Quick Start

### 1. Register Tags (Dynamic Content)

```php
// Text replacement
register_email_tag( 'shop', 'customer_name', [
	'type'     => 'text',
	'label'    => 'Customer Name',
	'callback' => fn( $order ) => $order->billing_name
] );

// Button component
register_email_tag( 'shop', 'view_order_btn', [
	'type'     => 'button',
	'label'    => 'View Order Button',
	'callback' => fn( $order ) => [
		'text' => 'View Order #' . $order->id,
		'url'  => 'https://example.com/order/' . $order->id
	]
] );

// Invoice table with currency
register_email_tag( 'shop', 'invoice', [
	'type'     => 'order_items',
	'callback' => fn( $order ) => [
		'items'    => [
			[ 'name' => 'Premium Plugin', 'quantity' => 1, 'price' => 9900 ]
		],
		'tax'      => 990,
		'shipping' => 500,
		'currency' => 'USD'
	]
] );
```

### 2. Register Templates

```php
register_email_template( 'shop', 'order_confirmation', [
	'label'    => 'Order Confirmation',
	'subject'  => 'Order #{order_id} Confirmed',
	'template' => 'invoice',  // Visual template
	'message'  => '
        <p>Hi {customer_name},</p>
        <p>Thank you for your order!</p>
        {invoice}
        {view_order_btn}
    '
] );
```

### 3. Send Emails

```php
send_email_template( 'shop', 'order_confirmation', [
	'to'   => 'customer@example.com',
	'data' => $order
] );
```

## Available Components

### Commerce & Orders
```php
// Invoice with totals
register_email_tag( 'shop', 'invoice', [
	'type'     => 'order_items',
	'callback' => fn( $order ) => [
		'items'    => [ ... ],
		'currency' => 'USD',
		'tax'      => 1000,
		'discount' => 500
	]
] );

// Shipping tracker
register_email_tag( 'shop', 'tracking', [
	'type'     => 'shipping_tracker',
	'callback' => fn( $order ) => [
		'carrier'         => 'FedEx',
		'tracking_number' => '123456789',
		'status'          => 'In Transit',
		'steps'           => [
			[ 'label' => 'Shipped', 'completed' => true ],
			[ 'label' => 'In Transit', 'completed' => false ]
		]
	]
] );

// Subscription status
register_email_tag( 'shop', 'subscription', [
	'type'     => 'subscription_status',
	'callback' => fn( $user ) => [
		'plan'              => 'Premium',
		'status'            => 'Active',
		'amount'            => 9900,
		'currency'          => 'USD',
		'next_billing_date' => '2025-02-01'
	]
] );
```

### Content & Display
```php
// Alert boxes
register_email_tag( 'system', 'warning', [
	'type'     => 'alert',
	'callback' => fn() => [
		'message' => 'Payment method expires soon',
		'type'    => 'warning'  // success|error|warning|info
	]
] );

// Progress bars
register_email_tag( 'system', 'progress', [
	'type'     => 'progress_bar',
	'callback' => fn( $data ) => [
		'current' => $data->completed,
		'total'   => $data->total,
		'label'   => 'Processing'
	]
] );

// Stats grid
register_email_tag( 'metrics', 'stats', [
	'type'     => 'stats_grid',
	'callback' => fn() => [
		'stats' => [
			[ 'value' => '1,234', 'label' => 'Orders' ],
			[ 'value' => '$12,345', 'label' => 'Revenue' ],
			[ 'value' => '98%', 'label' => 'Satisfaction' ]
		]
	]
] );
```

## WooCommerce Example

```php
add_action( 'init', function () {
	// Register tags
	register_email_tag( 'woo', 'order_number', [
		'type'     => 'text',
		'callback' => fn( $order ) => $order->get_order_number()
	] );

	register_email_tag( 'woo', 'order_table', [
		'type'     => 'order_items',
		'callback' => function ( $order ) {
			$items = [];
			foreach ( $order->get_items() as $item ) {
				$items[] = [
					'name'     => $item->get_name(),
					'quantity' => $item->get_quantity(),
					'price'    => $item->get_total() * 100 // Convert to cents
				];
			}

			return [
				'items'    => $items,
				'tax'      => $order->get_total_tax() * 100,
				'shipping' => $order->get_shipping_total() * 100,
				'currency' => $order->get_currency()
			];
		}
	] );

	// Register template
	register_email_template( 'woo', 'order_confirmation', [
		'label'    => 'Order Confirmation',
		'subject'  => 'Order #{order_number} Received',
		'template' => 'invoice',
		'message'  => '
            <h2>Thank you for your order!</h2>
            <p>Order #{order_number} has been received.</p>
            {order_table}
            <p>We will notify you when it ships.</p>
        '
	] );
} );

// Send on order placement
add_action( 'woocommerce_thankyou', function ( $order_id ) {
	send_email_template( 'woo', 'order_confirmation', [
		'to'   => $order->get_billing_email(),
		'data' => wc_get_order( $order_id )
	] );
} );
```

## Templates

| Template | Description | Best For |
|----------|-------------|----------|
| `default` | Clean with colored header | Welcome emails, marketing |
| `invoice` | Minimal Apple-inspired | Receipts, confirmations |
| `notification` | Simple bordered | System alerts |
| `plain` | Text-focused | Technical emails |
| `dark` | Dark mode | Modern apps |
| `soft` | Gentle gradients | SaaS products |
| `mono` | Black & white | Formal notices |

### Custom Templates

Place templates in your theme:
```
/wp-content/themes/your-theme/register-emails/custom.html
```

## All Components

* **Text**: `text`, `raw_html`, `divider`, `spacer`
* **Interactive**: `button`, `alert`, `info_box`, `coupon`
* **Data**: `table`, `order_items`, `key_value_list`, `stats_grid`, `progress_bar`
* **Commerce**: `product_list`, `downloads_list`, `shipping_tracker`, `subscription_status`
* **User**: `activity_log`, `event_details`, `reward_balance`, `testimonial`

## Global Tags

Always available in any template:
* `{site_name}` - WordPress site name
* `{site_url}` - Site URL
* `{admin_email}` - Admin email
* `{year}` - Current year
* `{date}` - Current date

## Advanced Features

### Tag Groups
```php
// Register tags to multiple groups
register_email_tag( 'shop', 'price', [
	'type'     => 'text',
	'groups'   => [ 'invoices', 'quotes' ],  // Available in multiple contexts
	'callback' => fn( $data ) => format_currency( $data->amount, $data->currency )
] );
```

### Preview Mode
```php
// Generate preview with sample data
$html = preview_email_template( 'shop', 'order_confirmation' );

// Preview with specific data
$html = preview_email_template( 'shop', 'order_confirmation', $sample_order );
```

### Settings Integration
```php
register_email_template( 'shop', 'welcome', [
	'settings_callback' => function () {
		// Pull from your settings
		return [
			'enabled' => get_option( 'welcome_email_enabled' ),
			'subject' => get_option( 'welcome_email_subject' ),
			'message' => get_option( 'welcome_email_content' )
		];
	},
	'default_settings'  => [
		'subject' => 'Welcome to {site_name}!',
		'message' => 'Thank you for joining us.'
	]
] );
```

## Requirements

- PHP 7.4 or later
- WordPress 5.0 or later

## License

GPL-2.0-or-later

## Credits

Created by [David Sherlock](https://davidsherlock.com) at [ArrayPress](https://arraypress.com)