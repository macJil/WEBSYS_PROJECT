<?php
require_once __DIR__ . '/input.php';
$id = null;
$old = null;
$form = [
    'household_code' => '',
    'representative_name' => '',
    'contact_phone' => '',
    'purok' => '',
    'barangay' => '',
    'address_details' => '',
    'registry_reason' => 'monitored',
    'hazard_type' => 'unassessed',
    'hazard_notes' => '',
    'vulnerability_category' => 'unassessed',
    'vulnerability_notes' => '',
    'evacuation_status' => 'at_home',
];
$errors = [];
if (isPost()) {
    $form = householdInput();
    $values = $form;
    $errors = prepareHousehold($values, $old);
    if (!$errors) {
        try {
            $stmt = db()->prepare('INSERT INTO households (
                household_code, representative_name, contact_phone,
                barangay, purok, address_details, registry_reason,
                hazard_type, hazard_notes, vulnerability_category,
                vulnerability_notes, vulnerability_assessed_by, vulnerability_assessed_at,
                evacuation_status, latitude, longitude
            ) VALUES (
                :household_code, :representative_name, :contact_phone,
                :barangay, :purok, :address_details, :registry_reason,
                :hazard_type, :hazard_notes, :vulnerability_category,
                :vulnerability_notes, :vulnerability_assessed_by, :vulnerability_assessed_at,
                :evacuation_status, :latitude, :longitude
            )');
            $stmt->execute($values);
            $id = (int) db()->lastInsertId();
            flash('Household saved.');
            redirect('households/details.php?id=' . $id);
        } catch (PDOException $error) {
            $errors[] = $error->getCode() === '23000'
                ? 'Choose a unique household code.'
                : 'The household could not be saved. Please try again.';
        }
    }
}
require __DIR__ . '/form.php';
