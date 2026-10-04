<?php
namespace Bexstar\Tracking;
if (!defined('ABSPATH')) { exit; }

/** Explicit allowlist: raw adapter responses never cross this boundary. */
final class PublicResponse {
    public const STATUSES=['shipment_created','cargo_received','warehouse_processing','departed_origin','in_transit',
        'arrived_destination','customs_clearance','customs_released','out_for_delivery','delivered','exception','unknown'];
    private $privateValues;
    public function __construct(array $privateValues=[]) {
        $this->privateValues=array_values(array_filter($privateValues,static fn($v)=>is_string($v) && $v!==''));
    }
    private function text($value): ?string {
        if (!is_string($value) || !preg_match('//u',$value)) { return null; }
        $value=strip_tags($value);
        if ($this->privateValues) { $value=str_ireplace($this->privateValues,'[redacted]',$value); }
        $value=preg_replace('/[\x00-\x1F\x7F]/u',' ',$value);
        preg_match('/^.{0,500}/us',trim($value),$m);
        return ($m[0] ?? '') !== '' ? $m[0] : null;
    }
    public static function date($value): ?string {
        if (!is_string($value) || !preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(?:Z|[+-]\d{2}:\d{2})$/',$value)) { return null; }
        try {
            $d=new \DateTimeImmutable($value);
            $errors=\DateTimeImmutable::getLastErrors();
            if ($errors && ($errors['warning_count'] || $errors['error_count'])) { return null; }
            return $d->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d\TH:i:s\Z');
        } catch (\Throwable $e) { return null; }
    }
    public static function status($value): string { return in_array($value,self::STATUSES,true) ? $value : 'unknown'; }
    public function project(array $data,string $reference): array {
        if (($data['schema_version'] ?? null)!==1 || !isset($data['shipment'],$data['events'],$data['meta'])
            || !is_array($data['shipment']) || !is_array($data['events']) || !is_array($data['meta'])
            || ($data['shipment']['tracking_number'] ?? null)!==$reference || count($data['events'])>2000
            || !is_bool($data['meta']['partial'] ?? null) || !is_bool($data['meta']['stale'] ?? null)) {
            throw new TrackingError('malformed_response');
        }
        $shipment=$data['shipment']; $events=[]; $pieces=[]; $partial=($data['meta']['partial'] ?? false)===true;
        foreach ($data['events'] as $row) {
            if (!is_array($row)) { $partial=true; continue; }
            $leg=null;
            if (isset($row['leg_id']) && is_string($row['leg_id'])) {
                if (!isset($pieces[$row['leg_id']])) { $pieces[$row['leg_id']]='piece-'.(count($pieces)+1); }
                $leg=$pieces[$row['leg_id']];
            }
            $status=self::status($row['normalized_status'] ?? null);
            $event=['leg_id'=>$leg,'timestamp'=>self::date($row['timestamp'] ?? null),
                'location'=>$this->text($row['location'] ?? null),'status'=>str_replace('_',' ',$status),
                'description'=>$this->text($row['description'] ?? null),'normalized_status'=>$status];
            $id=hash('sha256',json_encode($event));
            $events[$id]=['event_id'=>$id]+$event;
        }
        $events=array_values($events);
        usort($events,static fn($a,$b)=>strcmp($a['timestamp'] ?? '~',$b['timestamp'] ?? '~'));
        $times=array_values(array_filter(array_column($events,'timestamp')));
        $status=self::status($shipment['current_status'] ?? null);
        if ($partial && $status==='delivered') { $status='unknown'; }
        return ['schema_version'=>1,'shipment'=>[
            'tracking_number'=>$reference,
            'transport_mode'=>in_array($shipment['transport_mode'] ?? null,['sea','air','rail','truck','express','multimodal'],true) ? $shipment['transport_mode'] : null,
            'origin'=>$this->text($shipment['origin'] ?? null),'destination'=>$this->text($shipment['destination'] ?? null),
            'current_status'=>$status,'last_updated'=>$times ? max($times) : self::date($shipment['last_updated'] ?? null),
        ],'events'=>$events,'meta'=>['fetched_at'=>self::date($data['meta']['fetched_at'] ?? null),'stale'=>false,'partial'=>$partial]];
    }
    public function merge(array $results,string $reference,bool $partial): array {
        $events=[];$statuses=[];$origins=[];$destinations=[];$modes=[];$times=[];
        foreach ($results as $i=>$result) {
            $data=$this->project($result,$reference);$s=$data['shipment'];
            $statuses[]=$s['current_status'];$origins[]=$s['origin'];$destinations[]=$s['destination'];$modes[]=$s['transport_mode'];$times[]=$s['last_updated'];
            $partial=$partial || $data['meta']['partial'];
            foreach ($data['events'] as $event) { $event['leg_id']='leg-'.($i+1).'-'.($event['leg_id'] ?? 'single');$events[]=$event; }
        }
        if (count($events)>2000) { throw new TrackingError('malformed_response'); }
        $unique=array_values(array_unique($statuses));$status=count($unique)===1 ? $unique[0] : 'unknown';
        if (in_array('exception',$unique,true)) { $status='exception'; }
        $one=static function($v) { $v=array_values(array_unique(array_filter($v,static fn($x)=>$x!==null)));return count($v)===1 ? $v[0] : null; };
        return $this->project(['schema_version'=>1,'shipment'=>['tracking_number'=>$reference,'current_status'=>$status,
            'origin'=>$one($origins),'destination'=>$one($destinations),'transport_mode'=>$one($modes),'last_updated'=>array_filter($times) ? max(array_filter($times)) : null],
            'events'=>$events,'meta'=>['fetched_at'=>gmdate('Y-m-d\TH:i:s\Z'),'stale'=>false,'partial'=>$partial]],$reference);
    }
}
