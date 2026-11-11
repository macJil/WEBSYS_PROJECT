# Phase 8 — Relief inventory and distribution

## Setup and use

This phase uses the existing Phase 2 relief tables; no new database migration is required. Sign in as admin and open **Relief goods**. All inventory, movements, distribution forms, and recipient histories are staff-only.

1. Add goods with a name and unit, such as food packs / packs, drinking water / bottles, or blankets / pieces. Goods begin with zero stock.
2. Open **Stock movements** to receive supplies with stock-in. Use stock-out for damage, expiry, or another non-household issue; notes/reason are required.
3. Open **Household distributions**, select a registered household, and enter the person who actually receives the goods. Register an affected household in the registry first if it is missing.
4. Enter quantities beside one or more goods. Blank/zero quantities are omitted. Save once to record the distribution and deduct its goods together.
5. View the saved distribution to see household, representative, staff, date, notes, and all goods. Movement history links back to that distribution.

Dates/times come from the database at save time; staff IDs come from the session. There is no backdating control in this phase. Keep the local database/server time zone configured consistently across team machines. Quantities are whole units; package contents are not converted automatically.

## Stock consistency

Stock-in, manual stock-out, and distributions use transactions and lock item rows before checking or changing balances. A distribution locks all selected goods in ascending item-ID order. It checks every quantity before inserting the event, details, linked stock-out history, and balance changes. If an item is missing, stock is insufficient, or any write fails, the entire transaction rolls back.

Manual stock-out also rejects insufficient stock. Stock-in rejects balances beyond the MySQL unsigned integer limit. Form quantities must be positive integers up to 1 billion; the overall stock balance can span multiple movements.

The item form cannot change quantity directly. Units cannot change once movement history exists, since doing so would reinterpret recorded quantities. Create another catalog item to use a different unit. Name edits are allowed; historical screens display the current catalog name, so use edits for corrections, not repurposing an item as unrelated goods.

Movement and distribution records have no edit/delete controls. Historical rows and foreign-key relationships remain intact. A mistaken stock movement can be offset by a documented opposite movement; this does not cancel or rewrite a household distribution. Distribution reversal is not implemented in this phase.

## Search and privacy

- Inventory: search item name or unit.
- Stock movements: search item name, notes, or staff; filter by movement type and recorded date.
- Distribution history: search household code or receiving representative; open an event for its goods.
- Admin guards execute before database access. All displayed user text is escaped and queries use PDO parameters.

## Code layout

- `app/models/Relief.php`: item storage, stock transactions, distribution transactions, and history queries.
- `app/controllers/ReliefController.php`: admin checks, validation, form processing, and redirect-after-submit.
- `resources/views/admin/relief/inventory.php`: catalog and search.
- `resources/views/admin/relief/movements.php`: stock entry and history.
- `resources/views/admin/relief/distributions.php`: multiple-good distribution, history, and details.

Ordinary same-page PHP forms are used, with no additional JavaScript or dependencies. Stock is barangay-wide, not separated by warehouse. Location-level inventory and batch/expiry tracking are outside this phase's scope.

## Verification status

Changed source was reviewed by reading it. Automated tests, PHP execution, browser checks, and MySQL transaction/concurrency checks were not run in this phase. No live stock or real household distributions were changed.

Before presentation, verify using fictional goods/households: add/edit/search; duplicate item/unit rejection; unit-change restriction; stock-in/out; insufficient stock; multiple goods in one distribution; rollback when any item is short; concurrent deductions; invalid/negative quantities; resident access denial; and matching inventory, movement totals, and distribution details.
