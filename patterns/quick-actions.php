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
        <form action="<?php echo esc_url( home_url( '/get-a-quote/' ) ); ?>" method="get" class="bex-hub-form">
            <fieldset class="bex-hub-modes">
                <legend>Transport mode</legend>
                <div class="bex-hub-mode-options">
                    <label><input type="radio" name="transport_mode" value="sea" checked><span>Sea Freight</span></label>
                    <label><input type="radio" name="transport_mode" value="air"><span>Air Freight</span></label>
                    <label><input type="radio" name="transport_mode" value="rail-truck"><span>Rail &amp; Truck</span></label>
                    <label><input type="radio" name="transport_mode" value="express"><span>Express</span></label>
                </div>
            </fieldset>
            <fieldset class="bex-hub-route">
                <legend>FROM</legend>
                <div class="bex-hub-fields">
                    <label for="bex-hub-origin">Country / Origin
                        <input id="bex-hub-origin" name="origin_country" type="text" value="China" maxlength="100">
                    </label>
                    <label for="bex-hub-city">Origin city (optional)
                        <input id="bex-hub-city" name="origin_city" type="text" maxlength="100">
                    </label>
                </div>
            </fieldset>
            <fieldset class="bex-hub-route">
                <legend>TO</legend>
                <div class="bex-hub-fields">
                    <label for="bex-hub-country">Destination country
                        <input id="bex-hub-country" name="destination_country" type="text" maxlength="100">
                    </label>
                    <label for="bex-hub-postal">Destination ZIP / Postal Code
                        <input id="bex-hub-postal" name="destination_postal" type="text" maxlength="32">
                    </label>
                </div>
            </fieldset>
            <div class="bex-hub-next">
                <button class="bex-button" type="submit">NEXT</button>
                <p>Start your quote — no account required.</p>
            </div>
        </form>
    </section>
    <section class="bex-hub-section bex-hub-customers" aria-labelledby="bex-hub-customers-title">
        <h2 id="bex-hub-customers-title">Existing customer</h2>
        <p>Manage shipments, account activity and saved logistics information in one place.</p>
        <button class="bex-button bex-hub-disabled" type="button" disabled aria-describedby="bex-hub-portal-status">CUSTOMER PORTAL</button>
        <p id="bex-hub-portal-status" class="bex-hub-status">Coming soon</p>
        <div class="bex-hub-secondary">
            <h3>New to BEXSTAR?</h3>
            <ul>
                <li>Faster quote requests</li>
                <li>Shipment visibility in one place</li>
                <li>Saved shipping information</li>
                <li>Easier repeat shipments</li>
                <li>Direct support from the BEXSTAR team</li>
            </ul>
            <a class="bex-hub-contact" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">CONTACT BEXSTAR</a>
        </div>
    </section>
    <section class="bex-hub-section bex-hub-tracking" aria-labelledby="bex-hub-tracking-title">
        <h2 id="bex-hub-tracking-title">Track your shipment</h2>
        <div class="bex-hub-fields">
            <label for="bex-hub-tracking-number">Tracking number
                <input id="bex-hub-tracking-number" type="text" disabled aria-describedby="bex-hub-tracking-status">
            </label>
        </div>
        <button class="bex-button bex-hub-disabled" type="button" disabled aria-describedby="bex-hub-tracking-status">TRACK</button>
        <p id="bex-hub-tracking-status" class="bex-hub-status">Coming soon</p>
        <div class="bex-hub-secondary">
            <h3>Need help?</h3>
            <p>Contact the BEXSTAR team for shipment and logistics support.</p>
            <a class="bex-hub-contact" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">CONTACT BEXSTAR</a>
        </div>
    </section>
</div>
<!-- /wp:html -->
</section>
<!-- /wp:group -->
