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

class EPH_maroc extends payment{
  public $extras = array('pm_maroc_SecretKey', 'pm_maroc_storeId', 'pm_maroc_url');
  public $mandatory = array('pm_maroc_SecretKey', 'pm_maroc_storeId');


	function admin_view (){
    return "{gui->view name='pm_maroc_SecretKey'}".
           "{gui->view name='pm_maroc_storeId'}";
	}

  function admin_form (){
    return "{gui->input name='pm_maroc_SecretKey'}".
      "{gui->input name='pm_maroc_storeId'}".
      "{gui->input name='pm_maroc_url'}";
	}

	function admin_init (){
  		$this->handling_text_payment    = "Maroc";
	  	$this->handling_text_payment_alt= "Maroc";
    	$this->handling_html_template  .= "";
	 		$this->pm_maroc_url  = 'http://demo.maroctelecommerce.com/test/gateway/pay.asp';
	}

	function on_confirm(&$order) {
    global $_SHOP;
    $pm_Maroc_url= $this->pm_maroc_url;
//    <input type='hidden' name='image_url' value='https://www.paypal.com/images/x-click-but23.gif'>
    $storeId = $this->pm_maroc_storeId;
	  $dataMD5=$pm_Maroc_url . $storeId . $order->id . sprintf("%01.2F",$order->order_total_price) . $order->user_email . $this->pm_maroc_SecretKey;

	  $checksum=MD5($this->utf8entities(rawurlencode($dataMD5)));

	  //        <input type='hidden' name='updateURL' value='http://www.sitemarchand.com/result.asp'>
	  //  <input type='hidden' name='totalAmountCur' value='".sprintf("%01.2F", ($order->order_total_price-$order->order_fee))."'>
	  //  <input type='hidden' name='symbolCur' value='{$_SHOP->organizer_currency}'>

    $_SESSION['maloc'.$order->id]['offer'] =  $_SHOP->root_secured. 'checkout_cancel.php?'.$order->EncodeSecureCode();
	  $_SESSION['maloc'.$order->id]['updatex'] =  $_SHOP->root_secured. 'checkout_accept.php';
	  $_SESSION['maloc'.$order->id]['update'] =  $_SHOP->root_secured. 'checkout_accept.php?'.$order->EncodeSecureCode();
	  $_SESSION['maloc'.$order->id]['book'] =  $_SHOP->root_secured. 'checkout_notify.php?'.$order->EncodeSecureCode();

    return "
      <form name='Maroc' action='{$pm_Maroc_url}' method='post' onsubmit='this.submit.disabled=true;return true;'>
        <input type='hidden' name='storeId' value='{$storeId}'>
        <input type='hidden' name='langue' value='FR'>
        <input type='hidden' name='offerURL' value='".$_SHOP->root_secured. 'checkout_cancel.php?'.$order->EncodeSecureCode()."'>
        <input type='hidden' name='updateURL' value='".$_SHOP->root_secured. 'checkout_accept.php?'.$order->EncodeSecureCode()."'>
        <input type='hidden' name='bookURL' value='".$_SHOP->root_secured. 'checkout_notify.php?'.$order->EncodeSecureCode()."&setlang={$_SHOP->lang}'>
        <input type='hidden' name='cartId' value='{$order->order_id}'>
        <input type='hidden' name='totalAmountTx' value=".sprintf("%01.2F", ($order->order_total_price)).">
        <input type='hidden' name='name' value="._esc($order->user_lastname.', '.$order->user_firstname).">
        <input type='hidden' name='address' value='"._esc($order->user_address).">
        <input type='hidden' name='city' value="._esc($order->user_city).">
        <input type='hidden' name='state' value="._esc($order->user_state).">
        <input type='hidden' name='country' value="._esc($order->user_country).">
        <input type='hidden' name='postCode' value="._esc($order->user_zip).">
        <input type='hidden' name='tel' value="._esc($order->user_phone).">
        <input type='hidden' name='email' value="._esc($order->user_email).">
        <input type='hidden' name='checksum' value='{$checksum}'>
        <div align='right'>
          <input type='submit' value='{!pay!}' name='submit2' alt='{!paypal_pay!}' >
        </div>
      </form><br>";
  }

  function on_return(&$order, $result){
    global $_SHOP;
    if (!$result) {
      $updateURL = $_SESSION['maloc'.$order->id]['offer'];
    } else {
      $updateURL = $_SESSION['maloc'.$order->id]['update'];

    }
    $storeId = $this->pm_maroc_storeId;
    $cartId=$_GET["cartId"];
    $checksumMTC=$_GET["checksum"];
    $totalAmountTx=$_GET["totalAmountTx"];
    //$email=<récupéré à partir de la BDD ou les variables de sessions du site marchand"
    //$storeId=<valeur qui a été renseignée par Maroc Telecommerce>
    if (!$result) {
      $updateURL = $_SESSION['maloc'.$order->id]['offer'];
    } else {
      $updateURL = $_SESSION['maloc'.$order->id]['update'];
    }
    $dataMD5=$updateURL . $storeId . $cartId . $totalAmountTx . $order->user_email . $this->pm_maroc_SecretKey;

    $checksum=MD5($this->utf8entities(rawurlencode($dataMD5)));

    $result = $result & ($order->order_payment_status <> 'canceled');
    $value = 'Status: '.$order->order_payment_status.'<br>';

    if ($checksum == $checksumMTC && $result) {
      //Afficher un message de confirmation
      $value .= "OK: Votre commande a bien été traitée.";
    } else {
      $value .= "Error: Echec de traitement de la demande !";
    }

    OrderStatus::statusChange($order->id,'maroc',$value,'checkout::return',$value);

    return array('approved'=>$result  ,
                 'transaction_id'=>$order->order_payment_id,
                 'response'=> $value);
  }

