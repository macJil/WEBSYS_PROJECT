# BayanReady manual test guide

Use this checklist on the `bayanready-atok-simple` branch. Test with **fictional records in a separate development database**. Never run delete/archive checks on real barangay data. Record the tester, date, browser, result, and any defect for each section.

## 1. Prepare the site

1. Start MAMP's Apache and MySQL. Open `http://localhost:8888/WEBSYS_PROJECT/index.php` (use your actual MAMP port if different). Confirm the login page says **BayanReady** and the footer says **Atok, Benguet (DEMO)**.
2. Confirm your ignored `config/config.php` has your local MySQL connection and `base_url` and points `incident_upload_dir` to a writable folder **outside** the web root. Do not share or commit this file.
3. For a fresh disposable database, import `barangay_drrm.sql`. For an older teammate database, back it up and apply only the missing migrations in order through `007_automatic_map.sql`. Do not import the fresh schema over existing records.
4. Register two fictional residents through the site: `Demo Resident A` and `Demo Resident B`, each with a unique email and password. Public registration must make both residents.
5. In the disposable database only, promote a separate fictional account to admin using phpMyAdmin: inspect its username, then run `UPDATE users SET role='admin' WHERE username='your_demo_admin_username';`. Log out and back in so the new role takes effect. Never expose an admin registration choice on the public form.

Suggested fictional names: household `DEMO-ATOK-001`, member `Demo Member A`, incident landmark `DEMO ONLY Sample Road`, center `DEMO ONLY Sample Shelter`, team `DEMO ONLY Response Team`, good `Demo food pack`, relief point `DEMO ONLY Distribution Point`. These are invented labels, not verified facilities or addresses.

## 2. Login, sessions, and roles

1. While logged out, open the Map, Households, Incidents, and Relief URLs directly. Protected pages should redirect to login; a map-data request should return an unauthorized response.
2. Log in as Resident A. Confirm the resident dashboard and resident navigation appear. Open an admin URL directly, such as `/resources/views/admin/households.php`; it must deny access. Repeat by submitting an admin form URL if you can safely do so in the disposable database.
3. Log out. A refresh or browser Back must not restore access to protected data. Log in again with the correct password; a wrong password must fail without revealing which account exists.
4. Log in as the admin. Confirm the staff dashboard and all module links work. Admin should not be able to open resident-only pages by changing a URL.

## 3. Household and resident registry

1. As admin, open **Households** and create `DEMO-ATOK-001` with a fictional representative, contact number, purok/address, registry reason **Affected**, vulnerability category, and notes. Save it and find it by search.
2. Open the household detail and add two fictional members. Confirm the displayed member count becomes **2**. Edit one member and the household, reload, and confirm both changes persist.
3. As a resident, try the household list/detail URLs. Personal details must be inaccessible. Household markers must not appear on the shared map.

## 4. Incident reports and images

1. As Resident A, choose **Report incident**. Enter type **Flooding**, a fictional description/contact, required written location, and a point near the Atok demo view by clicking the map. Attach a small fictional JPEG, PNG, or WebP image if available. Submit.
2. Confirm the report appears in **My reports** with status **pending**. Reload its detail page: the written location, saved point, and image should persist. The report must **not** appear on the map while pending.
3. As Resident B, try Resident A's report detail URL. It must be denied or not found; Resident B's list must not show it. An anonymous user must not reach its image URL.
4. As admin, find the report, inspect its details, and mark it **verified**. Reload the shared map: its warning marker should now appear automatically at the saved point. There is no Public/Private control.
5. Change the report to **rejected** in a disposable test case; its marker should disappear. For a separate verified report, test **responding** and **resolved**; its marker should remain. The resident map response must contain no reporter name, phone, description, image path, or household details.
6. Create a report with a written location but **no coordinates**. It should remain in lists/counts and show a “No map point” note, but have no marker. Test an invalid coordinate pair or oversized/wrong-type upload: saving must fail with a readable error. Admin can also enter a fictional phone/in-person report.

## 5. Emergency response

1. As admin, create `DEMO ONLY Response Team`, edit its contact, and verify it appears in the team list.
2. Open a verified incident's response page, assign the team, and record an action. Confirm the incident becomes/responds as **responding** and the assignment/action appears in history.
3. Add a second action. The first action must still be visible. Complete or cancel active assignments before resolving the incident. Deactivate the team and confirm it cannot receive a new assignment, while old history remains.

