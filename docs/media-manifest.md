# Phase 2 media manifest
The final photographs, MP4s and approved logos remain to be supplied.
No DSV/Amazon/third-party media has been included. One original neutral logistics SVG
under assets/images/placeholders/ is a presentation fallback, not permanent brand artwork.
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
