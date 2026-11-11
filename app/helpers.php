<?php

define('BASE_URL', '/WEBSYS_PROJECT');
start_app_session();


function start_app_session(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}






function session_checker(): void
{
    if (empty($_SESSION['EMAIL'])) {
        header('Location: ' . BASE_URL . '/index.php');
        exit;
    }
}

function content_checker($data){
    return (count($data)<1) ? '<h1>No Data Found...</h1>' : 'hidden';
         
    
}

function delete(){
    if(isset($_POST['confirm_delete'])){
        
    
        $id = $_POST['id'];
        $category = $_POST['cat'];
        $categories = ['Announcement','User','Report'];
        if ((!$id || $category !== 'Announcement') && in_array($category,$categories)) {
            http_response_code(400);
            exit('Invalid delete request.');
        }
        foreach($categories as $cat){
            if($category === $cat){
                $contoller = $cat . 'Controller';
                $delete = new $contoller();
                return $delete->delete($id);
                
            }

        }
    }
    
}



