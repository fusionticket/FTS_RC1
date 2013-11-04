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
require_once('classes/model.orderstatus.php');

class Order Extends Model {

  const STS_PMT_PAID = 'paid';
  const STS_PMT_PEND = 'pending';
  const STS_PMT_CANCELLED = 'cancelled';
  const STS_PMT_NONE = 'none';

  const STS_SMT_SENT = 'send';
  const STS_SMT_NONE = 'none';

  const STATUS_NONE = 'none';
  const STATUS_ORDERED = 'ord';
  const STATUS_RESERVED = 'res';
  const STATUS_REISSUED = 'reissue';
  const STATUS_CANCELED = 'cancel';
  const STATUS_TRASH = 'trash';

  protected $_idName    = 'order_id';
  protected $_tableName = 'Order';
  protected $_columns   = array('#order_id', '*order_user_id', 'order_session_id', '*order_tickets_nr',
                                'order_partial_price','*order_total_price', '*~order_date','order_timestamp', '*order_shipment_status',
                                '*order_payment_status', 'order_payment_id', 'order_handling_id','order_order_set',
                                '*order_status', 'order_fee', '*order_place', '#order_owner_id','#order_admin_id',
                                '~order_date_expire', 'order_responce', 'order_responce_date',
								'order_custom1', 'order_custom2', 'order_custom3', 'order_custom4',
                                'order_note', 'order_discount_price', 'order_discount_promo', 'order_discount_id', 'order_lang'); //, 'order_lock', 'order_lock_time', '#order_lock_admin_id'
  public $places=array();
  public $tickets = array();
  public $no_fee = false;
  public $no_cost = false;
  public $handling;

	function create ($handling_id, $origin, $no_cost=0, $user_id =0 , $no_fee = 0, $FreeTicketCode=false){
    global $_SHOP;

    if(!$user_id){
      $user_id=$_SESSION['_SHOP_USER'];//['user_id'];
    }

    $cart=$_SESSION['_SMART_cart'];
    //
    if(!$cart || !$cart->can_checkout()){
      return addWarning('cart_empty_or_invalid');
    }

    if(!$handling_id or !$user_id or !$cart or !$cart->can_checkout()){
      return addWarning('reservate_failed');
    }

		if (!empty($_SHOP->freeTicketCode) and
			  !empty($FreeTicketCode) and
	     	$_SHOP->freeTicketCode == $FreeTicketCode) {
      $no_cost = true;
    }
    //compile order (order and tickets) from the shopping cart in order_func.php

    $order = new Order;
    $order->order_user_id=$user_id;
    $order->order_session_id=session_id();
    $order->order_handling_id=$handling_id;
    $order->order_place=$origin;
    $order->order_owner_id = ($origin == 'pos')? $_SESSION['_SHOP_AUTH_USER_DATA']['user_id']: null;
    $order->order_admin_id = ($origin == 'pos')? $_SESSION['_SHOP_AUTH_USER_DATA']['admin_id']: null;
    $order->no_fee =$no_fee;
    $order->no_cost=$no_cost;
    $order->handling = &Handling::load($handling_id);
		$order->user = User::load($user_id);

    //begin the transaction
    if (ShopDB::begin('Make order')){


			$cart->iterate('_collect', $order);

      // apply Global discount over the total price.
			if (!empty($FreeTicketCode) and !$order->no_cost) {
				if ((!$discount =Discount::loadPromoCode($FreeTicketCode, $order, $origin))) {
          addWarning('FreeTicketCode_notfound');
          ShopDB::rollback('FreeTicketCode_notfound');
          return;
        } else {
          $order->discount = $discount;
        }
      }

      //put the order into database
      if(!$order_id=$order->save()){
        addWarning('create_order_failed');
        ShopDB::rollback('create_order_failed');
        $cart->iterate('_reset', $order);
        return;
      }

      //commit the transaction
      return (ShopDB::commit('Make order'))? $order: false;
    } else {
      return  addWarning('cant_start_transaction');
    }

  }

  function load ($order_id, $handling=false, $tickets=false, $extend=false, $locked=false){
    global $_SHOP;

      $query="select *
              from `Order` ".
              (($extend)?"left join `User` on order_user_id=user_id":"")."
              WHERE order_id = "._esc($order_id). self::lock($locked);
 //   );
      if($data=ShopDB::query_one_row($query)){
        $order=new Order;

        $order->_fill($data);

        if($order && $handling){
          if ($order->order_handling_id) {
            $order->handling = Handling::load($order->order_handling_id);
          }
        }
			$order->no_cost = (strpos($order->order_order_set, 'nocost') !== false);
			$order->no_fee  = (strpos($order->order_order_set, 'nofee') !== false);

        if($order && $tickets){
          $order->places = Seat::loadAllOrder($order_id);
        } else {
          $order->places = array();
        }
        if($order){
        return $order;
      }
    }
    // the next log is included to find when or why sometimes it is not possible set the send state.
    ShopDB::dblogging("Cant load Order '{$order_id}', check of it exist.");
  }

  function loadExt ($order_id, $locked=self::LOCK_UPDATE){
    return self::load($order_id, true, true, true, $locked);
  }

  public function loadFromPaymentId($payment_id, $handling_id, $complete=false){
    global $_SHOP;

      $query="select *
              from `Order`
              WHERE order_payment_id = "._esc($payment_id)."
              AND order_handling_id = "._esc($handling_id);
       if($data=ShopDB::query_one_row($query)){
          $order=new Order;
          $order->_fill($data);

          if($order and $complete){
            if ($order->order_handling_id) {
                $order->handling= Handling::load($order->order_handling_id);
                $order->handling= &$order->handling;
            }
      //$order->places = Seat::loadall($order_id);
           }
          return $order;
      }
  }

