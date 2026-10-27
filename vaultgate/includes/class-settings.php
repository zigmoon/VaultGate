<?php
/**
 * Settings storage, defaults and sanitisation.
 *
 * @package VaultGate
 */

namespace VaultGate;

defined( 'ABSPATH' ) || exit;

/**
 * Reads and writes the plugin options.
 */
final class Settings {

	public const OPTION              = 'vaultgate_settings';
	public const HASH_OPTION         = 'vaultgate_password_hash';
	public const AUTH_VERSION_OPTION = 'vaultgate_auth_version';
	public const CHANGED_OPTION      = 'vaultgate_password_changed';
	public const LOG_OPTION          = 'vaultgate_log';
	public const VERSION_OPTION      = 'vaultgate_version';
	public const WELCOME_OPTION      = 'vaultgate_welcome';

	/**
	 * Runtime cache for the merged settings array.
	 *
	 * @var array<string, mixed>|null
	 */
	private ?array $cache = null;

	/**
	 * Plugin defaults.
	 *
	 * These values are written to the database, so they must never be
	 * translated: a site that switches language would keep the old strings.
	 * User-facing fallbacks are translated where they are rendered.
	 *
	 * @return array<string, mixed>
	 */
	public static function defaults(): array {
		return array(
			'enabled'            => false,
			'gate_title'         => '',
			'gate_message'       => '',
			'logo_id'            => 0,
			'logo_url'           => '',
			'accent'             => '#0b0d10',
			'theme'              => 'light',
			'session_days'       => 7,
			'remember_enabled'   => true,
			'remember_days'      => 30,
			'bypass_capability'  => 'manage_options',
			'ip_allowlist'       => '',
			'trust_proxy'        => false,
			'protect_rest'       => true,
			'protect_feeds'      => true,
			'protect_sitemaps'   => true,
			'protect_ajax'       => true,
			'protect_xmlrpc'     => true,
			'exclude_paths'      => "/favicon.ico\n/robots.txt",
			'max_attempts'       => 5,
			'lockout_minutes'    => 15,
			'log_enabled'        => true,
			'delete_data'        => true,
		);
	}

	/**
	 * Colour presets offered in the settings screen. Monochrome first, like
	 * the rest of the interface: black is the default.
	 *
	 * @return array<int, array{value: string, label: string}>
	 */
	public static function swatches(): array {
		$swatches = array(
			array(
				'value' => '#0b0d10',
				'label' => __( 'Ink', 'vaultgate' ),
			),
			array(
				'value' => '#44505f',
				'label' => __( 'Slate', 'vaultgate' ),
			),
			array(
				'value' => '#0a66ff',
				'label' => __( 'Blue', 'vaultgate' ),
			),
			array(
				'value' => '#0f7a53',
				'label' => __( 'Green', 'vaultgate' ),
			),
			array(
				'value' => '#9a5b00',
				'label' => __( 'Amber', 'vaultgate' ),
			),
			array(
				'value' => '#b42318',
				'label' => __( 'Red', 'vaultgate' ),
			),
			array(
				'value' => '#6d28d9',
				'label' => __( 'Violet', 'vaultgate' ),
			),
			array(
				'value' => '#be123c',
				'label' => __( 'Rose', 'vaultgate' ),
			),
		);

		/**
		 * Filters the accent colour presets.
		 *
		 * @param array<int, array{value: string, label: string}> $swatches Presets.
		 */
		return (array) apply_filters( 'vaultgate_swatches', $swatches );
	}

	/**
	 * Capability keys accepted as a bypass rule. Plain values, no translation:
	 * this list is used for validation, which can happen before `init`.
	 *
	 * @return array<int, string>
	 */
	public static function capability_keys(): array {
		return array( 'manage_options', 'edit_others_posts', 'edit_posts', 'read' );
	}

