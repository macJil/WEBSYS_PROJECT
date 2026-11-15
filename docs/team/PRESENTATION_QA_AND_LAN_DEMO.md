# Presentation questions and local-network demo guide

Prepared 15 November 2026. These are possible questions and suggested answers, not interview findings or results from a classroom trial. Explain the current code honestly and use fictional records. Owners: M1 foundations/registry; M2 incidents/response; M3 evacuation; M4 relief/removal; M5 maps/notices/summaries.

## Sharing the MAMP website during class

### Before classmates connect

1. Prepare a disposable demo database with fictional accounts, households, images and contacts. Keep a backup so the class can experiment without altering the presentation's only copy. Confirm the website is pointing to that database.
2. Start MAMP's web server and MySQL. Check the actual web-server port in MAMP settings; the usual Apache default is 8888. MySQL's port is a database setting, not the port classmates put in their browser.
3. Put the host laptop and visitors on a trusted classroom network that allows device-to-device traffic. School guest Wi-Fi may isolate clients even when everyone sees the same Wi-Fi name. Ask the instructor/network administrator about restrictions rather than bypassing them.
4. Find the host's current local IPv4 address in its network connection details. Share a URL shaped like `http://HOST-IP:WEB-PORT/WEBSYS_PROJECT/`. For example, `http://192.168.1.20:8888/WEBSYS_PROJECT/` is an illustration, not the team's verified address. Use the actual project folder/path and port.
5. Visitors must not use `localhost`: on their devices that means their own computer/phone. They do not need MAMP or MySQL installed. Their browser talks to the host's PHP website.
6. In the ignored local `config/config.php`, keep `base_url` as the correct relative project path, such as `/WEBSYS_PROJECT`. An absolute `http://localhost:...` value would send visitors back to their own device. Keep the database host local to the server; do not change it to each visitor's address.
7. Check the link from a second device before class. If it times out, check the address/port, MAMP server state, whether Apache listens beyond loopback, host firewall permission for the web server, and Wi-Fi client isolation. Allow only the needed web-server access. Do not disable the entire firewall or expose MySQL/phpMyAdmin to visitors.
8. Use distinct fictional resident accounts for classmates. Let the teacher use a separate temporary staff account if staff actions need hands-on review. Keep staff credentials off the shared resident instruction sheet. Public registration creates residents only.
9. Keep the host awake, plugged in and on the same network. Show the confirmed URL on the classroom screen; a QR code can encode that exact URL once verified. Do not configure router port forwarding for this LAN demonstration.
10. Announce that all submissions are classroom examples, not real emergency requests. Avoid real names, phone numbers, location tracking or personal photos. Use temporary passwords that participants do not reuse elsewhere. Plain HTTP does not encrypt submitted data.

### Important map limitation on a local network

Browser geolocation generally requires a secure context. `http://localhost` on the host may be treated as trustworthy, but `http://192.168.x.x:8888` on a classmate's phone generally is not. Therefore **Use my current location may fail on visitors' phones even while the same button works on the host**. Browser permission alone does not solve an insecure-context restriction.

For the simple LAN demonstration, use map click or manually entered coordinates and address. If the class needs device geolocation, arrange properly trusted HTTPS for the host on the visitors' devices and verify it in advance. Do not ask visitors to disable browser security. Explain the limitation during the demo instead of promising that button will work over ordinary LAN HTTP.

Map tiles require the visitor's internet connection. The address suggestion also depends on the host reaching its external provider. Ordinary record pages can still be used if the LAN and MAMP services work but internet lookup fails. A selected coordinate may be sent to the address provider; use fictional map points.

### Suggested hands-on activity (after the timed presentation)

- M1 helps visitors sign in with separate fictional resident accounts and explains role access.
- M2 asks each visitor to submit one clearly labeled practice report, with a written landmark and optional small supported image. Prefix descriptions with their demo account label.
- M5 shows why a pending report is visible privately but absent from the shared map. M2 verifies one appropriate fictional example, then M5 refreshes the map.
- M3 demonstrates partial household arrival and checkout with the teacher. Use a separate center/household for experiments so the prepared presentation totals remain understandable.
- M4 lets the teacher create a small distribution and compare its history with the stock balance. Keep deletion experiments to designated fictional records.
- M5 shows published notices, dashboard links and the legend. Record feedback in the form at the end.

