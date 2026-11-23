<?php
require_once __DIR__ . '/input.php';
$id = recordId($_GET['id'] ?? null);
$old = getHousehold($id);
$form = $old;
$errors = [];
if (isPost()) {
    $form = householdInput();
    $values = $form;
    $errors = prepareHousehold($values, $old);
    if (!$errors) {
        try {
            $values['id'] = $id;
            $stmt = db()->prepare('UPDATE households SET
                household_code = :household_code, representative_name = :representative_name, contact_phone = :contact_phone,
                purok = :purok, address_details = :address_details, registry_reason = :registry_reason,
                hazard_type = :hazard_type, hazard_notes = :hazard_notes, vulnerability_category = :vulnerability_category,
                vulnerability_notes = :vulnerability_notes, vulnerability_assessed_by = :vulnerability_assessed_by, vulnerability_assessed_at = :vulnerability_assessed_at,
                evacuation_status = :evacuation_status, latitude = :latitude, longitude = :longitude
            WHERE id = :id');
            $stmt->execute($values);
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
