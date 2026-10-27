<p align="center">
  <img src="assets/icon-256.png" alt="VaultGate" width="92" height="92">
</p>

<h1 align="center">VaultGate</h1>

<p align="center">
  <b>One password for the whole site — nothing else on the page.</b><br>
  A quiet WordPress plugin for sites that are still being built.
</p>

<p align="center">
  <a href="https://github.com/zigmoon/VaultGate/releases/latest"><b>⬇ Download</b></a> ·
  <a href="#install">Install</a> ·
  <a href="#what-you-get">What you get</a> ·
  <a href="#security">Security</a> ·
  <a href="#credits">Credits</a>
</p>

<p align="center">
  <img alt="WordPress 6.5 to 7.1" src="https://img.shields.io/badge/WordPress-6.5%20%E2%86%92%207.1-0b0d10?style=flat-square">
  <img alt="PHP 8.2 to 8.5" src="https://img.shields.io/badge/PHP-8.2%20%E2%86%92%208.5-0b0d10?style=flat-square">
  <img alt="Version 1.1.2" src="https://img.shields.io/badge/version-1.1.2-0a66ff?style=flat-square">
  <img alt="184 assertions" src="https://img.shields.io/badge/tests-184%20assertions-0b0d10?style=flat-square">
  <img alt="Licence GPL-2.0-or-later" src="https://img.shields.io/badge/licence-GPL--2.0--or--later-0b0d10?style=flat-square">
</p>

![Settings, Gate page tab: logo from the Media Library, accent swatches, theme, texts](docs/admin-appearance.png)

## What you get

**Settings → VaultGate** — five tabs, no noise: **Protection**, **Gate page**, **Access**, **Advanced**, **Activity**.

![Protection tab: master switch, password with reveal, sign out every visitor](docs/admin-protection.png)

- **One password, everything closed.** Pages, archives, feeds, sitemaps, the REST API, anonymous AJAX and XML-RPC — each with its own switch.
- **Your logo, your colour.** Logo picked from the Media Library (`wp-content/uploads`), eight accent swatches or any hex, light / dark / system gate. Button contrast is computed, never guessed.
- **Nothing you did not write.** No marketing sentence, no support link, no footer on the gate. A logo, a title, a field, a button.
- **No third party.** Fonts, icons and styles live inside the plugin. No Google Fonts, no CDN, no analytics, no outbound request.
- **Built to be removed.** No table, no `.htaccess`, no rewrite rule. Deactivate and the site is public again.
- **English, French, Spanish.** Source strings in English; the interface follows the site language.

## The gate

| Light | Dark |
|---|---|
| ![Light](docs/gate-light.png) | ![Dark](docs/gate-dark.png) |

One column, centred from 320 px up. It works without JavaScript; the eye that reveals the password, the lock-out countdown and the caps-lock hint are progressive enhancement only.

## Security

- **Password** — `wp_hash_password()` / `wp_check_password()`. Never stored in clear text, never logged, never sent anywhere.
- **Cookie** — `expiry.signature`, HMAC-SHA256 over the expiry, the session version and the site's `COOKIEHASH`, keyed with `wp_salt('auth')`. Compared with `hash_equals()`.
- **Revocation** — changing the password, toggling protection or pressing "Sign out every visitor" bumps the session version: every issued cookie dies immediately.
- **Cookie attributes** — `HttpOnly`, `SameSite=Lax`, `Secure` on HTTPS (or behind a trusted proxy), WordPress path and domain.
- **Brute force** — sliding window per visitor, keyed by an HMAC of the address; escalating lock-out with a live countdown.
- **Form** — expiring signed token, hidden honeypot, one generic error message, `303` redirect after POST so a refresh cannot replay it.
- **Headers** — `no-store`, `noindex, nofollow, noarchive`, `X-Frame-Options: DENY`, `nosniff`, `Referrer-Policy: no-referrer`, restrictive `Permissions-Policy`, and a Content-Security-Policy with a per-request nonce and no `unsafe-inline`.
- **Admin** — `manage_options`, a nonce on every action, server-side validation, escaped output everywhere, `wp_safe_redirect()` for every redirect.
- **Least data** — one option array, one hash, one small log with anonymised IP addresses.

## Install

1. Download `VaultGate-1.1.2.zip` from [Releases](https://github.com/zigmoon/VaultGate/releases/latest).
2. **Plugins → Add New → Upload Plugin**, choose the ZIP, install, activate.
3. **Settings → VaultGate**: set the password, then turn protection on.

Or from a checkout:

```bash
cp -r VaultGate /path/to/wp-content/plugins/
wp plugin activate VaultGate
wp vaultgate password 'a long passphrase'
wp vaultgate enable
```

The folder must be named `VaultGate`. WordPress 6.5+ and PHP 8.2+ — nothing else to install. The first activation shows a single notice pointing at the password field.

![First activation notice](docs/admin-welcome.png)

## Compatibility

| | |
|---|---|
| WordPress | 6.5 → 7.1 (latest stable at the time of writing: 7.1.3) |
| PHP | 8.2 → 8.5 |
| Database | No table created; `wp_options` only |
| Multisite | Yes, settings per site |
| PHP extensions | None beyond a standard WordPress install |
| Browsers | Anything current; the gate works without JavaScript |

## Limits

- **Static files.** `wp-content/uploads/...` is served by the web server and never reaches WordPress. Use server authentication for sensitive files.
- **Page cache.** The gate sends `no-store` and checks the cookie on every request; exclude the site from an external full-page cache while it is locked.
- **`wp-login.php` stays open.** It is the way back in: administrator sessions always bypass the gate.
- **One shared password.** Not a user or role manager.

## Removing it

Deactivating makes the site public again and revokes every issued cookie. Deleting the plugin removes its settings, hash and log — **on by default** — so nothing is left behind. If you ever get stuck: `define( 'VAULTGATE_DISABLED', true );` in `wp-config.php`, or `wp vaultgate disable`.

## Development

```bash
php tests/run.php                 # unit tests
php tests/smoke.php               # boot, guard, REST, admin render
php tools/build-translations.php  # .pot + fr_FR + es_ES, compiled and verified
pwsh -File tools/build-zip.ps1    # dist/VaultGate-<version>.zip, hash-verified
```

Expected output: `145 passed, 0 failed` and `39 passed, 0 failed`. The suites use a small set of WordPress stubs (`tests/bootstrap.php`): no database, no web server, no WordPress installation needed.

## Credits

A plugin by **[2.13](https://2points13.fr)** · dev by **[zigmoon](https://zigmoon.com)**.

![The plugin row on the Plugins screen](docs/admin-plugins.png)

GPL-2.0-or-later — see [LICENSE](LICENSE). Fonts: Inter, Space Grotesk, JetBrains Mono (SIL Open Font License, embedded). Security reports: [SECURITY.md](SECURITY.md).
