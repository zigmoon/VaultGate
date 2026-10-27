<?php
/**
 * Plugin Name:       VaultGate
 * Plugin URI:        https://github.com/zigmoon/passwordprotect
 * Description:       Password-protects an entire WordPress site with a quiet, Swiss-designed gate page, granular access rules and no third-party dependencies.
 * Version:           1.1.1
 * Requires at least: 6.5
 * Tested up to:      7.1
 * Requires PHP:      8.2
 * Author:            zigmoon
 * Author URI:        https://github.com/zigmoon
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       vaultgate
 * Domain Path:       /languages
 *
 * @package VaultGate
 */

namespace VaultGate;

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'VAULTGATE_VERSION' ) ) {
	define( 'VAULTGATE_VERSION', '1.1.1' );
}
if ( ! defined( 'VAULTGATE_FILE' ) ) {
	define( 'VAULTGATE_FILE', __FILE__ );
}
if ( ! defined( 'VAULTGATE_DIR' ) ) {
	define( 'VAULTGATE_DIR', plugin_dir_path( __FILE__ ) );
}
if ( ! defined( 'VAULTGATE_URL' ) ) {
	define( 'VAULTGATE_URL', plugin_dir_url( __FILE__ ) );
}
if ( ! defined( 'VAULTGATE_BASENAME' ) ) {
	define( 'VAULTGATE_BASENAME', plugin_basename( __FILE__ ) );
}

require_once VAULTGATE_DIR . 'includes/class-settings.php';
require_once VAULTGATE_DIR . 'includes/class-access-rules.php';
require_once VAULTGATE_DIR . 'includes/class-token.php';
require_once VAULTGATE_DIR . 'includes/class-rate-limiter.php';
require_once VAULTGATE_DIR . 'includes/class-guard.php';
require_once VAULTGATE_DIR . 'includes/class-admin.php';
require_once VAULTGATE_DIR . 'includes/class-cli.php';
require_once VAULTGATE_DIR . 'includes/class-plugin.php';

add_action(
	'plugins_loaded',
	static function (): void {
		Plugin::instance()->boot();
	},
	5
);

/*
 * WordPress 6.7 and later load translations just in time and report a warning
 * when a text domain is requested before `init`. Registering the catalogue on
 * `init` keeps the plugin quiet on every current version.
 */
add_action(
	'init',
	static function (): void {
		load_plugin_textdomain( 'vaultgate', false, dirname( VAULTGATE_BASENAME ) . '/languages' );
	},
	1
);

register_activation_hook( __FILE__, array( Plugin::class, 'activate' ) );
register_deactivation_hook( __FILE__, array( Plugin::class, 'deactivate' ) );
