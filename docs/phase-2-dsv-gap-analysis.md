# Phase 2 — DSV / BEXSTAR desktop gap analysis
Prepared before design changes. Baseline: 655de46. Date: 2026-09-21 UTC.
User confirms Phase 1 activation, homepage, header and click-open Shipping/Sourcing/Supply
Chain menus work in an isolated Hostinger development installation. These are user-reported
acceptance results, not new tests performed by this session.

## Evidence and limits
Current source revisited: https://www.dsv.com/en .
The extracted page verifies grouped solutions navigation, separate utility/customer actions,
a video-capable hero, digital tools, industry imagery, editorial content and a grouped footer.
It does not establish exact pixel dimensions, font metrics, menu hover delays or breakpoint
behavior. Those are unverified; no DSV dimensions or interactions are invented here.
BEXSTAR measurements below come from repository CSS. Phase 2 targets are independent design
decisions. No DSV copy, code or assets are incorporated.

## Gap matrix
| Area | Current BEXSTAR | Current DSV structural / UX pattern or evidence limit | Phase 2 action |
| --- | --- | --- | --- |
| Header | Three stacked layers; oversized logo/action row | Navigation and utility responsibilities are separated | Two compact desktop rows, shared baseline/grid |
| Utility | Contact + static English | Search/login/locale separate from service browsing | Keep Contact/English; reserve real locale integration, no fake selector |
| Main navigation | Seven uppercase groups spread across full width | Grouped service browsing | Compact aligned row, consistent open/hover/focus states |
| Mega menus | Four equal columns used for every group | Solutions grouped by customer task | Shipping four columns; lean groups three-part editorial composition |
| Hero | Flat fallback with extra brand text, 17ch H1 | Video-capable leading media and customer action hierarchy | Full-bleed quiet media, two intentional headline lines, strong contrast |
| Typography | 80px display with arbitrary wrap | Exact reference scale unverified | Fluid 48–72px desktop display; 36–48px section title; 16–18px body |
| Container | 1280px max with additional interior padding | Exact reference width unverified | 1440px wide shell, explicit gutters and 680px reading measure |
| Spacing | Uniform section padding, repetitive gaps | Exact reference spacing unverified | Tokenized 64–112px section rhythm; compact action band |
| Media | Textual development fallbacks | Prominent visual content, including video | Original neutral graphic fallback, labels OFF by default |
| Services | Four equal tiles and two text links | Service and solution categories are distinct | Lead Sea/Air features; compact secondary service rows |
| Destinations | Six identical tiles | Destination links occur in solution taxonomy | Asymmetric 2-feature / 4-secondary visual grid |
| Sourcing | Same split as next two sections | Reference supply-chain grouping exists; exact equivalent unverified | Media-left editorial; concise scope list |
| Supply chain | Same split, arrow text line | Reference separates supply-chain solutions | Reverse split; numbered, wrapping process strip |
| Amazon FBA | Same split with short paragraph | BEXSTAR-specific service, no direct DSV match asserted | Larger media with video-ready slot and service links |
| Industries | Six similar small tiles | Industry imagery and headings visible | Open editorial grid with wider leading entries |
| Digital logistics | Four equal developer-explained boxes | Separate digital tools / self-service hierarchy | Quote/Track emphasized; honest Coming soon status for unavailable tools |
| Case studies | Developer explanation + fake-looking empty slot | Case-study entry exists in insights | Conditional real-post query; conservative empty state |
| CTA | Final section has no actionable primary button | Repeated customer-action entry points | Two clear actions, safe unavailable state when no real page exists |
| Footer | Five columns + development/legal notes | Tools, solutions, company, legal groups | Dense typographic columns, separate brand/legal line, only real legal links |

## Interaction decision
Retain native click disclosures as the baseline. Add fine-pointer desktop hover intent
(160ms open / 220ms close), keyboard Enter/Space, Escape focus restoration and outside/focus-leave
closing. This is an accessibility-led BEXSTAR choice, not an assertion that DSV uses these timings.
Do not auto-open merely on keyboard focus: Tab should not trap visitors in every panel.

## Scope / release gate
Do not create Phase 3 route pages or APIs. Keep native content/pattern editability.
No manual Hostinger deployment, production access or DNS operations.
Saved WordPress template overrides can mask changed pattern files; document how to inspect them,
never reset live editor content automatically. New visual acceptance requires the development site.
