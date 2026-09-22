# Phase 2.1 media manifest
The final photographs, MP4s and approved logos remain to be supplied.
No DSV/Amazon/third-party media has been included. Seven original category illustrations under assets/images/placeholders/ are temporary
presentation fallbacks, not photographs, company facilities or permanent brand artwork.
All media labels are OFF by default. Local/development mode plus an explicit filter opt-in
is required to show filenames/dimensions on the frontend.

| Slot / section | Filename / path | Recommended px | Source ratio | Desktop focal point | Mobile focal point / ratio | Purpose |
| --- | --- | --- | --- | --- | --- | --- |
| hero / Home hero | assets/images/home/hero-poster.webp | 1920 × 1080 | 1920:1080 | 50% 50% | 50% 50%; 4:5 | Lead ocean/global-logistics visual |
| sea-freight / Services | assets/images/services/sea-freight.webp | 1200 × 800 | 1200:800 | 50% 50% | 50% 50%; 3:2 | Services editorial context |
| air-freight / Services | assets/images/services/air-freight.webp | 1200 × 800 | 1200:800 | 50% 50% | 50% 50%; 3:2 | Services editorial context |
| rail-truck / Services | assets/images/services/rail-truck.webp | 1200 × 800 | 1200:800 | 50% 50% | 50% 50%; 3:2 | Services editorial context |
| express / Services | assets/images/services/express.webp | 1200 × 800 | 1200:800 | 50% 50% | 50% 50%; 3:2 | Services editorial context |
| usa / Destinations | assets/images/destinations/usa.webp | 1200 × 900 | 1200:900 | 50% 50% | 50% 50%; 4:3 | Destinations editorial context |
| europe / Destinations | assets/images/destinations/europe.webp | 1200 × 900 | 1200:900 | 50% 50% | 50% 50%; 4:3 | Destinations editorial context |
| uk / Destinations | assets/images/destinations/uk.webp | 1200 × 900 | 1200:900 | 50% 50% | 50% 50%; 4:3 | Destinations editorial context |
| canada / Destinations | assets/images/destinations/canada.webp | 1200 × 900 | 1200:900 | 50% 50% | 50% 50%; 4:3 | Destinations editorial context |
| australia / Destinations | assets/images/destinations/australia.webp | 1200 × 900 | 1200:900 | 50% 50% | 50% 50%; 4:3 | Destinations editorial context |
| middle-east / Destinations | assets/images/destinations/middle-east.webp | 1200 × 900 | 1200:900 | 50% 50% | 50% 50%; 4:3 | Destinations editorial context |
| sourcing / Sourcing | assets/images/sourcing/sourcing-main.webp | 1440 × 960 | 1440:960 | 50% 50% | 50% 50%; 4:5 | Sourcing editorial context |
| supply-chain / Supply chain | assets/images/supply-chain/supply-chain-main.webp | 1440 × 960 | 1440:960 | 50% 50% | 50% 50%; 4:5 | Supply chain editorial context |
| fba / Amazon FBA | assets/images/fba/amazon-fba-main.webp | 1440 × 960 | 1440:960 | 50% 50% | 50% 50%; 4:5 | Amazon FBA editorial context |
| fba-video / Future FBA story | assets/images/fba/amazon-fba-poster.webp | 1920 × 1080 | 1920:1080 | 50% 50% | 50% 50%; 4:5 | Future FBA story editorial context |
| port / Media break | assets/images/home/port-video-poster.webp | 2100 × 900 | 2100:900 | 50% 50% | 50% 50%; 4:5 | Cinematic visual pause |
| ecommerce / Industries | assets/images/industries/ecommerce.webp | 1200 × 900 | 1200:900 | 50% 50% | 50% 50%; 4:3 | Industries editorial context |
| amazon-sellers / Industries | assets/images/industries/amazon-sellers.webp | 1200 × 900 | 1200:900 | 50% 50% | 50% 50%; 4:3 | Industries editorial context |
| importers / Industries | assets/images/industries/importers.webp | 1200 × 900 | 1200:900 | 50% 50% | 50% 50%; 4:3 | Industries editorial context |
| wholesalers / Industries | assets/images/industries/wholesalers.webp | 1200 × 900 | 1200:900 | 50% 50% | 50% 50%; 4:3 | Industries editorial context |
| retailers / Industries | assets/images/industries/retailers.webp | 1200 × 900 | 1200:900 | 50% 50% | 50% 50%; 4:3 | Industries editorial context |
| manufacturers / Industries | assets/images/industries/manufacturers.webp | 1200 × 900 | 1200:900 | 50% 50% | 50% 50%; 4:3 | Industries editorial context |
| case-study / Case study | assets/images/cases/case-study.webp | 1600 × 1000 | 1600:1000 | 50% 50% | 50% 50%; 4:3 | Case study editorial context |
| final-cta / Final CTA | assets/images/home/final-cta.webp | 1920 × 1080 | 1920:1080 | 50% 50% | 50% 50%; 4:5 | Closing conversion backdrop |

