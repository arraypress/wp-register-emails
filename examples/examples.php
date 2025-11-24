<?php
/**
 * Plugin Name: WP Register Emails
 * Plugin URI: https://github.com/arraypress/wp-register-emails
 * Description: Comprehensive demonstration of all email templates and components from the WP Register Emails library
 * Version: 1.0.0
 * Author: ArrayPress
 * Author URI: https://arraypress.com
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wp-register-emails-demo
 * Domain Path: /languages
 * Requires at least: 5.0
 * Requires PHP: 7.4
 */

declare( strict_types=1 );

namespace ArrayPress\RegisterEmailsDemo;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

// Load the library
require_once __DIR__ . '/vendor/autoload.php';

/**
 * Email Registration Demo Plugin
 *
 * Singleton class that demonstrates all features of the WP Register Emails library
 * including all templates and components.
 *
 * @package ArrayPress\RegisterEmailsDemo
 * @since   1.0.0
 */
final class Demo {

    /**
     * Plugin instance
     *
     * @var Demo|null
     * @since 1.0.0
     */
    private static ?Demo $instance = null;

    /**
     * Available templates configuration
     *
     * @var array
     * @since 1.0.0
     */
    private array $templates = [];

    /**
     * Component showcase message
     *
     * @var string
     * @since 1.0.0
     */
    private string $complete_message = '';

    /**
     * Private constructor to prevent direct instantiation
     *
     * @since 1.0.0
     */
    private function __construct() {
        $this->init_templates();
        $this->init_hooks();
    }

