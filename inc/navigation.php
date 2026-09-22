<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/** Until a service Page is published, link to its relevant homepage overview. */
function bexstar_nav_link( $label, $path ) {
    $page = get_page_by_path( trim( $path, '/' ) );
    if ( $page && 'publish' === get_post_status( $page ) ) {
        return '<a href="' . esc_url( get_permalink( $page ) ) . '">' . esc_html( $label ) . '</a>';
    }
    $root = explode( '/', trim( $path, '/' ) )[0];
    $anchor = 'about-us' === $root ? 'introduction' : $root;
    if ( false !== strpos( $path, 'china-to-' ) ) { $anchor = 'destinations'; }
    return '<a href="' . esc_url( home_url( '/#' . sanitize_title( $anchor ) ) ) . '">' . esc_html( $label ) . '</a>';
}

function bexstar_nav_leaves( $items ) {
    $html = '<ul>';
    foreach ( $items as $item ) {
        $html .= '<li>';
        if ( ! empty( $item[2] ) ) {
            $html .= '<details class="bex-subnav"><summary>' . esc_html( $item[0] ) . '</summary>';
            $html .= bexstar_nav_link( __( 'Overview', 'bexstar' ), $item[1] );
            $html .= bexstar_nav_leaves( $item[2] ) . '</details>';
        } else {
            $html .= bexstar_nav_link( $item[0], $item[1] );
        }
        $html .= '</li>';
    }
    return $html . '</ul>';
}

/** Native Appearance > Menus content, with bounded recursion for malformed imports. */
function bexstar_custom_menu( $items, $parent = 0, $depth = 0 ) {
    if ( $depth > 5 ) { return ''; }
    $html = '';
    foreach ( $items as $item ) {
        if ( (int) $item->menu_item_parent !== (int) $parent ) { continue; }
        $children = bexstar_custom_menu( $items, $item->ID, $depth + 1 );
        $link = '<a href="' . esc_url( $item->url ) . '">' . esc_html( $item->title ) . '</a>';
        $html .= '<li>';
        if ( $children ) {
            $html .= '<details class="' . ( 0 === $depth ? 'bex-mega' : 'bex-subnav' ) . '"><summary>' . esc_html( $item->title ) . '</summary>';
            $html .= '<div class="' . ( 0 === $depth ? 'bex-panel' : 'bex-subpanel' ) . '">' . $link . '<ul>' . $children . '</ul></div></details>';
        } else {
            $html .= $link;
        }
        $html .= '</li>';
    }
    return $html;
}

function bexstar_render_navigation() {
    $id = wp_unique_id( 'bex-navigation-' );
    $locations = get_nav_menu_locations();
    $items = ! empty( $locations['bexstar-primary'] ) ? wp_get_nav_menu_items( $locations['bexstar-primary'] ) : false;
    ob_start();
    ?>
    <div class="bex-header" data-bex-header>
        <a class="bex-skip" href="#main"><?php esc_html_e( 'Skip to content', 'bexstar' ); ?></a>
        <div class="bex-utility">
            <a href="<?php echo esc_url( bexstar_section_url( 'contact', 'contact' ) ); ?>"><?php esc_html_e( 'Contact', 'bexstar' ); ?></a>
            <span lang="en" aria-label="<?php esc_attr_e( 'Current language: English.', 'bexstar' ); ?>">English</span>
        </div>
        <div class="bex-header-row">
            <a class="bex-wordmark" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php esc_attr_e( 'BEXSTAR home', 'bexstar' ); ?>"><?php echo bexstar_brand_markup(); ?></a>
            <div class="bex-header-actions">
                <a href="<?php echo esc_url( bexstar_section_url( 'track', 'digital-logistics' ) ); ?>"><?php esc_html_e( 'Track', 'bexstar' ); ?></a>
                <a class="bex-button" href="<?php echo esc_url( bexstar_section_url( 'get-a-quote', 'contact' ) ); ?>"><?php esc_html_e( 'Get a Quote', 'bexstar' ); ?></a>
            </div>
            <button class="bex-menu-toggle" hidden aria-expanded="false" aria-controls="<?php echo esc_attr( $id ); ?>"><?php esc_html_e( 'Menu', 'bexstar' ); ?></button>
        </div>
        <nav id="<?php echo esc_attr( $id ); ?>" class="bex-nav" aria-label="<?php esc_attr_e( 'Main navigation', 'bexstar' ); ?>">
            <ul class="bex-top">
            <?php if ( $items ) : ?>
                <?php echo bexstar_custom_menu( $items ); // All values escaped by renderer. ?>
            <?php else : ?>
                <?php $groups = json_decode( file_get_contents( __DIR__ . '/navigation.json' ), true ); ?>
                <?php foreach ( $groups as $group ) : ?>
                    <li><details class="bex-mega <?php echo count( $group[2] ) > 1 ? 'bex-mega-deep' : 'bex-mega-editorial'; ?>">
                        <summary><?php echo esc_html( $group[0] ); ?></summary>
                        <div class="bex-panel">
                            <?php foreach ( $group[2] as $column ) : ?>
                                <div class="bex-nav-column">
                                    <p class="bex-nav-heading"><?php echo esc_html( $column[0] ); ?></p>
                                    <?php echo bexstar_nav_leaves( $column[1] ); ?>
                                </div>
                            <?php endforeach; ?>
                            <?php
                            $features = array(
                                'shipping' => array( 'Move your cargo forward.', 'Compare transport options around your cargo and destination.', 'sea-freight' ),
                                'sourcing' => array( 'Start at the source.', 'Connect supplier coordination, inspection and consolidation in China.', 'sourcing' ),
                                'supply-chain' => array( 'Connect every stage.', 'Bring sourcing, storage, transport and delivery into one plan.', 'supply-chain' ),
                                'amazon-fba' => array( 'Plan your next shipment.', 'Explore transport and warehouse-delivery options for Amazon FBA.', 'fba' ),
                                'industries' => array( 'Built around your business.', 'Find a starting point for your supply and shipping needs.', 'importers' ),
                                'resources' => array( 'Make informed decisions.', 'Explore shipping knowledge and stories from real work.', 'case-study' ),
                                'about-us' => array( 'Beyond expectations.', 'China sourcing. Global supply chain. International logistics.', 'sourcing' ),
                            );
                            $feature = $features[ $group[1] ];
                            ?>
                            <div class="bex-nav-feature">
                                <p class="bex-eyebrow"><?php echo esc_html( $group[0] ); ?></p>
                                <p class="bex-nav-title"><?php echo esc_html( $feature[0] ); ?></p>
                                <p><?php echo esc_html( $feature[1] ); ?></p>
                                <a href="<?php echo esc_url( bexstar_section_url( $group[1], 'about-us' === $group[1] ? 'introduction' : $group[1] ) ); ?>"><?php echo esc_html( sprintf( __( 'Explore %s', 'bexstar' ), $group[0] ) ); ?> →</a>
                            </div>
                            <?php if ( 1 === count( $group[2] ) ) : ?>
                                <div class="bex-nav-media"><?php echo bexstar_render_media( array( 'slot' => $feature[2], 'decorative' => true ) ); ?></div>
                            <?php endif; ?>
                        </div>
                    </details></li>
                <?php endforeach; ?>
            <?php endif; ?>
            </ul>
        </nav>
    </div>
    <?php
    return ob_get_clean();
}
