<?php
namespace Bexstar\Tracking;
if (!defined('ABSPATH')) { exit; }

class PendingAdapter implements ProviderAdapter {
    protected $provider;
    public function __construct(string $provider) { $this->provider=$provider; }
    public function code(): string { return $this->provider; }
    public function fetch(array $reference): array { throw new TrackingError('provider_unavailable'); }
}
final class ZxAdapter extends PendingAdapter {
    public function __construct() { parent::__construct('zx'); }
    // Known request: JSON POST /api/v1/common/tracking, FACTNO/SUPNO/SUPPASS/APPKEY/PACKNO.
    // No network until verified base URL and complete event response/status documentation.
}
final class NewWisdomAdapter extends PendingAdapter {
    public function __construct(string $provider) { parent::__construct($provider); }
    // Shared family implementation point. DMC is configuration, not a separate adapter.
    // Endpoint path/authentication/event schema must be documented before implementation.
}
