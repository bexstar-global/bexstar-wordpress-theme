<?php
namespace Bexstar\Tracking;
if (!defined('ABSPATH')) { exit; }

interface KqdTransport {
    /** Internal response only. Transport must not log payloads or raw responses. */
    public function request(string $method, array $params): array;
}

final class KqdSettings {
    public static function value(string $key): string {
        $value = defined($key) ? constant($key) : getenv($key);
        return is_string($value) ? trim($value) : '';
    }
    public static function credentials(): array {
        $endpoint = self::value('KQD_API_ENDPOINT');
        $parts = parse_url($endpoint);
        if (!filter_var($endpoint, FILTER_VALIDATE_URL) || ($parts['scheme'] ?? '') !== 'https'
            || isset($parts['user']) || isset($parts['pass']) || isset($parts['query']) || isset($parts['fragment'])
            || !self::value('KQD_APP_TOKEN') || !self::value('KQD_APP_KEY')) {
            throw new TrackingError('configuration');
        }
        return ['endpoint'=>$endpoint, 'appToken'=>self::value('KQD_APP_TOKEN'), 'appKey'=>self::value('KQD_APP_KEY')];
    }
}

final class WordPressKqdTransport implements KqdTransport {
    private $budget;
    public function __construct(?RequestBudget $budget=null) { $this->budget=$budget; }
    public function request(string $method, array $params): array {
        if (!in_array($method, ['gettrack','gettrackingnumber'], true)) { throw new TrackingError('configuration'); }
        $c = KqdSettings::credentials();
        // Safe HTTP rejects private hosts; HTTPS only, no credential-bearing redirects.
        $response = wp_safe_remote_post($c['endpoint'], [
            'timeout'=>$this->budget ? $this->budget->timeout() : 6, 'redirection'=>0, 'sslverify'=>true, 'limit_response_size'=>1048576,
            'headers'=>['Content-Type'=>'application/x-www-form-urlencoded'],
            'body'=>http_build_query(['appToken'=>$c['appToken'], 'appKey'=>$c['appKey'],
                'serviceMethod'=>$method, 'paramsJson'=>json_encode($params, JSON_UNESCAPED_UNICODE)], '', '&'),
        ]);
        if (is_wp_error($response)) {
            $message=method_exists($response,'get_error_message') ? $response->get_error_message() : '';
            throw new TrackingError(stripos($message,'timed out')!==false || stripos($message,'timeout')!==false ? 'timeout' : 'provider_unavailable');
        }
        $status = wp_remote_retrieve_response_code($response);
        if ($status===401 || $status===403) { throw new TrackingError('auth_error'); }
        if ($status < 200 || $status >= 300) { throw new TrackingError('provider_unavailable'); }
        $body = wp_remote_retrieve_body($response);
        if (strlen($body) >= 1048576) { throw new TrackingError('malformed_response'); }
        $decoded = json_decode($body, true, 64);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) { throw new TrackingError('malformed_response'); }
        return $decoded;
    }
}
