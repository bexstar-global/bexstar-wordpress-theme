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
    // Contract defaults only: no endpoints, secrets or live adapters.
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
