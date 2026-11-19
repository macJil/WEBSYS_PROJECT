-- Apply once after 009. Keep existing codes and names for reference.
ALTER TABLE households MODIFY household_code VARCHAR(30) NULL,
    MODIFY representative_name VARCHAR(100) NULL, ADD representative_member_id INT UNSIGNED NULL;
ALTER TABLE households ADD CONSTRAINT fk_household_representative
    FOREIGN KEY (representative_member_id) REFERENCES household_members(id) ON DELETE RESTRICT;
CREATE VIEW household_directory AS
SELECT h.*, COALESCE(m.full_name, 'Representative not selected') AS display_name,
       CONCAT_WS(' — ', COALESCE(m.full_name, 'Representative not selected'), h.barangay,
                 NULLIF(h.purok, ''), NULLIF(h.address_details, '')) AS household_label
FROM households h
LEFT JOIN household_members m ON m.id=h.representative_member_id
    AND m.household_id=h.id;
