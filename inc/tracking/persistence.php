<?php
namespace Bexstar\Tracking;
if (!defined('ABSPATH')) { exit; }

interface TrackingStore extends MappingRepository {
    public function cached(string $reference, int $now): ?array;
    public function remember(string $reference, array $response, int $ttl, int $now): void;
}
interface LookupGuard {
    public function consume(string $client, string $reference, int $now): void;
    public function acquire(string $reference, int $now): string;
    public function release(string $reference, string $token): void;
}

/** No automatic schema changes: run the explicit operator CLI installation command. */
final class TrackingDatabase {
    public static function install(): void {
        global $wpdb;
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        $collation=$wpdb->get_charset_collate();
        $shipments=$wpdb->prefix.'bexstar_tracking';
        $runtime=$wpdb->prefix.'bexstar_tracking_runtime';
        dbDelta("CREATE TABLE $shipments (
            tracking_reference varbinary(128) NOT NULL,
            mapping_json longtext NOT NULL,
            last_successful_lookup datetime DEFAULT NULL,
            last_status varchar(32) DEFAULT NULL,
            last_event_time varchar(40) DEFAULT NULL,
            cached_normalized_response longtext DEFAULT NULL,
            cache_expires bigint unsigned NOT NULL DEFAULT 0,
            created_at datetime NOT NULL,
            updated_at datetime NOT NULL,
            PRIMARY KEY  (tracking_reference)
        ) ENGINE=InnoDB $collation;");
        dbDelta("CREATE TABLE $runtime (
            bucket char(64) NOT NULL,
            hits bigint unsigned NOT NULL DEFAULT 0,
            owner char(32) NOT NULL DEFAULT '',
            expires bigint unsigned NOT NULL,
            PRIMARY KEY  (bucket),
            KEY expires (expires)
        ) ENGINE=InnoDB $collation;");
        // Probe expected columns before recording installation, including silent DB errors.
        $old=$wpdb->suppress_errors(true);
        try {
            $a=$wpdb->query("SELECT tracking_reference,mapping_json,cache_expires FROM $shipments LIMIT 0");
            $b=$wpdb->query("SELECT bucket,hits,owner,expires FROM $runtime LIMIT 0");
            if ($a===false || $b===false) { throw new TrackingError('configuration'); }
        } finally { $wpdb->suppress_errors($old); }
        update_option('bexstar_tracking_schema',1,false);
    }
}

final class WordPressTrackingStore implements TrackingStore {
    private $db;
    private $table;
    public function __construct($db) { $this->db=$db; $this->table=$db->prefix.'bexstar_tracking'; }
    private function query(callable $operation) {
        $old=$this->db->suppress_errors(true);
        try {
            $result=$operation();
            if ($this->db->last_error || $result===false) { throw new TrackingError('unavailable'); }
            return $result;
        } finally { $this->db->suppress_errors($old); }
    }
    public function find(string $reference): ?ShipmentMapping {
        $reference=TrackingReference::parse($reference);
        $json=$this->query(fn()=>$this->db->get_var($this->db->prepare("SELECT mapping_json FROM {$this->table} WHERE tracking_reference=%s",$reference)));
        if ($json===null) { return null; }
        $refs=json_decode($json,true);
        if (!is_array($refs)) { throw new TrackingError('configuration'); }
        return new ShipmentMapping($reference,$refs);
    }
    public function insert(ShipmentMapping $mapping): bool {
        // Parent plus all legs in one row: one atomic UNIQUE insert, no partial child rows.
        $old=$this->db->suppress_errors(true);
        try {
            $now=gmdate('Y-m-d H:i:s');
            $ok=$this->db->insert($this->table,['tracking_reference'=>$mapping->number(),
                'mapping_json'=>json_encode($mapping->references(),JSON_THROW_ON_ERROR),
                'created_at'=>$now,'updated_at'=>$now],['%s','%s','%s','%s']);
            if ($ok!==false) { return true; }
            // Only report collision when the exact immutable reference really exists.
            if ($this->find($mapping->number())!==null) { return false; }
            throw new TrackingError('unavailable');
        } finally { $this->db->suppress_errors($old); }
    }
    public function cached(string $reference,int $now): ?array {
        $row=$this->query(fn()=>$this->db->get_row($this->db->prepare("SELECT cached_normalized_response,cache_expires FROM {$this->table} WHERE tracking_reference=%s",$reference),ARRAY_A));
        if (!$row || (int)$row['cache_expires'] <= $now) { return null; }
        $data=json_decode($row['cached_normalized_response'] ?? '',true);
        return is_array($data) ? $data : null;
    }
    public function remember(string $reference,array $response,int $ttl,int $now): void {
        $this->query(fn()=>$this->db->update($this->table,[
            'cached_normalized_response'=>json_encode($response,JSON_THROW_ON_ERROR),
            'cache_expires'=>$now+$ttl, 'last_successful_lookup'=>gmdate('Y-m-d H:i:s',$now),
            'last_status'=>$response['shipment']['current_status'],
            'last_event_time'=>$response['shipment']['last_updated'], 'updated_at'=>gmdate('Y-m-d H:i:s',$now),
        ],['tracking_reference'=>$reference],['%s','%d','%s','%s','%s','%s'],['%s']));
    }
}

