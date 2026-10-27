<?php
/**
 * Standalone tests for the pure VaultGate components.
 *
 * Run with: php tests/run.php
 *
 * @package VaultGate
 */

use VaultGate\AccessRules;
use VaultGate\RateLimiter;
use VaultGate\Settings;
use VaultGate\Token;

require_once __DIR__ . '/bootstrap.php';
require_once VAULTGATE_DIR . 'includes/class-settings.php';
require_once VAULTGATE_DIR . 'includes/class-access-rules.php';
require_once VAULTGATE_DIR . 'includes/class-token.php';
require_once VAULTGATE_DIR . 'includes/class-rate-limiter.php';

$GLOBALS['vg_pass'] = 0;
$GLOBALS['vg_fail'] = 0;

/**
 * Asserts a condition.
 *
 * @param bool   $condition Condition.
 * @param string $message   Description.
 */
function vg_assert( bool $condition, string $message ): void {
	if ( $condition ) {
		++$GLOBALS['vg_pass'];
		return;
	}

	++$GLOBALS['vg_fail'];
	echo "FAIL: {$message}\n";
}

/**
 * Asserts strict equality.
 *
 * @param mixed  $expected Expected value.
 * @param mixed  $actual   Actual value.
 * @param string $message  Description.
 */
function vg_same( mixed $expected, mixed $actual, string $message ): void {
	vg_assert(
		$expected === $actual,
		$message . ' (expected ' . var_export( $expected, true ) . ', got ' . var_export( $actual, true ) . ')'
	);
}

/**
 * Resets the fake WordPress state.
 */
function vg_reset(): void {
	$GLOBALS['vg_options']    = array();
	$GLOBALS['vg_transients'] = array();
	$GLOBALS['vg_filters']    = array();
	$_GET                     = array();
	$_POST                    = array();
	$_COOKIE                  = array();
	$_SERVER                  = array(
		'REQUEST_URI'   => '/',
		'REMOTE_ADDR'   => '203.0.113.10',
		'HTTP_HOST'     => 'example.test',
		'REQUEST_METHOD' => 'GET',
	);
}

/**
 * Reproduces Token::sign() for expiry tests.
 *
 * @param string $context  Context.
 * @param int    $expires  Expiry.
 * @param int    $version  Auth version.
 */
function vg_sign_token( string $context, int $expires, int $version = 1 ): string {
	$secret  = hash_hmac( 'sha256', 'vaultgate|https://example.test/', 'test-salt-auth|vaultgate' );
	$payload = implode( '|', array( 'vaultgate-v1', $context, (string) $version, (string) $expires ) );

	return hash_hmac( 'sha256', $payload, $secret );
}

/* -------------------------------------------------------------------------
 * Settings: sanitisation and storage.
 * ---------------------------------------------------------------------- */

vg_reset();

$clean = Settings::sanitize(
	array(
		'enabled'           => '1',
		'gate_title'        => '  <b>Hello</b>  ',
		'gate_message'      => '<script>alert(1)</script><p>Hi</p>',
		'logo_id'           => '42',
		'logo_url'          => 'javascript:alert(1)',
		'accent'            => '#abc',
		'theme'             => 'neon',
		'session_days'      => '9999',
		'remember_enabled'  => '0',
		'remember_days'     => '0',
		'bypass_capability' => 'read',
		'ip_allowlist'      => "203.0.113.5\n300.1.1.1\n2001:db8::/32\n10.0.0.0/8\n10.0.0.0/33\n# comment",
		'exclude_paths'     => "api\n/foo bar\n/ok/*\n../etc\n# note",
		'protect_rest'      => '1',
		'protect_feeds'     => '0',
		'protect_sitemaps'  => '1',
		'protect_ajax'      => '1',
		'protect_xmlrpc'    => '0',
		'trust_proxy'       => '0',
		'max_attempts'      => '0',
		'lockout_minutes'   => '99999',
		'log_enabled'       => '1',
		'delete_data'       => '0',
	)
);

