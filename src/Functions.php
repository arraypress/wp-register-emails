<?php
/**
 * Registration
 *
 * @package     ArrayPress\RegisterEmails
 * @copyright   Copyright (c) 2026, ArrayPress Limited
 * @license     GPL2+
 * @since       2.0.0
 */

declare( strict_types=1 );

use ArrayPress\RegisterEmails\Email;
use ArrayPress\RegisterEmails\Emails;
use ArrayPress\RegisterEmails\Tag;
use ArrayPress\RegisterEmails\Tags;

if ( ! function_exists( 'register_email_tag' ) ) {
	/**
	 * Register a merge tag.
	 *
	 *     register_email_tag( 'shop', 'customer_name', [
	 *         'label'    => __( 'Customer name', 'my-plugin' ),
	 *         'callback' => fn( $order ) => $order->billing_name,
	 *         'preview'  => 'Jane Doe',
	 *     ] );
	 *
	 * What the callback returns goes in as text — a billing name with a
	 * `<b>` in it reads as one. A tag whose `type` is `html` returns markup
	 * it built itself, which goes in as it is. A tag whose `type` names a
	 * component draws that instead, and its callback returns the component's
	 * arguments:
	 *
	 *     register_email_tag( 'shop', 'view_order', [
	 *         'type'     => 'button',
	 *         'callback' => fn( $order ) => [
	 *             'text' => __( 'View your order', 'my-plugin' ),
	 *             'url'  => $order->url(),
	 *         ],
	 *     ] );
	 *
	 * @param string               $group  Whose it is. Usually the plugin.
	 * @param string               $name   Its name, without braces.
	 * @param array<string, mixed> $config Its configuration.
	 *
	 * @return Tag|null Null when it has no name.
	 */
	function register_email_tag( string $group, string $name, array $config = [] ): ?Tag {
		return Tags::add( $group, $name, $config );
	}
}

if ( ! function_exists( 'register_email' ) ) {
	/**
	 * Register an email.
	 *
	 *     register_email( 'shop', 'order_confirmation', [
	 *         'label'    => __( 'Order confirmation', 'my-plugin' ),
	 *         'subject'  => __( 'Order {order_id} confirmed', 'my-plugin' ),
	 *         'content'  => '<p>' . __( 'Thanks, {customer_name}.', 'my-plugin' ) . '</p>{view_order}',
	 *         'settings' => fn() => get_option( 'myplugin_order_email', [] ),
	 *     ] );
	 *
	 * `settings` returns whatever the site owner has configured — `subject`,
	 * `content`, `to`, `enabled`, `context` — and wins over the defaults
	 * above. Tags work in the subject as well as the body.
	 *
	 * @param string               $group  Whose it is.
	 * @param string               $name   Its name.
	 * @param array<string, mixed> $config Its configuration.
	 *
	 * @return bool
	 */
	function register_email( string $group, string $name, array $config = [] ): bool {
		return Emails::add( $group, $name, $config );
	}
}

if ( ! function_exists( 'send_registered_email' ) ) {
	/**
	 * Send a registered email.
	 *
	 *     send_registered_email( 'shop', 'order_confirmation', [
	 *         'to'   => $order->email,
	 *         'data' => $order,
	 *     ] );
	 *
	 * Returns a WP_Error rather than false, and refuses to send an email
	 * whose tags could not all be filled in — a customer receiving an order
	 * confirmation with a blank where the total should be is worse than one
	 * receiving nothing.
	 *
	 * @param string               $group     Its group.
	 * @param string               $name      Its name.
	 * @param array<string, mixed> $overrides to, data, subject, content, context.
	 *
	 * @return true|WP_Error
	 */
	function send_registered_email( string $group, string $name, array $overrides = [] ): true|WP_Error {
		return Emails::send( $group, $name, $overrides );
	}
}

if ( ! function_exists( 'compose_email' ) ) {
	/**
	 * Build an email without registering one.
	 *
	 *     compose_email()
	 *         ->to( $customer->email )
	 *         ->subject( __( 'Your download is ready', 'my-plugin' ) )
	 *         ->content( '<p>{download_link}</p>' )
	 *         ->tags( 'shop' )
	 *         ->about( $order )
	 *         ->send();
	 *
	 * @return Email
	 */
	function compose_email(): Email {
		return Email::make();
	}
}

if ( ! function_exists( 'email_tags_for_editor' ) ) {
	/**
	 * The tags of some groups, in the shape wp-field-kit's editor takes.
	 *
	 *     'body' => [
	 *         'type' => 'email_editor',
	 *         'tags' => email_tags_for_editor( [ 'shop' ] ),
	 *     ],
	 *
	 * This library owns the tags; the kit draws the chooser. One function is
	 * the whole seam between them.
	 *
	 * @param string|string[] $groups The groups.
	 *
	 * @return array<int, array{name: string, tag: string, description: string}>
	 */
	function email_tags_for_editor( string|array $groups ): array {
		return Tags::for_editor( (array) $groups );
	}
}

if ( ! function_exists( 'preview_registered_email' ) ) {
	/**
	 * What a registered email would look like, with samples in it.
	 *
	 * @param string               $group     Its group.
	 * @param string               $name      Its name.
	 * @param array<string, mixed> $overrides Anything to change first.
	 *
	 * @return string|WP_Error
	 */
	function preview_registered_email( string $group, string $name, array $overrides = [] ): string|WP_Error {
		return Emails::preview( $group, $name, $overrides );
	}
}
