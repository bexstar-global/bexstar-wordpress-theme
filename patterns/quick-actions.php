<?php
/**
 * Title: Customer quick actions
 * Slug: bexstar/quick-actions
 * Categories: bexstar
 * Inserter: yes
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
?>
<!-- wp:group {"tagName":"section","anchor":"customer-actions","className":"bex-quick","layout":{"type":"default"}} -->
<section id="customer-actions" class="wp-block-group bex-quick">
<!-- wp:html -->
<div class="bex-action-hub">
    <section class="bex-hub-section bex-hub-quote" aria-labelledby="bex-hub-quote-title">
        <h2 id="bex-hub-quote-title">Get a quote</h2>
        <p>Start with your destination and we’ll help you complete the shipment details.</p>
        <form action="<?php echo esc_url( home_url( '/get-a-quote/' ) ); ?>" method="get" class="bex-hub-form">
            <div class="bex-hub-fields">
                <label for="bex-hub-country">Destination Country
                    <input id="bex-hub-country" name="destination_country" type="text" autocomplete="country-name" maxlength="100">
                </label>
                <label for="bex-hub-postal">ZIP / Postal Code
                    <input id="bex-hub-postal" name="destination_postal" type="text" autocomplete="postal-code" maxlength="32">
                </label>
            </div>
            <button class="bex-button" type="submit">START A QUOTE</button>
        </form>
    </section>
    <section class="bex-hub-section bex-hub-customers" aria-labelledby="bex-hub-customers-title">
        <h2 id="bex-hub-customers-title">Existing customers</h2>
        <p>Manage shipments and account activity in one place.</p>
        <p class="bex-hub-pending"><span>CUSTOMER PORTAL</span><small>Coming soon</small></p>
    </section>
    <section class="bex-hub-section bex-hub-tracking" aria-labelledby="bex-hub-tracking-title">
        <h2 id="bex-hub-tracking-title">Track shipment</h2>
        <p>Track your BEXSTAR shipment from one entry point.</p>
        <p class="bex-hub-pending"><small>Coming soon</small></p>
    </section>
</div>
<!-- /wp:html -->
</section>
<!-- /wp:group -->
