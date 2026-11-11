<?php
require_once __DIR__ . '/../models/Announcement.php';

require_once __DIR__ . '/../controllers/Controller.php';
class AnnouncementController extends Controller{
  private $announcements ;
  function __construct()
  {
    $this->announcements = new AnnouncementModel();
  }
  public function store_announcement(){
    if(isset($_POST['submit_message'])){
      $title = trim($_POST['title']);
      $message = trim($_POST['message']);
      $created_by = $_SESSION['ID'];
      
      if ($title === '' || $message === '' || $created_by === '') {
        
        return parent::results_checker('Complete all fields.',0);
      }
      try{
        $this->announcements->store_announcement($title,$message,$created_by);
        return parent::results_checker("Announcement Added!",1);
      }catch(PDOException $e){
        error_log((string) $e);
        return parent::results_checker('The request could not be completed.',0);
      }
    }
  }
  public function get_announcements(){
    
  try{
      return $this->announcements->get_all_announcemnt(($_SESSION['ROLE'] ?? '') !== 'admin');
    }catch(PDOException $e){
      error_log((string) $e);
        return [];
    }
  }
  public function delete_announcements(){
    if (!isset($_POST['delete_announcements'])) {
      return null;
    }

    $ids = $_POST['announcements'] ?? [];
    if (!is_array($ids) || $ids === []) {
      return parent::results_checker('Select at least one announcement.', 0);
    }

    try {
      foreach ($ids as $id) {
        $id = filter_var($id, FILTER_VALIDATE_INT);
        if ($id === false || $id < 1) {
          return parent::results_checker('Invalid announcement ID.', 0);
        }

        $this->announcements->delete_announcement($id);
      }

      return parent::results_checker('Successfully Deleted!', 1);
    } catch (PDOException $e) {
      error_log($e->getMessage());
      return parent::results_checker('Error deleting announcements.', 0);
    }
  }
  public function get_announcement_by_id($id){
    try{
      return $this->announcements->get_by_id($id);
    }catch(PDOException $e){
      error_log((string) $e);
        return parent::results_checker('The request could not be completed.',0);
    }
  }
  public function update_announcement(){
    if(isset($_POST['update'])){
      
      $title = trim($_POST['title']);
      $message = trim($_POST['message']);
      $created_at = trim($_POST['created_at']);
      $is_published = trim($_POST['is_published']);
      $id = trim($_POST['id']);
      if ($title === '' || $message === '' || $created_at === '' || $is_published === '') {
        
        return parent::results_checker('Complete all fields.',0);
      }
      try{
        $this->announcements->update($title, $message, $created_at, $is_published,$id);
        $_SESSION['FLASH'] = parent::results_checker("Successfully Updated Announcement!",1);
        header('Location: ' . BASE_URL . '/resources/views/admin/announcement/announcement.php');
        exit;
      }catch(PDOException $e){
        error_log((string) $e);
        return parent::results_checker('The request could not be completed.',0);
      }
    }
  }

}


?>