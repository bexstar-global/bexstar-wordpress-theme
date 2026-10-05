> The adapter is now reused by the gated [Tracking API v1](tracking-api-v1.md).
> Public activation still requires explicit configuration and live acceptance.

# KQD — Phase 3A.1 controlled validation

## Scope and release gate

KQD (开渠达) implements the generic ProviderAdapter, registered by lowercase key
`kqd` (the business identifier is KQD). Loading the class does not register a public
adapter or enable requests. `/track/` and homepage tracking remain pre-live.
No credentials, real shipment responses, endpoint values or live calls are included.
The existing WordPress persistence layer supplies mappings and cache storage; this
command does not create shipment mappings or change the database schema.

## Reference and execution contract

The resolver uses the internal mapping first and injects `bexstar_reference` into
all leg references. `provider_tracking_number` is optional private metadata.
Lookup is format-agnostic, including numeric and provider-stored references. It
trims outer whitespace, preserves case/punctuation, and only rejects empty input,
invalid UTF-8, control characters or more than 128 bytes. Acceptance is not proof of
existence: the
mapping or explicitly selected provider must confirm it. New allocation still uses
BEXSTAR + MMDD + three digits, Shanghai date, atomic unique insertion and historical
no-reuse. Exhaustion remains an error. Public response schema accepts format-agnostic references. The canonical format is
also the existing 17TRACK BEXSTAR EXPRESS carrier recognition convention; it is not
a lookup restriction.
No provider inference or blind fan-out is introduced.

The CLI requires an existing explicit KQD mapping for one approved reference. It
uses InternalMappingResolver and TrackingService; missing or non-KQD primary mappings
fail before provider/cache access. It never creates or guesses mappings.

1. POST `gettrack`, `paramsJson={"tracking_number": bexstar_reference}`.
2. Return a nonempty successful direct result immediately.
3. Only successful empty `data` permits `gettrackingnumber` with `reference_no`.
4. Retry a nonempty, safe, distinct `shipping_method_no` if returned.
5. Query unique valid `packages[].child_tracknumber` values, at most eight.
   Never guess or substitute `child_number` / `channel_hawbcode`.
6. Use child timelines when available instead of duplicating the aggregate master.
   Retain same-looking events on separate pieces; deduplicate within a piece.
   Missing/failed children or truncated package sets mark `meta.partial=true`.
   A partial or mixed-status shipment is never declared wholly delivered.

One KQD lookup has at most eleven HTTP requests; the controlled command now shares
the service path with a 20-second / 12-call budget across legs, at most six seconds
per request, zero redirects, 1 MiB per response,
200 events per source, at most 100 package entries examined. HTTPS with verification
and WordPress safe HTTP private-address checks. No background retry loop.

## Envelope assumptions requiring live confirmation

The task supplied field names, not a complete response envelope or error dictionary.
The decoder accepts `success` 1, "1" or true and `data` as a shipment object or
single-object list. A successful null/empty-list/empty-string data value is no-result.
Missing data, malformed shapes, unknown envelopes and JSON errors are unavailable.
`success=0` is always unavailable: no guessed not-found/auth numeric code mapping.
An empty event list with an actual shipment object is a shipment, not automatically
not-found. Do not relax these rules based on matching provider error prose.
Before live acceptance obtain sanitized complete gettrack/gettrackingnumber success,
no-result and authentication-failure envelopes plus the official error dictionary.
If KQD uses nested/stringified data or success=0 for no-result, adapt the decoder
with verified samples and new tests before enabling public requests.

## Normalization

| Public field | Source / rule |
|---|---|
| tracking_number | Original BEXSTAR reference, never provider number |
| origin | origin_country, nullable |
| destination | destination_country_name, then destination_country |
| transport_mode | null until an authoritative mapping is documented |
| current_status | Exact track_status_ename semantic label; conservative multi-piece aggregate |
| last_updated | Latest valid event timestamp in UTC |
| events.timestamp | track_occur_date + explicit offset or gmt_offset |
| events.location | track_location, nullable |
| events.description | track_description_en; nonempty local track_description fallback |
| events.status / normalized_status | BEXSTAR normalized label/status |
| events.leg_id | Anonymous piece ordinal; never an internal identifier |
| meta.partial | Missing/failed/truncated child or malformed individual event |

Strict dates; numeric gmt_offset is treated as hours (including fractional hours).
Signed HH:MM and GMT/UTC-prefixed offsets are supported. Confirm units in KQD docs.
Missing/invalid offsets produce null timestamps; server timezone is never assumed.
Unknown timestamps sort after dated events, preserving source order among equals.

### Status mapper

Exact case-insensitive English labels map as follows. These are semantic-label
mappings, **not a claim about undocumented KQD numeric codes**.

| track_status_ename | Normalized status |
|---|---|
| Shipment created | shipment_created |
| Cargo received | cargo_received |
| Warehouse processing | warehouse_processing |
| Departed origin | departed_origin |
| In transit | in_transit |
| Arrived destination | arrived_destination |
| Customs clearance | customs_clearance |
| Customs released | customs_released |
| Out for delivery | out_for_delivery |
| Delivered | delivered |
| Exception | exception |
| Any other label/code | unknown |

