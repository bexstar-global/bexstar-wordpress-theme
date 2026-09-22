<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

function bexstar_published_url( $path ) {
    $page = get_page_by_path( trim( $path, '/' ) );
    return $page && 'publish' === get_post_status( $page ) ? get_permalink( $page ) : '';
}

/** Published pages only. No fake enquiry submission or unavailable-page links. */
function bexstar_render_customer_actions() {
    $html = '<div class="bex-contact-actions">';
    foreach ( array( 'get-a-quote' => __( 'GET A QUOTE', 'bexstar' ), 'contact' => __( 'CONTACT BEXSTAR', 'bexstar' ) ) as $path => $label ) {
        $url = bexstar_published_url( $path );
        if ( $url ) {
            $html .= '<a class="bex-button" href="' . esc_url( $url ) . '">' . esc_html( $label ) . '</a>';
        } else {
            $html .= '<span class="bex-button bex-unavailable" aria-disabled="true">' . esc_html( $label ) . '<small>' . esc_html__( 'Coming soon', 'bexstar' ) . '</small></span>';
        }
    }
    return $html . '</div>';
}

function bexstar_render_legal() {
    $html = '<div class="bex-legal">';
    foreach ( array( 'privacy' => __( 'Privacy', 'bexstar' ), 'terms' => __( 'Terms', 'bexstar' ), 'cookies' => __( 'Cookies', 'bexstar' ) ) as $path => $label ) {
        $url = bexstar_published_url( $path );
        if ( 'privacy' === $path && ! $url ) { $url = get_privacy_policy_url(); }
        if ( $url ) { $html .= '<a href="' . esc_url( $url ) . '">' . esc_html( $label ) . '</a>'; }
    }
    return $html . '</div>';
}

/** Small homepage selection, not a new blog/CPT system. */
function bexstar_render_case_studies() {
    $query = new WP_Query( array(
        'post_type' => 'post', 'post_status' => 'publish',
        'category_name' => 'case-studies', 'posts_per_page' => 3,
        'ignore_sticky_posts' => true, 'no_found_rows' => true,
    ) );
    if ( ! $query->have_posts() ) {
        return '<p class="bex-case-empty">' . esc_html__( 'New shipping stories are on the way.', 'bexstar' ) . '</p>';
    }
    $html = '<div class="bex-case-grid">';
    foreach ( $query->posts as $post ) {
        $url = get_permalink( $post );
        $html .= '<article class="bex-case-item">';
        if ( has_post_thumbnail( $post ) ) {
            $html .= '<a class="bex-case-image" href="' . esc_url( $url ) . '" tabindex="-1" aria-hidden="true">' . get_the_post_thumbnail( $post, 'large', array( 'loading' => 'lazy', 'alt' => '' ) ) . '</a>';
        }
        $html .= '<h3><a href="' . esc_url( $url ) . '">' . esc_html( get_the_title( $post ) ) . '</a></h3>';
        $html .= '<p>' . esc_html( wp_trim_words( get_the_excerpt( $post ), 28 ) ) . '</p></article>';
    }
    return $html . '</div>';
}

/** Custom logo first, trusted theme asset second, text wordmark last. */
function bexstar_brand_markup( $variant = 'dark' ) {
    $logo_id = absint( get_theme_mod( 'custom_logo' ) );
    if ( $logo_id && 'dark' === $variant ) {
        return wp_get_attachment_image( $logo_id, 'full', false, array( 'class' => 'bex-logo-image', 'alt' => 'BEXSTAR', 'loading' => 'eager' ) );
    }
    $files = 'light' === $variant ? array( 'bexstar-logo-light.svg' ) : array( 'bexstar-logo-dark.svg', 'bexstar-logo.svg' );
    foreach ( $files as $file ) {
        $path = 'assets/images/brand/' . $file;
        if ( is_file( get_theme_file_path( $path ) ) ) {
            return '<img class="bex-logo-image" src="' . esc_url( get_theme_file_uri( $path ) ) . '" width="200" height="48" alt="BEXSTAR">';
        }
    }
    return '<span class="bex-wordmark-text">BEXSTAR</span>';
}
