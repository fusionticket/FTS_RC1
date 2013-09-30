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

function Install_Form_Open ($target_pg, $onsubmit='', $title=''){
  global $states; //{$states[$target_pg]}
  echo "
          <div id=\"navbar\">
            <table width='100%'>
              <tr><td>&nbsp;<b>{$title}</b></td></tr>
            </table>
          </div>
         <div id=\"right\">";
  if (!is_numeric($target_pg)){
    echo "<form name='install' method=\"post\" action='$target_pg' onSubmit=\"".$onsubmit."\">\n";
  }else{
    echo "<form name='install' method=\"post\" action='".$_SERVER['PHP_SELF']."' onSubmit=\"".$onsubmit."\">\n";
    if (isset($target_pg)){
      echo "<input type='hidden' name=inst_pg value='{$target_pg}' /> \n";
    }
  }
  echo "<table border=0 cellpadding=\"0\" cellspacing=\"0\" width='100%' style=\"height: 400px\">
          <tr> <td height='6px'></td> </tr>
          <tr ><td valign='top' style='height:100%;' >\n"  ;
}

function Install_Form_Buttons (){
  echo "</td></tr><tr>\n";
  echo "<td  bgcolor=\"#f5F5f5\" valign=\"bottom\" style='border-top:1px solid #c0c0c0;padding: 5px;' align=\"right\">
          <input type=\"submit\" tabindex='1' value=\"Next\" name=\"do\" />
          &nbsp;
          <input type=\"button\" tabindex='2' value=\"Cancel\" name=\"do\" onClick=\"Confirm_Inst_Cancel()\" />\n";
}

function Install_Form_Rollback ($name='Back'){
  echo "</td></tr><tr>\n";
  echo "<td  bgcolor=\"#f5F5f5\" valign=\"bottom\" style='border-top:1px solid #c0c0c0;padding: 5px;' align=\"right\">
          <input type=\"submit\" tabindex='1' value=\"{$name}\"  name=\"do\"  />
          &nbsp;
          <input type=\"button\" tabindex='2' value=\"Cancel\" name=\"do\" onClick=\"return(Confirm_Inst_Cancel());\" />
          \n";
}

function Install_Form_Close (){
  echo "
        </td>
      </tr>
    </table>
  </form>\n";
  print '<script>$(\':input:enabled:visible:first\').focus();</script>';

}

function Install_request($arr, $Sub=''){
  foreach ($arr as $info){
    If (isset($_REQUEST[$info])){
      if ($Sub) {
        $_SESSION[$Sub][$info] = $_REQUEST[$info];
      } else {
        $_SESSION[$info] = $_REQUEST[$info];
      }
    } else {
      if ($Sub) {
        unset($_SESSION[$Sub][$info]);// = $_REQUEST[$info];
      } else {
        unset($_SESSION[$info]);// = $_REQUEST[$info];
      }

    }
  }
}

function loginmycheck ($link, $username,$auth){
  $query="SELECT admin_id FROM `Admin`
          WHERE `admin_login`="._esc($username). "
          AND  `admin_password`="._esc(Md5($auth));
  if($res=ShopDB::query_One_row($query)){
    return True;
  }	else {
    return false;
  }
}

function Opendatabase(){
  global $_SHOP;
  $DB_Hostname = $_SESSION['SHOP']['db_host'];
  $DB_Username = $_SESSION['SHOP']['db_uname'];
  $DB_Password = $_SESSION['SHOP']['db_pass'];
  $DB_Database = $_SESSION['SHOP']['db_name'];

  $pos = strpos($DB_Hostname,':');
  if ($pos != false) {
    $DB_Hostname = substr($DB_Hostname,0, $pos);
    $port = substr($DB_Hostname,$pos+1);
  } else
    $port = 3306;

  $link = @ new mysqli($DB_Hostname, $DB_Username, $DB_Password, '', $port);

  If (!(@mysqli_connect_error() or @mysqli_error($link))){
    $link->select_db($DB_Database);
    ShopDB::$link = $link;
  }
  return $link;
}
/*
* mysql < dump.sql
*/
function file_to_db($filename){

  if (!$lines = file($filename)){
    return "<div class=err>ERROR: can not read $filename</div>";
  }
  $query ='';
  foreach ($lines as $l){
    if (preg_match("/^\s*(#|--)/", $l)){
      // do no
    }else
    if (preg_match("/;\s*$/", $l)){
      $query = $query . substr($l, 0, - 1);

      if (!shopDB::query($query)){
        return "<div class=err>ERROR: cannot execute database query</div><pre>$query/\n".ShopDB::Error()." </pre>";
      }
      $query = '';
    }else{
      $query = $query . $l;
    }
  }
  return '';
}

