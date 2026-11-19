-- Apply once after 010. Prior reports have no recorded email snapshot.
ALTER TABLE incident_reports ADD COLUMN reporter_email VARCHAR(254) NULL;