	/**
	 * Capabilities offered as "can bypass" choices, with translated labels.
	 * Rendered in the settings screen only.
	 *
	 * @return array<string, string>
	 */
	public static function capabilities(): array {
		$capabilities = array(
			'manage_options'    => __( 'Administrators — manage_options', 'vaultgate' ),
			'edit_others_posts' => __( 'Editors and above — edit_others_posts', 'vaultgate' ),
			'edit_posts'        => __( 'Authors and above — edit_posts', 'vaultgate' ),
			'read'              => __( 'Any logged-in user — read', 'vaultgate' ),
		);

		/**
		 * Filters the capabilities that may bypass the gate.
		 *
		 * @param array<string, string> $capabilities Capability => label.
		 */
		return (array) apply_filters( 'vaultgate_bypass_capabilities', $capabilities );
	}

	/**
	 * All settings, merged with defaults.
	 *
	 * @return array<string, mixed>
	 */
	public function all(): array {
		if ( null === $this->cache ) {
			$stored = get_option( self::OPTION, array() );
			$stored = is_array( $stored ) ? $stored : array();
			$merged = self::defaults();

			foreach ( $stored as $key => $value ) {
				if ( array_key_exists( $key, $merged ) ) {
					$merged[ $key ] = $value;
				}
			}

			$this->cache = $merged;
		}

		return $this->cache;
	}

	/**
	 * One setting.
	 *
	 * @param string $key      Setting key.
	 * @param mixed  $fallback Value returned when the key is unknown.
	 * @return mixed
	 */
	public function get( string $key, mixed $fallback = null ): mixed {
		$all = $this->all();

		return array_key_exists( $key, $all ) ? $all[ $key ] : $fallback;
	}

	/**
	 * Clears the runtime cache after an external option update.
	 */
	public function refresh(): void {
		$this->cache = null;
	}

	/**
	 * Applies a partial update, sanitised and merged with the current values.
	 *
	 * @param array<string, mixed> $patch Values to change.
	 */
	public function update( array $patch ): void {
		$stored = get_option( self::OPTION, array() );
		$stored = is_array( $stored ) ? $stored : array();
		$merged = array_merge( self::defaults(), $stored, $patch );

		update_option( self::OPTION, self::sanitize( $merged ) );
		$this->cache = null;
	}

	/**
	 * Whether the gate is active. A password is mandatory: this prevents an
	 * unrecoverable lock-out if the option is toggled without a secret.
	 */
	public function is_enabled(): bool {
		if ( defined( 'VAULTGATE_DISABLED' ) && VAULTGATE_DISABLED ) {
			return false;
		}

		return (bool) $this->get( 'enabled' ) && $this->has_password();
	}

	/**
	 * Whether a password hash exists.
	 */
	public function has_password(): bool {
		return '' !== $this->password_hash();
	}

	/**
	 * Stored password hash.
	 */
	public function password_hash(): string {
		$hash = get_option( self::HASH_OPTION, '' );

		return is_string( $hash ) ? $hash : '';
	}

	/**
	 * Verifies a candidate password with WordPress' hashing API.
	 *
	 * @param string $password Candidate password.
	 */
	public function verify_password( string $password ): bool {
		$hash = $this->password_hash();

		return '' !== $hash && wp_check_password( $password, $hash );
	}

	/**
	 * Whether a password meets the minimum length (characters, not bytes).
	 *
	 * @param string $password Candidate password.
	 */
	public static function is_password_long_enough( string $password ): bool {
		if ( function_exists( 'mb_strlen' ) ) {
			return mb_strlen( $password, 'UTF-8' ) >= 8;
		}

		$count = preg_match_all( '/./us', $password );

		return false !== $count ? $count >= 8 : strlen( $password ) >= 8;
	}

	/**
	 * Stores a new password hash. The minimum length is enforced again here so
	 * that no code path (CLI included) can set a weak secret.
	 *
	 * @param string $password Plain password.
	 */
	public function set_password( string $password ): bool {
		if ( ! self::is_password_long_enough( $password ) ) {
			return false;
		}

		update_option( self::HASH_OPTION, wp_hash_password( $password ), false );
		update_option( self::CHANGED_OPTION, time(), false );
		$this->bump_auth_version();
		$this->refresh();

		return true;
	}

	/**
	 * When the password was last changed (Unix timestamp).
	 */
	public function password_changed_at(): int {
		return absint( get_option( self::CHANGED_OPTION, 0 ) );
	}

