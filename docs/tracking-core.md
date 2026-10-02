> Phase 3A.1 update: KQD is implemented for controlled CLI validation only. See
> [KQD adapter and live-test runbook](tracking-kqd.md). Lookup is format-agnostic (including numeric/provider-stored references); canonical allocation and historical uniqueness are unchanged.
> The resolver now passes `bexstar_reference` to every adapter; stored provider
> tracking numbers are optional and remain private. No public endpoint is enabled.

# Phase 3A Tracking Core — dormant contracts

This skeleton performs no HTTP requests, registers no REST endpoint, creates no
WordPress pages or database tables, and returns no public tracking results.
`bexstar_tracking_ready()` deliberately returns false. Header/footer Track links
retain their existing homepage anchor even if a Track page is published.
The disabled page foundation is available as “BEXSTAR — Track Shipment”. Create
a draft page with slug `track` and select that template for a future preview;
page publication and service activation are separate acceptance steps.

## Internal mapping

`MappingRepository` is the persistence boundary; there is no production storage
implementation yet. The in-memory implementation exists only inside offline tests.
A future database implementation should use a parent shipment table with a unique,
case-sensitive BEXSTAR number and a child reference table with a foreign shipment
key, unique reference ID, leg ID, provider code, provider tracking number, role,
and optional account/configuration reference. Save parent and children atomically.
Do not store secrets in mappings. Only controlled administrator imports may write.
Keep shipments independent of the active theme; a later plugin extraction must
preserve the tables. Migrations require explicit execution, not frontend requests.

One BEXSTAR number may contain many legs. Every leg has exactly one primary
reference and zero or more fallback references. A split shipment uses separate
leg IDs. A transfer/last-mile leg is explicit, never inferred from number format.
The resolver uses exact internal mapping only and never enumerates providers.
Its execution plan is PRIVATE; it contains provider codes and provider references.
A missing mapping returns not_found. Canonical public syntax is BEXSTAR + MMDD + three serial digits, no year; for
example BEXSTAR1002037. Input is trimmed and normalized to uppercase. Validate
month/day (February 29 is allowed because no year is encoded). Serial range is
000–999. Provider reference formats remain independent and internal.

## Adapter and configuration boundaries

Adapters implement `ProviderAdapter::code()` and `fetch($reference)` and raise
`TrackingError` for known failures. Registry registration is explicit and rejects
collisions; it must never auto-load a class from public request data. The skeleton
registers no adapters. Config defaults disable public access, remote requests and
fallback. Limits are contract defaults, not a rate limiter or network transport.
The future controller/service must enforce them before invoking any adapter.

Future 17TRACK support uses an adapter key such as `seventeentrack` and a mapped
fallback reference for a specific leg. The aggregator may need a carrier identifier,
registration step, account reference or different lookup number: obtain these from
its API contract rather than assuming it accepts a BEXSTAR number. The resolver
includes only explicitly enabled mapped fallback references, in stored order,
bounded by max_fallbacks_per_leg. A future executor first attempts the enabled
direct adapter and invokes a fallback only for documented eligible errors
(e.g. timeout/unavailable, or confirmed no-result where policy permits). It must
never fan out blindly or treat malformed/authentication failures as not_found.
Disabled direct adapters must not be called. Without an eligible fallback, fail
safely. Adding an adapter must not change the public page or schema.

## Normalized contract

`response.schema.json` is the public success contract. Provider names/codes,
provider tracking numbers, account references and raw payloads are intentionally
excluded with additionalProperties=false. The internal mapping supplies provider
identity; adapters may retain private metadata separately. Do not JSON-serialize
adapter arrays directly to the browser. A future normalizer/public projector must
validate the final response against this contract.

Missing data is null; unknown status is `unknown`, never a fabricated milestone.
Event `status` and `description` must be customer-safe plain text (no raw provider
technical text, addresses, phone numbers or secrets). Timestamps require a known
UTC offset. Never guess a timezone; preserve ambiguous source times privately
and publish null until resolved. `last_updated` is a shipment update, not fetch time.
Use an opaque event ID. Deduplicate within a source using provider event ID or a
stable fingerprint; do not collapse legitimate events across separate legs merely
because their timestamps match. Sort dated events consistently and undated events
separately. Keep leg IDs opaque. Mark partial data explicitly. Never mark the whole
shipment delivered unless all required legs have confirmed delivery; ambiguous
leg status should remain unknown. Fallback results must not overwrite stronger
confirmed milestones with an older snapshot. These normalization/aggregation rules
are contracts for the next implementation, not implemented live behavior.

## Required before enabling