function callback($matches){
  return $_SESSION[$matches[1]];
}

Function ShowResults($Install,$inst_mode){
  If ((count($Install->Errors)>0 || count($Install->Warnings)>0)){
    Install_Form_Open ($Install->return_pg,'', 'Errors and Warnings');
    echo "<input type='hidden' name='inst_mode' value='{$inst_mode}' />\n";
    echo "<div style='overflow: auto; height: 250px; width:100%'>";

    echo "<table cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">\n";
    if(count($Install->Errors)>0){
//      echo "<tr>\n<td colspan=\"2\"><h2><font color=\"#3366CC\">Error</font></h2></td>\n</tr>";
      echo "<tr><td>\n";
      echo "The installer encountered the following errors:<br><ul>\n";
      for($i=0;$i<count($Install->Errors);$i++){echo "<li type='square' class='err'>".$Install->Errors[$i]."</li>\n";}
      echo "</ul></td></tr>";
    }
    // Handle Warnings
    if(count($Install->Warnings)>0){
//      echo "<tr>\n<td colspan=\"2\"><h2><font color=\"#3366CC\">Warning !</font></h2></td>\n</tr><tr><td>\n";
      echo "<tr><td>\n";
      if(count($Install->Errors)>0){echo "<br>";}
      echo "The installer has issued the following warnings:<br><ul>\n";

      for($i=0;$i<count($Install->Warnings);$i++){echo "<li type='circle' class='warn'>".$Install->Warnings[$i]."</li>\n";}
      echo "</ul></td></tr>";
    }
    echo "</table></div>";

    If (count($Install->Errors)>0){
      Install_Form_Rollback (($inst_mode=='pre')?'Retry':'Back');
    } else {
      echo "<input type='hidden' name='continue' value='1' />\n";
      Install_Form_Buttons ();
    }

    $Install->Errors   = array ();
    $Install->Warnings = array ();

    Install_Form_Close ();
    return true;//count($Install->Errors)>0;
  }
  return false;
}

function RemoveDir($dir, $DeleteMe) {
  if(!$dh = @opendir($dir)) return;
  while (false !== ($obj = readdir($dh))) {
    if($obj=='.' || $obj=='..') continue;
    if (!@unlink($dir.'/'.$obj)) RemoveDir($dir.'/'.$obj, true);
  }
  closedir($dh);
  if ($DeleteMe){
    @rmdir($dir);
  }
}

function setmail() {
global $_SHOP;
  $_SHOP->mail_smtp_host = is($_SESSION['CONFIG']['mail_smtp_host'],null);
  $_SHOP->mail_smtp_port = is($_SESSION['CONFIG']['mail_smtp_port'],null);
  $_SHOP->mail_smtp_user = is($_SESSION['CONFIG']['mail_smtp_user'],null);
  $_SHOP->mail_smtp_pass = is($_SESSION['CONFIG']['mail_smtp_pass'],null);
  $_SHOP->mail_smtp_security = is($_SESSION['CONFIG']['mail_smtp_security'],null);
  $_SHOP->mail_sendmail  = is($_SESSION['CONFIG']['mail_sendmail'],null);
  require_once (ROOT."includes/classes/email.swift.sender.php");
}
?>