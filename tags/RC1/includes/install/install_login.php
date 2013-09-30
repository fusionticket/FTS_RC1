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

class install_login {
  static function precheck($Install) {
//    var_dump($_SESSION);
    If (is($_SESSION['ConfigExist'],false)){
      $tmp_dir = $GLOBALS['_SHOP']->tmp_dir;
      include (ROOT."includes/config/init_config.php");
      $_SESSION['SHOP']  = (array)$_SHOP;
      if (isset($_SHOP->tmp_dir) && $_SHOP->tmp_dir == $tmp_dir) {
        unset($_SESSION['SHOP']['tmp_dir']);
      }
      $_SHOP->tmp_dir = $tmp_dir;
      $GLOBALS['_SHOP'] = $_SHOP;
      $configValues = array('db_host', 'db_name', 'db_pass', 'db_uname', 'secure_id','root','root_secure');
      if (!isset($_SESSION['CONFIG']) || !is_array($_SESSION['CONFIG'])) {
        $_SESSION['CONFIG'] = array();
      }
      foreach ($_SHOP as $key => $value){
        if (!in_array($key,$configValues)) {
          $_SESSION['CONFIG'][$key] = $value;
          unset($_SESSION['SHOP'][$key]);
        }
      }
   //   var_dump($_SESSION);
      $link      = OpenDatabase();
      $result = false;
      $row = shopdb::query_one_row("show variables like 'have_inno%'");
 //     print_r($row);
      if ($row && ($row['Value'] !== 'YES')) {
        array_push($Install->Errors,'Fusion Ticket uses the MySQL InnoDB engine. This is not installed on your server.');
      } elseif ($result = $link->Query("SHOW TABLE STATUS LIKE 'Admin'")) {
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
    //  var_dump($row);
    }
    if (!isset($_SESSION['CONFIG']['mail_smtp_host'])) {
      $_SESSION['CONFIG']['mail_smtp_host'] = 'localhost';
    } else $_SESSION['usesmtp']='checked';
    if (!isset($_SESSION['CONFIG']['mail_smtp_port'])) {
      $_SESSION['CONFIG']['mail_smtp_port'] = '25';
    }
    if (!isset($_SESSION['CONFIG']['mail_sendmail']))  {
      $_SESSION['CONFIG']['mail_sendmail'] = '/usr/sbin/sendmail -bs';
    } else $_SESSION['sendmail']='checked';

    return  (is($_SESSION['DatabaseExist'],false));
  }

  static function postcheck($Install) {
    $link      = OpenDatabase();
    if(!loginmycheck ($link, $_POST['username'], $_POST['password'])){
      array_push($Install->Errors,"Username or Password not found.");
    }
    return true;
  }

  static function display($Install) {
    Install_Form_Open ($Install->return_pg,'','Login to update you system');
    echo "<table cellpadding=\"1\" cellspacing=\"2\" width=\"100%\">
            <tr>
              <td colspan=\"2\">
                 For security please login with your admin username and password. This is the one that you use to administer the system.
              </td>
            </tr>
            <tr> <td height='6px'></td> </tr>
            <tr>
              <td width='30%'>Admin login:</td>
              <td><input type=\"text\" name=\"username\" value=\"\" /></td>
            </tr>
            <tr>
              <td>Admin password:</td>
              <td><input type=\"password\" name=\"password\" value=\"\" /> (at least 6 letters)</td>
            </tr>
          </table>";
    Install_Form_Buttons ();
    Install_Form_Close ();
  }
}
?>