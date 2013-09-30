<?php
  define('ft_check','shop');
  require_once('../includes/config/init_common.php');
   //  require_once('../includes/config/init.php');
   $_SHOP->session_name = 'pmp_test';
  session_name('pmp_test');
  session_start();
  if (empty($_REQUEST)) {
    session_destroy();
    session_start();
  }
  global $_SHOP;
  require_once(INC.'admin/class.adminview.php');

  $doubles = $pmp->find_doubles($zone_ident);

 // $this_zone = $pmp->zones[$zone_ident];

  $zone_bounds = array();// $pmp->zone_bounds($zone_ident);
  $select = (isset($_POST['selection']))? ($_POST['selection']):array();
  //     $select = explode('||',$select);
  $array = array();
  foreach ($select as $key) {
    $key = explode('_',$key);
    $array[$key[1]][$key[2]] = $key;
    if (!isset($zone_bounds['top'])    || $zone_bounds['top']>$key[1])    { $zone_bounds['top'] =$key[1];}
    if (!isset($zone_bounds['bottom']) || $zone_bounds['bottom']<$key[1]) { $zone_bounds['bottom'] =$key[1];}
    if (!isset($zone_bounds['left'])   || $zone_bounds['left']>$key[2])   { $zone_bounds['left'] =$key[2];}
    if (!isset($zone_bounds['right'])  || $zone_bounds['right']<$key[2])  { $zone_bounds['right'] =$key[2];}
  }
  ?><head></head>
<body>
  <?php
  // title

  $thisx = new adminview ('100%');
?>
<div id="tabs">
    <ul>
        <li><a href="#tabs-1">Manual config</a></li>
        <li><a href="#tabs-2">Automaticly</a></li>
    </ul>
    <div id="tabs-1">
<?php
  // numbering
  echo "<form action='remote.php' method=POST name='manualform'>
          <input type=hidden name=action value='pmz_save_num_pmp'>
        	<input type='hidden' name='pmp_id' value='$pmp_id'>
       	<input type='hidden' name='pmz_ident' value='$zone_ident'> ";
  // <input type='hidden' name='pm_ort_id' value='{$pm['pm_ort_id']}'>";
  $thisx->list_head(con('seat_numbering'), 1);
  echo "<tr><td align=center><table>";

  for($j = $zone_bounds['top'];$j <= $zone_bounds['bottom'];$j++) {
    echo "<tr>";;
    for($k = $zone_bounds['left'];$k <= $zone_bounds['right'];$k++) {
      $seat = $pmp->data[$j][$k];

      if ($z = (int)$seat[PM_ZONE]) {
        $zone = $zones[$z];
        $col = "background-color:{$zone->pmz_color};";
      } else {
        $col = '';
      }

      echo "<td  style='$col'>"; //
      if (is_numeric($seat[PM_ZONE]) &&  ($seat[PM_ZONE] || $seat[PM_CATEGORY]) && isset($array[$j][$k])){
        if ($doubles[$j][$k]) {
          echo "<input type='text' name='seat[$j][$k]' value='{$seat[PM_ROW]}/{$seat[PM_SEAT]}' size='4' style='font-size:8px;color:#cc0000;'>";
        } else {
          echo "<input type='text' name='seat[$j][$k]' value='{$seat[PM_ROW]}/{$seat[PM_SEAT]}' size='4' style='font-size:8px;'>";
        }
      } else {
        echo "&nbsp;";
      }
      echo "</td>\n";
    }
    echo "</tr>\n";
  }

  echo "</table>";
  $thisx->form_foot(1);
  // echo "<tr><td align='center' class='admin_value' colspan='2'>
  // <input type=hidden name=action value='set_zone_num'>
  // <input type='submit' name='save' value='".save."'>
  // </tr></table><br>";
  // auto numbering
?>
</div>
    <div id="tabs-2">
<?php
  echo "<form action='{$_SERVER['PHP_SELF']}' method=POST>
      <input type='hidden' name='pmp_id' value='$pmp_id'>
       <input type='hidden' name='pmz_ident' value='$zone_ident'>
       <input type='hidden' name='action' value='pmz_auto_num_pmp'>";

  $thisx->form_head(con('autonumber_pmz'));

  if (!isset($data['first_row'])) {
    $data['first_row'] = 1;
  }
  if (!isset($data['step_row'])) {
    $data['step_row'] = 1;
  }
  if (!isset($data['first_seat'])) {
    $data['first_seat'] = 1;
  }
  if (!isset($data['step_seat'])) {
    $data['step_seat'] = 1;
  }

  $thisx->print_input('first_row', $data, $err, 3, 4);
  $thisx->print_input('step_row', $data, $err, 3, 4);
  $thisx->print_checkbox('inv_row', $data, $err);
  $thisx->print_input('first_seat', $data, $err, 3, 4);
  $thisx->print_input('step_seat', $data, $err, 3, 4);
  $thisx->print_checkbox('inv_seat', $data, $err);
  $thisx->print_checkbox('flip', $data, $err);
  $thisx->print_checkbox('keep', $data, $err);

  $thisx->form_foot();
?>
  </div>
</div>
<script>
$("#tabs").tabs();
</script>
</body>