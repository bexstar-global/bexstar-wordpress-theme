<?php
namespace Bexstar\Tracking;
if ( ! defined( 'ABSPATH' ) ) { exit; }

final class TrackingNumber {
    // Canonical public format: BEXSTAR + MMDD + exactly three serial digits.
    public static function parse( $value ): string {
        if ( ! is_string( $value ) ) { throw new TrackingError( 'invalid_number' ); }
        $value = strtoupper( trim( $value ) );
        if ( ! preg_match( '/\ABEXSTAR([0-9]{2})([0-9]{2})[0-9]{3}\z/', $value, $parts ) || ! checkdate( (int) $parts[1], (int) $parts[2], 2000 ) ) { throw new TrackingError( 'invalid_number' ); }
        return $value;
    }
}

/** Lookup syntax is deliberately broader than allocation. Existence is verified separately. */
final class TrackingReference {
    public static function parse( $value ): string {
        if (!is_string($value)) { throw new TrackingError('invalid_number'); }
        // Resource/encoding hygiene only, never a numbering-format or prefix rule.
        $value = trim($value);
        if ($value === '' || strlen($value)>128 || !preg_match('//u',$value) || preg_match('/[\x00-\x1F\x7F]/',$value)) {
            throw new TrackingError('invalid_number');
        }
        return $value; // Preserve case and punctuation for provider-stored references.
    }
}

final class ShipmentMapping {
    private $number;
    private $references;
    public function __construct( string $number, array $references ) {
        $this->number = TrackingReference::parse( $number );
        if ( ! $references || count( $references ) > 100 ) { throw new TrackingError( 'configuration' ); }
        $ids = array();
        foreach ( $references as $ref ) {
            foreach ( array( 'reference_id', 'leg_id', 'provider_code', 'role' ) as $key ) {
                if ( ! isset( $ref[$key] ) || ! is_string( $ref[$key] ) || '' === trim( $ref[$key] ) || strlen( $ref[$key] ) > 128 || preg_match( '/[\x00-\x1F\x7F]/', $ref[$key] ) ) { throw new TrackingError( 'configuration' ); }
            }
            if ( isset( $ids[$ref['reference_id']] ) || ! preg_match( '/\A[a-z][a-z0-9_-]{0,31}\z/', $ref['provider_code'] ) || ! in_array( $ref['role'], array( 'primary', 'fallback' ), true ) ) { throw new TrackingError( 'configuration' ); }
            if (isset($ref['provider_tracking_number']) && (!is_string($ref['provider_tracking_number']) || strlen($ref['provider_tracking_number'])>128 || preg_match('/[\x00-\x1F\x7F]/',$ref['provider_tracking_number']))) { throw new TrackingError('configuration'); }
            $ids[$ref['reference_id']] = true;
        }
        // One direct source per leg; split shipments use distinct leg IDs.
        $legs = array();
        foreach ( $references as $ref ) {
            if ( 'primary' === $ref['role'] ) {
                if ( isset( $legs[$ref['leg_id']] ) ) { throw new TrackingError( 'configuration' ); }
                $legs[$ref['leg_id']] = true;
            }
        }
        foreach ( $references as $ref ) {
            if ( ! isset( $legs[$ref['leg_id']] ) ) { throw new TrackingError( 'configuration' ); }
        }
        $this->references = $references;
    }
    public function number(): string { return $this->number; }
    /** Internal only. Never serialize mappings to customers. */
    public function references(): array { return $this->references; }
}
