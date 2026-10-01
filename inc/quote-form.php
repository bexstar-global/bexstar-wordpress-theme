<?php
/** Native quote inquiries. No credentials, uploads, CRM or customer account required. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

function bexstar_quote_fields() {
    return array( 'country', 'postal', 'whatsapp', 'email', 'method', 'package_weight', 'length', 'width', 'height', 'pieces', 'total_weight', 'volume', 'name', 'description', 'delivery', 'speed', 'additional' );
}

function bexstar_quote_validate( $input ) {
    $data = array();
    $errors = array();
    foreach ( bexstar_quote_fields() as $key ) {
        $raw = isset( $input[$key] ) && is_string( $input[$key] ) ? trim( $input[$key] ) : '';
        $limit = 'additional' === $key ? 3000 : ( 'description' === $key ? 500 : 254 );
        if ( strlen( $raw ) > $limit ) { $errors[$key] = 'Please shorten this field.'; }
        $data[$key] = 'additional' === $key ? sanitize_textarea_field( $raw ) : sanitize_text_field( $raw );
    }
    foreach ( array( 'country' => 'destination country', 'postal' => 'destination ZIP / postal code' ) as $key => $label ) {
        if ( '' === $data[$key] ) { $errors[$key] = 'Please enter your ' . $label . '.'; }
    }
    if ( '' === $data['whatsapp'] && '' === $data['email'] ) {
        $errors['contact'] = 'Please provide your WhatsApp number or email so we can send your quotation.';
    }
    if ( '' !== $data['email'] && ( ! is_email( $data['email'] ) || trim( $input['email'] ) !== $data['email'] ) ) {
        $errors['email'] = 'Please enter a valid email address.';
    }
    if ( '' !== $data['whatsapp'] && ! preg_match( '/^\+[0-9 ()\-\.]{7,30}$/', $data['whatsapp'] ) ) {
        $errors['whatsapp'] = 'Please enter a WhatsApp number with country code, for example +1 555 123 4567.';
    } elseif ( '' !== $data['whatsapp'] ) {
        $digits = preg_replace( '/\D/', '', $data['whatsapp'] );
        if ( strlen( $digits ) < 7 || strlen( $digits ) > 15 || '0' === $digits[0] ) {
            $errors['whatsapp'] = 'Please check your WhatsApp number and country code.';
        }
    }
    $methods = array( 'package' => array( 'package_weight', 'length', 'width', 'height', 'pieces' ), 'total' => array( 'total_weight', 'volume' ) );
    if ( ! isset( $methods[$data['method']] ) ) {
        $errors['method'] = 'Please choose how to describe your cargo.';
    } else {
        foreach ( $methods[$data['method']] as $key ) {
            $value = $data[$key];
            if ( ! preg_match( '/^(?:\d+(?:\.\d+)?|\.\d+)$/D', $value ) || (float) $value <= 0 || (float) $value > 1000000000 ) {
                $errors[$key] = 'Please enter a positive number.';
            } elseif ( 'pieces' === $key && ( ! ctype_digit( $value ) || (float) $value > 1000000 ) ) {
                $errors[$key] = 'Please enter a whole number of pieces from 1 to 1000000.';
            }
        }
    }
    $choices = array( 'delivery' => array( '', 'Amazon FBA', 'Commercial Address', 'Residential Address', 'Warehouse', 'Not sure' ), 'speed' => array( '', 'Fastest available', 'Standard', 'Economy / flexible', 'Not sure' ) );
    foreach ( $choices as $key => $allowed ) {
        if ( ! in_array( $data[$key], $allowed, true ) ) { $errors[$key] = 'Please select one of the listed options.'; }
    }
    return array( $data, $errors );
}

function bexstar_quote_message( $data ) {
    $text = "BEXSTAR shipping inquiry\n\nContact\nName: {$data['name']}\nWhatsApp: {$data['whatsapp']}\nEmail: {$data['email']}\n\nDestination\nCountry: {$data['country']}\nZIP / Postal Code: {$data['postal']}\n\nCargo data\n";
    if ( 'package' === $data['method'] ) {
        $text .= "Method: Package details\nWeight per package (kg): {$data['package_weight']}\nDimensions (cm), L x W x H: {$data['length']} x {$data['width']} x {$data['height']}\nNumber of packages / pieces: {$data['pieces']}\n";
    } else {
        $text .= "Method: Total cargo\nTotal weight (kg): {$data['total_weight']}\nTotal volume (CBM): {$data['volume']}\n";
    }
    return $text . "\nOptional information\nCargo description: {$data['description']}\nDelivery type: {$data['delivery']}\nPreferred delivery speed: {$data['speed']}\nAdditional information:\n{$data['additional']}\n";
}

function bexstar_quote_process( $input ) {
    list( $data, $errors ) = bexstar_quote_validate( $input );
    $nonce = isset( $input['bex_quote_nonce'] ) && is_string( $input['bex_quote_nonce'] ) ? $input['bex_quote_nonce'] : '';
    if ( ! wp_verify_nonce( $nonce, 'bexstar_quote' ) ) { $errors['security'] = 'Your form session expired. Please reload this page and try again.'; }
    if ( ! isset( $input['website'] ) || ! is_string( $input['website'] ) || '' !== $input['website'] ) { $errors['security'] = 'Please reload this page and try again.'; }
    if ( $errors ) { return array( 'data' => $data, 'errors' => $errors, 'sent' => false ); }
    $headers = array( 'Content-Type: text/plain; charset=UTF-8' );
    if ( $data['email'] ) { $headers[] = 'Reply-To: ' . sanitize_email( $data['email'] ); }
    try {
        $sent = wp_mail( 'quotes@bexgl.com', 'BEXSTAR shipping inquiry', bexstar_quote_message( $data ), $headers );
    } catch ( Throwable $error ) {
        $sent = false;
    }
    return array( 'data' => $data, 'errors' => $sent ? array() : array( 'send' => 'We could not send your request. Please check the required fields or contact BEXSTAR directly.' ), 'sent' => (bool) $sent );
}

add_action( 'template_redirect', function () {
    if ( ! is_page( 'get-a-quote' ) ) { return; }
    // Do not serve a cached nonce or cache contact details in a failed POST response.
    if ( ! defined( 'DONOTCACHEPAGE' ) ) { define( 'DONOTCACHEPAGE', true ); }
    nocache_headers();
    if ( 'POST' === ( $_SERVER['REQUEST_METHOD'] ?? '' ) && isset( $_POST['bex_quote_submit'] ) ) {
        $GLOBALS['bexstar_quote_result'] = bexstar_quote_process( wp_unslash( $_POST ) );
    }
} );

add_action( 'wp_enqueue_scripts', function () {
    if ( ! is_page( 'get-a-quote' ) ) { return; }
    wp_enqueue_style( 'bexstar-quote', get_theme_file_uri( 'assets/css/quote.css' ), array( 'bexstar-site' ), filemtime( get_theme_file_path( 'assets/css/quote.css' ) ) );
    wp_enqueue_script( 'bexstar-quote', get_theme_file_uri( 'assets/js/quote.js' ), array(), filemtime( get_theme_file_path( 'assets/js/quote.js' ) ), true );
} );

function bexstar_quote_input( $key, $label, $data, $errors, $type = 'text', $required = false, $extra = '' ) {
    $error = $errors[$key] ?? '';
    $describedby = in_array( $key, array( 'whatsapp', 'email' ), true ) ? 'bex-q-contact-help bex-q-contact-error' : '';
    if ( $error ) { $describedby .= ' bex-q-error-' . $key; }
    echo '<div class="bex-quote-field"><label for="bex-q-' . esc_attr( $key ) . '">' . esc_html( $label ) . ( $required ? ' <span aria-hidden="true">*</span>' : '' ) . '</label>';
    echo '<input id="bex-q-' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" type="' . esc_attr( $type ) . '" value="' . esc_attr( $data[$key] ?? '' ) . '"' . ( $required ? ' required' : '' ) . ( $error ? ' aria-invalid="true"' : '' ) . ( $describedby ? ' aria-describedby="' . esc_attr( trim( $describedby ) ) . '"' : '' ) . ' ' . $extra . '>';
    if ( $error ) { echo '<span class="bex-quote-error" id="bex-q-error-' . esc_attr( $key ) . '">' . esc_html( $error ) . '</span>'; }
    echo '</div>';
}

function bexstar_render_quote_form() {
    $result = $GLOBALS['bexstar_quote_result'] ?? array( 'data' => array(), 'errors' => array(), 'sent' => false );
    $data = $result['data']; $errors = $result['errors'];
    if ( $result['sent'] ) {
        return '<div class="bex-quote-notice" role="status" tabindex="-1" data-quote-result>Thank you. We received your shipment details. The BEXSTAR team will review your request and contact you shortly.</div>';
    }
    $method = in_array( $data['method'] ?? '', array( 'package', 'total' ), true ) ? $data['method'] : 'package';
    ob_start(); ?>
    <?php if ( $errors ) : ?>
    <div class="bex-quote-notice bex-quote-error" role="alert" tabindex="-1" data-quote-result>
        <p>We could not send your request. Please check the required fields or <a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">contact BEXSTAR directly</a>.</p>
        <ul><?php foreach ( $errors as $key => $error ) : ?><li><?php echo esc_html( $error ); ?></li><?php endforeach; ?></ul>
    </div>
    <?php endif; ?>
    <form class="bex-quote-form" method="post" action="<?php echo esc_url( home_url( '/get-a-quote/' ) ); ?>" data-quote-form>
        <?php wp_nonce_field( 'bexstar_quote', 'bex_quote_nonce', false ); ?>
        <input type="hidden" name="bex_quote_submit" value="1">
        <div class="bex-quote-trap" aria-hidden="true"><label for="bex-q-website">Leave this field empty</label><input id="bex-q-website" name="website" type="text" tabindex="-1" autocomplete="off"></div>
        <p class="bex-quote-hint">* Required. Provide either WhatsApp or email and one complete cargo method.</p>
        <fieldset><legend>Where is your cargo going?</legend><div class="bex-quote-grid">
            <?php bexstar_quote_input( 'country', 'Destination country', $data, $errors, 'text', true, 'autocomplete="country-name" maxlength="80"' ); ?>
            <?php bexstar_quote_input( 'postal', 'Destination ZIP / Postal Code', $data, $errors, 'text', true, 'autocomplete="postal-code" maxlength="30"' ); ?>
        </div></fieldset>
        <fieldset><legend>How can we reach you?</legend><p id="bex-q-contact-help">WhatsApp or email — at least one is required.</p>
        <p class="bex-quote-error" id="bex-q-contact-error" role="alert" <?php echo isset( $errors['contact'] ) ? '' : 'hidden'; ?>>Please provide your WhatsApp number or email so we can send your quotation.</p>
        <div class="bex-quote-grid">
            <?php bexstar_quote_input( 'whatsapp', 'WhatsApp', $data, $errors, 'tel', false, 'autocomplete="tel" maxlength="32" placeholder="+ Country code + phone number"' ); ?>
            <?php bexstar_quote_input( 'email', 'Email', $data, $errors, 'email', false, 'autocomplete="email" maxlength="254"' ); ?>
        </div></fieldset>
        <fieldset><legend>Cargo Information</legend>
        <p class="bex-quote-hint">Not sure about the exact weight or volume? Send what you know — we can help. Estimates are fine.</p>
        <p>How would you like to describe your cargo?</p>
        <div class="bex-quote-methods">
        <label><input type="radio" name="method" value="package" <?php checked( $method, 'package' ); ?>> Package details</label>
        <label><input type="radio" name="method" value="total" <?php checked( $method, 'total' ); ?>> Total cargo</label>
        </div>
        <noscript><p>Complete only the cargo method selected above. The other method can be left blank.</p></noscript>
        <fieldset data-cargo-method="package"><legend>Package details</legend><div class="bex-quote-grid">
        <?php foreach ( array( 'package_weight' => 'Weight per piece (kg)', 'length' => 'Length (cm)', 'width' => 'Width (cm)', 'height' => 'Height (cm)', 'pieces' => 'Number of pieces' ) as $key => $label ) { bexstar_quote_input( $key, $label . ' *', $data, $errors, 'number', false, 'min="' . ( 'pieces' === $key ? '1' : '0.000001' ) . '" step="' . ( 'pieces' === $key ? '1' : 'any' ) . '" inputmode="decimal"' ); } ?>
        </div></fieldset>
        <fieldset data-cargo-method="total"><legend>Total cargo</legend><div class="bex-quote-grid">
        <?php foreach ( array( 'total_weight' => 'Total weight (kg)', 'volume' => 'Total volume (CBM)' ) as $key => $label ) { bexstar_quote_input( $key, $label . ' *', $data, $errors, 'number', false, 'min="0.000001" step="any" inputmode="decimal"' ); } ?>
        </div></fieldset></fieldset>
        <details class="bex-quote-optional" <?php echo array_intersect( array_keys( $errors ), array( 'name', 'description', 'delivery', 'speed', 'additional' ) ) ? 'open' : ''; ?>>
        <summary>Add more information (optional)</summary><div class="bex-quote-grid">
        <?php bexstar_quote_input( 'name', 'Name (optional)', $data, $errors, 'text', false, 'autocomplete="name" maxlength="100"' ); ?>
        <?php bexstar_quote_input( 'description', 'Cargo description (optional)', $data, $errors, 'text', false, 'maxlength="500" placeholder="Furniture, clothing, machinery, Amazon FBA inventory, general cargo"' ); ?>
        <?php foreach ( array( 'delivery' => array( 'Delivery type', 'Amazon FBA', 'Commercial Address', 'Residential Address', 'Warehouse', 'Not sure' ), 'speed' => array( 'Preferred delivery speed', 'Fastest available', 'Standard', 'Economy / flexible', 'Not sure' ) ) as $key => $options ) : $label = array_shift( $options ); ?>
        <div class="bex-quote-field"><label for="bex-q-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?> (optional)</label><select id="bex-q-<?php echo esc_attr( $key ); ?>" name="<?php echo esc_attr( $key ); ?>"><option value="">Select if known</option><?php foreach ( $options as $option ) : ?><option <?php selected( $data[$key] ?? '', $option ); ?>><?php echo esc_html( $option ); ?></option><?php endforeach; ?></select></div>
        <?php endforeach; ?>
        </div><div class="bex-quote-field"><label for="bex-q-additional">Additional information (optional)</label><textarea id="bex-q-additional" name="additional" rows="3" maxlength="3000"><?php echo esc_textarea( $data['additional'] ?? '' ); ?></textarea></div></details>
        <button class="bex-button" type="submit">REQUEST A QUOTE</button>
    </form>
    <?php return ob_get_clean();
}
add_shortcode( 'bexstar_quote_form', 'bexstar_render_quote_form' );