Do not count an open-ended class trial as part of the 38-minute talk unless the instructor allocates time. Start with a few devices, then invite more users while observing response times and errors. Do not claim a 30-user capacity guarantee without representative load measurements.

### After the activity

End visitor access, stop the server when appropriate, and reset the disposable demo data or disable the temporary accounts using the team's approved setup process. Keep only agreed fictional feedback. No application firewall/server/account settings have been changed by this guide.

## Questions classmates may ask while using the website

| Question | Suggested answer | Owner / show |
|---|---|---|
| Why won't the link open on my phone? | Use the host laptop's current LAN address and web port. Both devices need a network that permits local connections. We will check MAMP, the address and firewall/network restrictions. | M1: confirmed URL |
| Do I need to install MAMP? | No. MAMP runs on the host laptop. You use a browser to access it. | M1 |
| Can I open it from home? | This address is for the reachable local network. Internet hosting has not been established by this classroom sharing setup. | M1 |
| Why did it stop when the laptop slept? | The laptop is hosting PHP and MySQL. It must stay awake and connected while you use this demo. | M1 |
| Can I make an admin account through registration? | Public registration creates residents. Staff access is supplied separately for authorized staff workflows. | M1: registration/controller |
| Why can't I see Household Registry? | That page contains staff-only household details. Resident navigation and the server role guard restrict access. | M1 |
| Can we share one resident login? | Separate demo accounts let everyone see their own reports and avoid confusion about ownership. | M1 |
| I submitted a report. Why isn't it on the map? | New reports are pending. The shared map requires an eligible status and valid coordinates. Staff review and map refresh are separate steps. | M2/M5 |
| Can I see another resident's full report? | The private detail and image queries restrict residents to their own submissions. The shared map exposes a smaller set of fields. | M2 |
| What information does the incident marker show? | Type, status, report date and saved point. Reporter names, phones, images and private descriptions are excluded from the map response. The point itself is shared once eligible. | M5: MapModel |
| Why does current location fail although I allowed it? | Plain HTTP on a LAN IP generally does not meet browser geolocation's secure-context requirement. Use map click/manual input for this demo. | M5 |
| Why is the suggested address incorrect? | It comes from an external lookup and may be incomplete. Please edit and confirm the written landmark before saving. | M5 |
| Can I report without a map point? | Yes, a written location is required. A point is optional, and no-point records remain in normal records but have no marker. | M2 |
| Why is the map blank or slow? | Background tiles and lookup depend on internet services. We will distinguish unavailable tiles from an empty layer, a pending report or a point outside the view. | M5 |
| Why does the map show Atok rather than our barangay? | Atok is the temporary municipal demo area. A final barangay and approved locations have not been confirmed with a stakeholder. | M5 |
| Why was my photo rejected? | Upload supports valid JPEG, PNG or WebP up to 5 MiB. A phone HEIC file or renamed text file is unsupported. Server upload limits/private storage can also cause failure. Choose a supported image again after an error. | M2 |
| Does reporting here call emergency responders automatically? | No. It records a report for staff review and follow-up. It does not guarantee an immediate response or automatically call emergency services. | M2 |
| Why can't I edit the historical distribution? | The recorded transaction explains previous stock changes. Corrections need a documented operational process, not silently rewriting the historical detail. | M4 |
| Why can't I check in the same person again? | A person can have only one active stay. Check out the earlier stay before a genuine transfer or new arrival. | M3 |
| Why did the whole household check-in fail? | Every selected arrival must be eligible and fit available capacity. If one person blocks the batch, no selected person is checked in. | M3 |
| Does choosing a household count everyone? | Only the checked arriving members count. Uncheck absent members. | M3 |
| What does Available capacity mean? | Remaining person capacity across open, non-archived centers, based on active stays. It is not a household count. | M3 |
| Why can't I distribute more than the available stock? | The transaction checks every selected good. Insufficient stock rejects the whole distribution so balances and history remain consistent. | M4 |
| Why can't I delete this record? | Active stays or remaining stock can block removal. Eligible linked records can leave active lists while historical links stay stored. Follow the displayed guidance rather than deleting history blindly. | M4 |
| Why do totals change while I am trying the website? | Everyone is using the same demo database. Other successful submissions/checkouts/distributions can change current totals. Refresh to see the current state. | All |
| Did my Save work? The network timed out. | We should check the relevant list/history before retrying. A lost response does not prove that the server did not save. | Feature owner |