    /**
     * Get plugin instance
     *
     * @return Demo
     * @since 1.0.0
     */
    public static function get_instance(): Demo {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Prevent cloning
     *
     * @since 1.0.0
     */
    private function __clone() {
    }

    /**
     * Prevent unserialization
     *
     * @since 1.0.0
     */
    public function __wakeup() {
        throw new \Exception( 'Cannot unserialize singleton' );
    }

    /**
     * Initialize WordPress hooks
     *
     * @since 1.0.0
     */
    private function init_hooks(): void {
        // Early hook for preview handling
        add_action( 'template_redirect', [ $this, 'handle_preview_request' ] );

        // Register components and templates
        add_action( 'init', [ $this, 'register_email_tags' ] );
        add_action( 'init', [ $this, 'register_email_templates' ] );

        // Admin interface
        add_action( 'admin_menu', [ $this, 'add_admin_menu' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ] );
    }

    /**
     * Initialize template configurations
     *
     * @since 1.0.0
     */
    private function init_templates(): void {
        $this->templates = [
                'default'      => [
                        'key'         => 'template_default',
                        'title'       => '🎨 Default Template',
                        'template'    => 'Default',
                        'description' => 'Clean, professional template with gradient header',
                        'color'       => '#667eea',
                        'settings'    => [
                                'title'    => 'Welcome to Premium!',
                                'subtitle' => 'Complete Component Showcase',
                        ],
                ],
                'invoice'      => [
                        'key'         => 'template_invoice',
                        'title'       => '📄 Invoice Template',
                        'template'    => 'Invoice',
                        'description' => 'Apple-inspired minimal invoice design',
                        'color'       => '#0071e3',
                        'settings'    => [
                                'title' => 'Invoice #' . wp_rand( 10000, 99999 ),
                        ],
                ],
                'notification' => [
                        'key'         => 'template_notification',
                        'title'       => '🔔 Notification',
                        'template'    => 'Notification',
                        'description' => 'Simple notification template for transactional emails',
                        'color'       => '#ef4444',
                        'settings'    => [
                                'title' => 'Important Notification',
                        ],
                ],
                'plain'        => [
                        'key'         => 'template_plain',
                        'title'       => '📝 Plain Template',
                        'template'    => 'Plain',
                        'description' => 'Minimal text-focused template',
                        'color'       => '#6b7280',
                        'settings'    => [
                                'title'    => 'System Report',
                                'subtitle' => date( 'F j, Y, g:i a' ),
                        ],
                ],
                'dark'         => [
                        'key'         => 'template_dark',
                        'title'       => '🌙 Dark Template',
                        'template'    => 'Dark',
                        'description' => 'Modern dark mode template',
                        'color'       => '#1a1a1a',
                        'settings'    => [
                                'title'    => 'Dark Mode Showcase',
                                'subtitle' => 'All Components in Dark Theme',
                        ],
                ],
                'mono'         => [
                        'key'         => 'template_mono',
                        'title'       => '⚫ Mono Template',
                        'template'    => 'Mono',
                        'description' => 'Ultra-minimal black and white design',
                        'color'       => '#000000',
                        'settings'    => [
                                'title'    => 'MONO',
                                'subtitle' => 'Minimalist Component Display',
                        ],
                ],
                'soft'         => [
                        'key'         => 'template_soft',
                        'title'       => '☁️ Soft Template',
                        'template'    => 'Soft',
                        'description' => 'Gentle design with soft colors and rounded corners',
                        'color'       => '#c3cfe2',
                        'settings'    => [
                                'title'    => 'Soft & Gentle',
                                'subtitle' => 'Calming Email Design',
                        ],
                ],
        ];

        // Build complete message with all components
        $this->complete_message = $this->build_complete_message();
    }

    /**
     * Build the complete message showcasing all components
     *
     * @return string
     * @since 1.0.0
     */
    private function build_complete_message(): string {
        return '
		<p>Dear {customer_name},</p>
		<p>Welcome to our comprehensive email component demonstration! This email showcases every available component in the library.</p>
		
		<h2>🎯 Alert Components</h2>
		<p>Four types of contextual alerts for different situations:</p>
		{success_alert}
		{spacer_small}
		{warning_alert}
		{spacer_small}
		{error_alert}
		{spacer_small}
		{info_alert}
		
		{divider}
		
		<h2>💳 Subscription & Account</h2>
		<p>Display subscription status and account information:</p>
		{subscription_status}
		{spacer_medium}
		{reward_balance}
		
		{divider}
		
		<h2>📦 Shipping & Delivery</h2>
		<p>Track packages with visual progress indicators:</p>
		{shipping_tracker}
		{spacer_small}
		{shipping_info}
		
		{divider}
		
		<h2>📅 Event Management</h2>
		<p>Complete event details with calendar integration:</p>
		{event_details}
		
		{divider}
		
		<h2>🔐 Security & Activity</h2>
		<p>Recent account activity and security events:</p>
		{activity_log}
		
		{divider}
		
		<h2>📊 Progress Tracking</h2>
		<p>Visual progress indicators for various metrics:</p>
		{setup_progress}
		{spacer_small}
		{delivery_progress}
		{spacer_small}
		{storage_progress}
		
		{divider}
		
		<h2>🧾 Invoice & Orders</h2>
		<p>Professional invoice layout with automatic calculations:</p>
		{order_items}
		{spacer_medium}
		<p>Alternative table format:</p>
		{invoice_table}
		{spacer_medium}
		<p>Order details in key-value format:</p>
		{order_details}
		
		{divider}
		
		<h2>🛍️ Products & Downloads</h2>
		<p>Showcase products and digital downloads:</p>
		{featured_products}
		{spacer_medium}
		{download_package}
		
		{divider}
		
		<h2>🎁 Special Offers</h2>
		<p>Promotional codes and special offers:</p>
		{special_coupon}
		{spacer_small}
		{license_key}
		
		{divider}
		
		<h2>📈 Statistics & Metrics</h2>
		<p>Display key performance indicators:</p>
		{performance_stats}
		
		{divider}
		
		<h2>💬 Social Proof</h2>
		<p>Customer testimonials and reviews:</p>
		{customer_testimonial}
		
		{divider}
		
		<h2>ℹ️ Information Displays</h2>
		{welcome_info}
		{spacer_small}
		{tip_info}
		
		{divider}
		
		<h2>🎨 Custom Content</h2>
		<p>Raw HTML for complete customization:</p>
		{custom_html}
		
		{divider}
		
		<h2>🔘 Call-to-Action Buttons</h2>
		<p>Various button styles and alignments:</p>
		{primary_button}
		{spacer_small}
		{secondary_button}
		{spacer_small}
		{outline_button}
		
		{divider}
		
		<p>Thank you for exploring our email component library!</p>
		<p>Best regards,<br>The {site_name} Team</p>
		';
    }

    /**
     * Handle email preview requests
     *
     * @since 1.0.0
     */
    public function handle_preview_request(): void {
        if ( ! isset( $_GET['email_preview'], $_GET['template'] ) ) {
            return;
        }

        // Check permissions
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Unauthorized' );
        }

        // Verify nonce
        $template = sanitize_text_field( $_GET['template'] );
        $nonce    = $_GET['nonce'] ?? '';

        if ( ! wp_verify_nonce( $nonce, 'preview_' . $template ) ) {
            wp_die( 'Invalid nonce' );
        }

        // Generate sample data
        $sample_data = $this->get_sample_data();

        // Generate preview HTML
        $html = preview_email_template( 'demo', $template, $sample_data );

        // Output HTML and exit
        header( 'Content-Type: text/html; charset=' . get_bloginfo( 'charset' ) );
        echo $html;
        exit;
    }

    /**
     * Register all email tags (components)
     *
     * @since 1.0.0
     */
    public function register_email_tags(): void {
        // Text components
        $this->register_text_tags();

        // Button components
        $this->register_button_tags();

        // Alert components
        $this->register_alert_tags();

        // Table and list components
        $this->register_table_tags();

        // E-commerce components
        $this->register_commerce_tags();

        // Information components
        $this->register_info_tags();

        // Progress components
        $this->register_progress_tags();

        // Advanced components
        $this->register_advanced_tags();

        // Layout components
        $this->register_layout_tags();
    }

    /**
     * Register text-based tags
     *
     * @since 1.0.0
     */
    private function register_text_tags(): void {
        register_email_tag( 'demo', 'customer_name', [
                'type'     => 'text',
                'label'    => 'Customer Name',
                'callback' => function ( $data ) {
                    return $data->name ?? 'Sarah Johnson';
                },
                'preview'  => 'Sarah Johnson',
        ] );

        register_email_tag( 'demo', 'order_number', [
                'type'     => 'text',
                'label'    => 'Order Number',
                'callback' => function ( $data ) {
                    return $data->order_id ?? '#ORD-' . wp_rand( 100000, 999999 );
                },
                'preview'  => '#ORD-123456',
        ] );
    }

