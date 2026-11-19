# Atok municipal revision • requirements and working baseline

Frozen at commit 8985790 on 19 November 2026. Use this folder as the starting point for all nine members. Member numbers follow the agreed presentation assignments. [File, table, helper and link inventory](INVENTORY.md)

## Interview record

The project team reports that Atok municipal DRRM personnel and assistant staff reviewed and approved all eight proposed modules. The team also reports approval by the head, who was away during the visit. Staff emphasized the dashboard, requested CAR weather information and asked to print reports. Staff and students exchanged contacts for further suggestions and the team took photographs. Approval covers the proposal and modules. Final system acceptance is a later milestone.

Member 1 owns confirmation of the exact office name, visit date, participant names and positions, head’s approval date and communication method, permission to show photographs, and the office’s description of its current operational problem. Check the team's visit notes or ask its contact person. These details are pending. Keep private contact numbers outside the repository.

## Required workflows and revision checklist

|Owner / reviewer|Preserve and demonstrate|Planned acceptance check|
|---|---|---|
|1 / 2|Registration, login, session and role guards|Current admin and resident access works. Public registration creates residents. Supply authenticated account details to Member 3.|
|2 / 3|Household and resident registry, calculated member counts|Verify the official barangay list for all Atok. Select an active member as household representative, show address for duplicate names, require replacement before deactivation. Keep internal IDs and linked history. Allow households awaiting representative selection.|
|3 / 4|Incident reporting, private ownership, images and admin-assisted intake|Prefill name, email and phone. Report-specific edits persist independently of account changes. Assisted intake clears caller fields and retains staff encoder. Protect contacts from map and other residents.|
|4 / 5|Emergency response, assignments and action history|Any active team means responding. Resolve after every assignment completes. Verify cancellation, reactivation and history hiding.|
|5 / 6|Evacuation centers, walk-ins, household arrivals and individual checkout|Apply barangay and representative selection. Count actual selected arrivals. Keep duplicate and capacity checks atomic.|
|6 / 7|Relief goods inventory and relief distribution|Keep one municipal inventory. Distribute multiple goods in one transaction. Keep actual receiving representative and history. Add barangay to relief locations.|
|7 / 8|Emergency announcements and resident notices|Add dashboard forecast for a clearly identified Atok/Benguet location and official CAR warning links. Show source, update time and unavailable state.|
|8 / 9|Disaster reports and dashboards|Prioritize dashboard. Reconcile filtered cards with lists, label municipal stock, distinguish current occupancy from period totals. Extend existing summary print and add incident print with Member 3. Residents may print only their own submission, staff notes excluded.|
|9 / 1|Shared map, picker, navigation and integration|Municipal opening view, barangay filters, saved points, manual fallback and role-safe markers. Keep no-point records in counts. Coordinate issue log and nine-member demo.|

Every row is retained. Member 2 coordinates shared barangay values and migration ordering. Module owners update their own selectors and queries. Preserve existing records with unknown barangay until explicitly identified.

## Baseline issue log

|Evidence at baseline|Follow-up and owner|
|---|---|
|Latest documented fictional HTTP run covered 35 page states, 62 links, authentication, image uploads, occupancy, stock, response synchronization and history hiding. See docs/team/RESPONSE_HISTORY_UPDATE.md.|Member 9 uses these as regression checks. This documentation revision records prior results.|
|users already stores full_name, email and phone. incident_reports stores submitted name and phone only.|3 with 1 adds report email snapshot and checks form retry/ownership.|
|households requires household_code and a text representative_name. Existing stays and distributions rely on relational IDs.|2 plans a compatible migration and explicit reconciliation of older names.|
|Existing disaster summary has a Print summary button and print script.|8 extends and checks the existing output. Confirm paper, headings, signatories and incident report fields with staff.|
|Two account roles exist. Migration 006 removed low-stock thresholds.|1 asks instructor about role requirements and visible incident reference. 6 confirms low-stock rubric expectation before schema work.|
|Dashboard order, official barangay source, weather provider, terms, warning links and forecast update interval need validation.|8 confirms card priorities, 2 verifies barangays, 7 verifies weather sources.|
|Stakeholder evidence details above are pending.|1 records confirmed facts and source before presentation.|

