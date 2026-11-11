# Phase 7 — Evacuation center management

## Setup and use

Fresh installations use root schema v1.3. Existing installations must back up their database and apply missing migrations in order (001, 002, then `database/migrations/003_phase_7.sql`). No real data or running database was modified during implementation.

Migration 003 adds reusable walk-in records, checkout staff attribution, and unique indexes for active stays. Before migration, use the query in its comments to identify duplicate active registered-member stays and reconcile them with staff. MySQL DDL is not transactional: if the ALTER fails after table creation, resolve the cause and run the remaining ALTER instead of repeating CREATE TABLE.

Admins open **Evacuation centers** to search names/addresses, add centers, or edit capacity/status. Select **People / history** for check-in, checkout, and previous stays. Private evacuee lists and every form are admin-only.

## Capacity and occupancy

- Capacity is a whole number of people, including infants; each person occupies one place. Zero capacity is permitted but accepts no check-ins.
- Occupancy is always calculated from evacuation records whose checkout time is NULL. No separate counter is maintained.
- Every check-in locks the center and reads current active records within a transaction before inserting. Parallel admissions cannot claim the same last place through this workflow.
- Editing capacity and closing a center use the same lock. Capacity cannot fall below current occupancy; centers must be empty before closure. Closed centers reject check-ins. Staff can reopen a center using its edit form.
- Checkout records the server timestamp and staff account on the existing stay. The stay is never deleted. A second checkout is rejected, and the record must belong to the center in the URL.
- To transfer someone, check out the original stay first and then check in at the destination; there is no automatic transfer operation.

## Registered members and walk-ins

Registered members are selected from the household registry, labeled with household code. Walk-ins can be selected from reusable walk-in records or entered with full name, age 0–130, home address, and a personal/guardian contact number. One form submits one person, not a household count.

New walk-ins receive a persistent ID. Re-entering the same normalized name, age, address, and phone reuses that ID. Database unique indexes permit only one active stay per registered member ID or walk-in ID across all centers, including concurrent requests. Historical stays remain unrestricted.

Identity limitations: these are records, not verified identity documents. Different spellings/details can create a different walk-in ID; a walk-in and a household-member record may represent the same person. Staff must inspect both lists and confirm identity before check-in. Reuse an existing walk-in ID for later visits, especially after age/address/contact changes. Do not create another record to bypass a duplicate warning. Distinct people with identical details require staff identity reconciliation; the form intentionally does not guess that they are different.

Existing legacy walk-in stays retain their name/contact and may have no reusable walk-in ID or age/address. They remain visible and can be checked out. A new walk-in matching an active legacy name/contact is blocked for reconciliation. Migration does not invent missing personal details or merge historical people automatically.

Household `evacuation_status` remains the manually maintained household advisory field. Actual people staying in centers are derived from evacuation records; checking in one member does not mark an entire household evacuated.

## Files and simple implementation

- `app/models/Evacuation.php`: PDO queries and transactional center/check-in/checkout writes.
- `app/controllers/EvacuationController.php`: admin gate, validation, same-page submission handling, redirects.
- `resources/views/admin/evacuation/centers.php`: searchable center directory and add/edit form.
- `resources/views/admin/evacuation/center.php`: individual check-in, checkout, and history.
- `database/migrations/003_phase_7.sql`: database changes; fresh schema includes them.

Ordinary forms and existing shared layouts are used. The only database-specific addition is generated active-person columns with unique indexes: checked-out records produce NULL, allowing unlimited historical visits while preventing duplicate active stays. Existing center map coordinates are preserved during edits.

## Verification status

Changed source and migration were reviewed by reading them. Automated tests, PHP execution, browser checks, migration execution, and MySQL concurrency checks were not run in this phase.

Before presentation, verify with fictional records: admin/resident access; center search/edit/closure; zero/full capacity; duplicate member/walk-in admission across two centers; concurrent last-place attempts; checkout and return visits; infant age zero; invalid fields; attempted capacity reduction below occupancy; legacy stay checkout; and history retention.
