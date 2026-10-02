<?php
namespace Bexstar\Tracking;
if ( ! defined( 'ABSPATH' ) ) { exit; }

interface MappingRepository {
    /** Exact BEXSTAR-number lookup; null means unmapped. No remote discovery. */
    public function find( string $bexstar_tracking_number ): ?ShipmentMapping;
    /** Implementations must save all references atomically and enforce uniqueness. */
    public function save( ShipmentMapping $mapping ): void;
}
