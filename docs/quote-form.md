# Native quote form

The Get a Quote template uses the core Shortcode block with `[bexstar_quote_form]`.
No plugin, credentials, uploads, accounts, CRM or payment collection is added.
The repository contains no reusable form plugin configuration. Its migration docs
explicitly exclude existing SureForms settings; the public quote page inspected
before implementation contained no form. Authenticated plugin inventory was not
available, so no existing plugin was disabled or changed.

## Validation and delivery

POST processing runs only on the `get-a-quote` page. Destination country and postal
code are required, with at least one valid WhatsApp number or email. WhatsApp must
include a leading + and country code. An email entered alongside WhatsApp must
also be valid. Package details require five positive numeric values (pieces is a
whole number); Total cargo requires positive weight and CBM. Only the selected
method is validated and sent. Switching methods preserves unsent values in the
browser. Without JavaScript both methods are visible and server validation still
requires only the selected one. Optional information is collapsed initially.

The handler verifies a WordPress nonce and empty honeypot, accepts only scalar
text values, sanitizes input, validates numeric and enumerated fields, and escapes
output. Submitted personal data is not placed in URLs, stored in the database or
logged by this implementation. Failed submissions retain sanitized values in the
response. The quote page sends no-cache headers and defines DONOTCACHEPAGE;
exclude `/get-a-quote/` from any upstream full-page cache that ignores these signals.

Notifications go to `quotes@bexgl.com` through `wp_mail()`, with a fixed subject
and a validated Reply-To only when an email was supplied. Each notification has
Contact, Destination, selected Cargo data and Optional information groups.
Success is displayed only when wp_mail returns true. This means the WordPress
mail transport accepted the message, not that inbox delivery has been verified.
Failures display an error and the Contact link. Form data stays in the POST
response; a browser-confirmed resubmission can send another inquiry.

## Validation performed

- PHP syntax checked with PHP WASM CLI.
- Run `php scripts/quote-form.test.php`: 34 checks with mocked WordPress APIs and
  mail transport. Covers WhatsApp-only, email-only, neither, both cargo methods,
  each missing cargo field, invalid email, header injection, numeric errors,
  nonce/honeypot rejection, mail success/failure, escaping and retained values.
- Local browser preview of PHP-rendered form: 1440, 1536, 1920, 768, 390 and 320px;
  no horizontal overflow; visible method only; keyboard radios/disclosure;
  contact OR logic, native email/numeric validity, values retained on switching.
- Preview uses theme CSS and a lightweight base typography stylesheet; it is not
  a complete running WordPress installation or a deployed acceptance test.

## Staging acceptance after authorized installation

1. Confirm the published `/get-a-quote/` page uses BEXSTAR — Get a Quote. Inspect
   saved Site Editor template overrides if the old placeholder remains; do not
   delete customizations blindly.
2. Verify the shortcode renders and the nonce is fresh while logged out. Check
   the full page with its shared header/footer on desktop and mobile.
3. Submit test inquiries with WhatsApp only and email only, using each cargo method.
   Confirm receipt at quotes@bexgl.com, section contents and Reply-To. No real
   customer or business email was sent during repository testing.
4. Verify a controlled mail transport failure shows the error instead of success.
   Use the site's existing approved mail setup; no new SMTP plugin is required
   by this code and none was installed.
5. Test with JavaScript disabled, then keyboard-only. Verify invalid requests do
   not generate notifications. Check upstream caching excludes the quote route.

No deployment is included in this change.
