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

class DiscountView extends AdminView {
  function table ($discount_event_id, $live = false) {
    global $_SHOP;
    if (!is_null($discount_event_id)) {
      $query = "SELECT event_name, ort_name, event_status
                FROM Event left join Ort on ort_id=event_ort_id
                WHERE event_id="._esc((int)$discount_event_id);
      if (!$names = ShopDB::query_one_row($query)) {
          $err = shopDB::error();
          if ($err) user_error($err);
          return;
      }

      $query = "SELECT *
                FROM Discount
                WHERE discount_event_id="._esc((int)$discount_event_id);
      if (!$res = ShopDB::query($query)) {
          user_error(shopDB::error());
          return;
      }
    } else {
      $query = "SELECT *
                FROM Discount
                WHERE discount_event_id is null";
      if (!$res = ShopDB::query($query)) {
          user_error(shopDB::error());
          return;
      }
    }
    $alt = 0;
    if (!is_null($discount_event_id)) {
    	$this->grid_head('discount_title',array(
    	                                       'status_header'=>array( 'width'=>10, 'sort'=>false ),
    	                                       'discount_name_header'=>array(),
    	                                       'discount_promo_header'=>array('width'=>150),
    	                                       'discount_value_header'=>array('width'=>120 ),
    	                                       'discount_used_header'=>array( 'width'=>80,'type' =>'null'  ),
    	                                       'discount_useweb_header'=>array( 'width'=>25,'type' =>'null','sort'=>false ),
    	                                       'discount_usepos_header'=>array( 'width'=>25,'type' =>'null','sort'=>false ),
    	                                       'actions_header'=>array('width'=>50 )),
                                        	array(
                                        	    'addaction'=> "{$_SERVER['PHP_SELF']}?action=add_disc&discount_event_id={$discount_event_id}",
                                        	    'width'=>$this->width,
                                        	    'height'=>'75px',
                                        	    'addfilter'=>false));//     ,$this->width,'75px',false);
    } else {
      $this->grid_head('discount_title',array(
                                             'discount_name_header'=>array(),
                                             'discount_promo_header'=>array('width'=>150),
                                             'discount_value_header'=>array('width'=>120 ),
                                             'discount_used_header'=>array( 'width'=>80,'type' =>'null'  ),
//                                             'discount_useweb_header'=>array( 'width'=>25,'type' =>'null','sort'=>false ),
//                                             'discount_usepos_header'=>array( 'width'=>25,'type' =>'null','sort'=>false ),
                                             'actions_header'=>array('width'=>50 )),
                                        array(
                                           'addaction'=>"{$_SERVER['PHP_SELF']}?action=add_disc",
                                           'width'=>$this->width,
                                           'height'=>'300px'));//      "{$_SERVER['PHP_SELF']}?action=add_disc",$this->width,'300px',true);
      }

    while ($row = shopDB::fetch_assoc($res)) {
        echo "<tr class='admin_list_row_$alt'>";
     //  echo "<td class='admin_list_item'>{$row['discount_id']}</td>\n";
        if (!is_null($discount_event_id)) {
          echo "<td class='admin_list_item' width=10'>&nbsp;</td>\n";
          echo "<td class='admin_list_item'>{$row['discount_name']}</td>\n";
        } else {
          echo "<td class='admin_list_item'>{$row['discount_name']}</td>\n";
        }
        echo "<td class='admin_list_item'>{$row['discount_promo']}</td>\n";
        if ($row['discount_type'] == 'percent') {
            $type = "{$row['discount_value']}%";
        } else if ($row['discount_type'] == 'fixe') {
            $type = valuta($row['discount_value']);
        }
        echo "<td class='admin_list_item' align='right'>$type</td>\n";
        echo "<td class='admin_list_item' align='right'>{$row['discount_used']}&nbsp;</td>\n";

        $handling_mode_pos=(strpos($row['discount_active'],'pos')!==false) || $row['discount_active'] =='yes';
        $handling_mode_web=(strpos($row['discount_active'],'www')!==false) || $row['discount_active'] =='yes';
        $pmp = ($row['discount_event_id'])?"&discount_event_id={$row['discount_event_id']}":'';
        if (!is_null($discount_event_id)) {
          echo "<td class='admin_list_item' align='center'>";

          if ($handling_mode_web ) {
            echo $this->show_button("javascript:if(confirm(\"".con('discount_www_deactivate')."\")){ location.href=\"{$_SERVER['PHP_SELF']}?action=active_disc&discount_id={$row['discount_id']}&mode=www&do=remove{$pmp}\"; }",('discount_www_activated'),2,
                 array('image'=>'shop_web_pub.png'));
          } else {
            echo $this->show_button("javascript:if(confirm(\"".con('discount_www_activate')."\")){ location.href=\"{$_SERVER['PHP_SELF']}?action=active_disc&discount_id={$row['discount_id']}&mode=www&do=add{$pmp}\"; }",('discount_www_deactivated'),2,
                 array('image'=>'shop_web_unpub.png'));
          }
          echo "</td>";
          echo "<td class='admin_list_item'  align='center'>";
          if ($handling_mode_pos ) {
            echo $this->show_button("javascript:if(confirm(\"".con('discount_pos_deactivate')."\")){ location.href=\"{$_SERVER['PHP_SELF']}?action=active_disc&discount_id={$row['discount_id']}&mode=pos&do=remove{$pmp}\"; }",('discount_pos_activated'),2,
                 array('image'=>'shop_pos_pub.png'));
          } else {
            echo $this->show_button("javascript:if(confirm(\"".con('discount_pos_activate')."\")){ location.href=\"{$_SERVER['PHP_SELF']}?action=active_disc&discount_id={$row['discount_id']}&mode=pos&do=add{$pmp}\"; }",('discount_pos_deactivated'),2,
                 array('image'=>'shop_pos_unpub.png'));
          }
          echo "</td>";

      }
      echo "<td class='admin_list_item' align='right' nowrap><nowrap>";

        echo $this->show_button("{$_SERVER['PHP_SELF']}?action=edit_disc&discount_id={$row['discount_id']}","edit",2);
        echo $this->show_button("javascript:if(confirm(\"".con('delete_item')."\")){location.href=\"{$_SERVER['PHP_SELF']}?action=remove_disc&discount_id={$row['discount_id']}{$pmp}\";}","remove",2,
                                array('tooltiptext'=>"Delete {$row['discount_name']}?",
                                      'disable'=>$live ));
        echo "</nowrap></td></tr>";
        $alt = ($alt + 1) % 2;
    }
    $this->grid_footer();
  }

