<?php
namespace Bexstar\Tracking;
if ( ! defined( 'ABSPATH' ) ) { exit; }

final class Config {
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
