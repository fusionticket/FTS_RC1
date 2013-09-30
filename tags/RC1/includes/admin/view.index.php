<?PHP
/**
 %%%copyright%%%
 *
 * FusionTicket - ticket reservation system
 *  Copyright (C) 2007-2013 FusionTicket Solution Limited . All rights reserved.
 *
 * Original Design:
 *	phpMyTicket - ticket reservation system
 * 	Copyright (C) 2004-2005 Anna Putrino, Stanislav Chachkov. All rights reserved.
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
  require_once("admin/class.adminview.php");
  require_once("admin/view.config.php");
  require_once("admin/view.versionutil.php");

class IndexView extends AdminView {
  var $tabitems= array(
    0 => "index_admin_tab|control",
    2 => "shopconfig_tab|admin",
    4 => "plugins_tab|admin");

  function __construct( $width=0){
    GLOBAL $_SHOP;
    plugin::call('doAddACLResource','index_default_specs', 'admin' );

    if($_SHOP->software_updater_enabled){
      $this->tabitems[3] = "version_updater|admin";
    }
    parent::__construct($width);
  }

  function draw() {
    global $_SHOP;
    $tab = $this->drawtabs();
    if (! $tab) { return; }
    switch ($tab-1){
      case 0:
        $licention = file_get_contents (ROOT."licence.txt");
        $this->form_head("Fusion&nbsp;Ticket&nbsp;".con('current_version').'&nbsp;'.CURRENT_VERSION.$this->hasNewVersion(),$this->width,1);
        echo "<tr><td class='admin_value'>" ;
        echo "<p><pre>",htmlspecialchars($licention),'</pre></p>';
        echo "</td></tr>";
        echo "</table>\n<br>";
 //       if ($_SHOP->admin->isAllowed('index_default_specs')) {
        $this->form_head( con('system_summary'),$this->width,2);
          self::$labelwidth = '25%';
        $this->print_field('InfoWebVersion',  $_SERVER['SERVER_SOFTWARE']);
        $this->print_field('InfoPhpVersion',  phpversion ());
        $this->print_field('InfoMysqlVersion',ShopDB::GetServerInfo ());
        $this->print_field('InfoMysqlDB'     ,$_SHOP->db_name);
        $this->print_field('InfoAdminCount',  $this->Admins_Count ());
        $this->print_field('InfoUserCount',   $this->Users_Count ());
        $this->print_field('InfoEventCount',  $this->Events_Count ());
        echo "</table>\n";
//        }
        break;

      case 1:
      case 2:
        $viewer = new ConfigView($this->width);
        $viewer->draw();
        $this->addJQuery($viewer->getJQuery());
        break;
      case 3:
        $viewer = new VersionUtilView($this->width);
        $viewer->draw();
        $this->addJQuery($viewer->getJQuery());
        break;
      case 4:
        //    $this->barcodeForm($_POST);
        require_once("admin/view.plugins.php");
        $viewer = new pluginsView($this->width);
        $viewer->draw();
        $this->addJQuery($viewer->getJQuery());
        break;

      default:
        plugin::call('is'.get_class($this).'_Draw', $tab-1, $this);
    }
  }

  function Users_Count () {
    $sql = "SELECT count(user_status) as count,user_status, IF(active IS NOT NULL,'yes','no') as active
  	       	FROM User left join auth on auth.user_id=User.user_id
            group by user_status, IF(active IS NOT NULL,'yes','no')";
    if(!$res=ShopDB::query($sql)){
      return FALSE;
    }

    while($data=shopDB::fetch_row($res)){
      $part[$data[1]][$data[2]]=$data[0];
    }

    return vsprintf(con('index_user_count'),array($part[1]['no'],$part[3]['no'],$part[2]['yes'],$part[2]['no'],$part[2]['yes']+$part[2]['no']));
  }

  function Groups_Count (){
    return 'not impented yet';
  }

  function Venues_Count () {
    $sql = "SELECT count(*)
  	       	FROM Ort";
    if(!$result=ShopDB::query_one_row($sql)){
      return FALSE;
    }
    return vsprintf(con('index_ort_count'),$result);

  }

  function Events_Count (){
    $part = array('pub'=>0, 'unpub'=>0, 'nosal'=>0,'trash'=>0,'total'=>0);
    $sql = "SELECT count(event_status) as count, event_status
  	       	FROM Event
            group by event_status";
    if(!$res=ShopDB::query($sql)){
      return FALSE;
    }

    while($data=shopDB::fetch_row($res)){
      $part['total'] += $data[0];
      $part[$data[1]]=$data[0];
    }

    return vsprintf(con('index_events_count'),$part);
  }

  function admins_Count (){
    $part = array('admin'=>0, 'organizer'=>0, 'pos'=>0, 'control'=>0,'total'=>0);
    $sql = "SELECT count(admin_status) as count, admin_status
  	       	FROM Admin
  	       	group by admin_status
  	       	order by admin_status";
    if(!$res=ShopDB::query($sql)){
      return FALSE;
    }

    while($data=shopDB::fetch_row($res)){ //print_r($daTA);
      $part['total'] += $data[0];
      $part[$data[1]]=$data[0];
    }
    return vsprintf(con('index_admins_count'),$part);
  }

}

?>