=== VaultGate ===
Contributors: zigmoon
Tags: password protection, maintenance mode, coming soon, access control, privacy
Requires at least: 6.5
Tested up to: 7.1
Requires PHP: 8.2
Stable tag: 1.1.6
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

One password for the whole site, with a quiet Swiss gate page, signed cookies and no third-party service.

== Description ==

VaultGate protects an entire WordPress installation while it is being built or kept private. Visitors see a fast, accessible password page instead of the site. Pages, feeds, sitemaps, the REST API, AJAX and XML-RPC stay closed until the correct password is entered.

**Design** — a white Swiss gate page: hairline rules, sharp corners, one accent colour, no shadow, no decoration. It ships with the Inter typeface embedded in the plugin as WOFF2, so the page renders identically everywhere and never calls Google Fonts or a CDN. Black is the default accent, with eight presets and a free colour picker.

**Your logo, from the media library** — pick any image in Settings → VaultGate → Gate page. It is stored in the WordPress Media Library (`wp-content/uploads/YYYY/MM/`), so it is included in your media backups and reused by any other theme or plugin. Wide logos work best; the gate caps them at 168 × 56 px.

**Built for the current stack** — WordPress 6.5 through 7.1, PHP 8.2 through 8.5. Only maintained WordPress APIs, no deprecated calls, no Composer or npm build step, no external request. The test suite runs on PHP 8.3 and PHP 8.5.

**Security**

* The password is stored with `wp_hash_password()` — never in clear text, never logged.
* Access is a stateless HMAC-SHA256 token in an HttpOnly, SameSite=Lax cookie (Secure on HTTPS).
* Changing the password, toggling protection or clicking "Sign out every visitor" rotates the signing version and invalidates every cookie instantly.
* Per-visitor brute-force throttling with a live countdown, generic error messages and a hidden honeypot field.
* `hash_equals()` for signature checks and `wp_check_password()` for passwords.
* The gate sends `no-store` caching headers, `X-Robots-Tag`, `X-Frame-Options`, `Referrer-Policy`, `X-Content-Type-Options`, `Permissions-Policy` and a strict Content-Security-Policy with a per-request nonce.
* No jQuery, no external fonts, no analytics, no CDN dependency.

**Quality**

* Strictly escaped output, sanitised input, capability checks and nonces on every admin action.
* Small, single-responsibility classes: settings, access rules, tokens, rate limiter, guard, admin, CLI.
* 181 automated assertions ship in the `tests/` directory (unit tests plus a boot/render smoke test).
* English source strings, complete French and Spanish translations, `.pot` catalogue.
* Data cleanup on uninstall is on by default, multisite-aware, and also removes transients; deactivation revokes every issued cookie.

**Features**

* Master switch plus password change from Settings → VaultGate.
* Gate page: your logo, title, optional message, accent colour with automatic contrast, light / dark / system theme.
* Session duration and an optional "Keep me signed in".
* Let logged-in users with a chosen capability (administrators, editors, authors or any logged-in user) bypass the gate. Administrators always bypass.
* IP allowlist with IPv4, IPv6 and CIDR ranges.
* Optional reverse-proxy header support, restricted to a list of trusted proxy addresses.
* Path exclusions with `*` wildcards, resolved relative to the installation folder, for health checks or public webhooks.
* Toggles for REST API, feeds, sitemaps, anonymous AJAX and XML-RPC.
* Capped, anonymised activity log.
* WP-CLI commands for emergencies and automation.
* Filters for integration with bespoke workflows.

**Emergency access**

You can always log in through `wp-login.php`. Administrator sessions always bypass the gate. If you need to disable the gate without the dashboard:

`define( 'VAULTGATE_DISABLED', true );` in `wp-config.php`, or:

`wp vaultgate disable`

