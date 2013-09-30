<?php

  require_once("../includes/libs/functions/array_replace_recursive.php");
  
  /* as of PHP 5.3.0 array_replace_recursive() does the work for us
  if (function_exists('array_replace_recursive'))
  {
    return call_user_func_array('array_replace_recursive', func_get_args());
  }*/

$defaultEmailOptions = array(
     "ordered"=>array("opt","opt"),
     "reserved"=>array("opt","opt"),
     "paid"=>array("opt","opt"),
     "unpaid"=>array("opt","opt"),     
     "sent"=>array("opt","opt"),
     "unsent"=>array("opt","opt"),
     "cancelled"=>array("opt","opt")
  );
  
  $defaultEmailOptions2 = array(
     "reserved"=>array("opt","none"),
     "paid"=>array("req","opt"),
     "unpaid"=>array("req","opt"),     
     "sent"=>array("req","req"),
     "unsent"=>array("opt","none"),
     "cancelled"=>array("opt","none")
  );
  
  $res = array_merge_recursive($defaultEmailOptions,$defaultEmailOptions2);
  echo "<pre>";
  print_r($res);
  echo "</pre>";
  
  $res = array_replace_recursive($defaultEmailOptions,$defaultEmailOptions2);
  echo "<pre>";
  print_r($res);
  echo "</pre>";
  

?>