vg_same( true, $clean['enabled'], 'enabled flag is boolean true' );
vg_same( false, $clean['protect_feeds'], 'unchecked flag is boolean false' );
vg_same( true, Settings::defaults()['delete_data'], 'uninstalling removes the plugin data by default' );
vg_same( '', Settings::defaults()['gate_title'], 'defaults written to the database are never translated' );
vg_same( false, Settings::sanitize( array( 'enabled' => array( 1 ) ) )['enabled'], 'array values never enable a flag' );
vg_same( '', Settings::sanitize( array( 'gate_title' => array( 'x' ) ) )['gate_title'], 'array titles are neutralised, not stored' );
vg_same( 'Hello', $clean['gate_title'], 'title is stripped and trimmed' );
vg_same( false, str_contains( $clean['gate_message'], '<script' ), 'message script is removed' );
vg_same( false, str_contains( $clean['gate_message'], 'alert(1)' ), 'message payload is removed' );
vg_same( '', $clean['logo_url'], 'javascript: logo URL is rejected' );
vg_same( 42, $clean['logo_id'], 'media logo id is kept as an integer' );
vg_same( 0, Settings::sanitize( array( 'logo_id' => '-7' ) )['logo_id'], 'negative logo ids are clamped to zero' );
vg_same( 0, Settings::sanitize( array( 'logo_id' => array( 9 ) ) )['logo_id'], 'array logo ids are rejected' );
vg_same( '', Settings::sanitize( array( 'gate_message' => '' ) )['gate_message'], 'the message can stay empty' );
vg_same( '#aabbcc', $clean['accent'], '3-digit hex is expanded' );
vg_same( 'light', $clean['theme'], 'unknown theme falls back to light' );
vg_same( 365, $clean['session_days'], 'session days are capped' );
vg_same( 30, $clean['remember_days'], 'an empty duration falls back to the default' );
vg_same( 5, $clean['max_attempts'], 'an empty attempt count falls back to the default' );
vg_same( 1, Settings::sanitize( array( 'max_attempts' => '1' ) )['max_attempts'], 'explicit attempt counts are kept' );
vg_same( 1440, $clean['lockout_minutes'], 'lockout is capped' );
vg_same( 'read', $clean['bypass_capability'], 'known capability is kept' );
vg_same( '#0b0d10', Settings::sanitize( array( 'accent' => 'rebeccapurple' ) )['accent'], 'invalid colours fall back to black' );

vg_same( '#ffffff', Settings::ink_on( '#0b0d10' ), 'white ink on black' );
vg_same( '#0b0d10', Settings::ink_on( '#f4f4f5' ), 'black ink on white' );
vg_same( '#f4f4f5', Settings::accent_for_dark( '#0b0d10' ), 'a black accent turns white on the dark theme' );
vg_same( '#0a66ff', Settings::accent_for_dark( '#0a66ff' ), 'a saturated accent is kept on the dark theme' );
vg_assert( Settings::luminance( '#ffffff' ) > 0.9, 'white luminance is close to 1' );
vg_assert( Settings::luminance( '#000000' ) < 0.01, 'black luminance is close to 0' );
vg_assert( count( Settings::swatches() ) >= 6, 'a palette of at least six presets is offered' );
vg_same( '#0b0d10', Settings::swatches()[0]['value'], 'the first preset is black' );

vg_assert( str_contains( $clean['ip_allowlist'], '203.0.113.5' ), 'valid IPv4 is kept' );
vg_assert( str_contains( $clean['ip_allowlist'], '2001:db8::/32' ), 'valid IPv6 range is kept' );
vg_assert( str_contains( $clean['ip_allowlist'], '10.0.0.0/8' ), 'valid IPv4 range is kept' );
vg_assert( ! str_contains( $clean['ip_allowlist'], '300.1.1.1' ), 'invalid IPv4 is rejected' );
vg_assert( ! str_contains( $clean['ip_allowlist'], '/33' ), 'invalid IPv4 prefix is rejected' );

vg_assert( str_contains( $clean['exclude_paths'], '/api' ), 'path gets a leading slash' );
vg_assert( str_contains( $clean['exclude_paths'], '/ok/*' ), 'wildcard path is kept' );
vg_assert( ! str_contains( $clean['exclude_paths'], 'foo bar' ), 'paths with spaces are rejected' );
vg_assert( ! str_contains( $clean['exclude_paths'], '..' ), 'parent traversal is rejected' );
vg_assert( ! str_contains( $clean['exclude_paths'], '# note' ), 'comments are rejected' );

vg_same( '#aabbcc', Settings::normalize_hex( '#ABC' ), 'normalize_hex expands case-insensitively' );

