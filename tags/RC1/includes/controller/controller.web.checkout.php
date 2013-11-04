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

require_once ("controller.web.shop.php");



class ctrlWebCheckout extends ctrlWebShop {
  private $secureType = false;
  private $secureCode = '';
  protected $useSSL = true;

  public function __construct($context='web', $page, $action) {
    parent::__construct($context, $page, $action);
  }

  public function drawContent() {
    GLOBAL $_SHOP;
    $action = is($this->action, 'index');

    ShopDB::begin('Running the Checkout pages');
    $this->getSecurecode();
    if ($this->secureType) {
    	if (is_callable(array($this,'action'.$action)) and ($fond = call_user_func_array(array($this,'action'.$action),array()))) {
    		$this->smarty->display($fond . '.tpl');
    	  $this->executed = !$this->smarty->ShowThema;
    	} else {
    	  $this->executed = true;
    	}
    } elseif ($this->__MyCart->can_checkout_f() or isset($_SESSION['_SHOP_order']) ) { //or isset($_SESSION['order'])
      if ( !is($_REQUEST['pos'],false) and
           (!$this->__User->logged && (!isset($this->__User->user_status) || (($this->__User->user_status!=3) and ($this->__User->user_status!=2)))) and
  		     $action !== 'register' and
        	 $action !== 'login' ) {
        $this->smarty->display('user_register.tpl');
        $this->executed = !$this->smarty->ShowThema;
      } elseif (is_callable(array($this,'action'.$action)) and ($fond = call_user_func_array(array($this,'action'.$action),array()))) {
     	  if (is_string($fond)) {
     	      $this->smarty->display($fond . '.tpl');
     	      $this->executed = !$this->smarty->ShowThema;
     	  } else {
     	    $this->executed = true;
     	  }
     	} else {
        echo "!!this is not good!! ".$action;
      }

    } else {
      if ($action == 'useredit') {
        $array = array('status'=>false,'msg'=>con('checkout_expired'));
        echo json_encode($array);
      } elseif(!$_REQUEST['pos']) {
      	redirect($_SHOP->root."index.php?action=cart_view",403);
      } else {
        addWarning('noting_checkout'); //echo 'bummer';
      }
    }
    if (ShopDB::isTxn()) { //Commit allready does this check!
      ShopDB::commit('Checkout page rendered.'); //Never Committs!
    }
  }

  Function actionLogin (){
    if (!$user->logged) {
  	  If (! $this->__User->login_f($_POST['username'], $_POST['password'], $errors)) {
  	    $this->assign('login_error',$errors);
  	    return "user_register";
      }
    }
    return "checkout_preview";
  }

  Function actionUseredit (){
    $this->assign('usekasse',true);
    if (isset($_POST['submit_update'])) {
      if ($this->__User->update_f($_POST, $errors)) {
        $array = array('saved'=>true,'msg'=>con('user_details_saved_successfully'));
      } else {
        $array = array('saved'=>false,'msg'=>printMsg('__Errors__', null, false).printMsg('__Warning__', null, false));
      }
      echo json_encode($array);
      return true;
    } else {
      $this->assign('user_data',   $this->__User->asarray());
    }
    return "user_update_win";
  }

  Function actionUseraddress (){
    $this->assign('title', true);
    $this->assign('user_data',   $this->__User->asarray());
    return "user_address";
  }

  /**
   * registerAction()
   *
   * @return String : SmartyPage
   */
  function actionRegister (){

    //if registerasmemeber field is not set, the user doenst want to be a member
    $type = is($_POST['ismember'],false);

    //Try and Register
    $user_id = $this->__User->register_f($type, $_POST, $errors, 0, 'user_nospam');
    //If errors return to user registration.
    if (!$user_id  ) {
      $this->assign('user_data',   $_POST);
      $this->assign('reg_type',    $type);
      return "user_register";
    } else {
      $this->assign('newuser_id', $user_id);
    }
    return "checkout_preview";
  }


  function actionIndex () {
    unset( $_SESSION['_SHOP_order']);
    return "checkout_preview";
  }

  function actionReserve ($origin='www',$user_id=null) {
    $myorder = Order::create(1, $origin, NULL, $user_id);
    if (!$myorder) {
      return "checkout_preview";
    } else {
      $this->setordervalues($myorder);
      $this->__MyCart->destroy_f();
      $this->assign('pm_return',array('approved'=>TRUE));
      return "checkout_result";
    }
  }

