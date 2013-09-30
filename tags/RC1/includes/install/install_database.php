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

class install_database {
  static function precheck($Install) {
    return true; //(!$_SESSION['ConfigExist']) or ($_SESSION['DB_Error']) or ($_SESSION['radio'] == 'NORMAL');
  }

  static function postcheck($Install) {
    Install_Request(array('db_name','db_uname','db_pass', 'db_host', 'db_prefix'),'SHOP');
    if(empty($_SESSION['SHOP']['db_host']))
      {array_push($Install->Errors,'No database hostname specified.');}
    if(empty($_SESSION['SHOP']['db_name']))
      {array_push($Install->Errors,'No database name specified.');}
    if ($Install->Errors) return true;
    $link = OpenDatabase();
    if(($error=@mysqli_connect_errno($link)) or ($error= @mysqli_errno($link))){
      if($error==1049 and is($_REQUEST['db_create_now'],false)){
        $link->query('CREATE DATABASE ' . $_SESSION['SHOP']['db_name']);
        if(!(@mysqli_connect_error($link) or @mysqli_error($link))){
          $link->select_db($_SESSION['SHOP']['db_name']);
          $_SESSION['radio']    = 'NORMAL';
  //        $_SESSION['db_demos'] = $_REQUEST['db_demos'];
        }
      }
      if(($error=@mysqli_connect_errno($link)) or ($error= @mysqli_errno($link))){
        array_push($Install->Errors,'A database connection could not be established using the settings you have provided.<br>'.
                                   "Error code: ({$error}) ". @mysqli_connect_error($link) . @mysqli_error($link));
        $_SESSION['DB_Error'] = ($error==1049);
        return true;
      }
    }

    $row = shopdb::query_one_row("show variables like 'have_inno%'");
    if ($row && ($row['Value'] !== 'YES')) {
      array_push($Install->Errors,'Fusion Ticket uses the MySQL InnoDB engine. This is not installed on your server.');
      return true;
    }

    if ($result = $link->Query("SHOW TABLE STATUS LIKE 'Admin'")) {
      //do nothing here;
    } elseif ($result = $link->Query("SHOW TABLE STATUS LIKE 'admin'")) {
      //do nothing here;
    }
    if (!$result) {
      $_SESSION['DatabaseExist'] = false;
    } elseif ( !$row = $result->fetch_assoc()) {
      $_SESSION['DatabaseExist'] = false;
    } elseif ( $row['Rows']==0  ) {
      $_SESSION['DatabaseExist'] = false;
    } else {
      $_SESSION['DatabaseExist'] = true;
      $_SESSION['radio'] = 'UPGRADE';
    }

    $OrgExist = false;

    if (ShopDB::tableExists('configuration')) {
      $select = 'select * from configuration';
      $tmp = ShopDB::query("select config_field, config_value from configuration");
      while ($row = ShopDB::fetch_row($tmp)) {
        $val = unserialize($row[1]);
        $_SESSION['CONFIG'][$row[0]] = $val;
        if (strpos($row[0],'organizer_')===0) {
          $OrgExist = true;
        }
      }
    }
   // var_dump($OrgExist, $_SESSION['CONFIG']);die();

    if (ShopDB::tableExists('Organizer')) {
      $select = 'select * from Organizer LIMIT 0,1';
      if ($row = ShopDB::query_one_row($select)) {
        $OrgExist = true;
        foreach ($row as $key => $value) {
           $_SESSION['CONFIG'][$key] = stripslashes($value);

        }
      }
    }
    if (!$OrgExist) {
      $_SESSION['CONFIG']['organizer_name'] ='Demo Owner';
      $_SESSION['CONFIG']['organizer_address'] = '5678 Demo St';
      $_SESSION['CONFIG']['organizer_plz'] = '11001';
      $_SESSION['CONFIG']['organizer_ort'] = 'Demo Town';
      $_SESSION['CONFIG']['organizer_state'] = 'DT';
      $_SESSION['CONFIG']['organizer_country'] = 'US';
      $_SESSION['CONFIG']['organizer_email'] = '';
      $_SESSION['CONFIG']['organizer_fax'] = '(555) 555-1215';
      $_SESSION['CONFIG']['organizer_phone'] = '(555) 555-1214';
      $_SESSION['CONFIG']['organizer_place'] = '';
      $_SESSION['CONFIG']['organizer_currency'] = 'USD';
      $_SESSION['CONFIG']['organizer_logo'] = '';
    }
    return true;
  }

  static function display($Install) {
    Install_Form_Open ($Install->return_pg,'','Database Connection Settings');
    if (empty($_SESSION['SHOP']['db_host'])) $_SESSION['SHOP']['db_host'] = 'localhost';
    if (empty($_SESSION['SHOP']['db_name'])){
      $tmp = strtolower( 'ft_'.INSTALL_VERSION);
      $tmp = str_replace(" ", "", $tmp);
      $tmp = str_replace(".", "_", $tmp);
      $tmp = str_replace("-", "_", $tmp);
      $_SESSION['SHOP']['db_name'] = $tmp;
    }

    echo "<table cellpadding=\"1\" cellspacing=\"2\" width=\"100%\">
            <tr>
              <td colspan=\"2\">
                 Enter the required database connection information below to allow the installation process to create tables in the specified database.<br>
              </td>
            </tr>
            <tr> <td height='6px'></td> </tr>
            <tr>
              <td width='30%'>Hostname</td>
              <td><input type=\"text\" name=\"db_host\" value=\"".is($_SESSION['SHOP']['db_host'],'')."\" /></td>
            </tr>
            <tr>
              <td>Database</td>
              <td><input type=\"text\" name=\"db_name\" value=\"".is($_SESSION['SHOP']['db_name'],'')."\" /></td>
            </tr>
            <tr>
              <td>Username</td>
              <td><input type=\"text\" name=\"db_uname\" value=\"".is($_SESSION['SHOP']['db_uname'],'')."\" /></td>
            </tr>
            <tr>
              <td>Password</td>
              <td><input type=\"text\" name=\"db_pass\" value=\"".is($_SESSION['SHOP']['db_pass'],'')."\" /></td>
            </tr>\n";
    if (!empty($_SESSION['DB_Error']) ) {
      echo "
            <tr>
              <td><br>Create Database:</td>
              <td><br><input type=checkbox name='db_create_now' value='1'></td>
            </tr>";
    }
    echo "</table>\n";

    Install_Form_Buttons ();
    Install_Form_Close ();

  }
}
?>