Unknown events are preserved. Original rows are retained only in private request-local
normalizer memory, omitted from serialization/debug dumps, with no log persistence.
Numeric track_code/track_status and Chinese status names need an official dictionary
before adding mappings. Provider shipment IDs, signatory, POD URL and raw error text
are omitted. Descriptions are plain bounded text; known internal references, credential
values and KQD naming are redacted. Never expose transport responses or private
normalizer memory. Production display must use textContent/escaping, never raw HTML.

## Controlled Hostinger test setup (operator only; no deployment performed)

1. Use the WordPress installation's authenticated SSH shell with WP-CLI available.
   If the plan has no SSH/WP-CLI, arrange a private administrative CLI environment;
   do not replace this with a public debug page.
2. Configure server-side environment variables or constants outside Git, preferably
   in the private WordPress configuration before theme loading:
   - `KQD_API_ENDPOINT`: verified HTTPS base endpoint, without auth/query parameters.
   - `KQD_APP_TOKEN`: supplied privately by KQD.
   - `KQD_APP_KEY`: supplied privately by KQD.
   - `BEXSTAR_KQD_LIVE_TEST_ENABLED`: string `1` only for the manual test window.
   Constants take precedence over environment values. Never paste real values into
   this repository, tickets, screenshots, shell command arguments or transcripts.
3. Confirm outgoing HTTPS to the verified endpoint is permitted, TLS validation
   works, PHP CLI is 8.0+, WordPress/theme loads, and KQD has allowlisted the server
   IP if required. Ensure HTTP-debug/APM plugins do not record POST bodies/responses.
   Disable debug display and avoid shell tracing or recording sensitive output.
4. Supply the approved reference through an existing stored mapping with primary
   `provider_code` set to `kqd`. If it has not been imported, the operator must use
   the existing `wp bexstar tracking-map /private/path/shipment.json` workflow
   documented in [Tracking API v1](tracking-api-v1.md),
   with verified mapping data outside the web root. The test command never imports
   or repairs mappings. From WordPress root, run (substitute that approved reference):

   ```sh
   wp bexstar tracking-test-kqd '<approved-reference>' --approved --bypass-cache
   ```

   The command refuses without the server enable flag, credentials or `--approved`.
   It requires an existing KQD mapping, never creates mappings or changes public
   tracking switches, and uses normal cache writes, lookup counters and owned locks. The known
   manually verified reference is not embedded in production code; supply it only
   when approved for that run. Output contains `public_response` (the unchanged normalized API envelope) and a
   clearly separated `internal_diagnostics` object. Fixed normalized error codes
   and safe messages are printed on failure, never stack traces or raw provider errors.
   `--bypass-cache` skips only this lookup's cache read. A successful result still
   refreshes the normal cache; other lookups retain normal caching. Omit the flag for
   a second approved lookup of the same reference to validate a cache hit.

   Diagnostics distinguish `source=provider_request` from `source=cache_hit` and
   include only request count, bypass flag, safe authentication-test evidence and
   the latest public-safe timed event. A cache hit always reports
   `live_authentication_test=not_performed_cache_hit`. A partial result reports
   `incomplete_partial_result`; otherwise a fresh success reports
   `provider_response_received`, not a claim that all provider scenarios passed.
   `latest_event` is the event with the newest valid timestamp, or null. Untimed
   events remain in `public_response.events` but cannot become latest. Public
   `current_status` and `last_updated` remain unchanged, with no new public field.

5. Verify actual events, timestamp offset, status, direct-call success and sanitized
   output against the operator's provider view. Test fallback separately only with
   an explicitly approved reference known to need it. Do not publish raw responses.
6. Remove/unset the test enable flag immediately. Public activation requires a
   separate approval and completion of endpoint security, storage and acceptance.

Real live validation is PENDING. Synthetic tests cannot establish API compatibility.
No live requests were made as part of this change.

## Offline verification

```sh
php -r "define('BEXSTAR_TRACKING_TEST',true); require 'tests/tracking/kqd.php';"
php -r "define('BEXSTAR_TRACKING_TEST',true); require 'tests/tracking/controlled.php';"
BEXSTAR_TEST_NODE_MODULES=/path/to/node_modules node tests/tracking/schema.mjs
node scripts/validate.mjs
```

Tests cover direct in-transit/delivered, genuine empty result, auth failure, malformed
JSON/envelope, missing English/location, duplicate events, master fallback, multiple
children, partial failure, canonical/legacy references, timeout/unavailable, invalid
dates/offsets, privacy projection and HTTP request safety via stubs. Fixtures use
synthetic data only; `.invalid` endpoint and dummy credentials never leave the process.

Still needed: verified endpoint and private credentials on server, complete sanitized
response envelopes, status/error dictionary, gmt_offset units, request quotas and
child/master semantics. Do not add guessed numeric mappings to pass a live test.
