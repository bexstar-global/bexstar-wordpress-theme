<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/** UI consumes only the versioned BEXSTAR API; no provider configuration is rendered. */
function bexstar_render_tracking_page() {
    $api_enabled = \Bexstar\Tracking\Config::apiEnabled();
    $partner_url = \Bexstar\Tracking\Config::partnerUrl();
    ob_start(); ?>
    <div class="bex-tracking-foundation">
        <form data-api-url="<?php echo esc_url(rest_url('bexstar-tracking/v1/lookup')); ?>" class="bex-tracking-form" method="post" action="<?php echo esc_url( home_url( '/track/' ) ); ?>" aria-describedby="bex-tracking-availability">
            <fieldset <?php if (!$api_enabled) { echo 'disabled'; } ?>>
                <legend class="screen-reader-text">BEXSTAR shipment lookup</legend>
                <label for="bex-tracking-number">Tracking / Reference Number</label>
                <div class="bex-tracking-entry">
                    <input id="bex-tracking-number" name="bexstar_tracking_number" type="text" placeholder="BEXSTAR1002037" maxlength="128" required autocomplete="off" autocapitalize="none" spellcheck="false" aria-describedby="bex-tracking-availability">
                    <button class="bex-button" type="submit">TRACK SHIPMENT</button>
                </div>
            </fieldset>
        </form>
        <p id="bex-tracking-availability" class="bex-tracking-notice"><?php echo $api_enabled ? 'Enter the shipment reference provided by BEXSTAR.' : 'Coming soon. Shipment tracking is not available yet.'; ?> <a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Contact BEXSTAR for shipment updates.</a></p>
        <noscript><p>Enable JavaScript to look up a shipment, or contact BEXSTAR for updates.</p></noscript>
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
                <div><dt>Reference</dt><dd data-tracking-field="tracking_number"></dd></div>
                <div><dt>Current status</dt><dd data-tracking-field="current_status"></dd></div>
                <div><dt>Origin</dt><dd data-tracking-field="origin"></dd></div>
                <div><dt>Destination</dt><dd data-tracking-field="destination"></dd></div>
                <div><dt>Transport mode</dt><dd data-tracking-field="transport_mode"></dd></div>
                <div><dt>Last updated</dt><dd data-tracking-field="last_updated"></dd></div>
            </dl>
            <p class="bex-tracking-partial" hidden>Some shipment updates are temporarily unavailable. The timeline may be incomplete.</p>
            <p class="bex-tracking-empty" hidden>No tracking events are available yet.</p>
            <h3>Tracking timeline</h3>
            <ol class="bex-tracking-timeline"></ol>
        </section>
        <template id="bex-tracking-event-template"><li><time></time><h4></h4><p data-event-location></p><p data-event-description></p></li></template>
        <template data-tracking-error="invalid_number">Invalid tracking number. Please check your shipment reference and try again.</template>
        <template data-tracking-error="not_found">We couldn't find tracking information for this reference yet. Please check the number or contact BEXSTAR.</template>
        <template data-tracking-error="unavailable">Tracking information is temporarily unavailable. Please try again later.</template>
        <template data-tracking-error="provider_unavailable">Tracking information is temporarily unavailable. Please try again later or contact BEXSTAR.</template>
    </div>
    <?php return ob_get_clean();
}