    /**
     * Register button components
     *
     * @since 1.0.0
     */
    private function register_button_tags(): void {
        register_email_tag( 'demo', 'primary_button', [
                'type'     => 'button',
                'label'    => 'Primary Button',
                'callback' => function ( $data ) {
                    return [
                            'text'       => 'Access Your Account',
                            'url'        => home_url( '/account' ),
                            'align'      => 'center',
                            'background' => '#667eea',
                    ];
                },
        ] );

        register_email_tag( 'demo', 'secondary_button', [
                'type'     => 'button',
                'label'    => 'Secondary Button',
                'callback' => function ( $data ) {
                    return [
                            'text'       => 'View Documentation',
                            'url'        => home_url( '/docs' ),
                            'align'      => 'left',
                            'background' => '#f3f4f6',
                            'color'      => '#111827',
                            'style'      => 'secondary',
                    ];
                },
        ] );

        register_email_tag( 'demo', 'outline_button', [
                'type'     => 'button',
                'label'    => 'Outline Button',
                'callback' => function ( $data ) {
                    return [
                            'text'       => 'Learn More',
                            'url'        => home_url( '/learn-more' ),
                            'align'      => 'right',
                            'background' => '#ffffff',
                            'color'      => '#667eea',
                            'style'      => 'outline',
                    ];
                },
        ] );
    }

    /**
     * Register alert components
     *
     * @since 1.0.0
     */
    private function register_alert_tags(): void {
        $alerts = [
                'success' => [
                        'message' => 'Congratulations! Your order has been successfully processed and will be delivered within 2-3 business days.',
                        'type'    => 'success',
                ],
                'warning' => [
                        'message' => 'Your subscription will expire in 7 days. Renew now to maintain access to premium features.',
                        'type'    => 'warning',
                ],
                'error'   => [
                        'message' => 'Payment failed. Please update your billing information to continue your subscription.',
                        'type'    => 'error',
                ],
                'info'    => [
                        'message' => 'New features have been added to your account. Explore the latest updates in your dashboard!',
                        'type'    => 'info',
                ],
        ];

        foreach ( $alerts as $key => $config ) {
            register_email_tag( 'demo', $key . '_alert', [
                    'type'     => 'alert',
                    'label'    => ucfirst( $key ) . ' Alert',
                    'callback' => function ( $data ) use ( $config ) {
                        return $config;
                    },
            ] );
        }
    }

    /**
     * Register table and list components
     *
     * @since 1.0.0
     */
    private function register_table_tags(): void {
        // Standard table
        register_email_tag( 'demo', 'invoice_table', [
                'type'     => 'table',
                'label'    => 'Invoice Table',
                'callback' => function ( $data ) {
                    return [
                            'data'       => [
                                    [ 'Product', 'Qty', 'Price', 'Total' ],
                                    [ 'Premium Plugin', '1', '$99.00', '$99.00' ],
                                    [ 'Priority Support', '1', '$149.00', '$149.00' ],
                                    [ 'Custom Development', '10 hrs', '$125.00', '$1,250.00' ],
                                    [ '', '', 'Subtotal:', '$1,498.00' ],
                                    [ '', '', 'Tax (10%):', '$149.80' ],
                                    [ '', '', '<strong>Total:</strong>', '<strong>$1,647.80</strong>' ],
                            ],
                            'has_header' => true,
                            'alignments' => [ 'left', 'center', 'right', 'right' ],
                            'bordered'   => true,
                    ];
                },
        ] );

        // Order items with currency support
        register_email_tag( 'demo', 'order_items', [
                'type'     => 'order_items',
                'label'    => 'Order Items Invoice',
                'callback' => function ( $data ) {
                    return [
                            'items'    => [
                                    [
                                            'name'     => 'Premium WordPress Plugin',
                                            'quantity' => 1,
                                            'price'    => 9900, // $99.00 in cents
                                    ],
                                    [
                                            'name'     => 'Priority Support Package',
                                            'quantity' => 1,
                                            'price'    => 14900, // $149.00 in cents
                                    ],
                                    [
                                            'name'     => 'Custom Development Hours',
                                            'quantity' => 10,
                                            'price'    => 12500, // $125.00 per hour
                                    ],
                            ],
                            'discount' => 2000,  // $20.00 discount
                            'shipping' => 1500,  // $15.00 shipping
                            'tax'      => 14880, // Tax amount
                            'currency' => 'USD',
                    ];
                },
        ] );

        // Key-value list
        register_email_tag( 'demo', 'order_details', [
                'type'     => 'key_value_list',
                'label'    => 'Order Details',
                'callback' => function ( $data ) {
                    return [
                            'Order Date'      => date( 'F j, Y, g:i a' ),
                            'Order Number'    => '#ORD-' . wp_rand( 100000, 999999 ),
                            'Payment Method'  => 'Credit Card •••• 4242',
                            'Transaction ID'  => 'ch_' . substr( md5( uniqid() ), 0, 24 ),
                            'Billing Address' => '123 Main Street, Suite 100, San Francisco, CA 94105',
                            'Shipping Method' => 'Express Delivery (2-3 business days)',
                            'Order Status'    => 'Processing',
                    ];
                },
        ] );
    }

