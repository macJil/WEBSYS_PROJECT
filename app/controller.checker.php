<?php
function results_checker($result, $status){
    $result = htmlspecialchars($result);
    
  if ($result !== null && $status === 0){
    return "<p class='text-danger'>$result</p>";
  }
  if ($result !== null && $status === 1){
    return "<p class='text-success'>$result</p>";
  }
}

?>