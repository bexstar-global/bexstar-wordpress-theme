<?php
/**
 * Title: Article enquiry CTA
 * Slug: bexstar/article-cta
 * Categories: bexstar
 * Inserter: yes
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
?>
<!-- wp:group {"tagName":"div","className":"bex-section bex-cta","layout":{"type":"default"}} -->
<div class="wp-block-group bex-section bex-cta">
<!-- wp:heading {"level":2} -->
<h2 class="wp-block-heading">Planning a shipment?</h2>
<!-- /wp:heading --><!-- wp:paragraph -->
<p>Have your destination, package count, dimensions and weight ready.</p>
<!-- /wp:paragraph --><!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button --><div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( bexstar_section_url( 'get-a-quote', 'contact' ) ); ?>">Get a Quote</a></div><!-- /wp:button --></div>
<!-- /wp:buttons -->
</div>
<!-- /wp:group -->