    /**
     * Register e-commerce components
     *
     * @since 1.0.0
     */
    private function register_commerce_tags(): void {
        // Product list
        register_email_tag( 'demo', 'featured_products', [
                'type'     => 'product_list',
                'label'    => 'Featured Products',
                'callback' => function ( $data ) {
                    // Get random images from media library
                    $images = get_posts( [
                            'post_type'      => 'attachment',
                            'post_mime_type' => 'image',
                            'posts_per_page' => 3,
                            'orderby'        => 'rand',
                            'fields'         => 'ids',
                    ] );

                    // Fallback to site icon or placeholder
                    $fallback = get_site_icon_url( 150 ) ?: 'https://via.placeholder.com/150';

                    // Get thumbnail URLs for each image
                    $image_urls = [];
                    foreach ( $images as $index => $image_id ) {
                        $image_urls[ $index ] = wp_get_attachment_image_url( $image_id, 'thumbnail' );
                        if ( ! $image_urls[ $index ] ) {
                            $image_urls[ $index ] = $fallback;
                        }
                    }

                    // Ensure we have 3 images
                    while ( count( $image_urls ) < 3 ) {
                        $image_urls[] = $fallback;
                    }

                    return [
                            'products' => [
                                    [
                                            'name'        => 'Professional WordPress Plugin',
                                            'description' => 'Full-featured plugin with lifetime updates and premium support',
                                            'price'       => '$199.00',
                                            'image'       => $image_urls[0],
                                            'url'         => home_url( '/product/pro-plugin' ),
                                            'button_text' => 'View Details',
                                            'button_url'  => home_url( '/product/pro-plugin' ),
                                    ],
                                    [
                                            'name'        => 'Developer Toolkit',
                                            'description' => 'Complete collection of development tools and utilities',
                                            'price'       => '$299.00',
                                            'image'       => $image_urls[1],
                                            'url'         => home_url( '/product/dev-toolkit' ),
                                    ],
                                    [
                                            'name'        => 'Support Package',
                                            'description' => 'Priority support with 24-hour response time guarantee',
                                            'price'       => '$99/month',
                                            'image'       => $image_urls[2],
                                            'quantity'    => 1,
                                    ],
                            ],
                    ];
                },
        ] );

        // Downloads list
        register_email_tag( 'demo', 'download_package', [
                'type'     => 'downloads_list',
                'label'    => 'Download Package',
                'callback' => function ( $data ) {
                    return [
                            'downloads' => [
                                    [
                                            'name'        => 'WordPress Plugin Package',
                                            'description' => 'Version 3.2.1 - Latest stable release with bug fixes',
                                            'files'       => [
                                                    [
                                                            'name' => 'plugin-pro-v3.2.1.zip',
                                                            'url'  => home_url( '/download/plugin' ),
                                                            'size' => '4.7 MB',
                                                    ],
                                                    [
                                                            'name' => 'documentation.pdf',
                                                            'url'  => home_url( '/download/docs' ),
                                                            'size' => '2.3 MB',
                                                    ],
                                                    [
                                                            'name' => 'changelog.txt',
                                                            'url'  => home_url( '/download/changelog' ),
                                                            'size' => '45 KB',
                                                    ],
                                            ],
                                    ],
                                    [
                                            'name'        => 'Bonus Resources',
                                            'description' => 'Additional templates, code snippets, and examples',
                                            'files'       => [
                                                    [
                                                            'name' => 'email-templates.zip',
                                                            'url'  => home_url( '/download/templates' ),
                                                            'size' => '1.2 MB',
                                                    ],
                                                    [
                                                            'name' => 'code-snippets.zip',
                                                            'url'  => home_url( '/download/snippets' ),
                                                            'size' => '856 KB',
                                                    ],
                                            ],
                                    ],
                            ],
                    ];
                },
        ] );

        // Coupon
        register_email_tag( 'demo', 'special_coupon', [
                'type'     => 'coupon',
                'label'    => 'Special Coupon',
                'callback' => function ( $data ) {
                    return [
                            'code'        => 'WELCOME30',
                            'description' => 'Get 30% off your next purchase',
                            'expiry'      => 'Valid until ' . date( 'F j, Y', strtotime( '+30 days' ) ),
                            'background'  => '#fef3c7',
                            'border'      => '#f59e0b',
                    ];
                },
        ] );

        // License key
        register_email_tag( 'demo', 'license_key', [
                'type'     => 'code_block',
                'label'    => 'License Key',
                'callback' => function ( $data ) {
                    $key       = strtoupper( substr( md5( uniqid() ), 0, 20 ) );
                    $formatted = implode( '-', str_split( $key, 4 ) );

                    return [
                            'label'      => 'Your Premium License Key',
                            'code'       => $formatted,
                            'background' => '#f3f4f6',
                            'color'      => '#111',
                    ];
                },
        ] );
    }

