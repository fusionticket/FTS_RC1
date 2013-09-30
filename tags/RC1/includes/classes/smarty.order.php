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

class Order_Smarty {

  var $user_auth_id;

  function __construct ($smarty){
    global $_SHOP;
    $smarty->register_object("order",$this,null,true,array("order_list","tickets"));
    $smarty->assign("order",$this);

    if(isset($_SESSION['_SHOP_AUTH_USER_DATA']['user_id'])) {
      $this->user_auth_id=$_SESSION['_SHOP_AUTH_USER_DATA']['user_id'];
    }
  }

  function can_freeTicketCode() {
  	global $_SHOP; ///print_r( $_SHOP  );
    return  !empty($_SHOP->freeTicketCode) || Discount::hasGlobals();
  }


  function res_to_order($params,$smarty){
      $order_id=$params['order_id'];
      $handling_id=$params['handling_id'];

      if(empty($order_id) || empty($handling_id)){
        return;
      }

      //if(($order_id=$this->secure_url_param($params['order_id']))<=1){return;}
      //if(($handling_id=$this->secure_url_param($params['handling_id']))<=1){return;}
      if($params['no_cost']===true){$no_cost=true;}
      if($params['no_fee']===true){$no_fee=true;}
      if($params['place']!='pos'){$place='www';}else{$place='pos';}

    if($this->res_to_order_f($order_id,$handling_id,$no_fee,$no_cost,$place)){
      $smarty->assign('order_success',true);
    }
  }

  function res_to_order_f($order_id,$handling_id,$no_fee,$no_cost,$place){
    //global $_SHOP; // no need for this?
    return Order::reserve_to_order($order_id,$handling_id,$no_fee,$no_cost,$place);
  }


  function cancel ($params,$smarty){
    $this->cancel_f($params['order_id'],$params['reason']);
  }

  function cancel_f ($order_id, $reason = null ){
    if ($order = Order::load($order_id)) {
      return Order::delete($order_id, is($reason,'order_cancelled_by_user'), $this->user_auth_id);
    }
  }

  function delete_ticket ($params, $smarty){
    $this->delete_ticket_f($params['order_id'],$params['ticket_id']);
  }

  function delete_ticket_f ($order_id,$ticket_id){
    if ($order = Order::load($order_id)) {
      return Order::delete_ticket($order_id,$ticket_id,0,$this->user_auth_id);
    }
  }



