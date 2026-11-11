<?php
require_once __DIR__ . '/../models/Report.php';

require_once __DIR__ . '/../controllers/Controller.php';
class ReportController extends Controller
{
  private $reports;
  function __construct()
  {
    $this->reports = new ReportModel();
    
  }

  public function store_report(){
    
    if(isset($_POST['store_report'])){
      $incident_type = trim($_POST['incident_type']); 
      $description = trim($_POST['description']);
      if ($incident_type === '' || $description === '') {
        return parent::results_checker('Complete all fields.',0);
      }
      try{
        $this->reports->add_report(
          $incident_type,
          $description,
          $_SESSION['ID'],
        );
        return parent::results_checker('Report Successful!',1);
        exit();
      } catch (PDOException $e) {
        error_log($e->getMessage());
        return parent::results_checker('Error Reporting Incident!',0);
      }

      $_SESSION['flash'] = 'Report Successful!';
      header('Location: ' . BASE_URL . '/resources/views/user/report.incident.php');
      exit;
    }
    return null;
  }
  
  public function get_report_by_id($id){
    try{
      return $this->reports->get_report_by_id($id);
    }catch(PDOException){
      return parent::results_checker("ERROR getting your reports",0);
    }
    
  }

  public function get_all_reports(){
    try{
      return $this->reports->get_all();
    }catch(PDOException){
      return parent::results_checker("ERROR getting reports",0);
    }
    
  }
  public function delete_reports(){
    if (!isset($_POST['delete_reports'])) {
      return null;
    }

    $ids = $_POST['reports'] ?? [];
    if (!is_array($ids) || $ids === []) {
      return parent::results_checker('Select at least one report.', 0);
    }

    try {
      foreach ($ids as $id) {
        $id = filter_var($id, FILTER_VALIDATE_INT);
        if ($id === false || $id < 1) {
          return parent::results_checker('Invalid report ID.', 0);
        }

        $this->reports->delete($id);
      }

      return parent::results_checker('Successfully Deleted!', 1);
    } catch (PDOException $e) {
      error_log($e->getMessage());
      return parent::results_checker('Error deleting reports.', 0);
    }

  }
  
  public function update_report(){
    if(isset($_POST['update'])){
      $incident_type = trim($_POST['incident_type'] ?? '');
      $description = trim($_POST['description'] ?? '');
      $location_description = trim($_POST['location_description'] ?? '');
      $contact_phone = trim($_POST['contact_phone'] ?? '');
      $status = trim($_POST['status'] ?? '');
      $created_at = trim($_POST['updated_at'] ?? '');
      $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
      if ($id === false || $id < 1) {
        return parent::results_checker('Invalid report ID.', 0);
      }
      if ($incident_type === '' || $description === '' || $status === '' || $created_at === '') {
        
        return parent::results_checker('Complete all fields.',0);
      }
      try{
        $this->reports->update($incident_type, $description,$location_description,$contact_phone,$status,$created_at,$id);
        $_SESSION['FLASH'] = parent::results_checker("Successfully Updated Report!",1);
        header('Location: ' . BASE_URL . '/resources/views/admin/reports/report.php');
        exit;
      } catch (PDOException $e) {
        error_log($e->getMessage());
        return parent::results_checker('Error updating reports.', 0);
      }
    }
  }
  public function count_type(){
    $reports = $this->get_all_reports();
    $type = ['fire'=>count($reports['fire']),'landslide'=>count($reports['landslide']),'flood'=>count($reports['flood']),'road_obstruction'=>count($reports['road_obstruction']),'other'=>count($reports['other'])];
    return $type;
  }
}


?>