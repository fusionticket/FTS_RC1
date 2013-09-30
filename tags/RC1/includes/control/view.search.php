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
require_once("classes/smarty.gui.php");

//error_reporting(E_ALL);

class searchView extends AdminView{
  var $title = "tickettaker";

  function search_form (&$data){
    global $_SHOP;

    echo "<form method='POST' action='{$_SERVER['PHP_SELF']}'>
            <input type='hidden' name='action' value='details'/>\n";

	$this->form_head( con('search_title_user'),$this->width,2);
	$this->print_input('user_lastname',$data, $err,25,100);
    $this->print_input('user_firstname',$data, $err,25,100);
    $this->print_input('user_zip',$data, $err,25,100);
    $this->print_input('user_city',$data, $err,25,100);
    $this->print_input('user_phone',$data, $err,25,100);
    $this->print_input('user_email',$data, $err,25,100);
    echo "
             <tr>
               <td class='admin_name'>".con('user_status')."</td>
               <td class='admin_value'>
                  <select name='user_status'>
                    <option value='0'>------</option>
                    <option value='1'>".con('sale_point')."</option>
                    <option value='2'>".con('member')."</option>
                    <option value='3'>".con('guest')."</option>
                  </select>
               </td>
             </tr>
             <tr><td class='admin_list_title' colspan='2'>".con("search_title_place")."</td></tr>
             <tr>";

    $this->print_input('seat_row_nr',$data, $err,4,4);
    $this->print_input('seat_nr',$data, $err,4,4);

    echo "
                <tr><td class='admin_list_title' colspan='2'>".con("search_title_order")."</td></tr>";
    $this->print_input('order_id',$data, $err,11,11);
  	$this->form_foot(2,'','search');
  }


  function draw (){
    GLOBAL $_SHOP;
    if($_REQUEST['action']=='details'){
      if($query_type=$this->search_check($_REQUEST)){
         if($this->search_result($_REQUEST, $query_type)) return 1;
      }
    }elseif($_REQUEST['action']=='detail'){
    	if($query_type=$this->detail_check($_REQUEST)){
    		if ($this->search_result($_REQUEST, $query_type)) return 1;
    	}
    }
     $this->search_form($_POST);
  }

  function search_check (&$data){
  	$count = 0;
  	$query = array();
  	if(!empty($data["user_firstname"])){
  		$query[]= "user_firstname LIKE "._esc($data['user_firstname'].'%');
  		$count++;
  	}
  	if(!empty($data["user_lastname"])){
  		$query[]= "user_lastname LIKE "._esc($data['user_lastname'].'%');
  		$count++;
  	}
  	if(!empty($data["user_zip"])){
  		$query[]= "user_zip LIKE "._esc($data['user_zip'].'%');
  		$count++;
  	}
  	if(!empty($data["user_city"])){
  		$query[]= "user_city LIKE "._esc($data['user_city'].'%');
  		$count++;
  	}
  	if(!empty($data["user_phone"])){
  		$query[]= "user_phone LIKE "._esc($data['user_phone'].'%');
  		$count++;
  	}
  	if(!empty($data["user_email"])){
  		$query[]= "user_email LIKE "._esc($data['user_email'].'%');
  		$count++;
  	}
  	if(!empty($data["user_status"])){
  		$query[]= "user_status="._esc($data['user_status']);
  		//  $count++;
  	}
  	if(!empty($data["seat_row_nr"])){
  		$query[]="seat_row_nr="._esc($data["seat_row_nr"]);
  	}
  	if(!empty($data["seat_nr"])){
  		$query[]="seat_nr="._esc($data["seat_nr"]);
  		$count++;
  	}
  	if(!empty($data["seat_row_nr"])|| !empty($data["seat_nr"])){
  		$count++;
  	}
  	if(!empty($data["order_id"])){
  		$query[]="order_id="._esc($data["order_id"]);
  		$count++;
  	}

  	if ($count <2) {
  		return addWarning('search_choice_two_fields');
  	}

    return $query;
  }

  function detail_check (&$data){
		$count = 0;
		$query = array();
		if(!empty($data["order_id"])){
			$query[]="order_id="._esc($data["order_id"]);
			$count++;
		}

		if ($count <1) {
			return addWarning('search_choice_two_fields');
		}

		return $query;
	}