vg_reset();

$settings = new Settings();
vg_same( false, $settings->is_enabled(), 'protection is off when there is no password' );
vg_same( false, $settings->set_password( 'short' ), 'short passwords are rejected' );
vg_same( false, Settings::is_password_long_enough( 'éééé' ), 'password length counts characters, not bytes' );
vg_same( true, Settings::is_password_long_enough( 'éééééééé' ), 'eight multibyte characters are accepted' );
vg_same( false, $settings->has_password(), 'no hash is stored for a short password' );
vg_same( true, $settings->set_password( 'correct horse battery staple' ), 'long password is stored' );
vg_same( true, $settings->has_password(), 'hash exists after storing' );
vg_same( true, $settings->verify_password( 'correct horse battery staple' ), 'correct password verifies' );
vg_same( false, $settings->verify_password( 'wrong password' ), 'wrong password fails' );
vg_same( 2, $settings->auth_version(), 'setting a password bumps the auth version' );

$settings->update( array( 'enabled' => true ) );
vg_same( true, $settings->is_enabled(), 'enabled + password activates the gate' );

vg_same( 'manage_options', Settings::sanitize( array( 'bypass_capability' => 'not_a_real_cap' ) )['bypass_capability'], 'unknown capability falls back to manage_options' );

$settings->log( 'login_failed', '203.0.113.10' );
$logs = $settings->logs();
vg_same( 1, count( $logs ), 'log stores one entry' );
vg_same( 'login_failed', $logs[0]['event'], 'log event is stored' );
vg_same( '203.0.113.0', $logs[0]['ip'], 'logged IP is anonymised' );

$settings->clear_log();
vg_same( array(), $settings->logs(), 'clearing removes entries' );

/* -------------------------------------------------------------------------
 * Token: signatures, expiry, version invalidation, filters.
 * ---------------------------------------------------------------------- */

vg_reset();
$settings = new Settings();
$token    = new Token( $settings );

$issued = $token->issue( time() + 3600 );
vg_assert( $token->verify( $issued ), 'fresh cookie token verifies' );
vg_assert( ! $token->verify( $issued . '0' ), 'tampered cookie token fails' );
vg_assert( ! $token->verify( 'not-a-token' ), 'garbage token fails' );
vg_assert( ! $token->verify( '9999999999.' . str_repeat( 'a', 64 ) ), 'forged token fails' );

$expired_expiry = time() - 30;
$expired        = $expired_expiry . '.' . vg_sign_token( 'cookie', $expired_expiry );
vg_assert( ! $token->verify( $expired ), 'expired token fails' );

$form = $token->form_token();
vg_assert( $token->verify_form_token( $form ), 'form token verifies' );
vg_assert( ! $token->verify_form_token( $form . 'f' ), 'tampered form token fails' );
vg_assert( ! $token->verify_form_token( $issued ), 'cookie token is not a form token' );

$issued_before_bump = $token->issue( time() + 3600 );
$settings->bump_auth_version();
vg_assert( ! $token->verify( $issued_before_bump ), 'password/session version invalidates existing tokens' );

$GLOBALS['vg_filters']['vaultgate_cookie_name'] = array(
	10 => array(
		static function ( string $name ): string {
			return 'bad name!';
		},
	),
);
vg_same( 'badname', Token::cookie_name(), 'cookie name filter is sanitised' );
vg_same( 'vaultgate_auth', Token::COOKIE, 'default cookie name is stable' );

/* -------------------------------------------------------------------------
 * Access rules.
 * ---------------------------------------------------------------------- */

vg_assert( AccessRules::ip_matches( '203.0.113.10', '203.0.113.0/24' ), 'IPv4 /24 matches' );
vg_assert( ! AccessRules::ip_matches( '203.0.114.10', '203.0.113.0/24' ), 'IPv4 outside /24 fails' );
vg_assert( AccessRules::ip_matches( '10.0.0.255', '10.0.0.0/24' ), 'IPv4 boundary matches' );
vg_assert( ! AccessRules::ip_matches( '10.0.1.0', '10.0.0.0/24' ), 'IPv4 just outside /24 fails' );
vg_assert( AccessRules::ip_matches( '2001:db8::5', '2001:db8::/32' ), 'IPv6 /32 matches' );
vg_assert( ! AccessRules::ip_matches( '2001:db9::5', '2001:db8::/32' ), 'IPv6 outside /32 fails' );
vg_assert( ! AccessRules::ip_matches( '10.0.0.1', '2001:db8::/32' ), 'mixed families fail' );
vg_assert( AccessRules::ip_matches( '192.0.2.7', "192.0.2.7\n198.51.100.1" ), 'exact IP matches' );
vg_assert( ! AccessRules::ip_matches( '', '0.0.0.0/0' ), 'empty IP never matches' );

