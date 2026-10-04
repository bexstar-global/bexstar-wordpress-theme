<?php
namespace Bexstar\Tracking;
if (!defined('ABSPATH')) { exit; }

final class ProviderCatalog {
    /** Internal administrative metadata, never serialized by the public controller. */
    public static function definitions(): array {
        return [
            'kqd'=>['state'=>'controlled-validation','adapter'=>KqdAdapter::class,'keys'=>['KQD_API_ENDPOINT','KQD_APP_TOKEN','KQD_APP_KEY']],
            'zx'=>['state'=>'pending','adapter'=>ZxAdapter::class,'keys'=>['ZX_API_BASE_URL','ZX_FACTNO','ZX_SUPNO','ZX_SUPPASS','ZX_APPKEY']],
            'dmc'=>['state'=>'pending','adapter'=>NewWisdomAdapter::class,'keys'=>['DMC_GATEWAY','DMC_APP_CODE','DMC_TOKEN','DMC_INTEGRATION_CODE']],
            'anshida'=>['state'=>'pending','adapter'=>PendingAdapter::class,'keys'=>[]],
            'shangyi'=>['state'=>'pending','adapter'=>PendingAdapter::class,'keys'=>[]],
        ];
    }
    public static function registry(): AdapterRegistry {
        $registry=new AdapterRegistry();
        // Factory is called per API request. Shared transport budget bounds every KQD leg.
        if (Config::liveEnabled() && in_array('kqd',Config::enabledProviders(),true)) {
            $registry->register(new KqdAdapter(new WordPressKqdTransport(new RequestBudget())));
        }
        $registry->register(new ZxAdapter());
        $registry->register(new NewWisdomAdapter('dmc'));
        $registry->register(new PendingAdapter('anshida'));
        $registry->register(new PendingAdapter('shangyi'));
        return $registry;
    }
}

final class RequestBudget {
    private $deadline;
    private $remaining;
    public function __construct(int $seconds=20,int $calls=12) { $this->deadline=microtime(true)+$seconds; $this->remaining=$calls; }
    public function timeout(): float {
        $left=$this->deadline-microtime(true);
        if ($this->remaining--<=0 || $left<0.1) { throw new TrackingError('timeout'); }
        return min(6.0,$left);
    }
}