	/**
	 * Session version. Bumping it invalidates every issued cookie.
	 */
	public function auth_version(): int {
		return max( 1, absint( get_option( self::AUTH_VERSION_OPTION, 1 ) ) );
	}

	/**
	 * Invalidates every existing gate cookie.
	 */
	public function bump_auth_version(): int {
		$version = $this->auth_version() + 1;
		update_option( self::AUTH_VERSION_OPTION, $version, false );

		return $version;
	}

	/**
	 * Removes the short-lived transients the plugin creates (rate-limit
	 * counters and log throttles). Called on deactivation so that turning the
	 * plugin off leaves nothing behind.
	 */
	public function purge_transients(): void {
		global $wpdb;

		if ( ! isset( $wpdb->options ) || ! is_object( $wpdb ) || ! method_exists( $wpdb, 'query' ) ) {
			return;
		}

		$wpdb->query(
			"DELETE FROM {$wpdb->options} WHERE option_name LIKE '\_transient\_vaultgate\_%' OR option_name LIKE '\_transient\_timeout\_vaultgate\_%'"
		);
	}

	/**
	 * Appends an entry to the capped activity log.
	 *
	 * @param string $event Short event key.
	 * @param string $ip    Optional visitor IP (already resolved by the guard).
	 */
	public function log( string $event, string $ip = '' ): void {
		if ( ! $this->get( 'log_enabled' ) ) {
			return;
		}

		$throttled = array( 'ajax_blocked', 'rest_blocked', 'xmlrpc_blocked', 'endpoint_blocked', 'honeypot', 'form_expired', 'login_locked', 'logout' );

		/**
		 * Filters which high-frequency events are throttled to one entry per minute.
		 *
		 * @param array<int, string> $throttled Event keys.
		 */
		$throttled = (array) apply_filters( 'vaultgate_throttled_log_events', $throttled );
		$throttle  = 'vaultgate_logged_' . sanitize_key( $event );

		if ( in_array( $event, $throttled, true ) && get_transient( $throttle ) ) {
			return;
		}

		if ( in_array( $event, $throttled, true ) ) {
			set_transient( $throttle, 1, MINUTE_IN_SECONDS );
		}

		if ( '' === $ip && isset( $_SERVER['REMOTE_ADDR'] ) && is_string( $_SERVER['REMOTE_ADDR'] ) ) {
			$ip = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
		}

		if ( '' !== $ip && function_exists( 'wp_privacy_anonymize_ip' ) ) {
			$masked = wp_privacy_anonymize_ip( $ip );
			$ip     = is_string( $masked ) ? $masked : '';
		}

		$agent = isset( $_SERVER['HTTP_USER_AGENT'] ) && is_string( $_SERVER['HTTP_USER_AGENT'] )
			? substr( sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ), 0, 120 )
			: '';

		$entry = array(
			'time'  => time(),
			'event' => sanitize_key( $event ),
			'ip'    => $ip,
			'agent' => $agent,
		);

		$log = $this->logs();
		array_unshift( $log, $entry );

		/**
		 * Filters how many log entries are kept.
		 *
		 * @param int $size Number of entries.
		 */
		$size = max( 1, (int) apply_filters( 'vaultgate_log_size', 20 ) );

