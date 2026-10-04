<?php
namespace Bexstar\Tracking;
if ( ! defined( 'ABSPATH' ) ) { exit; }

// Public partner URL; a server-level definition may override this default.
if ( ! defined( 'BEXSTAR_17TRACK_CARRIER_URL' ) ) {
    define( 'BEXSTAR_17TRACK_CARRIER_URL', 'https://www.17track.net/en/carriers/bexstar-express' );
}

final class Config {
    /** Only a reviewed official carrier-page URL should be set in server configuration. */
    public static function partnerUrl(): string {
        $url = defined( 'BEXSTAR_17TRACK_CARRIER_URL' ) ? BEXSTAR_17TRACK_CARRIER_URL : '';
        if ( ! is_string( $url ) || ! filter_var( $url, FILTER_VALIDATE_URL ) ) { return ''; }
        $parts = parse_url( $url );
        $host = strtolower( $parts['host'] ?? '' );
        if ( 'https' !== ($parts['scheme'] ?? '') || isset($parts['user']) || isset($parts['pass']) || isset($parts['port']) || ! preg_match('/(?:^|\.)17track\.net$/', $host) ) { return ''; }
        return $url;
    }
    public static function value(string $key): string {
        $v = defined($key) ? constant($key) : getenv($key);
        return is_string($v) ? trim($v) : ($v === true ? '1' : '');
    }
    public static function apiEnabled(): bool { return self::value('BEXSTAR_TRACKING_API_ENABLED') === '1'; }
    public static function liveEnabled(): bool { return self::value('BEXSTAR_TRACKING_LIVE_ENABLED') === '1'; }
    public static function enabledProviders(): array {
        return array_values(array_filter(array_map('trim', explode(',', self::value('BEXSTAR_TRACKING_ENABLED_PROVIDERS')))));
    }
    public static function cacheTtl(): int {
        $v=self::value('BEXSTAR_TRACKING_CACHE_TTL');
        return $v === '' ? 300 : max(30, min(900, (int)$v));
    }
    // All activation switches default closed. No credentials are stored here.
    public static function defaults(): array {
        return array(
            'schema_version' => 1,
            'public_enabled' => false,
            'external_requests_enabled' => false,
            'enabled_adapters' => array(),
            'fallback_enabled' => false,
            'timeout_seconds' => 6,
            'response_limit_bytes' => 1048576,
            'cache_ttl_seconds' => 300,
            'max_legs_per_lookup' => 10,
            'max_fallbacks_per_leg' => 1,
            'show_provider' => false,
        );
    }
}
