# Team self-test guide

Use an isolated development database and fictional records only. Copy it or take a backup before destructive tests. Do not use real emergency reports or contacts. Record each result with date, tester, URL, steps, expected result, actual result and screenshot.

## 1. Prepare

1. Start PHP/MySQL and complete the setup in the handbook. Confirm the intended branch and local URL.
2. Create Admin A, Resident A and Resident B using fictional names and example.invalid email addresses. Use separate browser profiles or log out between roles.
3. Prepare a small PNG/JPEG/WebP, a text file renamed .jpg, and an image over 5 MiB. Do not upload private photographs.
4. Make a checklist with columns: test ID, PASS/FAIL, actual result and defect reference. Stop and record a failure rather than changing expectations to make it pass.

## 2. Accounts and access

A1: Register Resident A; log in, log out and sign in again. Expect resident dashboard and session logout.
A2: Try wrong password, duplicate email and mismatched registration passwords. Expect understandable errors and no extra account.
A3: Copy an admin household/stock/records URL. Open it while logged out and as Resident A. Expect login redirection or denied access; no private data.
A4: Resident A submits a report. Resident B opens its detail and image URLs. Expect denied/not-found, while A and admin can view them.

## 3. Registry and evacuation (run in order)

H1: Add TEST-HH-A with five members and TEST-HH-B with one. Search/edit the address and emergency contact. Expect counts 5 and 1. Keep vulnerability manual.
E1: Create TEST-CENTER with capacity 5 and no coordinates. Expect occupancy 0 and available capacity 5 on its detail; it still contributes to dashboard totals.
E2: Select household A. All five members should be checked initially. Uncheck two and submit. Expect occupancy 3, available 2 and three new stay rows.
E3: Try checking in an already-active member together with a new member. Expect the whole batch rejected; occupancy stays 3.
E4: Check in the two absent members. Expect occupancy 5 and available 0.
E5: Try household B's member. Expect capacity rejection and no added stay.
E6: Check out one A member. Expect occupancy 4, available 1; previous stay remains in history. Check in B's member; occupancy returns to 5.
E7: Try lowering capacity below occupancy or closing the occupied center. Expect rejection. Check out everyone, then close it. Available capacity for that center must be 0 when closed.
E8: Reopen; test new and returning walk-ins, duplicate active stay prevention and individual checkout.
E9: After a completed stay, add another household member. The old stay must not silently change.

## 4. Incidents, uploads and response

I1: Resident A creates a fictional incident with written location and no point/image. Expect successful private detail; no map marker.
I2: Create another with map point and PNG. Refresh its detail; image and point persist. Admin can open it; B cannot.
I3: Test renamed text and oversized image. Expect rejection and no incomplete report. After any form error, choose the file again.
I4: Admin enters an in-person/phone report and searches by type/status/date/location.
R1: Verify I2. It appears automatically on the shared map. Pending/rejected reports do not appear.
R2: Create two teams and assign both. Expect responding status and two assignments. Add action notes; previous actions remain visible.
R3: Try resolving while an assignment is active. Expect rejection. Complete/cancel assignments, then resolve. The resolved marker remains until the incident is removed from active records.

## 5. Inventory and distributions

G1: Create TEST-PACKS (packs) and TEST-WATER (bottles). Stock in 10 and 20 respectively. Confirm movement date/staff/history.
D1: Distribute 3 packs and 4 bottles to household A, naming its receiving representative. Expect balances 7 and 16 and one distribution containing two details.
D2: Try 8 packs plus 1 bottle. Expect no distribution and balances still 7 and 16; no partial deduction.
D3: Try a nonexistent/unregistered household. Expect rejection.
G2: Record a justified fictional stock-out; check balance/history. Try changing a used good's unit; expect rejection to preserve past quantity meaning.

## 6. Announcements, summaries and dashboard