  public function loadTickets($order_id = 0){
    if(!empty($this->order_id)){
      return Order::_loadTickets($this->order_id);
    }elseif($order_id>0){
      return Order::_loadTickets($order_id);
    }else{
      addWarning("no_orderid_given");//"No Order ID for ticket loading");
    }
  }

  private function _loadTickets($order_id){

    $sql = "SELECT * FROM Seat LEFT JOIN Discount ON seat_discount_id=discount_id
            LEFT JOIN Event ON seat_event_id=event_id
            LEFT JOIN Category ON seat_category_id= category_id
            LEFT JOIN PlaceMapZone ON Seat.seat_zone_id=pmz_id
            WHERE seat_order_id="._esc($order_id);
    $result = ShopDB::query($sql);
    if(!$result){
      addWarning("No Tickets for that event");
    }
    $seats = array();
    while($seat = ShopDB::fetch_assoc($result)){
      $seats[] = $seat;
    }
    return $seats;
  }

  function add_seat ($event_id,$category_id,$place_id,$price,$discount=null){
    array_push($this->places, Seat::ticket($event_id, $category_id, $place_id, $this->order_user_id, $this->order_session_id, $price, $discount));
  }

  function size (){
    return count($this->places);
  }

  function amount (){
    $res=0;
    foreach($this->places as $seat){
      $res+=$seat->seat_price;
    }
    return $res;
  }

  /**
   * Save order function, will take parmaters from the class varibles constructor.
   *
   * @since 1.0
   * @updated 1.0 beta6.2
   */
  function save () {
    global $_SHOP;
  	$isGapes = plugin::call('isGrapesInstalled');

    if($this->order_id){
      return addWarning('order_already_created'); //already saved
    }
    if (!isset($this->order_lang)) {
      $this->order_lang = $_SHOP->lang;
    }
  	$this->order_total_price = $this->amount();
    $this->order_discount_price = 0.0;


  	$this->calcOrderAmount();


    //$this->order_date_expire = null;

    //var_dump($this->handling);

    if($this->handling->handling_id=='1'){
      $this->order_status="res";
      $this->order_date_expire = "TIMESTAMPADD(MINUTE,".$_SHOP->shopconfig_restime.",CURRENT_TIMESTAMP()) ";
    }else{
      $this->order_status="ord";
      if ($this->handling->handling_expires_min>=1) {
        $this->order_date_expire = "TIMESTAMPADD(MINUTE,".$this->handling->handling_expires_min.",CURRENT_TIMESTAMP()) ";
      }
    }

    //Set Default fields
    $this->order_tickets_nr = $this->size();
    $this->order_shipment_status = "none";
    $this->order_payment_status = "none";

    //This is legacy... all new orders will use the timestamp which is set on save.
    //$this->order_date = date('Y-m-d H:i:s');
    $this->order_date = "CURRENT_TIMESTAMP() ";

    if (!plugin::call('checkOrderStartCreate',$this)) {return false;}

    if(!ShopDB::begin('Create Order')){
      return FALSE;
    } elseif(parent::save()){
     /* Create intial Order status log */
      OrderStatus::statusChange($this->order_id,$this->order_status,NULL,'Order::save',"Create New order",$this);

      if (isset($this->discount)) {
        $this->discount->isUsed(count($this->places));
        OrderStatus::statusChange($this->order_id, false, NULL,'Order::save',"Global discount used: ".$this->discount->discount_name);
      }
      $tickets = array();
      foreach(array_keys($this->places) as $i){
        $ticket =& $this->places[$i];
        $ticket->order_id($this->order_id);
      	if ($isGapes) {
        $tickets[] = $ticket->id.'| '.$ticket->event_name. ', '.$ticket->category_name . '['.$ticket->seat_row_nr. ', '.$ticket->seat_nr. ']'  ;
      	} else {
      		$tickets[] = $ticket->id.'| ';//.$ticket->event_name. ', '.$ticket->category_name . '['.$ticket->seat_row_nr. ', '.$ticket->seat_nr. ']'  ;
      	}
        // Tickets are saved here if handled==1 tickets are reserved instead of ordered.
        if(!$ticket->save($this->handling->handling_id=='1', $this->discount)){
          return self::_abort('Errors_commiting_ticket_save');
        }
        @ $event_stat[$ticket->seat_event_id]++;
        @ $category_stat[$ticket->seat_category_id]++;
      }

      if (count($tickets)>0) {
        OrderStatus::statusChange($this->order_id, false, NULL, 'Order::save', "Seats:\n".implode("\n",$tickets ));
      }

      foreach($event_stat as $event_id=>$count){
        if(!Event::dec_stat($event_id, $count)){
          return self::_abort('Errors_commiting_order_stat');
        }
      }

      foreach($category_stat as $cat_id=>$count){
        if(!PlaceMapCategory::dec_stat($cat_id, $count)){
          return self::_abort('Errors_commiting_cat_stat');
        }
      }

      $no_tickets=$this->size();
      if($this->handling->handling_id==1){
        $set = "SET user_order_total=user_order_total+1,
                    user_current_tickets=user_current_tickets+{$no_tickets},
                    user_total_tickets=user_total_tickets+{$no_tickets} ";
      }else{
        $set = "SET user_order_total=user_order_total+1,
                    user_total_tickets=user_total_tickets+{$no_tickets} ";

      }
      $query="UPDATE `User`
                $set
              WHERE user_id="._esc($this->order_user_id);
      if(!$res=ShopDB::query($query)){
        return self::_abort('user_failed_not found');
      }

      $tmpOrdForDate = self::load($this->order_id);
      $this->order_date = $tmpOrdForDate->order_date;

      if($this->handling->handling_id=='1'){
         $ok = $this->set_status('res',TRUE);
      }else{
         $ok = $this->set_status('ord',TRUE);
      }

			$ok = plugin::call('updateOrderFinishedCreate',$ok, $this);

      if($ok and !ShopDB::commit('Order saved')){
        return false;// self::_abort('Errors_commiting_order4');
      }
      return $this->order_id;

    }else{
      return false; //self::_abort('Errors_commiting_order5');
    }
  }

