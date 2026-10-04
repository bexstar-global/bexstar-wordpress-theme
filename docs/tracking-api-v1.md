# BEXSTAR Tracking API v1 and customer foundation

## Boundary and activation state

`/track/` consumes the same independent WordPress REST API that future approved
external clients can consume. UI, REST controller, resolver, persistence and provider
adapters are separate modules. No provider HTTP logic exists in the UI/controller.
Existing KQD is reused. No live provider calls or Hostinger deployment were performed.

Default installation state is CLOSED: the page is pre-live, API returns a safe 503,
provider requests are disabled and homepage/navigation remain unchanged. Installing
the database alone does not activate tracking. Separate operator configuration after
live acceptance is required. This is a validated foundation, not a claim that KQD,
other providers or 17TRACK have passed production integration acceptance.

Theme bootstrap still hosts the module to respect the existing architecture. The
API is independent of the page, but still depends on WordPress and this active theme.
A future site-independent/must-use plugin migration can reuse these PHP modules.

## API contract

| Method | WordPress REST route | Input |
|---|---|---|
| GET | `/wp-json/bexstar-tracking/v1/shipments/{reference}` | URL-encode reference once |
| POST | `/wp-json/bexstar-tracking/v1/lookup` | JSON object containing only `reference` |

Prefer POST for references containing slashes, percent signs or non-ASCII text and
to avoid placing references in access-log URLs. The page derives the endpoint through
`rest_url()` so subdirectory installs and WordPress REST routing are respected.
Content-Type must be application/json; body limit 2 KiB, reference limit 128 UTF-8
bytes. Numeric references must be JSON strings to preserve leading zeros.
Empty, invalid-encoding and control-character input is rejected. Lookup never uses
a carrier-prefix or canonical-number regex, never uppercases provider references,
and never accepts client-selected provider overrides. Outer whitespace is trimmed.
Canonical BEXSTAR MMDD serial allocation remains strict and historically unique.

Success preserves the existing normalized `schema_version: 1` contract and adds
`success` and `reference`. See `inc/tracking/api-response.schema.json`. Example below
is deliberately synthetic documentation, never a live fixture served to customers:

```json
{
  "success": true,
  "reference": "EXAMPLE-REFERENCE",
  "schema_version": 1,
  "shipment": {
    "tracking_number": "EXAMPLE-REFERENCE",
    "transport_mode": null,
    "origin": "CN",
    "destination": null,
    "current_status": "in_transit",
    "last_updated": "2026-10-01T04:00:00Z"
  },
  "events": [{
    "event_id": "synthetic-example-id",
    "leg_id": "piece-1",
    "timestamp": "2026-10-01T04:00:00Z",
    "location": null,
    "status": "in transit",
    "description": "Shipment departed the origin terminal.",
    "normalized_status": "in_transit"
  }],
  "meta": {
    "fetched_at": "2026-10-01T04:05:00Z",
    "stale": false,
    "partial": false
  }
}
```

Provider code/name, provider reference, internal leg IDs, credentials and raw error
messages are intentionally absent, including for external API consumers. Existing
11 milestone statuses plus `unknown` are retained rather than breaking the earlier
contract for a second status set. Unknown event descriptions are preserved as plain
text. Missing fields remain null, events are deduplicated within anonymous pieces,
sorted chronologically (unknown dates last), and timestamps normalized to UTC.
A partial/mixed delivered shipment cannot be represented as wholly delivered.

Error envelope: `{"success":false,"error":{"code":"...","message":"..."}}`.

| Code | HTTP | Meaning |
|---|---|---|
| INVALID_REQUEST | 400 | Invalid/missing input or malformed JSON |
| PROVIDER_NOT_MAPPED | 404 | No explicit stored mapping; no provider was queried |
| TRACKING_NOT_FOUND | 404 | Mapped provider returned a genuine no-result |
| PROVIDER_AUTH_ERROR | 502 | Confirmed upstream HTTP 401/403; safe message only |
| PROVIDER_TIMEOUT | 504 | Transport/request budget timeout |
| PROVIDER_UNAVAILABLE | 502/503 | Malformed/upstream failure, disabled adapter or missing configuration |
| RATE_LIMITED | 429 | Rate or concurrent lookup limit; Retry-After: 60 |
| TRACKING_UNAVAILABLE | 503 | API/live gate or storage/service unavailable |
| INTERNAL_ERROR | 500 | Other failure; no exception/stack trace exposed |

KQD `success=0` remains unavailable until its official error-code dictionary is
provided. Do not infer authentication or not-found from undocumented error text.
All responses use `Cache-Control: no-store, private`; application cache is server-side.

