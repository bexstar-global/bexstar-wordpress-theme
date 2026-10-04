<?php
/** Offline tests only. Run: php -r "define('BEXSTAR_TRACKING_TEST', true); require 'tests/tracking/core.php';" */
if ( ! defined( 'BEXSTAR_TRACKING_TEST' ) ) { exit; }
define( 'ABSPATH', __DIR__ );
function add_action( $hook, $callback ) {}
function add_shortcode( $name, $callback ) { $GLOBALS['shortcodes'][$name] = $callback; }
function rest_url($path) { return 'https://example.test/wp-json/' . $path; }
function esc_url( $url ) { return $url; }
function home_url( $path ) { return 'https://example.test' . $path; }
require dirname( __DIR__, 2 ) . '/inc/tracking/bootstrap.php';
use Bexstar\Tracking\{TrackingNumber,TrackingError,ShipmentMapping,MappingRepository,InternalMappingResolver,Config,TrackingNumberAllocator,ProviderAdapter,AdapterRegistry};
function check( $value, $label ) { if ( ! $value ) { throw new Exception( $label ); } }
function rejects( $callback, $reason ) {
    try { $callback(); } catch ( TrackingError $e ) { check( $e->reason() === $reason, 'wrong error' ); return; }
    throw new Exception( 'expected ' . $reason );
}
final class MemoryRepository implements MappingRepository {
    private $items = array();
    public function find( string $number ): ?ShipmentMapping { return $this->items[$number] ?? null; }
    public function insert( ShipmentMapping $mapping ): bool { if ( isset($this->items[$mapping->number()]) ) { return false; } $this->items[$mapping->number()] = $mapping; return true; }
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
$repo->insert( new ShipmentMapping( 'BEXSTAR1002037', $refs ) );
$resolver = new InternalMappingResolver( $repo );
check( count( $resolver->resolve('BEXSTAR1002037')['legs'] ) === 2, 'split shipment' );
check( $resolver->resolve('BEXSTAR1002037')['legs']['leg-a']['fallbacks'] === array(), 'fallback disabled' );
$enabled = new InternalMappingResolver( $repo, array( 'fallback_enabled'=>true,'enabled_adapters'=>array('seventeentrack') ) );
check( $enabled->resolve('BEXSTAR1002037')['legs']['leg-a']['fallbacks'][0]['provider_code'] === 'seventeentrack', 'mapped aggregator' );
rejects( function() use($resolver) { $resolver->resolve('BEXSTAR1002999'); }, 'not_mapped' );
rejects( function() { TrackingNumber::parse(array()); }, 'invalid_number' );
rejects( function() { TrackingNumber::parse('ABC<script>'); }, 'invalid_number' );
check( TrackingNumber::parse(' bexstar1002037 ') === 'BEXSTAR1002037', 'canonical uppercase and leading zero' );
rejects( function() use($refs) { new ShipmentMapping('BEXSTAR1002037',array($refs[0],$refs[0])); }, 'configuration' );
rejects( function() use($refs) { new ShipmentMapping('BEXSTAR1002037',array($refs[2])); }, 'configuration' );
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

foreach ( array('BEXSTAR20261002037','BEXSTAR100237','BEXSTAR10020037','BEXSTAR0230037','BEXSTAR0431037','BEXSTAR0001037','BEXSTAR1301037') as $bad ) {
    rejects( function() use($bad) { TrackingNumber::parse($bad); }, 'invalid_number' );
}
check( TrackingNumber::parse('BEXSTAR0229000') === 'BEXSTAR0229000', 'leap day valid without a year' );
check( !$repo->insert(new ShipmentMapping('BEXSTAR1002037',array($refs[0]))), 'duplicate insert rejected' );
check( count($repo->find('BEXSTAR1002037')->references()) === 3, 'duplicate cannot replace existing references' );
$full = new MemoryRepository();
for($i=0;$i<1000;$i++) { if($i!==37) { $full->insert(new ShipmentMapping('BEXSTAR1002'.sprintf('%03d',$i),array($refs[0]))); } }
$allocator = new TrackingNumberAllocator($full);
check( $allocator->create(new DateTimeImmutable('2026-10-01T16:00:00Z'),array($refs[0]))->number() === 'BEXSTAR1002037', 'collision scan and Shanghai date' );
rejects(function() use($allocator,$refs) { $allocator->create(new DateTimeImmutable('2027-10-02T12:00:00+08:00'),array($refs[0])); }, 'number_space_exhausted');
final class RacingRepository implements MappingRepository {
    public $inserts=0;
    public function find(string $number): ?ShipmentMapping { return null; }
    public function insert(ShipmentMapping $mapping): bool { return ++$this->inserts > 1; }
}
$race = new RacingRepository();
(new TrackingNumberAllocator($race))->create(new DateTimeImmutable('2026-10-02'),array($refs[0]));
check($race->inserts===2,'atomic insert collision retried');
echo "PASS: canonical format, date validation, duplicate protection, random allocation, race retry and cross-year exhaustion\n";
check(substr_count($html,'<form ')===1,'single BEXSTAR primary form');
check(strpos($html,'Tracking / Reference Number')!==false && strpos($html,'BEXSTAR1002037')!==false && strpos($html,'TRACK SHIPMENT')!==false,'approved form copy');
check(substr_count($html,'data-tracking-error=')===4,'four safe error templates');
check(strpos($html,'bex-tracking-results-title" hidden')!==false,'results hidden until real data');
check(Config::partnerUrl()==='https://www.17track.net/en/carriers/bexstar-express', 'verified partner URL');
check(strpos($html,'href="https://www.17track.net/en/carriers/bexstar-express"')!==false, 'partner link rendered');
check(strpos($html,'provider_tracking_number')===false && strpos($html,'provider_code')===false,'no private mapping fields in view');
echo "PASS: BEXSTAR-first page structure, configured partner URL and empty result states\n";