    /**
     * Register information components
     *
     * @since 1.0.0
     */
    private function register_info_tags(): void {
        register_email_tag( 'demo', 'welcome_info', [
                'type'     => 'info_box',
                'label'    => 'Welcome Info',
                'callback' => function ( $data ) {
                    return [
                            'icon'       => '🎉',
                            'title'      => 'Welcome to Premium!',
                            'content'    => 'Your account has been upgraded to premium. You now have access to all advanced features, priority support, and exclusive content.',
                            'background' => '#f0fdf4',
                            'color'      => '#166534',
                    ];
                },
        ] );

        register_email_tag( 'demo', 'shipping_info', [
                'type'     => 'info_box',
                'label'    => 'Shipping Info',
                'callback' => function ( $data ) {
                    return [
                            'icon'    => '📦',
                            'title'   => 'Shipping Information',
                            'content' => 'Your order has been dispatched and is on its way! Track your package using the tracking number above. Estimated delivery: 2-3 business days.',
                    ];
                },
        ] );

        register_email_tag( 'demo', 'tip_info', [
                'type'     => 'info_box',
                'label'    => 'Pro Tip',
                'callback' => function ( $data ) {
                    return [
                            'icon'       => '💡',
                            'title'      => 'Pro Tip',
                            'content'    => 'Did you know? You can save 20% on your subscription by switching to annual billing. Plus, you\'ll get two months free!',
                            'background' => '#fef3c7',
                            'color'      => '#713f12',
                    ];
                },
        ] );

        // Testimonial
        register_email_tag( 'demo', 'customer_testimonial', [
                'type'     => 'testimonial',
                'label'    => 'Customer Testimonial',
                'callback' => function ( $data ) {
                    return [
                            'quote'  => 'This plugin has completely transformed our workflow. The time savings alone have paid for the license ten times over. The support team is incredibly responsive and helpful.',
                            'author' => 'Michael Chen',
                            'role'   => 'CTO, TechStart Inc.',
                            'border' => '#667eea',
                    ];
                },
        ] );
    }

    /**
     * Register progress components
     *
     * @since 1.0.0
     */
    private function register_progress_tags(): void {
        register_email_tag( 'demo', 'setup_progress', [
                'type'     => 'progress_bar',
                'label'    => 'Setup Progress',
                'callback' => function ( $data ) {
                    return [
                            'current' => 75,
                            'total'   => 100,
                            'label'   => 'Account Setup Progress',
                            'color'   => '#667eea',
                    ];
                },
        ] );

        register_email_tag( 'demo', 'delivery_progress', [
                'type'     => 'progress_bar',
                'label'    => 'Delivery Progress',
                'callback' => function ( $data ) {
                    return [
                            'current' => 3,
                            'total'   => 5,
                            'label'   => 'Delivery Status: In Transit',
                            'color'   => '#10b981',
                    ];
                },
        ] );

        register_email_tag( 'demo', 'storage_progress', [
                'type'     => 'progress_bar',
                'label'    => 'Storage Usage',
                'callback' => function ( $data ) {
                    return [
                            'current' => 45,
                            'total'   => 100,
                            'label'   => 'Storage Used: 45GB of 100GB',
                            'color'   => '#f59e0b',
                    ];
                },
        ] );

        // Stats grid
        register_email_tag( 'demo', 'performance_stats', [
                'type'     => 'stats_grid',
                'label'    => 'Performance Stats',
                'callback' => function ( $data ) {
                    return [
                            'stats' => [
                                    [ 'value' => '50,000+', 'label' => 'Active Users' ],
                                    [ 'value' => '99.99%', 'label' => 'Uptime' ],
                                    [ 'value' => '24/7', 'label' => 'Support' ],
                                    [ 'value' => '4.9★', 'label' => 'Rating' ],
                                    [ 'value' => '150+', 'label' => 'Features' ],
                                    [ 'value' => '<1s', 'label' => 'Load Time' ],
                            ],
                    ];
                },
        ] );
    }

