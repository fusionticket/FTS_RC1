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

class install_orphans {
  static function precheck($Install) {
    return false;
  }

  static function postcheck($Install) {
    if(isset($_GET['fix'])){
      Orphans::dofix($_GET['fix']);
      return false;
    }

    return true;
  }


  static function display($Install) {
    global $_SHOP, $orphancheck;
    require_once(INC."classes/redundantdatachecker.php");
    OpenDatabase();
    $data = Orphans::getlist($keys,false,"&inst_pg={$Install->return_pg}");

    $space = (count($keys)*60 < 780 -200)?1:0;
    Install_Form_Open ($Install->return_pg,'', 'Database Orphan check');

    echo "<table cellpadding=\"1\" cellspacing=\"2\" width='100%'>
            <tr><td>
              The list below is a view of the orphans in your database. Look on our website for instructions how to fix this or contact us on the forum or IRC.<br>
              To be on the safe side, we suggest you create a new database and import the common information into the new database. This can be done by the installer.
            </td></tr>
            <tr> <td height='6px'></td> </tr>
          </table>";

    echo "<div style='overflow: auto; height: 250px; width:100%;'>";
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
        print "<td align='center'>{$row[$key]}&nbsp;</td>\n";
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
}
?>