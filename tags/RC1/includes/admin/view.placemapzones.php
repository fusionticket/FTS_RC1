<?php
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

class PlaceMapZoneView extends AdminView {
  function table ($pm_id, $live = false) {
      global $_SHOP;

      if ($pm = PlaceMap::load($pm_id)) {
          $mine = true;
      }

      $query = "select * from PlaceMapZone where pmz_pm_id="._esc($pm_id);
      if (!$res = ShopDB::query($query)) {
          return;
      }
      // zones
      $alt = 0;
      $addif = (!$live and $mine)?"{$_SERVER['PHP_SELF']}?action=add_pmz&pm_id={$pm_id}":null;
      $this->grid_head('pm_zones',array( 'status_header'=>array( 'width'=>10, 'sort'=>false ),
                                            'zones_name_header'=>array(  ),
                                            'zone_short_header'=>array( 'width'=>100  ),
                                            'actions_header'=>array('width'=>65 )),
                                  array(
                                     'addaction'=>$addif,
                                     'width'=>$this->width,
                                     'height'=>'75px',
                                     'addfilter'=>false));//    $addif,$this->width,'75px',false);

      while ($zone = shopDB::fetch_object($res)) {
          $zone_ident = $zone->pmz_id;

          echo "<tr class='admin_list_row_$alt'>";
          echo "<td class='admin_list_item' bgcolor='{$zone->pmz_color}'>&nbsp;</td>\n";
          echo "<td class='admin_list_item'>{$zone->pmz_name}</td>\n";
          echo "<td class='admin_list_item'>{$zone->pmz_short_name}</td>\n";

          echo "<td class='admin_list_item' width=65 align=right>\n";

          if ($mine) {
            echo $this->show_button("{$_SERVER['PHP_SELF']}?action=edit_pmz&pm_id=$pm_id&pmz_id=$zone_ident","edit",2);
          } else {
            echo $this->show_button("{$_SERVER['PHP_SELF']}?action=view_pmz&pm_id=$pm_id&pmz_id=$zone_ident","view",2);
          }
          echo $this->show_button("javascript:if(confirm(\"".con('delete_item')."\")){location.href=\"{$_SERVER['PHP_SELF']}?action=remove_pmz&pm_id=$pm_id&pmz_id=$zone->pmz_id\";}","remove",2,
                                  array('tooltiptext'=>"Delete {$zone->pmz_name}?",
                                        'disable'    => ($live and $mine)));

          echo'</td></tr>';
          $alt = ($alt + 1) % 2;
      }

    $this->grid_footer();
  }

  function form ($data, $err) {
    $data['pmz_pm_id'] =(isset($data['pmz_pm_id']))?$data['pmz_pm_id']:$_REQUEST['pm_id'];
    echo "<form action='{$_SERVER['PHP_SELF']}' method='post' autocomplete='off'>\n";
    echo "<input type=hidden name=action value=save_pmz>\n";
    echo "<input type=hidden name=pmz_pm_id value={$data['pmz_pm_id']}>\n";
    echo "<input type=hidden name=pm_id value={$data['pmz_pm_id']}>\n";

    if (!empty($data['pmz_id'])) {
      echo "<input type=hidden name=pmz_id value={$data['pmz_id']}>";
    }
    $this->form_head(con('pm_zone'));
    //$this->print_field_o('pmz_id', $data);
    $this->print_field_o('pmz_ident', $data);
    //$this->print_field_o('pmz_pm_id', $data);
    $this->print_input('pmz_name', $data, $err, 30, 50);
    $this->print_input('pmz_short_name', $data, $err, 4, 10);
    $this->print_color('pmz_color', $data, $err);
    $this->form_foot(2,"{$_SERVER['PHP_SELF']}?action=edit_pm&pm_id={$data['pmz_pm_id']}");
  }

  function view($pmz){
    $data = (array)$pmz;

    $this->form_head(con('pm_zone'));
    $this->print_field('pmz_id', $data);
    $this->print_field('pmz_ident', $data);
    $this->print_field('pmz_pm_id', $data);
    $this->print_field('pmz_name', $data);
    $this->print_field('pmz_short_name', $data);
    $this->show_color('pmz_color', $data);

    echo "</table>";
  	echo "<br>".$this->show_button("{$_SERVER['PHP_SELF']}?action=edit_pm&pm_id={$data['pmz_pm_id']}",'admin_list',3);
  }

  function draw (){
    global $_SHOP;

    if ($_GET['action'] == 'add_pmz' and $_GET['pm_id'] > 0) {
      $pmz = new PlaceMapZone(true);
      $pmz->pmz_pm_id = $_GET['pm_id'];

      $this->form((array)$pmz, $pmz);
    } elseif ($_GET['action'] == 'edit_pmz' and $_GET['pmz_id'] > 0) {
      $pmz = PlaceMapZone::load($_GET['pmz_id']);
      $this->form((array)$pmz, $pmz);
    } else if ($_POST['action'] == 'save_pmz' and $_POST['pmz_pm_id'] > 0) {
      if (!$pmc = PlaceMapZone::load((int)is($_POST['pmz_id'],-1))) {
         $pmc = new PlaceMapZone(true);
      }
      if (!$pmc->fillPost() || !$pmc->saveEx()) {
        $this->form($_POST, $pmz);
      } else {
        return true;
      }
    }elseif ($_GET['action'] == 'view_pmz' and $_GET['pmz_id'] > 0) {
      $pmz = PlaceMapZone::load($_GET['pmz_id']);
      $this->pmz_view($pmz);

    }elseif ($_GET['action'] == 'remove_pmz' and $_GET['pmz_id'] > 0) {
    	if ( $pmz = PlaceMapZone::load($_GET['pmz_id'])) {
        $pmz->delete();
    	}
      return true;
    }
  }

  function show_color ($name, &$data)
  {
    if ($data[$name]) {
        $st = "style=background-color:{$data[$name]};";
    } else {
        $field = con('no_color');
    }
    echo "<tr><td class='admin_name' width='40%'>" . con($name) . "</td>
  <td class='admin_value'>
  <table width='40' $st><tr><td width='40'>$field&nbsp;</td></tr></table>
  </td></tr>\n";
  }
}

?>