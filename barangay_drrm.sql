-- BARANGAY DRRM: SIMPLE DEVELOPMENT DATABASE (v1.4)
-- MySQL 8.0.16+; new development database ONLY.
-- Includes fictional presentation records and classroom-only logins. Import into an empty database.
-- One barangay, admin and resident roles; incident images stored separately from MySQL.
-- Fresh installation only. Existing databases: apply missing migrations in order: 001, 002, 003, 004.
-- Existing v1.2 databases: run database/migrations/003_phase_7.sql.

CREATE DATABASE IF NOT EXISTS barangay_drrm CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE barangay_drrm;

-- 1. Users: staff enroll residents through household member records.
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
  archived_at DATETIME NULL,
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
  archived_at DATETIME NULL,
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  household_id INT UNSIGNED NOT NULL,
  full_name VARCHAR(100) NOT NULL,
  age SMALLINT UNSIGNED NULL,
  notes VARCHAR(255) NULL,
  FOREIGN KEY (household_id) REFERENCES households(id) ON DELETE RESTRICT,
  INDEX idx_members_household (household_id)
) ENGINE=InnoDB;

ALTER TABLE households ADD COLUMN barangay VARCHAR(40) NULL;
ALTER TABLE users
    ADD COLUMN assigned_barangay VARCHAR(40) NULL,
    ADD COLUMN household_member_id INT UNSIGNED NULL UNIQUE,
    ADD COLUMN must_change_password BOOLEAN NOT NULL DEFAULT FALSE,
    ADD FOREIGN KEY (household_member_id) REFERENCES household_members(id) ON DELETE RESTRICT;

