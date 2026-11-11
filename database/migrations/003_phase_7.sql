-- Back up first. Apply after migrations 001 and 002 to an existing database.
-- Resolve any duplicate active registered-member stays before this migration:
-- SELECT household_member_id, COUNT(*) FROM evacuation_records
-- WHERE checked_out_at IS NULL AND household_member_id IS NOT NULL
-- GROUP BY household_member_id HAVING COUNT(*) > 1;
CREATE TABLE evacuation_walkins (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(100) NOT NULL,
  age SMALLINT UNSIGNED NOT NULL,
  address_details VARCHAR(255) NOT NULL,
  contact_phone VARCHAR(20) NOT NULL,
  identity_key CHAR(64) NOT NULL UNIQUE,
  CHECK (age <= 130)
) ENGINE=InnoDB;
ALTER TABLE evacuation_records
  ADD walkin_id INT UNSIGNED NULL,
  ADD checked_out_by INT UNSIGNED NULL,
  ADD active_member_id INT UNSIGNED GENERATED ALWAYS AS (CASE WHEN checked_out_at IS NULL THEN household_member_id ELSE NULL END) STORED,
  ADD active_walkin_id INT UNSIGNED GENERATED ALWAYS AS (CASE WHEN checked_out_at IS NULL THEN walkin_id ELSE NULL END) STORED,
  ADD UNIQUE KEY uq_evac_active_member (active_member_id),
  ADD UNIQUE KEY uq_evac_active_walkin (active_walkin_id),
  ADD FOREIGN KEY (walkin_id) REFERENCES evacuation_walkins(id) ON DELETE RESTRICT,
  ADD FOREIGN KEY (checked_out_by) REFERENCES users(id) ON DELETE RESTRICT,
  ADD CHECK (walkin_id IS NULL OR household_member_id IS NULL);
