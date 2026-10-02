<?php
namespace Bexstar\Tracking;
if ( ! defined( 'ABSPATH' ) ) { exit; }

interface MappingRepository {
    /** Exact BEXSTAR-number lookup; null means unmapped. No remote discovery. */
    public function find( string $bexstar_tracking_number ): ?ShipmentMapping;
    /**
     * Insert parent + references atomically. Return false ONLY on an existing public
     * number; never overwrite. A persistent implementation MUST have a global UNIQUE
     * index on the canonical number (not number + year), including archived numbers.
     * Failures other than collisions must throw. The database index resolves races.
     */
    public function insert( ShipmentMapping $mapping ): bool;
}