  function on_notify(&$order){
    global $_SHOP;
    $bookURL =$_SHOP->root_secured. 'checkout_notify.php?sor='.$_GET['sor']."&setlang={$_SHOP->lang}";
    $storeId = $this->pm_maroc_storeId;
    $cartId=$_POST["cartId"];
    $email=$_POST["email"];
    $totalAmountTx=$_POST["totalAmountTx"];
    $checksumMTC=$_POST["checksum"];
    $orderNumber=$_POST["orderNumber"];

    $debug= $dataMD5=$bookURL . $storeId . $cartId . $totalAmountTx . $order->user_email . $this->pm_maroc_SecretKey;
    $debug= "\n";
    $checksum=MD5($this->utf8entities(rawurlencode($dataMD5)));

    if ($checksum == $checksumMTC && is_numeric($orderNumber) == "True" && $cartId === $order->id) {
      //  "Mettre à jour la base de données du site marchand en vérifiant si la commande existe et correspond au retour MTC!"
      //  "Dans cette MAJ, il faut enregistrer le n° du Bon de commande de paiement envoyé dans le paramètre ""orderNumber"" "
      echo "1;" . $cartId . ";".date('Ymd').";1";
      $order->order_payment_id='maroc:'.$_POST['pmtSeqId'].':'.$_POST['approvalCode'].':'.$_POST['orderNumber'];
      Order::set_payment_id($order->order_id,'maroc:'.$_POST['pmtSeqId'].':'.$_POST['approvalCode'].':'.$_POST['orderNumber']) ;
      $order->set_payment_status('paid');
      $debug .= "OK \n";

    } else {
      //  "Rejeter la demande !"
      echo "0;Null;Null;Null";
      $order->order_payment_id='maroc:'.$_POST['pmtSeqId'].':'.$_POST['approvalCode'].':'.$_POST['orderNumber'];
      Order::set_payment_id($order->order_id,'maroc:'.$_POST['pmtSeqId'].':'.$_POST['approvalCode'].':'.$_POST['orderNumber']) ;
      $order->set_payment_status('canceled');
      $debug .= "NOT OKAY \n";
      $debug .= "$checksum\n$checksumMTC \n";
    }
    $debug .= print_r($_POST,true);
    OrderStatus::statusChange($order->id,'maroc',$debug,'checkout::notify','3DSecureResult ='. (($_POST['3DSecureResult']==1)?'Successful':'Not authenticated.'));
    OrderStatus::statusChange($order->id,'maroc',$debug,'checkout::notify',$debug);

    return true;

  }

  function utf8entities($source)
  {
    //    array used to figure what number to decrement from character order value
    //    according to number of characters used to map unicode to ascii by utf-8
    $decrement[4] = 240;
    $decrement[3] = 224;
    $decrement[2] = 192;
    $decrement[1] = 0;

    //    the number of bits to shift each charNum by
    $shift[1][0] = 0;
    $shift[2][0] = 6;
    $shift[2][1] = 0;
    $shift[3][0] = 12;
    $shift[3][1] = 6;
    $shift[3][2] = 0;
    $shift[4][0] = 18;
    $shift[4][1] = 12;
    $shift[4][2] = 6;
    $shift[4][3] = 0;

    $pos = 0;
    $len = strlen($source);
    $encodedString = '';
    while ($pos < $len)
    {
      $charPos = substr($source, $pos, 1);
      $asciiPos = ord($charPos);
      if ($asciiPos < 128)
      {
        $encodedString .= htmlentities($charPos);
        $pos++;
        continue;
      }

      $i=1;
      if (($asciiPos >= 240) && ($asciiPos <= 255))  // 4 chars representing one unicode character
        $i=4;
      else if (($asciiPos >= 224) && ($asciiPos <= 239))  // 3 chars representing one unicode character
        $i=3;
      else if (($asciiPos >= 192) && ($asciiPos <= 223))  // 2 chars representing one unicode character
        $i=2;
      else  // 1 char (lower ascii)
        $i=1;
      $thisLetter = substr($source, $pos, $i);
      $pos += $i;

      //       process the string representing the letter to a unicode entity
      $thisLen = strlen($thisLetter);
      $thisPos = 0;
      $decimalCode = 0;
      while ($thisPos < $thisLen)
      {
        $thisCharOrd = ord(substr($thisLetter, $thisPos, 1));
        if ($thisPos == 0)
        {
          $charNum = intval($thisCharOrd - $decrement[$thisLen]);
          $decimalCode += ($charNum << $shift[$thisLen][$thisPos]);
        }
        else
        {
          $charNum = intval($thisCharOrd - 128);
          $decimalCode += ($charNum << $shift[$thisLen][$thisPos]);
        }

        $thisPos++;
      }

      $encodedLetter = '&#'. str_pad($decimalCode, ($thisLen==1)?3:5, '0', STR_PAD_LEFT).';';
      $encodedString .= $encodedLetter;
    }

    return $encodedString;
  }
}
?>