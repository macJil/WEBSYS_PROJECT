# Code walkthrough

This explains the current team-maintained application. [Complete source companion](SOURCE_COMPANION.md) includes the exact custom PHP, JavaScript, CSS and fresh schema with file/method navigation. Vendor internals are excluded: Bootstrap supplies styling/navigation and Leaflet supplies map rendering. Local credentials and uploaded files are never included.

## Request flow and PHP vocabulary

Browser opens a direct PHP view → view loads controller → controller checks session/role and interprets input → model runs PDO queries → view renders escaped results. POST saves go through the same page and redirect with HTTP 303 so refresh normally repeats GET rather than the save. There is no Laravel runtime, routing registry or build pipeline.

`require_once` loads a dependency once. `__DIR__` makes file paths relative to the PHP file instead of the browser URL. `$this->model` is an object property; `->` calls its method. `?int` allows an integer or null; null IDs usually mean create rather than edit. `: array` declares the return type. `??` supplies a fallback only for missing/null values. `?:` chooses a fallback for a false-like result. `foreach` reads each row/field. `throw` stops the current operation; `catch` converts a failure into an error or rolls back a transaction. Private methods are internal building blocks; public methods are called by controllers/views.

PDO's `prepare()` creates a statement, `execute([$value])` binds values to placeholders, `fetch()` reads one row, `fetchAll()` reads rows and `fetchColumn()` reads one value. Table identifiers cannot be bound: removal therefore chooses tables only from its fixed TYPES list. Search helpers escape %, _ and ! so searches treat typed wildcards literally. Parameters must not be concatenated into SQL.

A transaction groups writes: beginTransaction → validation/locking reads → writes → commit; any exception triggers rollBack. FOR UPDATE locks relevant records while capacity/stock is being checked. This matters when two people submit at once. JavaScript confirmation is usability only; PHP still guards roles, IDs, visible selection, active state and links.

## Shared foundation

- `config/config.example.php`: copyable returned settings array; teammate's config.php is ignored. base_url affects links, database fields affect PDO, upload path is private filesystem storage, map settings affect initial view.
- `config/db.php`: `db()` lazily creates/reuses PDO for the request. Exception mode avoids silent failures, associative fetches give named fields, native prepares keep binding explicit. Model constructors optionally accept a PDO so tests use an isolated database.
- `app/helpers.php`: loads config and starts a strict-mode session. `e()` escapes output, `url()` prepends base_url, `redirect()` sends 303, `isPost()` distinguishes writes, `input()` accepts scalar posted strings and optionally preserves password whitespace. Date/phone functions validate format. `flash()` stores a one-use success notice. `session_checker()` requires login; `requireRole()` checks the exact role and processes logout. `dashboardPath()` chooses a role home. `locationErrors()` checks paired coordinates/ranges/written description; `hasMapPoint()` identifies usable markers including zero coordinates. The exception handler returns a controlled generic error, not SQL or credentials.
- `Controller::success()` combines flash and redirect. `availableCapacity()` returns zero for closed/archived centers and otherwise clamps capacity minus active occupancy to zero.

## Login and registry

`UserController::handlePost()` accepts login/register only, validates fields and passwords, hashes registration passwords, verifies login hashes and checks active/allowed role. It regenerates session ID and stores identity/role. `UserModel::findByEmail()` looks up login; `createResident()` fixes the new account role to resident. Root index.php displays both forms and returned errors.

`HouseholdController` parses positive IDs, loads a household or returns not-found, lists/searches, validates household fields and member data, and redirects after saves. `HouseholdModel::getAll()` and `findById()` calculate member counts through active member rows. `save()` constructs a known field list, binds all values and either inserts or updates. `members()` excludes archived members; `findMember()` also checks household ownership. `saveMember()` refuses archived records. Household details and member forms share household.php. SQL foreign keys connect members and distributions to households.

## Incident and response code

`ReportController::owner()` returns the resident ID, or null for staff. That owner flows into list/detail/image model queries; changing a URL cannot grant access to another resident's report. `filters()` validates type/status/date/search; `values()` rebuilds form data. `create()` validates the report, calls upload and model create, removes a newly stored file if saving fails, then redirects.

`storage()` requires an absolute folder outside both project and web root. `upload()` checks PHP upload errors, genuine uploaded-file status, 1-byte–5-MiB size, MIME using fileinfo and image structure using getimagesize. It generates a random filename and moves the file privately. `serveImage()` checks ownership, validates the stored filename, reads private storage and sends its recorded MIME. Images are not public URLs under uploads/.

`ReportModel::create()` inserts incident and optional image reference in one transaction. `review()` locks the incident, checks active assignments, updates review fields and appends response_updates. `updateLocation()` locks, changes text/point and appends old/new location history. `listing/find/image` implement scope; `images/history` supply private details. Pending is the initial state.

`ResponseController` loads teams/incidents, validates save and assignment actions, and returns readable errors. `ResponseModel::record()` locks the incident, requires verified/responding, validates active team or existing assignment, prevents duplicate assignment, enforces status transitions, writes assignment state and appends action history. Assigned/on_scene makes the incident responding. Completing a team does not automatically resolve the incident. `teams/team/saveTeam`, `assignments` and `history` supply the team editor and incident response page.

## Evacuation code

`EvacuationController::handleStay()` dispatches household check-in, walk-in check-in and checkout. `saveCenter()` validates name/address/capacity/status/coordinates. `failure()` translates expected business and duplicate-key errors. Its read methods provide centers, households, members, walk-ins and stay history.

