<?php
/** Offline controlled CLI pipeline tests: real resolver/service/projector, fake KQD transport. */
if (!defined('BEXSTAR_TRACKING_TEST')) { exit; }
require __DIR__.'/api.php';
use Bexstar\Tracking\{KqdTransport,ControlledKqdValidation,KqdValidationCommand,ShipmentMapping,TrackingError,PublicResponse,Config};
final class ControlledTransport implements KqdTransport {
    public $calls=0;public $mode='success';public $references=[];
    public function request(string $method,array $params): array {
        $this->calls++;$this->references[]=$params['tracking_number'] ?? null;
        if($this->mode==='not_found')return ['success'=>1,'data'=>[]];
        if($this->mode!=='success')throw new TrackingError($this->mode);
        return ['success'=>1,'data'=>['track_status_ename'=>'Delivered','server_hawbcode'=>'PRIVATE-INTERNAL-NUMBER','details'=>[
            ['track_occur_date'=>'2026-10-03 14:00:00','gmt_offset'=>8,'track_status_ename'=>'Delivered','track_description_en'=>'Delivered'],
            ['track_occur_date'=>'2026-10-02 14:00:00','gmt_offset'=>8,'track_status_ename'=>'In transit','track_description_en'=>'Controlled-token-secret Controlled-key-secret PRIVATE-INTERNAL-NUMBER KQD'],
            ['track_description_en'=>'Untimed event retained','track_status_ename'=>'Undocumented'],
        ]]];
    }
}
class WP_CLI {
    public static $commands=[];public static $output='';
    public static function add_command($name,$handler,$options=[]){self::$commands[$name]=$handler;}
    public static function line($message){self::$output=$message;}
    public static function error($message){throw new RuntimeException($message);}
}
define('WP_CLI',true);require dirname(__DIR__,2).'/inc/tracking/cli.php';
check(WP_CLI::$commands['bexstar tracking-test-kqd'] instanceof KqdValidationCommand,'existing command registered to pipeline handler');
$store=new TestStore();$guard=new TestGuard();$transport=new ControlledTransport();
$runner=new ControlledKqdValidation($store,$guard,$transport);
$command=new KqdValidationCommand(fn()=>$runner);
function controlled_error($callback,$code){
    try{$callback();}catch(RuntimeException $e){
        check(strpos($e->getMessage(),$code.':')===0,'CLI error code '.$code);
        check(strpos($e->getMessage(),'Controlled-token-secret')===false,'no error secret');
        check(strpos($e->getMessage(),'raw-stack')===false,'no raw stack');return;
    }
    throw new Exception('Expected CLI error '.$code);
}
putenv('BEXSTAR_KQD_LIVE_TEST_ENABLED');
controlled_error(fn()=>$command(['154554'],['approved'=>true]),'TRACKING_UNAVAILABLE');
putenv('BEXSTAR_KQD_LIVE_TEST_ENABLED=1');
controlled_error(fn()=>$command(['154554'],[]),'TRACKING_UNAVAILABLE');
controlled_error(fn()=>$command(['154554'],['approved'=>false]),'TRACKING_UNAVAILABLE');
controlled_error(fn()=>$command(['154554','other'],['approved'=>true]),'INVALID_REQUEST');
controlled_error(fn()=>$command(['154554'],['approved'=>true,'provider'=>'kqd']),'INVALID_REQUEST');
controlled_error(fn()=>$command(['154554'],['approved'=>true]),'PROVIDER_UNAVAILABLE');
check($transport->calls===0,'gates prevent requests');
putenv('KQD_API_ENDPOINT=https://provider.invalid/private');putenv('KQD_APP_TOKEN=Controlled-token-secret');putenv('KQD_APP_KEY=Controlled-key-secret');
controlled_error(fn()=>$command(['154554'],['approved'=>true]),'PROVIDER_NOT_MAPPED');
check($transport->calls===0 && $store->mappings===[],'unmapped reference never silently inserted or queried');
$refs=[['reference_id'=>'mapped-ref','leg_id'=>'mapped-leg','provider_code'=>'kqd','role'=>'primary']];
$store->insert(new ShipmentMapping('154554',$refs));
$command(['154554'],['approved'=>true]);$first=json_decode(WP_CLI::$output,true);
check($transport->calls===1 && $store->writes===1 && $guard->locks===1 && $guard->released===1,'service cache write and lock path exercised');
check($first['public_response']['reference']==='154554','format agnostic lookup');
check($first['internal_diagnostics']['source']==='provider_request' && $first['internal_diagnostics']['provider_request_count']===1,'live request distinct');
check($first['internal_diagnostics']['live_authentication_test']==='provider_response_received','provider response evidence');
$command(['154554'],['approved'=>true]);$cached=json_decode(WP_CLI::$output,true);
check($transport->calls===1 && $store->writes===1,'cache path avoids adapter');
check($cached['internal_diagnostics']['source']==='cache_hit' && $cached['internal_diagnostics']['provider_request_count']===0,'cache diagnostic');
check($cached['internal_diagnostics']['live_authentication_test']==='not_performed_cache_hit','no cached auth claim');
$command(['154554'],['approved'=>true,'bypass-cache'=>true]);$fresh=json_decode(WP_CLI::$output,true);
check($transport->calls===2 && $store->writes===2 && $fresh['internal_diagnostics']['cache_bypassed'],'per-call bypass still writes cache');
$command(['154554'],['approved'=>true]);
check($transport->calls===2 && json_decode(WP_CLI::$output,true)['internal_diagnostics']['source']==='cache_hit','bypass does not persist');
check(!Config::apiEnabled() && !Config::liveEnabled() && Config::enabledProviders()===[],'global gates not changed');
$events=$fresh['public_response']['events'];$latest=$fresh['internal_diagnostics']['latest_event'];
check(count($events)===3 && end($events)['timestamp']===null,'untimed final event preserved');
check($latest['timestamp']==='2026-10-03T06:00:00Z' && $latest['normalized_status']==='delivered','newest timed event selected');
check($fresh['public_response']['shipment']['last_updated']===$latest['timestamp'],'shared latest calculation');
check(!isset($fresh['public_response']['shipment']['latest_event']),'stable public schema unchanged');
$latest=PublicResponse::latestEvent([
 ['timestamp'=>'2026-10-03T08:00:00+02:00','description'=>'newest'],
 ['timestamp'=>'2026-10-01T08:00:00Z','description'=>'older'],
 ['timestamp'=>'not-a-date'],['timestamp'=>null],['timestamp'=>'2026-02-30T08:00:00Z'],
]);
check($latest['description']==='newest' && $latest['timestamp']==='2026-10-03T06:00:00Z','unsorted timestamps compared in UTC');
check(PublicResponse::latestEvent([['timestamp'=>null],['timestamp'=>'bad']])===null,'no valid timestamp yields null');
foreach(['Controlled-token-secret','Controlled-key-secret','PRIVATE-INTERNAL-NUMBER','provider.invalid'] as $secret){check(strpos(WP_CLI::$output,$secret)===false,'safe CLI diagnostics');}
$public=json_encode($fresh['public_response']);check(strpos($public,'"provider"')===false && strpos($public,'provider_reference')===false && strpos($public,'KQD')===false,'public provider metadata hidden');
$wrong=$refs;$wrong[0]['provider_code']='zx';$store->insert(new ShipmentMapping('wrong-provider',$wrong));
$before=$transport->calls;controlled_error(fn()=>$command(['wrong-provider'],['approved'=>true]),'PROVIDER_NOT_MAPPED');check($transport->calls===$before,'wrong provider blocked');
foreach(['BEXMX0920US-1','order/Abc%20+?','111111123'] as $reference){
 $store->insert(new ShipmentMapping($reference,$refs));$command([$reference],['approved'=>true]);
 check(json_decode(WP_CLI::$output,true)['public_response']['reference']===$reference,'legacy/arbitrary reference preserved');
}
foreach(['not_found'=>'TRACKING_NOT_FOUND','auth_error'=>'PROVIDER_AUTH_ERROR','timeout'=>'PROVIDER_TIMEOUT','provider_unavailable'=>'PROVIDER_UNAVAILABLE'] as $mode=>$code){
 $transport->mode=$mode;controlled_error(fn()=>$command(['154554'],['approved'=>true,'bypass-cache'=>true]),$code);
}
controlled_error(fn()=>(new KqdValidationCommand(function(){throw new RuntimeException('raw-stack Controlled-token-secret');}))(['154554'],['approved'=>true]),'INTERNAL_ERROR');
check($guard->locks===$guard->released,'error paths release locks');
$transport->mode='success';$command(['154554'],['approved'=>true]);
check(json_decode(WP_CLI::$output,true)['internal_diagnostics']['source']==='cache_hit','failed fresh lookup preserves prior unexpired cache');
$GLOBALS['controlled_contract_outputs']=[$first['public_response'],$cached['public_response'],$fresh['public_response']];
foreach(['KQD_API_ENDPOINT','KQD_APP_TOKEN','KQD_APP_KEY','BEXSTAR_KQD_LIVE_TEST_ENABLED'] as $key){putenv($key);}
echo "PASS: mapped CLI service path, approval, cache/source/bypass, latest valid event, privacy and normalized errors\n";
