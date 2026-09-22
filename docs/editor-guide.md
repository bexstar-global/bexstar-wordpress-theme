# Editing and weekly operation
These instructions are for a future isolated staging WordPress installation.
The owner reported Phase 1 activation on an isolated Hostinger development site.
This session has not accessed or modified that installation.

## Homepage
Open Appearance → Editor → Templates → Front Page. The initial template contains BEXSTAR
patterns that resolve to editable native blocks. Edit headings, paragraphs and buttons
visually. Save a template only after previewing desktop/tablet/mobile.
Database-saved template overrides take precedence over theme files: export/back up edits
before resetting overrides after a code update.

## Images and video
Select a BEXSTAR media slot. In the block sidebar choose its image/poster from the Media Library.
Choose an optional decorative MP4. Set alt text for informative imagery; keep decorative
images marked decorative. Focal points use two percentages, e.g. 60% 40%; mobile can differ.
Media Library images receive WordPress responsive image markup. Theme-bundled files are
a developer fallback, not the preferred editorial workflow.
Video requires a poster, starts only on user request and has play/pause controls.
For video with speech or essential information, use a core Video block with captions instead.
Media labels are OFF by default, including on development sites. To enable them, a developer
must explicitly return true from bexstar_show_media_labels AND use a local/development
environment. Production/staging environments do not show these labels.

## Articles every week
Posts → Add New. Enter title, meaningful slug, excerpt, featured image and category.
Use core headings starting at H2 inside the body (the template supplies H1).
Add paragraphs, tables, images, captioned video and a BEXSTAR Article enquiry CTA pattern.
Add factual service/destination links once those pages exist.
Preview, have claims reviewed, then publish. No GitHub, SSH or PHP edit is needed.
SEO fields are entered using the selected SEO plugin, not theme source.

## Navigation
The fallback menu preserves the V3 taxonomy but does not create those Pages.
Until a Page is published, links lead to the matching homepage overview, not to a 404.
After Pages exist, a site administrator can assign a native hierarchical menu to
BEXSTAR primary navigation. Routine article authors do not need menu access.
Direct menu editor path: wp-admin/nav-menus.php. Confirm availability on the chosen
WordPress staging version; no claim of tested editor compatibility is made yet.

## Current limitations
Phase 2 supplies homepage composition and global UI only. Legal text, contact channels, finished case
studies, customer accounts and functioning quote/tracking forms are not included.
Do not publish the development site publicly.

## Phase 2 case studies, actions and brand
Publish reviewed posts in the category with slug `case-studies`; the homepage selects the
latest three published posts. Add featured images and excerpts in Posts. No CPT is required.
The empty state never inserts fictional stories.
Final Get a Quote / Contact actions become links only when matching Pages are published.
If those pages do not exist, they show Coming soon without collecting information.
Legal links appear only for published policy pages (or the configured WordPress privacy page).
Set a Custom Logo for the header where available, or supply approved SVG files under
assets/images/brand/. The footer uses the light variant only, otherwise its text fallback.

## Applying theme updates without losing editor work
Saved Front Page/header/footer overrides in the WordPress database may supersede changed
theme files. Back up/export those overrides and compare them with the new patterns first.
Do not reset templates blindly. Insert updated BEXSTAR patterns into a staging draft or
selectively adopt the changed blocks. This code never deletes or resets editor content.
