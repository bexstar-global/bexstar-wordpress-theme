<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
foreach ( array( 'errors', 'mapping', 'storage', 'config', 'resolver', 'providers/interface' ) as $module ) {
    require_once __DIR__ . '/' . $module . '.php';
}

/** Deliberately closed until endpoint security and real-provider acceptance are complete. */
function bexstar_tracking_ready() { return false; }

add_shortcode( 'bexstar_tracking', function () {
    return '<div class="bex-tracking-foundation"><p id="bex-tracking-availability">Shipment tracking is coming soon. Please contact BEXSTAR for shipment updates.</p><p><label for="bex-tracking-number">BEXSTAR tracking number</label><br><input id="bex-tracking-number" type="text" disabled aria-describedby="bex-tracking-availability"></p><button type="button" disabled aria-describedby="bex-tracking-availability">TRACK — Coming soon</button><p><a href="' . esc_url( home_url( '/contact/' ) ) . '">CONTACT BEXSTAR</a></p></div>';
} );
