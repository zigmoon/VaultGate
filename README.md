<p align="center">
  <img src="assets/icon-256.png" alt="VaultGate" width="112" height="112">
</p>

<h1 align="center">VaultGate</h1>

<p align="center">
  <b>One password for the whole site.</b><br>
  A quiet, dependency-free WordPress plugin that keeps a site private while you build it.
</p>

<p align="center">
  <a href="README.md"><b>English</b></a> ·
  <a href="README.fr.md">Français</a> ·
  <a href="README.es.md">Español</a>
</p>

<p align="center">
  <a href="https://github.com/zigmoon/passwordprotect/releases/latest"><b>⬇️ Download</b></a> ·
  <a href="#install">Install</a> ·
  <a href="#the-gate-page">The gate page</a> ·
  <a href="#security">Security</a> ·
  <a href="#development">Development</a>
</p>

<p align="center">
  <img alt="WordPress 6.5 – 7.1" src="https://img.shields.io/badge/WordPress-6.5%20%E2%86%92%207.1-0b0d10?style=flat-square">
  <img alt="PHP 8.2 – 8.5" src="https://img.shields.io/badge/PHP-8.2%20%E2%86%92%208.5-0b0d10?style=flat-square">
  <img alt="Version 1.1.0" src="https://img.shields.io/badge/version-1.1.0-0a66ff?style=flat-square">
  <img alt="Licence GPL-2.0-or-later" src="https://img.shields.io/badge/licence-GPL--2.0--or--later-0b0d10?style=flat-square">
  <img alt="163 assertions" src="https://img.shields.io/badge/tests-163%20assertions-0b0d10?style=flat-square">
  <img alt="No dependencies" src="https://img.shields.io/badge/dependencies-none-0b0d10?style=flat-square">
</p>

<p align="center">
  <img src="docs/gate-light.png" alt="The VaultGate password page" width="880">
</p>

---

## Highlights

- **One password, everything closed.** Pages, archives, feeds, sitemaps, the REST API, anonymous AJAX and XML-RPC — each switchable.
- **Your logo, your colour.** Pick any image from the WordPress Media Library; choose one of eight accent presets or any colour. Black by default, contrast computed automatically.
- **Fonts inside the plugin.** Inter (the SuperPhoto family), Space Grotesk and JetBrains Mono ship as WOFF2 in the plugin. No Google Fonts, no CDN, no request leaving the site.
- **Nothing you did not write.** No default marketing sentence, no support link, no footer. A logo, a title, a password field, a button.
- **Signed cookies, not sessions.** Stateless HMAC-SHA256 token; one click revokes every visitor.
- **Hardened by default.** `wp_hash_password()`, rate limiting per visitor, nonce-based CSP, `no-store`, `noindex`, `Permissions-Policy`.
- **Self-hosted and quiet.** No Composer, no npm, no build step, no analytics, no outbound request.
- **English, French, Spanish.** Source strings in English; the two translations ship compiled and verified.
- **163 assertions.** Two suites, run on PHP 8.3 and PHP 8.5, no WordPress install required.

## The gate page

| Light | Dark |
|---|---|
| ![Light](docs/gate-light.png) | ![Dark](docs/gate-dark.png) |

| Mobile (390 × 844) |
|---|
| <img src="docs/gate-mobile.png" alt="Mobile" width="330"> |

A single column, centred at every width, from 320 px upwards. A hairline separates the header from the form; an accent rule sits under the title; the button is the only coloured surface. The same design is used in the settings screen:

| Settings — logo, colour, theme | Settings — protection |
|---|---|
| ![Gate page settings](docs/admin-appearance.png) | ![Protection settings](docs/admin-protection.png) |

## Install

**From the archive**

