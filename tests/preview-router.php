<?php
/**
 * Router for the local preview server (development only).
 *
 *   php -S 127.0.0.1:8450 tests/preview-router.php
 *
 * Serves the plugin assets, the icon sources and the design mock-ups; any
 * other path renders the gate page.
 *
 * @package VaultGate
 */

$vg_root = realpath( dirname( __DIR__ ) );
$vg_path = (string) parse_url( $_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH );
$vg_file = realpath( $vg_root . '/' . ltrim( rawurldecode( $vg_path ), '/' ) );

if ( false !== $vg_file && false !== $vg_root && str_starts_with( $vg_file, $vg_root ) && is_file( $vg_file ) ) {
	return false;
}

if ( str_starts_with( $vg_path, '/admin-preview' ) ) {
	require __DIR__ . '/admin-preview.php';

	return;
}

require __DIR__ . '/preview.php';