  function calcOrderAmount(){

  	if (!plugin::call('checkOrderBeforeCalc', $this)) {return false;}

  	$amount = $this->order_total_price;

  	if (isset($this->discount) and !$this->no_cost) {
  		$this->order_discount_promo = $this->discount->discount_promo;
  		$this->order_discount_id    = $this->discount->discount_id;
  		$this->order_discount_price = $this->discount->value ($amount);
  	}
  	If ($this->order_discount_price > $amount) {
  		$this->order_discount_price = $amount;
  	}
  	$amount = $amount - $this->order_discount_price;
  	$order_set =array();
  	if(!$this->no_fee){
  		$fee= $this->handling->calculate_fee($amount, $this->size());
  	}else{
  		$fee=0;
  		$order_set[] ='nofee';
  	}

  	if($this->no_cost) {
  		$this->order_discount_price = $amount+$fee;
  		$order_set[] ='nocost';
  		$total=0;
  	}else {
  		$total=$amount+$fee;
  	}

  	if (!plugin::call('checkOrderAfterCalc', $this)) {return false;}

  	$this->order_order_set = implode(',',$order_set);
  	$this->order_discount_price = number_format($this->order_discount_price, 2, '.', '');
  	$this->order_partial_price=number_format($amount, 2, '.', '');
  	$this->order_total_price=number_format($total, 2, '.', '');
  	$this->order_fee=number_format($fee, 2, '.', '');
  }

  function save_order_note($order_id,$note){
    if(ShopDB::begin('save order_note')){
      $sql = "UPDATE `Order` SET order_note="._esc($note)."
              WHERE order_status NOT IN ('trash','cancel')
              AND order_id="._esc($order_id);
      if(!$res=ShopDB::query($sql)){
        return self::_abort("No such Order ID");
      }
      return ShopDB::commit('order_note saved');
    }
  }


  function order_description() {
    return plugin::call('updateOrderDescription', con('orderDescription'), $this);
  }

  function Check_payment($order_id){
    $order = self::load($order_id, true);
    //var_dump($order);
    if ($order && $order->handling) {
      return $order->handling->on_check($order);
    } else {
      return true;
    }
  }

  public function delete ($order_id, $reason = null){
    global $_SHOP;

    if(ShopDB::begin('delete orders:'.$order_id)){

      $query="SELECT *
              FROM `Seat`
              WHERE seat_order_id="._esc($order_id)." FOR UPDATE";
      if(!$res=ShopDB::query($query)){
        return self::_abort('Cant_lock_seat');
      }

      $query="SELECT *
              FROM `Order`
              WHERE order_id="._esc($order_id)." FOR UPDATE";
      if(!$order=ShopDB::query_one_row($query)){
        return self::_abort('cannot_find_order');
      }
      // Added v1.3.4 Checks to see if the order has allready been canceled.
      if($order['order_status']=='cancel'){
        return self::_abort('order_allready_cancelled');
      }

      //Added v1.3.4 - If deleteing a reserved ticket
      if($order['order_handling_id']==1){
        $query="UPDATE `User` SET
                  user_current_tickets=user_current_tickets-".$order['order_tickets_nr']."
                WHERE user_id=".$order['order_user_id'];
        if(!ShopDB::query($query)){
          return self::_abort('no_such_user');
        }
      }

			$seat_count='0'; // Lxsparks
      while($row=shopDB::fetch_object($res)){
				$seat_count ++; // Lxsparks
        $user_id=$row->seat_user_id;
        $places[]=array(
          'seat_id'=>$row->seat_id,
          'category_id'=>$row->seat_category_id,
          'event_id'=>$row->seat_event_id,
          'pmp_id'=>$row->seat_pmp_id);
      }

      if(count($places)!=0){
        if(!Seat::cancel($places,$user_id)){
          return false;
        }
      }

      $query="UPDATE `Order` set
                order_status='cancel',
                order_responce="._esc($reason).",
                order_responce_date= NOW()
              where order_id="._esc($order_id);

      if(!$res=ShopDB::query($query)){
        return self::_abort('cant_set_order_to_cancel');
      }

      OrderStatus::statusChange($order_id,'cancel',null,'Order::delete',$reason);

      if($order_handling=Handling::load($order['order_handling_id']) and
        !$order_handling->on_order_delete($order_id)){
         return self::_abort('eph_not_allowed_to_cancel');
      }

			// Lxsparks Update Order Totals and Ticket Totals for User
      // Niels the user counters are global, and should not be reset
      // without care of manual deletion of the seats/orders.
      // but should be untouched when the events are removed.
//			$query = "UPDATE `user` SET
//								`user_order_total` = `user_order_total` - 1
//								WHERE user_id=".$order['order_user_id'];
//			if(!$res=ShopDB::query($query)){
//        return self::_abort('cant_update_user_order_count');
//      }

      return ShopDB::commit('order_deleted');
    }
    return addWarning('cannot_begin_transaction');

  }
  /* static functions of common use */

