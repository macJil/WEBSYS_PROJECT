# Phase 4 — Household and resident registry

Staff entry: `resources/views/admin/households.php`, also available through the admin navigation. View a household and its members at `resources/views/admin/household.php?id=...`.

Requires the Phase 2 v1.1 schema and Phase 3 local database configuration. No schema migration is needed for this phase.

## Delivered workflow

Admins can add, view, edit, and search households by code, representative, purok, address/landmark, or contact number. Search uses prepared parameters and treats percent/underscore as literal text. Household codes are unique. A new household starts with zero recorded members; the representative must also be added as a member if they live there.

The representative name and contact number supply the initial emergency contact details. Address and location description share the address_details field. Existing optional coordinate values are preserved on edits; map selection belongs to a later phase. The form records monitored/affected/both, hazard type/notes, vulnerability assessment, and household advisory status.

Admins can view, add, and edit named members with optional age and notes. Blank age is NULL; zero is accepted for infants; other values must be whole numbers between 0 and 130. Editing preserves the member ID and household relationship for future evacuation records. There are no deletion or household-transfer actions in this phase.

Member counts are calculated from household_members rows on every read. There is no manual total to become inconsistent. Names do not need to be unique: different people may share a name.

## Assessment and privacy

The assessment categories follow Phase 1. An assessed category requires notes identifying the source and practical assistance needs. The current admin ID and assessment date are recorded when the category or notes change. Editing unrelated fields preserves existing assessment metadata. Selecting unassessed clears assessment notes, author, and date, as explained on the form. This is the current assessment snapshot, not an audit-history module.

The HouseholdController constructor checks the admin role before creating a database connection. Both direct pages load that controller before processing parameters or returning private information. Unknown IDs produce 404; malformed IDs produce 400; residents receive 403 for both GET and POST. Member lookups/updates use both member ID and household ID so a member cannot be edited through another household's URL. No resident-facing registry or public household endpoint is provided.

Forms use ordinary same-page POST. Validation errors preserve entered fields, successful writes redirect with 303 and a one-time message, and all displayed record values are HTML-escaped. PDO failure details are logged rather than printed in pages.

## Files for the team

- `app/models/Household.php`: household and member PDO queries, calculated counts, search and persistence.
- `app/controllers/HouseholdController.php`: staff guard, validation, assessment metadata, and redirects.
- `resources/views/admin/households.php`: searchable registry and create/edit household form.
- `resources/views/admin/household.php`: household details, member list, and create/edit member form.
- `resources/views/components/navigation.php`: admin registry link.

## Verification

PHP syntax and diff checks passed. HTTP checks with an isolated SQLite PDO fixture passed for anonymous/resident denial; household create/edit/search; duplicate household codes; escaped text; member create/edit; calculated count; invalid age; assessment metadata retention/reset; cross-household member mismatches; and missing/malformed IDs. All records were fictional and outside the repository.

MySQL runtime verification is still outstanding; fixture checks do not prove a MySQL import or live MySQL behavior. No running barangay database was modified. Before the classroom demo, run the same workflows using the team's configured MySQL development database.
