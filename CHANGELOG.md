# Changelog

All notable changes to VaultGate are documented here.
The format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/) and the project adheres to [Semantic Versioning](https://semver.org/).

## [1.1.0] — 27 October 2026

### Added

- **Logo picker** built on the WordPress Media Library: pick, preview and remove an image, stored as an attachment ID so it is reused across themes and included in media backups.
- **Accent colour** with eight presets (Ink, Slate, Blue, Green, Amber, Red, Violet, Rose) plus a free colour picker. Button text contrast is computed automatically (WCAG relative luminance), and a near-black accent falls back to white on the dark theme.
- **Theme selector**: light (default), dark, or follow the system.
- **Embedded typefaces**: Inter (front gate), Space Grotesk (admin headings) and JetBrains Mono (micro-labels), shipped as variable WOFF2 inside the plugin. No Google Fonts, no CDN, no third-party request.
- **Spanish translation** (`es_ES`) alongside French, with English as the source language.
- **New art direction**: white Swiss gate page and admin screen — hairline rules, 2 px corners, one accent, no shadow, black by default.
- **New icon, banners and social preview** in the same direction, plus admin identity in the settings header.
- Settings helpers: `Settings::swatches()`, `luminance()`, `ink_on()`, `accent_for_dark()`.
- Design preview harness: `tests/preview.php` (gate, with `?theme`, `?accent`, `?logo`) and `tests/admin-preview.php` (real admin render, used for the screenshots).

### Changed

- The gate page is a single quiet column. The default "under construction" sentence, the support link and the footer were removed: nothing is printed that the site owner did not write.
- Mobile layout rebuilt: centred at every width, no horizontal overflow, tested from 320 px upwards.
- Logo accepts `PNG`, `JPEG`, `WebP` and `GIF` from the media library; the legacy logo URL field remains as a fallback.
- French translation rewritten to match the new interface.

### Fixed

- The accent colour was declared on the gate body, which overrode the value chosen in the settings; it is now injected once per request.
- Content-Security-Policy explicitly allows the site origin and the plugin origin, so the gate works behind `www` redirects, reverse proxies and CDNs.
- Excluded paths are matched relative to the WordPress installation folder (subdirectory installs).
- Rate limiting falls back to a hashed raw address when the resolved IP is unusable, so a missing `REMOTE_ADDR` cannot remove the throttle.
- Non-scalar values in the settings form are neutralised instead of triggering a PHP warning.
- Uninstall also removes rate-limit and log-throttle transients.

### Compatibility

- WordPress 6.5 to 7.1.
- PHP 8.2 to 8.5; the suite runs on PHP 8.3.36 and PHP 8.5.12.
- No Composer or npm dependency, no external asset, no outbound request.
- 163 automated assertions (140 unit + 23 integration).

## [1.0.0] — 27 October 2026

### Added

- Whole-site password protection, switchable from Settings → VaultGate.
- Gate for pages, posts, archives, feeds, sitemaps, REST API, anonymous AJAX, XML-RPC and direct endpoints.
- HMAC-SHA256 signed cookie (`HttpOnly`, `SameSite=Lax`, `Secure` on HTTPS) with global revocation through a session version.
- Password hashing with `wp_hash_password()`; no plaintext password ever stored or logged.
- Per-visitor rate limiting with temporary lock-out, generic error messages, honeypot field and live countdown.
- IPv4/IPv6/CIDR allowlist, path exclusions with `*` wildcard, capability-based bypass.
- Security headers on the gate: `no-store`, `noindex`, `X-Frame-Options: DENY`, `nosniff`, `Referrer-Policy`, `Permissions-Policy`, nonce-based CSP.
- Anonymised activity log capped at 20 entries.
- WP-CLI commands: `status`, `enable`, `disable`, `password`, `logout-all`, `log`.
- `VAULTGATE_DISABLED` constant and eight integration filters.
- French translation, `.pot` catalogue and verified `.mo` compilation.
- Opt-in, multisite-aware uninstall cleanup.
- Reproducible tooling: translations, images and archive with SHA-256 verification.

[1.1.0]: https://github.com/zigmoon/passwordprotect/releases/tag/v1.1.0
[1.0.0]: https://github.com/zigmoon/passwordprotect/releases/tag/v1.0.0