N1: Save a draft. Resident A should not see it. Publish, refresh as A, then confirm it appears. Edit the notice and verify updated text.
N2: Withdraw a published notice. Confirm withdrawal when required; it disappears for residents.
S1: Match incident cards with corresponding filtered lists; resident cards count only that resident's reports.
S2: Sum capacity minus occupants for OPEN non-archived centers only. Match the available-capacity card. Closed/archived centers contribute zero; no-point open centers still count.
S3: Check relief summary shows 3 packs and 4 bottles for D1, separately by unit. Failed D2 contributes nothing.
S4: Filter dates to include/exclude D1 and incident submission dates. Current occupancy and affected-household counts should not change merely because of a historical date filter.
S5: Open print preview. Expect readable totals and no reporter contacts, personal names or household codes.

## 7. Maps and address input

M1: Open map as each role. Expect Atok demo label, visible legend/layer toggles and valid saved points. Toggle a layer and Show all locations.
M2: On incident, household, center and relief-location forms, click the map. Expect coordinates and an editable address suggestion. Confirm it, save and reopen.
M3: Change a point, then immediately type an address. A delayed lookup must not overwrite the later typing.
M4: Use current location and allow permission only with fictional testing consent. Expect coordinates, accuracy text and a suggestion when available. Deny permission on a separate attempt; manual entry still works. Device location usually requires HTTPS or localhost.
M5: Disconnect internet or block the address service. Manual written location and coordinate entry must still save. Do not expect an automatic address while the provider is unavailable.
M6: Clear pin; written address stays, both coordinates clear. A half-coordinate pair must be rejected.
M7: Inspect the map-data response using browser Network tools. Incident fields should only be id, incident_type, latitude, longitude, status, report_date. No reporter name, phone, private description, image path or household layer.

## 8. Manage Records

X1: Search unused TEST records, choose All record types and select mixed types. No duplicate-ID confusion: a household #1 is different from a good #1.
X2: Click Delete selected and Cancel. Everything stays. Repeat and OK; unused records disappear permanently. Select none; expect a prompt.
X3: Bring a used good to zero through actual stock transactions. Delete it. It disappears from active inventory/selection, while past stock/distribution history remains readable.
X4: Select an unused test record plus an occupied center or stocked good. Confirm. Expect the entire batch unchanged and steps explaining how to clear active use.
X5: Finish a response, then remove its incident. Expect disappearance from active lists/map; historical response and image linkage remain stored.
X6: Remove a linked household only after its active occupants leave. Active household/member selection should exclude it, while historical stays/distributions retain references.
X7: Change filter/search/page; Select all selects only that displayed page, never the whole database.

## 9. Phone, desktop and final signoff

At approximately 390px and 1366px widths, check login, dashboards, every module form, map and records. Buttons must be reachable, labels readable, tables scroll inside their containers and Menu expand on phone. Test Back/Cancel destinations and successful save redirects. Repeat refresh after saving: it should not silently duplicate records.

Each member signs off their assigned tests; reviewer repeats at least one success and one rejection. Remaining failures get reproduction steps and an owner. Do not describe the website as fully verified while failed tests remain.

## Automatic checks

On the current Mac/MAMP setup run from the repository:

```sh
PYTHONDONTWRITEBYTECODE=1 python3 tests/household_map_persistence.py
/Applications/MAMP/bin/php/php8.3.30/bin/php tests/household_checkin.php
node tests/household_map_address.js
node tests/revision4_picker.js
node tests/revision3_map.js
/Applications/MAMP/bin/php/php8.3.30/bin/php tests/phase10_summary.php
/Applications/MAMP/bin/php/php8.3.30/bin/php tests/revision5_dashboard.php
```

The MySQL runners create/drop their own fictional databases, requiring create/drop permission. They currently assume the MAMP socket and development credentials in the test code; these are not deployment instructions. Other computers must adapt test connection settings first. Do not run old phase/revision scripts blindly: some target superseded publication/archive workflows.
