# Phase 9 — Emergency announcements

## Use

Admins open **Announcements** to create a notice, then choose **Save draft** or **Publish**. Open an existing notice to edit it. Published notices offer **Save and keep published** or **Save and withdraw**. Withdrawal retains the record but hides it from residents on their next page load. Residents open **Announcements** to read published notices only.

No database migration is required. The existing `is_published` flag represents published (1) versus private draft/withdrawn (0); withdrawal does not need a separate status. The original creator and creation timestamp remain unchanged when editing. Date filters explicitly use the creation date, not a publication date. Revision history and publication timestamps are not implemented.

## Privacy and validation

- Admin checks run before database access on every editor endpoint, including the shared form fragment.
- Resident listing always includes `is_published=1` in SQL. A submitted status filter cannot reveal drafts. Resident POST requests are rejected; editor URLs require admin role.
- Title/message are required and limited server-side to 150/10,000 bytes. Output is escaped plain text, with line breaks preserved. There is no HTML editor or executable message markup.
- Prepared statements handle search and writes. Publication state comes from one of two allowed form actions, not an arbitrary submitted flag. IDs come from the validated editor URL.
- Successful writes redirect back to the list. Failed validation keeps entered text. No delete control is provided.
- Resident responses use private/no-store caching. Withdrawal cannot erase a notice already visible on someone's screen or captured elsewhere; the updated visibility applies to subsequent requests.

## Search and classroom data

Admin search combines title, published/draft status, and creation date. Residents can search title and creation date across published notices. Empty searches show all notices permitted for that role.

No real contacts or seeded announcements were added. The editor shows this clearly fictional example: **DEMO ONLY — Sample DRRM Desk, demo@example.invalid**. Use the same explicit demo labeling for classroom notices; replace examples with stakeholder-validated contacts only when preparing actual deployment.

## Files

- `app/models/Announcement.php`: parameterized listing, lookup, and save queries.
- `app/controllers/AnnouncementController.php`: role guards, filtering, validation, save actions.
- `resources/views/admin/announcement/announcement.php`: create editor and searchable staff list.
- `resources/views/admin/announcement/announcement.update.php`: edit/publication controls.
- `resources/views/admin/announcement/editor.php`: guarded shared form.
- `resources/views/user/announcement.php`: published resident notices.

## Checkpoint and verification status

Source was reviewed by reading it. No automated tests, PHP execution, browser checks, or MySQL runtime checks were run in this phase. The requested visibility checkpoint is implemented but still requires runtime verification.

With fictional accounts, verify: save a draft and confirm it is absent for residents; publish and confirm it appears; edit published text; withdraw and reload the resident page; attempt resident access to editor URLs; try a draft status filter on the resident page; search by title/date/status; and confirm escaped markup and validation errors.