  function delete_ticket ($order_id, $seat_id, $dummy=0, $user_id=0){
    global $_SHOP;

    if(ShopDB::begin('order_delete_ticket')){
      $query="SELECT *
              FROM `Seat`
              WHERE seat_id="._esc($seat_id)."
              AND seat_order_id="._esc($order_id)." FOR UPDATE";
      if(!$seat=ShopDB::query_one_row($query)){
        return self::_abort('cannot_find_seat');
      }
      $order = self::loadExt($order_id);
      if(!$order){
        return self::_abort('cannot_find_order');
      }

      // Added v1.3.4 Checks to see if the order has allready been canceled.
      if($order->order_status=='cancel'){
        return self::_abort("order_allready_cancelled");
      }

      //if the order has only one ticket, the whole order will be deleted/canceled instead of just the ticket!
      if($order->order_tickets_nr==1){
        ShopDB::rollback('order_delete_ticket');
        if (!Order::delete($order_id, 'deleted_all_tickets')){
          return self::_abort('cant_deleted_all_tickets');
        }
      } else {

        // If deleteing a reserved ticket
        if($order->order_handling_id==1){
          $query="UPDATE `User` SET
                    user_current_tickets=user_current_tickets-1
                  WHERE user_id=".$order->order_user_id;
          if(!ShopDB::query($query)){
            return self::_abort('no_such_user');
          }
        }

        $place=array('seat_id'=>$seat['seat_id'],
                     'event_id'=>$seat['seat_event_id'],
                     'category_id'=>$seat['seat_category_id'],
                     'pmp_id'=>$seat['seat_pmp_id']);

        if(!Seat::cancel(array($place),$seat['seat_user_id'])){
          return false;
        }
        unset($order->places[$seat['seat_id']]);
        OrderStatus::statusChange($order_id,false,null,'Order::delete_ticket',print_r($place,true));

        $order->calcOrderAmount();

        $query="update `Order` set
                  order_tickets_nr=(order_tickets_nr-1),
                  order_partial_price ="._esc($order->order_partial_price).",
                  order_total_price="._esc($order->order_total_price).",
                  order_discount_price = "._esc($order->order_discount_price).",
                  order_fee="._esc($order->order_fee)."
                where order_id="._esc($order_id)." LIMIT 1";

        if(!ShopDB::query($query) or shopDB::affected_rows() !=1){
          return self::_abort('cannot_delete_ticket_3');
        }
      }

			// Lxsparks Update Current Ticket & Total Ticket counts
      // Niels the user counters are global, and should not be reset
      // without care of manual deletion of the seats/orders.
      // but should be untouched when the events are removed.
/*
			if(false && $order['order_tickets_nr'] >0){
				$query = "UPDATE `user` SET
								`user_current_tickets` = `user_current_tickets` -1,
								`user_total_tickets` = `user_total_tickets` -1
								WHERE user_id=".$order['order_user_id'];
				if(!$res=ShopDB::query($query)){ echo ">".$seat_count;
					return self::_abort('cant_update_User_seat_count');
				}
			}
*/
      return ShopDB::commit('order_delete_ticket');
    }
    addWarning('cannot_delete_ticket');
    return false;
  }

  function reserve_to_order($order_id,$handling_id,$no_fee=false,$no_cost=false,$place='www'){

    if(ShopDB::begin('reserve_to_order:'.$order_id )){
    	$order = self::loadExt($order_id);
      if(!$order || $order->order_status!=='res' || $order->order_handling_id != 1){
        return self::_abort('order_not_found');
      }

    	if(!($order->handling=Handling::load($handling_id))){
    		return self::_abort('order_upgrade_handling_not_found');
    	}
			$order->order_handling_id = $handling_id;

    // If deleteing a reserved ticket
        $query="UPDATE `User` SET
                user_current_tickets=user_current_tickets-"._esc($order->order_tickets_nr)."
              WHERE user_id="._esc($order->order_user_id);
        if(!ShopDB::query($query)){
          return self::_abort('no_such_user');
        }

      //Update Status to let the admin know the order has been remitted.
      OrderStatus::statusChange($order->order_id,'ord',null,'Order::reserve_to_order','Order Completed Reissue');
      //recalculates cost, same as when placing a new order


      //Runs through each seat and gives it a new seat_code and the new order_id.
      foreach ($order->places as $seat) {
        $code=Seat::generate_code(8);
        $query="UPDATE `Seat` set
                  seat_code='{$code}',
                  seat_status='com'
                WHERE seat_id='{$seat->seat_id}' ";
        if(!ShopDB::query($query)){
          return self::_abort('order_cannot_reorder_update_seats');
        }
      }

    	$order->calcOrderAmount();

      //Change old order, change its status and give the it the id of the new order.
      $query="UPDATE  `Order` SET
                order_status='ord',
                order_date =NOW(),
                order_order_set= "._esc($order->order_order_set).",
                order_handling_id= "._esc($order->order_handling_id).",
                order_partial_price ="._esc($order->order_partial_price).",
                order_total_price="._esc($order->order_total_price).",
                order_discount_price = "._esc($order->order_discount_price).",
                order_fee="._esc($order->order_fee).",
                order_place="._esc($place)."
              WHERE order_id="._esc($order_id);
      if(!$res=ShopDB::query($query)){
        return self::_abort('order_cannot_reorder_cant_up_old_ord');
      }
      //Commit and finish
      if(!ShopDB::commit('order reordered')){
        return false;
      }

      addNotice('order_madefinal');

      return true;
    } else {
      return addWarning('cannot_begin_transaction');;
    }
  }

