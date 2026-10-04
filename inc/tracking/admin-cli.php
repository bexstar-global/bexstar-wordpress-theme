<?php
namespace Bexstar\Tracking;
if (!defined('ABSPATH')) { exit; }

if (defined('WP_CLI') && WP_CLI) {
    \WP_CLI::add_command('bexstar tracking-install',static function() {
        try { TrackingDatabase::install(); \WP_CLI::success('Tracking storage schema is ready. No live tracking was enabled.'); }
        catch (\Throwable $e) { \WP_CLI::error('Tracking storage could not be prepared. Check the server database configuration privately.'); }
    });
    \WP_CLI::add_command('bexstar tracking-map',static function($args) {
        // Private operator file; no credentials belong in a shipment mapping.
        try {
            if (count($args)!==1 || !is_readable($args[0]) || filesize($args[0])>65536) { throw new TrackingError('configuration'); }
            $input=json_decode(file_get_contents($args[0]),true);
            if (!is_array($input) || !isset($input['reference'],$input['references']) || !is_array($input['references'])) { throw new TrackingError('configuration'); }
            $refs=[];$catalog=ProviderCatalog::definitions();
            foreach ($input['references'] as $row) {
                if (!is_array($row) || !isset($catalog[$row['provider_code'] ?? ''])) { throw new TrackingError('configuration'); }
                if (array_diff(array_keys($row),['reference_id','leg_id','provider_code','provider_tracking_number','role'])) { throw new TrackingError('configuration'); }
                $refs[]=$row;
            }
            $mapping=new ShipmentMapping(TrackingReference::parse($input['reference']),$refs);
            global $wpdb;
            if (!(new WordPressTrackingStore($wpdb))->insert($mapping)) { \WP_CLI::error('Reference already exists. Existing mapping was preserved.'); return; }
            \WP_CLI::success('Tracking mapping inserted. No provider lookup was made.');
        } catch (\Throwable $e) { \WP_CLI::error('Mapping could not be inserted. Check the private input file and storage setup.'); }
    });
}