  /**
   * Order_Smarty::order_list()
   *
   * @param mixed $params
   * @param mixed $content
   * @param mixed $smarty
   * @param mixed $repeat
   * #Passable Params:
   * 	status = "ord,send"
   * 	user = id
   * 	place =
   * 	not_sent = bool
   * 	not_status = "paid,send"
   * 	order = "order_date DESC,order_time DESC"
   * 	order_id = id
   *  curr_order_id = "curr_id [DESC|ASC]" Subject to change..
   */
  function order_list ($params, $content, $smarty,&$repeat){

    if ($repeat) {
      $from= " FROM `Order` ";
      $where = " WHERE Order.order_status!='trash'";

      if($params['user_id']){
        $user_id=$this->secure_url_param($params['user_id']);
        $where .= " AND order_user_id='{$user_id}'";
  /*    }else if($this->user_auth_id){
           $where="where order_user_id='{$this->user_auth_id}' AND Order.order_status!='trash'";
  */
      }else{
        /* Filter Status */
        if($params['status']){
          $status=$params['status'];
      		$types=explode(",",$status);

      		foreach($types as $type){
            if($type=="paid"){
              $where .=" AND Order.order_payment_status='{$type}'";
            }elseif($type=="send"){
              $where .=" AND Order.order_shipment_status='{$type}'";
            }elseif($in){$in .= ",'".$type."'";
      			}else{$in = "'".$type."'";}
      		}
          if($in){
      		  $where .= " AND Order.order_status IN ({$in})";
          }
          unset($in);
        }
      }

      /* Not these status's */
      if($params['not_status']){
        $notStatus=$params['not_status'];
    		$types=explode(",",$notStatus);

    		foreach($types as $type){
          if($type=="paid" && $params['status']!="paid" ){
            $where .=" AND Order.order_payment_status <> "._esc($type);
          }elseif($type=="send" && $params['status']!="send"){
            $where .=" AND Order.order_shipment_status <> "._esc($type);
          }elseif($in){$in .= ","._esc($type);
    			}else{$in = _esc($type);}
    		}
        if($in){
    		  $where .= " AND Order.order_status NOT IN ({$in})";
        }
        unset($in);
      }

      /* Filter Handling */
      if($params['handling'] || $params['not_hand_payment'] || $params['not_hand_shipment'] || $params['hand_shipment'] || $params['hand_payment']){
      	$from .= ' left join Handling on handling_id = order_handling_id';

        //Not these payment types
      	if($params['not_hand_payment']){
      		$types=explode(",",$params['not_hand_payment']);
      		foreach($types as $type){
      			if($in){$in .= ",'".$type."'";
      			}else{$in = "'".$type."'";}
      		}
      		$where.=" AND handling_payment NOT IN ({$in})";
      	}
      	unset($in);

        //Not these shipping types
      	if($params['not_hand_shipment']){
      		$types=explode(",",$params['not_hand_shipment']);
      		foreach($types as $type){
      			if($in){$in .= ",'".$type."'";
      			}else{$in = "'".$type."'";}
      		}
  			$where.=" AND handling_shipment NOT IN ({$in})";
      	}
      	unset($in);

        //These shipping types
      	if($params['hand_shipment']){
      		$types=explode(",",$params['hand_shipment']);
      		foreach($types as $type){
      			if($in){$in .= ",'".$type."'";
      			}else{$in = "'".$type."'";}
      		}
      		$where.=" AND handling_shipment IN ({$in})";
      	}
      	unset($in);

        //These payment types
      	if($params['hand_payment']){
      		$types=explode(",",$params['hand_payment']);
      		foreach($types as $type){
      			if($in){$in .= ",'".$type."'";
      			}else{$in = "'".$type."'";}
      		}
      		$where.=" AND handling_payment IN ({$in})";
      	}
      }

      //Grab user details too
      if($params['user']){
        $from.=' left join User on order_user_id=user_id';
      }
      //Order Location
      if($params['place']) {
        $where .=" and order_place='{$params['place']}'";
      }
      /*DEPRICATED USE NOT_STATUS NOW */
      if($params['not_sent']){
        $where .=" AND order_shipment_status != 'send' ";
      }


      if($params['owner_id']){
        $where .= " and order_owner_id='{$params['owner_id']}'";
      }

      if($params['order_search']) {
          if(!$params['user']){
            $from.=' left join User on order_user_id=user_id';
          }

          $searchcount =0;
        $where = 'where 1=1 ';
        if($_REQUEST['seach_order_id']) {
          $where .=" and order_id='{$_REQUEST['seach_order_id']}'";
          $searchcount++;
        }

          if($_REQUEST['search_patron']){
          $where .= " and (User.user_lastname like "._esc('%'.clean($_REQUEST['search_patron']).'%').") \n";
            $searchcount++;
          }
          if($_REQUEST['search_phone']){
          $where .= " and (User.user_phone like "._esc('%'.clean($_REQUEST['search_phone']).'%').") \n";
            $searchcount++;
          }
          if($_REQUEST['search_email']){
          $where .= " and (User.user_email like "._esc('%'.clean($_REQUEST['search_email']).'%').") \n";
            $searchcount++;
          }
          if (!$searchcount) {
          if ($_REQUEST['search_submit']) {
            addwarning('search_orderid_patron');
          }
          $where = ' where 1 = 0 ';
        }
      } elseif($params['order_id']){
          $where .= " and order_id="._esc($params['order_id']);
      }
      if($params['curr_order_id']){
          $direction = explode(" ",$params['curr_order_id']);
          $curr_order_id=$this->secure_url_param($direction[0]);
          if(strcasecmp(is($direction[1],''),'DESC')===0){
            $where .= " AND order_id < '{$curr_order_id}' ";
          }else{
            $where .= " AND order_id > '{$curr_order_id}' ";
          }

      }

      if($params['start_date']){
        $where .= " and order_date>='{$params['start_date']}'";
      }

      if($params['end_date']){
        $where .= " and order_date<='{$params['end_date']}'";
      }

      if($params['order']){
        $order_by =" order by {$params['order']}";
      } elseif($params['order_by_date']){
        $order_by = " ORDER BY order_date {$params['order_by_date']}";
      }

      if($params['first']){
        $first=$this->secure_url_param($params['first']);
        $limit='limit '.$first;

     	if($params['length']){
            $limit.=','.$params['length'];
        }
      }elseif($params['length']){
        $limit='limit 0,'.$params['length'];
      }


      $query="SELECT SQL_CALC_FOUND_ROWS * $from $where $order_by $limit";
      $res=ShopDB::query($query);

      $part_count = ShopDb::query_one_row("Select FOUND_ROWS()", false);
      $part_count = $part_count[0];
      $res = array($res,$part_count);
      $order=shopDB::fetch_assoc($res[0]);

    }else{
      $res=$smarty->popBlockData();
      $part_count= $res[1];
      if(isset($res)){
        $order=shopDB::fetch_assoc($res[0]);
      }
    }

    if($params['all']){
      if(!empty($order)){
        $orders[]=$order;
        while($order=shopDB::fetch_assoc($res)){
          $orders[]=$order;
        }
        $smarty->assign("shop_orders",$orders);
        $smarty->assign("shop_orders_count",$part_count);
      }

      $repeat=FALSE;
      return $content;

    }else{

      $repeat=!empty($order);

      if($order){
        $smarty->assign("shop_order",$order);
        $smarty->assign("shop_orders_count",$part_count);
        $smarty->pushBlockData($res);

        $query="SELECT * FROM User WHERE user_id={$order['order_user_id']}";
        $res=ShopDB::query($query);
        $user=shopDB::fetch_assoc($res);
        if($user){
          $smarty->assign("user_order",$user);
        }
      }
    }
    return $content;
  }

