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

require_once('classes/class.payment.php');

class EPH_netpay extends payment{
  public $extras = array('pm_netpaypal_id', 'pm_netpaypal_secure');
  public $mandatory = array('pm_netpaypal_id');


	function admin_view (){
    return "{gui->view name='pm_netpaypal_id'}".
           "{gui->view name='pm_netpaypal_secure'}";
	}

  function admin_form ($view, &$data){
    $view->print_input('pm_netpaypal_id', $data ,$dummy);
    $view->print_input('pm_netpaypal_secure', $data ,$dummy);
    return "";
	}

	function admin_init (){
  		$this->handling_text_payment    = "NetPay";
    	$this->handling_text_payment_alt= "NetPay";
    	$this->handling_html_template  .= "";
//		$this->pm_paypal_test  = true;
	}

	function on_confirm(&$order) {
    global $_SHOP;
    $pm_paypal_url= 'https://services.netpay-intl.com/hosted//';

    $secure = $this->pm_netpaypal_id . sprintf("%01.2F", ($order->order_total_price)) . $_SHOP->organizer_currency .  $this->pm_netpaypal_secure; //$order->order_id .
		$secure = base64_encode(hash("sha256", $secure, true));

    return "
      <form name='netPay' action='{$pm_paypal_url}' method='post' onsubmit='this.submit.disabled=true;return true;'>
        <input type='hidden' name='merchantID' value='{$this->pm_netpaypal_id}'>
        <input type='hidden' name='trans_amount' value='".sprintf("%01.2F", ($order->order_total_price))."'>
        <input type='hidden' name='trans_currency' value='{$_SHOP->organizer_currency}'>
        <input type='hidden' name='url_redirect' value='".$_SHOP->root_secured. 'checkout_accept.php?'.$order->EncodeSecureCode()."'>
        <input type='hidden' name='url_notify' value='".$_SHOP->root_secured. 'checkout_notify.php?'.$order->EncodeSecureCode()."&setlang={$_SHOP->lang}'>

        <input type='hidden' name='disp_payFor' value='".$order->order_description()."'>
        <input type='hidden' name='trans_comment' value='".$order->order_description()."'>

		    <input type=\"hidden\" name=\"disp_paymentType\" value=\"CC,EC,WT,ID,DD,OB,WM,CUP\" />
        <input type='hidden' name='trans_type' value='0'>
        <input type='hidden' name='trans_installments' value='1'>
		    <input type='hidden' name='disp_lng' value='' />
		    <input type='hidden' name='disp_mobile' value='auto' />
        <input type='hidden' name='trans_refNum' value='{$order->order_id}'>
        <input type='hidden' name='client_fullName' value='{$order->user_firstname} {$order->user_lastname}'>
        <input type='hidden' name='client_email' value='{$order->user_email}'>
        <input type='hidden' name='client_phoneNum' value='{$order->user_phone}'>
        <input type='hidden' name='signature' value='".$secure."'>
        <div align='right'>
        <input type='submit' value='{!pay!}' name='submit2' alt='{!netpay_pay!}' >
        </div>
      </form>";
		/*
		   <form method="post" action="https://services.netpay-intl.com/hosted//">
		    <input type="hidden" name="disp_lng" value="" />
		    <input type="hidden" name="disp_mobile" value="auto" />
		    <input type="hidden" name="signature" value="yB4k37y7MCYM4ftTXIclx6BBZjBehFqcA6IyB8158tc=" />
		    <input type="submit" value="Send">
		  </form>
		   */
      // <input type='hidden' name='item_number' value='{$order->order_id}'>
  }

  function on_return(&$order, $result){
    If ((int)$_REQUEST['replyCode'] <= 1) {
      if ($_REQUEST['trans_id']) {
        Order::set_payment_id($order->order_id,'netpay:'.$_REQUEST['trans_id']);
      }
      $order->set_payment_status('pending');
      return array('approved'=>true,
                   'transaction_id'=>$_REQUEST['trans_id'],
                   'response'=> '');
    } else {
      return array('approved'=>false,
                   'transaction_id'=>false,
                   'response'=> '['.$_REQUEST['replyCode'].'] '.$_REQUEST['replyDesc']);
    }
  }

