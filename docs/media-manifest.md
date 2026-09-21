# Media replacement plan
No stock, DSV, Amazon or third-party media is bundled. Missing assets use an original,
neutral BEXSTAR placeholder generated as HTML/CSS; no fake .webp or .mp4 files are supplied.

| Slot | Image/poster path | Recommended px | Desktop ratio | Mobile ratio |
| --- | --- | --- | --- | --- |
| hero | assets/images/home/hero-poster.webp | 1920 × 1080 | 1920:1080 | 4:5 |
| sea-freight | assets/images/services/sea-freight.webp | 1200 × 800 | 1200:800 | 3:2 |
| air-freight | assets/images/services/air-freight.webp | 1200 × 800 | 1200:800 | 3:2 |
| rail-truck | assets/images/services/rail-truck.webp | 1200 × 800 | 1200:800 | 3:2 |
| express | assets/images/services/express.webp | 1200 × 800 | 1200:800 | 3:2 |
| usa | assets/images/destinations/usa.webp | 1200 × 900 | 1200:900 | 4:3 |
| europe | assets/images/destinations/europe.webp | 1200 × 900 | 1200:900 | 4:3 |
| uk | assets/images/destinations/uk.webp | 1200 × 900 | 1200:900 | 4:3 |
| canada | assets/images/destinations/canada.webp | 1200 × 900 | 1200:900 | 4:3 |
| australia | assets/images/destinations/australia.webp | 1200 × 900 | 1200:900 | 4:3 |
| middle-east | assets/images/destinations/middle-east.webp | 1200 × 900 | 1200:900 | 4:3 |
| sourcing | assets/images/sourcing/sourcing-main.webp | 1440 × 960 | 1440:960 | 4:5 |
| supply-chain | assets/images/supply-chain/supply-chain-main.webp | 1440 × 960 | 1440:960 | 4:5 |
| fba | assets/images/fba/amazon-fba-main.webp | 1440 × 960 | 1440:960 | 4:5 |
| fba-video | assets/images/fba/amazon-fba-poster.webp | 1920 × 1080 | 1920:1080 | 4:5 |
| port | assets/images/home/port-video-poster.webp | 2100 × 900 | 2100:900 | 4:5 |
| ecommerce | assets/images/industries/ecommerce.webp | 1200 × 900 | 1200:900 | 4:3 |
| amazon-sellers | assets/images/industries/amazon-sellers.webp | 1200 × 900 | 1200:900 | 4:3 |
| importers | assets/images/industries/importers.webp | 1200 × 900 | 1200:900 | 4:3 |
| wholesalers | assets/images/industries/wholesalers.webp | 1200 × 900 | 1200:900 | 4:3 |
| retailers | assets/images/industries/retailers.webp | 1200 × 900 | 1200:900 | 4:3 |
| manufacturers | assets/images/industries/manufacturers.webp | 1200 × 900 | 1200:900 | 4:3 |
| case-study | assets/images/cases/case-study.webp | 1600 × 1000 | 1600:1000 | 4:3 |
| final-cta | assets/images/home/final-cta.webp | 1920 × 1080 | 1920:1080 | 4:5 |

Video paths: assets/video/hero-video.mp4, assets/video/port-video.mp4,
assets/video/amazon-fba-video.mp4. Supply compressed silent MP4s with licensed posters.
The FBA video slot is reserved, not loaded on the Phase 1 homepage.
Every manifest entry records section, desktop and mobile focal point. Default is centered;
inspect actual subjects before accepting crops. Image and video remain object-fit: cover.
Slots reserve aspect ratios; failed image/video retains the underlying neutral fallback.
Hero has a minimum height instead of forcing a 16/9 ratio across all viewports.
Use Media Library attachments in production for generated srcset/sizes.
No automatic network media downloads, external fonts or autoplay.
