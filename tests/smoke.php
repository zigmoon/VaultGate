<?php
/**
 * Integration smoke test: boots the plugin with WordPress stubs.
 *
 * Run with: php tests/smoke.php
 *
 * @package VaultGate
 */

require_once __DIR__ . '/bootstrap.php';
require_once dirname( __DIR__ ) . '/VaultGate/vaultgate.php';

$GLOBALS['vg_smoke_pass'] = 0;
$GLOBALS['vg_smoke_fail'] = 0;

function vg_smoke_assert( bool $condition, string $message ): void {
	if ( $condition ) {
		++$GLOBALS['vg_smoke_pass'];
		return;
	}

	++$GLOBALS['vg_smoke_fail'];
	echo "FAIL: {$message}\n";
}

$_SERVER = array(
	'REQUEST_URI'    => '/',
	'REMOTE_ADDR'    => '203.0.113.10',
	'HTTP_HOST'      => 'example.test',
	'REQUEST_METHOD' => 'GET',
);

do_action( 'plugins_loaded' );
do_action( 'init' );
do_action( 'template_redirect' );

vg_smoke_assert( true, 'plugin boots without a fatal error' );

VaultGate\Plugin::activate();
vg_smoke_assert( 1 === (int) get_option( 'vaultgate_auth_version' ), 'activation stores the auth version' );

$settings = new VaultGate\Settings();
$settings->set_password( 'a long enough password' );
$settings->update( array( 'enabled' => true ) );

vg_smoke_assert( $settings->is_enabled(), 'enabled + password = active' );

$_SERVER['REQUEST_URI'] = '/private';
$guard                  = new VaultGate\Guard( $settings );
vg_smoke_assert( $guard->should_gate(), 'a normal page is gated' );

$_SERVER['REQUEST_URI'] = '/robots.txt';
$guard                  = new VaultGate\Guard( $settings );
vg_smoke_assert( ! $guard->should_gate(), 'excluded path bypasses the gate' );

$GLOBALS['vg_home_url'] = 'https://example.test/blog';
$_SERVER['REQUEST_URI'] = '/blog/robots.txt';
$guard                  = new VaultGate\Guard( $settings );
vg_smoke_assert( ! $guard->should_gate(), 'exclusion works when WordPress lives in a subdirectory' );

$_SERVER['REQUEST_URI'] = '/blog/private';
$guard                  = new VaultGate\Guard( $settings );
vg_smoke_assert( $guard->should_gate(), 'normal pages stay gated in a subdirectory install' );

$GLOBALS['vg_home_url'] = 'https://example.test';
$_SERVER['REQUEST_URI'] = '/private';

$_SERVER['REQUEST_URI'] = '/wp-json/wp/v2/posts';
$guard                  = new VaultGate\Guard( $settings );
vg_smoke_assert( is_wp_error( $guard->rest_authentication_errors( null ) ), 'anonymous REST request is blocked' );

$token                         = new VaultGate\Token( $settings );
$_COOKIE['vaultgate_auth']     = $token->issue( time() + 3600 );
$guard                         = new VaultGate\Guard( $settings );
vg_smoke_assert( ! $guard->should_gate(), 'a valid gate cookie bypasses the gate' );
vg_smoke_assert( null === $guard->rest_authentication_errors( null ), 'a valid gate cookie bypasses REST' );

unset( $_COOKIE['vaultgate_auth'] );

$settings->update( array( 'ip_allowlist' => '203.0.113.10' ) );
$_SERVER['REQUEST_URI'] = '/private';
$guard                  = new VaultGate\Guard( $settings );
vg_smoke_assert( ! $guard->should_gate(), 'an allowlisted IP bypasses the gate' );

$settings->update( array( 'ip_allowlist' => '' ) );
$settings->update( array( 'protect_rest' => false ) );
$_SERVER['REQUEST_URI'] = '/wp-json/wp/v2/posts';
$guard                  = new VaultGate\Guard( $settings );
vg_smoke_assert( null === $guard->rest_authentication_errors( null ), 'REST can be left open' );

