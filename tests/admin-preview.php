<?php
/**
 * Admin screen preview (development only).
 *
 * Renders the real Admin::render() output with the WordPress stubs, so the
 * design screenshots are produced from the shipped markup and stylesheet.
 *
 *   php -S 127.0.0.1:8450 tests/preview-router.php
 *   http://127.0.0.1:8450/admin-preview.php
 *
 * @package VaultGate
 */

require_once __DIR__ . '/bootstrap.php';
require_once VAULTGATE_DIR . 'includes/class-settings.php';
require_once VAULTGATE_DIR . 'includes/class-admin.php';

$GLOBALS['vg_current_user_can'] = true;
$GLOBALS['vg_is_admin']         = true;
$GLOBALS['vg_attachments']      = array();

$vg_settings = new VaultGate\Settings();
$vg_settings->set_password( 'a long enough password' );
$vg_settings->update(
	array(
		'enabled'      => true,
		'gate_title'   => 'Site privé',
		'accent'       => '#0b0d10',
		'theme'        => 'light',
		'session_days' => 7,
	)
);

$vg_settings->log( 'login_success', '203.0.113.10' );
$vg_settings->log( 'login_failed', '198.51.100.77' );
$vg_settings->log( 'login_locked', '198.51.100.77' );

$vg_tab = isset( $_GET['tab'] ) ? sanitize_key( (string) $_GET['tab'] ) : 'protection';

$_GET['page'] = 'vaultgate';

ob_start();
( new VaultGate\Admin( $vg_settings ) )->render();
$vg_markup = (string) ob_get_clean();

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>VaultGate — settings preview</title>
	<link rel="stylesheet" href="/VaultGate/assets/css/admin.css">
	<style>
		html { background: #f0f0f1; }
		body {
			margin: 0;
			padding: 26px 30px 60px;
			background: #f0f0f1;
			color: #1d2327;
			font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
			font-size: 13px;
			line-height: 1.4;
		}
		.wrap { margin: 0; }
		[hidden] { display: none !important; }
	</style>
</head>
<body>
	<?php echo $vg_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	<script>
		window.vaultGateAdmin = { chooseLogo: 'Choose a logo', useLogo: 'Use this image' };
		(function () {
			var tabs = document.querySelectorAll('[data-vg-tab]');
			var panels = document.querySelectorAll('[data-vg-panel]');
			var wanted = <?php echo wp_json_encode( $vg_tab ); ?>;
			tabs.forEach(function (tab) {
				var on = tab.getAttribute('data-vg-tab') === wanted;
				tab.setAttribute('aria-selected', on ? 'true' : 'false');
			});
			panels.forEach(function (panel) {
				panel.hidden = panel.getAttribute('data-vg-panel') !== wanted;
			});
			document.querySelector('[data-vg-admin]').classList.add('vg-admin--js');
			var accent = document.querySelector('[data-vg-accent]');
			var value = document.querySelector('[data-vg-accent-value]');
			document.querySelectorAll('[data-vg-swatch]').forEach(function (swatch) {
				swatch.addEventListener('click', function () {
					var color = swatch.getAttribute('data-vg-swatch');
					accent.value = color;
					value.textContent = color;
					document.querySelectorAll('[data-vg-swatch]').forEach(function (other) {
						other.setAttribute('aria-pressed', other === swatch ? 'true' : 'false');
					});
				});
			});
		})();
	</script>
</body>
</html>