	function tickets ($params, $content, $smarty,&$repeat){

		if ($repeat) {
      		if(!$params['order_id']){
        		$repeat=FALSE;
        		return;
      		}
      		$order_id=$this->secure_url_param($params['order_id']);

      		$from='FROM Seat LEFT JOIN Discount ON seat_discount_id=discount_id
            		LEFT JOIN Event ON seat_event_id=event_id
           			LEFT JOIN Category ON seat_category_id= category_id
            		LEFT JOIN PlaceMapZone ON Seat.seat_zone_id=pmz_id';
      		$where=" where seat_order_id='{$order_id}'";

	    	if($params['user_id']) {
	    		$where.=" and seat_user_id='{$this->user_auth_id}'";
	    	}
	    	if($params['place']) {
	      		$where .=" and order_place='{$params['place']}'";
	    	}
	      	if($params['order']){
		        $order_by="order by {$params['order']}";
	    	}

	      	if($params['limit']){
				$length=$this->secure_url_param($params['limit']);
	        	$limit='limit '.$length;
	      	}

	      	$query="select * $from $where $order_by $limit";

	      	$res=ShopDB::query($query);

	      	$ticket=ShopDB::fetch_array($res);
    	}else{
      		$res=$smarty->popBlockData();
		  	$ticket=ShopDB::fetch_assoc($res);
    	}
    	if($params['all']){
   			//$repeat=!empty($ticket); //not required
     		if($ticket){
       			$c=1;
		       $tickets[]=$ticket;
		       while($ticket=ShopDB::fetch_assoc($res)){
		         $tickets[]=$ticket;$c++;
		       }

		       $smarty->assign("shop_tickets",$tickets);
		       $smarty->assign("shop_tickets_count",$c);
     		}
     		$repeat=FALSE;
   			return $content;
    	}elseif($params['min_date']){
    		//$repeat=!empty($ticket); //not required.
    		if($ticket){
		    	$c=1;
		        $min_date=true;
		        while($ticket=ShopDB::fetch_assoc($res)){
		        	$c++;
		            $min_date=min($ticket['event_date'],$min_date);
      			}
      			$smarty->assign("shop_tickets_count",$c);
    			$smarty->assign("shop_ticket_min_date",$min_date);
      		}
      		$repeat=FALSE;
      		return $content;
    	}else{
      		$repeat=!empty($ticket);
      		if($ticket){
	        	$smarty->assign("shop_ticket",$ticket);
	        	//print_r($ticket);
	      		$smarty->pushBlockData($res);
			    }
	    }
    return $content;

  }
  //Added v1.3.4 For Processing Menu PoS process.tpl
  function set_status_f($order_id,$status){
    return Order::set_status_order($order_id,$status);
  }

  function setStatusPaid($order_id){
    return Order::set_paid($order_id);
  }

  /**
   * Order_Smarty::set_send_f()
   *
   * @deprecated beta6
   * @return
   */
  function set_send_f($order_id){
    global $_SHOP;
    return Order::set_send($order_id, 0, $this->user_auth_id);
  }

  function setStatusSent($order_id){
    global $_SHOP;
    return Order::set_send($order_id, 0, $this->user_auth_id);
  }

  function set_reserved ($params,$smarty){
    $this->set_reserved_f($params['order_id']);
  }

  function set_reserved_f ($order_id){
    global $_SHOP;
    return Order::set_reserved($order_id, 0, $this->user_auth_id);
  }

  // added for manual Pepper system - Legacy

  function save_order_note($params,$smarty){
    $ret=Order::save_order_note($params['order_id'],$params['note']);
    $smarty->assign('order_note',$ret);
  }

  /**
   * Order_Smarty::add_order_note()
   *
   * Need to POST at least onote_title,onote_body
   * <code>
   * {if $smarty.request.action eq 'addnote'}
   *   {order->add_order_note}
   *   {if $order_note}
   *     ...
   *   {/if}
   * {/if}
   * </code>
   * @return bool + assign warning/notice
   */
  function add_order_note($params,$smarty){
    $orderNote = new OrderNote();
    if (!$orderNote->fillRequest() || !$orderNote->saveEx()) {
      $smarty->assign('success',false);
      addWarning('failed_to_add_note');
      return;
		}

    if(!$order=Order::load($_REQUEST['order_id'])){return;}
    $order->emailSubject = $orderNote->onote_subject;
    $order->emailNote = $orderNote->onote_note;
    $this->_sendNote($orderNote,$order,$_REQUEST);
    $smarty->assign('success',true);
    addNotice('successfully_add_note');
  }