## Registry and provider status

`ProviderCatalog` defines internal metadata and constructs the existing shared
`AdapterRegistry` per request. Every adapter implements `ProviderAdapter::fetch()`
and receives `bexstar_reference` from the resolver. That adapter translates it to
its required wire field; the core does not know provider request parameter names.

| Key | Implementation | State / missing material |
|---|---|---|
| kqd | Existing KqdAdapter | Implemented; controlled live acceptance pending |
| zx | ZxAdapter pending stub | Base URL, complete event payload, timezone/status/error docs |
| dmc | Shared NewWisdomAdapter('dmc') pending stub | Verified API path, token/signing rules, request/response schemas |
| anshida | PendingAdapter | No usable endpoint/adapter specification found in current repo |
| shangyi | PendingAdapter | No usable endpoint/adapter specification found in current repo |

Known ZX information is JSON POST `/api/v1/common/tracking`, fields FACTNO, SUPNO,
SUPPASS, APPKEY, PACKNO (array, maximum 20); observed FACTNO is 003. It is not enough
to normalize undocumented events, so the stub sends no network requests.
DMC's supplied gateway is `dmcgyl.nextsls.com`, app_code `dmcgyl`. Those identify
configuration for the shared NewWisdom/NextSLS family, not an invented API endpoint.
Enabling a pending key does not make its adapter operational.

No blind lookup, prefix guessing or automatic mapping creation. Explicitly mapped
fallbacks can be tried only when enabled and a direct source returns not-found,
timeout or unavailable. Authentication and malformed responses do not trigger that
fallback. A future aggregator is another mapped adapter; public API/UI stay unchanged.
Direct provider-stored references (including numeric ones) are mapped exactly the
same way. Multiple primary legs support split shipments and handoffs.

## Persistence and concurrency

Explicit CLI installation creates two prefixed InnoDB tables through dbDelta:

- `{prefix}bexstar_tracking`: primary key `tracking_reference VARBINARY(128)`;
  `mapping_json` containing provider_code/provider_tracking_number/reference_id/
  leg_id/role per reference; last_successful_lookup, last_status, last_event_time,
  cached_normalized_response, cache_expires, created_at, updated_at.
- `{prefix}bexstar_tracking_runtime`: hashed rate/lock buckets, hits, owner, expires.

One row stores the immutable parent plus its complete leg mapping atomically.
Binary uniqueness preserves case and leading zeros. Insert never overwrites an
existing number; no deletion/reuse command is provided. Canonical allocation still
checks all 000–999 serials and returns controlled exhaustion. Cache writes cannot
change mapping_json. Operator mapping replacement/import reconciliation is future
work; do not delete historical references to make an import succeed.

Successful normalized responses cache for 300 seconds by default (30–900 configurable).
Partial results cache for at most 30 seconds. Expired/invalid cached data is refreshed;
there is no indefinite stale-success fallback. Failed lookups are not cached as success.
No provider response or credentials are stored in the public cache.

Limits use atomic shared SQL counters: 30/client/minute, 20/reference/minute,
300/site/minute. Expired runtime rows are cleaned in bounded batches. Client identity
uses REMOTE_ADDR only, salted before storage; untrusted forwarded headers are ignored.
An operator behind a proxy must establish trusted IP handling at the server/WAF layer.
A 45-second owned lease prevents concurrent same-reference refreshes; release checks
ownership. KQD REST requests share a 20-second / 12-call budget across legs; individual
calls have at most six seconds, HTTPS verification, response-size cap and no redirects.
Existing operator-only KQD CLI testing retains its separately documented bounds.

## Server setup — explicit operator actions, not executed on Hostinger

1. Back up the database, deploy through the approved process, and verify PHP 8.0+.
2. In authenticated SSH/WP-CLI at the WordPress installation root run:

   ```sh
   wp bexstar tracking-install
   ```

   No schema migration is triggered by visitors or REST requests.
3. Prepare a private mapping JSON file outside the public web root. Example shape:

   ```json
   {
     "reference": "EXAMPLE-REFERENCE",
     "references": [{
       "reference_id": "mapping-1",
       "leg_id": "leg-1",
       "provider_code": "kqd",
       "role": "primary"
     }]
   }
   ```

   `provider_tracking_number` is optional private metadata. KQD always attempts the
   public/customer reference first. Use genuine operator-verified mappings, not this
   example. Import with `wp bexstar tracking-map /private/path/shipment.json`.
   No public mapping administration route exists.
4. Configure KQD credentials privately using the existing `tracking-kqd.md` runbook
   and complete its approved manual live test before public activation.
