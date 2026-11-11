# Phase 2 field dictionary

Generated from the v1.1 fresh-install schema. See PHASE_2_DATABASE_SCHEMA.md for meanings, permissions, category definitions, workflows, and application-enforced rules.


## users

| Column | SQL definition |
|---|---|
| `id` | `INT UNSIGNED AUTO_INCREMENT PRIMARY KEY` |
| `full_name` | `VARCHAR(100) NOT NULL` |
| `username` | `VARCHAR(50) NOT NULL UNIQUE` |
| `email` | `VARCHAR(254) NOT NULL UNIQUE` |
| `phone` | `VARCHAR(20) NULL` |
| `password` | `VARCHAR(255) NOT NULL` |
| `role` | `ENUM('admin','resident') NOT NULL DEFAULT 'resident'` |
| `is_active` | `BOOLEAN NOT NULL DEFAULT TRUE` |
| `created_at` | `TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP` |

## households

| Column | SQL definition |
|---|---|
| `id` | `INT UNSIGNED AUTO_INCREMENT PRIMARY KEY` |
| `household_code` | `VARCHAR(30) NOT NULL UNIQUE` |
| `representative_name` | `VARCHAR(100) NOT NULL` |
| `contact_phone` | `VARCHAR(20) NULL` |
| `purok` | `VARCHAR(100) NOT NULL` |
| `address_details` | `VARCHAR(255) NULL` |
| `registry_reason` | `ENUM('monitored','affected','both') NOT NULL DEFAULT 'monitored'` |
| `hazard_type` | `ENUM('unassessed','landslide','flood','fire','other') NOT NULL DEFAULT 'unassessed'` |
| `hazard_notes` | `VARCHAR(255) NULL` |
| `vulnerability_category` | `ENUM('unassessed','no_additional_need_identified','assistance_needed') NOT NULL DEFAULT 'unassessed'` |
| `vulnerability_notes` | `VARCHAR(255) NULL` |
| `vulnerability_assessed_by` | `INT UNSIGNED NULL` |
| `vulnerability_assessed_at` | `DATETIME NULL` |
| `evacuation_status` | `ENUM('at_home','advised','evacuated','returned') NOT NULL DEFAULT 'at_home'` |
| `latitude` | `DECIMAL(10,7) NULL` |
| `longitude` | `DECIMAL(10,7) NULL` |
| `created_at` | `TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP` |

## household_members

| Column | SQL definition |
|---|---|
| `id` | `INT UNSIGNED AUTO_INCREMENT PRIMARY KEY` |
| `household_id` | `INT UNSIGNED NOT NULL` |
| `full_name` | `VARCHAR(100) NOT NULL` |
| `age` | `SMALLINT UNSIGNED NULL` |
| `notes` | `VARCHAR(255) NULL` |

## incident_reports

| Column | SQL definition |
|---|---|
| `id` | `INT UNSIGNED AUTO_INCREMENT PRIMARY KEY` |
| `reported_by` | `INT UNSIGNED NOT NULL` |
| `report_source` | `ENUM('resident','walk_in','admin') NOT NULL DEFAULT 'resident'` |
| `reporter_name` | `VARCHAR(100) NULL` |
| `occurred_at` | `DATETIME NULL` |
| `incident_type` | `ENUM('landslide','flood','fire','road_obstruction','other') NOT NULL` |
| `description` | `TEXT NOT NULL` |
| `location_description` | `VARCHAR(255) NULL` |
| `latitude` | `DECIMAL(10,7) NULL` |
| `longitude` | `DECIMAL(10,7) NULL` |
| `contact_phone` | `VARCHAR(20) NULL` |
| `status` | `ENUM('pending','verified','responding','resolved','rejected') NOT NULL DEFAULT 'pending'` |
| `reviewed_by` | `INT UNSIGNED NULL` |
| `reviewed_at` | `DATETIME NULL` |
| `review_notes` | `VARCHAR(255) NULL` |
| `created_at` | `TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP` |
| `updated_at` | `TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP` |

## incident_images

| Column | SQL definition |
|---|---|
| `id` | `INT UNSIGNED AUTO_INCREMENT PRIMARY KEY` |
| `incident_id` | `INT UNSIGNED NOT NULL` |
| `stored_filename` | `VARCHAR(100) NOT NULL UNIQUE` |
| `original_filename` | `VARCHAR(255) NOT NULL` |
| `mime_type` | `ENUM('image/jpeg','image/png','image/webp') NOT NULL` |
| `file_size` | `INT UNSIGNED NOT NULL` |
| `uploaded_by` | `INT UNSIGNED NOT NULL` |
| `uploaded_at` | `TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP` |

## responders

| Column | SQL definition |
|---|---|
| `id` | `INT UNSIGNED AUTO_INCREMENT PRIMARY KEY` |
| `team_name` | `VARCHAR(120) NOT NULL UNIQUE` |
| `contact_phone` | `VARCHAR(20) NULL` |
| `is_active` | `BOOLEAN NOT NULL DEFAULT TRUE` |

## incident_assignments

| Column | SQL definition |
|---|---|
| `id` | `INT UNSIGNED AUTO_INCREMENT PRIMARY KEY` |
| `incident_id` | `INT UNSIGNED NOT NULL` |
| `responder_id` | `INT UNSIGNED NOT NULL` |
| `assigned_by` | `INT UNSIGNED NOT NULL` |
| `assigned_at` | `TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP` |
| `status` | `ENUM('assigned','on_scene','completed','cancelled') NOT NULL DEFAULT 'assigned'` |

