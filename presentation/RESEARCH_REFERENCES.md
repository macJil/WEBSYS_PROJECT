# Presentation research and source matrix

Researched 15 November 2026. Includes official background, one related scholarly review, official technical documentation and clearly identified internal documents. This is a focused presentation evidence set, not an exhaustive RRL or stakeholder needs assessment. No testing results are included.

## References

### [R1]

Republic of the Philippines. (2010). Republic Act No. 10121, sections 3(p), 12(c)(12).

https://elibrary.judiciary.gov.ph/thebookshelf/showdocs/2/21121

Accessed 15 November 2026.

### [R2]

UNDRR. (n.d.). Disaster risk management. Terminology.

https://www.undrr.org/terminology/disaster-risk-management

Accessed 15 November 2026.

### [R3]

The PHP Group. (n.d.). What is PHP and what can it do?

https://www.php.net/intro

Accessed 15 November 2026.

### [R4]

The PHP Group. (n.d.). Classes and Objects: The Basics.

https://www.php.net/manual/en/language.oop5.basic.php

Accessed 15 November 2026.

### [R5]

MDN contributors. (n.d.). MVC. MDN Web Docs glossary.

https://developer.mozilla.org/en-US/docs/Glossary/MVC

Accessed 15 November 2026.

### [R6]

The PHP Group. (n.d.). PHP Data Objects: Introduction.

https://www.php.net/manual/en/book.pdo.php

Accessed 15 November 2026.

### [R7]

The PHP Group. (n.d.). Prepared statements and stored procedures.

https://www.php.net/manual/en/pdo.prepared-statements.php

Accessed 15 November 2026.

### [R8]

Leaflet contributors. (n.d.). Leaflet 1.9.4 API reference.

https://leafletjs.com/reference-1.9.4.html

Accessed 15 November 2026.

### [R9]

MDN contributors. (n.d.). Geolocation API.

https://developer.mozilla.org/en-US/docs/Web/API/Geolocation_API

Accessed 15 November 2026.

### [R10]

Bootstrap contributors. (n.d.). Breakpoints, Bootstrap 5.3.

https://getbootstrap.com/docs/5.3/layout/breakpoints/

Accessed 15 November 2026.

### [R11]

WEBSYS1 instructor. (n.d.). Final requirement announcement and project 2 mandatory module list.

Course announcement supplied by the team; retain the original course copy.

Accessed 15 November 2026.

### [R12]

BayanReady team. (2026). WEBSYS_PROJECT source and team documentation, branch bayanready-household-map-simplify, baseline 5696ac1.

https://github.com/macJil/WEBSYS_PROJECT/tree/bayanready-household-map-simplify

Accessed 15 November 2026.

### [R13]

The PHP Group. (n.d.). Transactions and auto-commit.

https://www.php.net/manual/en/pdo.transactions.php

Accessed 15 November 2026.

### [R14]

The PHP Group. (n.d.). Handling file uploads.

https://www.php.net/manual/en/features.file-upload.php

Accessed 15 November 2026.

### [R15]

komoot and Photon contributors. (n.d.). Photon README, reverse geocoding.

https://github.com/komoot/photon

Accessed 15 November 2026.

### [R16]

Daud, M., Ugliotti, F. M., & Osello, A. (2024). Comprehensive Analysis of the Use of Web-GIS for Natural Hazard Management: A Systematic Review. Sustainability, 16(10), 4238.

https://doi.org/10.3390/su16104238

Accessed 15 November 2026.

## Claim-to-source matrix

| ID | Topic | Relevant section | Supported point | Limit |
|---|---|---|---|---|
| R1 | Background | RA 10121 sections 3(p), 12(c)(12) | Defines disaster information systems and includes facility capacity records | National context only; no legal-compliance claim |
| R2 | Background | Terminology definition | Defines broader disaster risk management | A website covers only a limited information-support role |
| R3 | Programming approach | Paragraph beginning “So with PHP…” | PHP permits procedural, OOP or a mixture | Code establishes the particular mix used |
| R4 | OOP | class and new sections | Classes group properties/methods and instantiate objects | Does not establish that all code is OOP |
| R5 | MVC | Definition and components | Separation of data, presentation and control | BayanReady uses a simplified page-first adaptation |
| R6 | Database overview | Introduction | PDO supplies a database-access interface | Not an ORM or a standalone database |
| R7 | Prepared SQL | Overview and examples | Bound parameters separate values from SQL | Dynamic identifiers still require allowlists |
| R8 | Map | Map, Marker, TileLayer, LayerGroup | Leaflet renders maps, markers and controls | No automatic hazard assessment |
| R9 | Current location | Concepts and usage | Device location needs user permission and secure context | No guarantee of positional accuracy |
| R10 | Responsive layout | Core concepts and available breakpoints | Breakpoints adjust responsive layouts | Not evidence of usability on every device |
| R11 | Required features | Instructor announcement supplied in chat | Eight required course modules | Not stakeholder validation; original date/author not invented |
| R12 | Actual implementation | Source and docs/team at baseline 5696ac1 | Shows actual files, workflow and chosen rules | Not independent evidence of measured impact |
| R13 | Operational writes | Transactions and auto-commit | Transactions group related writes | Not a claim that all concurrency risks disappear |
| R14 | Incident images | POST upload documentation | PHP supports form uploads and server checks | 5 MiB/type choices come from project code |
| R15 | Address suggestions | README reverse endpoint and demo-server notes | Photon supports reverse geocoding | Availability, precision and demo service are not guaranteed |
| R16 | Related literature | Abstract, stakeholder-collaboration discussion, conclusions and limitations | Web-GIS literature includes visualisation and participation | Review of other systems, not evidence of this prototype’s effectiveness |

## Presentation use

Client profile and inputs remain blank. After these sections, explain documented background, related literature, PHP/OOP, MVC and one code example. Then show website features and documentation. Cite external definitions on relevant slides and full sources in notes. A research paper does not establish local needs, client approval, reduced response time or legal compliance. The NDRRMC monitoring site was inaccessible during this pass and is not relied on for an unverified claim. The Bootstrap 5.3 and Leaflet 1.9.4 references match the bundled versions. Course announcement metadata should be completed from the original course document, not invented.

Rehearsal target: about 36 minutes including video and questions. The four reference pages are an appendix. Screenshots take the place of existing demonstration time. The old video’s test-results segment still needs replacement before use.
