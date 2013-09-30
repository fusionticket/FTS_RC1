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

class PluginsView extends AdminView{

  function table (){
    global $_SHOP;
    $plugins = plugin::loadAll(true);
  	$plugins = plugin::call('updatePluginList',$plugins,'plugins');
    $alt=0;
    echo "<form method='POST' action='{$_SERVER['PHP_SELF']}'>\n";
    echo "<input type='hidden' name='action' value='update'/>\n";

    $this->grid_head('plugins_title',array(
       'plugin_info_header'=>array( 'width'=>320 ),
       'plugin_myversion_numbering_header'=>array('width'=>70  ),
       'plugin_author_header'=>array(  ),
       'priority_header'=>array( 'width'=>70  ),
       'actions_header'=>array('width'=>65 )),
    array(
       'addaction'=>false,
       'width'=>$this->width,
       'height'=>'300px',
       'addfilter'=>false));//       null,$this->width,'300px',false);


    $showSave =false;
    foreach($plugins as $row){
      echo "<tr class='admin_list_row_$alt'>";
      echo "<td class='admin_list_item' width='250' ><abbr title='{$row->plugin_description}'> {$row->plugin_info}</abbr></td>";
      echo "<td class='admin_list_item' width='50' >{$row->plugin_myversion}</td>";

      echo "<td class='admin_list_item' align='left'>";
      echo "<a href='mailto:{$row->plugin_email}' alt= >{$row->plugin_author}</a></td>";
      echo "<td class='admin_list_item' width='35' align='center' nowrap><nowrap>";
      if ($row->plugin_id  && in_array('priority',$row->plugin_actions)) {
        $sel = array(1=>'',2=>'',3=>'',4=>'',5=>'');
        $sel[$row->plugin_priority] = 'selected="selected"';
        echo "<select name='priority[{$row->plugin_name}]'>
                 <option value='5' {@$sel[5]}>5</option>
                 <option value='4' {@$sel[4]}>4</option>
                 <option value='3' {@$sel[3]}>3</option>
                 <option value='2' {@$sel[2]}>2</option>
                 <option value='1' {@$sel[1]}>1</option></select>";
        $showSave = true;
      }
      echo "&nbsp;</td>";
      echo "<td class='admin_list_item' width='65' align='right'>";

      if ($row->plugin_id && !$row->plugin_enabled) {
        echo $this->show_button("#","Disabled",2,
              array('image'=>'unpublish.png', 'disable'=>true));
      }

      if (!$row->plugin_id && in_array('install',$row->plugin_actions)) {
        echo $this->show_button("{$_SERVER['PHP_SELF']}?action=install&plugin_name={$row->plugin_name}","Install",2,
             array('image'=>'unpublish.png', 'tooltiptext'=>"Install {$row->plugin_name}?"));
      }
        //echo version_compare($row->plugin_version, $row->plugin_myversion);

        if ($row->plugin_id && in_array('uninstall',$row->plugin_actions)) {
          echo $this->show_button("javascript:if(confirm(\"".con('plugin_allow_uninstall')."\")){location.href=\"{$_SERVER['PHP_SELF']}?action=uninstall&plugin_name={$row->plugin_name}\";}","Uninstall",2,array('image'=>'publish.png', 'tooltiptext'=>"Unistall {$row->plugin_name}?"));
        }

      $update = ($row->plugin_id && version_compare($row->plugin_version, $row->plugin_myversion)<0  && in_array('upgrade',$row->plugin_actions));
      echo $this->show_button("{$_SERVER['PHP_SELF']}?action=upgrade&plugin_name={$row->plugin_name}","Upgrade",2,array('disable'=> !$update ,'image'=>'upgrade.png', 'tooltiptext'=>"Upgrade {$row->plugin_name}?"));
       $update =!$row->plugin_id && (in_array('install',$row->plugin_actions) || in_array('uninstall',$row->plugin_actions) );
      echo $this->show_button("javascript:if(confirm(\"".con('delete_item')."\")){location.href=\"{$_SERVER['PHP_SELF']}?action=remove&plugin_name={$row->plugin_name}\";}","remove",2,array('disable'=> !$update ,'tooltiptext'=>"Delete {$row->plugin_name}?"));

      echo "</td>\n";
      echo "</tr>\n";
      $alt=($alt+1)%2;
    }
    $this->grid_footer();

    if ($showSave) {
      echo "<table>";
      $this->form_foot(5);
    } else {
      echo "</form>";
    }
  }

  function draw () {
    global $_SHOP;

    if ($_GET['action'] == 'install' && $_GET['plugin_name']){
      $adm = Plugin::load($_REQUEST['plugin_name']);
      if ($adm && in_array('install',$adm->plugin_actions)) $adm->install();
      redirect('');

    } elseif ($_GET['action'] == 'upgrade' && $_GET['plugin_name']){
      $adm = Plugin::load($_REQUEST['plugin_name']);
      if ($adm && in_array('upgrade',$adm->plugin_actions)) $adm->upgrade();
      redirect('');
    } elseif($_GET['action']=='uninstall' and $_GET['plugin_name']){
      $adm = Plugin::load($_REQUEST['plugin_name']);
      if ($adm && in_array('uninstall',$adm->plugin_actions)) $adm->uninstall();
      redirect('');
    }
    $this->table();
  }
}
?>