vg_assert( AccessRules::ip_in_range( '192.0.2.255', '192.0.2.0/24' ), 'range helper matches' );
vg_assert( ! AccessRules::ip_in_range( '192.0.2.1', '192.0.2.0/33' ), 'invalid prefix fails' );

vg_assert( AccessRules::path_matches( '/robots.txt', '/robots.txt' ), 'exact path matches' );
vg_assert( AccessRules::path_matches( '/robots.txt/extra', '/robots.txt' ), 'path prefix matches' );
vg_assert( ! AccessRules::path_matches( '/robots.txtx', '/robots.txt' ), 'path boundary is respected' );
vg_assert( AccessRules::path_matches( '/api/v1/users', '/api/*' ), 'wildcard matches nested path' );
vg_assert( AccessRules::path_matches( '/api', '/api/*' ), 'wildcard also matches its base path' );
vg_assert( ! AccessRules::path_matches( '/apix', '/api' ), 'prefix requires a slash boundary' );
vg_assert( AccessRules::path_matches( '/foo', '/foo/' ), 'trailing slash is normalised' );
vg_assert( ! AccessRules::path_matches( '/FOO', '/foo' ), 'paths remain case-sensitive' );
vg_assert( AccessRules::path_matches( '/', '/' ), 'root path matches' );

vg_same( '/api/x', AccessRules::strip_base( '/blog/api/x', '/blog' ), 'subdirectory base is stripped' );
vg_same( '/', AccessRules::strip_base( '/blog', '/blog' ), 'installation root becomes /' );
vg_same( '/', AccessRules::strip_base( '/blog/', '/blog/' ), 'trailing slash base becomes /' );
vg_same( '/blogging/x', AccessRules::strip_base( '/blogging/x', '/blog' ), 'similar prefix is not stripped' );
vg_same( '/foo', AccessRules::strip_base( '/foo', false ), 'unknown base leaves the path untouched' );
vg_same( '/foo', AccessRules::strip_base( '/foo', '/' ), 'root base leaves the path untouched' );
vg_assert( AccessRules::path_matches( AccessRules::strip_base( '/blog/robots.txt', '/blog' ), '/robots.txt' ), 'exclusion works in a subdirectory install' );

vg_same( '/foo bar/', AccessRules::request_path( '/foo%20bar/?x=1' ), 'request path is decoded' );
vg_same( '/a/b', AccessRules::request_path( '/a//b' ), 'duplicate slashes are collapsed' );
vg_same( '/', AccessRules::request_path( '' ), 'empty URI becomes root' );
vg_same( '/wp-comments-post.php/', AccessRules::request_path( '/wp-comments-post.php/x/..' ), 'dot segments cannot hide a blocked endpoint' );
vg_same( '/wp-admin/admin-post.php', AccessRules::request_path( '/wp-admin/./admin-post.php' ), 'single dot segments are resolved' );
vg_same( '/etc', AccessRules::request_path( '/a/../../etc' ), 'parent segments clamp at the root' );
vg_same( '/webhooks/a', AccessRules::request_path( '/webhooks/a/../a' ), 'parent segments resolve inside the path' );
vg_same( '/webhooks/', AccessRules::request_path( '/webhooks/x/..' ), 'a cancelled segment leaves the parent folder as the server sees it' );
vg_same( '/', AccessRules::request_path( '/../..' ), 'a path made of parent segments becomes root' );
vg_same( '/', AccessRules::request_path( '/x/..' ), 'cancelling the only segment yields root' );
vg_assert(
	! AccessRules::path_matches( AccessRules::request_path( '/api/x/../../secret' ), '/api/*' ),
	'an exclusion cannot be triggered by a dot segment'
);
vg_assert(
	AccessRules::path_matches( AccessRules::request_path( '/webhooks/x/..' ), '/webhooks/*' ),
	'the normalised path is what the exclusion sees'
);

