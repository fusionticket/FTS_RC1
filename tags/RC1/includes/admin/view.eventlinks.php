<?php
/**
 *               %%%copyright%%%
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

class EventLinksView extends AdminView {
  function table ($event_id, $live = false)  {
    global $_SHOP;

  	$event = Event::load($event_id);

		echo "  <form action='{$_SERVER['PHP_SELF']}' method=post>\n";
    echo "      <input type=hidden name='action' value=add_admin_al>\n";
    echo "      <input type=hidden name='admin_id[]' value='-1'>\n";
    echo "      <input type=hidden name='user_id[]'  value='-1'>\n";
    echo "      <input type=hidden name=event_id value=" . _esc($event_id), ">\n";

    $query = 'select Admin.*, adminlink_id
              from `Admin` left join `adminlink` on admin_id = adminlink_admin_id and adminlink_event_id = ' . _esc($event_id) . "
              where admin_status='organizer' and (admin_country = '' or admin_country ="._esc($event->ort_country)." or admin_id = adminlink_admin_id)
                order by admin_login";

    echo "<table class='admin_form' width='$this->width' cellspacing='1' cellpadding='4'>\n";
    echo "  <tr><td class='admin_list_title' align='left'>" . con('linked_event_managers') . "</td></tr></table>\n";
    echo "      <select name='admin_id[]' class='multiselect'  size='10' multiple style='width:{$this->width}px;'>\n";

    if ($res = ShopDB::query($query)) {
      while ($row = shopDB::fetch_assoc($res)) {
        $select = ($row['adminlink_id'])?'selected':'';
        echo "         <option  {$select} value='{$row['admin_id']}'> {$row['admin_login']} (Email: " . con($row['admin_email']) . ") </option>\n";
      }
    }
    echo "</select>";

    echo "<br>";

    /* ----------------------------------------------------- */

    $query = 'select Admin.*, adminlink_id
              from `Admin` left join `adminlink` on admin_id = adminlink_admin_id and adminlink_event_id = ' . _esc($event_id) . "
                where admin_status='control' and (admin_country = '' or admin_country ="._esc($event->ort_country)." or admin_id = adminlink_admin_id)
                order by admin_login";
    echo "<table class='admin_form' width='$this->width' cellspacing='1' cellpadding='4'>\n";
    echo "<tr><td class='admin_list_title' align='left'>" . con('linked_tickettakers') . "</td></tr></table>\n";

    echo "      <select name='admin_id[]' class='multiselect' size='10' multiple  style='width:{$this->width}px;'>\n";
    if ($res = ShopDB::query($query)) {
      while ($row = shopDB::fetch_assoc($res)) {
        $select = ($row['adminlink_id'])?'selected':'';
        echo "         <option {$select}  value='{$row['admin_id']}'> {$row['admin_login']} (Email: " . con($row['admin_email']) . ") </option>\n";
      }
    }
    echo "</select>";


    echo "<br>";

    /* ----------------------------------------------------- */

   $query = 'select DISTINCT user_id, user_lastname, user_city , adminlink_id, seat_pos_id
              from `User` left join `adminlink` on user_id = adminlink_pos_id and adminlink_event_id ='._esc($event_id).'
              left join Seat on seat_event_id = '._esc($event_id).' and seat_status = \'free\' and user_id = seat_pos_id
              where user_status = 1  and (user_country = \'\' or user_country ='._esc($event->ort_country).' or user_id = adminlink_pos_id)';

    $toolTipText = con("delete_link_error");
    echo "<table class='admin_form' width='$this->width' cellspacing='1' cellpadding='4'>\n";
    echo "<tr><td class='admin_list_title' align='left'>" . con('linked_posoffices') . "</td></tr></table>\n";
    echo "      <select name='user_id[]' class='multiselect' size='10' multiple  style='width:{$this->width}px;'>\n";

    if ($res = ShopDB::query($query)) {
      while ($row = shopDB::fetch_assoc($res)) {
        $select = ($row['adminlink_id'])?'selected':'';
        if ($row['seat_pos_id'] && $select) {
          $select .= ' inuse="true" title="'.$toolTipText.'"';
        }

        echo "         <option {$select} value='{$row['user_id']}'> {$row['user_lastname']}, {$row['user_city']}</option>\n";

      }
    }
    echo "</select>";

    echo "<table  class='admin_list' width='{$this->width}' cellspacing='1' cellpadding='2'>";

    $this->form_foot(2,$_SERVER['PHP_SELF']);

    echo minify('css','ui.multiselect.css','css');
    echo minify('js','ui.multiselect.js','scripts/jquery');
    $this->addJQuery(" $('.multiselect').multiselect({sortable: false});");
  }

  function draw () {
    global $_SHOP;
    // print_r($_REQUEST);
    if ($_REQUEST['action'] == 'add_admin_al' and is_array($_REQUEST['admin_id']) and is_array($_REQUEST['user_id'] )) {
    	adminlink::updateLinks($_REQUEST['event_id'],$_REQUEST['admin_id'],$_REQUEST['user_id'], true);
    }
  }
}

?>