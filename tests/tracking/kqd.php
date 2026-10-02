<?php
/** Synthetic offline fixtures. Never invokes WordPress HTTP or real provider APIs. */
if (!defined('BEXSTAR_TRACKING_TEST')) { exit; }
require_once __DIR__.'/core.php';
use Bexstar\Tracking\{KqdAdapter,KqdTransport,KqdNormalizer,TrackingError,TrackingReference,ShipmentMapping,InternalMappingResolver,WordPressKqdTransport,KqdSettings};
final class QueueKqdTransport implements KqdTransport {
    public $calls=[];
    private $queue;
    public function __construct(array $queue) { $this->queue=$queue; }
    public function request(string $method,array $params): array {
        $this->calls[]=[$method,$params];
        if (!$this->queue) { throw new Exception('Unexpected extra provider call'); }
        $result=array_shift($this->queue);
        if ($result instanceof Throwable) { throw $result; }
        return $result;
    }
}
$f=json_decode(file_get_contents(__DIR__.'/fixtures/kqd.json'),true);
function kqd_run(array $queue,string $number='BEXSTAR0101TEST-1'): array {
    $t=new QueueKqdTransport($queue);
    $r=(new KqdAdapter($t))->fetch(['bexstar_reference'=>$number]);
    $GLOBALS['kqd_contract_outputs'][]=$r;
    return [$r,$t->calls];
}
[$r,$calls]=kqd_run([$f['in_transit']]);
check(count($calls)===1 && $calls[0]===['gettrack',['tracking_number'=>'BEXSTAR0101TEST-1']],'direct BEXSTAR lookup only');
check($r['shipment']['current_status']==='in_transit' && $r['events'][0]['timestamp']==='2026-10-01T04:00:00Z','transit and timezone');
check(strpos(json_encode($r),'INTERNAL-TEST-ID')===false,'no raw identifier');
[$d]=kqd_run([$f['delivered']]);
check($d['shipment']['current_status']==='delivered' && $d['events'][0]['timestamp']==='2026-10-02T13:30:00Z','delivered');
rejects(fn()=>kqd_run([$f['no_result'],$f['no_result']]),'not_found');
foreach (['auth_failure'=>'provider_unavailable','malformed'=>'malformed_response'] as $key=>$reason) {
    $t=new QueueKqdTransport([$f[$key]]);
    rejects(fn()=>(new KqdAdapter($t))->fetch(['bexstar_reference'=>'BEXSTAR0101TEST-1']),$reason);
    check(count($t->calls)===1,'no fallback on provider errors');
}
$local=$f['in_transit']; unset($local['data']['details'][0]['track_description_en'],$local['data']['details'][0]['track_location']);
$local['data']['details'][]=$local['data']['details'][0];
[$r]=kqd_run([$local]);
check(count($r['events'])===1 && $r['events'][0]['location']===null && $r['events'][0]['description']==='货物离开测试场站','local language, missing location, dedup');
[$r,$calls]=kqd_run([$f['no_result'],$f['mapping'],$f['in_transit']]);
check(count($calls)===3 && $calls[1]===['gettrackingnumber',['reference_no'=>'BEXSTAR0101TEST-1']] && $calls[2][1]['tracking_number']==='TEST-MASTER','master fallback');
[$r,$calls]=kqd_run([$f['no_result'],$f['children'],$f['delivered'],$f['in_transit']]);
check(count($calls)===4 && count($r['events'])===2 && !$r['meta']['partial'] && $r['shipment']['current_status']!=='delivered','child merge and conservative aggregate');
[$r]=kqd_run([$f['no_result'],$f['children'],$f['delivered'],new TrackingError('timeout')]);
check($r['meta']['partial'] && $r['shipment']['current_status']!=='delivered','partial child cannot mark whole delivered');
foreach (['BEXSTAR1002037','BEXSTAR0924US-10','BEXMX0920US-1','154554','111111123','order/Abc_1'] as $number) {
    [$r]=kqd_run([$f['in_transit']],$number);
    check($r['shipment']['tracking_number']===$number,'canonical/legacy lookup');
    $repo->insert(new ShipmentMapping($number,[['reference_id'=>'test','leg_id'=>'leg','provider_code'=>'kqd','role'=>'primary']]));
}
check((new InternalMappingResolver($repo))->resolve('BEXMX0920US-1')['legs']['leg']['primary']['bexstar_reference']==='BEXMX0920US-1','resolver passes public reference without provider number');
rejects(fn()=>kqd_run([new TrackingError('timeout')]),'timeout');
rejects(fn()=>kqd_run([new TrackingError('provider_unavailable')]),'provider_unavailable');
foreach (['',"reference\x00bad",str_repeat('B',129)] as $bad) { rejects(fn()=>TrackingReference::parse($bad),'invalid_number'); }
$unknown=$f['in_transit'];$unknown['data']['track_status_ename']='Undocumented';$unknown['data']['details'][0]['track_status_ename']='Undocumented';
[$r]=kqd_run([$unknown]);check($r['shipment']['current_status']==='unknown' && count($r['events'])===1,'unknown event retained');
check(KqdNormalizer::timestamp(['track_occur_date'=>'2026-10-01 12:00:00'])===null,'no invented timezone');
check(KqdNormalizer::timestamp(['track_occur_date'=>'2026-02-30 12:00:00','gmt_offset'=>8])===null,'invalid date');
check(KqdNormalizer::timestamp(['track_occur_date'=>'2026-10-01 12:00:00','gmt_offset'=>5.5])==='2026-10-01T06:30:00Z','fractional offset');
check(KqdNormalizer::timestamp(['track_occur_date'=>'2026-10-01 12:00:00','gmt_offset'=>'+14:30'])===null,'invalid offset');
$leak=$f['in_transit'];$leak['data']['details'][0]['track_description_en']='KQD INTERNAL-TEST-ID https://example.test/private';
[$r]=kqd_run([$leak]);check(strpos(json_encode($r),'KQD')===false && strpos(json_encode($r),'INTERNAL-TEST-ID')===false,'public redaction');
// HTTP transport contracts through stubs: no real network or real credentials.
function wp_safe_remote_post($url,$args) { $GLOBALS['http_args']=$args; return $GLOBALS['http_result']; }
function is_wp_error($r) { return $r instanceof Exception; }
function wp_remote_retrieve_response_code($r) { return $r['code']; }
function wp_remote_retrieve_body($r) { return $r['body']; }
rejects(fn()=>KqdSettings::credentials(),'configuration');
putenv('KQD_API_ENDPOINT=https://provider.invalid/test');putenv('KQD_APP_TOKEN=synthetic-test-token');putenv('KQD_APP_KEY=synthetic-test-key');
$http=new WordPressKqdTransport();
foreach ([['code'=>200,'body'=>'{bad'],['code'=>200,'body'=>'null']] as $response) {
    $GLOBALS['http_result']=$response; rejects(fn()=>$http->request('gettrack',[]),'malformed_response');
}
foreach ([['code'=>401,'body'=>'private'],['code'=>500,'body'=>'private'],new Exception('private timeout')] as $response) {
    $GLOBALS['http_result']=$response; rejects(fn()=>$http->request('gettrack',[]),'provider_unavailable');
}
$GLOBALS['http_result']=['code'=>200,'body'=>json_encode($f['in_transit'])];
$http->request('gettrack',['tracking_number'=>'BEXSTAR0101TEST-1']);
parse_str($GLOBALS['http_args']['body'],$body);
check($body['serviceMethod']==='gettrack' && json_decode($body['paramsJson'],true)['tracking_number']==='BEXSTAR0101TEST-1','form encoded request');
check($GLOBALS['http_args']['redirection']===0 && $GLOBALS['http_args']['sslverify'] && $GLOBALS['http_args']['timeout']===6,'transport safety limits');
check(!bexstar_tracking_ready(),'public tracking remains disabled');
echo "PASS: KQD direct, fallback, child/partial, canonical/legacy, normalization, redaction, transport and errors (offline)\n";
// Bounds and malformed shape checks.
$many=$f['children'];$many['data']['packages']=[];
for($i=0;$i<12;$i++) { $many['data']['packages'][]=['child_tracknumber'=>'TEST-PIECE-'.$i]; }
[$bounded,$calls]=kqd_run(array_merge([$f['no_result'],$many],array_fill(0,8,$f['delivered'])));
check(count($calls)===10 && $bounded['meta']['partial'] && $bounded['shipment']['current_status']!=='delivered','bounded child requests');
$bad=$f['in_transit'];$bad['data']['details']='bad';rejects(fn()=>kqd_run([$bad]),'malformed_response');
$bad=$f['mapping'];$bad['data']['reference_no']=[];rejects(fn()=>kqd_run([$f['no_result'],$bad]),'malformed_response');
$bad=$f['in_transit'];$bad['data']['details']=[null];[$r]=kqd_run([$bad]);check($r['meta']['partial'],'malformed row is partial');
// Exercise the CLI entry point with the same stub HTTP function, never live I/O.
class WP_CLI {
    public static $command;
    public static $output;
    public static function add_command($name,$handler) { self::$command=$handler; }
    public static function error($message) { throw new RuntimeException($message); }
    public static function line($message) { self::$output=$message; }
}
define('WP_CLI',true);
require dirname(__DIR__,2).'/inc/tracking/cli.php';
function cli_rejected($args,$flags) {
    try { (WP_CLI::$command)($args,$flags); } catch (RuntimeException $e) { return; }
    throw new Exception('Expected CLI gate');
}
putenv('BEXSTAR_KQD_LIVE_TEST_ENABLED');
cli_rejected(['BEXSTAR0101TEST-1'],['approved'=>true]);
putenv('BEXSTAR_KQD_LIVE_TEST_ENABLED=1');
cli_rejected(['BEXSTAR0101TEST-1'],[]);
cli_rejected(['BEXSTAR0101TEST-1','BEXSTAR0101TEST-2'],['approved'=>true]);
putenv('KQD_APP_KEY');cli_rejected(['BEXSTAR0101TEST-1'],['approved'=>true]);
putenv('KQD_APP_KEY=synthetic-test-key');
(WP_CLI::$command)(['BEXSTAR0101TEST-1'],['approved'=>true]);
check(json_decode(WP_CLI::$output,true)['shipment']['tracking_number']==='BEXSTAR0101TEST-1','controlled CLI normalized output');
check(strpos(WP_CLI::$output,'synthetic-test')===false,'CLI secrets absent');
foreach (['KQD_API_ENDPOINT','KQD_APP_TOKEN','KQD_APP_KEY','BEXSTAR_KQD_LIVE_TEST_ENABLED'] as $key) { putenv($key); }
echo "PASS: fan-out bounds, malformed shapes and controlled CLI gates (stub HTTP only)\n";

check(TrackingReference::parse('order/Abc_1')==='order/Abc_1','lookup preserves case and punctuation');

$numeric=$f['children'];$numeric['data']['reference_no']='154554';
$numeric['data']['packages']=[['child_tracknumber'=>'111111123']];
[$r,$calls]=kqd_run([$f['no_result'],$numeric,$f['in_transit']],'154554');
check($calls[2][1]['tracking_number']==='111111123','numeric child stays a string');
