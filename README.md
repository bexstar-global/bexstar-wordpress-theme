# BEXSTAR WordPress Theme

Custom WordPress block theme for BEXSTAR Global Website.

## Status
V3 Phase 2.1 desktop navigation and media polish, version 0.2.1. Runtime/visual acceptance pending.
The owner reports Phase 1 and Phase 2 are active on the isolated development site.
The repository root IS the theme root. Do not add another nested bexstar directory.
The owner reports Hostinger automatically deploys main to the isolated development site.
A commit may therefore trigger that existing automation; this change adds no deployment configuration.

## Included
- theme.json design tokens; seven standard block templates and two template parts.
- Editable homepage patterns; desktop mega-menu and mobile disclosure navigation.
- 32 media slots with Media Library selection and safe no-media fallbacks.
- Customer-facing Coming soon states for unavailable tools, not fake APIs.
- Published case-study selection, conditional legal links and real-logo fallbacks.
- Architecture, media, editorial and staging handoff documentation.

Start with [Phase 2.1 report](docs/phase-2.1-report.md),
[Phase 2 historical report](docs/phase-2-report.md),
[Phase 2 gap analysis](docs/phase-2-dsv-gap-analysis.md),
[Phase 1 historical report](docs/phase-1-report.md),
[DSV analysis](docs/dsv-architecture-analysis.md),
[BEXSTAR mapping](docs/bexstar-architecture-map.md),
[editor guide](docs/editor-guide.md) and [media manifest](docs/media-manifest.md).

## Local checks
Requires Node.js 18+ for dependency-free static validation:
```sh
node scripts/validate.mjs
node --check assets/js/site.js
node --check assets/js/editor.js
node --test scripts/navigation-contract.test.mjs
```
PHP lint on a machine with PHP 8+:
```sh
find . -name '*.php' -not -path './node_modules/*' -exec php -l {} \;
```
Read the validation limitations in the report before calling this theme validated.
Phase 2.1 editor save/reload and real-browser tests at 1440/1536/1920 remain acceptance gates.
The navigation test uses a simulated DOM; it is not a viewport or native-browser test.

## Safety
Do not manually deploy to Hostinger, change DNS or modify production bexgl.com.
Phase 2.1 explicitly authorizes committing main despite the existing test-site auto-deployment.
No credentials, customer personal data, invented results or third-party media belong here.
See [staging handoff](docs/staging-handoff.md). Stop after Phase 2.1; Phase 3 requires approval.
