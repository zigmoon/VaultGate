<?php
/**
 * Plugin bootstrap.
 *
 * @package VaultGate
 */

namespace VaultGate;

defined( 'ABSPATH' ) || exit;

/**
 * Wires the plugin components together.
 */
final class Plugin {

	/**
	 * Singleton.
	 *
	 * @var self|null
	 */
	private static ?self $instance = null;

	/**
	 * Settings.
	 *
	 * @var Settings|null
	 */
	private ?Settings $settings = null;

	/**
	 * Returns the singleton.
	 */
	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Registers the runtime hooks once.
	 */
	public function boot(): void {
		if ( null !== $this->settings ) {
			return;
		}

		$this->settings = new Settings();

		( new Guard( $this->settings ) )->hooks();

		if ( function_exists( 'is_admin' ) && is_admin() ) {
			( new Admin( $this->settings ) )->hooks();
		}

		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			Cli::register( $this->settings );
		}
	}

	/**
	 * Activation: store the session version and plugin version. Protection is
	 * deliberately left off so that activating cannot lock the site by accident.
	 * A flag is set so the dashboard can point to the password field once.
	 */
	public static function activate(): void {
		if ( false === get_option( Settings::AUTH_VERSION_OPTION, false ) ) {
			add_option( Settings::AUTH_VERSION_OPTION, 1, '', false );
		}

		update_option( Settings::VERSION_OPTION, VAULTGATE_VERSION, false );
		update_option( Settings::WELCOME_OPTION, time(), false );
	}

	/**
	 * Deactivation leaves the site exactly as it was before the plugin existed:
	 * the gate stops, issued cookies are revoked, and the short-lived
	 * transients are removed. Deleting the plugin afterwards removes the rest
	 * (see uninstall.php) — no table, no rewrite rule, no .htaccess, no
	 * modification of the database schema.
	 */
	public static function deactivate(): void {
		$settings = new Settings();

		$settings->bump_auth_version();
		$settings->purge_transients();

		delete_option( Settings::WELCOME_OPTION );
	}
}
