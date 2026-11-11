-- BARANGAY DRRM: SIMPLE DEVELOPMENT DATABASE (v1.2)
-- MySQL 8.0.16+; new development database ONLY.
-- No sample accounts/data. Never import over SmartSlope's existing database.
-- One barangay, admin and resident roles; incident images stored separately from MySQL.
-- Fresh installation only. Existing v1.0 databases: run migrations 001 then 002.
-- Existing v1.1 databases: run database/migrations/002_phase_5.sql.

CREATE DATABASE IF NOT EXISTS barangay_drrm CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE barangay_drrm;

-- 1. Users: public registration must always force the resident role in PHP.
CREATE TABLE users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(100) NOT NULL,
  username VARCHAR(50) NOT NULL UNIQUE,
  email VARCHAR(254) NOT NULL UNIQUE,
  phone VARCHAR(20) NULL,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin','resident') NOT NULL DEFAULT 'resident',
  is_active BOOLEAN NOT NULL DEFAULT TRUE,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 2. Households: monitored OR newly affected (registered before relief distribution).
CREATE TABLE households (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  household_code VARCHAR(30) NOT NULL UNIQUE,
  representative_name VARCHAR(100) NOT NULL,
  contact_phone VARCHAR(20) NULL,
  purok VARCHAR(100) NOT NULL,
  address_details VARCHAR(255) NULL,
  registry_reason ENUM('monitored','affected','both') NOT NULL DEFAULT 'monitored',
  hazard_type ENUM('unassessed','landslide','flood','fire','other') NOT NULL DEFAULT 'unassessed',
  hazard_notes VARCHAR(255) NULL,
  vulnerability_category ENUM('unassessed','no_additional_need_identified','assistance_needed') NOT NULL DEFAULT 'unassessed',
  vulnerability_notes VARCHAR(255) NULL,
  vulnerability_assessed_by INT UNSIGNED NULL,
  vulnerability_assessed_at DATETIME NULL,
  evacuation_status ENUM('at_home','advised','evacuated','returned') NOT NULL DEFAULT 'at_home',
  latitude DECIMAL(10,7) NULL,
  longitude DECIMAL(10,7) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (vulnerability_assessed_by) REFERENCES users(id) ON DELETE RESTRICT,
  INDEX idx_households_purok (purok),
  INDEX idx_households_representative (representative_name)
) ENGINE=InnoDB;

-- Household size is COUNT(household_members.id); do not store a second count.

-- 3. Members: linked household members; no login account required.
CREATE TABLE household_members (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  household_id INT UNSIGNED NOT NULL,
  full_name VARCHAR(100) NOT NULL,
  age SMALLINT UNSIGNED NULL,
  notes VARCHAR(255) NULL,
  FOREIGN KEY (household_id) REFERENCES households(id) ON DELETE RESTRICT,
  INDEX idx_members_household (household_id)
) ENGINE=InnoDB;

-- 4. Incidents: private to staff and the submitting account; verification does not publish.
-- reported_by is always the logged-in creator, including admin-entered walk-in reports.
CREATE TABLE incident_reports (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  reported_by INT UNSIGNED NOT NULL,
  report_source ENUM('resident','walk_in','phone','admin') NOT NULL DEFAULT 'resident',
  reporter_name VARCHAR(100) NULL,
  occurred_at DATETIME NULL,
  incident_type ENUM('landslide','flood','fire','road_obstruction','other') NOT NULL,
  description TEXT NOT NULL,
  location_description VARCHAR(255) NULL,
  latitude DECIMAL(10,7) NULL,
  longitude DECIMAL(10,7) NULL,
  contact_phone VARCHAR(20) NULL,
  status ENUM('pending','verified','responding','resolved','rejected') NOT NULL DEFAULT 'pending',
  reviewed_by INT UNSIGNED NULL,
  reviewed_at DATETIME NULL,
  review_notes VARCHAR(255) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (reported_by) REFERENCES users(id) ON DELETE RESTRICT,
  FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE RESTRICT,
  INDEX idx_incidents_status_date (status, created_at)
) ENGINE=InnoDB;