/* ── Client IP: proxy headers are only trusted from a trusted peer ─────── */

$_SERVER = array(
	'REMOTE_ADDR' => '203.0.113.10',
	'REQUEST_URI' => '/',
);

$_SERVER['HTTP_X_FORWARDED_FOR']   = '198.51.100.7, 10.0.0.1';
$_SERVER['HTTP_X_FORWARDED_PROTO'] = 'https';

vg_same( '203.0.113.10', AccessRules::client_ip( false ), 'proxy headers are ignored by default' );
vg_same( '203.0.113.10', AccessRules::client_ip( true ), 'headers from a public, untrusted peer are ignored' );
vg_same( '203.0.113.10', AccessRules::client_ip( true, '10.0.0.0/8' ), 'a peer outside the trusted list is ignored' );
vg_assert( AccessRules::is_https( true ), 'forwarded https is trusted when enabled' );
vg_assert( ! AccessRules::is_https( false ), 'forwarded https is ignored by default' );

// Spoofing: the visitor prepends an allowed address; the proxy appends the real one.
$_SERVER['REMOTE_ADDR']          = '10.0.0.1';
$_SERVER['HTTP_X_FORWARDED_FOR'] = '127.0.0.1, 198.51.100.7';
vg_same( '198.51.100.7', AccessRules::client_ip( true, '10.0.0.0/8' ), 'the appended hop wins over a prepended one' );
vg_same( '198.51.100.7', AccessRules::client_ip( true ), 'a private peer is a trusted proxy by default' );

// Several trusted proxies: keep walking left until the first untrusted hop.
$_SERVER['HTTP_X_FORWARDED_FOR'] = '127.0.0.1, 198.51.100.7, 10.0.0.2, 10.0.0.1';
vg_same( '198.51.100.7', AccessRules::client_ip( true, '10.0.0.0/8' ), 'trusted hops are skipped from the right' );

// A chain of only trusted proxies falls back to the nearest peer.
$_SERVER['HTTP_X_FORWARDED_FOR'] = '10.0.0.2, 10.0.0.1';
vg_same( '10.0.0.1', AccessRules::client_ip( true, '10.0.0.0/8' ), 'an all-trusted chain falls back to the peer' );

// Single-value proxy headers are attacker-controlled: a plain nginx proxy
// passes them through untouched, so they must be ignored.
$_SERVER['HTTP_X_FORWARDED_FOR']  = '198.51.100.7';
$_SERVER['HTTP_CF_CONNECTING_IP'] = '203.0.113.99';
$_SERVER['HTTP_TRUE_CLIENT_IP']   = '203.0.113.98';
$_SERVER['HTTP_X_REAL_IP']        = '203.0.113.97';
vg_same( '198.51.100.7', AccessRules::client_ip( true, '10.0.0.0/8' ), 'forged single-value headers are ignored even from a trusted peer' );

unset( $_SERVER['HTTP_CF_CONNECTING_IP'], $_SERVER['HTTP_TRUE_CLIENT_IP'], $_SERVER['HTTP_X_REAL_IP'] );
$_SERVER['HTTP_X_FORWARDED_FOR'] = 'not-an-ip, 10.0.0.1';
vg_same( '10.0.0.1', AccessRules::client_ip( true, '10.0.0.0/8' ), 'invalid hops are dropped before matching' );

vg_assert( AccessRules::is_private_ip( '10.1.2.3' ), 'a private IPv4 address is private' );
vg_assert( AccessRules::is_private_ip( '::1' ), 'loopback IPv6 is private' );
vg_assert( ! AccessRules::is_private_ip( '198.51.100.7' ), 'a public address is not private' );
vg_assert( ! AccessRules::is_private_ip( '' ), 'an empty address is not private' );

/* ── Direct endpoints: PATH_INFO cannot hide a blocked script ──────────── */

$vg_endpoints = array( '/wp-comments-post.php', '/wp-admin/admin-post.php' );