	function search_result(&$data, $query_type){
  	global $_SHOP;
		$eventlinks = $_SHOP->admin->getEventRestriction();

    $query="SELECT DISTINCT order_id, order_date, order_status, User.*, order_payment_status
            FROM `Order` left join `User` on order_user_id = user_id
                         left join `Seat` on seat_order_id = order_id
                         left join `Event` on seat_event_id = event_id
            WHERE event_status != 'unpub'
              AND event_rep LIKE '%sub%'
     	      {$eventlinks}
     	      AND ". implode("\n AND ",$query_type);

    if(!$res=ShopDB::query($query)){
      user_error(shopDB::error());
      return;
    }
  	$rows = array();
  	while($row=shopDB::fetch_assoc($res)){
      $rows[] = $row;
  	}
  	if (count($rows)==0) {
  		return AddWarning('search_result_none');
  	} elseif (count($rows)==1) {
  		$this->search_details($rows[0]);
  		return true;
  	}
    echo "
          <table class='admin_list' width='100%' cellspacing='2' cellpadding='2'>
            <tr>
              <td colspan='6' class='admin_list_title'>".con('search_result')."</td></tr>
			<tr  class='admin_list_header'>
				<th class='admin_list_item' width='50'>".con('search_order_id_header')."</th>
				<th class='admin_list_item' width='150'>".con('search_order_date_header')."</th>
				<th class='admin_list_item' width='100'>".con('search_order_status_header')."</th>
				<th class='admin_list_item' width='150'>".con('search_user_header')."</th>
				<th class='admin_list_item' width='150'>".con('search_address_header')."</th>
				<th class='admin_list_item' width='50'>".con('search_zip_header')."</th>
				<th class='admin_list_item' width='150'>".con('search_city_header')."</th>
				<th class='admin_list_item' width='50'>".con('search_country_header')."</th>
			</tr>\n" ;
	$alt=0;
  	foreach($rows as $row) {
      echo "
          <tr class='admin_list_row_$alt'>
            <td class='admin_list_item'><a class='link' href='?action=detail&order_id=".$row["order_id"]."'>".$row["order_id"]."</a></td>
            <td class='admin_list_item'><a class='link' href='?action=detail&order_id=".$row["order_id"]."'>".$row["order_date"]."</a></td>
            <td class='admin_list_item'>".$this->print_order_status($row["order_status"])."</td>
  	        <td class='admin_list_item'>
              <a class='link' href='?action=detail&order_id=".$row["order_id"]."'>".$row["user_firstname"]." ".$row["user_lastname"]."</a>
            </td>
			<td class='admin_list_item'>".$row["user_address"]." ".$row["user_address1"]."</td>

			<td class='admin_list_item'>".$row["user_zip"]."</td>
			<td class='admin_list_item'>".$row["user_city"]."</td>
			<td class='admin_list_item'>".$row["user_country"]."</td>
          </tr>" ;
      $alt=($alt+1)%2;
    }
    echo "</table>";
	return true;
  }