  function resend_note($params,$smarty){
    if(!is($_REQUEST['onote_id'],false)){
      addWarning('bad_id');
    }

    if($orderNote = OrderNote::load($_REQUEST['onote_id'])){return;}
    if(!$order=Order::load($_REQUEST['order_id'])){return;}
    $order->emailSubject = $orderNote->onote_subject;
    $order->emailNote = $orderNote->onote_note;

    if($this->_sendNote($orderNote, $order, $_REQUEST)){
      addNotice('successfully_sent_note');
    }

  }

  /**
   * Order_Smarty::_sendNote()
   *
   * Shared Function for note sending.
   *
   * @return bool
   */
  private function _sendNote($onote, $order, $request){
    if(!is_object($onote) || !is_object($order)){
      return false;
    }
    if(is($request['onote_set_sent'])==="1"){
      $order->set_shipment_status('send');
    }elseif(is($request['onote_set_paid'])==="1"){
      $order->set_payment_status('paid');
    }elseif(isset($request['save_payment'])){
      $onote->sendNote($order);
    }elseif(isset($request['save_ship'])){
      $onote->sendNote($order);
    }elseif(isset($request['save_note'])){
      $onote->sendNote($order);
    }
    if(!hasErrors("__Warning__")){
      return true;
    }
  }

  function set_paid ($params,$smarty){
    $this->set_paid_f($params['order_id']);
  }

  function set_paid_f ($order_id){
    return Order::set_paid($order_id);
  }

  function order_print ($params, $smarty){

    if($params['print_prefs']=='pdf'){
      $print=FALSE;
    }else{
      $print=TRUE;
    }
    $mode = (int)$params['mode'];
    If (!$mode) $mode =3;

    Order::printOrder($params['order_id'], '', 'stream', $print, $mode);
  }

  function paymentForOrder($params, $smarty){
    $orderId = is($params['order_id'],0);
    $orderInput = Order::load($orderId, true);
    $return = '';
    if(!is_object($orderInput)){
       addWarning('invalid_order');
    } else {
      $hand = $orderInput->handling; // get the payment handling object
      $confirmtext = $hand->on_confirm($orderInput); // get the payment button/method...
      $return = (is_string($confirmtext))?$confirmtext:'';
    }
    $smarty->assign('payment_tpl',$return);
  }

  function EncodeSecureCode($order= null, $item='sor=', $loging=false) {
    if (is_numeric($order)) $order = Order::load($order);
    if(!is_object($order)) return '';
    return $order->EncodeSecureCode($item, $loging);
  }

  function secure_url_param($num=FALSE, $nonum=FALSE)
  {
    if ($num) {
      $correct = is_numeric($num);
        if( $correct ) { return $num; }
        elseif(!$correct ){
          echo "No Such ID";
          //$num = cleanNUM($num);
          $num="1";
          return $num;
        }
      }
      if ($nonum) {
      $correct = preg_match('/^[a-z0-9_]*$/i', $nonum);
        //can also use ctype if you wish instead of preg_match
        //$correct = ctype_alnum($nonum);
        if($correct) { return $nonum; }
        elseif(!$correct) {
          addWarning("No Such Variable");
          $nonum="This";
          return $nonum;
      }
    }
  }
  /**
   * Countdown now used the order_date_expire.
   * Could be simplified down as there shouldnt be the need for the two
   * seperate methods.
   *
   * @name countdown
   * @uses Time, ShopDB
   * @author Christopher Jenkins
   * @access Public
   * @todo Clean and remove unnessary method and both use the same field to calc remaining time.
   * @version BETA4
   * @since 1.3.4
   */
  function countdown( $params, $smarty ) {
    global $_SHOP;

    $order_id = $this->secure_url_param( $params['order_id'] );
    $query = "SELECT order_date_expire
              FROM `Order`
              WHERE order_id=" . ShopDB::quote( $order_id ) ."
              AND order_status NOT IN ('cancel','trash') LIMIT 1";
    if ( $result = ShopDB::query_one_row($query) ) {
      $time  = Time::StringToTime( $result['order_date_expire'] );
      $timeRemain = Time::countdown( $time );
      if($result['order_date_expire'] == null){
        $timeRemain['forever']=true;
      }
      if ( $_SHOP->shopconfig_delunpaid_pos == 0 && $params['pos']==true ){
        $timeRemain['forever']=true;
      }
    }
    $smarty->assign( "order_remain", $timeRemain );
  }
}
?>