# BayanReady plain PHP live demonstration

Thirty minutes including live form entry, code walkthroughs and presenter handoffs. Q&A follows. Use fictional accounts and records. Start with a five-place empty center. The clock times are rehearsal targets.

## BayanReady for Atok

BAYANREADY  /  MEMBER 1 · 00:00–00:35

We visited the MDRRM Office in Atok on 17 November 2026. We showed staff all eight required modules. They approved the proposed modules and emphasized the dashboard. Today we will follow one fictional household from registration to evacuation and relief.

## The stakeholder visit

BAYANREADY  /  MEMBER 1 · 00:35–01:10

This slide identifies the interview visit. Use the approved photograph if available. We spoke with municipal DRRM personnel and assistant staff. We exchanged contact details so the office could provide further feedback.

## What staff asked to see

BAYANREADY  /  MEMBER 1 · 01:10–02:00

The office requested weather information for CAR and the ability to print reports. On this plain PHP branch we can demonstrate the staff and resident dashboards and print the disaster summary. Weather remains a stakeholder request for this version.

## Read the fictional database records

BAYANREADY  /  MEMBER 1 · 02:00–04:00

Here are the fictional database records. The household ID connects its members and relief distributions. An evacuation stay records one person's arrival at a center. Two active stays add two people to occupancy. We will use those relationships throughout the demonstration.

## Plain PHP modules and household registration

BAYANREADY  /  MEMBER 2 · 04:00–07:15

I will sign in as staff and open households/add.php. Each module has its own folder. This page reads the submitted fields, checks them, and uses PDO to insert the household. It then opens households/details.php. I will create Demo Household A, enter its representative name, and add three fictional members. Remember that household code for our center and distribution demonstrations.

## Submit an incident as a resident

BAYANREADY  /  MEMBER 3 · 07:15–10:45

I will sign in as a resident and open incidents/create.php. I will submit a fictional flood report with a written landmark and an optional image. The saved report opens in incidents/view.php. My account owns it. The image page checks ownership before serving the private file. I will hand the incident number to our response presenter.

## Keep response and incident status in step

BAYANREADY  /  MEMBER 4 · 10:45–14:00

I will verify that incident as staff and open response/incident.php. I will assign two teams and record an action. Completing only the first assignment still leaves work outstanding. When I complete both, we can reopen the incident to see resolved status and its recorded actions.

## Count the people who actually arrive

BAYANREADY  /  MEMBER 5 · 14:00–17:15

I will open evacuation/center.php and select Demo Household A. Three members are registered, but only two arrived. I will uncheck the absent member and check in the other two together. A center with five available places now has three. I will show both active stays and leave them open so our dashboard presenter can verify the count.

## Distribute several goods in one transaction

BAYANREADY  /  MEMBER 6 · 17:15–20:30

I will open relief-inventory/movements.php and record five units each of two fictional goods. In relief-distribution/index.php I will choose Demo Household A and enter the actual receiver. I will first request more of the second item than is available. Both balances stay at five. Issuing two of each succeeds, leaves three of each, and creates one distribution with two lines.

## Publish and withdraw a preparedness notice

BAYANREADY  /  MEMBER 7 · 20:30–23:30

I will save a fictional preparedness reminder as a draft in announcements/index.php. I will refresh the resident page, publish the notice, and refresh again. The resident can now read it. I will withdraw it with confirmation and refresh the resident dashboard to show that the notice disappears.

## Read the dashboard in people and records

BAYANREADY  /  MEMBER 8 · 23:30–25:20

I will open dashboard/staff.php and follow the active-evacuees card. We can find the two arrivals entered by Member 5. Now I will check out one member and refresh. Active occupants decrease by one and available capacity increases by one. That is how staff can check the figure against the underlying records.

## Print the staff disaster summary

BAYANREADY  /  MEMBER 8 · 25:20–27:00

I will open disaster-reports/index.php, choose dates covering our demo and show the distribution and incident totals. Current occupancy counts people still checked in. The selected period controls incident and relief activity. I will open the browser print preview for the staff summary.

## Find the same incident on the shared map

BAYANREADY  /  MEMBER 9 · 27:00–29:20

I will open map/map.php, enable the incident layer and locate the reported point. Resident map data includes eligible incident points, open centers and relief points. Personal reporter fields and household markers stay private. A point describes a reported location. Staff determine how to respond.

## Documentation and handoff

BAYANREADY  /  MEMBER 9 · 29:20–30:00

Our interview record explains the client requests. The module folders show where the work happens. We followed the same fictional household through registration, arrival, distribution and dashboard review. For the PHP behavior we referred to https://www.php.net/manual/en/book.pdo.php and https://www.php.net/manual/en/book.session.php and for the map to https://leafletjs.com/reference.html. We can now take questions.