  function reissue ($order_id){

    if(ShopDB::begin('reissue order: '.$order_id)){
      //loads old order into var
      $query="SELECT *
              FROM `Order`
              WHERE order_id='$order_id' FOR UPDATE";
      if (!$order=ShopDB::query_one_row($query)){
        return self::_abort('order_not_found');
      }

      //checks to see if its an remitted or canceled order!
      if($order['order_status']=='cancel' or
       $order['order_status']=='reissue'){
        return self::_abort('order_already_reissue_cancelled');
      }

      //Update Status to let the admin know the order has been remitted.
      OrderStatus::statusChange($order['order_id'],'reissue',null,'Order::reissue','Order Reissue');

      //Selects Seats from old order using passed order_id from 'params'
      $query="SELECT seat_id FROM `Seat` WHERE seat_order_id='$order_id' FOR UPDATE";
      if(!$res=ShopDB::query($query)){
        return self::_abort('order_cannot_lock_seats');
      }
      //Runs through each seat and gives it a new seat_code and the new order_id.
      $seats = Seat::loadAllOrder($order['order_id']);
      foreach($seats as $seat ){
        $seat->seat_code=Seat::generate_code(8);
        if (!$seat->save()){
          return self::_abort('order_cannot_change_seat');
        }
      }
      //Update Status to let the admin know the order has been remitted.
      OrderStatus::statusChange($order['order_id'],$order['order_status'],null,'Order::reissue','Order Completed Reissue');

      $handling = Handling::load($order['order_handling_id']);
      if($handling->handling_shipment == 'email'){

        if($order['order_shipment_status']=='send'){
          self::set_send($order_id);
        }

      }else{
        addNotice('tickets_reissued_remember_to_send');
      }

      //Commit and finish
      if(!ShopDB::commit('order reissued')){
        return addWarning('order_cannot_reissue');
      }

      addNotice('order_type_reissued_Success');

      return $order['order_id'];
    }
    return addWarning('cannot_begin_transaction');
  }

  function set_send ($order_id){
    $order=self::load($order_id);
    $order->set_shipment_status('send');
  }

  function set_paid ($order_id){
    $order=self::load($order_id);
    $order->set_payment_status ('paid');
  }

  function set_reserved ($order_id){
    $order=self::load($order_id);
    $order->set_status ('res');
  }

  /**
   * Order::set_payment_id()
   *
   * Use to set the payment id for a third party EPH such as:
   * Google, iDEAL, PayPal.
   *
   * @param int $order_id
   * @param mixed $payment_id
   * @return boolean
   */
  public function set_payment_id($order_id, $payment_id=null){
    $order=self::load($order_id);

    if(ShopDB::begin('set_order_payment_id')){
      $query="UPDATE `Order` SET
                order_payment_id="._esc($payment_id)."
              WHERE order_id="._esc($order_id);
      if(!ShopDB::query($query)){
        ShopDB::rollback('set order_payment_id');
        return FALSE;
      }

      return ShopDB::commit('set_order_payment_id');
    }
  }

  static function set_statusEx($order_id, $new_status){
    if(ShopDB::begin('set_statusEx to '.$new_status)){
      $order=self::load($order_id);
      if($order->order_status=='cancel' or
         $order->order_status=='reissue'){
        return false;
      }
      if (!$order->set_status ($new_status)) {
        return self::_abort('Cant set extern status');
      }
      return ShopDB::commit('set_statusEx commit');
    }
  }

  function set_status ($new_status,$dont_do_update=FALSE){
    return $this->_set_status('order_status',$new_status,$dont_do_update);
  }

  function set_payment_status ($new_status,$dont_do_update=FALSE){
    return $this->_set_status('order_payment_status',$new_status,$dont_do_update);
  }

  function set_shipment_status ($new_status,$dont_do_update=FALSE){
    return $this->_set_status('order_shipment_status',$new_status,$dont_do_update);
  }

  function _set_status ($field, $new_status, $dont_do_update=FALSE){
    if(ShopDB::begin("change_{$field}_to_{$new_status}")){
      $fieldx =(!in_array($field,array('order_status', 'order_payment_status')))?", $field":'';
	   	$query="SELECT order_status, order_payment_status $fieldx FROM `Order` WHERE order_id='{$this->order_id}' for update";
	   	if($data=ShopDB::query_one_row($query)){
	   		$this->_fill($data);
	   	  writeLog( print_r($data, true));
	   	  writeLog("$field => $new_status");
	   	} else {
	   	  return $this->_abort('Cant_lock_order_to_set_status');
	   	}

	   	$old_status=$this->order_status;
	   	$old_payment_status = $this->order_payment_status;
	   	//checks to see if its an remitted or canceled order!
	   	if($this->order_status=='cancel' or
	       $this->order_status=='reissue'){
	   		return self::_abort('order_already_cancelled');
	   	}

	   	if (!plugin::call('checkOrderStatusChanged',$this, $new_status, $dont_do_update)) {
	   		return self::_abort();
	   	}

      if(!$this->user_id){
        $query="SELECT * FROM `User` WHERE user_id='{$this->order_user_id}'";
        if($data=ShopDB::query_one_row($query)){
          $this->_fill($data);
        }
      }

      if($field=='order_payment_status'
          and  $new_status=='pending'
          and  $old_payment_status =='paid'){ //and
        self::_abort(); //'order_already_paid'
        return true; // just show the m
      }
      $suppl = ($field=='order_payment_status' and $new_status=='paid')?", order_date_expire=NULL":'';
      $query="UPDATE `Order` SET {$field}='{$new_status}' $suppl WHERE order_id='{$this->order_id}'";
      if($dont_do_update || (ShopDB::query($query))){// and shopDB::affected_rows()==1)){

        OrderStatus::statusChange($this->order_id,$new_status,NULL,'Order::_set_status',"Set $field to $new_status");

        if(!$this->handling){
          $this->handling=Handling::load($this->order_handling_id);
        }
        $this->handling->handle($this,$new_status,$old_status,$field);
      }
      $this->$field = $new_status;

      return ShopDB::commit("changed_{$field}_to_{$new_status}");
    }
    return false;
  }

