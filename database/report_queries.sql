-- Read-only query examples; run after selecting barangay_drrm.
-- Supply date parameters through prepared statements in PHP.

-- Household size, including households with zero recorded members.
SELECT h.id, h.household_code, h.representative_name, COUNT(m.id) AS member_count
FROM households h
LEFT JOIN household_members m ON m.household_id = h.id
GROUP BY h.id, h.household_code, h.representative_name;

-- Current affected registry designation, not per-disaster historical impact.
SELECT COUNT(*) AS affected_households
FROM households WHERE registry_reason IN ('affected', 'both');

-- Current occupancy; available capacity is based on active individual records.
SELECT c.id, c.name, c.status, c.capacity, COUNT(e.id) AS occupancy,
       GREATEST(c.capacity - COUNT(e.id), 0) AS available_capacity
FROM evacuation_centers c
LEFT JOIN evacuation_records e ON e.center_id = c.id AND e.checked_out_at IS NULL
GROUP BY c.id, c.name, c.status, c.capacity;

SELECT incident_type, status, COUNT(*) AS incident_count
FROM incident_reports
GROUP BY incident_type, status;

-- Summary by item/unit; never add incompatible item units into one total.
SELECT i.id, i.item_name, i.unit, SUM(d.quantity) AS distributed_quantity
FROM relief_distribution_items d
JOIN relief_items i ON i.id = d.item_id
GROUP BY i.id, i.item_name, i.unit;

SELECT r.id, h.household_code, r.received_by, r.distributed_at,
       i.item_name, i.unit, d.quantity
FROM relief_distributions r
JOIN households h ON h.id = r.household_id
JOIN relief_distribution_items d ON d.distribution_id = r.id
JOIN relief_items i ON i.id = d.item_id
ORDER BY r.distributed_at DESC, r.id DESC, i.id;
