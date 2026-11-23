# Atok MDRRM System setup and fictional pilot

1. Use the assessment branch with PHP 8.1 or newer, PDO MySQL, Fileinfo and MySQL supporting the supplied schema. Serve the application through root `index.php`.
2. For a fresh local installation import `barangay_drrm.sql` into a dedicated fictional database. For an existing plain-PHP assessment installation, back up the database and apply only missing assessment migrations in order (01, 04, 05). Do not import the fresh schema over a populated database.
3. Copy `setup/config.example.php` to `config/config.php`. Set the local database credentials, port, base URL path and a writable absolute upload directory outside the document root. For existing configurations set `app_name` to `Atok MDRRM System`. Use an empty base URL at the domain root or the actual subdirectory path.
4. Provision a fictional staff account directly in the isolated database. Generate its password hash with PHP’s `password_hash`, and insert the hash, a unique username and email, a fictional full name, role `admin` and an authorized `assigned_barangay`, such as Paoay. Deliver the password privately. Public login has no registration option.
5. Sign in as staff, add a fictional Paoay household and member, then use **Create resident account**. Enter a fictional email and phone. In a separate browser session, sign in as that resident and replace the temporary password.
6. Before the demonstration, prepare a five-person open center in Paoay, two active teams, two relief goods with zero starting balance and a separate returning walk-in with a completed stay. Start the timed [nine-member script](../presentation/LIVE_DEMO_PRESENTATION_SCRIPT.md) with another, fresh demo household.

## Online classroom pilot

Use an authorized HTTPS PHP/MySQL site with a separate fictional database. On managed hosting, if the provider assigns the database name, select that database and import the schema without the two database creation/selection statements. Keep config credentials, uploads, tests, SQL, repository metadata and documentation outside public access. Give the running application only the database privileges needed for its workflows.

Run the timed script on the hosted version with a staff session and an independent resident session. On a phone, expand navigation, select a pin, deny location permission, enter the landmark manually and try a point outside Atok. Check the flood count against its linked list, occupancy against open stays, and the two distributed item balances. Print the filtered summary and both incident copies. Try the staff report URL and another resident’s incident with the resident account. Record URL, revision, devices, dates, results and any corrections in the pilot record. The local automated suite creates and drops a disposable database and is for local testing only.

## Office review before real resident data

| Item to demonstrate to Atok MDRRM Office | Decision to record |
| --- | --- |
| Show all Atok, Sayangan opening, near-border locations and written-only reports | Accepted boundary source/version or requested corrections |
| Fictional A4 summary, staff incident copy and resident copy | Approved headings, page layout, signatories and private fields |
| Staff enrollment by barangay, municipal staff operational visibility, first-login change and resident ownership | Authorized roles and areas, account recovery and agreed access limits |

Record the reviewer’s role, review date, decision and follow-up for each item. Keep any identifying interview materials in the team’s approved private storage. Approval of the eight modules and acceptance of these specific operational details are separate decisions.