  function toTrash(){
    if (ShopDB::begin('trash order')){
      $query="SELECT order_id, order_tickets_nr, count(seat_id) as count
              FROM `Order` left join Seat on seat_order_id=order_id
              WHERE order_status !='trash'
              AND seat_status='trash'
              GROUP BY order_id
              FOR UPDATE";

      if(!$res=ShopDB::query($query)){
        return self::_abort('cant_lock_order');

      }


      while($data=shopDB::fetch_assoc($res)){
        //print_r($data);
        if($data['order_tickets_nr']==$data['count']){
          $query="update `Order` set
                    order_status='trash'
                  where order_id='{$data['order_id']}'";
          if(!ShopDB::query($query)){
            return self::_abort('cant_change_order_to_trash');
          }
        }
      }
      // trash orders without seats.
      $query="SELECT order_id, count(seat_id) as count
              FROM `Order` left join Seat on seat_order_id=order_id
              WHERE order_status !='trash'
              GROUP BY order_id
              having count = 0
              FOR UPDATE";

      if(!$res=ShopDB::query($query)){
        return self::_abort('cant_lock_order');
      }

      while($data=shopDB::fetch_assoc($res)){
        //print_r($data);
        if((int)$data['count'] == 0){
          $query="update `Order` set
                    order_status='trash'
                  where order_id='{$data['order_id']}'";
          if(!ShopDB::query($query)){
            return self::_abort('cant_change_order_to_trash');
          }
        }
      }
      return ShopDB::commit('order trashed');
    }
  }

  function emptyTrash(){
    global $_SHOP;
    if (ShopDB::begin('empry trashed orders')){
      $query="DELETE o.*, os.*
              FROM `Order` o
                LEFT JOIN Seat ON o.order_id=seat_order_id
                LEFT JOIN `order_status` os ON order_id = os_order_id
              WHERE o.order_status='trash'
                AND seat_id is NULL";

      if(!ShopDB::query($query)){
        return self::_abort('cant_delete_trashed_orders');
      }

      return ShopDB::commit('trashed order emptyed');
    }
  }

  function purgeDeleted($order_handling_id = 0){
    if(ShopDB::begin("Purge Delete start")){
      if($order_handling_id>0){
        $handling_cond = "and order_handling_id = "._esc($order_handling_id);
        $fields[' AND order_handling_id']= $order_handling_id;

      }

      $query = "SELECT * FROM `Order`
          WHERE order_status='cancel' $handling_cond
          FOR UPDATE";

      if(!$res = ShopDB::query($query)){
        return self::_abort("failed_to_find_cancelled_orders");
      }
      $nRows = ShopDB::num_rows($res);

      $fields[' AND order_status'] = 'cancel';
      OrderStatus::massStatusChange($fields,'trash',null,'Order::purgeDeleted',"Purged order to the bin");

      $query = "UPDATE `Order` SET
                  order_status='trash'
                WHERE order_status='cancel'
                {$handling_cond}";

      if(!ShopDB::query($query) || ShopDB::affected_rows()<>$nRows){
         return self::_abort("Failed to update purge.");
      }

      return ShopDB::commit("Purged Data to bin");
    }
    return false;
  }

  function purge($order_handling_id=0){
    Order::purgeDeleted($order_handling_id);
  }

  /**
   * Order::EncodeSecureCode()
   *
   * @param string $item : property value
   * @param bool $loging : set to true to enable extra logging to dblogging
   * @return string;
   */
  function EncodeSecureCode($item='sor=', $loging=false) {

    if (!$this->order_tickets_nr ) $this->order_tickets_nr = $this->size();

    $md5  = $this->order_session_id.'~'.$this->order_user_id .'~'. $this->order_tickets_nr .'~'.
            $this->order_id .'~'. $this->order_total_price;
    $md5x = base_convert(md5($md5),16,36);
    $code = base64_encode(base_convert(time(),10,36).'_'. base_convert($this->order_id,10,36).'_'. $md5x);
    if ($loging) {
      $_SESSION['LAST_SOR'] = 'Encode:'.$code.'|'.$md5.'|'.base_convert(md5($md5),16,36).' -> '.urlencode ($code)."/n";
    }

    return $item. strtr($code, '+/=', '-_~'); //  }
  }