/** Atomic shared-DB limits: works across PHP workers without depending on transients. */
final class WordPressLookupGuard implements LookupGuard {
    private $db;
    private $table;
    public function __construct($db) { $this->db=$db; $this->table=$db->prefix.'bexstar_tracking_runtime'; }
    private function run(callable $fn) {
        $old=$this->db->suppress_errors(true);
        try { $value=$fn(); if ($value===false || $this->db->last_error) { throw new TrackingError('unavailable'); } return $value; }
        finally { $this->db->suppress_errors($old); }
    }
    public function consume(string $client,string $reference,int $now): void {
        // Salted hashes only; never store raw IPs, tracking references or credentials here.
        foreach (['client:'.$client=>30,'reference:'.$reference=>20,'global'=>300] as $key=>$limit) {
            $bucket=hash_hmac('sha256','rate:'.$key,wp_salt('auth'));
            $this->run(fn()=>$this->db->query($this->db->prepare("INSERT INTO {$this->table} (bucket,hits,expires) VALUES (%s,1,%d)
                ON DUPLICATE KEY UPDATE hits=IF(expires<=%d,1,hits+1),expires=IF(expires<=%d,%d,expires)",$bucket,$now+60,$now,$now,$now+60)));
            $hits=$this->run(fn()=>$this->db->get_var($this->db->prepare("SELECT hits FROM {$this->table} WHERE bucket=%s",$bucket)));
            if ((int)$hits>$limit) { throw new TrackingError('rate_limited'); }
        }
        $this->run(fn()=>$this->db->query($this->db->prepare("DELETE FROM {$this->table} WHERE expires<%d LIMIT 100",$now)));
    }
    public function acquire(string $reference,int $now): string {
        $bucket=hash_hmac('sha256','lock:'.$reference,wp_salt('auth')); $token=bin2hex(random_bytes(16));
        $this->run(fn()=>$this->db->query($this->db->prepare("INSERT INTO {$this->table} (bucket,hits,owner,expires) VALUES (%s,0,%s,%d)
            ON DUPLICATE KEY UPDATE owner=IF(expires<=%d,VALUES(owner),owner),expires=IF(expires<=%d,VALUES(expires),expires)",$bucket,$token,$now+45,$now,$now)));
        $owner=$this->run(fn()=>$this->db->get_var($this->db->prepare("SELECT owner FROM {$this->table} WHERE bucket=%s",$bucket)));
        if ($owner!==$token) { throw new TrackingError('rate_limited'); }
        return $token;
    }
    public function release(string $reference,string $token): void {
        $bucket=hash_hmac('sha256','lock:'.$reference,wp_salt('auth'));
        $this->run(fn()=>$this->db->query($this->db->prepare("DELETE FROM {$this->table} WHERE bucket=%s AND owner=%s",$bucket,$token)));
    }
}
