<?php
/** Offline tests only. Run: php -r "define('BEXSTAR_TRACKING_TEST', true); require 'tests/tracking/core.php';" */
if ( ! defined( 'BEXSTAR_TRACKING_TEST' ) ) { exit; }
define( 'ABSPATH', __DIR__ );
function add_shortcode( $name, $callback ) { $GLOBALS['shortcodes'][$name] = $callback; }
function esc_url( $url ) { return $url; }
function home_url( $path ) { return 'https://example.test' . $path; }
require dirname( __DIR__, 2 ) . '/inc/tracking/bootstrap.php';
use Bexstar\Tracking\{TrackingNumber,TrackingError,ShipmentMapping,MappingRepository,InternalMappingResolver,Config,ProviderAdapter,AdapterRegistry};
function check( $value, $label ) { if ( ! $value ) { throw new Exception( $label ); } }
function rejects( $callback, $reason ) {
    try { $callback(); } catch ( TrackingError $e ) { check( $e->reason() === $reason, 'wrong error' ); return; }
    throw new Exception( 'expected ' . $reason );
}
final class MemoryRepository implements MappingRepository {
    private $items = array();
    public function find( string $number ): ?ShipmentMapping { return $this->items[$number] ?? null; }
    public function save( ShipmentMapping $mapping ): void { $this->items[$mapping->number()] = $mapping; }
}
final class FixtureAdapter implements ProviderAdapter {
    public function code(): string { return 'test_fixture'; }
    public function fetch( array $reference ): array { return json_decode( file_get_contents( __DIR__ . '/fixtures/normalized.json' ), true ); }
}
$repo = new MemoryRepository();
$refs = array(
    array( 'reference_id'=>'r1','leg_id'=>'leg-a','provider_code'=>'anshida','provider_tracking_number'=>'TEST-A-001','role'=>'primary' ),
    array( 'reference_id'=>'r2','leg_id'=>'leg-b','provider_code'=>'shangyi','provider_tracking_number'=>'TEST-S-002','role'=>'primary' ),
    array( 'reference_id'=>'r3','leg_id'=>'leg-a','provider_code'=>'seventeentrack','provider_tracking_number'=>'TEST-LASTMILE-003','role'=>'fallback' ),
);
$repo->save( new ShipmentMapping( 'TEST-BEX-0001', $refs ) );
$resolver = new InternalMappingResolver( $repo );
check( count( $resolver->resolve('TEST-BEX-0001')['legs'] ) === 2, 'split shipment' );
check( $resolver->resolve('TEST-BEX-0001')['legs']['leg-a']['fallbacks'] === array(), 'fallback disabled' );
$enabled = new InternalMappingResolver( $repo, array( 'fallback_enabled'=>true,'enabled_adapters'=>array('seventeentrack') ) );
check( $enabled->resolve('TEST-BEX-0001')['legs']['leg-a']['fallbacks'][0]['provider_code'] === 'seventeentrack', 'mapped aggregator' );
rejects( function() use($resolver) { $resolver->resolve('TEST-UNMAPPED'); }, 'not_found' );
rejects( function() { TrackingNumber::parse(array()); }, 'invalid_number' );
rejects( function() { TrackingNumber::parse('ABC<script>'); }, 'invalid_number' );
check( TrackingNumber::parse(' 001a-BC ') === '001a-BC', 'leading zero and case preserved' );
rejects( function() use($refs) { new ShipmentMapping('TEST-BEX-0001',array($refs[0],$refs[0])); }, 'configuration' );
rejects( function() use($refs) { new ShipmentMapping('TEST-BEX-0001',array($refs[2])); }, 'configuration' );
$registry = new AdapterRegistry();
rejects( function() use($registry) { $registry->get('anshida'); }, 'provider_unavailable' );
$registry->register(new FixtureAdapter());
check( $registry->get('test_fixture')->fetch($refs[0])['shipment']['current_status'] === 'unknown', 'offline adapter contract' );
rejects( function() use($registry) { $registry->register(new FixtureAdapter()); }, 'configuration' );
check( (new TrackingError('malformed_response'))->publicError()['code'] === 'unavailable', 'safe error' );
check( !Config::defaults()['external_requests_enabled'] && !Config::defaults()['public_enabled'], 'closed defaults' );
check( !bexstar_tracking_ready(), 'readiness gate' );
$html = $GLOBALS['shortcodes']['bexstar_tracking']();
check( strpos($html,'Coming soon') !== false && strpos($html,'TEST-') === false && strpos($html,'disabled') !== false, 'honest foundation' );
echo "PASS: offline resolver, split mapping, fallback, registry, validation, safe errors and disabled foundation\n";
