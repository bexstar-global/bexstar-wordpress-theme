<?php
namespace Bexstar\Tracking;
if ( ! defined( 'ABSPATH' ) ) { exit; }

interface Resolver {
    /** Returns an internal execution plan, never a public response. */
    public function resolve( string $number ): array;
}

final class InternalMappingResolver implements Resolver {
    private $repository;
    private $config;
    public function __construct( MappingRepository $repository, array $config = array() ) {
        $this->repository = $repository;
        $this->config = array_replace( Config::defaults(), $config );
    }
    public function resolve( string $number ): array {
        $number = TrackingReference::parse( $number );
        $mapping = $this->repository->find( $number );
        if ( ! $mapping ) { throw new TrackingError( 'not_mapped' ); }
        if ( $mapping->number() !== $number ) { throw new TrackingError( 'configuration' ); }
        $legs = array();
        foreach ( $mapping->references() as $ref ) {
            $ref['bexstar_reference'] = $number;
            if ( 'primary' === $ref['role'] ) { $legs[$ref['leg_id']] = array( 'primary' => $ref, 'fallbacks' => array() ); }
        }
        if ( count( $legs ) > $this->config['max_legs_per_lookup'] ) { throw new TrackingError( 'configuration' ); }
        foreach ( $mapping->references() as $ref ) {
            $ref['bexstar_reference'] = $number;
            if ( 'fallback' === $ref['role'] && $this->config['fallback_enabled'] && in_array( $ref['provider_code'], $this->config['enabled_adapters'], true ) ) {
                $leg = &$legs[$ref['leg_id']];
                if ( count( $leg['fallbacks'] ) < $this->config['max_fallbacks_per_leg'] ) { $leg['fallbacks'][] = $ref; }
                unset( $leg );
            }
        }
        return array( 'tracking_number' => $number, 'legs' => $legs );
    }
}
