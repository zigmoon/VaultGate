# Changelog

All notable changes to VaultGate are documented here.
The format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/) and the project adheres to [Semantic Versioning](https://semver.org/).

## [1.1.4] — 28 October 2026

### Fixed

- **No more flash of five stacked panels.** Inactive tabs are now hidden by the server (`hidden` attribute) instead of waiting for the script, so the settings screen paints its real shape immediately. Without JavaScript every panel is still shown, as before.

### Changed

- README: the gate comes first, the settings screen follows in a three-column table, and every screenshot was retaken on WordPress 7.1.3 — same crop, same height, both password eyes and the new Trusted proxies field included.

## [1.1.3] — 28 October 2026

### Security

- **Spoofed `X-Forwarded-For` no longer bypasses the gate.** With "trust reverse-proxy headers" enabled, the left-most forwarded address was used — the one an attacker can prepend. Forwarded headers are now ignored unless the direct peer is a configured trusted proxy, and the chain is read from the right. A visitor can no longer imitate an allowlisted address or dodge the rate limiter by inventing one.
- **New setting: trusted proxies.** A list of IPs/CIDRs for your load balancer or CDN. Empty means only a loopback or private peer is trusted, which covers nginx or Docker on the same machine.
- **`CF-Connecting-IP`, `True-Client-IP` and `X-Real-IP` are no longer read.** A plain proxy passes those through untouched, so they were only another way to choose your own address.
- **Path traversal in the request path.** `.` and `..` segments are now resolved before the endpoint blocklist and the exclusion list are evaluated, so `/wp-comments-post.php/x/..` can no longer reach a blocked script, and an exclusion can no longer swallow a different path.
- **PATH_INFO no longer unblocks a direct endpoint.** `/wp-comments-post.php/<anything>` is routed to the script by Apache and nginx, so it stays blocked, like `/wp-admin/admin-post.php/<anything>`.

### Changed

- The endpoint blocklist compares normalised paths, with or without a trailing slash.

## [1.1.2] — 27 October 2026

### Changed

- **The plugin folder is now `VaultGate/`** (was `vaultgate/`), like the plugin name. WordPress notices and moves the folder automatically on update; `wp-content/plugins/VaultGate/` is the expected path.
- **One credit line, not two.** The `admin_footer_text` filter is gone: the credits live inside the settings page only.

### Added

- **An eye on every password field** — the gate and the two admin fields. Two SVG paths switched by `aria-pressed`, so the state is right before the script runs, and the label is translated for assistive technology.

### Fixed

- The gate no longer prints a redundant text "Show" toggle in the label row.

## [1.1.1] — 27 October 2026

### Added

- **Credits where they belong.** The settings footer, the dashboard footer of the plugin screen, the Plugins row and the welcome notice all link to [2.13](https://2points13.fr) and [zigmoon](https://zigmoon.com).
- **Welcome notice after activation**, dismissable, pointing at the password field and stating where the plugin comes from.

### Changed

- **Removal is clean by default.** "Delete all plugin data" now defaults to on; deactivation also revokes every cookie and deletes the rate-limit transients, so a site can be handed over without residue.
- **Responsive admin**: a second breakpoint (600 px) tightens tabs, swatches and tables; the credits block stacks.
- **Shorter README**, lead by the interface: real screenshots of the settings screen, the gate (light, dark) and the protection tab, all captured on a live WordPress 7.1.3 install.

### Fixed

- `_load_textdomain_just_in_time` notice on WordPress 6.7+: translations now load on `init`, and the option defaults no longer call `__()`.
- The site name is no longer printed on the gate or in its `<title>`; the gate prints the plugin title only.
- The logo image is fetched eagerly with a high priority, since it is the only image above the fold.

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

[1.1.4]: https://github.com/zigmoon/VaultGate/releases/tag/v1.1.4
[1.1.3]: https://github.com/zigmoon/VaultGate/releases/tag/v1.1.3
[1.1.1]: https://github.com/zigmoon/VaultGate/releases/tag/v1.1.1
[1.1.0]: https://github.com/zigmoon/VaultGate/releases/tag/v1.1.0
[1.0.0]: https://github.com/zigmoon/VaultGate/releases/tag/v1.0.0
