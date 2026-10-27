<?php
/**
 * Standalone gate page.
 *
 * Visual language: white Swiss, hairline rules, sharp corners, one accent.
 * Typography: Inter — the SuperPhoto / SuperPrint family — embedded locally.
 *
 * @package VaultGate
 *
 * @var array<string, mixed> $vg View data prepared by Guard::render_gate().
 */

defined( 'ABSPATH' ) || exit;

$vg_theme_class = 'light' === (string) $vg['theme'] ? 'vg-theme-light' : ( 'dark' === (string) $vg['theme'] ? 'vg-theme-dark' : 'vg-theme-auto' );
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="robots" content="noindex, nofollow, noarchive">
	<meta name="color-scheme" content="light dark">
	<title><?php echo esc_html( (string) $vg['title'] ); ?> — <?php echo esc_html( (string) $vg['site_name'] ); ?></title>
	<link rel="stylesheet" href="<?php echo esc_url( (string) $vg['style_url'] ); ?>">
	<style nonce="<?php echo esc_attr( (string) $vg['nonce'] ); ?>">
		:root {
			--vg-accent: <?php echo esc_attr( (string) $vg['accent'] ); ?>;
			--vg-accent-ink: <?php echo esc_attr( (string) $vg['accent_ink'] ); ?>;
			--vg-accent-dark: <?php echo esc_attr( (string) $vg['accent_dark'] ); ?>;
			--vg-accent-dark-ink: <?php echo esc_attr( (string) $vg['accent_dark_ink'] ); ?>;
		}
	</style>
</head>
<body class="vg-gate <?php echo esc_attr( $vg_theme_class ); ?>">
	<a class="vg-skip" href="#vg-password"><?php esc_html_e( 'Skip to the password field', 'vaultgate' ); ?></a>

	<main class="vg-shell">
		<header class="vg-head">
			<?php if ( '' !== (string) $vg['logo'] ) : ?>
				<div class="vg-logo-slot">
					<?php
					// Core-generated attachment markup, already escaped by logo_markup().
					echo $vg['logo']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					?>
				</div>
			<?php else : ?>
				<span class="vg-mark" aria-hidden="true">
					<svg viewBox="0 0 40 40" width="34" height="34" focusable="false" aria-hidden="true">
						<path d="M10.5 34V17.5a9.5 9.5 0 0 1 19 0V34" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="square"/>
						<path d="M6 34h28" stroke="currentColor" stroke-width="2.2" stroke-linecap="square"/>
						<circle cx="20" cy="23" r="2.4" fill="currentColor"/>
						<path d="M20 24.6v5.4" stroke="currentColor" stroke-width="2.2" stroke-linecap="square"/>
					</svg>
				</span>
			<?php endif; ?>

			<p class="vg-site"><?php echo esc_html( (string) $vg['site_name'] ); ?></p>
			<h1 class="vg-title" id="vg-title"><?php echo esc_html( (string) $vg['title'] ); ?></h1>

			<?php if ( '' !== trim( (string) $vg['message'] ) ) : ?>
				<div class="vg-message"><?php echo wp_kses_post( wpautop( (string) $vg['message'] ) ); ?></div>
			<?php endif; ?>
		</header>

		<form class="vg-form" method="post" action="<?php echo esc_url( (string) $vg['action'] ); ?>" aria-labelledby="vg-title">
			<input type="hidden" name="vaultgate_action" value="login">
			<input type="hidden" name="vaultgate_form_token" value="<?php echo esc_attr( (string) $vg['form_token'] ); ?>">
			<input type="hidden" name="vaultgate_redirect" value="<?php echo esc_attr( (string) $vg['redirect'] ); ?>">

			<div class="vg-field">
				<div class="vg-label-row">
					<label for="vg-password"><?php esc_html_e( 'Password', 'vaultgate' ); ?></label>
					<button class="vg-reveal" type="button" data-vg-reveal aria-controls="vg-password" aria-pressed="false" data-label-show="<?php echo esc_attr__( 'Show', 'vaultgate' ); ?>" data-label-hide="<?php echo esc_attr__( 'Hide', 'vaultgate' ); ?>">
						<span data-vg-reveal-label><?php esc_html_e( 'Show', 'vaultgate' ); ?></span>
					</button>
				</div>
				<input id="vg-password" name="vaultgate_password" type="password" autocomplete="current-password" autocapitalize="none" spellcheck="false" enterkeyhint="go" required autofocus aria-describedby="vg-password-hint<?php echo ! empty( $vg['invalid'] ) ? ' vg-notice' : ''; ?>"<?php echo ! empty( $vg['invalid'] ) ? ' aria-invalid="true"' : ''; ?>>
				<p id="vg-password-hint" class="vg-hint"><?php esc_html_e( 'Enter the password provided by the site owner.', 'vaultgate' ); ?></p>
			</div>

			<?php if ( '' !== (string) $vg['notice'] ) : ?>
				<p class="vg-alert" id="vg-notice" role="alert" aria-live="polite" aria-atomic="true"><?php echo esc_html( (string) $vg['notice'] ); ?></p>
			<?php endif; ?>

			<?php if ( ! empty( $vg['remember'] ) ) : ?>
				<label class="vg-check">
					<input type="checkbox" name="vaultgate_remember" value="1">
					<span>
						<?php
						$vg_days = (int) $vg['remember_days'];

						if ( 1 === $vg_days ) {
							esc_html_e( 'Keep me signed in for 1 day', 'vaultgate' );
						} else {
							printf(
								/* translators: %d: number of days. */
								esc_html__( 'Keep me signed in for %d days', 'vaultgate' ),
								$vg_days
							);
						}
						?>
					</span>
				</label>
			<?php endif; ?>

			<button class="vg-submit" type="submit" data-vg-lock="<?php echo esc_attr( (string) (int) $vg['lock_seconds'] ); ?>" data-label-wait="<?php echo esc_attr__( 'Try again in %s', 'vaultgate' ); ?>">
				<span data-vg-submit-label><?php esc_html_e( 'Enter site', 'vaultgate' ); ?></span>
			</button>

			<div class="vg-hp" aria-hidden="true" inert>
				<span><?php esc_html_e( 'Website', 'vaultgate' ); ?></span>
				<input id="vg-website" type="text" name="vaultgate_website" value="" tabindex="-1" autocomplete="off">
			</div>
		</form>
	</main>

	<script src="<?php echo esc_url( (string) $vg['script_url'] ); ?>" defer></script>
</body>
</html>
