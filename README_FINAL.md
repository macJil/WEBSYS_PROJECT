# Atok MDRRM System

A PHP and MySQL website for the Municipal Disaster Risk Reduction and Management Office of Atok, Benguet. Residents report incidents and follow their progress. Staff coordinate responses and track households, evacuees, and relief supplies.

## Features

- Register households and members, and issue resident accounts.
- Report incidents with a location and optional photo, assign response teams, and track status.
- Manage evacuation centers, arrivals, checkout, and occupancy.
- Record relief stock movements and household distributions.
- Publish announcements, view dashboard and map updates, and print disaster summaries and incident reports.

## Requirements

PHP 8.3 with PDO MySQL, mbstring and fileinfo, MySQL 8, and a local web server such as MAMP. Online map tiles, address suggestions and weather updates need internet access.

## Local setup

1. Place the project in your server’s web directory. For MAMP, use `htdocs/WEBSYS_PROJECT`.
2. Import `barangay_drrm.sql` into a fresh MySQL database. For a classroom demonstration, also import `database/presentation-records.sql`.
3. Set the database connection, base URL, and private image upload directory in `config/config.php`.
4. For the presentation, use the sign-ins in the comments at the top of the presentation SQL. For the office installation, set a private password and activate the initial MDRRMO account using the [account setup instructions](README.md#fresh-installation-and-sign-in).
5. Open [the MAMP login page](http://localhost:8888/WEBSYS_PROJECT/) and sign in.

## Usage

Staff enroll residents from verified household records. A resident can submit a report and check its status. Staff can review it, assign a team, and follow the response from the dashboard.
