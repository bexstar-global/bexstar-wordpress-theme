<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

function bexstar_render_media( $attributes ) {
    $manifest = json_decode( file_get_contents( get_theme_file_path( 'assets/media-manifest.json' ) ), true );
    $key = isset( $attributes['slot'] ) ? sanitize_key( $attributes['slot'] ) : 'hero';
    if ( ! isset( $manifest[ $key ] ) ) { $key = 'hero'; }
    $slot = $manifest[ $key ];
    $image_id = isset( $attributes['imageId'] ) ? absint( $attributes['imageId'] ) : 0;
    $video_id = isset( $attributes['videoId'] ) ? absint( $attributes['videoId'] ) : 0;
    $alt = ! empty( $attributes['decorative'] ) ? '' : ( $attributes['alt'] ?? '' );
    $poster = '';
    $image = '';
    $hero = 'hero' === $key;
    if ( $image_id && wp_attachment_is_image( $image_id ) ) {
        $poster = wp_get_attachment_image_url( $image_id, 'full' );
        $image = wp_get_attachment_image( $image_id, 'full', false, array(
            'alt' => $alt, 'loading' => $hero ? 'eager' : 'lazy',
            'fetchpriority' => $hero ? 'high' : 'auto', 'decoding' => 'async',
        ) );
    } elseif ( is_file( get_theme_file_path( $slot['path'] ) ) ) {
        $poster = get_theme_file_uri( $slot['path'] );
        $image = '<img src="' . esc_url( $poster ) . '" alt="' . esc_attr( $alt ) . '" width="' . absint( $slot['width'] ) . '" height="' . absint( $slot['height'] ) . '" loading="' . ( $hero ? 'eager' : 'lazy' ) . '" decoding="async"' . ( $hero ? ' fetchpriority="high"' : '' ) . '>';
    }
    $video = '';
    if ( $video_id && 'video/mp4' === get_post_mime_type( $video_id ) ) {
        $video = wp_get_attachment_url( $video_id );
    } elseif ( ! empty( $slot['video'] ) && is_file( get_theme_file_path( $slot['video'] ) ) ) {
        $video = get_theme_file_uri( $slot['video'] );
    }
    $position = $attributes['position'] ?? '50% 50%';
    $mobile_position = $attributes['mobilePosition'] ?? '50% 50%';
    $valid_position = '/^(100|[0-9]{1,2})% (100|[0-9]{1,2})%$/';
    if ( ! preg_match( $valid_position, $position ) ) { $position = '50% 50%'; }
    if ( ! preg_match( $valid_position, $mobile_position ) ) { $mobile_position = '50% 50%'; }
    $ratio = absint( $slot['width'] ) . '/' . absint( $slot['height'] );
    $mobile_ratio = str_replace( ':', '/', $slot['mobileRatio'] );
    $labels = ( 'production' !== wp_get_environment_type() ) && apply_filters( 'bexstar_show_media_labels', true );
    $style = '--bex-ratio:' . $ratio . ';--bex-mobile-ratio:' . $mobile_ratio . ';--bex-position:' . $position . ';--bex-mobile-position:' . $mobile_position;
    $html = '<figure class="bex-media" data-bex-slot="' . esc_attr( $key ) . '" style="' . esc_attr( $style ) . '">';
    $html .= '<div class="bex-media-fallback" aria-hidden="true"><span>BEXSTAR</span></div>' . $image;
    if ( $video && $poster ) {
        // Deliberately no autoplay: user control + no video download before intent.
        $html .= '<video class="bex-video" muted playsinline loop preload="none" poster="' . esc_url( $poster ) . '" aria-label="' . esc_attr__( 'Decorative logistics video', 'bexstar' ) . '"><source data-src="' . esc_url( $video ) . '" type="video/mp4"></video>';
        $html .= '<button type="button" class="bex-video-toggle" hidden aria-pressed="false">' . esc_html__( 'Play video', 'bexstar' ) . '</button>';
    }
    if ( $labels ) {
        $html .= '<figcaption class="bex-media-label">MEDIA SLOT · ' . esc_html( basename( $slot['path'] ) ) . ' · ' . absint( $slot['width'] ) . ' × ' . absint( $slot['height'] ) . '</figcaption>';
    }
    return $html . '</figure>';
}