-- 17. Image metadata only. File contents are kept separately on disk.
-- PHP generates stored_filename; never use the original filename as a path.
CREATE TABLE incident_images (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  incident_id INT UNSIGNED NOT NULL,
  stored_filename VARCHAR(100) NOT NULL UNIQUE,
  original_filename VARCHAR(255) NOT NULL,
  mime_type ENUM('image/jpeg','image/png','image/webp') NOT NULL,
  file_size INT UNSIGNED NOT NULL,
  uploaded_by INT UNSIGNED NOT NULL,
  uploaded_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (incident_id) REFERENCES incident_reports(id) ON DELETE RESTRICT,
  FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE RESTRICT,
  CHECK (file_size > 0 AND file_size <= 5242880),
  INDEX idx_images_incident (incident_id)
) ENGINE=InnoDB;

-- 5. Directory of response teams (no separate responder login role).
CREATE TABLE responders (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  team_name VARCHAR(120) NOT NULL UNIQUE,
  contact_phone VARCHAR(20) NULL,
  is_active BOOLEAN NOT NULL DEFAULT TRUE
) ENGINE=InnoDB;

-- 6. Several teams may be assigned to one incident.
CREATE TABLE incident_assignments (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  incident_id INT UNSIGNED NOT NULL,
  responder_id INT UNSIGNED NOT NULL,
  assigned_by INT UNSIGNED NOT NULL,
  assigned_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  status ENUM('assigned','on_scene','completed','cancelled') NOT NULL DEFAULT 'assigned',
  UNIQUE KEY uq_incident_team (incident_id, responder_id),
  FOREIGN KEY (incident_id) REFERENCES incident_reports(id) ON DELETE RESTRICT,
  FOREIGN KEY (responder_id) REFERENCES responders(id) ON DELETE RESTRICT,
  FOREIGN KEY (assigned_by) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- 7. Admin actions/notes for each incident.
CREATE TABLE response_updates (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  incident_id INT UNSIGNED NOT NULL,
  updated_by INT UNSIGNED NOT NULL,
  action_taken TEXT NOT NULL,
  from_status ENUM('pending','verified','responding','resolved','rejected') NULL,
  to_status ENUM('pending','verified','responding','resolved','rejected') NULL,
  assignment_id INT UNSIGNED NULL,
  assignment_status ENUM('assigned','on_scene','completed','cancelled') NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (incident_id) REFERENCES incident_reports(id) ON DELETE RESTRICT,
  FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE RESTRICT,
  FOREIGN KEY (assignment_id) REFERENCES incident_assignments(id) ON DELETE RESTRICT,
  INDEX idx_response_incident_date (incident_id, created_at)
) ENGINE=InnoDB;

-- 8. Admin-written emergency announcements.
CREATE TABLE announcements (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(150) NOT NULL,
  message TEXT NOT NULL,
  is_published BOOLEAN NOT NULL DEFAULT FALSE,
  created_by INT UNSIGNED NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT,
  INDEX idx_announcements_public (is_published, created_at)
) ENGINE=InnoDB;

-- 9. Centers: capacity is in individuals; occupancy comes from active evacuation_records.
CREATE TABLE evacuation_centers (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  address_details VARCHAR(255) NULL,
  latitude DECIMAL(10,7) NULL,
  longitude DECIMAL(10,7) NULL,
  capacity INT UNSIGNED NOT NULL DEFAULT 0,
  status ENUM('open','closed') NOT NULL DEFAULT 'closed',
  CHECK (capacity >= 0)
) ENGINE=InnoDB;

-- 10. One record per individual (registered member OR walk-in).
-- checked_out_at IS NULL means currently checked in.
CREATE TABLE evacuation_records (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  center_id INT UNSIGNED NOT NULL,
  household_member_id INT UNSIGNED NULL,
  walkin_name VARCHAR(100) NULL,
  walkin_contact VARCHAR(20) NULL,
  checked_in_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  checked_out_at DATETIME NULL,
  recorded_by INT UNSIGNED NOT NULL,
  FOREIGN KEY (center_id) REFERENCES evacuation_centers(id) ON DELETE RESTRICT,
  FOREIGN KEY (household_member_id) REFERENCES household_members(id) ON DELETE RESTRICT,
  FOREIGN KEY (recorded_by) REFERENCES users(id) ON DELETE RESTRICT,
  CHECK (
    (household_member_id IS NOT NULL AND walkin_name IS NULL)
    OR (household_member_id IS NULL AND walkin_name IS NOT NULL)
  ),
  CHECK (checked_out_at IS NULL OR checked_out_at >= checked_in_at),
  INDEX idx_evac_active (center_id, checked_out_at),
  INDEX idx_evac_member_active (household_member_id, checked_out_at)
) ENGINE=InnoDB;

-- 11. Relief item catalog and current stock quantity.
CREATE TABLE relief_items (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  item_name VARCHAR(120) NOT NULL,
  unit VARCHAR(30) NOT NULL DEFAULT 'pieces',
  quantity_on_hand INT UNSIGNED NOT NULL DEFAULT 0,
  UNIQUE KEY uq_relief_item_unit (item_name, unit)
) ENGINE=InnoDB;

-- 12. Warehouse/storage/distribution points shown on the staff map.
CREATE TABLE relief_locations (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  location_type ENUM('storage','distribution','both') NOT NULL DEFAULT 'distribution',
  address_details VARCHAR(255) NULL,
  latitude DECIMAL(10,7) NULL,
  longitude DECIMAL(10,7) NULL
) ENGINE=InnoDB;

-- 13. One distribution event to one registered household, possibly at a relief point.
CREATE TABLE relief_distributions (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  household_id INT UNSIGNED NOT NULL,
  relief_location_id INT UNSIGNED NULL,
  received_by VARCHAR(100) NOT NULL,
  distributed_by INT UNSIGNED NOT NULL,
  distributed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  notes VARCHAR(255) NULL,
  FOREIGN KEY (household_id) REFERENCES households(id) ON DELETE RESTRICT,
  FOREIGN KEY (relief_location_id) REFERENCES relief_locations(id) ON DELETE RESTRICT,
  FOREIGN KEY (distributed_by) REFERENCES users(id) ON DELETE RESTRICT,
  INDEX idx_distribution_household_date (household_id, distributed_at)
) ENGINE=InnoDB;

-- 14. Many items per distribution; every quantity must be positive.
CREATE TABLE relief_distribution_items (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  distribution_id INT UNSIGNED NOT NULL,
  item_id INT UNSIGNED NOT NULL,
  quantity INT UNSIGNED NOT NULL,
  UNIQUE KEY uq_distribution_item (distribution_id, item_id),
  FOREIGN KEY (distribution_id) REFERENCES relief_distributions(id) ON DELETE RESTRICT,
  FOREIGN KEY (item_id) REFERENCES relief_items(id) ON DELETE RESTRICT,
  CHECK (quantity > 0)
) ENGINE=InnoDB;

-- 15. Stock history; negative stock prevented by transaction logic in PHP.
CREATE TABLE relief_stock_movements (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  item_id INT UNSIGNED NOT NULL,
  movement_type ENUM('stock_in','stock_out','distribution') NOT NULL,
  quantity INT UNSIGNED NOT NULL,
  distribution_id INT UNSIGNED NULL,
  recorded_by INT UNSIGNED NOT NULL,
  notes VARCHAR(255) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (item_id) REFERENCES relief_items(id) ON DELETE RESTRICT,
  FOREIGN KEY (distribution_id) REFERENCES relief_distributions(id) ON DELETE RESTRICT,
  FOREIGN KEY (recorded_by) REFERENCES users(id) ON DELETE RESTRICT,
  CHECK (quantity > 0),
  CHECK ((movement_type = 'distribution' AND distribution_id IS NOT NULL)
    OR (movement_type IN ('stock_in','stock_out') AND distribution_id IS NULL)),
  UNIQUE KEY uq_stock_distribution_item (distribution_id, item_id),
  INDEX idx_stock_item_date (item_id, created_at)
) ENGINE=InnoDB;

-- 16. One-barangay configuration (seed value is non-personal placeholder only).
CREATE TABLE system_settings (
  id TINYINT UNSIGNED PRIMARY KEY,
  barangay_name VARCHAR(120) NOT NULL DEFAULT 'Demo Barangay',
  emergency_contact VARCHAR(50) NULL,
  CHECK (id = 1)
) ENGINE=InnoDB;
INSERT INTO system_settings (id, barangay_name) VALUES (1, 'Demo Barangay');

-- NO extra reports table: reports are SELECT queries on the tables above.
-- All FK relationships intentionally use RESTRICT for historical integrity.
-- Delete only unlinked records; never turn on foreign_key_checks=0 to force deletion.
-- Validate coordinates, same-barangay location, uniqueness, and roles server-side.
-- Prevent duplicate active evacuation records; enforce center capacity in a transaction.
-- Record inventory distribution using transactions and SELECT ... FOR UPDATE.
-- Use password_hash/password_verify; prepared statements; session/role and ownership checks.
