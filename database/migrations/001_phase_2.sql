-- Phase 2: upgrade the existing v1.0 schema to v1.1, once only.
-- Back up first. MySQL DDL implicitly commits; this file is not an atomic migration.
-- Do not run after importing the updated fresh-install schema.
-- Legacy report source cannot be inferred reliably: review old admin/walk-in reports manually.
USE barangay_drrm;

ALTER TABLE households
  ADD COLUMN vulnerability_category ENUM('unassessed','no_additional_need_identified','assistance_needed') NOT NULL DEFAULT 'unassessed',
  ADD COLUMN vulnerability_notes VARCHAR(255) NULL,
  ADD COLUMN vulnerability_assessed_by INT UNSIGNED NULL,
  ADD COLUMN vulnerability_assessed_at DATETIME NULL,
  ADD FOREIGN KEY (vulnerability_assessed_by) REFERENCES users(id) ON DELETE RESTRICT;

ALTER TABLE incident_reports
  ADD COLUMN report_source ENUM('resident','walk_in','admin') NOT NULL DEFAULT 'resident',
  ADD COLUMN reporter_name VARCHAR(100) NULL,
  ADD COLUMN occurred_at DATETIME NULL,
  ADD COLUMN reviewed_by INT UNSIGNED NULL,
  ADD COLUMN reviewed_at DATETIME NULL,
  ADD COLUMN review_notes VARCHAR(255) NULL,
  ADD FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE RESTRICT;

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

ALTER TABLE response_updates
  ADD COLUMN from_status ENUM('pending','verified','responding','resolved','rejected') NULL,
  ADD COLUMN to_status ENUM('pending','verified','responding','resolved','rejected') NULL,
  ADD COLUMN assignment_id INT UNSIGNED NULL,
  ADD COLUMN assignment_status ENUM('assigned','on_scene','completed','cancelled') NULL,
  ADD FOREIGN KEY (assignment_id) REFERENCES incident_assignments(id) ON DELETE RESTRICT;

ALTER TABLE evacuation_records
  ADD INDEX idx_evac_member_active (household_member_id, checked_out_at);

-- Preflight existing stock rows before the next ALTER; see the Phase 2 notes.
ALTER TABLE relief_stock_movements
  ADD CONSTRAINT chk_stock_distribution_link CHECK (
    (movement_type = 'distribution' AND distribution_id IS NOT NULL)
    OR (movement_type IN ('stock_in','stock_out') AND distribution_id IS NULL)),
  ADD UNIQUE KEY uq_stock_distribution_item (distribution_id, item_id);
