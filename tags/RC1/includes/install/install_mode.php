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

class install_mode {
  static function precheck($Install) {
    return true;// $_SESSION['DatabaseExist'] ;
  }

  static function postcheck($Install) {
    $_SESSION['radio']    = $_REQUEST['radio'];
    $_SESSION['db_demos'] = isset($_REQUEST['db_demos'])?$_REQUEST['db_demos']:0;

    return true;
  }


  static function display($Install) {

    Install_Form_Open ($Install->return_pg,'return(Validate_Inst_Upgrade());', 'Installation mode.');
    if (!$mode = $_SESSION['radio']){
      $mode = 'NORMAL';
    }
    $chk = array('NORMAL'=>'','UPGRADE'=>'');
    $chk[$mode] = 'checked="checked"';
    $disabled = (!$_SESSION['DatabaseExist'])?'disabled':'';
    echo "<table cellpadding=\"1\" cellspacing=\"2\" width=\"100%\">
            <tr>
              <td colspan=\"2\">
                The installation process can optionally leave your existing database intact if you are performing an upgrade.
                If you wish to leave your existing database unchanged select the \"UPGRADE\" option below;
                otherwise, select the \"FULL INSTALL\" option to continue with a normal installation.<br />
              </td>
            </tr>
            <tr> <td height='6px'></td> </tr>
            <tr>
              <td colspan=\"2\">
                <input type=\"radio\" name=\"radio\" value=\"NORMAL\" id='doinstall'  {$chk['NORMAL']}/><label for='doinstall'>  Full (re)installation of your database</label>
              </td>\n
            </tr>
            <tr>
              <td colspan='2'>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Install demonstration data:  <input  type=checkbox name='db_demos' value='1'>
              </td>
            </tr>
            <tr>
              <td colspan=\"2\"><br>
                <input type=\"radio\" $disabled name=\"radio\" id='doupgrade'  value=\"UPGRADE\" {$chk['UPGRADE']} /> <label for='doupgrade'> Upgrade existing selected database.</label>
              </td>\n
            </tr>
          </table>\n";
    Install_Form_Buttons ();
    Install_Form_Close ();

  }
}
?>