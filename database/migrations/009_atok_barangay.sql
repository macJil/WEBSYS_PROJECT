-- Apply once after 008. Existing rows await explicit identification.
ALTER TABLE households ADD COLUMN barangay VARCHAR(32) NULL;
ALTER TABLE evacuation_centers ADD COLUMN barangay VARCHAR(32) NULL;
ALTER TABLE relief_locations ADD COLUMN barangay VARCHAR(32) NULL;
ALTER TABLE incident_reports ADD COLUMN barangay VARCHAR(32) NULL;
