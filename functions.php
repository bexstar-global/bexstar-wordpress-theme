<?php
/**
 * Theme bootstrap. No activation imports, external requests or deployment hooks.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

require_once __DIR__ . '/inc/navigation.php';
require_once __DIR__ . '/inc/media.php';

add_action( 'after_setup_theme', function () {
    load_theme_textdomain( 'bexstar', get_template_directory() . '/languages' );
    add_theme_support( 'wp-block-styles' );
    add_theme_support( 'editor-styles' );
    add_theme_support( 'responsive-embeds' );
    add_theme_support( 'post-thumbnails' );
    add_editor_style( 'assets/css/site.css' );
    register_nav_menus( array( 'bexstar-primary' => __( 'BEXSTAR primary navigation', 'bexstar' ) ) );
} );

add_action( 'wp_enqueue_scripts', function () {
    $version = wp_get_theme()->get( 'Version' );
    wp_enqueue_style( 'bexstar-site', get_theme_file_uri( 'assets/css/site.css' ), array(), $version );
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
} );

/** Resolve links against this WordPress installation, not the future production domain. */
function bexstar_section_url( $path, $anchor ) {
    $page = get_page_by_path( trim( $path, '/' ) );
    if ( $page && 'publish' === get_post_status( $page ) ) {
        return get_permalink( $page );
    }
    return home_url( '/#' . sanitize_title( $anchor ) );
}
