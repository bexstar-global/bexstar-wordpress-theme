# BEXSTAR architecture map — V3 / Phase 1
The repository root is the theme root. No production WordPress installation is connected.

## Mapping decisions
| Reference structural concept | BEXSTAR area | Implementation / future owner |
| --- | --- | --- |
| Transport and solution hierarchy | Shipping; transport, destinations, solutions | Navigation fallback now; real Pages in Phase 3 |
| Industry navigation | Six BEXSTAR customer groups | Homepage entry now; Pages in Phase 4 |
| Supply-chain service depth | Sourcing, Supply Chain, Amazon FBA | Editorial entries now; Pages in Phase 4 |
| Insights hierarchy | Resources, guides, insights, cases, FAQ, updates | Native posts and categories; Phase 5 |
| Self-service / digital hub | Quote, Track, Calculator, Portal | Honest planned status now; Phase 6 |
| Task-based contact | Get a Quote / Contact | Preview target now; approved form integration later |
| Enterprise footer | Services, destinations, content, company, tools, legal | Structure now; approved legal copy later |

## Phase 1 homepage
Keep the V3 order: hero → quick actions → introduction → services → media break →
destinations → sourcing → supply chain → Amazon FBA → industries → digital logistics →
case-study entry → Why BEXSTAR → final CTA. Header and footer frame the page.
The Resources anchor intentionally contains the case-study entry in this phase.
Detailed guides, article archives and related-content editorial rules belong to Phase 5.

## Data model and weekly publishing
Use Pages for service hubs, routes and destination children; native Posts for weekly
guides, insights, case studies, news and market updates. Use categories for content type
and tags for destinations/modes initially. No CPT is registered in Phase 1: the editorial
benefit is not established yet. If cases later need typed fields and filters, put their
CPT/taxonomy registration in a separate site plugin so changing the theme does not hide content.
Use ordinary core heading, paragraph, button, group and post blocks; media is a small dynamic
block with image/video selectors. No entire homepage stored as custom HTML.

## Navigation and URLs
The exact V3 planned hierarchy is maintained in inc/navigation.json.
Published Pages are resolved using their hierarchical path, never a production domain.
Unpublished navigation leaves are labelled non-links. Homepage/footers resolve to the
matching in-page overview until those Pages exist. They are NOT finished destination pages.
A native WordPress menu assigned to bexstar-primary overrides the default hierarchy.
Use Appearance > Menus (wp-admin/nav-menus.php) where supported; verify this UI in staging.
A dedicated block-native menu editor is not included in this phase.

Seven primary groups: Shipping, Sourcing, Supply Chain, Amazon FBA, Industries,
Resources, About Us. Utility/actions: Track, Get a Quote, Contact and English status.
English is deliberately not a fake language switcher. No unprovided social profile is linked.
Shipping includes a third-level Europe disclosure. Other depth can be added through a native menu.

## Migration and Elementor boundary
This is a new block theme, not an exported Elementor website or an Elementor child theme.
The current GitHub baseline contained README only. There is no evidence that existing
WordPress pages, media, SureForms configuration or Elementor layouts have been synced here.
Do not activate this theme on production as a shortcut: headers, templates and page rendering
will change. Existing content inventory and template migration require a separate staging task.

## Business rules
Do not generate rates, transit times, statistics or testimonials. Applicable EU service copy
states customs duty included and VAT excluded. Confirm the commercial delivery term and tax
wording before publication. No customer record is embedded in the theme.
Do not assume carrier endorsement or warehouse ownership.

## Future API separation — not implemented
Theme: presentation and accessible states only.
Site plugin/server: quote calculations, authenticated requests, consent/rate limits,
CRM delivery and secret storage. Provider adapters normalize tracking behind BEXSTAR API;
never expose agent credentials or internal cost/identity by default.
Stable future routes: /get-a-quote/, /track/, a calculator and customer portal.
No fake submissions, generated tracking events or pricing are returned.

## SEO ownership
Core handles semantic blocks, search and archive pagination; native featured images
supply responsive markup. SEO plugin owns canonical, schema, Open Graph and sitemap metadata.
Do not emit duplicate metadata from this theme. Rank Math is not required or hard-wired.
Single templates expose author/date/categories/tags; breadcrumb insertion and related content
remain Phase 5/7 work. Template presence is not proof of a completed SEO implementation.