    /**
     * Register advanced components
     *
     * @since 1.0.0
     */
    private function register_advanced_tags(): void {
        // Shipping Tracker
        register_email_tag( 'demo', 'shipping_tracker', [
                'type'     => 'shipping_tracker',
                'label'    => 'Shipping Tracker',
                'callback' => function ( $data ) {
                    return [
                            'carrier'            => 'FedEx Express',
                            'tracking_number'    => '7489' . wp_rand( 100000, 999999 ),
                            'status'             => 'In Transit',
                            'estimated_delivery' => date( 'F j, Y', strtotime( '+3 days' ) ),
                            'tracking_url'       => 'https://fedex.com/tracking',
                            'steps'              => [
                                    [
                                            'label'     => 'Order Placed',
                                            'completed' => true,
                                            'timestamp' => date( 'M j, g:i a', strtotime( '-2 days' ) ),
                                    ],
                                    [
                                            'label'     => 'Package Picked Up',
                                            'completed' => true,
                                            'timestamp' => date( 'M j, g:i a', strtotime( '-1 day' ) ),
                                    ],
                                    [
                                            'label'     => 'In Transit',
                                            'completed' => true,
                                            'timestamp' => date( 'M j, g:i a', strtotime( '-6 hours' ) ),
                                    ],
                                    [
                                            'label'     => 'Out for Delivery',
                                            'completed' => false,
                                    ],
                                    [
                                            'label'     => 'Delivered',
                                            'completed' => false,
                                    ],
                            ],
                    ];
                },
        ] );

        // Subscription Status
        register_email_tag( 'demo', 'subscription_status', [
                'type'     => 'subscription_status',
                'label'    => 'Subscription Status',
                'callback' => function ( $data ) {
                    return [
                            'plan'               => 'Professional Plan',
                            'status'             => 'Active',
                            'amount'             => 9900, // $99.00 in cents
                            'currency'           => 'USD',
                            'interval'           => 'month',
                            'next_billing_date'  => date( 'F j, Y', strtotime( '+30 days' ) ),
                            'features'           => [
                                    'Unlimited projects',
                                    'Priority 24/7 support',
                                    'Advanced analytics & reporting',
                                    'Custom integrations',
                                    'White-label options',
                                    'API access',
                            ],
                            'update_payment_url' => home_url( '/account/billing' ),
                            'cancel_url'         => home_url( '/account/cancel' ),
                    ];
                },
        ] );

        // Event Details
        register_email_tag( 'demo', 'event_details', [
                'type'     => 'event_details',
                'label'    => 'Event Details',
                'callback' => function ( $data ) {
                    $next_thursday = strtotime( 'next Thursday' );

                    return [
                            'title'        => 'Product Demo & Q&A Session',
                            'date'         => date( 'l, F j, Y', $next_thursday ),
                            'time'         => '2:00 PM - 3:00 PM EST',
                            'duration'     => '1 hour',
                            'location'     => 'Zoom Webinar',
                            'description'  => 'Join us for an exclusive walkthrough of our latest features and get your questions answered by our product team. We\'ll cover new updates, best practices, and upcoming features.',
                            'join_url'     => 'https://zoom.us/j/123456789',
                            'calendar_url' => home_url( '/calendar/add' ),
                            'organizer'    => 'Sarah Johnson, Product Manager',
                            'attendees'    => [
                                    'John Smith',
                                    'Emily Davis',
                                    'Michael Chen',
                                    'Lisa Wilson',
                                    'Tom Anderson',
                                    'Jane Parker',
                            ],
                    ];
                },
        ] );

        // Activity Log
        register_email_tag( 'demo', 'activity_log', [
                'type'     => 'activity_log',
                'label'    => 'Activity Log',
                'callback' => function ( $data ) {
                    return [
                            'title'   => 'Recent Account Activity',
                            'items'   => [
                                    [
                                            'action'   => 'Successfully logged in',
                                            'time'     => '2 hours ago',
                                            'device'   => 'Chrome on Mac',
                                            'location' => 'San Francisco, CA',
                                            'ip'       => '192.168.1.42',
                                            'type'     => 'success',
                                    ],
                                    [
                                            'action' => 'Password changed',
                                            'time'   => 'Yesterday at 3:45 PM',
                                            'device' => 'Mobile App (iOS)',
                                            'type'   => 'warning',
                                    ],
                                    [
                                            'action'   => 'API key generated',
                                            'time'     => '3 days ago',
                                            'device'   => 'Firefox on Windows',
                                            'location' => 'New York, NY',
                                            'type'     => 'info',
                                    ],
                                    [
                                            'action'   => 'Failed login attempt blocked',
                                            'time'     => '1 week ago',
                                            'location' => 'Unknown location',
                                            'ip'       => '45.67.89.123',
                                            'type'     => 'error',
                                    ],
                                    [
                                            'action' => 'Two-factor authentication enabled',
                                            'time'   => '2 weeks ago',
                                            'device' => 'Settings Dashboard',
                                            'type'   => 'success',
                                    ],
                            ],
                            'show_ip' => true,
                            'limit'   => 5,
                    ];
                },
        ] );

        // Reward Balance
        register_email_tag( 'demo', 'reward_balance', [
                'type'     => 'reward_balance',
                'label'    => 'Reward Balance',
                'callback' => function ( $data ) {
                    return [
                            'current_balance' => 2750,
                            'currency_label'  => 'points',
                            'recent_activity' => [
                                    [
                                            'description' => 'Purchase - Premium Plugin',
                                            'amount'      => '+500',
                                            'date'        => '3 days ago',
                                    ],
                                    [
                                            'description' => 'Referral bonus earned',
                                            'amount'      => '+250',
                                            'date'        => '1 week ago',
                                    ],
                                    [
                                            'description' => 'Redeemed for discount',
                                            'amount'      => '-1000',
                                            'date'        => '2 weeks ago',
                                    ],
                                    [
                                            'description' => 'Birthday bonus',
                                            'amount'      => '+100',
                                            'date'        => '1 month ago',
                                    ],
                            ],
                            'expiring_soon'   => 500,
                            'expiry_date'     => date( 'F j, Y', strtotime( '+60 days' ) ),
                            'redeem_url'      => home_url( '/rewards' ),
                            'tier_info'       => [
                                    'name'           => 'Gold Member',
                                    'progress'       => 75,
                                    'next_tier'      => 'Platinum',
                                    'points_to_next' => 1250,
                            ],
                    ];
                },
        ] );

        // Raw HTML
        register_email_tag( 'demo', 'custom_html', [
                'type'     => 'raw_html',
                'label'    => 'Custom HTML',
                'callback' => function ( $data ) {
                    return [
                            'content' => '
					<div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; border-radius: 12px; text-align: center; color: white !important;">
						<h3 style="margin: 0 0 15px; font-size: 24px; color: white !important;">🎊 Exclusive Member Benefit</h3>
						<p style="margin: 0 0 20px; font-size: 16px; line-height: 1.5; color: white !important;">
							As a valued premium member, you have access to exclusive features and priority support.
						</p>
						<a href="' . home_url( '/exclusive' ) . '" style="display: inline-block; background: white; color: #667eea !important; padding: 12px 30px; border-radius: 6px; text-decoration: none; font-weight: 600;">
							Explore Benefits
						</a>
					</div>',
                    ];
                },
        ] );
    }

