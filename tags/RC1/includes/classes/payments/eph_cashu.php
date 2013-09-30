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
//if (!defined('ft_check')) {die('System intrusion ');}

require_once('classes'.DS.'class.payment.php');

//  error_reporting(E_ALL);

class EPH_cashu extends payment{
  public $extras = array('pm_cashu_accountid', 'pm_cashu_seckey','pm_cashu_test','pm_cashu_service_name');
  public $mandatory = array('pm_cashu_accountid', 'pm_cashu_seckey');


	function admin_view (){
    return "{gui->view name='pm_cashu_accountid'}".
           "{gui->view name='pm_cashu_seckey'}".
           "{gui->view name='pm_cashu_test'}";
	}

  function admin_form ($view,$data){
    global $_SHOP;
    return "{gui->input name='pm_cashu_accountid'}".
           "{gui->input name='pm_cashu_seckey'}".
           "{gui->input name='pm_cashu_service_name'}".
           "{gui->view name='pm_cashu_return_callback_link'  value='".dirname($_SHOP->root_secured)."/checkout_accept.php?".$this->encodeCallback()."'}".
           "{gui->view name='pm_cashu_notify_callback_link'  value='".dirname($_SHOP->root_secured)."/checkout_notify.php?".$this->encodeCallback()."'}".
           "{gui->view name='pm_cashu_reject_callback_link'  value='".dirname($_SHOP->root_secured)."/checkout_cancel.php?".$this->encodeCallback()."'}".
      "{gui->checkbox name='pm_cashu_test'}";
	}
  public function admin_check(&$data){
    global $_SHOP;
    return parent::admin_check($data);
  }

	function admin_init (){
    $this->handling_text_payment    = "cashu";
    $this->handling_text_payment_alt= "cashu";
    $this->handling_html_template  .= "";
	  $this->pm_cashu_service_name  = "";

    $this->pm_cashu_test  = true;
	}

	function on_confirm(&$order) {
    global $_SHOP;
    if (!$this->pm_cashu_test) {
      $ebs_mode= '0';
    } else {
      $ebs_mode= '1';
    }
	  $currency = $_SHOP->organizer_currency;
	  $token = strtolower($this->pm_cashu_accountid). ":" .sprintf("%01.2F",$order->order_total_price). ":" .strtolower($currency);
	  $token = md5($token. ":" .$this->pm_cashu_seckey);
	  //
    return "
      <form  method='post' action='https://www.cashu.com/cgi-bin/pcashu.cgi' name='frmTransaction' id='frmTransaction'>
        <input name='merchant_id' type='hidden' value='". strtolower($this->pm_cashu_accountid)."' />
        <input name='test_mode' type='hidden' value='{$ebs_mode}'  />
        <input name='reference_no' type='hidden' value='{$order->order_id}'  />
        <input name='amount' type='hidden' value='".sprintf("%01.2F",$order->order_total_price)."' />
        <input name='display_text' type='hidden' value='{$order->order_description()}'  />
        <input name='currency' type='hidden' value='".strtoupper($currency)."'  />
        <input name='language' type='hidden'  value='{$_SHOP->lang}' />
        ".($this->pm_cashu_service_name?"\n<input name='service_name' type='hidden' value='".$this->pm_cashu_service_name."' />\n":'').
       "<input name='txt1' type='hidden' value='".$order->EncodeSecureCode('')."' />
        <input name='session_id' type='hidden'  value='".session_id()."' />
        <input name='token' type='hidden' value='{$token}' />
        <div align='right'>
          <input type='submit' value='{!pay!}' name='submitted' alt='{!cachu_pay!}' >
        </div>
      </form>";
  }

  function on_return(&$order, $result){
    global $_SHOP;
    //die('getOrder');
  	if (!isset ($_GET['redirected'])) {
  		$_POST['errorCode'] = (int) is($_POST['errorCode'],0);
  		//var_dump($response);

  		if ($order->order_payment_status !=='paid') {
  			$currency = $_SHOP->organizer_currency;
  			$token = strtolower($this->pm_cashu_accountid). ":" .sprintf("%01.2F",$order->order_total_price). ":" .strtolower($currency);
  			$token = md5($token. ":" .$this->pm_cashu_seckey);
  			if ($token !== $_POST['token']) {
  				$_POST['errorCode']= -1 ;
  			}
  			$token = sha1(strtolower($this->pm_cashu_accountid).":".$_POST['trn_id'].":".$this->pm_cashu_seckey);
  			if ($token !== $_POST['verificationString']) {
  				$_POST['errorCode']= -2 ;
  			}
  		}
  		redirect($_SHOP->root.'checkout_accept.php?redirected='.session_id().'&'.$order->EncodeSecureCode().'&errorCode='.$_POST['errorCode'].'&trn_id='.$_POST['trn_id'], true);
  		die();
  	} else{ //if ($_GET['redirected']==session_id()) {
  //		var_dump($_GET);//	die();
	    if ($_GET['errorCode']==0 || $order->order_payment_status =='paid') {
	      return array('approved'=>true,
	                   'transaction_id'=>$_GET['trn_id'],
	                   'response'=> '');
	    } else {
	      $list = $this->getErrorlist();
	      return array('approved'=>false,
	                   'response'=> $_GET['errorCode'].': '. $list[$_GET['errorCode']]);
	    }
  	}
  }

