# Editing and weekly operation
These instructions are for a future isolated staging WordPress installation.
No such installation was accessed or changed during Phase 1.

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
Development slot labels show only when WordPress environment is not production.
A developer may disable labels with the bexstar_show_media_labels filter.

## Articles every week
Posts → Add New. Enter title, meaningful slug, excerpt, featured image and category.
Use core headings starting at H2 inside the body (the template supplies H1).
Add paragraphs, tables, images, captioned video and a BEXSTAR Article enquiry CTA pattern.
Add factual service/destination links once those pages exist.
Preview, have claims reviewed, then publish. No GitHub, SSH or PHP edit is needed.
SEO fields are entered using the selected SEO plugin, not theme source.

## Navigation
The fallback menu previews the entire V3 taxonomy but does not create those Pages.
After Pages exist, a site administrator can assign a native hierarchical menu to
BEXSTAR primary navigation. Routine article authors do not need menu access.
Direct menu editor path: wp-admin/nav-menus.php. Confirm availability on the chosen
WordPress staging version; no claim of tested editor compatibility is made yet.

## Current limitations
Only phase-one homepage content is supplied. Legal text, contact channels, finished case
studies, customer accounts and functioning quote/tracking forms are not included.
Do not publish the development site publicly.
