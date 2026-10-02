<?php
namespace Bexstar\Tracking;
if ( ! defined( 'ABSPATH' ) ) { exit; }

final class TrackingNumberAllocator {
    private $repository;
    public function __construct( MappingRepository $repository ) { $this->repository = $repository; }
    /** No clock ambiguity: issuing date is interpreted in the BEXSTAR business timezone. */
    public function create( \DateTimeImmutable $issued_at, array $references ): ShipmentMapping {
        $prefix = 'BEXSTAR' . $issued_at->setTimezone( new \DateTimeZone( 'Asia/Shanghai' ) )->format( 'md' );
        $serials = range( 0, 999 );
        // Visit every serial at most once in random order; bounded even at capacity.
        for ( $i = 999; $i >= 0; $i-- ) {
            $j = random_int( 0, $i );
            $serial = $serials[$j];
            $serials[$j] = $serials[$i];
            $number = $prefix . sprintf( '%03d', $serial );
            if ( $this->repository->find( $number ) ) { continue; }
            $mapping = new ShipmentMapping( $number, $references );
            if ( $this->repository->insert( $mapping ) ) { return $mapping; }
            // Another writer may have inserted between find and insert: try next.
        }
        throw new TrackingError( 'number_space_exhausted' );
    }
}
