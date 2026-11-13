-- Back up first. Apply once after migration 006 on existing databases.
-- The map now uses incident status and facility type/status automatically.
ALTER TABLE incident_reports DROP COLUMN is_public;
ALTER TABLE evacuation_centers DROP COLUMN is_public;
ALTER TABLE relief_locations DROP COLUMN is_public;
