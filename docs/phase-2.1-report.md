# Phase 2.1 — desktop navigation and media system
Date: 2026-09-22 UTC · Theme version: 0.2.1
Repository: bexstar-global/bexstar-wordpress-theme · Branch: main
Parent commit: f1f4b6c79789717ba7595c815b656b3cc4d552c5

## Scope and outcome
Continues the existing WordPress block theme. The owner reports Phase 1 and Phase 2
are deployed to the isolated Hostinger development site. This phase implements the
requested focused polish; it is not a claim of final visual or WordPress runtime acceptance.

## 1. Mega menu interaction
- Summary clicks now update open/close state synchronously, avoiding reliance on the
  browser's queued toggle event to close the previous panel. Clicking open closes it;
  clicking another switches immediately. No top-level navigation occurs on these triggers.
- Native details/summary semantics and keyboard activation remain. Native toggle handling
  remains a fallback. Outside click and focus departure close menus.
- Escape closes all open disclosures, including nested Europe navigation, and restores
  focus to the containing top-level trigger.
- Existing 160 ms hover intent / 220 ms leave delay remains optional; click cancels timers.
  Hover cannot hide another menu with keyboard focus.
- Fixed-width plus/minus indicator follows the open attribute without changing trigger width.
  Yellow open underline, 160 ms entry animation and reduced-motion support are retained.

## 2. Mega menu design
- Desktop panels share 32 px vertical padding and a 420 px minimum height where viewport
  height permits; overflow scrolls inside the panel rather than expanding the page.
- Aligned section labels, link rhythm, vertical separators and yellow-accented feature CTAs.
- Shipping keeps three deep link columns and a fourth feature column, now with its own
  compact shipping image slot. Europe children are preserved.
- Other default menus use links / editorial feature / category media, with fixed 8:5 media.
  Short menus do not stretch their images to fill the whole panel.
- Copy follows the Phase 2.1 specification. Industries remains present and receives its
  own media slot; it was not removed just because the brief emphasizes six other menus.
- Mobile navigation stays at the existing 1239 px breakpoint, with menu imagery hidden.
  A compact desktop header rule protects the 1240–1399 px interval.

## 3. Hero
- Existing copy, two-line headline architecture and editable core blocks are preserved.
- Desktop hero reduced from 620–760 px to 560–660 px; title capped at 66 px, with tighter
  vertical spacing and a bounded title measure.
- Directional navy overlay protects left-side copy while allowing right-side imagery.
  Mobile retains a stronger even overlay and its existing compact heading rules.
- Hero poster is still the first asset priority. Optional MP4 only loads after explicit
  play, requires a poster and has pause/reduced-motion behavior. No autoplay added.

## 4. Quick actions
The three existing actions remain. Equal-height 80 px link areas, aligned right arrows,
consistent separators, hover feedback and inset keyboard-focus outlines clarify the controls.
Existing destination fallback behavior is preserved; no tracking API or quote backend is invented.

## 5. First homepage section
More than freight forwarding now explicitly explains international logistics, China sourcing
and supply chain coordination. The heading has an editorial measure, its image uses 8:5,
and the new more-than-freight slot prefers its own image, then sourcing-main.webp.
The existing Gutenberg copy and image selection workflow remains.

## 6. Media slots and fallback behavior
Eight new slots: shipping-menu, sourcing-menu, supply-chain-menu, fba-menu, resources-menu,
about-menu, industries-menu and more-than-freight. Total: 32.
Six new original, text-free SVG placeholders depict supplier/inspection, connected stages,
warehouse parcels, reference documents, company/global context and industry facilities.
They are temporary illustrations, not photographs or claims about BEXSTAR premises.
Shipping retains the original logistics illustration. No third-party media was downloaded.
Navigation block inspector adds independent Media Library image selection for seven default menus.
Missing bundled files are never emitted as broken image URLs; a failed image load (including
failure before script initialization) exposes the category fallback.
Media labels remain off by default and unavailable in staging/production environments.

## 7. Required final media
Supply approved generic/dark/light BEXSTAR logos; hero-poster.webp first, optional hero-video.mp4
second; sourcing-menu.webp, supply-chain-menu.webp, fba-menu.webp, resources-menu.webp,
about-menu.webp, shipping-menu.webp, industries-menu.webp and more-than-freight.webp.
Full paths, dimensions, ratios and desktop/mobile crop guidance are in [media manifest](media-manifest.md).
Existing service and downstream section photographs from Phase 2 also remain outstanding.

## 8. Created files (9)
- assets/images/about/.gitkeep
- assets/images/placeholders/about-neutral.svg
- assets/images/placeholders/fba-neutral.svg
- assets/images/placeholders/industries-neutral.svg
- assets/images/placeholders/resources-neutral.svg
- assets/images/placeholders/sourcing-neutral.svg
- assets/images/placeholders/supply-chain-neutral.svg
- assets/images/resources/.gitkeep
- docs/phase-2.1-report.md

## 9. Modified files (16)
- README.md
- assets/css/site.css
- assets/js/editor.js
- assets/js/site.js
- assets/media-manifest.json
- blocks/navigation/block.json
- docs/editor-guide.md
- docs/file-tree.md
- docs/media-manifest.md
- inc/media.php
- inc/navigation.php
- patterns/introduction.php
- patterns/quick-actions.php
- scripts/navigation-contract.test.mjs
- scripts/validate.mjs
- style.css
No files deleted.

## 10. Validation performed
- Dependency-free static validator: 577 checks passed, 16 patterns, 32 media slots.
  Covers block comment structure, JSON, homepage anchors, slot references, editor slot
  availability, CSS fallback file existence and forbidden runtime domain/credential patterns.
- Production JavaScript navigation contract tests: 12 passed. Three new tests cover immediate
  click switching/closing, hover-open click close and mobile/pending-hover cancellation.
- JavaScript syntax checks passed for site.js and editor.js.
- All seven SVG fallback files parsed as valid XML.
- Source review confirmed existing menu hierarchy, no added external media/network requests,
  no fabricated business metrics, and no manual deployment workflow.

## 11. Known issues / acceptance still required
There is no PHP CLI or installed browser binary in the current execution environment.
No PHP lint, WordPress activation, Gutenberg save/reload or real-browser screenshot tests
were run for Phase 2.1. The simulated DOM tests do not establish actual keyboard/native
details behavior, visual menu overlap or viewport overflow.
1440, 1536 and 1920 px desktop rendering, hero image crop, every menu, nested Europe,
Escape, click-outside and mobile/tablet switching must still be accepted on the isolated
development site. Check these with actual approved media once supplied.
Saved WordPress template/pattern instances may retain old introduction markup and copy;
this commit does not reset templates or edit the database. Custom assigned menus do not
use default V3 feature media; their structure remains owner-controlled.
The existing tracking/quote/contact availability states remain; no new secondary-page
program or Phase 3 work was started.

## 12. Commit and deployment boundary
Commit message: Phase 2.1: polish desktop navigation and media system
The containing Git commit is the Phase 2.1 commit; its exact hash is delivered in the
handoff message (a file cannot contain the hash of its own commit without changing it).
The owner explicitly authorized a main commit knowing Hostinger may automatically deploy
main to the isolated development site. That automation is not triggered manually or verified here.

NO production bexgl.com changes.
NO DNS changes.
NO manual Hostinger deployment.
STOP AFTER PHASE 2.1. PHASE 3 NOT STARTED.
