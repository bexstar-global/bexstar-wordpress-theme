# DSV architecture analysis — Phase 1
Inspection date: 2026-09-21 (UTC). Method: live public page text and link extraction, not recollection.
This is a structural analysis, NOT a completed visual/interaction audit.

## Verified homepage structure
Source: https://www.dsv.com/en

The global navigation separates solutions, insights, corporate subjects and self-service.
Solutions groups industries, transport modes, supply-chain services and additional services.
Insights combines expert content, cases, advisories and a glossary. Self-service separates
support from shipment actions. Search, login, locale, booking and tracking are distinct utilities.
The extracted homepage has a video-capable hero, customer actions, digital tools, industry
content, numerical proof, news, testimonials and learning links. The footer repeats tools,
solutions, company and legal groups. A second nested navigation representation appears
in the extracted page, but its responsive visibility and animation were not verified.

## Service depth
Source: https://www.dsv.com/en/our-solutions/modes-of-transport/air-freight

The service page has its own headline, product-level links, benefit explanations and
supporting service categories. This supports a hub → service → specific offering hierarchy,
rather than one undifferentiated services page. Images appear alongside explanatory content.

## Case-study structure
Source: https://www.dsv.com/en/insights/case-studies

The case-study hub introduces customer stories and leads toward contact. The extraction
does not expose enough listing items to establish filtering, pagination or case fields;
these are not claimed as observed behavior.

## Digital-service structure
Source: https://www.dsv.com/en/digital-solutions

A shared hub introduces digital logistics, then separates shipment management, inventory
visibility, estimated arrival tools and API/EDI integration. Product entries lead to deeper
explanations. BEXSTAR should distinguish tool entry points from actual connected services.

## Contact architecture
Source: https://www.dsv.com/en/support/help-and-contact

Contact begins with task-based help: tracking, locations, quotation and self-service,
alongside other audience needs. BEXSTAR should route inquiries by purpose, without
inventing global offices, careers departments or support channels.

## BEXSTAR implementation decisions, not measured DSV values
- Two-tier desktop header, seven BEXSTAR primary groups, separate customer actions.
- Shipping disclosure: three information columns and one featured overview column.
- Native details/summary and ordinary links; no inappropriate application-menu roles.
- Content width 760px, wide width 1280px; system font; 16/9 hero, 3/2 editorial,
  4/3 destination, 16/10 case and 21/9 wide-media starting ratios.
- 1100px desktop-navigation threshold; stacked accordion on narrower screens.
- Mobile uses single-column content below 700px, explicit focal points and generous touch targets.
- Keep V3 homepage ordering. Do not import DSV statistics, customer statements or corporate topics.
- No DSV source, branding, wording, images, video or icons are included in theme assets.

## Unverified reference details / visual follow-up
Exact desktop content widths, font measurements, image crops, dropdown coordinates,
sticky-header behavior, hover delays, tablet transitions, keyboard handling, mobile back
behavior and touch interactions require a rendered DSV browser audit. None are certified
by text extraction. Phase 2 must include that audit before any high-fidelity claim.
