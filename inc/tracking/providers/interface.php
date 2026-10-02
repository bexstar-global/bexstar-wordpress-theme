<?php
namespace Bexstar\Tracking;
if ( ! defined( 'ABSPATH' ) ) { exit; }

interface ProviderAdapter {
    public function code(): string;
    /**
     * Internal input: one explicitly mapped reference, not user-selected provider data.
     * Output: normalized shipment/events contract plus optional private source metadata.
     * Errors: TrackingError. Implementations must enforce timeout/size/config limits.
     * No provider implementation is registered or invoked by this skeleton.
     */
    public function fetch( array $reference ): array;
}

final class AdapterRegistry {
    private $adapters = array();
    public function register( ProviderAdapter $adapter ): void {
        $code = $adapter->code();
        if ( ! preg_match( '/\A[a-z][a-z0-9_-]{0,31}\z/', $code ) || isset( $this->adapters[$code] ) ) { throw new TrackingError( 'configuration' ); }
        $this->adapters[$code] = $adapter;
    }
    public function get( string $code ): ProviderAdapter {
        if ( ! isset( $this->adapters[$code] ) ) { throw new TrackingError( 'provider_unavailable' ); }
        return $this->adapters[$code];
    }
}
