<?php
/** Private, administrator-only dispatch routing. Provider names live in WordPress, never in theme files. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

function bexstar_dispatch_providers() {
    $providers = get_option( 'bexstar_private_providers', array() );
    return is_array( $providers ) ? $providers : array();
}

function bexstar_dispatch_assignments() {
    $assignments = get_option( 'bexstar_private_assignments', array() );
    return is_array( $assignments ) ? $assignments : array();
}

function bexstar_dispatch_redirect( $status ) {
    wp_safe_redirect( add_query_arg( 'bexstar_dispatch', $status, admin_url( 'tools.php?page=bexstar-private-dispatch' ) ) );
    exit;
}

add_action( 'admin_menu', function () {
    add_management_page(
        'Private Dispatch Routing',
        'Dispatch Routing',
        'manage_options',
        'bexstar-private-dispatch',
        'bexstar_render_private_dispatch'
    );
} );

add_action( 'admin_post_bexstar_add_provider', function () {
    if ( ! current_user_can( 'manage_options' ) ) { wp_die( 'Access denied.', '', array( 'response' => 403 ) ); }
    check_admin_referer( 'bexstar_add_provider' );
    $name = isset( $_POST['provider_name'] ) && is_string( $_POST['provider_name'] )
        ? sanitize_text_field( wp_unslash( $_POST['provider_name'] ) ) : '';
    if ( '' === $name || strlen( $name ) > 120 ) { bexstar_dispatch_redirect( 'invalid-provider' ); }
    $providers = bexstar_dispatch_providers();
    foreach ( $providers as $provider ) {
        if ( isset( $provider['name'] ) && 0 === strcasecmp( $provider['name'], $name ) ) {
            bexstar_dispatch_redirect( 'duplicate-provider' );
        }
    }
    $id = wp_generate_uuid4();
    $providers[$id] = array( 'name' => $name );
    update_option( 'bexstar_private_providers', $providers, false );
    bexstar_dispatch_redirect( 'provider-added' );
} );

add_action( 'admin_post_bexstar_assign_provider', function () {
    if ( ! current_user_can( 'manage_options' ) ) { wp_die( 'Access denied.', '', array( 'response' => 403 ) ); }
    check_admin_referer( 'bexstar_assign_provider' );
    $reference = isset( $_POST['shipment_reference'] ) && is_string( $_POST['shipment_reference'] )
        ? strtoupper( trim( wp_unslash( $_POST['shipment_reference'] ) ) ) : '';
    $provider_id = isset( $_POST['provider_id'] ) && is_string( $_POST['provider_id'] )
        ? sanitize_text_field( wp_unslash( $_POST['provider_id'] ) ) : '';
    $providers = bexstar_dispatch_providers();
    if ( ! preg_match( '/^[A-Z0-9][A-Z0-9_-]{2,63}$/D', $reference ) || ! isset( $providers[$provider_id] ) ) {
        bexstar_dispatch_redirect( 'invalid-assignment' );
    }
    $assignments = bexstar_dispatch_assignments();
    $assignments[$reference] = array( 'provider_id' => $provider_id, 'updated_at' => current_time( 'mysql', true ) );
    update_option( 'bexstar_private_assignments', $assignments, false );
    bexstar_dispatch_redirect( 'assignment-saved' );
} );

function bexstar_render_private_dispatch() {
    if ( ! current_user_can( 'manage_options' ) ) { wp_die( 'Access denied.', '', array( 'response' => 403 ) ); }
    $providers = bexstar_dispatch_providers();
    $assignments = bexstar_dispatch_assignments();
    $messages = array(
        'provider-added' => 'Provider saved for internal use.',
        'duplicate-provider' => 'That provider is already listed.',
        'invalid-provider' => 'Enter a provider name of 1 to 120 characters.',
        'assignment-saved' => 'Shipment routing saved.',
        'invalid-assignment' => 'Use a 3–64 character reference (letters, numbers, underscores or hyphens) and choose a listed provider.',
    );
    $status = isset( $_GET['bexstar_dispatch'] ) && is_string( $_GET['bexstar_dispatch'] )
        ? sanitize_key( wp_unslash( $_GET['bexstar_dispatch'] ) ) : '';
    echo '<div class="wrap"><h1>Private Dispatch Routing</h1>';
    echo '<p>Administrator-only. Provider names and shipment assignments are stored in this WordPress database, not in the public theme repository or customer-facing pages. Use an internal reference without customer personal information.</p>';
    if ( isset( $messages[$status] ) ) {
        $class = in_array( $status, array( 'provider-added', 'assignment-saved' ), true ) ? 'notice-success' : 'notice-error';
        echo '<div class="notice ' . esc_attr( $class ) . ' is-dismissible"><p>' . esc_html( $messages[$status] ) . '</p></div>';
    }
    echo '<h2>Internal providers</h2><form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
    echo '<input type="hidden" name="action" value="bexstar_add_provider">';
    wp_nonce_field( 'bexstar_add_provider' );
    echo '<label for="bexstar-provider-name">Provider name</label> <input id="bexstar-provider-name" name="provider_name" type="text" maxlength="120" required class="regular-text"> ';
    submit_button( 'Add provider', 'secondary', 'submit', false );
    echo '</form>';
    if ( $providers ) {
        echo '<ul>';
        foreach ( $providers as $provider ) {
            if ( isset( $provider['name'] ) ) { echo '<li>' . esc_html( $provider['name'] ) . '</li>'; }
        }
        echo '</ul>';
    }
    echo '<h2>Assign a shipment</h2><p>Enter the same internal reference used in your shipment workflow. Saving an existing reference updates its provider.</p>';
    echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
    echo '<input type="hidden" name="action" value="bexstar_assign_provider">';
    wp_nonce_field( 'bexstar_assign_provider' );
    echo '<label for="bexstar-shipment-reference">Shipment reference</label> <input id="bexstar-shipment-reference" name="shipment_reference" type="text" maxlength="64" pattern="[A-Za-z0-9][A-Za-z0-9_-]{2,63}" required class="regular-text"> ';
    echo '<label for="bexstar-provider-id">Provider</label> <select id="bexstar-provider-id" name="provider_id" required><option value="">Select provider</option>';
    foreach ( $providers as $id => $provider ) {
        if ( isset( $provider['name'] ) ) { echo '<option value="' . esc_attr( $id ) . '">' . esc_html( $provider['name'] ) . '</option>'; }
    }
    echo '</select> ';
    submit_button( 'Save routing', 'primary', 'submit', false, $providers ? array() : array( 'disabled' => 'disabled' ) );
    echo '</form>';
    echo '<h2>Recent assignments</h2>';
    if ( ! $assignments ) { echo '<p>No shipments have been assigned yet.</p>'; }
    else {
        $assignments = array_reverse( $assignments, true );
        echo '<table class="widefat striped"><thead><tr><th>Reference</th><th>Provider</th><th>Updated (UTC)</th></tr></thead><tbody>';
        foreach ( $assignments as $reference => $assignment ) {
            $provider_id = $assignment['provider_id'] ?? '';
            $name = $providers[$provider_id]['name'] ?? 'Unavailable provider';
            $updated = $assignment['updated_at'] ?? '';
            echo '<tr><td>' . esc_html( $reference ) . '</td><td>' . esc_html( $name ) . '</td><td>' . esc_html( $updated ) . '</td></tr>';
        }
        echo '</tbody></table>';
    }
    echo '</div>';
}

