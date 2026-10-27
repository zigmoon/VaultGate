<?php
/**
 * Pure access rules: client IP, CIDR ranges and path matching.
 *
 * @package VaultGate
 */

namespace VaultGate;

defined( 'ABSPATH' ) || exit;

/**
 * Stateless helpers used by the guard and fully unit-testable.
 */
final class AccessRules {

	/**
	 * Resolves the visitor IP address.
	 *
	 * Forwarded headers are only read when the administrator enabled
	 * "trust proxy" **and** the direct peer (`REMOTE_ADDR`) is a configured
	 * trusted proxy. The `X-Forwarded-For` chain is then walked from the right
	 * — the hop closest to us — and the first address that is not itself a
	 * trusted proxy wins, because every proxy appends to that header and an
	 * attacker can prepend anything.
	 *
	 * @param bool   $trust_proxy     Whether forwarded headers may be trusted.
	 * @param string $trusted_proxies Newline-separated IPs/CIDRs of the proxies.
	 */
	public static function client_ip( bool $trust_proxy, string $trusted_proxies = '' ): string {
		$remote = isset( $_SERVER['REMOTE_ADDR'] ) && is_string( $_SERVER['REMOTE_ADDR'] )
			? self::canonical( trim( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) )
			: '';

		if ( ! $trust_proxy || '' === $remote ) {
			return $remote;
		}

		// Fail closed: an untrusted peer cannot speak for the visitor.
		if ( ! self::is_trusted_proxy( $remote, $trusted_proxies ) ) {
			return $remote;
		}

		// Only `X-Forwarded-For` is read. Single-value headers such as
		// `CF-Connecting-IP`, `True-Client-IP` or `X-Real-IP` are deliberately
		// ignored: a plain nginx/Docker/Caddy proxy passes whatever the visitor
		// sent for headers it does not itself set, so they cannot be trusted.

		$chain = array();

		if ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) && is_string( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			foreach ( explode( ',', wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) as $hop ) {
				$hop = self::canonical( trim( $hop, " \t\n\r\0\x0B\"" ) );

				if ( '' !== $hop ) {
					$chain[] = $hop;
				}
			}
		}

		foreach ( array_reverse( $chain ) as $hop ) {
			if ( ! self::is_trusted_proxy( $hop, $trusted_proxies ) ) {
				return $hop;
			}
		}