$settings->update( array( 'protect_rest' => true ) );

( new VaultGate\Admin( $settings ) )->hooks();
vg_smoke_assert( true, 'admin hooks register without a fatal error' );

$GLOBALS['vg_current_user_can'] = true;
$_GET['page']                   = 'vaultgate';

ob_start();
( new VaultGate\Admin( $settings ) )->render();
$admin_html = (string) ob_get_clean();

vg_smoke_assert( str_contains( $admin_html, 'role="tablist"' ), 'admin tabs render' );
vg_smoke_assert( str_contains( $admin_html, 'name="vaultgate[enabled]"' ), 'master switch renders' );
vg_smoke_assert( str_contains( $admin_html, 'name="vaultgate_new_password"' ), 'password field renders' );
vg_smoke_assert( str_contains( $admin_html, 'name="vaultgate_force_logout"' ), 'force-logout field renders with the expected name' );
vg_smoke_assert( str_contains( $admin_html, 'name="vaultgate[ip_allowlist]"' ), 'IP allowlist renders' );
vg_smoke_assert( str_contains( $admin_html, 'id="vg-clear-log"' ), 'clear-log form renders' );
vg_smoke_assert( str_contains( $admin_html, 'id="vg-panel-activity"' ), 'activity panel renders' );
vg_smoke_assert( str_contains( $admin_html, 'role="switch"' ), 'booleans render as switches' );
vg_smoke_assert( 8 <= substr_count( $admin_html, 'type="checkbox" role="switch"' ), 'every boolean is a switch, not a box' );
vg_smoke_assert( str_contains( $admin_html, 'name="vaultgate[enabled]"' ) && str_contains( $admin_html, 'value="0"' ), 'the switch still posts a hidden 0 with the settings array' );

/* ── Favicon on the gate ────────────────────────────────────────────────── */

$favicon = new ReflectionMethod( VaultGate\Guard::class, 'favicon_markup' );
$favicon->setAccessible( true );
$guard_object = new VaultGate\Guard( $settings );

$GLOBALS['vg_site_icon'] = array();
$fallback                = (string) $favicon->invoke( $guard_object );

vg_smoke_assert( str_contains( $fallback, 'assets/icon.svg' ), 'a site without a Site Icon gets the plugin mark' );
vg_smoke_assert( str_contains( $fallback, 'type="image/svg+xml"' ), 'the fallback favicon is the vector one' );
vg_smoke_assert( str_contains( $fallback, 'icon-64.png' ), 'a raster fallback is offered too' );
vg_smoke_assert( ! str_contains( $fallback, 'apple-touch-icon' ), 'no touch icon without a site icon' );

$GLOBALS['vg_site_icon'] = array(
	512 => 'https://example.test/wp-content/uploads/icon-512.png',
	180 => 'https://example.test/wp-content/uploads/icon-180.png',
);
$branded = (string) $favicon->invoke( $guard_object );

vg_smoke_assert( str_contains( $branded, 'icon-512.png' ), 'the WordPress Site Icon is used on the gate' );
vg_smoke_assert( str_contains( $branded, 'rel="apple-touch-icon"' ), 'the touch icon is printed for iOS' );
vg_smoke_assert( ! str_contains( $branded, 'assets/icon.svg' ), 'the plugin mark steps aside when the site has an icon' );
vg_smoke_assert( 1 === substr_count( $branded, 'rel="icon"' ), 'no duplicate icon link' );

$GLOBALS['vg_site_icon'] = array( 512 => 'https://example.test/wp-content/uploads/icon-512.png' );
$single                  = (string) $favicon->invoke( $guard_object );

vg_smoke_assert( 1 === substr_count( $single, '<link' ), 'a single site icon prints a single link' );
vg_smoke_assert( ! str_contains( $single, 'apple-touch-icon' ), 'no touch link when the 180 size is missing' );

