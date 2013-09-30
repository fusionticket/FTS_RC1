<?php
/**
 %%%copyright%%%
 *
 * FusionTicket - ticket reservation system
 *  Copyright (C) 2007-2013 FusionTicket Solution Limited . All rights reserved.
 *
 * Original Design:
 *  phpMyTicket - ticket reservation system
 *   Copyright (C) 2004-2005 Anna Putrino, Stanislav Chachkov. All rights reserved.
 *
 * This file is part of FusionTicket.
 *
 * This file may be distributed and/or modified under the terms of the
 * "GNU General Public License" version 3 as published by the Free
 * Software Foundation and appearing in the file LICENSE included in
 * the packaging of this file.
 *
 * This file is provided AS IS with NO WARRANTY OF ANY KIND, INCLUDING
 * THE WARRANTY OF DESIGN, MERCHANTABILITY AND FITNESS FOR A PARTICULAR
 * PURPOSE.
 *
 * Any links or references to Fusion Ticket must be left in under our licensing agreement.
 *
 * By USING this file you are agreeing to the above terms of use. REMOVING this licence does NOT
 * remove your obligation to the terms of use.
 *
 * The "GNU General Public License" (GPL) is available at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * Contact help@fusionticket.com if any conditions of this licencing isn't
 * clear to you.
 */

if (!defined('ft_check')) {die('System intrusion ');}
require_once(INC."classes/redundantdatachecker.php");
require_once(INC."classes".DS."class.model.php");
//require_once(INC."classes".DS."model.organizer.php");


class install_execute {

  static function precheck($Install, $configpath = false) {
    global $_SHOP;
    $tempdir = is($_SESSION['SHOP']['tmp_dir'],ROOT."includes/temp");
    RemoveDir($tempdir,false);
    $install_mode=$_SESSION['radio'];

    OpenDatabase();
    if (!ShopDB::$link) {
      array_push($Install->Errors,"Can not connect to the database.");
      return true;
    }

    if($install_mode == 'NORMAL'){
      $Table_Names = ShopDB::TableList('');
      for ($i=0;$i<count($Table_Names);$i++){
        ShopDB::query("drop table `{$Table_Names[$i]}`");
      }
    }

    global $tbls;
    require_once(ROOT."includes/install/data_db.php");
    if ($errors = ShopDB::DatabaseUpgrade($tbls, true)){
      foreach ($errors as $data) {
        if ($data['error']) {
          $Install->Errors[] = "<pre>".$data['changes']. $data['error']."</pre>";
        }
      }
      if ($Install->Errors) return true;
    }
    if (ShopDB::Tableexists('SPoint')){
      $Install->Warnings[] = "<pre>Migrated Spoint table</pre>";
      self::MigrateSpoint();
    }
    if (ShopDB::Tableexists('Control')){
      $Install->Warnings[] = "<pre>Migrated Control table</pre>";
      self::MigrateControl();
    }

    if (ShopDB::Tableexists('Organizer')){
      $Install->Warnings[] = "<pre>Removed Organizer table</pre>";
      self::renameTables(array('Organizer'));
    }
    if (ShopDB::Tableexists('ShopConfig')){
      $Install->Warnings[] = "<pre>Removed ShopConfig table</pre>";
      self::MigrateShopConfig();
    }

    self::renameTables(array('Color','Payment_log','sessions','Sessions'));

    if ($install_mode == 'NORMAL'){
      // import contens of mysqldump to db
      if ($error = file_to_db(ROOT."includes/install/base_sql.sql")){
        array_push($Install->Errors,$error);
        return true;
      }

      if ($_SESSION['db_demos']==1 and $error = file_to_db(ROOT."includes/install/demo_sql.sql")){
        array_push($Install->Errors,$error);
        return true;
      }
      $_SESSION['admin_realname'] = is($_SESSION['admin_realname'],'');
      $query = "update Admin set
                  admin_login='{$_SESSION['admin_login']}',
                  admin_realname='{$_SESSION['admin_realname']}',
                  admin_password=md5('{$_SESSION['admin_password']}'),
                  admin_status='admin'
                where admin_id = 1";

      if (!shopDB::query($query)){
        array_push($Install->Errors,"Admin user can not be created! \n".ShopDB::error());
        return true;
      }
    }
    self::recalcStats($Install);
    self::moveOrderNotes($Install);

    Orphans::clearZeros('Category',     array('category_pm_id','category_event_id','category_pmp_id'));
    Orphans::clearZeros('Event',        array('event_group_id','event_main_id'));
    Orphans::clearZeros('Order',        array('order_owner_id', 'order_admin_id'));
    Orphans::clearZeros('PlaceMapPart', array('pmp_pm_id','pmp_ort_id','pmp_event_id'));
    Orphans::clearZeros('Seat',         array('seat_category_id','seat_zone_id' ,'seat_user_id' ,
                                              'seat_order_id'   ,'seat_pmp_id'  ,'seat_discount_id'));

    shopDB::query("UPDATE Template set template_status='new'");
    shopDB::query("UPDATE Template set template_type='systm' where  template_type='email' and template_name='forgot_passwd'");
    shopDB::query("UPDATE Template set template_type='systm' where  template_type='email' and template_name='Signup_email'");
    shopDB::query("UPDATE Template set template_type='systm' where  template_type='email' and template_name='email_res'");
    shopDB::query("UPDATE `Order`  set order_payment_status='cancelled' where order_payment_status='canceled'");
    shopDB::query("UPDATE `Order`  set order_payment_status='paid'      where order_payment_status='payed'");
    shopDB::query("UPDATE `User`   set user_status=1 where user_status=0");

    if ((install_execute::CreateConfig($configpath)===false) or !file_exists($configpath)){
      array_push($Install->Errors,"Configuration file is not saved correctly check the folder permissions!");
      return true;
    }
    install_execute::fillConfigDB();

    if (getophandata()!=='none') {
      array_push($Install->Warnings,'After the update the installer found some problems with your database.<br>'.
                                    'To use with the new version we suggest fixing the database or create an new database.');

      return true ;
    }

    return false;
  }

