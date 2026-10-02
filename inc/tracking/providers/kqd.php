<?php
namespace Bexstar\Tracking;
if (!defined('ABSPATH')) { exit; }

final class KqdAdapter implements ProviderAdapter {
    private $transport;
    public function __construct(KqdTransport $transport) { $this->transport=$transport; }
    public function code(): string { return 'kqd'; } // Registry convention is lowercase; business code: KQD.
    private function lookup(string $method, array $params): ?array {
        $r=$this->transport->request($method,$params);
        // Never classify success=0 as not-found without an official documented error code.
        if (!array_key_exists('success',$r)) { throw new TrackingError('malformed_response'); }
        if (!in_array($r['success'],[1,'1',true],true)) { throw new TrackingError('provider_unavailable'); }
        if (!array_key_exists('data',$r)) { throw new TrackingError('malformed_response'); }
        $data=$r['data'];
        if ($data===null || $data===[] || $data==='') { return null; }
        if (!is_array($data)) { throw new TrackingError('malformed_response'); }
        if (array_keys($data) === range(0, count($data)-1)) {
            if (count($data)!==1 || !is_array($data[0])) { throw new TrackingError('malformed_response'); }
            $data=$data[0];
        }
        if ($method==='gettrack') {
            if (isset($data['details']) && (!is_array($data['details']) || ($data['details'] && array_keys($data['details'])!==range(0,count($data['details'])-1)) || count($data['details'])>200)) { throw new TrackingError('malformed_response'); }
            if (!array_intersect(['details','track_status','track_status_name','track_status_ename','shipper_hawbcode'],array_keys($data))) { throw new TrackingError('malformed_response'); }
        }
        return $data;
    }
    private static function internalReference($value): ?string {
        try { return TrackingReference::parse(is_int($value) ? (string)$value : $value); }
        catch (TrackingError $e) { return null; }
    }
    public function fetch(array $reference): array {
        $number=TrackingReference::parse($reference['bexstar_reference'] ?? null);
        $sources=[]; $partial=false; $secrets=[KqdSettings::value('KQD_APP_TOKEN'),KqdSettings::value('KQD_APP_KEY')];
        $direct=$this->lookup('gettrack',['tracking_number'=>$number]);
        if ($direct!==null) { $sources[]=$direct; }
        else {
            $mapping=$this->lookup('gettrackingnumber',['reference_no'=>$number]);
            if ($mapping===null) { throw new TrackingError('not_found'); }
            if (isset($mapping['reference_no']) && self::internalReference($mapping['reference_no'])!==$number) { throw new TrackingError('malformed_response'); }
            $master=self::internalReference($mapping['shipping_method_no'] ?? null);
            if ($master && $master!==$number) {
                try { $row=$this->lookup('gettrack',['tracking_number'=>$master]); if ($row!==null) { $sources[]=$row; } }
                catch (TrackingError $e) { $partial=true; }
                $secrets[]=$master;
            }
            $packages=$mapping['packages'] ?? [];
            if (!is_array($packages)) { throw new TrackingError('malformed_response'); }
            $children=[];
            if (count($packages)>100) { $partial=true; }
            foreach (array_slice($packages,0,100) as $package) {
                $child=is_array($package) ? self::internalReference($package['child_tracknumber'] ?? null) : null;
                if (!$child) { $partial=true; continue; }
                if ($child!==$number && $child!==$master) { $children[$child]=true; }
            }
            // Bound request fan-out (direct + mapping + master + at most eight children).
            if (count($children)>8) { $partial=true; }
            $childSources=[];
            foreach (array_slice(array_keys($children),0,8) as $child) {
                $child=(string)$child; // PHP converts numeric-string array keys to integers.
                $secrets[]=$child;
                try { $row=$this->lookup('gettrack',['tracking_number'=>$child]); if ($row!==null) { $childSources[]=$row; } else { $partial=true; } }
                catch (TrackingError $e) { $partial=true; }
            }
            // Children are authoritative pieces; avoid duplicating an aggregate master timeline.
            if ($childSources) { $sources=$childSources; }
            if (!$sources) { throw new TrackingError($partial ? 'provider_unavailable' : 'not_found'); }
        }
        foreach ($sources as $source) {
            foreach (['shipper_hawbcode','server_hawbcode','channel_hawbcode','signatory_name','last_delivery_name','pod_url'] as $key) {
                if (isset($source[$key]) && is_string($source[$key]) && $source[$key]!==$number) { $secrets[]=$source[$key]; }
            }
        }
        return (new KqdNormalizer($secrets))->normalize($number,$sources,$partial);
    }
}
