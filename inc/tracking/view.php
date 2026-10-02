<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/** Empty presentation only. No endpoint, result fixture or provider calls. */
function bexstar_render_tracking_page() {
    $partner_url = \Bexstar\Tracking\Config::partnerUrl();
    ob_start(); ?>
    <div class="bex-tracking-foundation">
        <form class="bex-tracking-form" method="post" action="<?php echo esc_url( home_url( '/track/' ) ); ?>" aria-describedby="bex-tracking-availability">
            <fieldset disabled>
                <legend class="screen-reader-text">BEXSTAR shipment lookup</legend>
                <label for="bex-tracking-number">BEXSTAR Tracking Number</label>
                <div class="bex-tracking-entry">
                    <input id="bex-tracking-number" name="bexstar_tracking_number" type="text" placeholder="BEXSTAR1002037" maxlength="14" autocomplete="off" autocapitalize="characters" spellcheck="false" aria-describedby="bex-tracking-availability">
                    <button class="bex-button" type="submit">TRACK NOW</button>
                </div>
            </fieldset>
        </form>
        <p id="bex-tracking-availability" class="bex-tracking-notice">Coming soon. Shipment tracking is not available yet. <a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Contact BEXSTAR for shipment updates.</a></p>
        <div class="bex-tracking-partner">
            <p>Supported tracking partner</p>
            <?php if ( $partner_url ) : ?>
                <a class="bex-tracking-partner-link" href="<?php echo esc_url( $partner_url ); ?>" target="_blank" rel="noopener noreferrer">17TRACK<span class="screen-reader-text"> (opens in a new tab)</span></a>
            <?php else : ?>
                <span class="bex-tracking-partner-link" aria-disabled="true">17TRACK</span>
                <span class="bex-tracking-partner-note">Partner link coming soon</span>
            <?php endif; ?>
        </div>
        <div class="bex-tracking-feedback" role="status" aria-live="polite" aria-atomic="true"></div>
        <div class="bex-tracking-error" role="alert" tabindex="-1" hidden></div>
        <section class="bex-tracking-results" aria-labelledby="bex-tracking-results-title" hidden>
            <h2 id="bex-tracking-results-title">Shipment updates</h2>
            <dl class="bex-tracking-summary">
                <div><dt>Current status</dt><dd data-tracking-field="current_status"></dd></div>
                <div><dt>Origin</dt><dd data-tracking-field="origin"></dd></div>
                <div><dt>Destination</dt><dd data-tracking-field="destination"></dd></div>
                <div><dt>Transport mode</dt><dd data-tracking-field="transport_mode"></dd></div>
                <div><dt>Last updated</dt><dd data-tracking-field="last_updated"></dd></div>
            </dl>
            <h3>Tracking timeline</h3>
            <ol class="bex-tracking-timeline"></ol>
        </section>
        <template id="bex-tracking-event-template"><li><time></time><h4></h4><p data-event-location></p><p data-event-description></p></li></template>
        <template data-tracking-error="invalid_number">Invalid tracking number. Please check your BEXSTAR tracking number and try again.</template>
        <template data-tracking-error="not_found">Tracking number not found. Please check your number or contact BEXSTAR.</template>
        <template data-tracking-error="unavailable">Tracking information is temporarily unavailable. Please try again later.</template>
        <template data-tracking-error="provider_unavailable">Provider temporarily unavailable. Please try again later or contact BEXSTAR.</template>
    </div>
    <?php return ob_get_clean();
}
