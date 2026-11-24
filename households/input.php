<?php
require_once __DIR__ . '/common.php';

function householdInput()
{
    $data = [];
    foreach (
        [
            'household_code',
            'representative_name',
            'contact_phone',
            'barangay',
            'purok',
            'address_details',
            'registry_reason',
            'hazard_type',
            'hazard_notes',
            'vulnerability_category',
            'vulnerability_notes',
            'evacuation_status',
            'latitude',
            'longitude',
        ]
        as $field
    ) {
        $data[$field] = input($field);
    }
    return $data;
}

function prepareHousehold(array &$data, ?array $old)
{
    if (input('action') !== ($old === null ? 'create_household' : 'update_household')) {
        return ['Invalid form action.'];
    }
    if (!empty($old['archived_at'])) {
        return ['Archived households cannot be edited.'];
    }
    $errors = locationErrors($data['address_details'], $data['latitude'], $data['longitude']);
    if (!in_array($data['barangay'], ATOK_BARANGAYS, true)) {
        $errors[] = 'Choose an Atok barangay.';
    }
    foreach (['household_code' => 30, 'representative_name' => 100, 'purok' => 100] as $field => $max) {
        if ($data[$field] === '' || strlen($data[$field]) > $max) {
            $errors[] =
                str_replace('_', ' ', ucfirst($field)) .
                ' is required and must fit within ' .
                $max .
                ' bytes.';
        }
    }
    foreach (['address_details', 'hazard_notes', 'vulnerability_notes'] as $field) {
        if (strlen($data[$field]) > 255) {
            $errors[] = str_replace('_', ' ', ucfirst($field)) . ' must fit within 255 bytes.';
        }
    }
    if ($data['contact_phone'] !== '' && !validPhone($data['contact_phone'], 7)) {
        $errors[] = 'Enter a valid emergency contact number (7–20 characters), or leave it blank.';
    }
    foreach (
        [
            'registry_reason' => HOUSEHOLD_REASONS,
            'hazard_type' => HOUSEHOLD_HAZARDS,
            'vulnerability_category' => HOUSEHOLD_VULNERABILITY,
            'evacuation_status' => HOUSEHOLD_EVACUATION,
        ]
        as $field => $options
    ) {
        if (!array_key_exists($data[$field], $options)) {
            $errors[] = 'Choose a valid ' . str_replace('_', ' ', $field) . '.';
        }
    }
    if ($data['vulnerability_category'] !== 'unassessed' && $data['vulnerability_notes'] === '') {
        $errors[] = 'Record the assessment source and practical assistance notes.';
    }
    if ($errors) {
        return $errors;
    }
    foreach (['latitude', 'longitude'] as $field) {
        $data[$field] = $data[$field] === '' ? null : $data[$field];
    }
    $assessed = $data['vulnerability_category'] !== 'unassessed';
    $changed =
        !$old ||
        $data['vulnerability_category'] !== $old['vulnerability_category'] ||
        $data['vulnerability_notes'] !== ($old['vulnerability_notes'] ?? '');
    $data['vulnerability_notes'] = $assessed ? $data['vulnerability_notes'] : null;
    $data['vulnerability_assessed_by'] = $assessed
        ? ($changed
            ? $_SESSION['ID']
            : $old['vulnerability_assessed_by'])
        : null;
    $data['vulnerability_assessed_at'] = $assessed
        ? ($changed
            ? date('Y-m-d H:i:s')
            : $old['vulnerability_assessed_at'])
        : null;
    return [];
}