## Coding and integration checklist

- Start from the shared branch. Pick one workflow and agree with its reviewer on affected files.
- Match existing names. Use snake_case for MySQL columns and form actions such as save_team, PascalCase for classes and camelCase for methods. Use descriptive direct PHP view paths and explicit POST actions.
- Indent PHP and HTML four spaces at each nesting level, including markup inside PHP loops and conditions. Keep one statement per line.
- Models prepare and execute SQL through PDO. Controllers validate requests and enforce session roles. Views escape displayed values and provide ordinary forms and explicit return links.
- Retain password hashing and verification, ownership checks, image type/size checks and transactions for related writes. Validate on the server.
- Coordinate shared schema, configuration, navigation and dashboard edits with their listed owners. Pair fresh schema changes with an upgrade migration and fictional upgrade check.
- Review each small change and check its normal form, denial case, redirect and downstream totals before integrating. Add issue, fix and recheck to the log.

The [reviewed cleanup checklist](CLEANUP.md) covers the fixed forms, helper call sites and configuration readers for the revised implementation sequence. Existing module owners and reviewers own their view cleanup. Member 1 owns configuration and shared helpers, reviewed by Member 2. Member 8 confirms print formatting and dashboard priorities with staff.

## Dashboard priorities and counts

The team’s provisional staff order is pending, verified and responding incidents, active evacuees, available capacity, open centers, affected households, then municipal inventory items. The client emphasized having a dashboard and did not specify a card order. The order above is the team’s working choice. Instructor clarification on low-stock alerts is pending. The inventory card counts active catalog items, including zero-stock items. Stock quantities are listed per good and unit on its linked page.

The barangay filter uses the incident location, registered household location or evacuation center location according to the card. All barangays includes records awaiting identification. A missing map point does not exclude a center. Active evacuees have a stay without checkout. Available capacity sums remaining places across open, non-archived centers. Incident cards link to matching status and barangay filters. Resident incident counts use the signed-in account. Reload or apply the filter to refresh the figures.

## Dashboard weather

Both roles see a daily point forecast for the municipal map center. Open-Meteo returned a forecast for this location during the 19 November 2026 integration check. The displayed time is the last successful retrieval in Philippine time. Each browser caches valid daily values for 30 minutes. Forecast retrieval runs after the dashboard renders and times out after five seconds. Official PAGASA links remain available during outages.

The [Open-Meteo forecast API](https://open-meteo.com/en/docs) provides daily temperatures and the maximum precipitation probability for the requested coordinates. The [free API terms](https://open-meteo.com/en/terms) permit noncommercial educational use with attribution under CC BY 4.0. Published limits are 600 calls/minute, 5,000/hour, 10,000/day and 300,000/month. Availability is best effort. The public endpoint needs no API key. Its fixed coordinates are shared across barangays. Official notices come from [PAGASA Northern Luzon](https://www.pagasa.dost.gov.ph/regional-forecast/nlprsd) and [PAGASA flood advisories](https://www.pagasa.dost.gov.ph/flood).

## Printing

Staff can apply the date range and barangay on Disaster summaries, then choose Print summary. Current household and occupancy figures use the selected area. Period incident counts use submission time, and historical relief totals use distribution time and the recipient household’s current registered barangay. Records whose barangay is unknown contribute to All barangays.

Print incident report on an incident detail page uses the signed-in account’s existing access. Staff see operational history. Residents can print their own submission. The browser print dialog supports printing or saving as PDF. Both pages show the generation time in Philippine time. The office’s requested headings, paper format and signatories are pending. The current version uses BayanReady headings, the browser’s selected paper size and no signature block.
