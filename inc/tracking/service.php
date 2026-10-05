<?php
namespace Bexstar\Tracking;
if (!defined('ABSPATH')) { exit; }

final class TrackingService {
    private $resolver; private $store; private $registry; private $guard; private $live; private $enabled; private $ttl;
    public function __construct(Resolver $resolver,TrackingStore $store,AdapterRegistry $registry,LookupGuard $guard,bool $live,array $enabled,int $ttl=300) {
        $this->resolver=$resolver;$this->store=$store;$this->registry=$registry;$this->guard=$guard;
        $this->live=$live;$this->enabled=$enabled;$this->ttl=$ttl;
    }
    private $lookupSource='none';
    /** Internal diagnostic only; never included in the public API response. */
    public function lookupSource(): string { return $this->lookupSource; }
    public function lookup(string $reference, bool $bypassCache=false): array {
        $this->lookupSource='none';
        $reference=TrackingReference::parse($reference);
        $plan=$this->resolver->resolve($reference);
        $private=['KQD','开渠达','智行','大墨仓','安时达','商壹'];
        foreach (ProviderCatalog::definitions() as $definition) {
            foreach ($definition['keys'] as $key) { $v=Config::value($key); if ($v!=='') { $private[]=$v; } }
        }
        foreach ($plan['legs'] as $leg) {
            foreach (array_merge([$leg['primary']],$leg['fallbacks']) as $ref) {
                foreach (['provider_tracking_number','reference_id','leg_id'] as $key) {
                    if (isset($ref[$key]) && $ref[$key]!==$reference) { $private[]=$ref[$key]; }
                }
            }
        }
        $projector=new PublicResponse($private);
        // A kill switch stops both live and cached disclosure; pending adapters never run.
        if (!$this->live) { throw new TrackingError('provider_unavailable'); }
        foreach ($plan['legs'] as $leg) {
            if (!in_array($leg['primary']['provider_code'],$this->enabled,true)) { throw new TrackingError('provider_unavailable'); }
        }
        $cached=$bypassCache ? null : $this->store->cached($reference,time());
        if ($cached) { try { $result=$projector->project($cached,$reference);$this->lookupSource='cache';return $result; } catch (TrackingError $e) { /* Discard invalid cache and refresh. */ } }
        $token=$this->guard->acquire($reference,time());
        try {
            $results=[];$partial=false;$lastError=null;$deadline=microtime(true)+20;
            foreach ($plan['legs'] as $leg) {
                if (microtime(true)>$deadline) { $partial=true;$lastError=new TrackingError('timeout');continue; }
                $done=false;
                foreach (array_merge([$leg['primary']],$leg['fallbacks']) as $index=>$ref) {
                    // Aggregators require explicit mapped fallback + enabled config, never discovery.
                    if (!in_array($ref['provider_code'],$this->enabled,true)) { continue; }
                    if ($index>0 && (!$lastError || !in_array($lastError->reason(),['not_found','provider_unavailable','timeout'],true))) { break; }
                    try {
                        $this->lookupSource='provider';
                        $raw=$this->registry->get($ref['provider_code'])->fetch($ref);
                        $results[]=$projector->project($raw,$reference);$done=true;break;
                    } catch (TrackingError $e) { $lastError=$e; }
                    catch (\Throwable $e) { $lastError=new TrackingError('unavailable'); }
                }
                if (!$done) { $partial=true; }
            }
            if (!$results) { throw $lastError ?? new TrackingError('provider_unavailable'); }
            $result=$projector->merge($results,$reference,$partial);
            $this->store->remember($reference,$result,$partial ? min(30,$this->ttl) : $this->ttl,time());
            return $result;
        } finally { $this->guard->release($reference,$token); }
    }
}
