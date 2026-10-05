<?php
namespace Bexstar\Tracking;
if (!defined('ABSPATH')) { exit; }

/** Enforces the scope on the real resolver's plan, before any cache or provider access. */
final class KqdValidationResolver implements Resolver {
    private $resolver;
    public function __construct(Resolver $resolver) { $this->resolver=$resolver; }
    public function resolve(string $reference): array {
        $plan=$this->resolver->resolve($reference);
        if (!$plan['legs']) { throw new TrackingError('not_mapped'); }
        foreach ($plan['legs'] as $leg) {
            if ($leg['primary']['provider_code']!=='kqd' || $leg['fallbacks']) { throw new TrackingError('not_mapped'); }
        }
        return $plan;
    }
}

/** Counts transport attempts without recording parameters, credentials or raw results. */
final class ObservedKqdTransport implements KqdTransport {
    private $transport;
    private $attempts=0;
    public function __construct(KqdTransport $transport) { $this->transport=$transport; }
    public function request(string $method,array $params): array {
        $this->attempts++;
        return $this->transport->request($method,$params);
    }
    public function attempts(): int { return $this->attempts; }
    public function reset(): void { $this->attempts=0; }
}

final class ControlledKqdValidation {
    private $service;private $transport;private $guard;
    public function __construct(TrackingStore $store,LookupGuard $guard,KqdTransport $transport) {
        $this->transport=new ObservedKqdTransport($transport);$this->guard=$guard;
        $registry=new AdapterRegistry();$registry->register(new KqdAdapter($this->transport));
        $resolver=new KqdValidationResolver(new InternalMappingResolver($store));
        // Request-local permission only. Never alter the global API/live/provider configuration.
        $this->service=new TrackingService($resolver,$store,$registry,$guard,true,['kqd'],Config::cacheTtl());
    }
    public static function production(): self {
        global $wpdb;
        return new self(new WordPressTrackingStore($wpdb),new WordPressLookupGuard($wpdb),new WordPressKqdTransport(new RequestBudget()));
    }
    public function run(string $reference,bool $bypassCache=false): array {
        $reference=TrackingReference::parse($reference);$this->transport->reset();
        $this->guard->consume('controlled-kqd-cli',$reference,time());
        $data=$this->service->lookup($reference,$bypassCache);
        $source=$this->service->lookupSource();
        return [
            'public_response'=>['success'=>true,'reference'=>$reference]+$data,
            'internal_diagnostics'=>[
                'provider'=>'kqd', 'source'=>$source==='cache' ? 'cache_hit' : 'provider_request',
                'cache_bypassed'=>$bypassCache, 'provider_request_count'=>$this->transport->attempts(),
                // Never claim credential validity from a cache hit, or full acceptance from partial data.
                'live_authentication_test'=>$source==='cache' ? 'not_performed_cache_hit' : ($data['meta']['partial'] ? 'incomplete_partial_result' : 'provider_response_received'),
                'latest_event'=>PublicResponse::latestEvent($data['events']),
            ],
        ];
    }
}

final class KqdValidationCommand {
    private $factory;
    public function __construct(?callable $factory=null) { $this->factory=$factory ?? [ControlledKqdValidation::class,'production']; }
    public function __invoke(array $args,array $assoc): void {
        try {
            if (KqdSettings::value('BEXSTAR_KQD_LIVE_TEST_ENABLED')!=='1' || ($assoc['approved'] ?? false)!==true) {
                throw new TrackingError('unavailable');
            }
            if (count($args)!==1 || array_diff(array_keys($assoc),['approved','bypass-cache'])
                || (isset($assoc['bypass-cache']) && $assoc['bypass-cache']!==true)) { throw new TrackingError('invalid_number'); }
            $reference=TrackingReference::parse($args[0]);
            KqdSettings::credentials();
            $runner=($this->factory)();
            $report=$runner->run($reference,isset($assoc['bypass-cache']));
            \WP_CLI::line(json_encode($report,JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
        } catch (\Throwable $e) {
            $reason=$e instanceof TrackingError ? $e->reason() : 'internal';
            $codes=['not_found'=>'TRACKING_NOT_FOUND','not_mapped'=>'PROVIDER_NOT_MAPPED',
                'provider_unavailable'=>'PROVIDER_UNAVAILABLE','configuration'=>'PROVIDER_UNAVAILABLE',
                'malformed_response'=>'PROVIDER_UNAVAILABLE','auth_error'=>'PROVIDER_AUTH_ERROR',
                'timeout'=>'PROVIDER_TIMEOUT','invalid_number'=>'INVALID_REQUEST','rate_limited'=>'RATE_LIMITED',
                'unavailable'=>'TRACKING_UNAVAILABLE'];
            $message=$e instanceof TrackingError ? $e->getMessage() : 'Tracking information is temporarily unavailable.';
            \WP_CLI::error(($codes[$reason] ?? 'INTERNAL_ERROR').': '.$message);
        }
    }
}
