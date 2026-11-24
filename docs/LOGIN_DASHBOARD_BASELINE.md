# Classroom comparison data

Run `TEST_DASHBOARD_BASELINE=1 python3 tests/household_map_persistence.py` from the repository root with the test PHP and MySQL socket configured. The runner creates a fresh disposable database and removes it afterward. Run without the baseline flag for the full workflow suite.

One fictional admin and two residents use separate sessions. Resident B initially has no reports. The following reports are then created in order, on the test date.

| Report | Owner | Barangay | Type | Status |
| --- | --- | --- | --- | --- |
| A1 | Resident A | Paoay | Flooding | Pending |
| A2 | Resident A | Topdac | Fire | Resolved |
| B1 | Resident B | Topdac | Landslide | Verified |
| B2 | Resident B | Paoay | Flooding | Pending |
| B3 | Resident B | Paoay | Fire | Resolved, archived |

**Staff expectations.** Two pending, one verified and zero responding. Paoay has two active-list flood reports. Topdac has one fire and one landslide. All other type counts are zero. The pending queue planned for Phase 3 should contain A1 followed by B2.

**Center expectations.** The open Paoay shelter has five places, one person staying and four places remaining. The closed Topdac shelter has nine places and zero occupants. The staff dashboard counts one open center, four available places, one current evacuee and zero affected households.

**Resident expectations.** A sees one pending and one resolved report. B sees one pending and one verified report. Before B submits anything, all five of B’s status counts are zero. Both see one open center and zero published notices. Neither can open the other’s report by ID. For the later latest-report feature, A2 and B2 are the expected selections.