		update_option( self::LOG_OPTION, array_slice( $log, 0, $size ), false );
	}

	/**
	 * Activity log, newest first.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function logs(): array {
		$log = get_option( self::LOG_OPTION, array() );

		if ( ! is_array( $log ) ) {
			return array();
		}

		$clean = array();

		foreach ( $log as $entry ) {
			if ( ! is_array( $entry ) ) {
				continue;
			}

			$clean[] = array(
				'time'  => (int) ( $entry['time'] ?? 0 ),
				'event' => sanitize_key( (string) ( $entry['event'] ?? '' ) ),
				'ip'    => (string) ( $entry['ip'] ?? '' ),
				'agent' => (string) ( $entry['agent'] ?? '' ),
			);
		}

		return $clean;
	}

	/**
	 * Deletes the activity log.
	 */
	public function clear_log(): void {
		delete_option( self::LOG_OPTION );
	}

	/**
	 * Reads a scalar input as a string. Arrays and objects yield an empty
	 * string instead of a PHP "array to string" warning: a settings field is
	 * always a single value, even when the request tries otherwise.
	 *
	 * @param mixed $value Raw value.
	 */
	private static function scalar( mixed $value ): string {
		return is_scalar( $value ) ? (string) $value : '';
	}

	/**
	 * Sanitises a full settings array.
	 *
	 * @param array<string, mixed> $input Raw input.
	 * @return array<string, mixed>
	 */
	public static function sanitize( array $input ): array {
		$defaults = self::defaults();
		$clean    = array();

		foreach ( array( 'enabled', 'remember_enabled', 'trust_proxy', 'protect_rest', 'protect_feeds', 'protect_sitemaps', 'protect_ajax', 'protect_xmlrpc', 'log_enabled', 'delete_data' ) as $flag ) {
			$value        = $input[ $flag ] ?? null;
			$clean[ $flag ] = is_scalar( $value ) && ! empty( $value );
		}

		$clean['gate_title'] = trim( sanitize_text_field( self::scalar( $input['gate_title'] ?? '' ) ) );

		$clean['gate_message'] = wp_kses_post( self::scalar( $input['gate_message'] ?? '' ) );

		$clean['logo_id']  = max( 0, (int) self::scalar( $input['logo_id'] ?? '' ) );
		$logo              = esc_url_raw( trim( self::scalar( $input['logo_url'] ?? '' ) ) );
		$logo_scheme       = '' !== $logo ? strtolower( (string) wp_parse_url( $logo, PHP_URL_SCHEME ) ) : '';
		if ( '' !== $logo && ! in_array( $logo_scheme, array( 'http', 'https' ), true ) ) {
			$logo = '';
		}
		$clean['logo_url'] = $logo;

		$accent               = sanitize_hex_color( self::scalar( $input['accent'] ?? '' ) );
		$clean['accent']      = self::normalize_hex( $accent ? $accent : $defaults['accent'] );
		$theme                = sanitize_key( self::scalar( $input['theme'] ?? '' ) );
		$clean['theme']       = in_array( $theme, array( 'auto', 'light', 'dark' ), true ) ? $theme : 'light';
		$clean['session_days']  = min( 365, max( 1, absint( self::scalar( $input['session_days'] ?? '' ) ) ?: (int) $defaults['session_days'] ) );
		$clean['remember_days'] = min( 365, max( 1, absint( self::scalar( $input['remember_days'] ?? '' ) ) ?: (int) $defaults['remember_days'] ) );

		$capability = sanitize_key( self::scalar( $input['bypass_capability'] ?? '' ) );
		$clean['bypass_capability'] = in_array( $capability, self::capability_keys(), true )
			? $capability
			: $defaults['bypass_capability'];

		$clean['ip_allowlist']  = self::sanitize_ip_list( self::scalar( $input['ip_allowlist'] ?? '' ) );
		$clean['exclude_paths'] = self::sanitize_path_list( self::scalar( $input['exclude_paths'] ?? '' ) );

		$clean['max_attempts']    = min( 50, max( 1, absint( self::scalar( $input['max_attempts'] ?? '' ) ) ?: (int) $defaults['max_attempts'] ) );
		$clean['lockout_minutes'] = min( 1440, max( 1, absint( self::scalar( $input['lockout_minutes'] ?? '' ) ) ?: (int) $defaults['lockout_minutes'] ) );

		return $clean;
	}

	/**
	 * Normalises a hex colour to the 6-digit form required by <input type="color">.
	 *
	 * @param string $color Hex colour.
	 */
	public static function normalize_hex( string $color ): string {
		$color = strtolower( trim( $color ) );

		if ( preg_match( '/^#([0-9a-f])([0-9a-f])([0-9a-f])$/', $color, $matches ) ) {
			return '#' . $matches[1] . $matches[1] . $matches[2] . $matches[2] . $matches[3] . $matches[3];
		}

		return preg_match( '/^#[0-9a-f]{6}$/', $color ) ? $color : '#0b0d10';
	}

	/**
	 * Relative luminance of a hex colour (WCAG 2.1 definition).
	 *
	 * @param string $hex 6-digit hex colour.
	 */
	public static function luminance( string $hex ): float {
		$hex = ltrim( self::normalize_hex( $hex ), '#' );

		$channels = array(
			hexdec( substr( $hex, 0, 2 ) ) / 255,
			hexdec( substr( $hex, 2, 2 ) ) / 255,
			hexdec( substr( $hex, 4, 2 ) ) / 255,
		);

		$linear = array_map(
			static function ( float $channel ): float {
				return $channel <= 0.04045 ? $channel / 12.92 : ( ( $channel + 0.055 ) / 1.055 ) ** 2.4;
			},
			$channels
		);

		return 0.2126 * $linear[0] + 0.7152 * $linear[1] + 0.0722 * $linear[2];
	}

	/**
	 * Readable text colour on top of an accent colour.
	 *
	 * @param string $hex 6-digit hex colour.
	 */
	public static function ink_on( string $hex ): string {
		return self::luminance( $hex ) > 0.48 ? '#0b0d10' : '#ffffff';
	}

	/**
	 * Accent colour to use on the dark theme.
	 *
	 * A near-black accent would disappear on a near-black page, so dark themes
	 * fall back to white for the button and keep the chosen hue for highlights.
	 *
	 * @param string $hex 6-digit hex colour.
	 */
	public static function accent_for_dark( string $hex ): string {
		return self::luminance( $hex ) < 0.06 ? '#f4f4f5' : self::normalize_hex( $hex );
	}

	/**
	 * Keeps only valid IP addresses, CIDR ranges and comments-free lines.
	 *
	 * @param string $raw Textarea content.
	 */
	public static function sanitize_ip_list( string $raw ): string {
		$lines = preg_split( '/\r\n|\r|\n/', $raw );
		$lines = is_array( $lines ) ? $lines : array();
		$out   = array();

		foreach ( $lines as $line ) {
			$line = trim( (string) preg_replace( '/\s+#.*$/', '', (string) $line ) );

			if ( '' === $line ) {
				continue;
			}

			if ( str_contains( $line, '/' ) ) {
				list( $ip, $prefix ) = array_pad( explode( '/', $line, 2 ), 2, '' );
				$ip     = trim( $ip );
				$prefix = trim( $prefix );

				if ( ! filter_var( $ip, FILTER_VALIDATE_IP ) || ! ctype_digit( $prefix ) ) {
					continue;
				}

				$binary = inet_pton( $ip );
				if ( false === $binary ) {
					continue;
				}

				$max = 4 === strlen( $binary ) ? 32 : 128;
				if ( (int) $prefix > $max ) {
					continue;
				}

				$canonical = inet_ntop( $binary );
				if ( false !== $canonical ) {
					$out[] = $canonical . '/' . (int) $prefix;
				}

				if ( count( $out ) >= 100 ) {
					break;
				}

				continue;
			}

			if ( ! filter_var( $line, FILTER_VALIDATE_IP ) ) {
				continue;
			}

			$binary    = inet_pton( $line );
			$canonical = false !== $binary ? inet_ntop( $binary ) : false;

			if ( false !== $canonical ) {
				$out[] = $canonical;
			}

			if ( count( $out ) >= 100 ) {
				break;
			}
		}

		return implode( "\n", array_values( array_unique( $out ) ) );
	}

	/**
	 * Normalises a list of path rules (one per line, optional `*` wildcard).
	 *
	 * @param string $raw Textarea content.
	 */
	public static function sanitize_path_list( string $raw ): string {
		$lines = preg_split( '/\r\n|\r|\n/', $raw );
		$lines = is_array( $lines ) ? $lines : array();
		$out   = array();

		foreach ( $lines as $line ) {
			$line = trim( (string) $line );

			if ( '' === $line || str_starts_with( $line, '#' ) ) {
				continue;
			}

			$line = (string) preg_replace( '/[?#].*$/', '', $line );
			$line = '/' . ltrim( $line, '/' );

			if ( preg_match( '/\s/', $line ) || str_contains( $line, '..' ) ) {
				continue;
			}

			$out[] = $line;

			if ( count( $out ) >= 100 ) {
				break;
			}
		}

		return implode( "\n", array_values( array_unique( $out ) ) );
	}
}
