<?php
namespace Bexstar\Tracking;
if ( ! defined( 'ABSPATH' ) ) { exit; }

final class TrackingNumber {
    // Provisional BEXSTAR-only contract; preserve case and leading zeros.
    public static function parse( $value ): string {
        if ( ! is_string( $value ) ) { throw new TrackingError( 'invalid_number' ); }
        $value = trim( $value );
        if ( ! preg_match( '/\A[A-Za-z0-9][A-Za-z0-9-]{2,63}\z/', $value ) ) { throw new TrackingError( 'invalid_number' ); }
        return $value;
    }
}

final class ShipmentMapping {
    private $number;
    private $references;
    public function __construct( string $number, array $references ) {
        $this->number = TrackingNumber::parse( $number );
        if ( ! $references || count( $references ) > 100 ) { throw new TrackingError( 'configuration' ); }
        $ids = array();
        foreach ( $references as $ref ) {
            foreach ( array( 'reference_id', 'leg_id', 'provider_code', 'provider_tracking_number', 'role' ) as $key ) {
                if ( ! isset( $ref[$key] ) || ! is_string( $ref[$key] ) || '' === trim( $ref[$key] ) || strlen( $ref[$key] ) > 128 || preg_match( '/[\x00-\x1F\x7F]/', $ref[$key] ) ) { throw new TrackingError( 'configuration' ); }
            }
            if ( isset( $ids[$ref['reference_id']] ) || ! preg_match( '/\A[a-z][a-z0-9_-]{0,31}\z/', $ref['provider_code'] ) || ! in_array( $ref['role'], array( 'primary', 'fallback' ), true ) ) { throw new TrackingError( 'configuration' ); }
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
