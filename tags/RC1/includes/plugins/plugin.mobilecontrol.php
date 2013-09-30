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


/**
 *
 *
 * @version $Id$
 * @copyright 2010
 */
class plugin_mobilecontrol extends baseplugin {

	public $plugin_info		  = 'Mobile TicketTaker';
	/**
	 * description - A full description of your plugin.
	 */
	public $plugin_description	= 'This plugin allows you to use your phone to check tickets';
	/**
	 * version - Your plugin's version string. Required value.
	 */
	public $plugin_myversion		= '0.0.1';
	/**
	 * requires - An array of key/value pairs of basename/version plugin dependencies.
	 * Prefixing a version with '<' will allow your plugin to specify a maximum version (non-inclusive) for a dependency.
	 */
	public $plugin_requires	= null;
	/**
	 * author - Your name, or an array of names.
	 */
	public $plugin_author		= 'The FusionTicket team';
	/**
	 * contact - An email address where you can be contacted.
	 */
	public $plugin_email		= 'info@fusionticket.com';
	/**
	 * url - A web address for your plugin.
	 */
	public $plugin_url			= 'http://www.fusionticket.org';

  public $plugin_actions  = array ('install','uninstall','named');

	function LoadAdmin(){
	  global $_SHOP;

	}

  function actionwebLogin($controller) {
  	global $_SHOP;
  	require_once "Auth/Auth.php";
  	require_once "classes/model.admin.php";

  	if (!empty($controller->request['user']) && !empty($controller->request['password'])) {
  		$auth_container = new CustomAuthContainer('control');
  		$auth_container->_auth_obj = new __logskip();
  		//
  	  $result = $auth_container->fetchData($controller->request['user'], $controller->request['password']);
  //		var_dump($auth_container, $result, $_SHOP->Messages);
  //		die ('here');
  		if ($result) {
  			$_SHOP->admin = $auth_container->_auth_obj->admin;
  			unset($_SHOP->admin->admin_password);
  			$_SESSION['_SHOP_AUTH_USER_NAME']=$controller->request['user'];
  			$_SESSION['_SHOP_AUTH_ADMIN_ID']=$_SHOP->admin->admin_id;
  			$eventlinks = $_SHOP->admin->getEventRestriction();
  			//	$eventlinks = ($eventlinks)?$eventlinks:'-1';

  			$query="select SQL_CALC_FOUND_ROWS * from Event left join Ort on event_ort_id=ort_id
             where event_status = 'pub'
             and   event_date = "._esc(date('Y-m-d'))."
             AND   event_rep LIKE '%sub%'
    	       {$eventlinks}
             order by event_date, event_time ";
  			if(!$events=ShopDB::query($query)){
  				$controller->jsonreason = shopDB::error();
  				return false;
  			}

  			$part_count=ShopDB::num_rows($events);

  			if($part_count==0){
  				$controller->jsonreason = con("no_event_sets");
   			}else{
  				$controller->json['events'] = array();
  				while($event=shopDB::fetch_assoc($events)){
  					$etime=formatTime($event["event_time"]);
            $controller->json['events'][] = $event['event_id'].' ['.$etime.'] '.$event['event_name']; //." - {$event['ort_name']} - {$event['ort_city']} - {$event['ort_country']}";
  				}
  				return true;
  			}

  		} else {
  			$controller->jsonreason = con("admin_not_found");
  		}
  	}
  	session_unset();
  	$_SESSION = array();
  	session_destroy();
  	return false;
  }

	function actionwebLogout($controller) {
		session_unset();
		$_SESSION = array();
		session_destroy();
		$controller->json  = array();
		return true;
	}

	function checkLogin() {
		global $_SHOP;
		require_once "classes/model.admin.php";
		if (isset($_SESSION['_SHOP_AUTH_ADMIN_ID'])) {
		  if($res = Admins::load($_SESSION['_SHOP_AUTH_ADMIN_ID'])) {
			  $_SHOP->admin = $res;
			  unset($res->admin_password);
	      $_SHOP->event_ids = $_SHOP->admin->getEventLinks();
		  	return true;
		  }
		}
 	  header('HTTP/1.1 403 Forbidden');
    echo con('you_do_not_have_access');
	  die();
	}