vg_assert( AccessRules::is_blocked_endpoint( '/wp-comments-post.php', $vg_endpoints ), 'the script itself is blocked' );
vg_assert( AccessRules::is_blocked_endpoint( '/wp-comments-post.php/', $vg_endpoints ), 'a trailing slash does not unblock it' );
vg_assert( AccessRules::is_blocked_endpoint( '/wp-comments-post.php/anything', $vg_endpoints ), 'PATH_INFO does not unblock it' );
vg_assert( AccessRules::is_blocked_endpoint( '/blog/wp-comments-post.php/anything', $vg_endpoints ), 'a subdirectory does not unblock it' );
vg_assert( AccessRules::is_blocked_endpoint( '/wp-admin/admin-post.php?x=1', array( '/wp-admin/admin-post.php' ) ) === false, 'a query string is not part of the path' );
vg_assert( AccessRules::is_blocked_endpoint( '/wp-admin/admin-post.php/extra', $vg_endpoints ), 'admin-post is blocked behind PATH_INFO' );
vg_assert( ! AccessRules::is_blocked_endpoint( '/', $vg_endpoints ), 'the homepage is not a blocked endpoint' );
vg_assert( ! AccessRules::is_blocked_endpoint( '/wp-comments-post.php.bak', $vg_endpoints ), 'a look-alike script is not blocked' );
vg_assert( ! AccessRules::is_blocked_endpoint( '/notes/wp-comments-post.phpx', $vg_endpoints ), 'a longer name is not blocked' );
vg_assert( ! AccessRules::is_blocked_endpoint( '/about', array( '/' ) ), 'a root endpoint never blocks everything' );

/* -------------------------------------------------------------------------
 * Rate limiter.
 * ---------------------------------------------------------------------- */

vg_reset();
$limiter = new RateLimiter( 3, 10 );

vg_same( 0, $limiter->seconds_remaining( '198.51.100.7' ), 'no lock-out initially' );
vg_same( 0, $limiter->register_failure( '198.51.100.7' ), 'first failure does not lock' );
vg_same( 0, $limiter->register_failure( '198.51.100.7' ), 'second failure does not lock' );
$locked = $limiter->register_failure( '198.51.100.7' );
vg_assert( $locked > 0, 'third failure locks the IP' );
vg_assert( $limiter->seconds_remaining( '198.51.100.7' ) > 0, 'lock-out is reported' );
vg_same( 0, $limiter->seconds_remaining( '198.51.100.8' ), 'other IPs are unaffected' );

$keys = array_keys( $GLOBALS['vg_transients'] );
vg_assert( array() !== $keys, 'limiter stores a transient' );
vg_assert( ! str_contains( (string) $keys[0], '198.51.100.7' ), 'transient key never contains the IP' );

$limiter->clear( '198.51.100.7' );
vg_same( 0, $limiter->seconds_remaining( '198.51.100.7' ), 'clear removes the lock-out' );

$limiter = new RateLimiter( 1, 5 );
vg_assert( $limiter->register_failure( '192.0.2.1' ) > 0, 'max_attempts=1 locks immediately' );

/* -------------------------------------------------------------------------
 * Gate template.
 * ---------------------------------------------------------------------- */

vg_reset();

$vg = array(
	'title'           => 'Private <script>alert(1)</script>',
	'message'         => '<script>alert(1)</script><p>Welcome &amp; hello</p>',
	'logo'            => '<img class="vg-logo" src="https://example.test/logo.png?x=1&amp;y=2" alt="Test &amp; Site">',
	'accent'          => '#0b0d10',
	'accent_ink'      => '#ffffff',
	'accent_dark'     => '#f4f4f5',
	'accent_dark_ink' => '#0b0d10',
	'theme'           => 'auto',
	'nonce'           => 'abc123',
	'action'          => 'https://example.test/?vaultgate=login',
	'redirect'        => 'https://example.test/protected?x=1&y=2',
	'form_token'      => '123.abc',
	'remember'        => true,
	'remember_days'   => 30,
	'notice'          => 'Incorrect "password" <b>',
	'invalid'         => true,
	'lock_seconds'    => 0,
	'site_name'       => 'Test & Site',
	'favicon'         => '<link rel="icon" href="https://example.test/wp-content/uploads/icon-512.png?s=1&amp;t=2" sizes="any">',
	'style_url'       => 'https://example.test/gate.css',
	'script_url'      => 'https://example.test/gate.js',
);

ob_start();
require VAULTGATE_DIR . 'templates/gate.php';
$html = (string) ob_get_clean();

