<?php
/**
 * Gate page preview with query switches (development only).
 *
 *   /                     light theme, no logo
 *   /?theme=dark          dark theme
 *   /?theme=auto          follow the system
 *   /?accent=%230a66ff    custom accent colour
 *   /?logo=1              sample logo in place of the built-in mark
 *   /?notice=1            error state
 *
 * @package VaultGate
 */

require_once __DIR__ . '/bootstrap.php';
require_once VAULTGATE_DIR . 'includes/class-settings.php';

$vg_theme  = isset( $_GET['theme'] ) ? (string) $_GET['theme'] : 'light';
$vg_accent = isset( $_GET['accent'] ) ? (string) $_GET['accent'] : '#0b0d10';
$vg_logo   = ! empty( $_GET['logo'] );

$vg_theme  = in_array( $vg_theme, array( 'light', 'dark', 'auto' ), true ) ? $vg_theme : 'light';
$vg_accent = preg_match( '/^#[0-9a-f]{6}$/i', $vg_accent ) ? strtolower( $vg_accent ) : '#0b0d10';

$vg_accent_dark = VaultGate\Settings::accent_for_dark( $vg_accent );

$vg = array(
	'title'           => isset( $_GET['title'] ) ? (string) $_GET['title'] : 'Site privé',
	'message'         => '',
	'logo'            => $vg_logo ? '<img class="vg-logo" src="/assets/logo-sample.svg" alt="" decoding="async">' : '',
	'accent'          => $vg_accent,
	'accent_ink'      => VaultGate\Settings::ink_on( $vg_accent ),
	'accent_dark'     => $vg_accent_dark,
	'accent_dark_ink' => VaultGate\Settings::ink_on( $vg_accent_dark ),
	'theme'           => $vg_theme,
	'nonce'           => 'preview-nonce',
	'action'          => 'http://127.0.0.1:8450/?vaultgate=login',
	'redirect'        => 'http://127.0.0.1:8450/',
	'form_token'      => '1234567890.' . str_repeat( 'a', 64 ),
	'remember'        => true,
	'remember_days'   => 30,
	'notice'          => ! empty( $_GET['notice'] ) ? 'Mot de passe incorrect. Veuillez réessayer.' : '',
	'invalid'         => ! empty( $_GET['notice'] ),
	'lock_seconds'    => 0,
	'style_url'       => 'http://127.0.0.1:8450/VaultGate/assets/css/gate.css',
	'script_url'      => 'http://127.0.0.1:8450/VaultGate/assets/js/gate.js',
);

require VAULTGATE_DIR . 'templates/gate.php';