5. Only after acceptance, set these server constants/environment values:

   | Key | Value / default |
   |---|---|
   | BEXSTAR_TRACKING_API_ENABLED | `1` to enable page/API, default closed |
   | BEXSTAR_TRACKING_LIVE_ENABLED | `1` to permit adapter execution/cache access, default closed |
   | BEXSTAR_TRACKING_ENABLED_PROVIDERS | reviewed lowercase comma-separated keys; default empty |
   | BEXSTAR_TRACKING_CACHE_TTL | seconds, default 300, clamped 30–900 |
   | BEXSTAR_TRACKING_FALLBACK_ENABLED | `1` for explicit mapped fallback; default closed |
   | KQD_API_ENDPOINT | verified HTTPS endpoint, private server config |
   | KQD_APP_TOKEN / KQD_APP_KEY | private server credentials |

   Planned ZX keys: ZX_API_BASE_URL, ZX_FACTNO, ZX_SUPNO, ZX_SUPPASS, ZX_APPKEY.
   Planned DMC keys: DMC_GATEWAY, DMC_APP_CODE, DMC_TOKEN, DMC_INTEGRATION_CODE.
   Pending keys are a configuration contract only and are not sent by stubs.
   Do not put values in Git, browser configuration, screenshots or request logs.
6. If `/track/` is not published, create the WordPress page with slug `track` and
   template **BEXSTAR — Track Shipment**. Existing stored Gutenberg overrides may
   need resetting to the theme template. No page/database content was remotely edited.
7. This commit deliberately does not activate homepage/navigation Track links.
   Approve that small change separately after live acceptance.

Security model: anonymous read-only tracking, with gates, rate limits and strict
response projection. A REST nonce is not used as pretend authentication for guests.
No mappings, addresses, signatures or customer-account data are publicly exposed.
Reference possession is not strong authentication, especially for numeric references.
Before public launch, confirm this disclosure policy and edge-level bot/rate controls.
External partner authentication/signatures/IP policies require that partner's actual
specification. There is no request logging in this module; disable request-body capture
in host/APM/debug plugins. See WordPress official REST endpoint and dbDelta guidance:
https://developer.wordpress.org/rest-api/extending-the-rest-api/adding-custom-endpoints/
https://developer.wordpress.org/plugins/creating-tables-with-plugins/

## Future 17TRACK integration

Add `inc/tracking/integrations/seventeentrack-controller.php` only after receiving
17TRACK's official protocol. That controller will authenticate the partner, call
TrackingService, project through PublicResponse and translate to the documented
partner schema. Never call provider adapters from that controller directly or expose
private mapping metadata. It is distinct from a possible outbound aggregator adapter.
The existing small 17TRACK carrier-page link is unchanged. API compatibility with
17TRACK has not been claimed or implemented.

## Verification and remaining boundaries

Offline tests:

```sh
php -r "define('BEXSTAR_TRACKING_TEST',true); require 'tests/tracking/kqd.php';"
php -r "define('BEXSTAR_TRACKING_TEST',true); require 'tests/tracking/api.php';"
BEXSTAR_TEST_NODE_MODULES=/path/to/node_modules node tests/tracking/schema.mjs
BEXSTAR_TEST_NODE_MODULES=/path/to/node_modules node tests/tracking/api-schema.mjs
node scripts/validate.mjs
```

Browser harness (Playwright and a local PHP binary required):

```sh
BEXSTAR_TEST_NODE_MODULES=/path/to/node_modules node tests/tracking/browser.cjs
```

Optional BEXSTAR_TEST_PHP, BEXSTAR_TEST_CHROMIUM and BEXSTAR_TEST_SCREENSHOTS configure
local executables/output. It renders actual PHP view and JS with synthetic REST
responses at 1920/1536/1440/768/390/320, keyboard submit, safe text rendering and error/
empty/partial states. It never sends provider HTTP requests.

`tests/tracking/wordpress.php` runs real REST + SQL integration only against a local
WordPress installation with DB_NAME exactly `bex_tracking_test`, environment `local`
and WP_HTTP_BLOCK_EXTERNAL=true. It truncates only the two tracking test tables.
Run with BEXSTAR_TEST_WP_PATH pointing to that disposable installation. Never run
it on staging/production. Provider HTTP is intercepted with synthetic responses.

Validated: core/KQD regressions, all requested reference/error cases, actual public
schema, rate/lock/cache SQL, case-sensitive uniqueness, real REST GET/POST dispatch,
responsive layout and keyboard controls. Live provider acceptance, actual Hostinger
configuration, pending providers, external-partner auth and 17TRACK protocol remain
outside this foundation. No external credentials or carrier requests were used.