## 6. Evacuation centers

1. As admin, create `DEMO ONLY Sample Shelter` with fictional address, saved point, **capacity 2**, and status **open**. It should appear automatically on the map and in the resident open-center list.
2. Check in one registered household member and one fictional walk-in. Occupancy should become **2/2**. A third check-in and duplicate active check-in must be rejected.
3. Try lowering capacity below occupancy or closing the occupied center; both must be rejected. Check out one person, confirm occupancy becomes **1/2**, and verify the completed stay remains in history.
4. Check out the second person, close the center, and reload the map. Its marker should disappear; past stays must remain readable.

## 7. Relief inventory and distribution

1. As admin, add `Demo food pack` (unit `packs`) and `Demo water` (unit `bottles`). Record stock-in of **10** and **8** respectively. Verify each balance and movement history.
2. Record a manual stock-out of **2 food packs**. The food balance should become **8**. Attempt stock-out greater than the balance; it must fail without changing stock.
3. Add `DEMO ONLY Distribution Point` with type **distribution** and a saved point. It should appear automatically on the map. Change its type to **storage**; it should disappear.
4. Distribute **3 food packs** and **2 waters** to `DEMO-ATOK-001`, recording a fictional receiving representative. Balances should become **5 packs** and **6 bottles**; one distribution should list both goods and the matching stock movements.
5. Try a distribution with an insufficient quantity or an unregistered household. It must fail as a whole: no partial distribution, stock movement, or balance change.

## 8. Emergency announcements

1. As admin, create a fictional preparedness notice as a **draft**. A resident must not see it.
2. Publish it. A resident should see it on the announcement page and dashboard. Edit the notice and reload; the published text should update.
3. Withdraw it. It must disappear from resident views while remaining in staff history/listing.

## 9. Disaster summaries and dashboards

1. As admin, open **Disaster summaries**. Check affected households against the registry, active evacuees against current center stays, incident counts by type/status, and distributed goods against the distribution history.
2. Change the date range. Confirm the incident/distribution period totals change as expected while current occupancy remains based on active stays. Use Print Preview: private household/member details should not appear in the printable summary.
3. Compare each staff dashboard card with its linked filtered list. Compare Resident A's report-status cards with **My reports**; Resident B must not see A's private totals. Open-center count should match the resident center list.

## 10. Map and location picker

1. Open the map as both roles. It should start around **Atok, Benguet**, with a legend and checkboxes for incidents, open centers, and relief locations. There is no household layer or publication switch. Toggle each checkbox off/on and confirm only its markers change.
2. For incident, center, household, and relief-location forms, try a map click, typed coordinate pair, and **Use my current location**. Grant location permission only if you intentionally want to test your device location; use fictional points for saved test records. Try denying permission too. Written/manual entry must remain usable if tiles or geolocation fail.
3. Edit a saved point, reload, and confirm the new point belongs to the intended record. A marker is a recorded location, not a hazard prediction or proof of safety.

## 11. CRUD deletion/archive and history

1. In the disposable database, use **Manage records** to review one unused fictional record, then delete it. Confirm it disappears. Try two selected records together; the review screen must name both before confirmation.
2. Try deleting or archiving a linked/occupied/stocked record. The action must be blocked or use the allowed archive/deactivate rule; a blocked batch must change **none** of the selected records.
3. Reopen response actions, evacuation stays, stock movements, and distributions. Their history and foreign-key links must remain intact. Use **Show archived** where available.

## 12. Phone and desktop review

1. Test at roughly **390 px** and **1280 px** wide using browser responsive mode. Visit every main navigation destination as admin and resident.
2. At phone width, open the **Menu**, use a primary action on each form, scroll wide tables within their own table region, and confirm the map legend does not block the map. No page-wide horizontal scrolling or clipped primary buttons should occur.
3. Test keyboard Tab navigation, visible focus, form labels, status words alongside colors, map layer checkboxes, and readable errors. Repeat a save after refreshing to confirm data persists.

## 13. Record results

For each step, mark **Pass / Fail / Not tested**. For a failure, record: account/role, page URL, fictional record ID, exact steps, expected result, actual result, screenshot if helpful, fix, and recheck date. Retest the affected module plus its linked dashboard/map/history page after each fix. Keep real personal information and images out of the test log and repository.
