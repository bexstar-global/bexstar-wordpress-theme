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
<!-- wp:group {"tagName":"div","className":"bex-actions","layout":{"type":"default"}} -->
<div class="wp-block-group bex-actions">
<!-- wp:paragraph --><p><a href="<?php echo esc_url( bexstar_section_url( 'get-a-quote', 'contact' ) ); ?>">Get a Quote <span aria-hidden="true">↗</span></a></p><!-- /wp:paragraph --><!-- wp:paragraph --><p><a href="<?php echo esc_url( bexstar_section_url( 'track', 'digital-logistics' ) ); ?>">Track Shipment <span aria-hidden="true">↗</span></a></p><!-- /wp:paragraph --><!-- wp:paragraph --><p><a href="<?php echo esc_url( bexstar_section_url( 'contact', 'contact' ) ); ?>">Contact BEXSTAR <span aria-hidden="true">↗</span></a></p><!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
</section>
<!-- /wp:group -->
