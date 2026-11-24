<?php
require_once __DIR__ . '/../includes/helpers.php';
requireRole('admin');
require_once __DIR__ . '/../config/db.php';

const HOUSEHOLD_VULNERABILITY = [
    'unassessed' => 'Not yet assessed',
    'no_additional_need_identified' => 'No additional assistance need identified',
    'assistance_needed' => 'Additional assistance needed',
];
require_once __DIR__ . '/../includes/barangays.php';

const HOUSEHOLD_HAZARDS = [
    'unassessed' => 'Not assessed',
    'landslide' => 'Landslide',
    'flood' => 'Flood',
    'fire' => 'Fire',
    'other' => 'Other',
];
const HOUSEHOLD_REASONS = [
    'monitored' => 'Monitored',
    'affected' => 'Affected',
    'both' => 'Monitored and affected',
];
const HOUSEHOLD_EVACUATION = [
    'at_home' => 'At home',
    'advised' => 'Advised to evacuate',
    'evacuated' => 'Evacuated',
    'returned' => 'Returned',
];

function recordId($value)
{
    $id = is_scalar($value)
        ? filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])
        : false;
    if ($id === false) {
        http_response_code(400);
        exit('Invalid record ID.');
    }
    return $id;
}

function getHousehold(int $id)
{
    $stmt = db()->prepare(
        'SELECT h.*, (SELECT COUNT(*) FROM household_members m WHERE m.household_id=h.id AND m.archived_at IS NULL) AS member_count FROM households h WHERE h.id=?',
    );
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if (!$row) {
        http_response_code(404);
        exit('Household not found.');
    }
    return $row;
}

function member(int $id, int $householdId)
{
    $stmt = db()->prepare('SELECT * FROM household_members WHERE id=? AND household_id=?');
    $stmt->execute([$id, $householdId]);
    $row = $stmt->fetch();
    if (!$row) {
        http_response_code(404);
        exit('Member not found in this household.');
    }
    return $row;
}