    /**
     * Register layout components
     *
     * @since 1.0.0
     */
    private function register_layout_tags(): void {
        // Divider
        register_email_tag( 'demo', 'divider', [
                'type'  => 'divider',
                'label' => 'Divider',
        ] );

        // Spacers
        register_email_tag( 'demo', 'spacer_small', [
                'type'     => 'spacer',
                'label'    => 'Small Spacer',
                'callback' => function ( $data ) {
                    return [ 'height' => 20 ];
                },
        ] );

        register_email_tag( 'demo', 'spacer_medium', [
                'type'     => 'spacer',
                'label'    => 'Medium Spacer',
                'callback' => function ( $data ) {
                    return [ 'height' => 40 ];
                },
        ] );

        register_email_tag( 'demo', 'spacer_large', [
                'type'     => 'spacer',
                'label'    => 'Large Spacer',
                'callback' => function ( $data ) {
                    return [ 'height' => 60 ];
                },
        ] );
    }

    /**
     * Register email templates
     *
     * @since 1.0.0
     */
    public function register_email_templates(): void {
        foreach ( $this->templates as $template_key => $config ) {
            register_email_template( 'demo', $config['key'], [
                    'label'            => $config['title'] . ' - Complete Demo',
                    'template'         => strtolower( $template_key ),
                    'tag_group'        => 'demo',
                    'default_settings' => [
                            'enabled'  => true,
                            'subject'  => $config['title'] . ' - {site_name}',
                            'title'    => $config['settings']['title'] ?? $config['title'],
                            'subtitle' => $config['settings']['subtitle'] ?? 'All Components Demonstration',
                            'message'  => $this->complete_message,
                    ],
                    'visual_config'    => $this->get_visual_config( $template_key ),
            ] );
        }
    }

    /**
     * Get visual configuration for a template
     *
     * @param string $template_key Template key
     *
     * @return array Visual configuration
     * @since 1.0.0
     */
    private function get_visual_config( string $template_key ): array {
        $base_config = [
                'logo'   => get_site_icon_url() ?: '',
                'colors' => [
                        'primary' => $this->templates[ $template_key ]['color'] ?? '#667eea',
                ],
        ];

        // Add footer for templates that support it
        if ( ! in_array( $template_key, [ 'invoice', 'notification' ], true ) ) {
            $base_config['footer'] = [
                    'text'         => '© {year} {site_name}. All rights reserved.',
                    'links'        => [
                            'My Account'  => home_url( '/account' ),
                            'Support'     => home_url( '/support' ),
                            'Unsubscribe' => home_url( '/unsubscribe' ),
                    ],
                    'social_links' => [
                            'Twitter'  => 'https://twitter.com',
                            'Facebook' => 'https://facebook.com',
                            'LinkedIn' => 'https://linkedin.com',
                            'GitHub'   => 'https://github.com',
                    ],
            ];
        }

        // Template-specific customizations
        switch ( $template_key ) {
            case 'dark':
                $base_config['colors']['primary'] = '#6366f1'; // Indigo works better on dark
                break;

            case 'soft':
                $base_config['colors']['primary'] = '#a78bfa'; // Soft purple
                break;

            case 'mono':
                $base_config['footer']['social_links'] = []; // Minimal footer for mono
                break;
        }

        return $base_config;
    }

    /**
     * Add admin menu
     *
     * @since 1.0.0
     */
    public function add_admin_menu(): void {
        add_menu_page(
                'Email Templates Demo',
                'Email Demo',
                'manage_options',
                'email-demo',
                [ $this, 'render_admin_page' ],
                'dashicons-email-alt',
                30
        );
    }

    /**
     * Enqueue admin assets
     *
     * @param string $hook Current admin page hook
     *
     * @since 1.0.0
     */
    public function enqueue_admin_assets( string $hook ): void {
        if ( 'toplevel_page_email-demo' !== $hook ) {
            return;
        }

        // Add inline CSS for better admin styling
        wp_add_inline_style( 'wp-admin', $this->get_admin_css() );
    }

