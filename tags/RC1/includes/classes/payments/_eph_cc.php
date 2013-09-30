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
class EPH_cc extends payment{
  public $extras = array('pm_cc_pubkey');

 	function admin_view (){
    return "{gui->view name='pm_cc_pubkey'}";
	}

  function admin_form (){
    return "{gui->area name='pm_cc_pubkey'}";
	}

	function admin_init (){
    global $_SHOP;

		$form1=
      "
      <div class='cc_div'>
      To validate your order please introduce your payment information and
      click on 'Pay'. <br> At once that your payment is completed, you receive an email
      with more information about your tickets.
      </div>
      <br><br>
      ";
    $this->handling_html_template .= $form1;

		$this->handling_text_payment = 'Credit Card';
		$this->handling_text_payment_alt = 'Credit Card';
	}


  function on_confirm( $order) {
    global $_SHOP;
    if (!isset($_POST['cc_name'])) {
      $_POST['cc_name'] = "{$order->user_firstname} {$order->user_lastname}";
    }
		$order_id= $order->order_id;
  return "{gui->StartForm  width='100%' id='payment-confirm-form' action='{$_SHOP->root_secured}checkout.php' method='POST' onsubmit='this.submit.disabled=true;return true;'}
            <input type='hidden' name='action' value='submit'>
            <input type='hidden' name='sor' value='{$order->EncodeSecureCode('')}'>
            <input type='hidden' name='order_id' value='{$order_id}'>
            {gui->input name='cc_name'}
            {gui->input name='cc_address' }
            {gui->input name='cc_zip' size=10 maxlength=10}
            {gui->input name='cc_number'}
            {gui->inputdate type='MY' name='cc_exp' range=10}
            {gui->EndForm title=!pay! noreset=true}";
  }

  function on_submit(&$order, &$err){
		$date = getdate();
//		if($_POST['cc_exp_y']<($date['year']-2000) or
//		($_POST['cc_exp_y']==($date['year']-2000) and $_POST['cc_exp_m']<$date['mon'])){
//			$err['cc_exp']= con('invalid_date');
//		}

//verify by mod10 formula
		if(empty($_POST['cc_number']) or !$this->ccval($_POST['cc_number'])){
			$err['cc_number']= con('invalid_number');
		}
//verify...

/*		if(strlen(trim($cc_name))==0){
			$err['cc_name']=1;
		}
	*/


		if(!empty($err)){
			return $this->on_confirm($order);
		}
    print_r($order);

    $order_id=$order->order_id;
		$order_total_price=$order->order_total_price;
		$cc_pubkey=$this->pm_cc_pubkey;

		$currency = $order->organizer_currency;

		$cc_name   = $_POST['cc_name'];
		$cc_number = $_POST['cc_number'];
		$cc_month  = $_POST['cc_exp_m'];
		$cc_year   = $_POST['cc_exp_y'];
		$cc_street = $_POST['cc_street'];
		$cc_zip    = $_POST['cc_zip'];

//store

		$cc_info = '"'.$order_id.'","'.
          			$order_total_price.'","'.
          			$currency.'","'.
          			$cc_name.'","'.
          			$cc_number.'","'.
          			$cc_month.'","'.
          			$cc_year.'","'.
			          $cc_street.'","'.
		           	$cc_zip.'"';



		if($cinfo = $this->ssl_crypt($cc_info,$cc_pubkey)){
			if($this->_store($order_id, $cinfo)){
				return array('approved'=>TRUE);
			}
			return array('approved'=>FALSE,'response'=>con('cannot_store'));
		}

		return array('approved'=>FALSE,'response'=>con('cannot_seal'));
	}


	function on_handle($order,$new_status,$old_status,$field){
		global $_SHOP;

		if($order->order_id){
			if($field=='order_payment_status' and $new_status=='paid'){
				$query="DELETE from CC_Info where cc_info_order_id='{$order->order_id}'";
				ShopDB::query($query);
			}
		}
	}

	function on_order_delete($order_id){
		global $_SHOP;

		if($order_id){
				$query="DELETE from CC_Info where cc_info_order_id='$order_id'";
				ShopDB::query($query);
		}
	}

	private function _store($order_id,$cinfo){
		global $_SHOP;

		//echo "_store($order_id,$sealed64,$ekey64)";

		$query="insert into CC_Info set cc_info_order_id='$order_id',	cc_info_data='$cinfo'";
		return ShopDB::query($query);
	}

	private function ssl_crypt($data,$key){
		global $_SHOP;

		if(strlen($data)==0){
		  user_error('empty data');
			return FALSE;
		}

		if($_SHOP->crypt_mode=='seal'){
		  return $this->_seal($data,$key);
		}else{
			return $this->_crypt($data,$key);
		}
	}

	private function ssl_decrypt($data,$key,$pwd=''){
		global $_SHOP;

		if(strlen($data)==0){
		  user_error('empty data');
			return FALSE;
		}

		if($_SHOP->crypt_mode=='seal'){
		  return $this->_open($data,$key,$pwd);
		}else{
			return $this->_decrypt($data,$key,$pwd);
		}
	}

	private function _openssl_error(){
	  while($err=openssl_error_string()){
		  user_error($err);
		}
		return FALSE;
	}

	private function _str_split($string,$length=1){
		$parts = array();
		while ($string) {
			array_push($parts, substr($string,0,$length) );
			$string = substr($string,$length);
		}
		return $parts;
	}


	private function _crypt($info,$key){
		if($pk = openssl_get_publickey($key)){

			$parts=$this->_str_split($info,53);

			foreach($parts as $part){
				if(!openssl_public_encrypt($part, $sealed, $pk)){
					return $this->_openssl_error();
				}
				$crypts[]=base64_encode($sealed);
			}

			openssl_free_key($pk);

			return implode(',',$crypts);
		}
		return $this->_openssl_error();
	}

	private function _decrypt($cinfo,$pkey,$pwd){
		if($pk = openssl_get_privatekey($pkey,$pwd)){

			$crypts=explode(',',$cinfo);

			foreach($crypts as $crypt){
				if(!openssl_private_decrypt(base64_decode($crypt), $i, $pk)){
					return $this->_openssl_error();
				}
				$info.=$i;
			}

			openssl_free_key($pk);

			return $info;
		}

		return _openssl_error();
	}


	private function _seal($info,$key){
		if($pk = openssl_get_publickey($key)){



			if(!$sealres=openssl_seal($info, $sealed, $ekeys, array($pk))){
			  return $this->_openssl_error();
			}

			openssl_free_key($pk);

			return base64_encode($sealed).",".base64_encode($ekeys[0]);
		}

		return _openssl_error();
	}

	private function _open($cinfo_ekey,$pkey,$pwd){
		if($pk = openssl_get_privatekey($pkey,$pwd)){

			list($cinfo,$ekey)=explode(',',$cinfo_ekey);
			$cinfo=base64_decode($cinfo);
			$ekey=base64_decode($ekey);

			if(!$sealres=openssl_open($cinfo, $info, $ekey, $pk)){
			  return $this->_openssl_error();
			}

			openssl_free_key($pk);

			return $info;
		}

		return _openssl_error();
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