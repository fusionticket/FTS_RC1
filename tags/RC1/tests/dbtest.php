<?php
  $link = new mysqli('localhost', 'root', '', 'bret_holstein');
  echo 'A:'.time(),"<br>\n";

//  $link->query('START TRANSACTION');//
  $link->autocommit(false);

  $result = $link->query('select order_custom3 x from `Order` where order_id=17 for update');
  $row = $result->fetch_array();
  $row = (int) $row['x'];
  echo 'B:'.time()," -> $row","<br>\n";
  if (isset($_GET['wait'])) {
   sleep(20);
  }
  $link->query('update `Order` set order_custom3='.$row.'+1 where order_id=17');
  $link->commit();
  echo 'C:'.time(),"<br>\n";
//  $link->autocommit(true);
?>