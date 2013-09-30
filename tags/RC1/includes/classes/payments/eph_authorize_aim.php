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
class EPH_authorize_aim Extends Payment{
  public $extras = array ('pm_authorize_aim_login', 'pm_authorize_aim_txnkey',// 'pm_authorize_aim_hash',
                          'pm_authorize_aim_test');
  public $mandatory = array ('pm_authorize_aim_login', 'pm_authorize_aim_txnkey');
                           //  'pm_authorize_aim_hash'

	function admin_view (){
	  return "{gui->view name='pm_authorize_aim_login'}".
	         "{gui->view name='pm_authorize_aim_txnkey'}".
//	         "{gui->view name='pm_authorize_aim_hash'}".
	         "{gui->view name='pm_authorize_aim_test'}";
	}

  function admin_form (){
		//$docs=array('pm_authorize_aim_site'=>'<a class="link" href="https://www.authorize_aim.com/" target="_blank">PayPal</a>');
    return  "{gui->input name='pm_authorize_aim_login'}".
        		"{gui->input name='pm_authorize_aim_txnkey'}".
  //      		"{gui->input name='pm_authorize_aim_hash'}".
            "{gui->checkbox name='pm_authorize_aim_test'}";
//    $this->print_field('pm_yp_docs',$docs);
	}

	function admin_init (){
		$form1= '
            <div class="cc_div">
              To validate your order please enter your payment information and
              click on "Pay". <br> Once payment is completed, you will receive
              your tickets.
             </div>';


    $this->handling_html_template   .= $form1;
		$this->handling_text_payment     = 'Credit Card';
		$this->handling_text_payment_alt = 'Credit Card';
		$this->pm_authorize_aim_test     = TRUE;
	}


  function on_confirm(&$order ) {
    Global $_SHOP;
    if (!isset($_POST['cc_name'])) {
      $_POST['cc_name'] = "{$order->user_firstname} {$order->user_lastname}";
    }
		$order_id=$order->order_id;
    return "{gui->StartForm  width='100%' name='authorize_aim-form' id='payment-confirm-form' action='{$_SHOP->root_secured}checkout.php' method='POST' onsubmit='this.submit.disabled=true;return true;'}
            <input type='hidden' name='action' value='submit'>
            <input type='hidden' name='sor' value='{$order->EncodeSecureCode('')}'>

            {gui->input name='cc_name'}
            {gui->input name='cc_number'}
            {gui->inputdate type='mm-y' name=cc_exp range=10}
            {gui->input name='cc_code' size='4' lenght='4'}
            {gui->EndForm title=!pay! noreset=true}
            ";
  }

  function on_submit(&$order){
		global $_SHOP;
		$date = getdate();
    list($cc_exp_m, $cc_exp_y) = explode('-',$_POST['cc_exp']);
		if($cc_exp_y < ($date['year']-2000) or ($cc_exp_y==($date['year']-2000) and $cc_exp_m<$date['mon'])){
			addError('cc_exp', 'invalid_date');
		}
    $_POST['cc_exp'] = str_replace('-','' ,$_POST['cc_exp']);

//verify by mod10 formula
		if(empty($_POST['cc_number']) or !$this->ccval($_POST['cc_number'],'xx',$_POST['cc_exp'])){
			addError('cc_number', 'invalid_number');
		}
//verify...

/*		if(strlen(trim($cc_name))==0){
			$err['cc_name']=1;
		}
	*/


		if(hasErrors()){
			return $this->on_confirm($order);
		}
		if($this->pm_authorize_aim_test){
			$url="https://test.authorize.net/gateway/transact.dll";
		}else{
			$url="https://secure.authorize.net/gateway/transact.dll";
		}

		$order_id   =$order->order_id;
		$order_total=$order->order_total_price;

    $post['x_login']   = $this->pm_authorize_aim_login;
    $post['x_tran_key']= $this->pm_authorize_aim_txnkey;
    $post["x_type"]    = "AUTH_CAPTURE";

		if($this->pm_authorize_aim_test){
		  $post['x_test_request']='TRUE';
		}

		$post["x_version"]    = "3.1";
		$post["x_delim_data"] = "TRUE";
		$post["x_delim_char"] = "|";
    		$post['x_encap_char']='';
		$post["x_relay_response"]	= "FALSE";

		$post['x_invoice_num']=$order_id;
    $post['x_description']=$order->order_description();

		$post['x_amount'] = $order_total;
    $post['x_currency_code'] = $_SHOP->organizer_currency;

		$post['x_card_num']  = $_POST['cc_number'];
		$post['x_exp_date']  = $_POST['cc_exp'];
		$post['x_card_code'] = $_POST['cc_code'];

		$post['x_cust_id'] = $order->user_id;
		$post['x_first_name'] = $order->user_firstname;
//		$post['x_last_name'] = $order->user_lastname;
//		$post['x_address'] = $order->user_address.' '.$order->user_address1;
//		$post['x_city'] = $order->user_city;
//		$post['x_zip'] = $order->user_zip;
//    $post['x_state'] = $order->user_state;
		$post['x_country'] = $order->user_country;
		$post['x_phone'] = $order->user_phone;
		$post['x_fax'] = $order->user_fax;
		$post['x_email'] = $order->user_email;

		$post['x_customer_ip'] = $_SERVER['REMOTE_ADDR'];

		//echo "<pre>";		print_r($post); echo "</pre>";
    $debug ="date: ".date('r')."\n";
    $debug .="url: $url\n";

    $debug.="Order_id : $order_id\n";
    $debug.="Amount   : $order_total\n";

		$result =$this->url_post($url,$post);

    $len= strlen($post['x_card_num']);
    $post['x_card_num'] = str_pad('',$len-4, 'x' )+ substr($post['x_card_num'],-4 );
    unset($post['x_card_code']);

    $debug .= print_r($post, true);
    $debug .= $result ."\n";
		if(!empty($result)){
			$res=explode("|",$result );

			$response_code              =$res[0];
			$transaction_id             =$res[6];
			$order_id                   =$res[7];
			$md5_hash                   =$res[37];

			$return['response']         = "{$res[3]}. ";
			$return['transaction_id']   = $transaction_id ;
  	  $return['approved']         = false;

			if($response_code==1){
				if($order->order_id == $order_id){
					if($this->_check_order($order, $res)){
						$order->order_payment_id=$transaction_id;
      	    Order::set_payment_id($order->order_id,'auth_aim:'.$transaction_id);
						$order->set_payment_status('paid');
						$return['approved']=TRUE;
					}else{
						$return['response'].="Payment Error: Order $order_id check failed!";
					}
				}else{
				  $return['response'].="Payment Error: Order $order_id not found!";
				}
			} else
         $return['response'] ="( {$res[0]} / {$res[1]} / {$res[2]}  ) " .  $return['response'];
		}else{
			$return['response'].="Payment Error: Order $order_id can't be valided, no responce from Authorize.net!";
    }
    OrderStatus::statusChange($order_id,'authorize_aim',$return['response'],'checkout::notify',$debug.print_r($_POST,true));
		return $return ;
	}