		// The whole chain is made of trusted proxies: the nearest peer is the
		// best answer we have.
		return $remote;
	}

	/**
	 * Canonical form of an IP address, or an empty string when it is not one.
	 *
	 * @param string $candidate Raw candidate.
	 */
	private static function canonical( string $candidate ): string {
		if ( '' === $candidate || ! filter_var( $candidate, FILTER_VALIDATE_IP ) ) {
			return '';
		}

		$binary = inet_pton( $candidate );

		if ( false === $binary ) {
			return '';
		}

		$canonical = inet_ntop( $binary );

		return false !== $canonical ? $canonical : '';
	}

	/**
	 * Whether an address is one of the configured proxies.
	 *
	 * With no list configured, a loopback or private peer is accepted: that is
	 * the usual nginx/Docker reverse proxy on the same machine, and a remote
	 * visitor cannot forge `REMOTE_ADDR`.
	 *
	 * @param string $ip              Direct peer.
	 * @param string $trusted_proxies Newline-separated IPs/CIDRs.
	 */
	public static function is_trusted_proxy( string $ip, string $trusted_proxies ): bool {
		if ( '' === trim( $trusted_proxies ) ) {
			return self::is_private_ip( $ip );
		}

		return self::ip_matches( $ip, $trusted_proxies );
	}

	/**
	 * Whether an address is loopback, link-local or private.
	 *
	 * @param string $ip IP address.
	 */
	public static function is_private_ip( string $ip ): bool {
		if ( '' === $ip ) {
			return false;
		}

		if ( ! filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Whether a path targets one of the blocked direct endpoints.
	 *
	 * A suffix counts as a hit — `/wp-comments-post.php/x` is routed to
	 * `wp-comments-post.php` by Apache and nginx (PATH_INFO), so the script
	 * must stay blocked whatever follows its name.
	 *
	 * @param string             $path      Normalised request path.
	 * @param array<int, string> $endpoints Blocked endpoints.
	 */
	public static function is_blocked_endpoint( string $path, array $endpoints ): bool {
		$haystack = '/' . trim( $path, '/' );

		foreach ( $endpoints as $endpoint ) {
			$endpoint = '/' . trim( (string) $endpoint, '/' );

			if ( '' === $endpoint || '/' === $endpoint ) {
				continue;
			}

			if ( $haystack === $endpoint || str_ends_with( $haystack, $endpoint ) || str_contains( $haystack, $endpoint . '/' ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Whether an IP is present in a newline-separated allowlist.
	 *
	 * @param string $ip    Visitor IP.
	 * @param string $rules Allowlist (IPs and CIDR ranges).
	 */
	public static function ip_matches( string $ip, string $rules ): bool {
		if ( '' === $ip ) {
			return false;
		}

		foreach ( preg_split( '/\r\n|\r|\n/', $rules ) ?: array() as $rule ) {
			$rule = trim( (string) preg_replace( '/\s+#.*$/', '', (string) $rule ) );

			if ( '' === $rule ) {
				continue;
			}

			if ( str_contains( $rule, '/' ) ) {
				if ( self::ip_in_range( $ip, $rule ) ) {
					return true;
				}

				continue;
			}

			$left  = inet_pton( $ip );
			$right = inet_pton( $rule );

			if ( false !== $left && false !== $right && $left === $right ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Whether an IP belongs to a CIDR range (IPv4 or IPv6).
	 *
	 * @param string $ip    Visitor IP.
	 * @param string $range CIDR range.
	 */
	public static function ip_in_range( string $ip, string $range ): bool {
		$parts = explode( '/', $range, 2 );

		if ( 2 !== count( $parts ) || ! ctype_digit( $parts[1] ) ) {
			return false;
		}

		$ip_bin     = inet_pton( $ip );
		$subnet_bin = inet_pton( $parts[0] );

		if ( false === $ip_bin || false === $subnet_bin || strlen( $ip_bin ) !== strlen( $subnet_bin ) ) {
			return false;
		}

		$prefix = (int) $parts[1];
		$bits   = strlen( $ip_bin ) * 8;

		if ( $prefix < 0 || $prefix > $bits ) {
			return false;
		}

		$full_bytes = intdiv( $prefix, 8 );
		$remainder  = $prefix % 8;

		if ( $full_bytes > 0 && substr( $ip_bin, 0, $full_bytes ) !== substr( $subnet_bin, 0, $full_bytes ) ) {
			return false;
		}

		if ( 0 === $remainder ) {
			return true;
		}

		$mask = ( 0xFF << ( 8 - $remainder ) ) & 0xFF;

		return ( ord( $ip_bin[ $full_bytes ] ) & $mask ) === ( ord( $subnet_bin[ $full_bytes ] ) & $mask );
	}

	/**
	 * Path of the current request, decoded and normalised.
	 *
	 * Dot segments are resolved here because the web server resolves them
	 * before routing, while `REQUEST_URI` keeps the raw target: comparing an
	 * unnormalised path against the endpoint blocklist or the exclusion list
	 * would let `/wp-comments-post.php/x/..` reach a blocked script, or let an
	 * excluded prefix swallow a different path.
	 *
	 * @param string|null $uri Optional URI instead of the current request.
	 */
	public static function request_path( ?string $uri = null ): string {
		if ( null === $uri ) {
			$uri = isset( $_SERVER['REQUEST_URI'] ) && is_string( $_SERVER['REQUEST_URI'] )
				? wp_unslash( $_SERVER['REQUEST_URI'] )
				: '/';
		}

		$path = wp_parse_url( $uri, PHP_URL_PATH );

		if ( ! is_string( $path ) || '' === $path ) {
			return '/';
		}

		$path = rawurldecode( $path );
		$path = preg_replace( '#/+#', '/', $path );
		$path = is_string( $path ) ? $path : '/';

		return self::collapse_dot_segments( '/' . ltrim( $path, '/' ) );
	}

	/**
	 * Resolves `.` and `..` the way the web server does before routing.
	 *
	 * @param string $path Absolute path.
	 */
	public static function collapse_dot_segments( string $path ): string {
		$last     = substr( $path, (int) strrpos( $path, '/' ) + 1 );
		$trailing = '' === $last || '..' === $last;
		$segments = explode( '/', $path );
		$resolved = array();

		foreach ( $segments as $segment ) {
			if ( '' === $segment || '.' === $segment ) {
				continue;
			}

			if ( '..' === $segment ) {
				array_pop( $resolved );
				continue;
			}

			$resolved[] = $segment;
		}

		$normalised = '/' . implode( '/', $resolved );

		if ( $trailing && '/' !== $normalised ) {
			$normalised .= '/';
		}

		return $normalised;
	}

	/**
	 * Removes the WordPress installation subdirectory from a path.
	 *
	 * On a site installed in `/blog`, the request `/blog/api/x` is matched
	 * against the rules as `/api/x`, so that configuration written once works
	 * for the root and for subdirectory installs alike.
	 *
	 * @param string        $path Normalised request path.
	 * @param string|false  $base Installation base path, for example `/blog`.
	 */
	public static function strip_base( string $path, string|false $base ): string {
		if ( ! is_string( $base ) ) {
			return $path;
		}

		$base = rtrim( $base, '/' );

		if ( '' === $base || '/' === $base ) {
			return $path;
		}

		if ( $path === $base ) {
			return '/';
		}

		if ( str_starts_with( $path, $base . '/' ) ) {
			return '/' . ltrim( substr( $path, strlen( $base ) ), '/' );
		}

		return $path;
	}

	/**
	 * Whether the current path is excluded by one of the rules.
	 *
	 * Rules are path prefixes; `*` is accepted as a wildcard. For example:
	 * `/robots.txt`, `/api/*`, `/preview*`.
	 *
	 * @param string $path     Normalised request path.
	 * @param string $patterns Newline-separated rules.
	 */
	public static function path_matches( string $path, string $patterns ): bool {
		$path = '' === $path ? '/' : $path;
		$path = '/' === $path ? '/' : rtrim( $path, '/' );

		foreach ( preg_split( '/\r\n|\r|\n/', $patterns ) ?: array() as $pattern ) {
			$pattern = trim( (string) $pattern );

			if ( '' === $pattern || str_starts_with( $pattern, '#' ) ) {
				continue;
			}

			$pattern = '/' . ltrim( $pattern, '/' );

			if ( str_contains( $pattern, '*' ) ) {
				$regex = '#^' . str_replace( '\*', '.*', preg_quote( $pattern, '#' ) ) . '$#D';

				if ( str_ends_with( $pattern, '/*' ) && $path === substr( $pattern, 0, -2 ) ) {
					return true;
				}

				if ( 1 === preg_match( $regex, $path ) ) {
					return true;
				}

				continue;
			}

			$pattern = '/' === $pattern ? '/' : rtrim( $pattern, '/' );

			if ( $path === $pattern || str_starts_with( $path, $pattern . '/' ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * HTTPS detection, optionally trusting a reverse proxy.
	 *
	 * @param bool $trust_proxy Whether forwarded headers may be trusted.
	 */
	public static function is_https( bool $trust_proxy ): bool {
		if ( function_exists( 'is_ssl' ) && is_ssl() ) {
			return true;
		}

		if ( ! $trust_proxy ) {
			return false;
		}

		$proto = isset( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) && is_string( $_SERVER['HTTP_X_FORWARDED_PROTO'] )
			? strtolower( trim( explode( ',', wp_unslash( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) )[0] ) )
			: '';

		if ( 'https' === $proto ) {
			return true;
		}

		$ssl = isset( $_SERVER['HTTP_X_FORWARDED_SSL'] ) && is_string( $_SERVER['HTTP_X_FORWARDED_SSL'] )
			? strtolower( trim( wp_unslash( $_SERVER['HTTP_X_FORWARDED_SSL'] ) ) )
			: '';

		return 'on' === $ssl;
	}
}