  function form ($data, $err, $title) {
    echo "<form method='POST' action='{$_SERVER['PHP_SELF']}'>\n";
    if (!is_null($data['discount_event_id'])) {
      echo "<input type='hidden' name='discount_event_id' value='{$data['discount_event_id']}' />\n";
    }
    echo "<input type='hidden' name='action' value='save_disc' />\n";
    if ($data['discount_id']) {
      echo "<input type='hidden' name='discount_id' value='{$data['discount_id']}' />\n";
    }

    echo "<table class='admin_form' width='$this->width' cellspacing='1' cellpadding='4'>\n";
    echo "<tr><td class='admin_list_title' colspan='2' align='left'>" . $title . "</td></tr>";

//    $this->print_field_o('discount_id', $data);
    $this->print_field_o('discount_activate', $data);

    $this->print_input('discount_name', $data, $err, 30, 50);
    if (!is_null($data['discount_event_id'])) {
      $this->print_Category('discount_category_id', $data, $data['discount_event_id'], $err);
    }
  	$this->print_input('discount_promo', $data, is_null($data['discount_event_id']), 15, 15);

    $this->print_select ("discount_type", $data, $err, array("fixe", "percent"));

    $this->print_input('discount_value', $data, $err, 6, 5);

    $this->print_area('discount_cond', $data, $err, 6);

    $this->print_field_o('discount_used', $data);
    if ($data['discount_used'] != 0) {
      echo "<tr><td class='admin_name' align='left'>&nbsp;</td><td class='admin_value' >" .
        $this->show_button("{$_SERVER['PHP_SELF']}?action=clear_disc&discount_id={$data['discount_id']}&count={$data['discount_used']}", 'Clear')
      . "</td></tr>";
    }

    if ($data['event_pm_id']) {
		  $this->form_foot(2,"{$_SERVER['PHP_SELF']}?action=edit_pm&pm_id={$data['event_pm_id']}");
    } else {

      $this->form_foot(2);
    }
  }