  /**
   * Order::DecodeSecureCode()
   *
   * This is used by most checkout actions and eph's where we sent a url to the EPH and when the system GET/POST the SOR back we can
   * handle the request and load the order and proccess the payment.
   *
   * ***THIS WORKS DIFFERNTLY TO CallBackRequest***
   *
   * @param string $codestr : SecureOrderRequest code (SOR).
   * @param bool $loging : set to true to enable extra logging to dblogging
   * @return int/object : true if code correct and order loaded
   * Call Back errors:
   * -5 : No SOR
   * -4 : MD5 doesnt match the one sent
   * -3 : Order Loaded doesnt match SOR
   * -1 : Can't load/get Order Object
   * $order (Tobject) : All is good!
   */
  static function DecodeSecureCode( $codestr ='', $secureType='sor', $loging=false) {
    $error ='';
    if ($secureType=='cbr'){
      $return = Handling::decodeEPHCallback($codestr, true);
      if (is_a($return,'Order') || is_numeric($return)) return $return;
      if (is_a($return,'Handling')) {
        $codestr = $return;
      } elseif (is_null($return)) {
      	return -99;
      } else {
        Die('System error: Contact your the webmaster.');
      	var_dump($return);
      }

    }
    If (empty($codestr) and isset($_REQUEST['sor'])) $codestr = $_REQUEST['sor'];
   //
    If (!empty($codestr)) {
      $text = base64_decode(strtr($codestr, '-_~', '+/='));
      $code = explode('_',$text);
      $code[0] = base_convert($code[0],36,10);
      $code[1] = base_convert($code[1],36,10);
      if ($loging) {
        if (!isset($_SESSION['LAST_SOR'])) $_SESSION['LAST_SOR'] ='';
        $_SESSION['LAST_SOR'] .= ('Decode:'.$codestr.' -> '.$text)."/n";
        $_SESSION['LAST_SOR'] .= ('Code:'.print_r( $code, true)."/n");
        $_SESSION['LAST_SOR'] .= ('MD5.a:'.$code[2])."/n";
      }

      $order = self::loadExt($code[1], true);
      if (!is_object($order)) return -1;

      $md5 = $order->order_session_id.'~'.$order->order_user_id .'~'. $order->order_tickets_nr .'~'.
             $order->order_id .'~'. $order->order_total_price;
      $md5 = base_convert(md5($md5),16,36);
      if ($loging) {
        $_SESSION['LAST_SOR'] .= ('MD5.b:'.$md5)."/n";

        $_SESSION['LAST_SOR'] .= ('code[2] <> $md5 = '.(($code[2] <> $md5)?'True':'false'))."/n";
        $_SESSION['LAST_SOR'] .= ('strcmp (code[2], $md5) = '.strcmp($code[2], $md5))."/n";
      }
//      if ($code[0] > time()) return -2;
      if ($code[1] <> $order->order_id) return -3;
      if ($code[2] <> $md5) return -4;
      return $order;
    } else
      return -5;
  }