  function actionConfirm ( $origin="www", $user_id=0, $no_fee=0, $no_cost=0) {

    if ($this->secureType) {
      $myorder = Order::DecodeSecureCode($this->secureCode, $this->secureType);
      if(is_numeric($myorder)) {
        return addwarning("Confirm_error", ($myorder));
      }
    } elseif (!isset($_SESSION['_SHOP_order'])) {
      if (isset($_POST['handling_id'])) {
        $myorder = Order::create($_POST['handling_id'], $origin, $no_cost, $user_id, $no_fee, is($_POST['FreeTicketCode'], false));
      }
 	  } else {
      $myorder = Order::load($_SESSION['_SHOP_order'], true, true, true, true);
    }
    if (!$myorder) {
      $pm_return['response'] = "<tr><td></td><td><p class='notice'>".con('order_not_found_or_created')."</p></td></tr>";
      //$this->assign('pm_return',$pm_return);
     // unset( $_SESSION['_SHOP_order']);
      return "checkout_preview";
    } else {
      $this->__MyCart->destroy_f(); // destroy cart
      if(!$myorder->handling){
        $myorder->handling = Handling::load($myorder->order_handling_id);
      }
    	$hand = $myorder->handling; // get the payment handling object
      $confirmtext = $hand->on_confirm($myorder); // get the payment button/method...
      $this->setordervalues($myorder); //assign order vars

   //    ShopDB::commit('UnLock Created Order'); // Dont commit within the processes  will be done at the end.
      if (is_array($confirmtext)) {
    	 $this->assign('pm_return',$confirmtext);
        if(!$confirmtext['approved']) {
          $myorder->delete($myorder->order_id,'payment_not_approved' );
        }
     		unset( $_SESSION['_SHOP_order']);
      	return "checkout_result";
      } else {
 			  if ($hand->is_eph()) {
    		  $_SESSION['_SHOP_order'] = $myorder->order_id;
   			}
    		$this->__Order->obj = $myorder;
      	$this->assign('confirmtext', $confirmtext);
   			return "checkout_confirm";
      }
    }
  }

  function actionSubmit () {
    $myorder = Order::DecodeSecureCode($this->secureCode, $this->secureType);
    if(is_numeric($myorder)) {
      ShopDB::dblogging("submit error ($myorder).");
      return;
    }
    $hand= $myorder->handling;
    $pm_return = $hand->on_submit($myorder);
    $this->setordervalues($myorder);
    if (empty($pm_return)) {
      return false;
    } elseif (is_string($pm_return)) {
      $this->__Order->obj = $myorder;
      $this->smarty->assign('confirmtext', $pm_return);
      return "checkout_confirm";
    } else  {
      $this->smarty->assign('pm_return', $pm_return);
      if(!$pm_return['approved']){
       	Order::delete($myorder->order_id,'payment_not_approved' );
      }
      unset( $_SESSION['_SHOP_order']);
      return "checkout_result";
    }
  }

  function actionView(){
    $myorder = Order::DecodeSecureCode($this->secureCode, $this->secureType);
    if(is_numeric($myorder)) {
      ShopDB::dblogging("View error ($myorder).");echo 'here';
      return;
    }
    if ($myorder->order_id == $_GET['id']) {
      return "personal_order";
    } else echo 'there';
  }

  function actionorder_res(){
    $myorder = Order::DecodeSecureCode($this->secureCode, $this->secureType);
    if(is_numeric($myorder)) {
      ShopDB::dblogging("View error ($myorder).");
      return;
    }
    if (empty($_POST['handling'])) {
      addwarning('shopping_cart_check_out');
    }elseif ($myorder->order_id == $_POST['id']) {
      Order::reserve_to_order($myorder->order_id,$_POST['handling'],false,false,'www');
    }
    return "personal_order";
  }

  function actionPrint () {
    global $_SHOP;

    $myorder = Order::DecodeSecureCode($this->secureCode, $this->secureType);
    if(is_numeric($myorder)) {
      ShopDB::dblogging("Print error ($myorder).");
      return;
    }
    $mode = (int)$_REQUEST['mode'];
    if (!$mode) $mode =2;
    $print = (is($_SHOP->admin->user_prefs_print, false) === 'pdt');
   // die (is($_SHOP->admin->user_prefs_print, false));
    Order::printOrder($myorder->order_id, '', 'stream', $print, $mode );
    return;
  }

  function actionAccept () {
    $myorder = Order::DecodeSecureCode($this->secureCode, $this->secureType);
    if(is_numeric($myorder)) {
    	ShopDB::dblogging("Accept error ($myorder).");
      return addwarning("checkout_accept_error", ($myorder));
    }

    $hand=$myorder->handling;

    $pm_return = $hand->on_return($myorder, true);
    $this->setordervalues($myorder);
    If (!$pm_return['approved']) {
       Order::delete($myorder->order_id,'payment_not_approved' );
       $pm_return['response'] .= "<tr><td colspan='2'><p class='notice'>".con('orderdeleted')."</p></td></tr>";

    }
    $this->assign('pm_return',$pm_return);
    unset( $_SESSION['_SHOP_order']);
    return "checkout_result";
  }

  function actionPosCancel () {
 		$this->__MyCart->destroy_f(); // destroy cart
    $myorder = Order::DecodeSecureCode($this->secureCode, $this->secureType);
    if ($myorder) {
       Order::delete($myorder->order_id,'pos_manual_cancelled' );
    }
    return true;
  }