  function draw ($showlist=false) {
    if ($_GET['action'] == 'clear_disc') {
        $disc = Discount::load($_GET['discount_id']);
        $disc->isUsed(- $disc->discount_used);
        $disc = Discount::load($_GET['discount_id']);
        $row = (array)$disc;
        $this->form($row, $disc, con('discount_update_title'));
    } elseif ($_GET['action'] == 'add_disc') {
        $disc = new Discount(true, is($_GET['discount_event_id'],false));
        $row = (array)$disc;
        $this->form($row, $disc, con('discount_add_title'));
    } elseif ($_GET['action'] == 'active_disc') {
        $row = Discount::load($_GET['discount_id']);
        var_dump($row);
        if ($row) {
          $mode = ($_GET['mode']==='www')?'www':'pos';
          $do = is($_GET['do'],'');
          if ($do==='add' and !in_array($mode,$row->discount_active)) {
            $row->discount_active[] = $mode;
            $row->save();
          } elseif ($do==='remove' and in_array($mode,$row->discount_active)) {
            $row->discount_active = array_diff($row->discount_active, array($mode));
            $row->save();
          }
        }
        if ($showlist) {
            $this->table(null, false);
        } else {
          return true;
        }
    } elseif ($_GET['action'] == 'edit_disc') {
        $row = Discount::load($_GET['discount_id']);
        $this->form((array)$row, $row, con('discount_update_title'));
    } elseif ($_POST['action'] == 'save_disc') {
      if (!$disc = Discount::load((int)$_POST['discount_id'])) {
         $disc = new Discount(true);
      }
      if ( !$disc->fillPost() || !$disc->saveEx() ) {
        $this->form( $_POST, $disc , con((isset($_POST['ort_id']))?'discount_update_title':'discount_add_title') );
      } elseif ($showlist) {
         $this->table(null, false);
      } else {
        return true;
      }

    } elseif ($_GET['action'] == 'remove_disc' and $_GET['discount_id'] > 0) {
      $row = Discount::load($_GET['discount_id']);
      $row->delete();
      if ($showlist) {
         $this->table(null, false);
      } else {
        return true;
      }
    } elseif ($showlist) {
      $this->table(null, false);
    }
  }

  function print_Category($name, $data, $event_id, $err) {
    global $_SHOP;
    $query="SELECT category_id, category_name
            FROM Category
            WHERE category_event_id = "._esc($event_id)."
            order by category_name";
    if($res=ShopDB::query($query)){
      echo "<tr><td class='admin_name'>".self::print_label($name,'',self::check_required($name, $err))."</td>
                <td class='admin_value'>
                  <select name='{$name}'>";
      echo "<option value=''>".con('no_category_selected')."</option>";
      while($event=shopDB::fetch_assoc($res)){
        $sel = ($event["category_id"] == $data[$name])? 'selected':'';
        echo "<option value='{$event["category_id"]}' $sel>{$event["category_name"]}</option>";
      }

      echo "</select>".printMsg($name)."</td></tr>";
    }
  }

}

?>