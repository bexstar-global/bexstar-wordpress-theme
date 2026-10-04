<?php
namespace Bexstar\Tracking;
if (!defined('ABSPATH')) { exit; }

final class TrackingController {
    private $service; private $guard; private $enabled;
    public function __construct(TrackingService $service,LookupGuard $guard,bool $enabled) { $this->service=$service;$this->guard=$guard;$this->enabled=$enabled; }
    public static function production(): self {
        global $wpdb;
        $store=new WordPressTrackingStore($wpdb);$guard=new WordPressLookupGuard($wpdb);
        $resolver=new InternalMappingResolver($store,['enabled_adapters'=>Config::enabledProviders(),'fallback_enabled'=>Config::value('BEXSTAR_TRACKING_FALLBACK_ENABLED')==='1']);
        return new self(new TrackingService($resolver,$store,ProviderCatalog::registry(),$guard,Config::liveEnabled(),Config::enabledProviders(),Config::cacheTtl()),$guard,Config::apiEnabled());
    }
    public static function register(): void {
        // Anonymous read-only API by design. A WordPress nonce is not anonymous authorization.
        // Limits/gates are enforced in handle(). No mapping/admin operations are exposed.
        foreach (['/shipments/(?P<reference>[^/]+)'=>'GET','/lookup'=>'POST'] as $route=>$method) {
            register_rest_route('bexstar-tracking/v1',$route,[
                'methods'=>$method,'permission_callback'=>'__return_true',
                'callback'=>static fn($request)=>self::production()->handle($request),
            ]);
        }
    }
    public function handle($request) {
        $headers=['Cache-Control'=>'no-store, private','X-Content-Type-Options'=>'nosniff'];
        try {
            if (!$this->enabled) { throw new TrackingError('unavailable'); }
            if ($request->get_method()==='POST') {
                if (strlen($request->get_body())>2048 || !preg_match('~^application/json(?:\s*;|$)~i',$request->get_header('content-type'))) { throw new TrackingError('invalid_number'); }
                $data=json_decode($request->get_body(),true);
                if (!is_array($data) || array_keys($data)!==['reference']) { throw new TrackingError('invalid_number'); }
                $reference=TrackingReference::parse($data['reference']);
            } else {
                // WordPress route capture is URL-encoded: decode exactly once. JSON POST supports slashes reliably.
                $params=$request->get_url_params();
                $reference=TrackingReference::parse(rawurldecode($params['reference'] ?? ''));
            }
            // Ignore spoofable X-Forwarded-For. Reverse proxy trust must be configured by operator.
            $client=$_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $this->guard->consume($client,$reference,time());
            $result=$this->service->lookup($reference);
            return new \WP_REST_Response(['success'=>true,'reference'=>$reference]+$result,200,$headers);
        } catch (\Throwable $e) {
            $reason=$e instanceof TrackingError ? $e->reason() : 'internal';
            $map=[
                'invalid_number'=>['INVALID_REQUEST',400,'Please enter a valid tracking reference.'],
                'not_mapped'=>['PROVIDER_NOT_MAPPED',404,"We couldn't find tracking information for this reference yet. Please check the number or contact BEXSTAR."],
                'not_found'=>['TRACKING_NOT_FOUND',404,"We couldn't find tracking information for this reference yet. Please check the number or contact BEXSTAR."],
                'timeout'=>['PROVIDER_TIMEOUT',504,'Tracking information is temporarily unavailable. Please try again later.'],
                'auth_error'=>['PROVIDER_AUTH_ERROR',502,'Tracking information is temporarily unavailable. Please contact BEXSTAR.'],
                'configuration'=>['PROVIDER_UNAVAILABLE',503,'Tracking information is temporarily unavailable. Please try again later.'],
                'malformed_response'=>['PROVIDER_UNAVAILABLE',502,'Tracking information is temporarily unavailable. Please try again later.'],
                'provider_unavailable'=>['PROVIDER_UNAVAILABLE',503,'Tracking information is temporarily unavailable. Please try again later.'],
                'rate_limited'=>['RATE_LIMITED',429,'Please wait before trying again.'],
                'unavailable'=>['TRACKING_UNAVAILABLE',503,'Tracking information is temporarily unavailable. Please try again later.'],
            ];
            [$code,$status,$message]=$map[$reason] ?? ['INTERNAL_ERROR',500,'Tracking information is temporarily unavailable. Please try again later.'];
            if ($status===429) { $headers['Retry-After']='60'; }
            // Deliberately no raw request, reference, provider response or exception logging.
            return new \WP_REST_Response(['success'=>false,'error'=>['code'=>$code,'message'=>$message]],$status,$headers);
        }
    }
}
