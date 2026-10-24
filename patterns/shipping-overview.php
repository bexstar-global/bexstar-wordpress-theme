<?php
/**
 * Title: Shipping overview — complete page
 * Slug: bexstar/shipping-overview
 * Categories: bexstar
 * Description: Image-led, editable overview of live China shipping destinations and transport options.
 * Inserter: yes
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
?>
<!-- wp:group {"tagName":"div","className":"bex-ship-page","layout":{"type":"default"}} -->
<div class="wp-block-group bex-ship-page">
<!-- wp:group {"tagName":"section","className":"bex-section bex-ship-hero","layout":{"type":"default"}} -->
<section class="wp-block-group bex-section bex-ship-hero">
<!-- wp:bexstar/media {"slot":"shipping-hero","decorative":true} /-->
<!-- wp:group {"tagName":"div","className":"bex-ship-hero-copy","layout":{"type":"default"}} --><div class="wp-block-group bex-ship-hero-copy">
<!-- wp:paragraph {"className":"bex-eyebrow"} --><p class="bex-eyebrow">SHIPPING FROM CHINA</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":1} --><h1 class="wp-block-heading">Built around the destination.</h1><!-- /wp:heading -->
<!-- wp:paragraph {"className":"bex-lead"} --><p class="bex-lead">Explore the route, the mode and the receiving point in one place. BEXSTAR helps turn cargo details into a workable delivery plan.</p><!-- /wp:paragraph -->
<!-- wp:buttons --><div class="wp-block-buttons"><!-- wp:button --><div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="/get-a-quote/">PLAN MY SHIPMENT</a></div><!-- /wp:button --><!-- wp:button {"className":"is-style-outline"} --><div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="#shipping-destinations">EXPLORE DESTINATIONS</a></div><!-- /wp:button --></div><!-- /wp:buttons -->
<!-- wp:paragraph {"className":"bex-ship-mode-line"} --><p class="bex-ship-mode-line">SEA &nbsp;·&nbsp; AIR &nbsp;·&nbsp; RAIL &amp; ROAD &nbsp;·&nbsp; EXPRESS</p><!-- /wp:paragraph -->
</div><!-- /wp:group -->
</section><!-- /wp:group -->

<!-- wp:group {"tagName":"section","className":"bex-section bex-ship-proof","layout":{"type":"default"}} --><section class="wp-block-group bex-section bex-ship-proof">
<!-- wp:group {"tagName":"div","layout":{"type":"default"}} --><div class="wp-block-group"><!-- wp:paragraph --><p><strong>Destination</strong><span>Country, postcode and receiving rules.</span></p><!-- /wp:paragraph --></div><!-- /wp:group -->
<!-- wp:group {"tagName":"div","layout":{"type":"default"}} --><div class="wp-block-group"><!-- wp:paragraph --><p><strong>Cargo</strong><span>Goods, dimensions and readiness.</span></p><!-- /wp:paragraph --></div><!-- /wp:group -->
<!-- wp:group {"tagName":"div","layout":{"type":"default"}} --><div class="wp-block-group"><!-- wp:paragraph --><p><strong>Delivery</strong><span>FBA, warehouse or business address.</span></p><!-- /wp:paragraph --></div><!-- /wp:group -->
</section><!-- /wp:group -->

<!-- wp:group {"tagName":"section","anchor":"shipping-destinations","className":"bex-section bex-ship-destinations","layout":{"type":"default"}} --><section id="shipping-destinations" class="wp-block-group bex-section bex-ship-destinations">
<!-- wp:paragraph {"className":"bex-eyebrow"} --><p class="bex-eyebrow">CHOOSE YOUR MARKET</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":2} --><h2 class="wp-block-heading">Start with where the cargo needs to arrive.</h2><!-- /wp:heading -->
<!-- wp:paragraph {"className":"bex-ship-intro"} --><p class="bex-ship-intro">Each destination has its own cargo, customs and final-delivery requirements. Explore the route, then tell us what you are moving.</p><!-- /wp:paragraph -->
<!-- wp:group {"tagName":"div","className":"bex-ship-route-grid","layout":{"type":"default"}} --><div class="wp-block-group bex-ship-route-grid">
<!-- wp:group {"tagName":"article","className":"bex-ship-route","layout":{"type":"default"}} --><article class="wp-block-group bex-ship-route">
<!-- wp:bexstar/media {"slot":"shipping-uk","decorative":false,"alt":"Freight being prepared for delivery at a UK warehouse"} /-->
<!-- wp:group {"tagName":"div","className":"bex-ship-route-copy","layout":{"type":"default"}} --><div class="wp-block-group bex-ship-route-copy">
<!-- wp:paragraph {"className":"bex-eyebrow"} --><p class="bex-eyebrow">01 / UNITED KINGDOM</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3 class="wp-block-heading">China to UK</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Sea, air, rail and road DDP options for eligible shipments.</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p><a href="/shipping/china-to-uk/">EXPLORE UK SHIPPING <span aria-hidden="true">↗</span></a></p><!-- /wp:paragraph -->
</div><!-- /wp:group -->
</article><!-- /wp:group -->
<!-- wp:group {"tagName":"article","className":"bex-ship-route","layout":{"type":"default"}} --><article class="wp-block-group bex-ship-route">
<!-- wp:bexstar/media {"slot":"shipping-usa","decorative":false,"alt":"Freight truck leaving a United States intermodal port"} /-->
<!-- wp:group {"tagName":"div","className":"bex-ship-route-copy","layout":{"type":"default"}} --><div class="wp-block-group bex-ship-route-copy">
<!-- wp:paragraph {"className":"bex-eyebrow"} --><p class="bex-eyebrow">02 / UNITED STATES</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3 class="wp-block-heading">China to USA</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Air, express and ocean routes, with FBA and selected truck delivery.</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p><a href="/shipping/china-to-usa/">EXPLORE USA SHIPPING <span aria-hidden="true">↗</span></a></p><!-- /wp:paragraph -->
</div><!-- /wp:group -->
</article><!-- /wp:group -->
<!-- wp:group {"tagName":"article","className":"bex-ship-route","layout":{"type":"default"}} --><article class="wp-block-group bex-ship-route">
<!-- wp:bexstar/media {"slot":"shipping-europe","decorative":false,"alt":"Containers transferred between rail and road at a European freight terminal"} /-->
<!-- wp:group {"tagName":"div","className":"bex-ship-route-copy","layout":{"type":"default"}} --><div class="wp-block-group bex-ship-route-copy">
<!-- wp:paragraph {"className":"bex-eyebrow"} --><p class="bex-eyebrow">03 / EUROPE</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3 class="wp-block-heading">China to Europe</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Air, sea, rail and road DDP parcels across eligible destinations.</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p><a href="/shipping/china-to-europe/">EXPLORE EUROPE SHIPPING <span aria-hidden="true">↗</span></a></p><!-- /wp:paragraph -->
</div><!-- /wp:group -->
</article><!-- /wp:group -->
<!-- wp:group {"tagName":"article","className":"bex-ship-route","layout":{"type":"default"}} --><article class="wp-block-group bex-ship-route">
<!-- wp:bexstar/media {"slot":"shipping-australia","decorative":false,"alt":"Palletized freight loaded for final delivery at an Australian warehouse"} /-->
<!-- wp:group {"tagName":"div","className":"bex-ship-route-copy","layout":{"type":"default"}} --><div class="wp-block-group bex-ship-route-copy">
<!-- wp:paragraph {"className":"bex-eyebrow"} --><p class="bex-eyebrow">04 / AUSTRALIA</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3 class="wp-block-heading">China to Australia</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Air and sea freight, with parcel, FBA and selected truck delivery.</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p><a href="/shipping/china-to-australia/">EXPLORE AUSTRALIA SHIPPING <span aria-hidden="true">↗</span></a></p><!-- /wp:paragraph -->
</div><!-- /wp:group -->
</article><!-- /wp:group -->
</div><!-- /wp:group -->
</section><!-- /wp:group -->

<!-- wp:group {"tagName":"section","className":"bex-section bex-ship-modes","layout":{"type":"default"}} --><section class="wp-block-group bex-section bex-ship-modes">
<!-- wp:group {"tagName":"div","className":"bex-ship-modes-intro","layout":{"type":"default"}} --><div class="wp-block-group bex-ship-modes-intro">
<!-- wp:paragraph {"className":"bex-eyebrow"} --><p class="bex-eyebrow">A ROUTE THAT FITS</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":2} --><h2 class="wp-block-heading">One shipment. Several decisions.</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>Mode, delivery point and cargo eligibility are reviewed together before a tailored quotation.</p><!-- /wp:paragraph -->
</div><!-- /wp:group -->
<!-- wp:group {"tagName":"div","className":"bex-ship-mode-grid","layout":{"type":"default"}} --><div class="wp-block-group bex-ship-mode-grid">
<!-- wp:group {"tagName":"div","layout":{"type":"default"}} --><div class="wp-block-group"><!-- wp:paragraph {"className":"bex-eyebrow"} --><p class="bex-eyebrow">01 / SEA</p><!-- /wp:paragraph --><!-- wp:heading {"level":3} --><h3 class="wp-block-heading">Plan for volume.</h3><!-- /wp:heading --><!-- wp:paragraph --><p>For planned inventory, pallets and larger shipments.</p><!-- /wp:paragraph --></div><!-- /wp:group -->
<!-- wp:group {"tagName":"div","layout":{"type":"default"}} --><div class="wp-block-group"><!-- wp:paragraph {"className":"bex-eyebrow"} --><p class="bex-eyebrow">02 / AIR</p><!-- /wp:paragraph --><!-- wp:heading {"level":3} --><h3 class="wp-block-heading">Move what matters now.</h3><!-- /wp:heading --><!-- wp:paragraph --><p>For time-sensitive stock and eligible parcels.</p><!-- /wp:paragraph --></div><!-- /wp:group -->
<!-- wp:group {"tagName":"div","layout":{"type":"default"}} --><div class="wp-block-group"><!-- wp:paragraph {"className":"bex-eyebrow"} --><p class="bex-eyebrow">03 / RAIL &amp; ROAD</p><!-- /wp:paragraph --><!-- wp:heading {"level":3} --><h3 class="wp-block-heading">Connect the land route.</h3><!-- /wp:heading --><!-- wp:paragraph --><p>Overland options where the cargo and destination fit.</p><!-- /wp:paragraph --></div><!-- /wp:group -->
<!-- wp:group {"tagName":"div","layout":{"type":"default"}} --><div class="wp-block-group"><!-- wp:paragraph {"className":"bex-eyebrow"} --><p class="bex-eyebrow">04 / EXPRESS</p><!-- /wp:paragraph --><!-- wp:heading {"level":3} --><h3 class="wp-block-heading">Keep samples moving.</h3><!-- /wp:heading --><!-- wp:paragraph --><p>Ask about eligible small packages and sample shipments.</p><!-- /wp:paragraph --></div><!-- /wp:group -->
</div><!-- /wp:group -->
<!-- wp:paragraph {"className":"bex-ship-availability"} --><p class="bex-ship-availability">Service availability, customs responsibilities and final-mile scope are confirmed for each shipment.</p><!-- /wp:paragraph -->
</section><!-- /wp:group -->

<!-- wp:group {"tagName":"section","className":"bex-section bex-ship-close","layout":{"type":"default"}} --><section class="wp-block-group bex-section bex-ship-close">
<!-- wp:paragraph {"className":"bex-eyebrow"} --><p class="bex-eyebrow">START THE CONVERSATION</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":2} --><h2 class="wp-block-heading">Tell us where it needs to go.</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>Share the destination, cargo and receiving point. We will review suitable options and respond with a tailored plan.</p><!-- /wp:paragraph -->
<!-- wp:buttons --><div class="wp-block-buttons"><!-- wp:button --><div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="/get-a-quote/">REQUEST A TAILORED QUOTE</a></div><!-- /wp:button --></div><!-- /wp:buttons -->
<!-- wp:paragraph --><p>Or <a href="/contact/">contact BEXSTAR</a> directly.</p><!-- /wp:paragraph -->
</section><!-- /wp:group -->
</div><!-- /wp:group -->