## Priority and composition
1. Hero poster: licensed container-vessel/ocean visual. Keep readable low-detail space at
   left and main subject toward the right. Desktop hero is fluid-height full bleed.
2. Sea/Air service images, port poster, sourcing/supply-chain/FBA editorial imagery.
3. USA/Europe lead visuals, then the remaining destinations, industries, real cases and CTA.
4. Videos are optional: images and the neutral fallback must remain useful without them.

Hero video: assets/video/hero-video.mp4 — pair with hero-poster.webp.
Port video: assets/video/port-video.mp4 — pair with port-video-poster.webp.
FBA video: assets/video/amazon-fba-video.mp4 — pair with amazon-fba-poster.webp.
The FBA slot prefers its dedicated poster when supplied, otherwise amazon-fba-main.webp.
Media Library selection overrides bundled files. Video requires a valid poster path;
there is no autoplay and no video fetch before a visitor presses Play.
Use captioned core Video blocks for essential spoken/informational content.

Default focal points are recommendations, not approved crops. Inspect each real asset.
Layout may override the source ratio: USA/Europe and leading industries use 16:9 on desktop,
FBA uses 4:5, the port break uses 21:9, and hero/final CTA fill their container.
Responsive attachment markup remains WordPress-owned; theme files are a developer fallback.

## Brand assets
assets/images/brand/bexstar-logo.svg
assets/images/brand/bexstar-logo-dark.svg
assets/images/brand/bexstar-logo-light.svg

Recommended SVG viewBox ratio approximately 200:48, preserving the actual approved logo.
No new logo has been invented. Header: WordPress Custom Logo → dark SVG → generic SVG →
text. Footer: light SVG → text (never assume a generic dark mark works on navy).
Supply only trusted, reviewed SVG files; the theme does not enable arbitrary SVG uploads.

## Phase 2.1 priority assets and crop guidance
All files below are still required from BEXSTAR or an approved licensed source.
Keep original masters separately; deliver optimized WebP posters first, optional MP4 second.
Never source DSV, Maersk, UPS or Amazon media/logos. No stock library was downloaded.

| Section | Filename | Recommended dimensions | Ratio | Desktop crop | Mobile crop |
| --- | --- | --- | --- | --- | --- |
| Header/general brand | assets/images/brand/bexstar-logo.svg | SVG viewBox about 200 × 48 | Approved logo ratio | Entire logo, transparent margins | Entire logo; never crop |
| Header dark logo | assets/images/brand/bexstar-logo-dark.svg | SVG viewBox about 200 × 48 | Approved logo ratio | Entire dark mark on white | Entire mark |
| Footer light logo | assets/images/brand/bexstar-logo-light.svg | SVG viewBox about 200 × 48 | Approved logo ratio | Entire light mark on navy | Entire mark |
| Hero poster | assets/images/home/hero-poster.webp | 1920 × 1080 | 16:9 | Vessel/right, ocean/quiet left for type; fills 560–660 px hero | Keep vessel in central 4:5 crop; inspect actual asset |
| Hero video, optional | assets/video/hero-video.mp4 | 1920 × 1080 | 16:9 | Match poster and avoid text; no autoplay | Same safe central subject; poster first |
| Shipping menu | assets/images/services/shipping-menu.webp | 1200 × 750 | 8:5 | Cargo/port scene, centered, no logos | Hidden in navigation; central crop if reused |
| Sourcing menu | assets/images/sourcing/sourcing-menu.webp | 1200 × 750 | 8:5 | Supplier/inspection subject, center safe | Hidden in navigation; central crop if reused |
| Supply Chain menu | assets/images/supply-chain/supply-chain-menu.webp | 1200 × 750 | 8:5 | Warehouse/coordination scene, center safe | Hidden in navigation; central crop if reused |
| FBA menu | assets/images/fba/fba-menu.webp | 1200 × 750 | 8:5 | Unbranded parcels/warehouse, no Amazon logo | Hidden in navigation; central crop if reused |
| Resources menu | assets/images/resources/resources-menu.webp | 1200 × 750 | 8:5 | Planning/knowledge scene; no legible sensitive documents | Hidden in navigation; central crop if reused |
| About menu | assets/images/about/about-menu.webp | 1200 × 750 | 8:5 | Approved company/team context; no invented facility | Hidden in navigation; central crop if reused |
| Industries menu | assets/images/industries/industries-menu.webp | 1200 × 750 | 8:5 | Product/business context, center safe | Hidden in navigation; central crop if reused |
| More than freight | assets/images/home/more-than-freight.webp | 1600 × 1000 | 8:5 | Sourcing/logistics coordination, subject clear in right column | Central subject; section keeps 8:5, standalone slot 4:3 |

Eight new slots bring the manifest to 32. Shipping retains the logistics fallback;
the other six menus have their own category illustrations. Introduction uses the sourcing
illustration, with sourcing-main.webp as its optional real-image fallback.
The navigation block's Gutenberg inspector can select a Media Library image separately
for each of the seven menus; each selection overrides that menu's theme file.
Those controls apply to the default V3 navigation, not an assigned custom WordPress menu.