## Questions the teacher may ask

### 1. Who is the actual client, and what did they request? — Member 1

“We have not acquired a stakeholder yet. The client profile and inputs are intentionally blank. The current scope comes from the instructor's eight required modules. We will document the actual interview and validated changes once a client is confirmed.”

### 2. What problem does the website solve? — Members 1 and 5

“It organizes related household, incident, response, evacuation and relief records in one application. For example, the same household can be selected for arrivals and distributions. Whether these workflows match a particular client's actual problem still needs interview validation.” Do not invent measured efficiency improvements.

### 3. Is this procedural PHP or OOP? — Member 1

“It combines procedural page setup and shared functions with ordinary controller/model classes. The PHP introduction supports mixed use. In households.php, we create a controller object and call its method before rendering the form.” Show the actual snippet rather than only citing a definition.

### 4. Why MVC, and is this Laravel? — Member 1

“MDN describes separation of data, presentation and control logic. We use that idea to organize queries, request handling and markup. It is a simple MVC organization with direct PHP pages, not Laravel. Some page setup remains in the view entry files.”

### 5. What code did each member learn? — All

Each member opens their assigned trace: household save (M1), incident/image save (M2), household check-in (M3), relief transaction (M4), and JSON-to-map rendering (M5). Explain inputs, validation, model work and the returned page/result. Do not claim authorship of every line if the team used shared or assisted code.

### 6. How do you protect role access and report ownership? — Members 1 and 2

“Server guards require a signed-in role, and private report/image queries include the resident's account ID. Hiding a link is not the authorization check.” Show requireRole and the report ownership query. Do not claim complete production security merely because these controls exist.

### 7. Why use prepared statements? — Relevant member

“They keep submitted values separate from the SQL structure. Our application uses fixed server-side column/table choices where identifiers are needed. A question mark binds a value; it does not safely bind arbitrary table names.”

### 8. How do uploads work? — Member 2

“The controller checks upload errors, actual MIME/image structure and size. It stores bytes in a private directory using a generated filename. MySQL stores the incident link and image metadata. Access goes through an ownership-checked PHP endpoint.” A failed database save also removes a newly moved file because SQL rollback cannot undo file movement.

### 9. What happens if two staff check in people at the same time? — Member 3

“The model uses a transaction and locks the relevant center before checking current active stays. It validates the whole group and inserts one stay per arriving person. This supports consistent capacity handling, but we should demonstrate the actual concurrency behavior rather than promise unlimited users.”

### 10. Why one stay per person if you select a household? — Member 3

“Household selection reduces repeated entry, while individual stay rows support partial arrivals and individual checkout. Changes to household membership later do not automatically rewrite a previous stay.”

### 11. What stops partial relief deductions? — Member 4

“The model checks locked stock rows, then saves the distribution, item details and movements in one transaction. A failure rolls back that database operation. We keep units separate, so packs and bottles are not added into one stock quantity.”

### 12. Is Delete really permanent? — Member 4

“For an eligible unused record, yes. For linked records, the application may remove them from active pages while retaining the stored row and history. Some active records are blocked until the required operational step is done. We should explain the actual behavior rather than call every removal permanent.”

### 13. Why are some database tables separate? — Relevant member

“A household can have several members, an incident can have several teams/images, and a distribution can contain several goods. Related rows preserve those details and previous actions.” Keep this brief unless the teacher asks for a specific relationship.