  static function postcheck($Install) {
    if (is($_POST['fixdatabase'],0)==2) {
      self::renameTables(array('Category','Discount','Event','Event_group',
                               'PlaceMap2','PlaceMapPart','PlaceMapZone','Seat','Order'));
      array_push($Install->Warnings,"The next tables are renamed:
                                     Category, Discount, Event, Event_group, PlaceMap2, PlaceMapPart, PlaceMapZone,Seat, Order.
                                     You can copy the data back yourself.");
    }
    return false;
  }

  static function fillConfigDB() {
    $config = config::load();
    $config->_fill($_SESSION['CONFIG']);
    $config->Save();
  }

  static function fillConfig($array, $suffix, $eq=' = ',$isarray=false){
    $arr_type = array('langs_names','valutas','event_group_type_enum','event_type_enum');
    $config  =($isarray!==2)?'':array();
    foreach ($array as $key =>$value) {
      if (is_int($key)){
        $key= '';
        if ($isarray===1) { $key = "[]"; }
      } else {
        if ($isarray===1) { $key = "['$key']"; }
        if ($isarray===2) { $key = "'$key'"; }
      }
      if (is_array($value)) {
        $x = (!$isarray and !in_array($key,$arr_type ))?2:(($isarray)?$isarray:1);
        if ($x==1) {
          $config .= self::fillConfig($value, $suffix .$key, $eq,  $x);
        } else {
          $config .= "{$suffix}{$key} {$eq} array(";
          $item    = self::fillConfig($value, '', '=>',  $x);
          $config .= implode(", " ,$item );
          $config .= ");\n";
        }
        continue;
      } elseif(is_null($value)) {
        $value = 'null';
      } elseif(is_bool($value)) {
        $value = ($value)?'True':'False';
      } elseif(is_string($value)) {
        $value = _esc($value);
      }
      If ($isarray==2) {
        if (strlen(trim("{$suffix}{$key}"))==0) {
          $config[] = "{$value}";
        } else
          $config[] = "{$suffix}{$key} {$eq} {$value}";
      } elseif (strlen(trim("{$suffix}{$key}"))==0) {
        $config .= "{$value}";
      } else
        $config .= "{$suffix}{$key} {$eq} {$value};\n";
    }

    return $config;
  }

  static function CreateConfig(&$configpath) {
    $storetmp = !empty($configpath);
    $config = "<?php\n";
    $config .= "/**\n";
    $config .= " *  %%%copyright%%%\n";
    $config .= file_get_contents (ROOT."licence.txt")."\n";
    $config .= " */\n\n";
    $config .= "// Create on: ".date('r')."\n\n";
    $config .= "// The following settings are automatically filled by the installation procedure:\n\n";
    //   $config .= "global \$_SHOP;\n\n";
    $config .= "define(\"CURRENT_VERSION\",\"".INSTALL_VERSION."\");\n\n";

    unset($_SESSION['SHOP']['install_dir']);
    if (is($_SESSION['fixed_url'],false)) {
      $_SESSION['SHOP']['root'] = BASE_URL."/";

      if (!isset($_SESSION['SHOP']['root_secured']) or empty($_SESSION['SHOP']['root_secured'])) {
        $_SESSION['SHOP']['root_secured'] = $_SESSION['SHOP']['root'];
      }
    } else {
      unset ($_SESSION['SHOP']['root']);
      unset ($_SESSION['SHOP']['root_secured']);
      if (!$storetmp) {unset ($_SESSION['SHOP']['tmp_dir']); }
    }
    if (!isset($_SESSION['SHOP']['secure_id'])) {
      if (!isset($_SESSION['BASE_URL'])) {
        $_SESSION['BASE_URL'] = BASE_URL;
      }
      $_SESSION['SHOP']['secure_id'] = sha1(AUTH_REALM. $_SESSION['BASE_URL'] . uniqid());
    }
    ksort($_SESSION['SHOP']);
    $config .= self::fillConfig($_SESSION['SHOP'],'$_SHOP->');
    $config .= "\n?>";
    $configpath = ($configpath)?$configpath:(ROOT."includes".DS."config".DS."init_config.php");
    return file_put_contents($configpath, $config);
  }

  static function display($Install) {
    global $_SHOP, $orphancheck;
    OpenDatabase();
    if(isset($_GET['fix'])){
      Orphans::dofix($_GET['fix']);
    }
    $data = Orphans::getlist($keys,true,"&do=fix&inst_mode=post&inst_pg={$Install->return_pg}");

    $space = (count($keys)*60 < 780 -200)?1:0;
    Install_Form_Open ($Install->return_pg,'', 'Database Orphan check');

    echo "<table cellpadding=\"1\" cellspacing=\"2\" width='100%'>

            <tr><td>
              The list below gives you a view of the orphans in your database. Look at our website for instructions how to fix this or contact us on the forum or IRC.
              To be safe, we suggest creating a new database and importing the common information. This can be done by the installer.
            </td></tr>
            <tr> <td height='6px'></td> </tr>
            <tr> <td>
               <input type=\"radio\" name=\"fixdatabase\" value=\"1\" id='fixdatabase1'  checked /><label for='fixdatabase1'> Fix tables manual </label>
               <input type=\"radio\" name=\"fixdatabase\" value=\"2\" id='fixdatabase2' /><label for='fixdatabase2'>  Recreate tables </label>
            </td> </tr>

          </table>";

    echo "<div style='overflow: auto; height: 250px; width:100%; border: 1'>";
    echo "<table cellpadding=\"1\" cellspacing=\"2\" width='100%'>";
    print " <tr class='admin_list_header'>
              <th width=130 align='left'>
                Tablename
              </th>
              <th width=50 align='right'>
                ID
              </th>";
    foreach ($keys as $key) {
      print "<th width=60 align='center'> {$key}&nbsp;</th>";
    }
    if ($space) {
      print "<th align='center'>&nbsp;</th>";
    }

    print "</tr>";
    $alt =0;
    foreach ($data as $row) {
      print "<tr class='admin_list_row_$alt'>
        <td class='admin_list_item'>{$row['_table']}</td>
        <td class='admin_list_item' align='right'>{$row['_id']}</td>\n";
      foreach ($keys as $key) {
        print "<td align='center'>".((isset($row[$key]))?$row[$key]:'&nbsp;')."</td>\n";
      }
      if ($space) {
        print "<th align='center'>&nbsp;</th>";
      }
      print "</tr>";
      $alt = ($alt + 1) % 2;
    }
    echo "</table></div>\n";
    Install_Form_Buttons ();
    Install_Form_Close ();
  }

  static function checkadmin($name) {
    $query="select Count(*) as count
            from Admin
            where admin_login= "._esc($name);
    if(!$res=ShopDB::query_one_row($query)){
      user_error(shopDB::error());
    } else
      return ($res["count"]>0);
  }

  static function MigrateSpoint() {
    $query = "select * from SPoint";
    $res = ShopDB::Query($query);
    while ($row = ShopDB::fetch_assoc($res)){
      If (self::checkAdmin($row['login'])) $row['login'] = "pos~{$row['login']}";
      $query = "INSERT INTO `Admin` SET ".
         "admin_login = '{$row['login']}',
          admin_password = '{$row['password']}',
          admin_user_id = '{$row['user_id']}',
          admin_status = 'pos'";
      ShopDB::query($query);
    }
    $sql = "RENAME TABLE `SPoint` TO  `old_spoint`"; // The MySQL way.
    ShopDB::query($sql);
  }

  static function MigrateShopConfig() {
    $query = "select * from ShopConfig";
    $res = ShopDB::Query($query);
    $row = ShopDB::fetch_assoc($res);
    foreach($row as $key => $value) {
      $_SESSION['CONFIG'][$key] = $value;
    }
    $sql = "RENAME TABLE `ShopConfig` TO  `old_shopconfig`"; // The MySQL way.
    ShopDB::query($sql);
  }

  static function MigrateControl(){
    $query = "select * from `Control`";
    $res = ShopDB::Query($query);
    while ($row = ShopDB::fetch_assoc($res)){
      If (self::checkAdmin($row['control_login'])) $row['control_login'] = "tt~{$row['control_login']}";
      $query = "INSERT INTO `Admin` SET ".
         "admin_login = '{$row['control_login']}',
          admin_password = '{$row['control_password']}',
          control_event_ids = '{$row['control_event_ids']}',
          admin_status = 'control'";
      ShopDB::query($query);
    }
    $sql = "RENAME TABLE `Control` TO  `old_control`"; // The MySQL way.
    ShopDB::query($sql);
  }

  static function renameTables($array) {
    if (is_array($array)) {

      foreach($array as $table) {
        $no = '';
        if (ShopDB::Tableexists($table)) {
          while (ShopDB::TableExists("old{$no}_{$table}")) { $no = (int)$no +1; }
          $sql = "RENAME TABLE `{$table}` TO `old{$no}_{$table}`"; // The MySQL way.
          ShopDB::query($sql);
        }
      }
    }
  }

  static function recalcStats($Install) {
    if (ShopDB::TableExists ('Event_stat')) {
      ShopDB::Query("update Event set
                        event_free  = (select count(*) from `Seat`
                                       where seat_event_id = event_id
                                       and seat_status IN ('res','free','trash')
                                       and seat_user_id IS NULL
                                       and seat_order_id IS NULL ),
                        event_total = (select count(*) from `Seat`
                                       where seat_event_id = event_id)");
      ShopDB::Query("update Category set
                       category_free = (select count(*) from `Seat`
                                        where seat_category_id = category_id
                                        and seat_status in ('res', 'free','trash')
                                        and seat_user_id IS NULL
                                        and seat_order_id IS NULL)");

      array_push($Install->Warnings,"We moved the statistics information back to there main table this gives us a more stable system.");

      self::renameTables(array('Category_stat','Event_stat'));
    }
  }

  /**
   * install_execute::moveOrderNotes()
   *
   * Move order notes to new order_notes table
   *
   * @return void
   */
  static function moveOrderNotes($Install){
    $rec = is(ShopDB::query_one_row("SELECT COUNT(*) FROM `Order` WHERE order_note IS NOT NULL"),array(0));
    if(isset($rec[0]) && $rec[0] >0 ){
      $query = "INSERT INTO `order_note`
                (`onote_order_id`,`onote_subject`,`onote_note`)
                SELECT order_id,'Old Note',order_note
                FROM `Order`
                WHERE order_note IS NOT NULL";
      if(ShopDB::query($query)){
        $query = "UPDATE `Order`
                  SET order_note = NULL
                  WHERE order_note IS NULL";
        ShopDB::query($query);
      }
      array_push($Install->Warnings,"Moved the Order Note to there new location!");
    }
  }
}
?>