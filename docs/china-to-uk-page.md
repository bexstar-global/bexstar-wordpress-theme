# China to UK shipping page — publication handoff

This review adds page support only. No WordPress page/database changes, navigation edits, deployment, SEO plugin installation or production changes are included.

## Publish after review and approved deployment

1. In WordPress Pages, search published pages, drafts and Trash for the exact route before creating a duplicate. Back up any existing page content.
2. Use the existing published `Shipping` parent page (slug `shipping`). If none exists, create a minimal parent page with that slug as a separate admin decision; it must not be an empty public landing page. The child requires this hierarchy for `/shipping/china-to-uk/`. Do not change permalink structure or existing production pages to force the route.
3. Create a draft child page titled `China to UK Shipping`; slug `china-to-uk`; parent `Shipping`.
4. Select template **BEXSTAR — China to UK Shipping**.
5. In the block inserter, Patterns → BEXSTAR sections, insert **China to UK shipping — complete page** once. It inserts editable blocks into the page, not a synced template. There is one H1 in the pattern; the template deliberately does not add a second post title.
6. Edit copy, images and FAQ in Pages → Edit. The existing BEXSTAR media blocks retain their media-library controls and fallback behavior. Do not replace the content with a Custom HTML block. Future pattern code updates do not overwrite this saved content.
7. Preview at desktop, tablet and mobile. Check header/footer, FAQ keyboard operation, `/get-a-quote/` and `/contact/`. Confirm the UK CSS loads with this template and the permalink is exactly `/shipping/china-to-uk/`.
8. Confirm service copy with BEXSTAR operations before publishing, especially truck-route availability, DDP scope and FBA arrangements. This page gives no prices or promised delivery times.
9. Set a concise excerpt. If an existing SEO plugin is available, use SEO title `China to UK Shipping | Sea, Air & DDP Options | BEXSTAR` and description `Plan your China to UK shipment with BEXSTAR. Discuss sea, air, truck, DDP and Amazon FBA delivery options, and request a cargo-specific quote.` Do not install a plugin as part of this change.
10. Use the page's own URL as canonical via the existing SEO setup; never hard-code the staging domain. Keep staging excluded from indexing. After separately approved launch, verify production indexing, canonical and sitemap inclusion. Do not add invented review, FAQ or business structured data.
11. Publish only after approval. Existing navigation resolves published destination pages automatically; clear relevant WordPress/page caches and verify the homepage UK card and menu resolve to the new route. No navigation changes are necessary in this PR.

## Scope and assets

- Template: `templates/page-china-to-uk.html` — shared header/footer and editable post content.
- Pattern: `patterns/china-to-uk.php` — intro, transport, DDP, FBA, process, FAQ and quote CTA.
- Scoped stylesheet: `assets/css/china-to-uk.css`; frontend conditional on the template, editor-loaded for pattern editing.
- Approved existing media slots: `uk`, `sea-freight`, `air-freight`, `fba-menu`. The latter reuses the approved FBA still image without attaching the optional homepage video.
- No image, JavaScript, homepage, tracking, quote form or contact page changes.

Admin publication and real Gutenberg save/preview are required after deployment; repository changes alone cannot publish the route or configure database SEO settings.

## Review validation

- Repository static validation passed; navigation contract tests: 12/12 passed.
- Both changed/new PHP files passed PHP 8.3 syntax parsing.
- All 98 pattern blocks passed Gutenberg parse/serialize validation.
- Local isolated page-content preview checked at 1920, 1536, 1440, 768, 390 and 320px: no horizontal overflow, all four images loaded, one H1, FAQ opens with Enter.
- Preview used theme CSS/media and a small core-button style harness; it is not a deployed WordPress acceptance test. Shared header/footer were not reimplemented. Final template assignment, saved block rendering, theme global styles, SEO and published URL must be checked in WordPress after approved deployment.
