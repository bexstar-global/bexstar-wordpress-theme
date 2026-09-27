# Quote and Contact page foundation

These block templates reuse the existing header, footer, reading container and
responsive styles. They contain one dynamic page-title H1 each. No form, contact
details, new CSS, JavaScript or database-writing hooks are included.

| Route | Page title | Slug | Template shown in the editor |
| --- | --- | --- | --- |
| `/get-a-quote/` | Get a Quote | `get-a-quote` | BEXSTAR — Get a Quote |
| `/contact/` | Contact | `contact` | BEXSTAR — Contact |

## WordPress admin setup

Repository files do not create WordPress database pages. After the updated theme
has been installed through the site's normal approved deployment process:

1. Sign in to the development site's WordPress admin. Confirm BEXSTAR is active
   under Appearance → Themes.
2. Open Pages → All Pages. Check published pages, drafts and Trash for either slug
   before creating anything. Reuse an existing page where appropriate.
3. Open Pages → Add New. Set the title to **Get a Quote**. In the editor's Page
   settings, set the URL/slug to `get-a-quote` with no parent page. Leave the page
   content empty for now: the template already supplies the introduction and
   explicit non-functional quote-form placeholder.
4. In Page settings → Template, use the template selector (sometimes labelled
   **Swap template**) and choose **BEXSTAR — Get a Quote**. Save as a draft and
   Preview. Confirm one title, shared header/footer and the quote availability
   notice. Do not insert the same introduction into the page content.
5. Repeat for **Contact**, slug `contact`, no parent, using **BEXSTAR — Contact**.
   Leave its content empty until verified contact details are available.
6. Publish each page only when ready to expose these foundation pages. Confirm
   their permalinks are exactly `/get-a-quote/` and `/contact/` (not suffixed with
   `-2`). Draft/private pages do not provide public working routes.
7. Verify both public URLs while logged out. If a published route returns 404,
   check its slug and publication status first. If necessary, open Settings →
   Permalinks and click Save Changes without changing the existing structure.
   Do not change the site's homepage selection or production domain.
8. If the editor offers an existing customized template with the same name,
   inspect it first: saved Site Editor templates can override theme files. Do
   not reset or delete template customizations blindly.

The `page-{slug}.html` names also support WordPress's automatic page-slug template
selection, but explicitly selecting the registered template makes the assignment
clear. Standard WordPress document-title handling remains in use.

## Existing CTA behavior

No homepage or CTA code is changed by this foundation. The existing dynamic
customer-actions block enables a button when its matching page is published;
navigation helpers likewise start resolving to that page's permalink. Publishing
these pages can therefore activate existing homepage links automatically, even
though quote submission and contact details remain unconfigured. Keep pages as
drafts if that activation is not yet desired.

## Future content areas

- Quote: `#quote-form` contains the page-content block where a future approved
  form block can be inserted. Remove the template's unavailable notice only after
  that form is configured and its submission flow verified.
- Contact: `#contact-details` contains the page-content block for verified email,
  WhatsApp and business information. Replace the pending notice when real details
  are ready; do not invent them.

No pages were created or published by this repository change. No Hostinger
deployment is part of this task.
