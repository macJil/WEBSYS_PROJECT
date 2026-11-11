-- Run once after migration 001 on existing installations. Fresh imports already include this.
ALTER TABLE incident_reports MODIFY report_source ENUM('resident','walk_in','phone','admin') NOT NULL DEFAULT 'resident';