	private function _check_order(&$order, &$res){
	  $check=TRUE;

		//echo "{$order->order_total_price}=={$res[9]} ";

		//commented because it is not clear how the x_amount is formatted
	  //for some currency 1.00 or 1,00, 1'000.00 or 1000.00?
		//$check = ($check and ($order->order_total_price==$res[9]));

		//echo "<br>{$order->order_user_id}=={$res[12]} ";

		$check = ($check and ($order->order_user_id==$res[12]));

		//echo "<br>";

		if($h_val = $this->pm_authorize_aim_hash){

			$md5 = strtoupper(md5($h_val.$this->pm_authorize_aim_login.$res[6].$res[9]));

			//echo "$h_val:{$this->pm_authorize_aim_login}:{$res[6]}:{$res[9]}<br>";
			//echo strtoupper($res[37])."<br>";
			//echo $md5;

			$check = ($check and (strtoupper($res[37])==$md5));
		}

		return $check;
	}
  private function CCVal($Num, $Name = "n/a", $Exp = "") {

//  Check the expiration date first
    if (strlen($Exp)) {
      $Month = substr($Exp, 0, 2);
      $Year  = substr($Exp, -2);

      $WorkDate = "$Month/01/$Year";
      $WorkDate = strtotime($WorkDate);
      $LastDay  = date("t", $WorkDate);

      $Expires  = strtotime("$Month/$LastDay/$Year 11:59:59");
      if ($Expires < time()) return 0;
    }

//  Innocent until proven guilty
    $GoodCard = true;

//  Get rid of any non-digits
    $Num = ereg_replace("[^0-9]", "", $Num);

//  Perform card-specific checks, if applicable
    switch ($Name) {

    case "mcd" :
      $GoodCard = ereg("^5[1-5].{14}$", $Num);
      break;

    case "vis" :
      $GoodCard = ereg("^4.{15}$|^4.{12}$", $Num);
      break;

    case "amx" :
      $GoodCard = ereg("^3[47].{13}$", $Num);
      break;

    case "dsc" :
      $GoodCard = ereg("^6011.{12}$", $Num);
      break;

    case "dnc" :
      $GoodCard = ereg("^30[0-5].{11}$|^3[68].{12}$", $Num);
      break;

    case "jcb" :
      $GoodCard = ereg("^3.{15}$|^2131|1800.{11}$", $Num);
      break;

    case "dlt" :
      $GoodCard = ereg("^4.{15}$", $Num);
      break;

    case "swi" :
      $GoodCard = ereg("^[456].{15}$|^[456].{17,18}$", $Num);
      break;

    case "enr" :
      $GoodCard = ereg("^2014.{11}$|^2149.{11}$", $Num);
      break;
    }

//  The Luhn formula works right to left, so reverse the number.
    $Num = strrev($Num);

    $Total = 0;

    for ($x=0; $x<strlen($Num); $x++) {
      $digit = substr($Num,$x,1);

//    If it's an odd digit, double it
      if ($x/2 != floor($x/2)) {
        $digit *= 2;

//    If the result is two digits, add them
        if (strlen($digit) == 2)
          $digit = substr($digit,0,1) + substr($digit,1,1);
      }

//    Add the current digit, doubled and added if applicable, to the Total
      $Total += $digit;
    }

//  If it passed (or bypassed) the card-specific check and the Total is
//  evenly divisible by 10, it's cool!
    if ($GoodCard && $Total % 10 == 0) return true; else return false;
  }
}
?>