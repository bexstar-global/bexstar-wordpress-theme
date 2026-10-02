<?php
namespace Bexstar\Tracking;
if (!defined('ABSPATH')) { exit; }

final class KqdNormalizer {
    // Exact semantic labels only. Numeric codes are deliberately NOT interpreted.
    private const LABELS = [
        'shipment created'=>'shipment_created', 'cargo received'=>'cargo_received',
        'warehouse processing'=>'warehouse_processing', 'departed origin'=>'departed_origin',
        'in transit'=>'in_transit', 'arrived destination'=>'arrived_destination',
        'customs clearance'=>'customs_clearance', 'customs released'=>'customs_released',
        'out for delivery'=>'out_for_delivery', 'delivered'=>'delivered', 'exception'=>'exception',
    ];
    private $privateEvents = [];
    private $redactions;
    public function __construct(array $redactions = []) { $this->redactions = $redactions; }
    /** Request-local diagnostics only; never returned, persisted, logged or serialized. */
    public function __debugInfo(): array { return []; }
    public function __serialize(): array { return []; }
    public static function status(array $row): string {
        $label = $row['track_status_ename'] ?? '';
        return is_string($label) ? (self::LABELS[strtolower(trim($label))] ?? 'unknown') : 'unknown';
    }
    private function text($value): ?string {
        if (!is_string($value) || trim($value) === '') { return null; }
        $value = strip_tags($value);
        $value = str_ireplace(array_merge(['KQD','开渠达'], array_filter($this->redactions)), '[redacted]', $value);
        $value = preg_replace('/https?:\/\/\S+|[\x00-\x1F\x7F]/u', '', $value);
        // Unicode-safe bounded text without assuming mbstring is installed.
        preg_match('/^.{0,500}/us', trim($value), $match);
        return ($match[0] ?? '') ?: null;
    }
    public static function timestamp(array $row): ?string {
        $date = $row['track_occur_date'] ?? null;
        if (!is_string($date)) { return null; }
        $date = str_replace('T',' ',trim($date));
        if (!preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}(Z|[+-]\d{2}:\d{2})?$/', $date, $m)) { return null; }
        $zone = $m[1] ?? null;
        if (!$zone) {
            $offset = $row['gmt_offset'] ?? null;
            if (is_int($offset) || is_float($offset) || (is_string($offset) && preg_match('/^[+-]?\d{1,2}(?:\.\d{1,2})?$/',$offset))) {
                $minutes = (float)$offset * 60;
                if (abs($minutes) > 840 || floor($minutes) != $minutes) { return null; }
                $zone = sprintf('%s%02d:%02d', $minutes < 0 ? '-' : '+', intdiv((int)abs($minutes),60), (int)abs($minutes)%60);
            } elseif (is_string($offset) && preg_match('/^(?:GMT|UTC)?([+-]\d{2}:\d{2})$/',$offset,$z)) { $zone=$z[1]; }
            else { return null; }
            $date .= $zone;
        }
        if ($zone !== 'Z' && (!preg_match('/^[+-](\d{2}):(\d{2})$/',$zone,$z) || (int)$z[1]>14 || (int)$z[2]>59 || ((int)$z[1]===14 && (int)$z[2]!==0))) { return null; }
        $d = \DateTimeImmutable::createFromFormat('!Y-m-d H:i:sP', str_replace('Z','+00:00',$date));
        $errors = \DateTimeImmutable::getLastErrors();
        if (!$d || ($errors && ($errors['warning_count'] || $errors['error_count']))) { return null; }
        return $d->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d\TH:i:s\Z');
    }
    public function normalize(string $number, array $sources, bool $partial): array {
        $events=[]; $statuses=[]; $origins=[]; $destinations=[];
        foreach ($sources as $i=>$source) {
            $statuses[]=self::status($source);
            $origins[]=$this->text($source['origin_country'] ?? null);
            $destinations[]=$this->text($source['destination_country_name'] ?? $source['destination_country'] ?? null);
            foreach ($source['details'] ?? [] as $row) {
                if (!is_array($row)) { $partial=true; continue; }
                $this->privateEvents[]=$row; // Unknown descriptions retained only in request-local private memory.
                $status=self::status($row);
                $event=['event_id'=>'', 'leg_id'=>count($sources)>1 ? 'piece-'.($i+1) : null,
                    'timestamp'=>self::timestamp($row), 'location'=>$this->text($row['track_location'] ?? null),
                    'status'=>str_replace('_',' ',$status),
                    'description'=>$this->text($row['track_description_en'] ?? null) ?? $this->text($row['track_description'] ?? null),
                    'normalized_status'=>$status];
                // Dedup within each piece, retaining otherwise identical events on distinct pieces.
                $id=hash('sha256',json_encode([$i,$row['track_occur_date'] ?? null,$event]));
                $event['event_id']=$id; $events[$id]=$event;
            }
        }
        usort($events, static function($a,$b) { return strcmp($a['timestamp'] ?? '~',$b['timestamp'] ?? '~'); });
        $times=array_values(array_filter(array_column($events,'timestamp')));
        $unique=array_values(array_unique($statuses));
        $current=count($unique)===1 ? $unique[0] : 'unknown';
        if (in_array('exception',$unique,true)) { $current='exception'; }
        if ($partial && $current==='delivered') { $current='unknown'; }
        $one=static function($values) { $values=array_values(array_unique(array_filter($values))); return count($values)===1 ? $values[0] : null; };
        return ['schema_version'=>1, 'shipment'=>['tracking_number'=>$number,'transport_mode'=>null,
            'origin'=>$one($origins), 'destination'=>$one($destinations), 'current_status'=>$current,
            'last_updated'=>$times ? max($times) : null], 'events'=>array_values($events),
            'meta'=>['fetched_at'=>gmdate('Y-m-d\TH:i:s\Z'),'stale'=>false,'partial'=>$partial]];
    }
}