	function actionwebscan($controller) {
		$this->checkLogin();
		$controller->json  = array();
		list($seat_id, $ticket_code)= seat::decodeBarcode($_POST['barcode']);
//		$controller->barcode  = array('x'=>$seat_id, 'y'=>$ticket_code);
		$query="select seat_event_id, discount_name, seat_status, seat_nr, seat_row_nr,
                     seat_checked_by, seat_checked_date,
                     category_numbering, category_name, order_payment_status, order_status,order_id,
                     admin_login, admin_realname
              from `Seat` LEFT JOIN `Category` on seat_category_id=category_id
                          LEFT JOIN `Order` on seat_order_id= order_id
                          LEFT JOIN `Discount` on discount_id= seat_discount_id
                          LEFT JOIN `Admin` on seat_checked_by= admin_id
              where seat_id="._esc($seat_id)."
  	          AND   seat_code="._esc($ticket_code);

		if(!$ticket=ShopDB::query_one_row($query)){
			return $this->showerror('ticket_not_found',$ticket,$controller);
		} elseif($ticket['seat_event_id'] !==$_POST['event']){
			return $this->showerror('not_valid_event',$ticket,$controller);
		} elseif($ticket['seat_status']=='check'){
			return $this->showerror('ticket_already_checked',$ticket,$controller);
		} elseif($ticket['seat_status']=='free'){
			return $this->showerror('place_not_commanded',$ticket,$controller);
		} elseif($ticket['seat_status']=='res'){
			return $this->showerror('place_only_reserved',$ticket,$controller);
		} elseif(!in_array($ticket['order_status'],array('ord','pros'))){
			return $this->showerror('order_is_not_valid',$ticket,$controller);
		} elseif(!in_array($ticket['order_payment_status'],array('paid'))){
			return $this->showerror('order_is_not_paid',$ticket,$controller);
		}
		if($ticket['category_numbering']=='both'){
			$place_nr=con('place_nr')." ".$ticket['seat_row_nr']."-".$ticket['seat_nr'];
		}else if($ticket['category_numbering']=='rows'){
			$place_nr=con('rang_nr')." ".$ticket['seat_row_nr'];
		}else if($ticket['category_numbering']=='seat'){
			$place_nr=con('place_nr')." ".$ticket['seat_nr'];
		}else if($ticket['category_numbering']=='none'){
			$place_nr=con('place_without_nr');
		}

		$controller->json['order_id'] = $ticket['order_id'];
   	$controller->json['category'] = "{$ticket['category_name']} {$ticket['pmz_name']}";
   	$controller->json['seat'] = $place_nr;
   	$controller->json['discount'] = $ticket['discount_name'];

		OrderStatus::statusChange($ticket['order_id'],'TicketTaker',con('seat_checked').$seat_id,'tickettaker::checkticket');
		$query="UPDATE Seat set seat_status='check', seat_checked_date=CURRENT_TIMESTAMP, seat_checked_by="._esc($_SHOP->admin->admin_id)." where seat_id="._esc($seat_id);

		if(!ShopDB::query($query)){
			$controller->jsonreason .= con('place_status_not_updated');
			return false;
		}

		return true;
	}

	function showerror($message, $ticket,$controller){
		GLOBAL $_SHOP;
		//   var_dump($ticket);
		if (empty($ticket)) {
			OrderStatus::statusChange($ticket['order_id'],'TicketTaker',$message,'tickettaker::error');
		}
		$controller->jsonreason = con($message)."\n";
		if ($ticket['seat_checked_by']) {
			$controller->jsonreason .= con('checked_by').$ticket['admin_login']."\n";
			$controller->jsonreason .= con('checked_at').$ticket['seat_checked_date']."\n";
		}
    return false;
	}

}

class __logskip{
	function log($value){
//		addwarning($value);
	}

}
?>