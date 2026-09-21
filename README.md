# BEXSTAR WordPress Theme

Custom WordPress block theme for BEXSTAR Global Website.

## Status
V3 Phase 1 foundation, version 0.1.0. Development only; not production-ready.
The repository root IS the theme root. Do not add another nested bexstar directory.
No deployment or production connection is configured by this change.

## Included
- theme.json design tokens; seven standard block templates and two template parts.
- Editable homepage patterns; desktop mega-menu and mobile disclosure navigation.
- 24 media slots with Media Library selection and safe no-media fallbacks.
- Honest planned states for future customer tools, not fake APIs.
- Architecture, media, editorial and staging handoff documentation.

Start with [Phase 1 report](docs/phase-1-report.md),
[DSV analysis](docs/dsv-architecture-analysis.md),
[BEXSTAR mapping](docs/bexstar-architecture-map.md),
[editor guide](docs/editor-guide.md) and [media manifest](docs/media-manifest.md).

## Local checks
Requires Node.js 18+ for dependency-free static validation:
```sh
node scripts/validate.mjs
node --check assets/js/site.js
node --check assets/js/editor.js
```
PHP lint on a machine with PHP 8+:
```sh
find . -name '*.php' -not -path './node_modules/*' -exec php -l {} \;
```
Read the validation limitations in the report before calling this theme validated.
WordPress activation/editor tests remain a staging acceptance gate.

## Safety
Do not deploy to Hostinger, change DNS or modify bexgl.com without separate approval.
No credentials, customer personal data, invented results or third-party media belong here.
See [staging handoff](docs/staging-handoff.md). Stop after Phase 1; Phase 2 requires approval.