  /**
   * Order::printOrder()
   *
   * @param mixed $order_id
   * @param string $bill_template
   * @param string $mode
   * @param bool $print
   * @param integer $subj : 1=tickets, 2=invoice, 3=both, 4=address
   * @return pdf_data or pdf_file
   */
  function printOrder($order_id, $bill_template='', $mode='file', $print=FALSE, $subj=3, $admin=false){ //print subj: 1=tickets, 2=invoice, 3=both, 4=address LXSPARKS 04/08/2013
    require_once("classes/model.template.php");
    require_once(LIBS."html2pdf/html2pdf.class.php");
    require_once('classes/smarty.gui.php');

    global $_SHOP;
    if (!$mode) $mode = 'file';

    $orderqry = '
        SELECT `Order`.*,User.*, Handling.*, discount_name as orderdiscount_name , discount_cond as orderdiscount_cond
        FROM `Order` left join User     on (order_user_id= user_id)
                     left join Handling on (order_handling_id = handling_id)
                     left join Discount on (order_discount_id = discount_id)
        where order_id = '._esc($order_id);

    $seatqry = '
        SELECT * FROM Seat LEFT JOIN Discount ON seat_discount_id=discount_id
                           left join Event    on event_id = seat_event_id
                           left join Event_group on Event.event_group_id = Event_group.event_group_id
                           left join Ort      on ort_id = event_ort_id
                           left join Category on category_id = seat_category_id
                           left join PlaceMap2 on event_pm_id = pm_id
                           left join PlaceMapZone on seat_zone_id = pmz_id
                           left join PlaceMapPart on seat_pmp_id = pmp_id
        WHERE seat_order_id = '._esc($order_id);



    if(!$order=ShopDB::query_one_row($orderqry)){
      return addWarning('cant_load_orderdata');
    }

    if(!$res=ShopDB::query($seatqry)){
      return addWarning('cant load ticket data');
    }
    $order['bill'] = array();

    $hand = Handling::load($order['order_handling_id']);
    $seats = array();
    while($data=shopDB::fetch_assoc($res)){
      if($data['category_numbering']=='none'){
        $data['seat_nr']='0';
        $data['seat_row_nr']='0';
      }else if($data['category_numbering']=='rows'){
        $data['seat_nr']='0';
      }else if($data['category_numbering']=='seat'){
        $data['seat_row_nr']='0';
      }
      //compute  barcode
      $data['barcode_text']=  seat::encodeBarcode( $order, $data);

      //save the data for the bill
      $key = "{$data['category_id']}";
      if ($data['discount_id']) {
        $key .= "_{$data['discount_id']}";
      }

      if(!isset($order['bill'][$key])){
        $order['bill'][$key]=array(
          'event_name'=>$data['event_name'],
          'event_date'=>$data['event_date'],
          'event_time'=>$data['event_time'],
          'event_open'=>$data['event_open'],
          'ort_name'=>$data['ort_name'],
          'ort_city'=>$data['ort_city'],
          'qty'=>1,
          'category_name'=>$data['category_name'],
          'seat_price'=>$data['seat_price'],
          'discount_name'=>$data['discount_name']
        );
      }else{
        $order['bill'][$key]['qty']++;
      }
      if (($subj & 1) && ($order['order_payment_status'] == 'paid' || $admin)) {
          //  print_r($seat); print($hand);
        if($hand->handling_pdf_ticket_template){
          $tpl_id = $hand->handling_pdf_ticket_template;
        }else if($data['category_template']){
          $tpl_id = $data['category_template'];
        }else if($data['event_template']){
          $tpl_id = $data['event_template'];
        }else{
          $tpl_id = false;
        }
        $data['tpl_id'] = $tpl_id;
        $tickets[$tpl_id][] = $data;
        $seats[] = $data;
      }
    }
    //calculating the sub-total
    foreach(array_keys($order['bill']) as $key){
      $order['bill'][$key]['total']= $order['bill'][$key]['seat_price'] * $order['bill'][$key]['qty'];
    }
    $order['order_subtotal'] = $order['order_total_price'] - $order['order_fee'];


    $order['user_country_name']= gui_smarty::getCountry($order['user_country']);
    $order['order_seats'] = $seats;
    $order['pdf_subject'] = $subj;

    $paper_size=$_SHOP->pdf_paper_size;
    $paper_orientation=$_SHOP->pdf_paper_orientation;
    if ($mode==='bulk' and is_object($print)) {
      $pdf = $print;
    } else {
      $pdf = new html2pdf(($paper_orientation!=="L")?'P':'L', $paper_size, $_SHOP->lang);
    }



    if(!$bill_template){
      $bill_template=$hand->handling_pdf_template;
    }
    $first_page = true;
    if($bill_template and ($subj & 2)){
      //loading the template
      if($tpl = Template::getTemplate($bill_template)){
        $first_page=FALSE;

        //applying the template
        $tpl->write($pdf, $order);
      }else{
        return addWarning('template_not_found', $bill_template);
      }

    }

		$address_template = $_SHOP->address_label_template; // Lxsparks 05/08/2013

    if (($subj & 1) && ($order['order_payment_status'] == 'paid' || $admin)) {
      if ($order['order_shipment_status'] == 'none') {
        self::set_send($order_id);
      }

			if ($address_template && ($_SHOP->address_label_always==1)){ // Lxsparks 05/08/2013
				if($label = Template::getTemplate($address_template)){
					//applying the template
					$label->write($pdf, $order);
				}
			}

      foreach($seats as $seat) {
        $tpl_id = $seat['tpl_id'];
        if($tpl_id ){
          //load the template
          if(!$tpl =&Template::getTemplate($tpl_id)){
            return addwarning('no_template', ": name: {$tpl_id} cat: {$seat['category_id']}, event: {$seat['event_id']}");
          }
  //        echo $tpl_id,":";
  //
          $first_page=FALSE;

          //print the ticket
          If (is_object($tpl)) {
            $tpl->write($pdf, array_merge($seat,$order), false);
          } else {
            var_dump($tpl);
            return addwarning('no_template', ": name: {$tpl_id} cat: {$seat['category_id']}, event: {$seat['event_id']}");

          }
        }
      }
    }

    if($address_template and ($subj==4)){ // Lxsparks 05/08/2013

      if($label = Template::getTemplate($address_template)){
        $first_page=FALSE;

        //applying the template
        $label->write($pdf, $order);
      } else {
        return addWarning('template_not_found', $address_template);
      }
    }

    if(true and !$first_page){

      //composing filename without extension

    	if (isset($subj)){
    	 if ($subj=="3") { $fext=con("pdf_tickets_and_invoice");}
    	 if ($subj=="1") { $fext=con("pdf_tickets_only");}
    	 if ($subj=="2") { $fext=con("pdf_invoice_only");}
			 if ($subj=="4") { $fext=con("pdf_address_label");} // Lxsparks 05/08/2013
	  	  }
      $order_file_name = "order_".$order_id.'_'.$fext.'.pdf';
        //producing the output

      if($mode=='file'){
        $pdf->output($_SHOP->ticket_dir.DS.$order_file_name, 'F');
      }else if($mode=='stream'){
        if($print){
          $pdf->pdf->IncludeJS("print({bUI: false, bSilent: true}); this.exit(true)");
        }
      //    $pdf->output($order_file_name, 'D');
      //  } else
        $pdf->output($order_file_name, 'I');
      }else if($mode=='data'){
        $pdf_data=$pdf->output($order_file_name, 'S');
      }else if($mode=='bulk'){
        $pdf_data = $pdf;
      }
      return $pdf_data;
    } elseif($mode=='stream'){
      echo '<h1>'.con('pdf_noting_to_show').'</h1>';
    }
  }
}


function _collect(&$event_item,&$cat_item,&$place_item,&$order){

  if(!$place_item->is_expired()){

    $i=0;
    foreach($place_item->seats as $place_id => $place){
      $order->add_seat($event_item->event_id, $cat_item->cat_id, $place_id, $cat_item->cat_price, $place_item->discount($place->discount_id));
      $i++;
    }
    $place_item->ordered =  $order;
  }

  return 1;
}

function _reset(&$event_item,&$cat_item,&$place_item,&$order){
  if ($place_item->ordered ==  $order) {
    unset($place_item->ordered);
  }
  return 1;
}
?>