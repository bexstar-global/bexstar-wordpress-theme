<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
foreach ( array( 'errors', 'mapping', 'storage', 'allocator', 'config', 'resolver', 'providers/interface', 'providers/kqd-transport', 'providers/kqd-normalizer', 'providers/kqd', 'persistence', 'providers/pending', 'provider-catalog', 'public-response', 'service', 'controller', 'cli', 'admin-cli' ) as $module ) {
    require_once __DIR__ . '/' . $module . '.php';
}

/** Deliberately closed until endpoint security and real-provider acceptance are complete. */
function bexstar_tracking_ready() { return false; }

require_once __DIR__ . '/view.php';
add_shortcode( 'bexstar_tracking', 'bexstar_render_tracking_page' );
add_action('rest_api_init', [\Bexstar\Tracking\TrackingController::class, 'register']);
add_action( 'wp_enqueue_scripts', function () {
    if ( ! is_page( 'track' ) && ! is_page_template( 'page-track' ) ) { return; }
    wp_enqueue_script('bexstar-tracking',get_theme_file_uri('assets/js/tracking.js'),array(),filemtime(get_theme_file_path('assets/js/tracking.js')),true);
    wp_enqueue_style( 'bexstar-tracking', get_theme_file_uri( 'assets/css/tracking.css' ), array( 'bexstar-site' ), filemtime( get_theme_file_path( 'assets/css/tracking.css' ) ) );
} );