## response_updates

| Column | SQL definition |
|---|---|
| `id` | `INT UNSIGNED AUTO_INCREMENT PRIMARY KEY` |
| `incident_id` | `INT UNSIGNED NOT NULL` |
| `updated_by` | `INT UNSIGNED NOT NULL` |
| `action_taken` | `TEXT NOT NULL` |
| `from_status` | `ENUM('pending','verified','responding','resolved','rejected') NULL` |
| `to_status` | `ENUM('pending','verified','responding','resolved','rejected') NULL` |
| `assignment_id` | `INT UNSIGNED NULL` |
| `assignment_status` | `ENUM('assigned','on_scene','completed','cancelled') NULL` |
| `created_at` | `TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP` |

## announcements

| Column | SQL definition |
|---|---|
| `id` | `INT UNSIGNED AUTO_INCREMENT PRIMARY KEY` |
| `title` | `VARCHAR(150) NOT NULL` |
| `message` | `TEXT NOT NULL` |
| `is_published` | `BOOLEAN NOT NULL DEFAULT FALSE` |
| `created_by` | `INT UNSIGNED NOT NULL` |
| `created_at` | `TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP` |

## evacuation_centers

| Column | SQL definition |
|---|---|
| `id` | `INT UNSIGNED AUTO_INCREMENT PRIMARY KEY` |
| `name` | `VARCHAR(150) NOT NULL` |
| `address_details` | `VARCHAR(255) NULL` |
| `latitude` | `DECIMAL(10,7) NULL` |
| `longitude` | `DECIMAL(10,7) NULL` |
| `capacity` | `INT UNSIGNED NOT NULL DEFAULT 0` |
| `status` | `ENUM('open','closed') NOT NULL DEFAULT 'closed'` |

## evacuation_records

| Column | SQL definition |
|---|---|
| `id` | `INT UNSIGNED AUTO_INCREMENT PRIMARY KEY` |
| `center_id` | `INT UNSIGNED NOT NULL` |
| `household_member_id` | `INT UNSIGNED NULL` |
| `walkin_name` | `VARCHAR(100) NULL` |
| `walkin_contact` | `VARCHAR(20) NULL` |
| `checked_in_at` | `DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP` |
| `checked_out_at` | `DATETIME NULL` |
| `recorded_by` | `INT UNSIGNED NOT NULL` |

## relief_items

| Column | SQL definition |
|---|---|
| `id` | `INT UNSIGNED AUTO_INCREMENT PRIMARY KEY` |
| `item_name` | `VARCHAR(120) NOT NULL` |
| `unit` | `VARCHAR(30) NOT NULL DEFAULT 'pieces'` |
| `quantity_on_hand` | `INT UNSIGNED NOT NULL DEFAULT 0` |

## relief_locations

| Column | SQL definition |
|---|---|
| `id` | `INT UNSIGNED AUTO_INCREMENT PRIMARY KEY` |
| `name` | `VARCHAR(150) NOT NULL` |
| `location_type` | `ENUM('storage','distribution','both') NOT NULL DEFAULT 'distribution'` |
| `address_details` | `VARCHAR(255) NULL` |
| `latitude` | `DECIMAL(10,7) NULL` |
| `longitude` | `DECIMAL(10,7) NULL` |

## relief_distributions

| Column | SQL definition |
|---|---|
| `id` | `INT UNSIGNED AUTO_INCREMENT PRIMARY KEY` |
| `household_id` | `INT UNSIGNED NOT NULL` |
| `relief_location_id` | `INT UNSIGNED NULL` |
| `received_by` | `VARCHAR(100) NOT NULL` |
| `distributed_by` | `INT UNSIGNED NOT NULL` |
| `distributed_at` | `DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP` |
| `notes` | `VARCHAR(255) NULL` |

## relief_distribution_items

| Column | SQL definition |
|---|---|
| `id` | `INT UNSIGNED AUTO_INCREMENT PRIMARY KEY` |
| `distribution_id` | `INT UNSIGNED NOT NULL` |
| `item_id` | `INT UNSIGNED NOT NULL` |
| `quantity` | `INT UNSIGNED NOT NULL` |

## relief_stock_movements

| Column | SQL definition |
|---|---|
| `id` | `INT UNSIGNED AUTO_INCREMENT PRIMARY KEY` |
| `item_id` | `INT UNSIGNED NOT NULL` |
| `movement_type` | `ENUM('stock_in','stock_out','distribution') NOT NULL` |
| `quantity` | `INT UNSIGNED NOT NULL` |
| `distribution_id` | `INT UNSIGNED NULL` |
| `recorded_by` | `INT UNSIGNED NOT NULL` |
| `notes` | `VARCHAR(255) NULL` |
| `created_at` | `TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP` |

## system_settings

| Column | SQL definition |
|---|---|
| `id` | `TINYINT UNSIGNED PRIMARY KEY` |
| `barangay_name` | `VARCHAR(120) NOT NULL DEFAULT 'Demo Barangay'` |
| `emergency_contact` | `VARCHAR(50) NULL` |
