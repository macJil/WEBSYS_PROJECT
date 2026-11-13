-- Back up the database first. Public incidents will use their saved report point.
-- Apply once after migration 004; do not run on a fresh installation.
ALTER TABLE incident_reports
  DROP COLUMN public_label,
  DROP COLUMN public_latitude,
  DROP COLUMN public_longitude;
