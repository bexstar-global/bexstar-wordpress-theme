# Sample parcel calculator (phase 1)

The page template `page-sample-calculator.html` renders `[bexstar_sample_calculator]` at
`/sample-calculator/`. It covers express parcels and air parcels from China to the
UK, US, Australia, UAE and Europe. Customers choose a named European country or
enter another European destination. The calculator accepts a destination postal
code when known, cargo type, number of identical parcels, weight and dimensions.
It computes total actual weight and parcel volume in the browser.

Phase 1 does **not** calculate, store or display prices, chargeable weights, transit
promises or provider names. Parcel input stays in the browser until the customer
submits the separate inquiry form. Customers may copy a
shipment summary and follow the existing Get a Quote link; they enter their
contact details on that separate inquiry form. After a valid review, clicking
Request a Quote carries parcel fields through same-tab session storage, never a
URL parameter. The quote form shows the transferred fields for review, then
removes the temporary data. It does not auto-submit or store contact details.
The navigation link resolves to the published page and falls back to the home
page while that page is unavailable. No WordPress database page is created by
theme deployment.

## Development-site publication

After the theme change is approved and deployed to the isolated development site:

1. In WordPress, create a page titled “Sample Shipping Calculator” with slug
   `sample-calculator`. Confirm it uses the page-specific template and that the
   shortcode renders. Publish only after verifying desktop and mobile layouts.
2. Enter a 1-parcel and multi-parcel example for each destination group; check
   actual-weight and volume arithmetic, validation and the copy action.
3. Check the Request a Quote link and a real inquiry receipt. The calculator
   itself does not submit an inquiry.
4. Confirm the Shipping menu exposes the calculator only after it is live.

Production bexgl.com is outside this workflow.

## Reserved 报价猫 integration

The form field names are the future input contract: `country`, `other_country`,
`postal`, `service`, `pieces`, `weight`, `length`, `width`, `height` and `cargo`.
`weight` is kilograms per parcel and dimensions are centimeters per parcel.
The selected service is `express`, `air` or `both`. The browser currently computes
only actual weight and total volume. No rate endpoint, token, API request or
customer price exists in phase 1.

When a documented 报价猫 integration is available, implement it in a separate
server-side WordPress plugin. Its adapter will convert the form fields to the
provider's request and normalize the response into either an approved retail
price or a `manual_quote` result. The public response may contain the service
label, customer currency/amount, validity and customer-facing exclusions; it
must not contain wholesale cost, markup, supplier identity, channel code or API
credentials. Rate limits, timeout handling and a manual-quote fallback belong
in that plugin. Confirm the actual provider API and permitted data use before
building the adapter. The theme can then display the normalized result without
changing the customer's input fields.

## Later customer-visible rate lookup

Keep approved **retail** rates in a WordPress administrator-only rate manager,
separate from wholesale supplier files. The manager should import a normalized
retail CSV, validate country/postcode zone, service, weight brackets, dimensional
divisor, rounding step, size limits, currency, inclusions and extra-charge rules.
Each release needs an effective date, expiry date, preview calculation and an
explicit publish step; expired or unmatched rows return a manual-quote result.
The public endpoint returns only an eligible customer rate and plain exclusions.
It never serves the underlying table, supplier identity, markup or cost formula.
Retain a previous published version for rollback. If 报价猫 becomes the
authoritative retail-price source, use it instead of maintaining duplicate
prices in WordPress.

Do not upload wholesale price lists to the WordPress Media Library or commit
them to the public GitHub theme repository. Some partner sheets include
low-weight sample bands, but they are wholesale inputs rather than a complete,
approved customer tariff for every requested destination and cargo type. A
retail price policy and route-coverage review are required before any amount
is shown.
