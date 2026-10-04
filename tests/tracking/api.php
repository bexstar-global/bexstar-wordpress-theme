<?php
/** Offline API/service contract suite. All adapters and runtime guards are in-memory. */
if (!defined('BEXSTAR_TRACKING_TEST')) { exit; }
require_once __DIR__.'/core.php';
use Bexstar\Tracking\{TrackingStore,LookupGuard,ShipmentMapping,AdapterRegistry,ProviderAdapter,TrackingController,TrackingService,InternalMappingResolver,TrackingError,PublicResponse,ProviderCatalog,RequestBudget};
final class TestStore implements TrackingStore {
    public $mappings=[];public $cache=[];public $writes=0;
    public function find(string $ref): ?ShipmentMapping { return $this->mappings[$ref] ?? null; }
    public function insert(ShipmentMapping $map): bool { if(isset($this->mappings[$map->number()]))return false;$this->mappings[$map->number()]=$map;return true; }
    public function cached(string $ref,int $now): ?array { return isset($this->cache[$ref]) && $this->cache[$ref]['expires']>$now ? $this->cache[$ref]['response'] : null; }
    public function remember(string $ref,array $data,int $ttl,int $now): void { $this->writes++;$this->cache[$ref]=['response'=>$data,'expires'=>$now+$ttl]; }
}
final class TestGuard implements LookupGuard {
    public $count=0;public $locks=0;public $released=0;public $blocked=false;
    public function consume(string $client,string $ref,int $now): void { $this->count++;if($this->blocked)throw new TrackingError('rate_limited'); }
    public function acquire(string $ref,int $now): string { $this->locks++;return 'test-lock'; }
    public function release(string $ref,string $token): void { $this->released++; }
}
final class TestProvider implements ProviderAdapter {
    public $calls=0;public $mode='success';public $inputs=[];public $provider;
    public function __construct(string $code='kqd'){$this->provider=$code;}
    public function code(): string { return $this->provider; }
    public function fetch(array $ref): array {
        $this->calls++;$this->inputs[]=$ref;
        if($this->mode==='panic')throw new RuntimeException('PRIVATE secret-stack');
        if($this->mode!=='success')throw new TrackingError($this->mode);
        return ['schema_version'=>1,'shipment'=>['tracking_number'=>$ref['bexstar_reference'],'current_status'=>'delivered','origin'=>'CN','destination'=>'DE','transport_mode'=>'rail','last_updated'=>null,'provider_tracking_number'=>'PRIVATE-ID'],
            'events'=>[
                ['timestamp'=>'2026-10-02T12:00:00Z','location'=>null,'description'=>'Delivered','normalized_status'=>'delivered','leg_id'=>'INTERNAL-LEG','provider'=>'PRIVATE'],
                ['timestamp'=>'2026-10-01T12:00:00Z','location'=>'Test terminal','description'=>'Cargo received','normalized_status'=>'cargo_received','leg_id'=>'INTERNAL-LEG'],
            ],'meta'=>['fetched_at'=>'2026-10-02T12:00:00Z','partial'=>false,'stale'=>false],'credentials'=>['token'=>'SECRET']];
    }
}
class WP_REST_Response {
    public $data,$status,$headers;
    public function __construct($data,$status=200,$headers=[]){$this->data=$data;$this->status=$status;$this->headers=$headers;}
}
class ApiRequest {
    private $method,$body,$type,$params;
    public function __construct($reference,$method='POST',$type='application/json'){$this->method=$method;$this->body=json_encode(['reference'=>$reference]);$this->type=$type;$this->params=['reference'=>rawurlencode(is_string($reference)?$reference:'')];}
    public function get_method(){return $this->method;}
    public function get_body(){return $this->body;}
    public function get_header($key){return $this->type;}
    public function get_url_params(){return $this->params;}
    public function body($value){$this->body=$value;return $this;}
}
function register_rest_route($ns,$path,$args){$GLOBALS['registered_routes'][$ns.$path]=$args;}
$store=new TestStore();$guard=new TestGuard();$provider=new TestProvider();$registry=new AdapterRegistry();$registry->register($provider);
$service=new TrackingService(new InternalMappingResolver($store),$store,$registry,$guard,true,['kqd']);
$controller=new TrackingController($service,$guard,true);
$refs=[['reference_id'=>'ref-a','leg_id'=>'leg-a','provider_code'=>'kqd','provider_tracking_number'=>'PRIVATE-ID','role'=>'primary']];
foreach(['BEXSTAR1002037','BEXSTAR0924US-10','BEXMX0920US-1','154554','111111123','order/Abc%20+?#','订单-测试'] as $reference){
    $store->insert(new ShipmentMapping($reference,$refs));
    $response=$controller->handle(new ApiRequest($reference));
    check($response->status===200 && $response->data['reference']===$reference,'format agnostic API');
    check($response->data['shipment']['current_status']==='delivered','delivered normalization');
    check($response->data['events'][0]['timestamp']==='2026-10-01T12:00:00Z','chronological ordering');
    check(strpos(json_encode($response->data),'PRIVATE')===false && strpos(json_encode($response->data),'SECRET')===false && strpos(json_encode($response->data),'INTERNAL-LEG')===false,'private fields excluded');
    $GLOBALS['api_contract_outputs'][]=$response->data;
}
check($provider->inputs[3]['bexstar_reference']==='154554','resolver passes numeric reference');
$before=$provider->calls;
$response=$controller->handle(new ApiRequest('154554','GET'));
check($response->status===200 && $provider->calls===$before,'shared GET cache');
check($controller->handle(new ApiRequest('order/Abc%20+?#','GET'))->status===200,'GET decode once');
check($response->headers['Cache-Control']==='no-store, private','response not proxy cached');
$response=$controller->handle(new ApiRequest('unmapped'));
check($response->status===404 && $response->data['error']['code']==='PROVIDER_NOT_MAPPED','unmapped error');
foreach(['not_found'=>[404,'TRACKING_NOT_FOUND'],'timeout'=>[504,'PROVIDER_TIMEOUT'],'auth_error'=>[502,'PROVIDER_AUTH_ERROR'],'provider_unavailable'=>[503,'PROVIDER_UNAVAILABLE'],'panic'=>[503,'TRACKING_UNAVAILABLE']] as $mode=>$expected){
    $store->cache=[];$provider->mode=$mode;$response=$controller->handle(new ApiRequest('154554'));
    check($response->status===$expected[0] && $response->data['error']['code']===$expected[1],'normalized error '.$mode);
    check(strpos(json_encode($response->data),'secret')===false,'raw exception omitted');
}
check($guard->locks===$guard->released,'locks released after failure');
$provider->mode='success';$guard->blocked=true;$before=$provider->calls;
$response=$controller->handle(new ApiRequest('154554'));
check($response->status===429 && $response->headers['Retry-After']==='60' && $provider->calls===$before,'rate limit prevents provider call');
$guard->blocked=false;
foreach(['',[],str_repeat('x',129),"a\x00b"] as $bad){check($controller->handle(new ApiRequest($bad))->status===400,'invalid input safety');}
check($controller->handle((new ApiRequest('154554'))->body('{bad'))->status===400,'bad JSON');
check($controller->handle((new ApiRequest('154554'))->body(json_encode(['reference'=>'154554','provider'=>'kqd'])))->status===400,'no user provider override');
check($controller->handle(new ApiRequest('154554','POST','text/plain'))->status===400,'JSON content type required');
check($controller->handle((new ApiRequest('154554'))->body(str_repeat('x',2049)))->status===400,'request bound');
$closed=new TrackingController($service,$guard,false);$before=$provider->calls;
check($closed->handle(new ApiRequest('154554'))->status===503 && $provider->calls===$before,'API gate closed');
$closedService=new TrackingService(new InternalMappingResolver($store),$store,$registry,$guard,false,['kqd']);
rejects(fn()=>$closedService->lookup('154554'),'provider_unavailable');
$store->cache=['154554'=>['expires'=>time()-1,'response'=>[]]];$before=$provider->calls;
check($controller->handle(new ApiRequest('154554'))->status===200 && $provider->calls===$before+1,'expired cache refresh');
$store->cache['154554']['response']['shipment']['tracking_number']='wrong';$before=$provider->calls;
check($controller->handle(new ApiRequest('154554'))->status===200 && $provider->calls===$before+1,'invalid cache refresh');
// Split shipments: one delivered source plus one timeout is partial, never wholly delivered.
$second=new TestProvider('second');$second->mode='timeout';$registry->register($second);
$split=[ $refs[0],['reference_id'=>'ref-b','leg_id'=>'leg-b','provider_code'=>'second','role'=>'primary'] ];
$store->insert(new ShipmentMapping('split-reference',$split));
$splitService=new TrackingService(new InternalMappingResolver($store),$store,$registry,$guard,true,['kqd','second']);
$data=$splitService->lookup('split-reference');check($data['meta']['partial'] && $data['shipment']['current_status']!=='delivered','partial split');
// Explicit mapped aggregator fallback only, no blind scan.
$provider->mode='not_found';$second->mode='success';$fallback=[$refs[0],['reference_id'=>'fallback','leg_id'=>'leg-a','provider_code'=>'second','role'=>'fallback']];
$store->insert(new ShipmentMapping('fallback-reference',$fallback));
$fallbackService=new TrackingService(new InternalMappingResolver($store,['fallback_enabled'=>true,'enabled_adapters'=>['second']]),$store,$registry,$guard,true,['kqd','second']);
check($fallbackService->lookup('fallback-reference')['shipment']['current_status']==='delivered','explicit fallback');
$projector=new PublicResponse(['sensitive-token']);$fixture=$second->fetch(['bexstar_reference'=>'154554']);$fixture['events'][0]['description']='<b>sensitive-token</b>';
check($projector->project($fixture,'154554')['events'][1]['description']==='[redacted]','plain text secret scrub');
TrackingController::register();check(count($GLOBALS['registered_routes'])===2,'two independent versioned endpoints');
check(count(ProviderCatalog::definitions())===5,'five provider entries');
rejects(fn()=>ProviderCatalog::registry()->get('zx')->fetch([]),'provider_unavailable');
rejects(fn()=>ProviderCatalog::registry()->get('dmc')->fetch([]),'provider_unavailable');
rejects(fn()=>(new RequestBudget(0))->timeout(),'timeout');
check(!bexstar_tracking_ready(),'homepage routing gate untouched');
echo "PASS: API/reference/resolver/errors/cache/partial/fallback/security/route contracts\n";
