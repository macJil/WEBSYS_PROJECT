-- Apply once after migration 003. Back up first; MySQL DDL is not transactional.
ALTER TABLE households
  ADD COLUMN archived_at DATETIME NULL;

ALTER TABLE household_members
  ADD COLUMN archived_at DATETIME NULL;

ALTER TABLE incident_reports
  ADD COLUMN archived_at DATETIME NULL,
  ADD COLUMN is_public BOOLEAN NOT NULL DEFAULT FALSE,
  ADD COLUMN public_label VARCHAR(150) NULL,
  ADD COLUMN public_latitude DECIMAL(10,7) NULL,
  ADD COLUMN public_longitude DECIMAL(10,7) NULL;

ALTER TABLE evacuation_centers
  ADD COLUMN archived_at DATETIME NULL,
  ADD COLUMN is_public BOOLEAN NOT NULL DEFAULT FALSE;

ALTER TABLE evacuation_walkins
  ADD COLUMN archived_at DATETIME NULL;

ALTER TABLE relief_items
  ADD COLUMN archived_at DATETIME NULL,
  ADD COLUMN minimum_stock INT UNSIGNED NOT NULL DEFAULT 0;

ALTER TABLE relief_locations
  ADD COLUMN archived_at DATETIME NULL,
  ADD COLUMN is_public BOOLEAN NOT NULL DEFAULT FALSE;

ALTER TABLE announcements
  ADD COLUMN archived_at DATETIME NULL,
  ADD COLUMN first_published_at DATETIME NULL,
  ADD COLUMN publication_history_unknown BOOLEAN NOT NULL DEFAULT FALSE;

-- Existing notices have unknown earlier publication history; protect them from deletion.
UPDATE announcements SET publication_history_unknown=TRUE;
