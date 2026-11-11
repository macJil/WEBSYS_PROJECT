<?php
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../models/Household.php';
class HouseholdController extends Controller
{
    public const VULNERABILITY = ['unassessed'=>'Not yet assessed','no_additional_need_identified'=>'No additional assistance need identified','assistance_needed'=>'Additional assistance needed'];
    public const HAZARDS = ['unassessed'=>'Not assessed','landslide'=>'Landslide','flood'=>'Flood','fire'=>'Fire','other'=>'Other'];
    public const REASONS = ['monitored'=>'Monitored','affected'=>'Affected','both'=>'Monitored and affected'];
    public const EVACUATION = ['at_home'=>'At home','advised'=>'Advised to evacuate','evacuated'=>'Evacuated','returned'=>'Returned'];
    private HouseholdModel $households;
    public function __construct()
    {
        requireRole('admin');
        $this->households = new HouseholdModel();
    }
    public function id($value): int
    {
        $id = is_scalar($value) ? filter_var($value, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]) : false;
        if ($id === false) { http_response_code(400); exit('Invalid record ID.'); }
        return $id;
    }
    public function find(int $id): array
    {
        $row = $this->households->findById($id);
        if (!$row) { http_response_code(404); exit('Household not found.'); }
        return $row;
    }
    public function member(int $id, int $householdId): array
    {
        $row = $this->households->findMember($id,$householdId);
        if (!$row) { http_response_code(404); exit('Member not found in this household.'); }
        return $row;
    }
    public function listing(string $search): array { return $this->households->getAll($search); }
    public function members(int $id): array { return $this->households->members($id); }
    public function householdInput(): array
    {
        $data=[];
        foreach (['household_code','representative_name','contact_phone','purok','address_details','registry_reason','hazard_type','hazard_notes','vulnerability_category','vulnerability_notes','evacuation_status'] as $field) { $data[$field]=input($field); }
        return $data;
    }
    public function saveHousehold(?int $id, array $data): array
    {
        if (!isPost()) { return []; }
        if (input('action') !== ($id === null ? 'create_household' : 'update_household')) { return ['Invalid form action.']; }
        $old = $id === null ? null : $this->find($id);
        $errors=[];
        foreach (['household_code'=>30,'representative_name'=>100,'purok'=>100] as $field=>$max) {
            if ($data[$field] === '' || strlen($data[$field]) > $max) { $errors[] = str_replace('_',' ',ucfirst($field)) . ' is required and must fit within ' . $max . ' bytes.'; }
        }
        foreach (['address_details','hazard_notes','vulnerability_notes'] as $field) {
            if (strlen($data[$field]) > 255) { $errors[] = str_replace('_',' ',ucfirst($field)) . ' must fit within 255 bytes.'; }
        }
        if ($data['contact_phone'] !== '' && !preg_match('/^[0-9+() .-]{7,20}$/',$data['contact_phone'])) { $errors[]='Enter a valid emergency contact number (7–20 characters), or leave it blank.'; }
        foreach (['registry_reason'=>self::REASONS,'hazard_type'=>self::HAZARDS,'vulnerability_category'=>self::VULNERABILITY,'evacuation_status'=>self::EVACUATION] as $field=>$options) {
            if (!array_key_exists($data[$field],$options)) { $errors[]='Choose a valid ' . str_replace('_',' ',$field) . '.'; }
        }
        if ($data['vulnerability_category'] !== 'unassessed' && $data['vulnerability_notes'] === '') { $errors[]='Record the assessment source and practical assistance notes.'; }
        if ($errors) { return $errors; }
        $assessed = $data['vulnerability_category'] !== 'unassessed';
        $changed = !$old || $data['vulnerability_category'] !== $old['vulnerability_category'] || $data['vulnerability_notes'] !== ($old['vulnerability_notes'] ?? '');
        $data['vulnerability_notes'] = $assessed ? $data['vulnerability_notes'] : null;
        $data['vulnerability_assessed_by'] = $assessed ? ($changed ? $_SESSION['ID'] : $old['vulnerability_assessed_by']) : null;
        $data['vulnerability_assessed_at'] = $assessed ? ($changed ? date('Y-m-d H:i:s') : $old['vulnerability_assessed_at']) : null;
        try {
            $savedId=$this->households->save($data,$id);
            $this->success('Household saved.', 'resources/views/admin/household.php?id=' . $savedId);
        } catch (PDOException $error) {
            error_log((string)$error);
            return [$error->getCode() === '23000' ? 'That household code is already used, or the record could not be linked. Choose a unique code.' : 'The household could not be saved. Please try again.'];
        }
    }
    public function saveMember(int $householdId, ?int $memberId, array $data): array
    {
        if (!isPost()) { return []; }
        if (input('action') !== ($memberId === null ? 'create_member' : 'update_member')) { return ['Invalid form action.']; }
        $this->find($householdId);
        if ($memberId !== null) { $this->member($memberId,$householdId); }
        $errors=[];
        if ($data['full_name'] === '' || strlen($data['full_name'])>100) { $errors[]='Enter a member name of up to 100 bytes.'; }
        if (strlen($data['notes'])>255) { $errors[]='Member notes must fit within 255 bytes.'; }
        $age=$data['age'] === '' ? null : filter_var($data['age'], FILTER_VALIDATE_INT, ['options'=>['min_range'=>0,'max_range'=>130]]);
        if ($age === false) { $errors[]='Enter a whole-number age from 0 to 130, or leave it blank.'; }
        if ($errors) { return $errors; }
        $data['age']=$age;
        try {
            $this->households->saveMember($householdId,$memberId,$data);
            $this->success('Household member saved.', 'resources/views/admin/household.php?id=' . $householdId);
        } catch (PDOException $error) {
            error_log((string)$error);
            return ['The member could not be saved. Please try again.'];
        }
    }
}