  function on_notify(&$order){
    global $_SHOP;

		$url= 'https://process.netpay-intl.com/member/verify_trans.asp';

    if (!isset($_REQUEST['trans_refNum']) or !is_numeric($_REQUEST['trans_refNum']) or ($_REQUEST['trans_refNum']<>$order->order_id)) {
      ShopDB::dblogging("Notification error, order_id mismatch: \n". print_r($_REQUEST, true));
      return;
    }
    $debug  = "date: ".date('r')."\n";
    $debug .= "url: $url\n";

    $order_id    = $order->order_id;
    $order_total = $order->order_total_price;

    $debug .= "Order_id : $order_id\n";
    $debug .= "Amount   : $order_total\n";

  	$currecies = array(
	   'ILS' => 0,
	   'USD' => 1,
	   'EUR' => 2,
	   'GBP' => 3,
	   'AUD' => 4,
	   'CAD' => 5,
	   'JPY' => 6,
	   'NOK' => 7,
	   'PLN' => 8,
	   'MXN' => 9,
  	 'ZAR' =>10,
  	 'RUB' =>11,
  	 'TRY' =>12,
  	 'CHF' =>13,
		 'INR' =>14,
		 'DKK' =>15,
		 'SEK' =>16,
		 'CNY' =>17,
		 'HUF' =>18,
		 'NZD' =>19,
		 'HKD' =>20,
		 'KRW' =>21,
		 'SGD' =>22);
//    $_POST["cmd"]="_notify-validate";

    $arr = array ('CompanyNum'=>$this->pm_netpaypal_id, 'TransID'=>$_REQUEST['trans_id'],'TransDate'=>date('d/m/Y H:i:s',strtotime(substr($_REQUEST['trans_date'],0,-1))),'TransType'=>'0',
                  'TransAmount'=>$_REQUEST['trans_amount'],'TransCurrency'=>$currecies[$_REQUEST['trans_currency']]);
    $result=$this->url_post($url,$arr);

   //
  	$debug .= "res : $result\n";
    $return = false;

   if(stristr($result,"00")===false) {
   	    $errors=array(
					   	'00' => 'Transaction exists',
					   	'10' => 'Not enough data or invalid data',
					   	'11' => 'Merchant does not exist',
					   	'12' => 'Transaction number does not exist or is not associated with merchant',
					   	'13' => 'Transaction date does not match',
					   	'14' => 'Transaction amount does not match',
					   	'15' => 'Transaction currency type does not match');
   	$debugx="NOT OK: ({$result}) {$errors[$result]} \n";
   } elseif((strtolower($_REQUEST["merchantID"]) != strtolower($this->pm_netpaypal_id))) {
        $debugx="wrong merchantID\n";
    } elseif($_REQUEST["trans_amount"]<$order_total) {
        $debugx="Invalid payment\n";
    } elseif($_REQUEST["replyCode"] != "000") {
        $debugx='Payment status:'.$_REQUEST["replyCode"].': '.$_REQUEST["replyDesc"]."\n";
        $order->set_payment_status('pending');
    } else {
        $debugx="OK \n";
        $return =true;
      	$order->order_payment_id='netpay:'.$_REQUEST['trans_id'];
  	    Order::set_payment_id($order->order_id,'netpay:'.$_REQUEST['trans_id']) ;
        $order->set_payment_status('paid');
    }
    $debug .= $debugx;

  	if (!$return) {
  		$debug .= print_r($arr,true);
  		$debug .= print_r($this->debug,true);
      $debug .= print_r($_REQUEST,true);
  	}

    OrderStatus::statusChange($order_id,'netpay',$debugx,'checkout::notify',$debug);
//    $handle=fopen($_SHOP->tmp_dir."netpay.log","a");
 //   fwrite($handle,$debug);
  //  fclose($handle);
    return $return;
  }
}
?>