vg_assert( str_contains( $html, '<form' ), 'template renders the form' );
vg_assert( str_contains( $html, 'name="vaultgate_password"' ), 'template renders the password field' );
vg_assert( str_contains( $html, 'name="vaultgate_website"' ), 'template renders the honeypot' );
vg_assert( str_contains( $html, 'name="vaultgate_form_token"' ), 'template renders the signed form token' );
vg_assert( str_contains( $html, '--vg-accent: #0b0d10' ), 'template injects the accent colour' );
vg_assert( str_contains( $html, '--vg-accent-ink: #ffffff' ), 'template injects the contrast colour' );
vg_assert( str_contains( $html, '--vg-accent-dark: #f4f4f5' ), 'template injects the dark-theme accent' );
vg_assert( str_contains( $html, 'vg-theme-auto' ), 'template applies the theme class' );
vg_assert( str_contains( $html, 'nonce="abc123"' ), 'template uses the CSP nonce' );
vg_assert( str_contains( $html, 'href="https://example.test/gate.css"' ), 'template links the local stylesheet' );
vg_assert( str_contains( $html, 'src="https://example.test/gate.js"' ), 'template links the local script' );
vg_assert( str_contains( $html, 'rel="icon"' ), 'template prints the favicon links' );
vg_assert( str_contains( $html, 'icon-512.png?s=1&amp;t=2' ), 'the favicon markup is printed as prepared, still escaped' );
vg_assert( str_contains( $html, 'data-label-show="Show password"' ), 'template passes translated reveal labels' );
vg_assert( str_contains( $html, 'class="vg-eye vg-eye--open"' ) && str_contains( $html, 'vg-eye--off' ), 'the reveal control is an eye, both states in the markup' );
vg_assert( str_contains( $html, 'aria-label="Show password"' ), 'the eye is labelled for assistive technology' );
vg_assert( ! str_contains( $html, 'data-vg-reveal-label' ), 'the old text toggle is gone' );
vg_assert( ! str_contains( $html, '<script>alert' ), 'template never echoes unescaped scripts' );
vg_assert( 1 === substr_count( $html, '<script' ), 'template contains only the external script tag' );
vg_assert( str_contains( $html, '&lt;script&gt;' ), 'title is HTML-escaped' );
vg_assert( str_contains( $html, '&quot;password&quot; &lt;b&gt;' ), 'notice is HTML-escaped' );
vg_assert( str_contains( $html, 'Test &amp; Site' ), 'site name is HTML-escaped' );
vg_assert( str_contains( $html, 'class="vg-logo"' ), 'template renders the prepared logo markup' );
vg_assert( str_contains( $html, 'aria-invalid="true"' ), 'invalid attempts mark the field' );
vg_assert( str_contains( $html, 'value="https://example.test/protected?x=1&amp;y=2"' ), 'redirect target is escaped' );
vg_assert( str_contains( $html, 'Keep me signed in for 30 days' ), 'remember-me label is rendered' );
vg_assert( ! str_contains( $html, '<footer' ), 'template has no footer' );
vg_assert( ! str_contains( $html, 'Need access' ), 'template has no support link' );
vg_assert( ! str_contains( $html, 'under construction' ), 'template has no default marketing sentence' );

/* A quiet gate: no message, no logo, no notice. */
$vg_quiet = array_merge(
	$vg,
	array(
		'message' => '',
		'logo'    => '',
		'notice'  => '',
		'invalid' => false,
	)
);

unset( $vg );

$vg = $vg_quiet;

ob_start();
require VAULTGATE_DIR . 'templates/gate.php';
$html = (string) ob_get_clean();

vg_assert( ! str_contains( $html, 'vg-message' ), 'an empty message renders no paragraph' );
vg_assert( ! str_contains( $html, 'vg-alert' ), 'no notice renders no alert' );
vg_assert( ! str_contains( $html, 'class="vg-logo"' ), 'no logo renders no image' );
vg_assert( str_contains( $html, 'class="vg-mark"' ), 'no logo falls back to the built-in mark' );

/* -------------------------------------------------------------------------
 * Report.
 * ---------------------------------------------------------------------- */

echo "\n" . $GLOBALS['vg_pass'] . ' passed, ' . $GLOBALS['vg_fail'] . " failed\n";

exit( $GLOBALS['vg_fail'] > 0 ? 1 : 0 );
