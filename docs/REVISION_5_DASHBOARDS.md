# Revision 5 — Home dashboards

Admin home now shows pending, verified, and responding incidents; current affected households; active evacuees; open centers and available places; and low-stock goods. Resident home shows only the account's own incident statuses, published notice count and latest three titles, and public open center count. Both retain the shared Map link.

Cards link to the matching incident/stock filters, current household/evacuee lists, published announcements, or the shared open-center list. Center availability is calculated from active stays. Closed/archived centers are excluded from open-center totals; active stays are never hidden just because a related record is archived. Residents see no evacuee names or private household counts. Centers without map points remain in the open-center list and count.

Relief item add/edit now accepts minimum_stock (0–1 billion whole units). Zero disables warnings; stock strictly below the positive threshold is low. Equal quantities are not low. Each threshold uses the item's own unit. No stock quantities from different units are summed. Inventory offers a low-stock checkbox, and archived goods are excluded from active selections. Current incident lists and announcement lists exclude archived records to match current cards. Historical distribution/movement queries remain unchanged.

No new migration: minimum_stock and archive fields were added in migration 004, which must be applied first. Implementation uses direct guarded PHP pages, models and ordinary forms; no chart dependency or background polling.

Verification: syntax checks passed across app/views. Eight fictional SQLite checks passed using the real models: role/account count scope, matching incident/low-stock lists, open/public center eligibility, active occupancy, affected households, published notices, and threshold persistence. Run `php tests/revision5_dashboard.php`. Browser layout, live HTTP navigation and MySQL runtime verification remain pending for this revision.