`EvacuationModel::lockCenter()` locks a center; `occupancy()` counts active stays through a current locking read. `saveCenter()` rejects capacity below current occupancy or closing an occupied center. `householdMembers()` returns active members from an active household.

`checkinHousehold()` validates nonempty unique positive member IDs, locks center/household/members, checks open state, membership, duplicate active stays and capacity for the whole selection. It inserts one stay per arriving person in one transaction. `checkin()` supports walk-in identity matching/reuse and the older individual-member entry path; `checkout()` ends one active stay and records staff. `records()` joins person/household/encoder/check-out names while preserving history. The browser household-checkin script previews selection totals; server calculations are authoritative.

## Relief code

`ReliefController` validates identifiers, dates, integer quantities, good name/unit and distribution input. `stock()` accepts stock_in/stock_out, and `distribute()` builds a good-ID → quantity list. It rejects invalid or missing household/receiver/goods before model writes.

`ReliefModel::saveItem()` prevents a used good changing unit. `stock()` locks the item, computes next quantity, rejects negative/overflow stock, updates balance and records a movement atomically. `distribute()` validates all quantities, locks the active household and goods in ID order, verifies every balance, creates distribution plus per-good details, deducts all quantities and records movements. It commits only after all succeed. `movement()` is the shared insert helper. List/detail methods join item, household and staff names without deleting history.

`ReliefLocationController` validates a location's name/type/address/point and guards edits. `ReliefLocationModel` lists/finds/saves storage, distribution or both locations. One barangay-wide inventory remains: these locations do not create separate stock balances.

## Announcements and summaries

`AnnouncementController` validates title/message/actions and requires confirmation to withdraw an already-published notice. Model `listing()` applies active status plus resident published-only restriction; `save()` inserts/updates and retains first_published_at. Editor is a guarded shared partial, while the resident page displays published notices.

`DisasterSummaryController::period()` validates actual calendar dates and converts an inclusive end date to next-day exclusive midnight. `summary()` delegates to the model. `DisasterSummaryModel::summarize()` uses a read transaction for a consistent set of affected-household, center, incident and distribution queries. Active occupancy is a join restricted to unended stays. Relief totals join distribution details and group by item/unit. Archived incidents remain in historical incident statistics. The view prints only totals and facility/item names.

## Map, dashboard and removal

`MapController::mapData()` requires login. `MapModel::publicMap()` explicitly selects safe incident fields and eligible statuses; centers are open and active with valid points; relief points are active distribution/both. `point()` builds a fixed range/null condition. `reliefLocation()` loads a staff-only detail. data.php returns JSON with no-store and rejects unauthenticated/non-GET requests.

`DashboardController::dashboardData()` selects staff-wide or resident-owned incident counts. Staff receive affected households and active stays; residents receive published notices. `DashboardModel` queries these rows; card rendering uses the same meanings and matching list links. The capacity helper prevents definitions drifting between model outputs.

`RecordActionsModel::listing()` uses an allowlisted UNION ALL for All record types, ordinary bound search terms and a 50-row page. Each row carries type and ID, so equal IDs from different tables stay distinct. Controller `delete()` validates selected type:ID keys against displayed rows and groups IDs by type. `deleteSelected()` locks/checks the entire batch first. `removalMode()` blocks active use with steps, then chooses permanent delete for unused records or hide/deactivate for linked/published records. Only after every record passes do writes occur. Removing a linked household also hides its members. The transaction preserves all-or-nothing behavior.

## JavaScript, CSS and views

`community-map.js` loads saved data, builds independent marker groups, safe text popups, a legend, layer toggles, fit-all and demo-home controls. `location-picker.js` initializes each picker once; map click/drag/device callback updates coordinates and starts a debounced address lookup. Cancellation/version checks ignore outdated responses and protect later typing; a cache and limits reduce requests. Clear/submit cancels lookup. Errors leave manual inputs usable. `maps/address.php` validates coordinates/login, limits requests per session, releases the session lock, calls a fixed Photon URL with cURL timeouts and returns JSON. No browser API key is used.

`incident-location.js` displays a saved point. `household-checkin.js` previews arrivals/capacity. `record-selection.js` maintains checkbox totals and select-page; submit with no selection alerts, otherwise confirm() offers OK/Cancel. `print-summary.js` invokes printing. The shared CSS defines cards, focus/button styling, spacing, maps, responsive forms and table scrolling; print CSS adjusts summary output.

Views perform page-level setup/guards and call controllers before output. Header includes styles and navigation; navigation chooses role links; errors/messages display escaped notices; footer closes markup and loads Bootstrap. location-picker and saved-point are reusable map fragments; dashboard-cards is shared count markup. Forms carry action names matching controller branches. Detail pages load IDs through validation and do not trust hidden inputs alone. records-all.php is a guarded redirect retained for older URLs.

## Database and source reference

The 17 tables are users; households; household_members; incident_reports; incident_images; responders; incident_assignments; response_updates; announcements; evacuation_centers; evacuation_walkins; evacuation_records; relief_items; relief_locations; relief_distributions; relief_distribution_items; relief_stock_movements. The schema in the source companion shows exact fields, defaults, indexes and foreign keys. Multiple goods, multiple teams and separate stays need their own rows; removing these tables would lose required workflows.

Read the source companion in this order: helpers/config → User → one view/controller/model trio → transaction modules → map scripts → schema. It includes every maintained source file and named PHP method so members can trace all code without relying on outdated phase descriptions. Tests are separate from runtime code and never belong in normal user navigation.
