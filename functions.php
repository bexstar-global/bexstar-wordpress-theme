<?php
/**
 * Theme bootstrap. No activation imports, external requests or deployment hooks.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

require_once __DIR__ . '/inc/navigation.php';
require_once __DIR__ . '/inc/media.php';
require_once __DIR__ . '/inc/customer-content.php';
require_once __DIR__ . '/inc/quote-form.php';
require_once __DIR__ . '/inc/sample-calculator.php';
if ( is_admin() ) { require_once __DIR__ . '/inc/private-dispatch.php'; }
require_once __DIR__ . '/inc/tracking/bootstrap.php';

add_action( 'after_setup_theme', function () {
    load_theme_textdomain( 'bexstar', get_template_directory() . '/languages' );
    add_theme_support( 'wp-block-styles' );
    add_theme_support( 'editor-styles' );
    add_theme_support( 'responsive-embeds' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'custom-logo', array( 'height' => 48, 'width' => 200, 'flex-width' => true, 'flex-height' => true ) );
    add_editor_style( 'assets/css/site.css' );
    add_editor_style( 'assets/css/china-to-uk.css' );
    add_editor_style( 'assets/css/china-to-uk-v2.css' );
    add_editor_style( 'assets/css/china-to-europe.css' );
    add_editor_style( 'assets/css/china-to-australia.css' );
    add_editor_style( 'assets/css/china-to-canada.css' );
    add_editor_style( 'assets/css/shipping-overview.css' );
    add_editor_style( 'assets/css/china-to-middle-east.css' );
    register_nav_menus( array( 'bexstar-primary' => __( 'BEXSTAR primary navigation', 'bexstar' ) ) );
} );

add_action( 'wp_enqueue_scripts', function () {
    $version = wp_get_theme()->get( 'Version' );
    // Refresh cached styles when a deployment changes the stylesheet.
    $style_path = get_theme_file_path( 'assets/css/site.css' );
    $style_hash = is_readable( $style_path ) ? md5_file( $style_path ) : false;
    $style_version = $style_hash ? substr( $style_hash, 0, 12 ) : $version;
    wp_enqueue_style( 'bexstar-site', get_theme_file_uri( 'assets/css/site.css' ), array(), $style_version );
    if ( is_page_template( 'page-china-to-uk' ) || is_page_template( 'page-china-to-usa-fba' ) ) {
        $uk_style = 'assets/css/china-to-uk.css';
        wp_enqueue_style( 'bexstar-china-to-uk', get_theme_file_uri( $uk_style ), array( 'bexstar-site' ), substr( md5_file( get_theme_file_path( $uk_style ) ), 0, 12 ) );
    }
    if ( is_page_template( 'page-china-to-uk' ) ) {
        $uk_v2_style = 'assets/css/china-to-uk-v2.css';
        wp_enqueue_style( 'bexstar-china-to-uk-v2', get_theme_file_uri( $uk_v2_style ), array( 'bexstar-china-to-uk' ), substr( md5_file( get_theme_file_path( $uk_v2_style ) ), 0, 12 ) );
    }
    if ( is_page_template( 'page-china-to-europe' ) ) {
        $europe_style = 'assets/css/china-to-europe.css';
        wp_enqueue_style( 'bexstar-china-to-europe', get_theme_file_uri( $europe_style ), array( 'bexstar-site' ), substr( md5_file( get_theme_file_path( $europe_style ) ), 0, 12 ) );
    }
    if ( is_page_template( 'page-china-to-australia' ) ) {
        $australia_style = 'assets/css/china-to-australia.css';
        wp_enqueue_style( 'bexstar-china-to-australia', get_theme_file_uri( $australia_style ), array( 'bexstar-site' ), substr( md5_file( get_theme_file_path( $australia_style ) ), 0, 12 ) );
    }
    if ( is_page_template( 'page-china-to-canada' ) ) {
        $canada_style = 'assets/css/china-to-canada.css';
        wp_enqueue_style( 'bexstar-china-to-canada', get_theme_file_uri( $canada_style ), array( 'bexstar-site' ), substr( md5_file( get_theme_file_path( $canada_style ) ), 0, 12 ) );
    }
    if ( is_page_template( 'page-shipping-overview' ) ) {
        $shipping_style = 'assets/css/shipping-overview.css';
        wp_enqueue_style( 'bexstar-shipping-overview', get_theme_file_uri( $shipping_style ), array( 'bexstar-site' ), substr( md5_file( get_theme_file_path( $shipping_style ) ), 0, 12 ) );
    }
    if ( is_page_template( 'page-china-to-middle-east' ) ) {
        $middle_east_style = 'assets/css/china-to-middle-east.css';
        wp_enqueue_style( 'bexstar-china-to-middle-east', get_theme_file_uri( $middle_east_style ), array( 'bexstar-site' ), substr( md5_file( get_theme_file_path( $middle_east_style ) ), 0, 12 ) );
    }
    wp_enqueue_script( 'bexstar-site', get_theme_file_uri( 'assets/js/site.js' ), array(), $version, true );
} );

add_action( 'init', function () {
    register_block_pattern_category( 'bexstar', array( 'label' => __( 'BEXSTAR sections', 'bexstar' ) ) );
    wp_register_script(
        'bexstar-block-editor', get_theme_file_uri( 'assets/js/editor.js' ),
        array( 'wp-blocks', 'wp-element', 'wp-components', 'wp-block-editor', 'wp-server-side-render', 'wp-i18n' ),
        wp_get_theme()->get( 'Version' ), true
    );
    register_block_type( __DIR__ . '/blocks/navigation', array( 'render_callback' => 'bexstar_render_navigation' ) );
    register_block_type( __DIR__ . '/blocks/media', array( 'render_callback' => 'bexstar_render_media' ) );
    foreach ( array( 'customer-actions', 'case-studies', 'legal' ) as $block ) {
        register_block_type( __DIR__ . '/blocks/' . $block, array( 'render_callback' => 'bexstar_render_' . str_replace( '-', '_', $block ) ) );
    }
} );

/** Resolve links against this WordPress installation, not the future production domain. */
function bexstar_section_url( $path, $anchor ) {
    if ( 'track' === trim( $path, '/' ) && ! bexstar_tracking_ready() ) {
        return home_url( '/#' . sanitize_title( $anchor ) );
    }
    $page = get_page_by_path( trim( $path, '/' ) );
    if ( $page && 'publish' === get_post_status( $page ) ) {
        return get_permalink( $page );
    }
    return home_url( '/#' . sanitize_title( $anchor ) );
}
