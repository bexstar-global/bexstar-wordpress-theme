<?php
/** Real WordPress + MariaDB integration. ONLY an isolated local database is permitted. */
if (PHP_SAPI!=='cli' || !getenv('BEXSTAR_TEST_WP_PATH')) { exit; }
require rtrim(getenv('BEXSTAR_TEST_WP_PATH'),'/').'/wp-load.php';
if (wp_get_environment_type()!=='local' || DB_NAME!=='bex_tracking_test' || !WP_HTTP_BLOCK_EXTERNAL) {
    throw new RuntimeException('Use the isolated bex_tracking_test local database with external HTTP blocked.');
}
require dirname(__DIR__,2).'/inc/tracking/bootstrap.php';
use Bexstar\Tracking\{TrackingDatabase,WordPressTrackingStore,WordPressLookupGuard,ShipmentMapping,TrackingError};
function verify_db($value,$message){if(!$value)throw new RuntimeException($message);}
function db_reject($callback,$reason){try{$callback();}catch(TrackingError $e){verify_db($e->reason()===$reason,'wrong DB error');return;}throw new RuntimeException('Expected '.$reason);}
TrackingDatabase::install();
global $wpdb;
$shipments=$wpdb->prefix.'bexstar_tracking';$runtime=$wpdb->prefix.'bexstar_tracking_runtime';
$wpdb->query("TRUNCATE TABLE $shipments");$wpdb->query("TRUNCATE TABLE $runtime");
$store=new WordPressTrackingStore($wpdb);$guard=new WordPressLookupGuard($wpdb);
$refs=[['reference_id'=>'ref-a','leg_id'=>'leg-a','provider_code'=>'kqd','role'=>'primary']];
foreach(['154554','111111123','BEXSTAR1002037','BEXSTAR0924US-10','order/Abc%20+?#','订单-测试','Case','case',"quote' OR 1=1 --"] as $number){
    verify_db($store->insert(new ShipmentMapping($number,$refs)),'atomic insert');
    verify_db($store->find($number)->number()===$number,'exact reference read');
    verify_db(!$store->insert(new ShipmentMapping($number,$refs)),'unique collision');
}
verify_db($store->find('CASE')===null,'case sensitive reference');
TrackingDatabase::install();verify_db($store->find('154554')!==null,'repeat schema install preserves mapping');
$fixture=json_decode(file_get_contents(__DIR__.'/fixtures/normalized.json'),true);$fixture['shipment']['tracking_number']='154554';
$store->remember('154554',$fixture,30,1000);
verify_db($store->cached('154554',1029)===$fixture && $store->cached('154554',1030)===null,'cache expiry');
$row=$wpdb->get_row($wpdb->prepare("SELECT last_status,last_successful_lookup FROM $shipments WHERE tracking_reference=%s",'154554'),ARRAY_A);
verify_db($row['last_status']==='unknown' && $row['last_successful_lookup']!==null,'lookup metadata persisted');
$guard->consume('test-client','test-reference',2000);
for($i=0;$i<19;$i++)$guard->consume('test-client','test-reference',2000);
db_reject(fn()=>$guard->consume('test-client','test-reference',2000),'rate_limited');
$guard->consume('test-client','test-reference',2060); // Reset exactly at expiry.
$token=$guard->acquire('154554',3000);db_reject(fn()=>$guard->acquire('154554',3001),'rate_limited');
$guard->release('154554','wrong-token');db_reject(fn()=>$guard->acquire('154554',3002),'rate_limited');
$guard->release('154554',$token);$replacement=$guard->acquire('154554',3003);$guard->release('154554',$replacement);
$expired=$guard->acquire('154554',4000);$new=$guard->acquire('154554',4045);
$guard->release('154554',$expired);db_reject(fn()=>$guard->acquire('154554',4046),'rate_limited');$guard->release('154554',$new);
$wpdb->query("TRUNCATE TABLE $runtime");
for($i=0;$i<30;$i++)$guard->consume('client-limit','reference-'.$i,5000);
db_reject(fn()=>$guard->consume('client-limit','next-reference',5000),'rate_limited');
$wpdb->query("TRUNCATE TABLE $runtime");
for($i=0;$i<300;$i++)$guard->consume('client-'.$i,'reference-'.$i,6000);
db_reject(fn()=>$guard->consume('client-extra','reference-extra',6000),'rate_limited');
$wpdb->query("TRUNCATE TABLE $runtime");
// All WordPress HTTP is intercepted, in addition to the global external HTTP block.
$calls=0;
add_filter('pre_http_request',function($pre,$args,$url)use(&$calls){
    $calls++;parse_str($args['body'],$body);
    verify_db($body['serviceMethod']==='gettrack','direct gettrack');
    return ['response'=>['code'=>200,'message'=>'OK'],'headers'=>[],'body'=>json_encode([
        'success'=>1,'data'=>['origin_country'=>'CN','destination_country'=>'DE','track_status_ename'=>'Delivered',
        'details'=>[['track_occur_date'=>'2026-10-02 12:00:00','gmt_offset'=>8,'track_status_ename'=>'Delivered','track_description_en'=>'Shipment delivered']]],
    ])];
},10,3);
putenv('BEXSTAR_TRACKING_API_ENABLED=1');putenv('BEXSTAR_TRACKING_LIVE_ENABLED=1');putenv('BEXSTAR_TRACKING_ENABLED_PROVIDERS=kqd');
putenv('KQD_API_ENDPOINT=https://provider.invalid/test');putenv('KQD_APP_TOKEN=synthetic-token');putenv('KQD_APP_KEY=synthetic-key');
$server=rest_get_server();
foreach(['154554','order/Abc%20+?#','订单-测试'] as $reference){
    $request=new WP_REST_Request('POST','/bexstar-tracking/v1/lookup');$request->set_header('Content-Type','application/json');$request->set_body(json_encode(['reference'=>$reference]));
    $response=$server->dispatch($request);
    verify_db($response->get_status()===200,'real REST POST: '.json_encode($response->get_data()));
    verify_db($response->get_data()['shipment']['current_status']==='delivered','real adapter normalization');
    $get=new WP_REST_Request('GET','/bexstar-tracking/v1/shipments/'.rawurlencode($reference));
    $getResponse=$server->dispatch($get);
    verify_db($getResponse->get_status()===200 && $getResponse->get_data()['reference']===$reference,'real REST encoded GET');
}
verify_db($calls===3,'GET reuses POST cache and no preliminary lookup');
putenv('BEXSTAR_TRACKING_API_ENABLED=0');
$request=new WP_REST_Request('POST','/bexstar-tracking/v1/lookup');$request->set_header('Content-Type','application/json');$request->set_body('{"reference":"154554"}');
verify_db($server->dispatch($request)->get_status()===503 && $calls===3,'real API closed gate');
echo "PASS: real WordPress REST, MariaDB schema/uniqueness/case/cache/rate limits/locks, mocked KQD HTTP\n";

// Controlled CLI runner must reuse the same persisted mapping/cache while API is closed.
$runner=\Bexstar\Tracking\ControlledKqdValidation::production();
$cached=$runner->run('154554');
verify_db($cached['internal_diagnostics']['source']==='cache_hit' && $calls===3,'controlled runner reads API cache');
verify_db($cached['internal_diagnostics']['live_authentication_test']==='not_performed_cache_hit','no cached authentication claim');
$fresh=$runner->run('154554',true);
verify_db($fresh['internal_diagnostics']['source']==='provider_request' && $calls===4,'controlled runner bypass with mocked HTTP');
verify_db($fresh['internal_diagnostics']['latest_event']['timestamp']===$fresh['public_response']['shipment']['last_updated'],'latest event from service result');
verify_db($runner->run('154554')['internal_diagnostics']['source']==='cache_hit' && $calls===4,'normal caching resumes');
verify_db(!\Bexstar\Tracking\Config::apiEnabled(),'public API remains closed');
echo "PASS: controlled service runner with actual persistent mapping/cache/locks and mocked provider HTTP\n";