- Persistent mapping repository and authorized import workflow.
- Controller/service, public-data projection, normalization and contract validation.
- Public-reference entropy/access policy and atomic rate limiting.
- Server-only credentials, fixed HTTPS allowlists, response/timeout limits.
- Cache, request coalescing, provider circuit breakers and redacted logs.
- Provider-specific documented status/timezone/error mappings.
- Anshida acceptance first; Shangyi second; no aggregator integration yet.
- Frontend timeline and accessible error/loading states; six-width acceptance.
- Explicit review of the readiness gate and homepage integration.

No customer result fixtures may be loaded in production. Provider fixture identifiers use TEST prefixes. The public fixture uses the
canonical example BEXSTAR1002037; all fixture data is offline-only and illustrative.

## Offline checks

Run from the theme root with PHP 8.0+:

    php -r "define('BEXSTAR_TRACKING_TEST', true); require 'tests/tracking/core.php';"

The test defines minimal WordPress stubs and uses only an in-memory repository and
a fixture adapter. It sends no email and makes no network/database requests.
`tests/tracking/schema.mjs` needs development-only `ajv` and `ajv-formats` packages;
run it with `BEXSTAR_TEST_NODE_MODULES` pointing to their node_modules directory.
Nothing from those packages is needed by the theme at runtime.

API documentation still required for BOTH Anshida and Shangyi: exact company/API
identity and version, sandbox/production endpoints, auth/signature specification
(no secrets in chat/Git), lookup-number types, request/response envelopes, status
codes, timezone conventions, pagination/multi-piece behavior, rate limits, IP
allowlists, and sanitized success/no-result/auth-error/timeout response samples.

## Allocation and permanent uniqueness

`TrackingNumberAllocator` uses the Asia/Shanghai issue date and random serial
selection without replacement. It checks the repository before each insert and
retries when an atomic insert loses a concurrent race. A full MMDD namespace raises
number_space_exhausted; it never adds a year, expands the serial or overwrites data.
There are only 1,000 numbers per MMDD across ALL years, not 1,000 renewed annually.
Archive/deletion must preserve reserved numbers. Store issue year/timestamp as
internal metadata, never as part of the public number or uniqueness key.

`MappingRepository::insert()` replaces the ambiguous save/upsert contract. The
in-memory test implementation rejects duplicates. Production persistence is still
not implemented: before activation its database MUST enforce a UNIQUE index on
the canonical public number and atomically insert all child references. A lookup
before insert alone does not prevent concurrent collisions. Updates to provider
references require a separate authorized operation, not duplicate parent inserts.

This short number is guessable and is not a secret/access token. Before public
activation, implement rate limits and the previously planned public-data/access
policy. All live tracking remains disabled.

## BEXSTAR-first tracking UX

`templates/page-track.html` uses the existing shared header/footer and loads the
`bexstar_tracking` shortcode from `inc/tracking/view.php`. Tracking CSS is loaded
only on the Track page/template. There is one primary form, with the canonical
example and TRACK NOW CTA. Its fieldset stays disabled until a separately reviewed
Core endpoint and activation change exist. No result or error is fabricated; empty
result fields, timeline template and four customer-safe error templates are hidden.
The readiness gate still prevents homepage/header/footer activation.

17TRACK is a small text-only partner area below the primary form, not a second
lookup channel. The verified BEXSTAR EXPRESS carrier-page URL is
https://www.17track.net/en/carriers/bexstar-express. It is defined as the default
`BEXSTAR_17TRACK_CARRIER_URL` in `inc/tracking/config.php`. A pre-existing definition
in server configuration (for example wp-config.php) takes precedence. Only 17track.net or its subdomains are accepted; credentials and custom ports
are rejected. Without a valid configured URL the partner element is non-clickable
and explicitly pending. No support/registration claim beyond the requested partner
label is added. No logos or external branding assets are used.

When the endpoint is implemented, the primary form must submit exclusively to the
BEXSTAR Tracking Core. Do not wire this form directly to 17TRACK or a provider.
The future frontend must render only the public schema via textContent, omit
unknown optional fields, announce loading/results, show an allowlisted error
message, and ignore stale requests. Status labels map to the canonical enum;
`unknown` is allowed without inventing milestones. The existing error-template
keys are invalid_number, not_found, unavailable and provider_unavailable. No
technical details or actual provider names may be interpolated into these messages.

A future OUTBOUND aggregator adapter (BEXSTAR queries 17TRACK) is separate from a
future INBOUND BEXSTAR Carrier API (17TRACK queries BEXSTAR). The inbound API must
accept the canonical BEXSTAR public number and use the same Core/public projection.
It must exclude provider_code, provider_tracking_number, internal TMS IDs, credentials
and underlying errors. Add versioning, partner authentication/scopes, rate limits
and contract tests before publishing it. Do not expose the private resolver plan.
Neither API direction is activated by this page foundation or its partner link.