	function search_details($order){
		global $_SHOP;

		$order["order_status"]=$this->print_order_status($order);

		echo "<table class='admin_form' width='100%' cellspacing='0' cellpadding='2'>\n";
		echo "<tr><td class='admin_list_title' colspan='2'>".con('order_nr')."  ".$order["order_id"]."</td></tr>";

//		$this->print_field('order_tickets_nr',$order);
//		$this->print_field('order_total_price',$order);
		$this->print_field('order_date',$order);

//		$order['order_shipment_status']=con($order['order_shipment_status']);
		$order['order_payment_status']=con($order['order_payment_status']);

//		$this->print_field('order_shipment_status',$order);
		$this->print_field('order_payment_status',$order);
//		$this->print_field('order_fee',$order);
		$this->print_field_o('order_status',$order);
		echo "</table><br>\n";

		$order["user_country_name"]=gui_smarty::getCountry($order["user_country"]);
		$order["user_status"]=$this->print_status($order["user_status"]);

		echo "<table class='admin_form' width='100%' cellspacing='0' cellpadding='2' border='0'>\n";
		echo "<tr><td class='admin_list_title' colspan='2'>".con('user_id')." ".$order["user_id"]."</td></tr>";

		$this->print_field_o('user_lastname',$order);
		$this->print_field_o('user_firstname',$order);
		$this->print_field_o('user_address',$order);
		$this->print_field_o('user_address1',$order);
		$this->print_field_o('user_zip',$order);
		$this->print_field_o('user_city',$order);
		$this->print_field_o('user_country_name',$order);
//		$this->print_field_o('user_phone',$order);
//		$this->print_field_o('user_fax',$order);
//		$this->print_field_o('user_email',$order);
		$this->print_field_o('user_status',$order);

		echo "</table><br>\n";
		if(!$seats=Order::loadTickets($order["order_id"])){
			return true;
		}
		$order = Order::loadExt($order["order_id"]);

		echo "<table class='admin_form' width='100%' cellspacing='2' cellpadding='2'>\n";
		echo "<tr><td class='admin_list_title' colspan='8'>".con('tickets')."</td></tr>";
		echo "<tr class='admin_list_header'>
			   <th class='admin_list_item' width='70' align='left'>".con("search_seat_id_header")."</th>
			   <th class='admin_list_item' width='350' align='left'>".con("search_event_name_header")."</th>
			   <th class='admin_list_item' width='200' align='left'>".con("search_pmz_name_header")."</th>
			   <th class='admin_list_item' width='150' align='left'>".con("search_category_name_header")."</th>
			   <th class='admin_list_item' width='100' align='left'>".con('search_place_header')."</th>
			   <th class='admin_list_item' align='right' colspan=2>".con("search_seat_status_header")."</th>

			<tr>\n";
		$alt=0;
		foreach($seats as $ticket){
			if((!$ticket["category_numbering"]) or $ticket["category_numbering"]=='both'){
				$place=$ticket["seat_row_nr"]."-".$ticket["seat_nr"];
			}else if($ticket["category_numbering"]=='rows'){
				$place=con('place_row')." ".$ticket["seat_row_nr"];
			}else if($ticket["category_numbering"]=='seat'){
				$place=con('place_seat')." ".$ticket["seat_nr"];
			}else{
				$place='---';
			}


			echo "<tr class='admin_list_row_$alt'>
				   <td class='admin_list_item'>".$ticket["seat_id"]."</td>
				   <td class='admin_list_item'>".$ticket["event_name"]." (".formatAdminDate($ticket["event_date"], false).' '.
				formatTime($ticket["event_time"]).")</td>
				   <td class='admin_list_item'>".$ticket["category_name"]."</td>
				   <td class='admin_list_item'>".$ticket["pmz_name"]."</td>

				   <td class='admin_list_item'>$place</td>

				   <td class='admin_list_item' align='right'>".
				$this->print_place_status($ticket["seat_status"])."</td>";
			    echo "<td width='17'>";
                if ($ticket["event_id"]===$_SESSION['tickettaker_event']) {
                  echo "<form method='post' action='index.php' onsubmit='javascript:return confirm(\"".con('checkin_ticket')."\")'>";

                	$bar = seat::encodeBarcode($order, $ticket);
                  $this->print_hidden('codebar',$bar);

                  echo $this->show_button('submit',"checkin",2,
                  	array('tooltiptext'=>con("checkin_ticket"),
                  	      'image'=>'ok.png',
                  	      'disable' => $ticket["seat_status"] != 'com'
                          ));
                  echo "</form>";

                } else echo "&nbsp;";

			echo "</td><tr>\n";
			$alt=($alt+1)%2;

		}
		echo "</table><br>\n";

	}

  function print_status ($user_status){
    if($user_status=='1'){
      return con('sale_point');
    }else if ($user_status=='2'){
      return con('member');
    }else if($user_status=='3'){
      return con('guest');
    }
  }

  function print_order_status ($order_status){
    if($order_status=='ord'){
      return "<font color='blue'>".con('order_status_ordered')."</font>";
    }else if ($order_status=='send'){
      return "<font color='red'>".con('order_status_sended')."</font>";
    }else if($order_status=='paid'){
      return "<font color='green'>".con('order_status_paid')."</font>";
    }else if($order_status=='cancel'){
      return "<font color='#787878'>".con('order_status_cancelled')."</font>";
    }
  }
	function print_place_status ($place_status){
		switch($place_status){
			case 'free': return "<font color='green'>".con('free')."</font>";
			case 'res':  return "<font color='orange'>".con('reserved')."</font>";
			case 'com':  return "<font color='red'>".con('com')."</font>";
			case 'check':return "<font color='blue'>".con('checked')."</font>";
		}
	}

}
?>