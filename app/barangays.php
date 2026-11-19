<?php
// Philippine Statistics Authority: https://psa.gov.ph/classification/psgc/barangays/1401101000
const ATOK_BARANGAYS = ['Abiang', 'Caliking', 'Cattubo', 'Naguey', 'Paoay', 'Pasdong', 'Poblacion', 'Topdac'];

function filterBarangay(array $rows): array
{
    $selection = $_GET['barangay'] ?? '';
    if ($selection === '') {
        return $rows;
    }
    return array_filter($rows, fn($row) => ($row['barangay'] ?? null) === $selection);
}
