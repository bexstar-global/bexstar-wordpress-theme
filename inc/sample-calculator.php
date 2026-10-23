<?php
/** Sample-shipment planning UI. No prices, provider data, submissions or customer records. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

add_action( 'wp_enqueue_scripts', function () {
    if ( ! is_page( 'sample-calculator' ) && ! is_preview() ) { return; }
    $css = 'assets/css/sample-calculator.css';
    $js = 'assets/js/sample-calculator.js';
    wp_enqueue_style( 'bexstar-sample-calculator', get_theme_file_uri( $css ), array( 'bexstar-site' ), substr( md5_file( get_theme_file_path( $css ) ), 0, 12 ) );
    wp_enqueue_script( 'bexstar-sample-calculator', get_theme_file_uri( $js ), array(), substr( md5_file( get_theme_file_path( $js ) ), 0, 12 ), true );
} );

function bexstar_render_sample_calculator() {
    $destinations = array(
        'GB' => 'United Kingdom',
        'US' => 'United States',
        'DE' => 'Germany',
        'FR' => 'France',
        'IT' => 'Italy',
        'ES' => 'Spain',
        'NL' => 'Netherlands',
        'EU-OTHER' => 'Other European country',
        'AU' => 'Australia',
        'AE' => 'United Arab Emirates',
    );
    ob_start(); ?>
    <form class="bex-sample-form" data-sample-calculator novalidate>
        <div class="bex-sample-grid">
            <div><label for="bex-sample-country">Destination country</label><select id="bex-sample-country" name="country" required><option value="">Choose a country</option><?php foreach ( $destinations as $code => $label ) : ?><option value="<?php echo esc_attr( $code ); ?>"><?php echo esc_html( $label ); ?></option><?php endforeach; ?></select></div>
            <div data-other-country hidden><label for="bex-sample-other-country">Which European country?</label><input id="bex-sample-other-country" name="other_country" type="text" maxlength="80" autocomplete="country-name"></div>
            <div><label for="bex-sample-postal">Destination postal code (if known)</label><input id="bex-sample-postal" name="postal" type="text" maxlength="24" autocomplete="postal-code"></div>
            <div><label for="bex-sample-service">Service interest</label><select id="bex-sample-service" name="service"><option value="both">Compare both</option><option value="express">Express parcel</option><option value="air">Air parcel</option></select></div>
            <div><label for="bex-sample-pieces">Number of identical parcels</label><input id="bex-sample-pieces" name="pieces" type="number" min="1" max="20" step="1" inputmode="numeric" required value="1"></div>
            <div><label for="bex-sample-weight">Weight per parcel (kg)</label><input id="bex-sample-weight" name="weight" type="number" min="0.01" max="30" step="any" inputmode="decimal" required></div>
            <?php foreach ( array( 'length' => 'Length', 'width' => 'Width', 'height' => 'Height' ) as $key => $label ) : ?>
            <div><label for="bex-sample-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?> per parcel (cm)</label><input id="bex-sample-<?php echo esc_attr( $key ); ?>" name="<?php echo esc_attr( $key ); ?>" type="number" min="0.01" max="150" step="any" inputmode="decimal" required></div>
            <?php endforeach; ?>
            <div><label for="bex-sample-cargo">Cargo type</label><select id="bex-sample-cargo" name="cargo"><option value="general">General goods</option><option value="battery">Contains batteries</option><option value="liquid">Liquid or powder</option><option value="unsure">Not sure / other</option></select></div>
        </div>
        <button class="bex-button" type="button" data-sample-review>REVIEW MY PARCEL</button>
    </form>
    <div class="bex-sample-result" data-sample-result hidden tabindex="-1" aria-live="polite">
        <p class="bex-sample-error" data-sample-error hidden></p>
        <div data-sample-summary hidden>
            <h2>Your shipment at a glance</h2>
            <div class="bex-sample-results">
                <div class="bex-sample-card"><span>Actual weight</span><strong data-sample-weight></strong><small>All parcels combined</small></div>
                <div class="bex-sample-card"><span>Parcel volume</span><strong data-sample-volume></strong><small>All parcels combined</small></div>
            </div>
            <p class="bex-sample-destination" data-sample-destination></p>
            <div class="bex-sample-services"><div data-sample-service="express"><h3>Express parcel</h3><p>For small shipments when speed is a priority. Availability and delivery scope are confirmed after reviewing the parcel.</p></div><div data-sample-service="air"><h3>Air parcel</h3><p>For sample shipments that can follow an air cargo schedule. We check the weight, dimensions and destination before quoting.</p></div></div>
            <p>Shipping charges are confirmed by BEXSTAR after checking the destination, cargo type and chargeable weight. No price is calculated or displayed here.</p>
            <div class="bex-sample-actions"><button type="button" class="bex-button bex-sample-copy" data-sample-copy>Copy parcel details</button><a class="bex-button" data-sample-quote href="<?php echo esc_url( home_url( '/get-a-quote/' ) ); ?>">REQUEST A QUOTE</a></div>
            <p class="bex-sample-copy-status" data-sample-copy-status aria-live="polite"></p>
        </div>
    </div>
    <noscript><p>To calculate parcel size, enable JavaScript. You can still <a href="<?php echo esc_url( home_url( '/get-a-quote/' ) ); ?>">request a sample quote</a>.</p></noscript>
    <?php return ob_get_clean();
}
add_shortcode( 'bexstar_sample_calculator', 'bexstar_render_sample_calculator' );