### 14. Does the map predict disasters or identify safe locations? — Member 5

“No. It displays eligible saved points and recorded facility information. Marker color, proximity or a selected point does not establish risk, safety or an approved boundary.”

### 15. Is a shared incident point anonymous? — Member 5

“The JSON omits personal report fields, but coordinates can still reveal a sensitive location. The current automatic map policy shares eligible saved incident points. A real stakeholder must validate that policy before real data is used.”

### 16. Will the website work with 30 or more users? — Members 1 and 5

“We have not established a measured 30-user guarantee for this classroom host. Capacity depends on the laptop, server settings, database work, image traffic and Wi-Fi. Multiple login sessions are supported, but login count alone is not a load measurement. We will observe the class trial and report limitations honestly.”

### 17. Can it work without internet? — Member 5

“Local record pages can run while the LAN and host PHP/MySQL are available. External map tiles and reverse-geocoding may fail without internet. Manual written locations remain usable.”

### 18. Is it ready for production? — All

“It is a course prototype with the required workflows. A real deployment still needs stakeholder validation, hosting/security review, HTTPS, backups, privacy decisions and a documented operational process.” Do not promise that adding more features alone makes it deployment-ready.

### 19. How do you know an entered emergency is true? — Member 2

“The website records the submission; staff verification is a separate human workflow. A valid image format or coordinate range does not prove the incident is real.”

### 20. Which documents support your claims? — Member 5

“The course announcement supports the required modules. The PHP manual, MDN MVC page, PDO transaction documentation and Leaflet reference support the specific technical explanations. Our code guide and actual source show our implementation. These references do not prove client acceptance or real-world effectiveness.”

### 21. What if the live demonstration fails? — All

“We will describe the actual error, identify whether the host, network, external service or application failed, and record the steps. We will not call an unconfirmed save successful. If needed, we can explain the workflow using existing fictional records while the responsible member investigates.”

## Short opening instructions for visitors

“Please connect to the classroom network and open the address shown on screen. Use your assigned fictional resident account, or follow our registration instructions. Do not enter real personal information or actual emergencies. The host laptop must stay running. On your phone, use map click or manual location entry because current location may be unavailable over this local HTTP address. Let the assigned team member know if a page fails or a result is unclear.”

## Feedback record

| Demo account / device | Page or feature | Steps tried | Expected result | Actual result / message | Question or suggestion | Responsible member |
|---|---|---|---|---|---|---|
| Fictional label only | | | | | | |

Record whether feedback is a usability suggestion, a reproducible defect or a future stakeholder decision. Do not include passwords or real personal data. A classmate's suggestion is useful feedback but is not a stakeholder interview finding.

## Reference links used in the presentation

- [The PHP Group: Introduction](https://www.php.net/intro) — procedural/OOP/mixed PHP.
- [MDN Web Docs: MVC](https://developer.mozilla.org/en-US/docs/Glossary/MVC) — separation of responsibilities.
- [The PHP Group: Transactions and auto-commit](https://www.php.net/manual/en/pdo.transactions.php) — commit/rollback explanation.
- [Leaflet 1.9.4 API reference](https://leafletjs.com/reference-1.9.4.html) — markers, popups and layers.
- Instructor's WEBSYS1 announcement — supplied course material; no public link has been supplied. Keep the actual announcement available rather than inventing a URL.

## Additional sources for the LAN instructions

- [MAMP for Mac: Ports](https://documentation.mamp.info/en/MAMP-Mac/Reference/Settings/Ports/) — verify the configured web and database ports.
- [MDN: Geolocation API](https://developer.mozilla.org/en-US/docs/Web/API/Geolocation_API) — secure-context and permission requirements.
- [MDN: Secure contexts](https://developer.mozilla.org/en-US/docs/Web/Security/Secure_Contexts) — local development trust differs from ordinary insecure network origins.

Sources checked 15 November 2026. The network checklist is a rehearsal plan, not a claim that the school's LAN or the current host has been configured or tested.
