<?php
namespace Bexstar\Tracking;
if ( ! defined( 'ABSPATH' ) ) { exit; }

final class TrackingError extends \RuntimeException {
    private $reason;
    private const MESSAGES = array(
        'invalid_number' => 'Invalid tracking number.',
        'not_found' => 'Tracking number not found.',
        'unavailable' => 'Tracking information is temporarily unavailable.',
        'provider_unavailable' => 'Provider temporarily unavailable.',
        'timeout' => 'Tracking information is temporarily unavailable.',
        'malformed_response' => 'Tracking information is temporarily unavailable.',
        'rate_limited' => 'Please wait before trying again.',
        'configuration' => 'Tracking information is temporarily unavailable.',
    );
    public function __construct( $reason ) {
        $this->reason = isset( self::MESSAGES[$reason] ) ? $reason : 'unavailable';
        parent::__construct( self::MESSAGES[$this->reason] );
    }
    public function reason(): string { return $this->reason; }
    public function publicError(): array {
        $public = in_array( $this->reason, array( 'timeout', 'malformed_response', 'configuration' ), true ) ? 'unavailable' : $this->reason;
        return array( 'code' => $public, 'message' => self::MESSAGES[$public] );
    }
}