1. Download `vaultgate-1.1.0.zip` from [Releases](https://github.com/zigmoon/passwordprotect/releases/latest).
2. WordPress → **Plugins → Add New → Upload Plugin**.
3. Activate **VaultGate**.
4. Open **Settings → VaultGate**.

**From a checkout**

```bash
cp -r vaultgate /path/to/wp-content/plugins/
wp plugin activate vaultgate
wp vaultgate password 'a long passphrase'
wp vaultgate enable
```

Requires WordPress 6.5+ and PHP 8.2+. No PHP extension beyond a standard WordPress install.

## Configure

**Settings → VaultGate** has five tabs.

| Tab | What it holds |
|---|---|
| Protection | Master switch, new password, "sign out every visitor", lock-out help |
| Gate page | **Logo**, title, optional message, **accent colour**, **theme** |
| Access | Session duration, "keep me signed in", bypass capability, IP allowlist, proxy trust, excluded paths |
| Advanced | REST / feeds / sitemaps / AJAX / XML-RPC toggles, brute-force limits, activity log, uninstall cleanup |
| Activity | The last 20 events, with anonymised IP addresses |

### The logo

Pick an image with **Choose image**; it opens the WordPress Media Library. VaultGate stores the attachment ID, not a copy:

- the file lives in `wp-content/uploads/YYYY/MM/`, like any other media;
- it comes back with your media backups and can be reused by a theme;
- WordPress generates the sizes, so the gate serves a right-sized image;
- **Remove** clears the field, and the built-in geometric mark comes back.

The gate caps the logo at 168 × 56 CSS px and keeps the aspect ratio, so a wide lock-up works best. PNG, JPEG, WebP and GIF are accepted; SVG needs an `upload_mimes` filter, as everywhere in WordPress.

### The colour

Eight presets — **Ink** (default), Slate, Blue, Green, Amber, Red, Violet, Rose — or the native colour picker. The accent paints the button, the focus ring and the rule under the title. Button text switches between white and ink from the WCAG relative luminance, and a near-black accent is replaced by white on the dark theme so the button never disappears.

### The theme

**Light** (default), **Dark**, or **System** to follow the visitor's operating system.

## Security

- **Password** — `wp_hash_password()` / `wp_check_password()`. Never stored in clear text, never logged, never sent anywhere.
- **Cookie** — `expiration.signature`, HMAC-SHA256 over context, session version and expiry, keyed with `wp_salt('auth')` and the site's `COOKIEHASH`. Verified with `hash_equals()`.
- **Revocation** — changing the password, toggling protection or pressing "sign out every visitor" increments `vaultgate_auth_version`: every issued cookie becomes invalid immediately.
- **Cookie attributes** — `HttpOnly`, `SameSite=Lax`, `Secure` on HTTPS (or behind a trusted proxy), WordPress path and domain.
- **Rate limiting** — sliding window per visitor in a transient whose key is an HMAC of the address. Lock-out with a live countdown on the button.
- **Form** — expiring signed token, hidden honeypot, generic error messages, `303` redirect after processing.
- **Headers** — `no-store`, `noindex, nofollow, noarchive`, `X-Frame-Options: DENY`, `nosniff`, `Referrer-Policy: no-referrer`, restrictive `Permissions-Policy`, CSP with a per-request nonce.
- **Admin** — `manage_options`, nonces on every action, server-side validation, escaped output.

### Limits

- **Static files.** Files served directly by the web server (`wp-content/uploads/...`) never reach WordPress. Use server authentication or private media if they are sensitive.
- **Page cache.** The gate sends `no-store` and checks the cookie on every request, but exclude the site from an external full-page cache while it is locked.
- **`wp-login.php` stays open.** It has to: it is the way back in. VaultGate is a public door, not a replacement for WordPress authentication.
- **One shared password.** Not a user or role manager.
- **Multisite.** Settings are per site.

## Emergency access

1. `wp-login.php` is never gated, and administrator sessions always bypass the gate.
2. Or, without the dashboard, in `wp-config.php`:
   ```php
   define( 'VAULTGATE_DISABLED', true );
   ```
3. Or with WP-CLI:
   ```bash
   wp vaultgate status
   wp vaultgate disable
   wp vaultgate password 'a new passphrase'
   ```

A visitor can lock themselves out again with `https://example.com/?vaultgate=logout`.

## WP-CLI

```text
wp vaultgate status              Full state: protection, password, surfaces, capability
wp vaultgate enable              Turn protection on (a password must exist)
wp vaultgate disable             Turn it off, keep the password
wp vaultgate password <pass>     Set the password (8 characters minimum)
wp vaultgate password --stdin    Read it from standard input
wp vaultgate logout-all          Revoke every issued cookie
wp vaultgate log                 Print the activity log
wp vaultgate log --clear         Empty it
```

## Filters

| Filter | Purpose |
|---|---|
| `vaultgate_should_gate` | Allow or refuse the gate for one request |
| `vaultgate_client_ip` | Replace the detected address |
| `vaultgate_bypass_capabilities` | Add or remove bypass capabilities |
| `vaultgate_blocked_endpoints` | Change the list of blocked direct endpoints |
| `vaultgate_secure_cookie` | Force or disable the `Secure` attribute |
| `vaultgate_cookie_name` | Rename the cookie |
| `vaultgate_swatches` | Change the accent presets |
| `vaultgate_throttled_log_events` | Choose events limited to one entry per minute |
| `vaultgate_log_size` | Number of entries kept (20 by default) |

```php
// Let a payment webhook through without opening the whole site.
add_filter(
	'vaultgate_should_gate',
	static function ( bool $gate ): bool {
		return str_starts_with( $_SERVER['REQUEST_URI'] ?? '', '/webhooks/stripe' ) ? false : $gate;
	}
);
```

## Development

PHP 8.2+ and nothing else.

```bash
php tests/run.php                 # unit tests
php tests/smoke.php               # boot, guard, REST, admin render
php tools/build-translations.php  # .pot + fr_FR + es_ES, compiled and verified
pwsh -File tools/build-images.ps1 # icon sizes from assets/icon-master.png
pwsh -File tools/build-zip.ps1    # dist/vaultgate-<version>.zip, hash-verified

# Design previews (no WordPress needed)
php -S 127.0.0.1:8450 tests/preview-router.php
#   /                        gate, light
#   /?theme=dark             gate, dark
#   /?accent=%230a66ff       gate, custom accent
#   /?logo=1                 gate with a sample logo
#   /admin-preview.php       settings screen
```

Expected output:

```text
140 passed, 0 failed
23 passed, 0 failed
```

The suites use a small set of WordPress stubs (`tests/bootstrap.php`): no database, no web server, no WordPress installation. They are run against **PHP 8.3.36** and **PHP 8.5.12**.

## Layout

```text
passwordprotect/
├── vaultgate/                     the plugin, exactly as installed
│   ├── vaultgate.php              WordPress header + bootstrap
│   ├── includes/                  settings, access rules, tokens, limiter, guard, admin, CLI
│   ├── templates/gate.php         the gate page
│   ├── assets/css|js/             styles and scripts, no CDN
│   ├── assets/fonts/              Inter, Space Grotesk, JetBrains Mono (WOFF2, OFL)
│   └── languages/                 .pot, fr_FR, es_ES (sources, compiled and verified)
├── assets/                        icon sources, banners, social preview
├── docs/                          screenshots used above
├── dist/                          versioned installable archive
├── tests/                         test suites + design previews
└── tools/                         translations, images, archive
```

## Compatibility

| | |
|---|---|
| WordPress | 6.5 → 7.1 (latest stable at the time of writing: 7.1.3) |
| PHP | 8.2 → 8.5, tests run on 8.3.36 and 8.5.12 |
| Database | No table created; `wp_options` only |
| Multisite | Yes, settings per site |
| PHP extensions | None beyond a standard WordPress install |
| Browsers | Anything current; the gate works without JavaScript |

## FAQ

**Where does the logo go?**
Into the WordPress Media Library. VaultGate stores the attachment ID, so the file is backed up with your media and can be reused elsewhere.

**How do I remove the sentence under the title?**
There is none by default. The message field in **Gate page** is optional; the gate is a single quiet column without it.

**Can I use my own font?**
The three families are embedded in the plugin. To change them, replace the WOFF2 files in `vaultgate/assets/fonts/` and update the `@font-face` rules in `vaultgate/assets/css/`. Nothing is fetched from a third party.

**Does it work with a page cache?**
The headers and the per-request cookie check are correct, but exclude the site from an external cache while it is locked.

**Are uploads protected?**
No — those files bypass WordPress. See [Limits](#limits).

**What happens when I deactivate it?**
The site becomes public and every issued cookie is revoked, so re-activating asks for the password again.

## Licence

GPL-2.0-or-later — see [LICENSE](LICENSE). Copyright © 2026 zigmoon.

Security reports: [SECURITY.md](SECURITY.md).