  function actionCancel () {
    $myorder = Order::DecodeSecureCode($this->secureCode, $this->secureType);
    if(is_numeric($myorder) || !is_a($myorder,'order')) {
    	if(is_object($myorder)) {$myorder = '= class '.get_class($myorder);}
      return addwarning("checkout_cancel_error", ($myorder));
    }
    $hand=$myorder->handling;
    $pm_return = $hand->on_return($myorder, false );
    Order::delete($myorder->order_id,'order_cancelled_will_paying' );
    $this->setordervalues($myorder);
    $pm_return['response'] .= "<tr><td colspan='2'><p class='notice'>".con('orderdeleted')."</p></td></tr>";
    $this->assign('pm_return',$pm_return);
    unset( $_SESSION['_SHOP_order']);
    return "checkout_result";
  }

  function actionNotify () {
    $myorder = Order::DecodeSecureCode($this->secureCode, $this->secureType);
      if(is_numeric($myorder)) {
		   		header('HTTP/1.1 502 Action not allowed', true, 502);
		   		ShopDB::dblogging("accept error ($myorder)");
      ShopDB::dblogging("Post ".print_r($_POST,true));
      ShopDB::dblogging("Get ".print_r($_GET,true));
		   		return;
			}
			ShopDB::dblogging("notify message for order: $myorder->order_id.\n");

			$hand= $myorder->handling;
			$hand->on_notify($myorder);
  }

  function actionPayment (){
    $myorder = Order::DecodeSecureCode($this->secureCode,  $this->secureType);
    if(is_numeric($myorder)) {
      return addwarning("Payment_error", ($myorder));
    }
    return $this->_payment($myorder);
  }
  /**
   * Checkout::paymentAction()
   *
   * For the recheckout methods with show just the payment method that you would see from
   * just checking out.
   *
   * @param object $order
   * @return boolean
   */
  protected function _payment($orderInput){

    if(!$orderInput){
      addWarning('invalid_order');
      return false;
    }
    if(is_numeric($orderInput)){
      $orderInput = Order::loadExt($orderInput, true);
      if(!is_object($orderInput)){ addWarning('invalid_order'); return false;}
    }

    $this->setordervalues($orderInput); //assign order vars
    $hand = $orderInput->handling; // get the payment handling object
    $confirmtext = $hand->on_confirm($orderInput); // get the payment button/method...

    if (is_array($confirmtext)) {
      $this->assign('pm_return',$confirmtext);
      if(!$confirmtext['approved']) {
        $orderInput->delete($orderInput->order_id,'payment_not_approved' );
      }
  		unset( $_SESSION['_SHOP_order']);
      return "checkout_result";
    } else {
      if ($hand->is_eph()) {
        $_SESSION['_SHOP_order'] = $orderInput->order_id;
 			}
      $this->assign('confirmtext', $confirmtext);
   		return "checkout_confirm";
    }
  }


	private function getsecurecode($type='sor') {
    if (isset($_REQUEST['sor'])) {
      $this->secureType = 'sor';
    }elseif( isset($_REQUEST['cbr'])) {
      $this->secureType = 'cbr';
    } else return false;
		if (isset($_POST[$this->secureType])) {
     		$this->secureCode = urldecode( $_POST[$this->secureType]);
	 	} elseif (isset($_GET[$this->secureType])) {
     	$this->secureCode = $_GET[$this->secureType];
/*    } elseif (strlen( $_SERVER["PATH_INFO"])>1) {
     	$return = substr($action, 1);
    } else {
      echo "getsecurecode: <pre>";
     	print_r($_REQUEST); Print_r($_SERVER);
      echo "</pre>";
     	$return =''; */
    }
    //  echo $return;
    	return true;
  }

  /**
	 * @name SetOrderValues
	 *
	 * Used to set the order values using the smarty assign methods, which can then be used
	 * by the plugable payments.
	 *
	 * @author Niels
	 * @since 1.0
	 * @uses Smarty, Smarty_Order
	 * @param aorder : Order Object [required]
	 * @return null loads the values to smarty vars
	 */
  function setordervalues($aOrder){
    $this->__Order->obj = $aOrder;

    if (!is_a  ( $aOrder, 'Order')) return;
    if (is_array($aOrder->places)) {
      foreach($aOrder->places as $ticket){
    		$seats[$ticket->id]=TRUE;
      }
    } /* else {
      echo "<pre>";

      //print_r($aOrder);
      echo "</pre>";
    } */
    $this->smarty->assign('order_success',true);
    $this->smarty->assign('order_id',$aOrder->order_id);
    $this->smarty->assign('order_fee',$aOrder->order_fee);
    $this->smarty->assign('order_total_price',$aOrder->order_total_price);
    $this->smarty->assign('order_partial_price',$aOrder->order_partial_price);
    $this->smarty->assign('order_discount_price',$aOrder->order_discount_price);
    $this->smarty->assign('order_tickets_nr',$aOrder->size());
    $this->smarty->assign('order_shipment_mode',$aOrder->order_shipment_mode);
    $this->smarty->assign('order_payment_mode',$aOrder->order_payment_mode);
    $this->smarty->assign('order_description',$aOrder->order_description());

    $this->smarty->assign('shop_handling', (array)$aOrder->handling);
    $this->smarty->assign('shop_order', (array)$aOrder);

    $this->smarty->assign('order_seats_id',$seats);
  }
}
//session_write_close();
?>