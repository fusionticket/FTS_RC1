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

class patronView extends AdminView{
  function __construct ($width, $id){
    $this->width   = $width;
    $this->user_id = $id;
  }

  function print_user ($user){
    $user["user_country_name"] = $this->getCountry($user["user_country"]);
    $status = $this->print_status($user["user_status"]);
    $user["user_status"] = $status;
    echo "<table class='admin_form' width='{$this->width}' cellspacing='1' cellpadding='4'>\n";
    echo "<tr><td class='admin_list_title' colspan='2'>{$user["user_lastname"]} {$user["user_firstname"]}</td></tr>";

    $this->print_field('user_lastname', $user);
    $this->print_field('user_firstname', $user);
    $this->print_field('user_address', $user);
    $this->print_field('user_address1', $user);
    $this->print_field('user_zip', $user);
    $this->print_field('user_city', $user);
    $this->print_field('user_state', $user);
    $this->print_field('user_country', $user);
    // $this->print_field('user_country_name',$user );
    $this->print_field('user_phone', $user);
    $this->print_field('user_fax', $user);
    $this->print_field('user_email', $user);
    $this->print_field('user_status', $user);

    echo "</table>\n";
  }

  function draw () {
    global $_SHOP;
    $user = User::loadArr($this->user_id);
    $this->print_user($user);
    $query = "select * from `Order` where order_user_id ='{$this->user_id}'";
    if (!$res = ShopDB::query($query)){
      user_error(shopDB::error());
      return;
    }
    echo "<br><table class='admin_list' cellspacing='0' cellpadding='5' width='{$this->width}'>
   <tr><td class='admin_list_title' colspan='7'>" . con('orders') . "</td></tr>";
    while ($order = shopDB::fetch_assoc($res)){
      echo "<tr><td class='order_item'>" . $order["order_id"] . "</td>
               <td class='order_item' colspan='6'>" . con('tickets_nr') . " " . $order["order_tickets_nr"] .
      " - " . valuta($order["order_total_price"]) . " - " . con('date') . "  " . $order["order_date"] .
      " - " . $order["order_shipment_mode"] . " - " .
      $this->print_order_status($order["order_status"]) . "
	       <a href='{$_SERVER['PHP_SELF']}?tab=2&action=order_detail&order_id=" . $order["order_id"] . "'>
	       <img src=\"".$_SHOP->images_url."view.png\" border='0'/>
	       </a></td><tr>";
      $query = "select * from Seat LEFT JOIN Discount ON seat_discount_id=discount_id,Event,Category where seat_order_id='" . $order["order_id"] . "'
               AND seat_event_id=event_id AND seat_category_id= category_id ".$_SHOP->admin->getEventRestriction();
      if (!$res1 = ShopDB::query($query)){
        user_error(shopDB::error());
        return;
      } while ($ticket = shopDB::fetch_assoc($res1)){
        if ((!$ticket["category_numbering"]) or $ticket["category_numbering"] == 'both'){
          $place = $ticket["seat_row_nr"] . "-" . $ticket["seat_nr"];
        }else if ($ticket["category_numbering"] == 'rows'){
          $place = con('place_row') . " " . $ticket["seat_row_nr"];
        }else if ($ticket["category_numbering"] == 'seat'){
          $place = con('place_seat') . " " . $ticket["seat_nr"];
        }else{
          $place = '---';
        }

        echo "<tr><td class='ticket_item_1'>&nbsp;</td>
	       <td class='ticket_item'>" . $ticket["seat_id"] . "</td>
	       <td class='ticket_item'>" . $ticket["event_name"] . "</td>
	       <td class='ticket_item'>" . $ticket["category_name"] . "</td>
	       <td class='ticket_item'>$place</td>
	       <td class='ticket_item'>" . $ticket["discount_name"] . "</td>

	       <td class='ticket_item' align='right'>" . valuta($ticket["seat_price"]) . "</td><tr>";
      }
    }
    echo "</table>";
  }

  function print_status ($user_status) {
    if ($user_status == '1'){
      return con('sale_point');
    }else if ($user_status == '2'){
      return con('member');
    }else if ($user_status == '3'){
      return con('guest');
    }
  }

  function print_order_status ($order_status) {
    if ($order_status == 'ord'){
      return "<font color='blue'>" . con('order_status_ordered') . "</font>";
    }else if ($order_status == 'send'){
      return "<font color='red'>" . con('order_status_sended') . "</font>";
    }else if ($order_status == 'paid'){
      return "<font color='green'>" . con('order_status_paid') . "</font>";
    }else if ($order_status == 'cancel'){
      return "<font color='#787878'>" . con('order_status_cancelled') . "</font>";
    }
  }
}

?>