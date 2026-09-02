<?php
/**
 * Emails
 *
 * @package     ArrayPress\RegisterEmails
 * @copyright   Copyright (c) 2026, ArrayPress Limited
 * @license     GPL2+
 * @since       2.0.0
 */

declare( strict_types=1 );

namespace ArrayPress\RegisterEmails;

use WP_Error;

/**
 * The emails a plugin can send, registered once and sent by name.
 *
 * A registered email is a default subject, a default body and the tag groups
 * that apply to it. What actually goes out is usually whatever the site's
 * administrator has since typed into a settings screen, which is what
 * `settings` is for: a callback that returns their version, or nothing if
 * they have not changed it.
 *
 * Which is the arrangement worth having, because it means the plugin author
 * writes the email once and the site owner edits it without either of them
 * having to know about the other.
 */
final class Emails {

	/**
	 * The emails, by group and name.
	 *
	 * @var array<string, array<string, array<string, mixed>>>
	 */
	private static array $emails = [];

	/**
	 * Register an email.
	 *
	 * @param string               $group  Whose it is.
	 * @param string               $name   Its name.
	 * @param array<string, mixed> $config Its configuration.
	 *
	 * @return bool
	 */
	public static function add( string $group, string $name, array $config ): bool {
		$group = sanitize_key( $group );
		$name  = sanitize_key( $name );

		if ( '' === $group || '' === $name ) {
			return false;
		}

		self::$emails[ $group ][ $name ] = array_merge(
			[
				'label'       => ucfirst( str_replace( '_', ' ', $name ) ),
				'description' => '',
				'subject'     => '',
				'content'     => '',
				'template'    => 'default',
				'context'     => [],

				// Whose tags apply. The email's own group by default, which
				// is what almost every email wants and what nobody should
				// have to write down.
				'tag_groups'  => [ $group ],

				// Returns what the site owner has configured: subject,
				// content, to, enabled, and anything the template shows.
				'settings'    => null,
			],
			$config
		);

		return true;
	}

	/**
	 * One registered email.
	 *
	 * @param string $group Its group.
	 * @param string $name  Its name.
	 *
	 * @return array<string, mixed>|null
	 */
	public static function get( string $group, string $name ): ?array {
		return self::$emails[ sanitize_key( $group ) ][ sanitize_key( $name ) ] ?? null;
	}

	/**
	 * A group's emails.
	 *
	 * @param string $group The group.
	 *
	 * @return array<string, array<string, mixed>>
	 */
	public static function group( string $group ): array {
		return self::$emails[ sanitize_key( $group ) ] ?? [];
	}

	/**
	 * Build one, ready to send or look at.
	 *
	 * @param string               $group     Its group.
	 * @param string               $name      Its name.
	 * @param array<string, mixed> $overrides to, subject, content, context, data.
	 *
	 * @return Email|WP_Error
	 */
	public static function compose( string $group, string $name, array $overrides = [] ): Email|WP_Error {
		$config = self::get( $group, $name );

		if ( null === $config ) {
			return self::unknown( $group, $name );
		}

		return self::build( $config, self::settings( $config ), $overrides );
	}

	/**
	 * Send one.
	 *
	 * @param string               $group     Its group.
	 * @param string               $name      Its name.
	 * @param array<string, mixed> $overrides to, subject, content, context, data.
	 *
	 * @return true|WP_Error
	 */
	public static function send( string $group, string $name, array $overrides = [] ): true|WP_Error {
		$config = self::get( $group, $name );

		if ( null === $config ) {
			return self::unknown( $group, $name );
		}

		$settings = self::settings( $config );

		// Turned off by the site owner. Not an error — it is a setting, and
		// reporting it as a failure would fill a log with somebody's
		// preference.
		if ( array_key_exists( 'enabled', $settings ) && ! $settings['enabled'] ) {
			return true;
		}

		return self::build( $config, $settings, $overrides )->send();
	}

	/**
	 * What one would look like, with samples in it.
	 *
	 * @param string               $group     Its group.
	 * @param string               $name      Its name.
	 * @param array<string, mixed> $overrides Anything to change first.
	 *
	 * @return string|WP_Error
	 */
	public static function preview( string $group, string $name, array $overrides = [] ): string|WP_Error {
		$email = self::compose( $group, $name, $overrides );

		return $email instanceof WP_Error ? $email : $email->preview();
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
			self::$emails = [];

			return;
		}

		unset( self::$emails[ sanitize_key( $group ) ] );
	}

	/**
	 * What the site owner has configured for one email.
	 *
	 * Asked once per send. It used to be asked twice — once to see whether
	 * the email was turned on, and again to build it — which is nothing for
	 * a callback that reads an option and something for one that runs a
	 * query.
	 *
	 * @param array<string, mixed> $config The registered email.
	 *
	 * @return array<string, mixed>
	 */
	private static function settings( array $config ): array {
		return is_callable( $config['settings'] ) ? (array) call_user_func( $config['settings'] ) : [];
	}

	/**
	 * Put one together from its registration, its settings and the call.
	 *
	 * @param array<string, mixed> $config    The registered email.
	 * @param array<string, mixed> $settings  What the site owner configured.
	 * @param array<string, mixed> $overrides to, subject, content, context, data.
	 *
	 * @return Email
	 */
	private static function build( array $config, array $settings, array $overrides ): Email {
		// The site owner's version wins over the plugin author's default, and
		// an override given at the call site wins over both — that is the one
		// the code sending it chose deliberately.
		$subject = (string) ( $overrides['subject'] ?? $settings['subject'] ?? $config['subject'] );
		$content = (string) ( $overrides['content'] ?? $settings['content'] ?? $config['content'] );

		$email = Email::make()
			->subject( $subject )
			->content( $content )
			->template( (string) ( $settings['template'] ?? $config['template'] ) )
			->tags( (array) $config['tag_groups'] )
			->context( array_merge( (array) $config['context'], (array) ( $settings['context'] ?? [] ), (array) ( $overrides['context'] ?? [] ) ) );

		// The same order for the recipient. A receipt goes to whoever the
		// call names; a sale notification goes to whoever the site owner
		// typed into the settings screen.
		if ( isset( $overrides['to'] ) ) {
			$email->to( $overrides['to'] );
		} elseif ( isset( $settings['to'] ) ) {
			$email->to( $settings['to'] );
		}

		if ( array_key_exists( 'data', $overrides ) ) {
			$email->about( $overrides['data'] );
		}

		return $email;
	}

	/**
	 * The error for an email nobody registered.
	 *
	 * @param string $group The group it was looked for in.
	 * @param string $name  Its name.
	 *
	 * @return WP_Error
	 */
	private static function unknown( string $group, string $name ): WP_Error {
		return new WP_Error(
			'no_such_email',
			sprintf(
				/* translators: 1: an email's name, 2: the group it was looked for in. */
				__( 'There is no email called %1$s registered for %2$s.', 'arraypress' ),
				$name,
				$group
			)
		);
	}
}
