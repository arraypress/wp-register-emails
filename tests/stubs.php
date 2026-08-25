<?php
/**
 * WordPress stubs.
 *
 * @package ArrayPress\RegisterEmails
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __DIR__ ) . '/' );
}

/**
 * Forget everything a previous test set up.
 *
 * @return void
 */
function re_reset_globals(): void {
	$GLOBALS['re_options'] = [ 'admin_email' => 'admin@example.test', 'date_format' => 'F j, Y', 'time_format' => 'g:i a' ];
	$GLOBALS['re_mail']    = [];
	$GLOBALS['re_sent']    = true;
	$GLOBALS['re_filters'] = [];
	$GLOBALS['re_actions'] = [];
	$GLOBALS['re_clock']   = [];

	foreach ( [ 'ArrayPress\\RegisterEmails\\Tags', 'ArrayPress\\RegisterEmails\\Emails' ] as $registry ) {
		if ( class_exists( $registry ) ) {
			$registry::forget();
		}
	}

	if ( class_exists( 'ArrayPress\\RegisterEmails\\Render\\Components' ) ) {
		ArrayPress\RegisterEmails\Render\Components::flush();
	}
}

re_reset_globals();

if ( ! function_exists( '__' ) ) {
	function __( $text, $domain = 'default' ) {
		return $text;
	}
}

if ( ! function_exists( 'esc_html' ) ) {
	function esc_html( $text ) {
		return htmlspecialchars( (string) $text, ENT_QUOTES );
	}
}

if ( ! function_exists( 'esc_attr' ) ) {
	function esc_attr( $text ) {
		return htmlspecialchars( (string) $text, ENT_QUOTES );
	}
}

if ( ! function_exists( 'esc_url' ) ) {
	function esc_url( $url, $protocols = null, $context = 'display' ) {
		return (string) $url;
	}
}

if ( ! function_exists( 'wp_kses_post' ) ) {
	function wp_kses_post( $content ) {
		return preg_replace( '@<(script|style)[^>]*?>.*?</\1>@si', '', (string) $content ) ?? '';
	}
}

if ( ! function_exists( 'sanitize_key' ) ) {
	function sanitize_key( $key ) {
		return strtolower( preg_replace( '/[^a-z0-9_\-]/i', '', (string) $key ) ?? '' );
	}
}

if ( ! function_exists( 'sanitize_email' ) ) {
	function sanitize_email( $email ) {
		return filter_var( trim( (string) $email ), FILTER_VALIDATE_EMAIL ) ?: '';
	}
}

if ( ! function_exists( 'get_option' ) ) {
	function get_option( $name, $default_value = false ) {
		return $GLOBALS['re_options'][ $name ] ?? $default_value;
	}
}

if ( ! function_exists( 'get_bloginfo' ) ) {
	function get_bloginfo( $show = '' ) {
		return [ 'name' => 'Example Shop', 'charset' => 'UTF-8' ][ $show ] ?? '';
	}
}

if ( ! function_exists( 'home_url' ) ) {
	function home_url( $path = '' ) {
		return 'https://example.test/' . ltrim( (string) $path, '/' );
	}
}

if ( ! function_exists( 'current_time' ) ) {
	/*
	 * Records what it was asked for. gmdate() and current_time() agree on a
	 * site running in UTC, which every test site is, so comparing the values
	 * cannot tell the two apart — and telling them apart is the whole
	 * question when the answer is a copyright year.
	 */
	function current_time( $type, $gmt = 0 ) {
		$GLOBALS['re_clock'][] = (string) $type;

		return 'timestamp' === $type ? time() : gmdate( (string) $type );
	}
}

if ( ! function_exists( 'esc_html__' ) ) {
	function esc_html__( $text, $domain = 'default' ) {
		return $text;
	}
}

/*
 * A theme may override an email template. Nothing here has a theme, so these
 * point at a directory that does not exist and the shipped template wins.
 */
if ( ! function_exists( 'get_stylesheet_directory' ) ) {
	function get_stylesheet_directory() {
		return sys_get_temp_dir() . '/no-such-theme';
	}
}

if ( ! function_exists( 'get_template_directory' ) ) {
	function get_template_directory() {
		return sys_get_temp_dir() . '/no-such-theme';
	}
}

if ( ! function_exists( 'trailingslashit' ) ) {
	function trailingslashit( $value ) {
		return rtrim( (string) $value, '/\\' ) . '/';
	}
}

if ( ! function_exists( 'apply_filters' ) ) {
	function apply_filters( $hook, $value, ...$args ) {
		foreach ( $GLOBALS['re_filters'][ $hook ] ?? [] as $callback ) {
			$value = $callback( $value, ...$args );
		}

		return $value;
	}
}

if ( ! function_exists( 'add_filter' ) ) {
	function add_filter( $hook, $callback, $priority = 10, $args = 1 ) {
		$GLOBALS['re_filters'][ $hook ][] = $callback;

		return true;
	}
}

if ( ! function_exists( 'do_action' ) ) {
	function do_action( $hook, ...$args ) {
		$GLOBALS['re_actions'][] = $hook;
	}
}

if ( ! function_exists( 'wp_mail' ) ) {
	function wp_mail( $to, $subject, $message, $headers = '', $attachments = [] ) {
		$GLOBALS['re_mail'][] = compact( 'to', 'subject', 'message', 'headers', 'attachments' );

		return (bool) $GLOBALS['re_sent'];
	}
}

if ( ! function_exists( 'is_wp_error' ) ) {
	function is_wp_error( $thing ) {
		return $thing instanceof WP_Error;
	}
}

if ( ! class_exists( 'WP_Error' ) ) {
	/**
	 * The parts of WP_Error this library uses.
	 */
	class WP_Error {

		/**
		 * The messages, by code.
		 *
		 * @var array<string, string>
		 */
		private array $errors = [];

		/**
		 * The data, by code.
		 *
		 * @var array<string, mixed>
		 */
		private array $data = [];

		/**
		 * Build one.
		 *
		 * @param string $code    Its code.
		 * @param string $message Its message.
		 * @param mixed  $data    Anything else worth carrying.
		 */
		public function __construct( string $code = '', string $message = '', mixed $data = null ) {
			if ( '' !== $code ) {
				$this->errors[ $code ] = $message;
				$this->data[ $code ]   = $data;
			}
		}

		/**
		 * The first code.
		 *
		 * @return string
		 */
		public function get_error_code(): string {
			return (string) array_key_first( $this->errors );
		}

		/**
		 * The first message.
		 *
		 * @return string
		 */
		public function get_error_message(): string {
			return (string) ( $this->errors[ $this->get_error_code() ] ?? '' );
		}

		/**
		 * What was carried with it.
		 *
		 * @return mixed
		 */
		public function get_error_data() {
			return $this->data[ $this->get_error_code() ] ?? null;
		}
	}
}