-- 4. Incidents: private to staff and the submitting account; verification does not publish.
-- reported_by is always the logged-in creator, including admin-entered walk-in reports.
CREATE TABLE incident_reports (
  archived_at DATETIME NULL,
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  reported_by INT UNSIGNED NOT NULL,
  barangay VARCHAR(40) NULL,
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
  status ENUM('assigned','completed','cancelled') NOT NULL DEFAULT 'assigned',
  UNIQUE KEY uq_incident_team (incident_id, responder_id),
  FOREIGN KEY (incident_id) REFERENCES incident_reports(id) ON DELETE RESTRICT,
  FOREIGN KEY (responder_id) REFERENCES responders(id) ON DELETE RESTRICT,
  FOREIGN KEY (assigned_by) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- 7. Admin actions/notes for each incident.
CREATE TABLE response_updates (
  history_hidden_at DATETIME NULL,
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
  archived_at DATETIME NULL,
  first_published_at DATETIME NULL,
  publication_history_unknown BOOLEAN NOT NULL DEFAULT FALSE,
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
  barangay VARCHAR(40) NULL,
  archived_at DATETIME NULL,
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
CREATE TABLE evacuation_walkins (
  archived_at DATETIME NULL,
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(100) NOT NULL,
  age SMALLINT UNSIGNED NOT NULL,
  address_details VARCHAR(255) NOT NULL,
  contact_phone VARCHAR(20) NOT NULL,
  identity_key CHAR(64) NOT NULL UNIQUE,
  CHECK (age <= 130)
) ENGINE=InnoDB;

CREATE TABLE evacuation_records (
  history_hidden_at DATETIME NULL,
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  center_id INT UNSIGNED NOT NULL,
  household_member_id INT UNSIGNED NULL,
  walkin_name VARCHAR(100) NULL,
  walkin_contact VARCHAR(20) NULL,
  checked_in_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  checked_out_at DATETIME NULL,
  recorded_by INT UNSIGNED NOT NULL,
  walkin_id INT UNSIGNED NULL,
  checked_out_by INT UNSIGNED NULL,
  active_member_id INT UNSIGNED GENERATED ALWAYS AS (CASE WHEN checked_out_at IS NULL THEN household_member_id ELSE NULL END) STORED,
  active_walkin_id INT UNSIGNED GENERATED ALWAYS AS (CASE WHEN checked_out_at IS NULL THEN walkin_id ELSE NULL END) STORED,
  UNIQUE KEY uq_evac_active_member (active_member_id),
  UNIQUE KEY uq_evac_active_walkin (active_walkin_id),
  FOREIGN KEY (walkin_id) REFERENCES evacuation_walkins(id) ON DELETE RESTRICT,
  FOREIGN KEY (checked_out_by) REFERENCES users(id) ON DELETE RESTRICT,
  CHECK (walkin_id IS NULL OR household_member_id IS NULL),
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
  archived_at DATETIME NULL,
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  item_name VARCHAR(120) NOT NULL,
  unit VARCHAR(30) NOT NULL DEFAULT 'pieces',
  quantity_on_hand INT UNSIGNED NOT NULL DEFAULT 0,
  UNIQUE KEY uq_relief_item_unit (item_name, unit)
) ENGINE=InnoDB;

-- 12. Warehouse/storage/distribution points shown on the staff map.
CREATE TABLE relief_locations (
  archived_at DATETIME NULL,
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  location_type ENUM('storage','distribution','both') NOT NULL DEFAULT 'distribution',
  address_details VARCHAR(255) NULL,
  latitude DECIMAL(10,7) NULL,
  longitude DECIMAL(10,7) NULL
) ENGINE=InnoDB;

-- 13. One distribution event to one registered household, possibly at a relief point.
CREATE TABLE relief_distributions (
  history_hidden_at DATETIME NULL,
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
  history_hidden_at DATETIME NULL,
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

-- Demo labels are configured in ignored config/config.php.

-- NO extra reports table: reports are SELECT queries on the tables above.
-- All FK relationships intentionally use RESTRICT for historical integrity.
-- Delete only unlinked records; never turn on foreign_key_checks=0 to force deletion.
-- Validate coordinates, same-barangay location, uniqueness, and roles server-side.
-- Prevent duplicate active evacuation records; enforce center capacity in a transaction.
-- Record inventory distribution using transactions and SELECT ... FOR UPDATE.
-- Use password_hash/password_verify; prepared statements; session/role and ownership checks.

-- CLASSROOM DEMO DATA
-- These public passwords are only for a fictional local demonstration.
-- Barangay admin: barangay.admin@atok.example.invalid / AtokDemoAdmin26!
-- Demo staff: staff@demo.example.invalid / AtokDemoStaff26!
-- Demo resident: resident@demo.example.invalid / AtokDemoResident26!
-- All three demo accounts open their own dashboards directly.
START TRANSACTION;

INSERT INTO households
(id,household_code,representative_name,barangay,purok,address_details,registry_reason,hazard_type,latitude,longitude) VALUES
(1,'DEMO-ATOK-01','Demo Resident One','Paoay','Demo Block A','Fictional training lane','both','flood',16.62366,120.7575),
(2,'DEMO-ATOK-02','Demo Resident Three','Topdac','Demo Block B','Fictional upper lane','monitored','landslide',NULL,NULL);
INSERT INTO household_members (id,household_id,full_name,age) VALUES
(1,1,'Demo Resident One',32),(2,1,'Demo Resident Two',24),(3,2,'Demo Resident Three',48);
INSERT INTO users (id,full_name,username,email,password,phone,role,household_member_id) VALUES
(1,'Barangay Demo Administrator','barangay_admin','barangay.admin@atok.example.invalid','$2y$10$bkxLSgOLfwXgV7J6AImvTuGXgQEu53UR2K8BY0toNIYrVhBl2aM2e',NULL,'admin',NULL),
(2,'Demo Duty Officer','demo_staff','staff@demo.example.invalid','$2y$10$RY0w1QyXoqXSSEI1xnOfNewoWtKW.4.OeFSYlcRc/0Um2mN1bgDaW',NULL,'admin',NULL),
(3,'Demo Resident One','demo_resident','resident@demo.example.invalid','$2y$10$iY/.6zxDi7PO0H1SzxkuCexnkfsIQzMqrMEisVTyRIexDXaCEpTN.','09000000000','resident',1);

INSERT INTO incident_reports
(id,reported_by,barangay,report_source,reporter_name,incident_type,description,location_description,latitude,longitude,status,reviewed_by,reviewed_at,created_at) VALUES
(1,2,'Topdac','phone','Demo Caller','landslide','Drill: debris cleared from the training road.','Fictional upper lane',NULL,NULL,'resolved',2,NOW()-INTERVAL 1 DAY,NOW()-INTERVAL 2 DAY),
(2,2,'Paoay','walk_in','Demo Visitor','road_obstruction','Drill: a branch blocks the practice crossing.','Demo crossing',16.62366,120.76422,'responding',2,NOW(),NOW()-INTERVAL 2 HOUR),
(3,3,'Paoay','resident','Demo Resident One','flood','Drill: water across the training lane.','Demo training lane',16.617,120.76422,'pending',NULL,NULL,NOW());
UPDATE incident_reports SET contact_phone='09000000000' WHERE id=3;
INSERT INTO responders (id,team_name) VALUES (1,'Demo Response Alpha'),(2,'Demo Response Bravo');
INSERT INTO incident_assignments (id,incident_id,responder_id,assigned_by,status,assigned_at) VALUES
(1,1,1,2,'completed',NOW()-INTERVAL 1 DAY),(2,2,2,2,'assigned',NOW());
INSERT INTO response_updates (incident_id,updated_by,action_taken,from_status,to_status,assignment_id,assignment_status,created_at) VALUES
(1,2,'Demo dispatch to training road.','pending','responding',1,'assigned',NOW()-INTERVAL 1 DAY),
(1,2,'Demo team cleared debris.','responding','resolved',1,'completed',NOW()-INTERVAL 23 HOUR),
(2,2,'Demo team dispatched to crossing.','pending','responding',2,'assigned',NOW());

INSERT INTO evacuation_centers (id,name,barangay,address_details,capacity,status,latitude,longitude) VALUES
(1,'Demo Training Shelter','Paoay','Fictional assembly hall',5,'open',16.63,120.76422),
(2,'Demo Reserve Shelter','Topdac','Fictional reserve hall',4,'closed',NULL,NULL);
INSERT INTO evacuation_walkins (id,full_name,age,address_details,contact_phone,identity_key) VALUES
(1,'Demo Returning Visitor',42,'Demo Lane B','09000000001',SHA2('["demo returning visitor","42","demo lane b","09000000001"]',256));
INSERT INTO evacuation_records (center_id,household_member_id,recorded_by) VALUES (1,1,2);
INSERT INTO evacuation_records (center_id,walkin_id,walkin_name,walkin_contact,recorded_by,checked_in_at,checked_out_at,checked_out_by) VALUES
(1,1,'Demo Returning Visitor','09000000001',2,NOW()-INTERVAL 1 DAY,NOW(),2);

INSERT INTO relief_locations (id,name,location_type,address_details,latitude,longitude) VALUES
(1,'Demo Relief Desk','distribution','Fictional east supply desk',16.62366,120.7714);
INSERT INTO relief_items (id,item_name,unit,quantity_on_hand) VALUES
(1,'Demo food pack','packs',18),(2,'Demo drinking water','bottles',18),(3,'Demo blanket','pieces',0);
INSERT INTO relief_distributions (id,household_id,relief_location_id,received_by,distributed_by,notes) VALUES
(1,1,1,'Demo Resident One',2,'Fictional training distribution');
INSERT INTO relief_distribution_items (distribution_id,item_id,quantity) VALUES (1,1,2),(1,2,2);
INSERT INTO relief_stock_movements (item_id,movement_type,quantity,distribution_id,recorded_by,notes) VALUES
(1,'stock_in',20,NULL,2,'Demo delivery'),(2,'stock_in',20,NULL,2,'Demo delivery'),
(1,'distribution',2,1,2,'Demo household issue'),(2,'distribution',2,1,2,'Demo household issue'),
(3,'stock_in',3,NULL,2,'Demo donation'),(3,'stock_out',3,NULL,2,'Demo damaged supplies');
INSERT INTO announcements (title,message,is_published,first_published_at,created_by) VALUES
('Demo exercise notice','Classroom exercise only. View the Demo Training Shelter on the map.',1,NOW(),2),
('Demo staff draft','Draft of the fictional preparedness reminder.',0,NULL,2);
COMMIT;