$GLOBALS['vg_site_icon'] = array();

$_GET['vg_error'] = 'password_short,password_mismatch';
ob_start();
( new VaultGate\Admin( $settings ) )->notices();
$notices_html = (string) ob_get_clean();

vg_smoke_assert( str_contains( $notices_html, 'at least 8 characters' ), 'password length error is shown' );
vg_smoke_assert( str_contains( $notices_html, 'do not match' ), 'password mismatch error is shown' );

/* ── Credits: the plugin says where it comes from ───────────────────────── */

$admin = new VaultGate\Admin( $settings );

$row_meta = $admin->row_meta( array(), 'VaultGate/vaultgate.php' );
$row_html = implode( ' ', $row_meta );

vg_smoke_assert( 3 === count( $row_meta ), 'the plugin row gains author, developer and documentation links' );
vg_smoke_assert( str_contains( $row_html, 'https://2points13.fr' ), 'the plugin row links to 2points13.fr' );
vg_smoke_assert( str_contains( $row_html, 'https://zigmoon.com' ), 'the plugin row links to zigmoon.com' );
vg_smoke_assert( str_contains( $row_html, 'rel="noopener noreferrer"' ), 'outbound links are opened safely' );
vg_smoke_assert( array() === $admin->row_meta( array(), 'other/other.php' ), 'other plugins keep their own row' );

$GLOBALS['vg_screen_id'] = 'dashboard';
vg_smoke_assert( ! has_filter( 'admin_footer_text' ) , 'the WordPress footer is left untouched (one credit line only)' );

vg_smoke_assert( str_contains( $admin_html, 'vg-credits' ), 'the settings page ends with the credits block' );
vg_smoke_assert( str_contains( $admin_html, '2points13.fr' ) && str_contains( $admin_html, 'zigmoon.com' ), 'the credits block shows both sites' );
vg_smoke_assert( 1 === substr_count( $admin_html, 'a plugin by' ), 'the credits are printed exactly once' );

/* ── Welcome notice ─────────────────────────────────────────────────────── */

update_option( 'vaultgate_welcome', time(), false );

$_GET['page'] = 'dashboard';
ob_start();
$admin->notices();
$welcome_html = (string) ob_get_clean();

vg_smoke_assert( str_contains( $welcome_html, 'Set the password' ), 'activation points to the password field' );
vg_smoke_assert( str_contains( $welcome_html, 'Dismiss' ), 'the welcome notice can be dismissed' );
vg_smoke_assert( str_contains( $welcome_html, '2points13.fr' ), 'the welcome notice says where the plugin comes from' );

$_GET['page'] = 'vaultgate';
ob_start();
$admin->notices();
ob_end_clean();

vg_smoke_assert( false === get_option( 'vaultgate_welcome', false ), 'opening the settings screen clears the welcome notice' );

/* ── Deactivation leaves nothing behind ─────────────────────────────────── */

$version_before = (int) get_option( 'vaultgate_auth_version' );

VaultGate\Plugin::deactivate();

vg_smoke_assert( (int) get_option( 'vaultgate_auth_version' ) === $version_before + 1, 'deactivation revokes every issued cookie' );
vg_smoke_assert( false === get_option( 'vaultgate_welcome', false ), 'deactivation removes the welcome flag' );
vg_smoke_assert( true, 'deactivation runs without a fatal error and without a database table' );

define( 'VAULTGATE_DISABLED', true );
vg_smoke_assert( ! $settings->is_enabled(), 'VAULTGATE_DISABLED disables the gate' );

echo "\n" . $GLOBALS['vg_smoke_pass'] . ' passed, ' . $GLOBALS['vg_smoke_fail'] . " failed\n";

exit( $GLOBALS['vg_smoke_fail'] > 0 ? 1 : 0 );