**Origin** — a plugin by [2.13](https://2points13.fr), dev by [zigmoon](https://zigmoon.com). Both links are also in the settings footer and on the Plugins screen.

== Installation ==

1. Upload the `vaultgate` folder to `/wp-content/plugins/`, or upload the ZIP through Plugins → Add New → Upload Plugin.
2. Activate the plugin.
3. Open Settings → VaultGate.
4. Enter a password (minimum 8 characters), confirm it, then tick "Enable password protection" and save.
5. Optional: choose your logo and accent colour under "Gate page".
6. Open the site in a private window to check the gate.

== Frequently Asked Questions ==

= Where is my logo stored? =

In the WordPress Media Library, under `wp-content/uploads/YYYY/MM/`. VaultGate only stores the attachment ID, so the image survives plugin updates and is included in any media backup. Removing the plugin does not delete the image from your library.

= Which image formats can I use? =

PNG, JPEG, WebP and GIF. SVG uploads are blocked by WordPress by default; enable them with a plugin or an `upload_mimes` filter if you need vector logos. A wide logo works best: the gate caps it at 168 × 56 px and keeps the aspect ratio.

= Is the password stored in clear text? =

No. VaultGate stores a one-way hash produced by `wp_hash_password()`. The plain password is never written to the database or to the log.

= What happens if I forget the password? =

`wp-login.php` is never gated, and administrator sessions bypass the gate. You can also use the `VAULTGATE_DISABLED` constant or `wp vaultgate password 'new passphrase'` with WP-CLI.

= Does it work with a full-page cache? =

The gate sends `Cache-Control: no-store`, `DONOTCACHEPAGE` and `DONOTCACHEOBJECT`, and the cookie is checked on every request. If you use a reverse proxy or CDN, exclude the site (or at least dynamic HTML) from its cache while the gate is on.

= Are uploaded files protected? =

No. VaultGate protects requests handled by WordPress. Files served directly by the web server (for example `/wp-content/uploads/2024/01/photo.jpg`) are outside WordPress and cannot be gated by a plugin alone. Use server-level authentication or a private-media solution if those files are sensitive.

= Does it protect the REST API? =

Yes, by default anonymous `/wp-json` requests return 401. Logged-in users above the bypass capability, and visitors with a valid gate cookie, keep access. You can turn REST protection off in the Advanced tab.

= Does it work when WordPress is installed in a subdirectory? =

Yes. Excluded paths are matched relative to the installation folder, so `/robots.txt` keeps working on a site installed under `/blog`.

= Does it support multisite? =

Yes. Settings are per site. Network activation is supported; uninstall cleanup runs for each site that opted in.

= Does it work behind a CDN or a reverse proxy? =

Yes. Enable "Trust reverse-proxy headers" and list the proxy addresses under "Trusted proxies". Forwarded headers are only read when the direct peer is on that list, and the `X-Forwarded-For` chain is read from the right, so a visitor cannot invent an address to skip the gate or dodge the rate limiter. Left empty, only a loopback or private peer is trusted — the usual nginx or Docker setup — and behind a CDN every visitor is then seen as the CDN and shares one rate-limit bucket.

= Which PHP versions are supported? =

PHP 8.2 and newer, including PHP 8.3, 8.4 and 8.5.

= Is the gate page accessible? =

It uses semantic HTML, a skip link, visible focus rings, a properly labelled password field, an `aria-live` error message, reduced-motion support and AA-level colour contrast. The password reveal button and the lock-out countdown are progressive enhancement only: the form works without JavaScript.

= Can I remove the message under the title? =

Yes, it is empty by default. The gate is then a single quiet column: logo, title, password field and button.

== Changelog ==

= 1.1.6 =
* New icon: the arch mark is now drawn in black on a near-white card instead of white on a black plate, in the same style as the banners.

= 1.1.5 =
* The gate page prints the WordPress Site Icon (and an Apple touch icon), so the browser tab is no longer blank while the plugin is active. Without a Site Icon, the VaultGate mark is used.
* Every boolean setting is a switch instead of a checkbox, still a native input exposed as role="switch".
* Fix: a boolean nested in a label that also had a "for" attribute could toggle twice in Chrome.

= 1.1.4 =
* The settings screen no longer flashes the five panels stacked before the script runs: inactive tabs are hidden by the server.

= 1.1.3 =
* Security: a spoofed X-Forwarded-For can no longer imitate an allowlisted address or dodge the rate limiter. Forwarded headers are ignored unless the direct peer is a trusted proxy, and the chain is read from the right.
* Security: CF-Connecting-IP, True-Client-IP and X-Real-IP are no longer read, because a plain proxy passes them through untouched.
* New "Trusted proxies" setting (IPs/CIDRs). Empty means only a loopback or private peer is trusted.
* Security: `.` and `..` segments are resolved before the endpoint blocklist and the exclusion list are evaluated.
* Security: PATH_INFO no longer unblocks a direct endpoint (`/wp-comments-post.php/<anything>`).

= 1.1.2 =
* The plugin folder is now `VaultGate/`.
* One credit line only: the WordPress footer is no longer replaced.
* An eye on every password field (gate and settings), with a translated label.
* The text "Show" toggle is gone from the gate.

= 1.1.1 =
* Credits: 2.13 (2points13.fr) and dev by zigmoon (zigmoon.com) in the settings footer, the plugin-screen footer, the Plugins row and the welcome notice.
* Welcome notice after activation, dismissable, pointing at the password field.
* Deleting plugin data is now on by default; deactivation revokes every cookie and removes the rate-limit transients.
* Responsive admin down to 360 px.
* Translations now load on `init` (WordPress 6.7+ notice fixed); option defaults are no longer translated.
* The site name is no longer printed on the gate or in its browser title.
* README led by the interface, with screenshots captured on a live WordPress install.

= 1.1.0 =
* New visual language: white Swiss gate page and admin screen, hairline rules, sharp corners, black by default.
* Inter, Space Grotesk and JetBrains Mono are now embedded in the plugin — no Google Fonts, no CDN.
* Logo picker built on the WordPress Media Library (attachment ID, honours image sizes).
* Eight accent presets plus a free colour picker, with automatic contrast and a dark-theme fallback.
* Light / dark / system theme, light by default.
* The default "under construction" sentence, the support link and the footer are gone: nothing is printed that you did not write.
* Spanish translation added; English is the source language, French and Spanish ship as translations.
* Mobile layout rebuilt: the gate is centred at every width, with no horizontal overflow.
* New icon, banners and social preview in the same art direction.
* New validation helpers: `Settings::luminance()`, `ink_on()`, `accent_for_dark()`, `swatches()`.

= 1.0.0 =
* Initial release.
* Whole-site gate for pages, feeds, sitemaps, REST, AJAX, XML-RPC and direct endpoints.
* HMAC-signed HttpOnly cookies with instant global revocation.
* Per-visitor rate limiting with a live countdown, and an anonymised activity log.
* CIDR/IP allowlist, path exclusions resolved against the installation folder, and capability-based bypass.
* WP-CLI commands and opt-in uninstall cleanup.

== Upgrade Notice ==

= 1.1.6 =
A new icon and the same plugin: nothing to reconfigure. Existing settings are kept.

= 1.1.5 =
The tab keeps its favicon on the gate page, and the settings use real switches. Existing settings are kept.

= 1.1.4 =
Small polish release: the settings screen paints cleanly instead of stacking its five panels for a frame. Existing settings are kept.

= 1.1.3 =
Security release: forwarded headers are only trusted from a configured proxy, and path normalisation no longer has a traversal gap. Update now if "Trust reverse-proxy headers" is enabled.

= 1.1.2 =
The plugin folder is renamed VaultGate/. One credit line instead of two, and an eye to show the password. Existing settings are kept.

= 1.1.1 =
Credits and origin links, a welcome notice after activation, clean removal by default, a responsive settings screen and a fix for the WordPress 6.7+ translation notice. Existing settings are kept.

= 1.1.0 =
New Swiss design, media-library logo picker, accent presets, embedded fonts and a Spanish translation. Existing settings are kept.
