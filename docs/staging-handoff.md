# Future safe staging connection — plan only, NOT executed
No Hostinger login, deployment, DNS change or production WordPress access occurred.

## Before any connection
Obtain separate approval for the exact staging hostname and destination directory.
Inventory existing production theme, Elementor templates, plugins, pages, URLs, forms and
uploads through an explicitly authorized read-only export. Back up files AND database.
Do not assume GitHub is a complete WordPress backup.

## Isolated environment
Create a separate test WordPress instance with its own database, file tree and credentials.
A sanitized copy may be used after approval; remove customer data and credentials.
Use authentication/access control plus search-engine noindex; robots.txt alone is not privacy.
Disable outbound email, CRM, payment and webhook actions on staging.
Do not point staging at the production database or overwrite wp-config.php or uploads.
Confirm PHP ≥8.0 and WordPress ≥6.6, with a currently supported release preferred.

## Repository mapping
Repository root → the STAGING installation's wp-content/themes/bexstar/.
No nested bexstar/bexstar folder. Do not automatically use production public_html.
No deployment workflow is included. Do not configure an automatic main-branch deployment.
First upload/install requires its own approval.

## Test gate
1. Run PHP lint, node static checks and activate ONLY in isolated staging.
2. Confirm WordPress recognizes a block theme and reports no fatal errors.
3. Open Site Editor; verify pattern editing, custom media controls, preview and save/reload.
4. Verify desktop mega menus and mobile Menu/Track/Quote/Contact, keyboard focus and Escape.
5. Test published/draft page resolution and WordPress installed in a subdirectory.
6. Check single/archive/search/no-results/404 pages, pagination and plugin compatibility.
7. Check 320/375/768/1024/1440/1920 layouts with missing and real media.
8. Test poster/video failures, reduced motion, no JavaScript and media-library srcset.
9. Audit SEO metadata once a plugin is configured; add approved legal/contact content.
10. Confirm existing Elementor/SureForms behavior and migrate explicitly; do not bulk overwrite.

## Rollback
Retain a known-good theme package and database backup before activation. Restore the prior
theme and revert staging template overrides if needed. Production migration needs its own
URL redirect plan, form-delivery test, backup, rollback window and explicit sign-off.