  function on_notify($order) {
    global $_SHOP;
    $errorCode = 0;
    $xml = $this->GetXMLresult($_POST['sRequest']);
  	$currency = $_SHOP->organizer_currency;
    $token = strtolower($this->pm_cashu_accountid). ":" .sprintf("%01.2F",$order->order_total_price). ":" .strtolower($currency);
    $token = md5($token. ":" .$this->pm_cashu_seckey);
    if ($token !== $xml['token']) {
      $errorCode= -1 ;
    }
    $token = MD5(strtolower($this->pm_cashu_accountid).":".$xml['cashU_trnID'].":".$this->pm_cashu_seckey);
    if ($token !== $xml['cashUToken']) {
 //     $errorCode= -2 ;
    }
  	$return =false;
  	if ($errorCode==0) {
	    $data = array();
	    $data['sRequest'] = '<cashUTransaction>'.
	    	                  '<merchant_id>'.$this->pm_cashu_accountid.'</merchant_id>'.
	                        '<cashU_trnID>'.$xml['cashU_trnID'].'</cashU_trnID>'.
	                        '<cashUToken>'.$xml['cashUToken'].'</cashUToken>'.
	                        '<responseCode>'.$xml['responseCode'].'</responseCode>'.
	                        '<responseDate>'.$xml['trnDate'].'</responseDate>'.
	                        '</cashUTransaction>';
	    $url = 'https://www.cashu.com/cgi-bin/notification/MerchantFeedBack.cgi';
      $result=$this->url_post($url,$data);
  		$result=explode("\n", $result);
  	  if ($xml['responseCode']=='OK' && in_array('ok ', $result)) {
	      $return =true;
	      $order->order_payment_id='cashu:'.$xml['cashU_trnID'];
	      Order::set_payment_id($order->order_id,'cashu:'.$xml['cashU_trnID']) ;
	      $order->set_payment_status('paid');
	    }
		}
 //   ShopDB::dblogging("xml_values ".print_r($xml,true));
    ShopDB::dblogging("result: ".var_export(($result),true));
    $debug = print_r($xml, true). print_r($result, true);
  	if ($errorCode !=0) {
  		$debug .= 'errorcode ='.$errorCode;
  	}
    OrderStatus::statusChange($order->order_id,'cashu','','checkout::notify',$debug);
    return $return;
  }

  public function getOrder(){
    if (isset($_POST['sRequest'])) {
      $xml = $this->GetXMLresult($_POST['sRequest']);
      $_POST['txt1'] = $xml['txt1'];
    }
    return Order::DecodeSecureCode($_POST['txt1']);
  }

  private function GetXMLresult($request) {
    $parser = xml_parser_create('UTF-8');
    xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, "UTF-8"); # http://minutillo.com/steve/weblog/2004/6/17/php-xml-and-character-encodings-a-tale-of-sadness-rage-and-data-loss
    xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
    xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
    xml_parse_into_struct($parser, trim($request), $xml_values);
    xml_parser_free($parser);
    $result = array();
    foreach($xml_values as $key) {
      if ($key['level']==2) {
        $result[$key['tag']] = $key['value'];
      }
    }
    return $result;
  }

  public function encodeCallback(){
    $sha1 = $this->pm_cashu_accountid;
    $hash = sha1($sha1, true);

    return $this->encodeEPHCallback($hash);
  }

  public function decodeCallback($ephHash){
    $sha1 = $this->pm_cashu_accountid;
    if($ephHash <> sha1($sha1,true)){
      return false;
    }else{
      return true;
    }
  }
  private function getErrorlist() {
    return array(
      -1 =>'Invalid token retutned',
      -2 =>'invalid validation code returned',
      2 =>'Inactive Merchant ID.',
      4 =>'Inactive Payment Account.',
      6 =>'Insufficient funds.',
      7 =>' Incorrect Payment account details.',
      8 =>'Invalid account.',
      15 =>'Password for the Payment Account has expired.',
      17 =>'The transaction has not been completed.',
      20  =>'The Merchant has limited his sales to some countries; the purchase attempt is coming from a country that is not listed in the profile.',
      21 =>' The transaction value is more than the limit. This limitation is applied to Payment Accounts that not complied by KYC rules.',
      22 =>' The Merchant has limited his sales to only KYC complied Payment accounts; the purchase attempt is coming from a Payment account that is KYC complied.',
      23 =>' The transaction has been cancelled by the customer. If the user clicks on the “Cancel” button.'
    );
  }
}

?>