    /**
     * Get admin CSS styles
     *
     * @return string CSS styles
     * @since 1.0.0
     */
    private function get_admin_css(): string {
        return '
		.email-demo-header { background: white; padding: 30px; border-radius: 8px; margin-bottom: 30px; }
		.email-demo-header h1 { margin: 0 0 10px; font-size: 32px; }
		.email-demo-header p { color: #666; font-size: 16px; margin: 0; }
		.template-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 24px; margin-bottom: 30px; }
		.template-card { background: white; border: 1px solid #ddd; border-radius: 8px; overflow: hidden; transition: box-shadow 0.3s; }
		.template-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
		.template-header { padding: 24px; color: white; min-height: 120px; display: flex; flex-direction: column; justify-content: center; }
		.template-header h2 { margin: 0 0 5px; color: white; font-size: 20px; }
		.template-header .template-name { opacity: 0.9; font-size: 14px; margin: 0; }
		.template-body { padding: 24px; }
		.template-body p { color: #666; margin: 0 0 20px; }
		.test-email-field { width: 100%; padding: 8px 12px; margin-bottom: 12px; }
		.template-actions { display: flex; gap: 10px; }
		.template-actions .button { flex: 1; text-align: center; justify-content: center; }
		.components-card { background: white; padding: 30px; border-radius: 8px; }
		.components-card h2 { margin-top: 0; }
		.components-list { columns: 3; column-gap: 30px; }
		.components-list li { break-inside: avoid; margin-bottom: 8px; }
		@media (max-width: 1200px) { .components-list { columns: 2; } }
		@media (max-width: 768px) { .template-grid { grid-template-columns: 1fr; } .components-list { columns: 1; } }
		';
    }

    /**
     * Render admin page
     *
     * @since 1.0.0
     */
    public function render_admin_page(): void {
        $sent_template = $this->handle_test_email_send();
        ?>
        <div class="wrap email-demo-wrap">
            <div class="email-demo-header">
                <h1>📧 Email Templates Component Showcase</h1>
                <p>Complete demonstration of all <?php echo count( $this->templates ); ?> templates with all 21
                    component types. Each template includes every available component.</p>
            </div>

            <?php if ( $sent_template ) : ?>
                <div class="notice notice-success is-dismissible">
                    <p>✅ Test email sent successfully for <?php echo esc_html( $sent_template ); ?>!</p>
                </div>
            <?php endif; ?>

            <div class="template-grid">
                <?php foreach ( $this->templates as $key => $config ) : ?>
                    <?php $this->render_template_card( $config ); ?>
                <?php endforeach; ?>
            </div>

            <div class="components-card">
                <h2>📦 All 21 Components Included in Every Template</h2>
                <p>Each template demonstrates the complete component library:</p>
                <ul class="components-list">
                    <li><strong>Alerts:</strong> Success, Warning, Error, Info</li>
                    <li><strong>Buttons:</strong> Primary, Secondary, Outline</li>
                    <li><strong>Tables:</strong> Standard & Invoice</li>
                    <li><strong>Order Items:</strong> With currency support</li>
                    <li><strong>Product Lists:</strong> With images & buttons</li>
                    <li><strong>Downloads:</strong> Multi-file support</li>
                    <li><strong>Key-Value Lists:</strong> Clean data display</li>
                    <li><strong>Code Blocks:</strong> License keys & codes</li>
                    <li><strong>Coupons:</strong> Promotional offers</li>
                    <li><strong>Info Boxes:</strong> Highlighted information</li>
                    <li><strong>Progress Bars:</strong> Visual indicators</li>
                    <li><strong>Testimonials:</strong> Customer quotes</li>
                    <li><strong>Stats Grids:</strong> Metrics display</li>
                    <li><strong>Dividers:</strong> Content separation</li>
                    <li><strong>Spacers:</strong> Precise spacing</li>
                    <li><strong>Shipping Tracker:</strong> Package tracking</li>
                    <li><strong>Subscription Status:</strong> Plan details</li>
                    <li><strong>Event Details:</strong> Meeting/event info</li>
                    <li><strong>Activity Logs:</strong> Account activity</li>
                    <li><strong>Reward Balance:</strong> Points & tiers</li>
                    <li><strong>Raw HTML:</strong> Custom content</li>
                </ul>
            </div>
        </div>
        <?php
    }

    /**
     * Render individual template card
     *
     * @param array $config Template configuration
     *
     * @since 1.0.0
     */
    private function render_template_card( array $config ): void {
        $preview_url = add_query_arg( [
                'email_preview' => '1',
                'template'      => $config['key'],
                'nonce'         => wp_create_nonce( 'preview_' . $config['key'] ),
        ], home_url() );
        ?>
        <div class="template-card">
            <div class="template-header" style="background: <?php echo esc_attr( $config['color'] ); ?>;">
                <h2><?php echo esc_html( $config['title'] ); ?></h2>
                <p class="template-name"><?php echo esc_html( $config['template'] ); ?> Template</p>
            </div>
            <div class="template-body">
                <p><?php echo esc_html( $config['description'] ); ?></p>
                <form method="post">
                    <?php wp_nonce_field( 'send_test_' . $config['key'] ); ?>
                    <input type="hidden" name="template" value="<?php echo esc_attr( $config['key'] ); ?>">
                    <input type="email"
                           name="test_email"
                           class="test-email-field"
                           placeholder="Enter email address"
                           value="<?php echo esc_attr( get_option( 'admin_email' ) ); ?>"
                           required>
                    <div class="template-actions">
                        <a href="<?php echo esc_url( $preview_url ); ?>"
                           target="_blank"
                           class="button button-primary">
                            👁️ Preview
                        </a>
                        <button type="submit" name="send_test" class="button">
                            📤 Send Test
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <?php
    }

    /**
     * Handle test email sending
     *
     * @return string|null Sent template name or null
     * @since 1.0.0
     */
    private function handle_test_email_send(): ?string {
        if ( ! isset( $_POST['send_test'], $_POST['template'] ) ) {
            return null;
        }

        $template = sanitize_text_field( $_POST['template'] );

        // Verify nonce
        check_admin_referer( 'send_test_' . $template );

        // Get email address
        $email = sanitize_email( $_POST['test_email'] ?? '' );
        if ( ! $email ) {
            return null;
        }

        // Generate sample data
        $sample_data = $this->get_sample_data();

        // Send test email
        if ( send_email_template( 'demo', $template, [
                'to'      => $email,
                'data'    => $sample_data,
                'context' => 'test',
        ] ) ) {
            return $template;
        }

        return null;
    }

    /**
     * Get sample data for previews and tests
     *
     * @return object Sample data object
     * @since 1.0.0
     */
    private function get_sample_data(): object {
        return (object) [
                'name'     => 'John Doe',
                'email'    => 'john@example.com',
                'order_id' => 'ORD-' . wp_rand( 10000, 99999 ),
        ];
    }
}

// Initialize the plugin
add_action( 'plugins_loaded', function () {
    Demo::get_instance();
} );