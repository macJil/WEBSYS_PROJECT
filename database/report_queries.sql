-- Staff-only aggregate summaries. No personal names, household codes or contacts.
-- Runtime equivalents: app/models/DisasterSummary.php.
-- Bind start and end_exclusive as YYYY-MM-DD 00:00:00 parameters via PDO.
-- end_exclusive is the day AFTER the selected inclusive end date.

-- Current registry designation: date filter deliberately does not apply.
SELECT COUNT(*) AS affected_households
FROM households WHERE registry_reason IN ('affected', 'both');

-- Current occupancy, not a historical occupancy calculation.
SELECT c.id, c.name, c.status, c.capacity, COUNT(e.id) AS occupancy,
       CASE WHEN c.status='open' AND c.capacity>COUNT(e.id)
            THEN c.capacity-COUNT(e.id) ELSE 0 END AS available
FROM evacuation_centers c
LEFT JOIN evacuation_records e ON e.center_id=c.id AND e.checked_out_at IS NULL
GROUP BY c.id,c.name,c.status,c.capacity
ORDER BY c.name,c.id;

-- Incidents submitted in the period, grouped by their CURRENT status.
SELECT incident_type,status,COUNT(*) AS total
FROM incident_reports
WHERE created_at>=:start AND created_at<:end_exclusive
GROUP BY incident_type,status;

-- Group by good/unit; do not combine unlike units into a grand quantity.
SELECT i.id,i.item_name,i.unit,SUM(d.quantity) AS quantity
FROM relief_distributions r
JOIN relief_distribution_items d ON d.distribution_id=r.id
JOIN relief_items i ON i.id=d.item_id
WHERE r.distributed_at>=:start AND r.distributed_at<:end_exclusive
GROUP BY i.id,i.item_name,i.unit
ORDER BY i.item_name,i.unit,i.id;

-- Count events separately from detail rows to avoid multiplying event totals.
SELECT COUNT(*) AS events,COUNT(DISTINCT household_id) AS households
FROM relief_distributions
WHERE distributed_at>=:start AND distributed_at<:end_exclusive;
