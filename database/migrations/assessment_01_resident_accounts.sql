ALTER TABLE households ADD COLUMN barangay VARCHAR(40) NULL;
ALTER TABLE users
    ADD COLUMN assigned_barangay VARCHAR(40) NULL,
    ADD COLUMN household_member_id INT UNSIGNED NULL UNIQUE,
    ADD COLUMN must_change_password BOOLEAN NOT NULL DEFAULT FALSE,
    ADD FOREIGN KEY (household_member_id) REFERENCES household_members(id) ON DELETE RESTRICT;
