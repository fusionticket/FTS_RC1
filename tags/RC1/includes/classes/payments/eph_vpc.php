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

class EPH_vpc extends payment{
	public $extras = array('pm_vpc_AccessCode', 'pm_vpc_Merchant','pm_vpc_SECURE_SECRET', 'pm_vpc_User', 'pm_vpc_Password');
  public $mandatory = array('pm_vpc_AccessCode', 'pm_vpc_Merchant');


	function admin_view (){
    return "{gui->view name='pm_vpc_AccessCode'}".
           "{gui->view name='pm_vpc_Merchant'}".
    	"{gui->view name='pm_vpc_SECURE_SECRET'}".
    	"{gui->view name='pm_vpc_User'}".
    	"{gui->view name='pm_vpc_Password'}";
	}

  function admin_form (){
    return "{gui->input name='pm_vpc_AccessCode'}".
           "{gui->input name='pm_vpc_Merchant'}".
    	"{gui->input name='pm_vpc_SECURE_SECRET'}".
    	"{gui->input name='pm_vpc_User'}".
    	"{gui->input name='pm_vpc_Password'}";
	}

	function admin_init (){
	  $this->handling_text_payment    = "Migs";
	  $this->handling_text_payment_alt= "Migs";

    $this->handling_text_payment    = "Virtual Payment Client";
    $this->pm_vpc_SECURE_SECRET  = md5(date('r'));
	}

	function on_confirm(&$order) {
    global $_SHOP;
    return "
      <form action='{$_SHOP->root_secured}checkout.php' method='POST'>
	    <input type='hidden' name='action' value='submit'>
	    	<input type='hidden' name='sor' value='{$order->EncodeSecureCode('')}'>
        <div align='right'>
          <input type='submit' value='{!pay!}' name='submitted' alt='{!vpc_pay!}' class='payButton' >
        </div>
      </form>";
  }

  function on_submit(&$order, &$err){
        global $_SHOP;
        $amount = sprintf("%01d", ($order->order_total_price)*100);
	    $url    = $_SHOP->root_secured. 'checkout_accept.php?'.$order->EncodeSecureCode();
		$md5HashData = $this->pm_vpc_SECURE_SECRET;
		$POST = array();
        $POST['vpc_Version']     ='1';
        $POST['vpc_Command']     ='pay';
        $POST['vpc_AccessCode']  = $this->pm_vpc_AccessCode;
        $POST['vpc_MerchTxnRef'] = $order->order_id;
        $POST['vpc_Merchant'] 	 = $this->pm_vpc_Merchant;
        $POST['vpc_OrderInfo']   = $order->order_description();
        $POST['vpc_Amount'] 	 = $amount;
        $POST['vpc_Locale']      = $order->user_country;
        $POST['vpc_ReturnURL']   = $url;

//		$result = $this->digitalOrder($POST);

//Print_r($result);

  	ksort ($POST);
     //   print_r ($POST);
		// set a parameter to show the first pair in the URL
		$appendAmp = 0;
		$vpcURL ='';
		foreach($POST as $key => $value) {

			// create the md5 input and URL leaving out any fields that have no value
			if (strlen($value) > 0) {

				// this ensures the first paramter of the URL is preceded by the '?' char
				if ($appendAmp == 0) {
					$vpcURL .= urlencode($key) . '=' . urlencode($value);
					$appendAmp = 1;
				} else {
					$vpcURL .= '&' . urlencode($key) . "=" . urlencode($value);
				}
				$md5HashData .= $value;
			}
		}
       // echo $vpcURL;
		// Create the secure hash and append it to the Virtual Payment Client Data if
		// the merchant secret has been provided.
		if (strlen($this->pm_vpc_SECURE_SECRET) > 0) {
			$vpcURL .= "&vpc_SecureHash=" . strtoupper(md5($md5HashData));
		}

		// FINISH TRANSACTION - Redirect the customers using the Digital Order
		// ===================================================================
		header("Location: https://migs.mastercard.com.au/vpcpay?".$vpcURL);



	}

  function on_return(&$order, $result){
    $vpc_Txn_Secure_Hash = $_GET["vpc_SecureHash"];
    unset($_GET["vpc_SecureHash"]);
   // print_r($_GET);
    // set a flag to indicate if hash has been validated
    $errorExists = false;

    if (strlen($this->vpc_SECURE_SECRET) > 0 && is($_GET["vpc_TxnResponseCode"],7) != 7 ) {

        $md5HashData = $this->vpc_SECURE_SECRET;

        // sort all the incoming vpc response fields and leave out any with no value
        foreach($_GET as $key => $value) {
            if ($key != "vpc_SecureHash" or strlen($value) > 0) {
                $md5HashData .= $value;
            }
        }

        // Validate the Secure Hash (remember MD5 hashes are not case sensitive)
    	// This is just one way of displaying the result of checking the hash.
    	// In production, you would work out your own way of presenting the result.
    	// The hash check is all about detecting if the data has changed in transit.
        if (strtoupper($vpc_Txn_Secure_Hash) == strtoupper(md5($md5HashData))) {
            // Secure Hash validation succeeded, add a data field to be displayed
            // later.
            $hashValidated = "<FONT color='#00AA00'><strong>CORRECT</strong></FONT>";
        } else {
            // Secure Hash validation failed, add a data field to be displayed
            // later.
            $hashValidated = "<FONT color='#FF0066'><strong>INVALID HASH</strong></FONT>";
            $errorExists = true;
        }
    } else {
        // Secure Hash was not validated, add a data field to be displayed later.
        $hashValidated = "<FONT color='orange'><strong>Not Calculated - No 'SECURE_SECRET' present.</strong></FONT>";
    }

    // Define Variables
    // ----------------
    // Extract the available receipt fields from the VPC Response
    // If not present then let the value be equal to 'No Value Returned'

    // Standard Receipt Data
    $batchNo         = self::null2unknown($_GET["vpc_BatchNo"]);
    $message         = self::null2unknown($_GET["vpc_Message"]);
    $cardType        = self::null2unknown($_GET["vpc_Card"]);
    $receiptNo       = self::null2unknown($_GET["vpc_ReceiptNo"]);
    $merchantID      = self::null2unknown($_GET["vpc_Merchant"]);
    $authorizeID     = self::null2unknown($_GET["vpc_AuthorizeId"]);
    $transactionNo   = self::null2unknown($_GET["vpc_TransactionNo"]);
    $acqResponseCode = self::null2unknown($_GET["vpc_AcqResponseCode"]);
    $txnResponseCode = self::null2unknown($_GET["vpc_TxnResponseCode"]);


    // 3-D Secure Data
    $verType         = array_key_exists("vpc_VerType", $_GET)          ? $_GET["vpc_VerType"]          : "No Value Returned";
    $verStatus       = array_key_exists("vpc_VerStatus", $_GET)        ? $_GET["vpc_VerStatus"]        : "No Value Returned";
    $token           = array_key_exists("vpc_VerToken", $_GET)         ? $_GET["vpc_VerToken"]         : "No Value Returned";
    $verSecurLevel   = array_key_exists("vpc_VerSecurityLevel", $_GET) ? $_GET["vpc_VerSecurityLevel"] : "No Value Returned";
    $enrolled        = array_key_exists("vpc_3DSenrolled", $_GET)      ? $_GET["vpc_3DSenrolled"]      : "No Value Returned";
    $xid             = array_key_exists("vpc_3DSXID", $_GET)           ? $_GET["vpc_3DSXID"]           : "No Value Returned";
    $acqECI          = array_key_exists("vpc_3DSECI", $_GET)           ? $_GET["vpc_3DSECI"]           : "No Value Returned";
    $authStatus      = array_key_exists("vpc_3DSstatus", $_GET)        ? $_GET["vpc_3DSstatus"]        : "No Value Returned";


  	$_GET['ResponseMessage'] = $this->getResponseDescription($txnResponseCode);
  	$_GET['StatusMessage']   = $this->getStatusDescription($verStatus);

    OrderStatus::statusChange($order->order_id,'vpc',$txnResponseCode,'checkout::notify',$debug.print_r($_GET,true));
    if ($_REQUEST['vpc_3DSXID']) {
       Order::set_payment_id($order->order_id,'vpc:'.$_REQUEST['vpc_TransactionNo'].'/'.$_REQUEST['vpc_ReceiptNo']);
    }
	$info =  "<tr>
				        <td>Response</td>
				        <td>".$this->getResponseDescription($txnResponseCode)." ({$txnResponseCode})</td>
				    </tr>";

		if ($txnResponseCode == "0" || $txnResponseCode == "P") {
			if ($txnResponseCode == "P" && $order->order_payment_status != 'pending') {
        $order->set_payment_status('pending');
			} elseif ($txnResponseCode == "0" && $order->order_payment_status != 'paid') {
        $order->set_payment_status('paid');
	  }
      return array('approved'=>true,
                   'transaction_id'=>$_REQUEST['vpc_TransactionNo'].'/'.$_REQUEST['vpc_ReceiptNo'],
                   'response'=> $info);
    } else {
      return array('approved'=>false,
                   'transaction_id'=>false,
                   'response'=> "<tr><td>Response</td><td>{$message} ({$txnResponseCode})</td></tr>" /*<tr>
				        <td>Status</td>
				        <td>".$this->getStatusDescription($verStatus)." ({$verStatus})</td>
				    </tr>"*/);
    }

	}

	function on_check($order){
		if (!$this->pm_vpc_User or !$this->pm_vpc_Password) {
			return true;
		}
		$vpcURL = "https://migs.mastercard.com.au/vpcdps";
		$POST["vpc_Version"]="1";
		$POST["vpc_Command"]="queryDR";
		$POST["vpc_AccessCode"]=$this->pm_vpc_AccessCode;
		$POST["vpc_Merchant"]=$this->pm_vpc_Merchant;
		$POST["vpc_MerchTxnRef"]=$order->order_id;
		$POST["vpc_User"]=$this->pm_vpc_User;
		$POST["vpc_Password"]=$this->pm_vpc_Password;

		// create a variable to hold the POST data information and capture it
		$postData = "";

		$ampersand = "";
		foreach($POST as $key => $value) {
			// create the POST data input leaving out any fields that have no value
			if (strlen($value) > 0) {
				$postData .= $ampersand . urlencode($key) . '=' . urlencode($value);
				$ampersand = "&";
			}
		}

		// Get a HTTPS connection to VPC Gateway and do transaction
		// turn on output buffering to stop response going to browser
		ob_start();

		// initialise Client URL object
		$ch = curl_init();

		// set the URL of the VPC
		curl_setopt ($ch, CURLOPT_URL, $vpcURL);
		curl_setopt ($ch, CURLOPT_POST, 1);
		curl_setopt ($ch, CURLOPT_POSTFIELDS, $postData);

		// (optional) set the proxy IP address and port
		//curl_setopt ($ch, CURLOPT_PROXY, "192.168.21.13:80");

		// (optional) certificate validation
		// trusted certificate file
		//curl_setopt($ch, CURLOPT_CAINFO, "c:/temp/ca-bundle.crt");

		//turn on/off cert validation
		// 0 = don't verify peer, 1 = do verify
		curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);

		// 0 = don't verify hostname, 1 = check for existence of hostame, 2 = verify
		//curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);

		// connect
		curl_exec ($ch);

		// get response
		$response = ob_get_contents();

		// turn output buffering off.
		ob_end_clean();

		// set up message paramter for error outputs
		$message = "";

		// serach if $response contains html error code
		if(strchr($response,"<html>") || strchr($response,"<html>")) {
			OrderStatus::statusChange($order->order_id,'vpc', $txnResponseCode, 'checkout::check', $response);
		} elseif (curl_error($ch)){
			OrderStatus::statusChange($order->order_id,'vpc',$txnResponseCode,'checkout::check','curl error:'.curl_errno($ch) . "<br/>" . curl_error($ch));
		} else {
			$message = $response;
		}

		// close client URL
		curl_close ($ch);

		// Extract the available receipt fields from the VPC Response
		// If not present then let the value be equal to 'No Value Returned'
		$map = array();
		//var_dump($order);
		// process response if no errors
		if (strlen($message) !== 0) {
			$pairArray = split("&", $message);
			foreach ($pairArray as $pair) {
				$param = split("=", $pair);
				$map[urldecode($param[0])] = urldecode($param[1]);
			}
			$txnResponseCode = self::null2unknown($map["vpc_TxnResponseCode"]);
			$transactionid = self::null2unknown($map["vpc_3DSXID"]);

			if ($txnResponseCode == "0" || $txnResponseCode == "P") {
				if ($txnResponseCode == "P" && $order->order_payment_status != 'pending') {
					$order->set_payment_status('pending');
				} elseif ($txnResponseCode == "0" && $order->order_payment_status != 'paid') {
					if ($transactionid) {
						Order::set_payment_id($order->order_id,'vpc:'.$map['vpc_TransactionNo'].'/'.$map['vpc_ReceiptNo']);
					}
					OrderStatus::statusChange($order->order_id,'vpc',$txnResponseCode,'checkout::check','on_check: '.print_r($map,true)."change to paid.");
					$order->set_payment_status('paid');
				}
				return true;
			} else {
				return false;
			}

		}
		return true;
  }
	function digitalOrder($data) {
//		if (!in_array($this->config->mode, $this->ALLOWED_MODES)) return; // Invalid mode, bail.

//		$mergedData = array_merge($this->VPC_CONSTANTS, $this->config->merchant[$this->config->mode], $data);

		$queryString = $this->__package($data);
		$response = $this->__process('https://migs.mastercard.com.au/vpcdp', $queryString);

		// Unpackage the response string if the cURL request succeeded.
		if ($response) {
			$response = $this->__unpackage($response);
		}

		return $response;
	}

	/**
	 * Internal function where all the juicy curl fun takes place.
	 * @access private
	 * @param string $url Required. Url to request
	 * @param string $postfields Optional. Url encoded query string.
	 */
	private function __process($url, $postfields=false) {
		$ch = curl_init($url);

		// Add in the post fields (this should be always)!
		if ($postfields !== false) {
			curl_setopt ($ch, CURLOPT_POST, true);
			curl_setopt ($ch, CURLOPT_POSTFIELDS, $postfields);
		}

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_VERBOSE, 1);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);

		// Some localhost/test environments might need relaxed security.
		if ($this->config->mode == 'test') {
        	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		}

    $response = curl_exec($ch);
    $responseInfo = curl_getinfo($ch);

    curl_close($ch);

    // Return null for any requests that didn't return successfully.
    if (intval($responseInfo['http_code']) != 200) {
      $response = null;
		}
		return $response;
	}

	/**
	 * Function to piece together a cohesive query string from the values passed in.
	 * Fields with empty values will be ignored.
	 * The query string will have an md5 hash of the query string values added according to Bendigo's protocol.
	 * NOTE: If the $SECURE_SECRET is left empty - this will not take be done.
	 * @access private
	 * @param array $values
	 * @return string $queryString
	 */
	private function __package($values) {
		$queryString = '';
	    $hashData = $this->vpc_SECURE_SECRET;

		// 1. The Secure Hash Secret is always first.
		// 2. Then all DO fields are concatenated to the Secure Hash Secret in alphabetical order of the field name.
		ksort($values);

		foreach ($values as $key => $val) {
		    if (strlen($val) > 0) {
		        $queryString .= $key . '=' . rawurlencode($val) . '&';
				$hashData .= $val;
			}
	    }

		// Remove the trailing '&'.
		$queryString = substr($queryString, 0, strlen($queryString)-1);

		// Create the secure hash and append it if the merchant secret has been provided.
		if (strlen($this->config->secureSecret) > 0) {
		    $queryString = 'vpc_SecureHash=' . strtoupper(md5($hashData)) . '&' . $queryString;
		}

	    return $queryString;
	}

	/**
	 * Convert the $queryString (from the format: key=value&foo=bar)
	 * into an associative array.
	 */
	private function __unpackage($queryString) {
		$array = array();
		parse_str($queryString, $array);

		return $array;
	}

  // This method uses the QSI Response code retrieved from the Digital
  // Receipt and returns an appropriate description for the QSI Response Code
  //
  // @param $responseCode String containing the QSI Response Code
  //
  // @return String containing the appropriate description
  //
  function getResponseDescription($responseCode) {

      switch ($responseCode) {
          case "0" : $result = "Transaction Successful"; break;
          case "?" : $result = "Transaction status is unknown"; break;
          case "1" : $result = "Unknown Error"; break;
          case "2" : $result = "Bank Declined Transaction"; break;
          case "3" : $result = "No Reply from Bank"; break;
          case "4" : $result = "Expired Card"; break;
          case "5" : $result = "Insufficient funds"; break;
          case "6" : $result = "Error Communicating with Bank"; break;
          case "7" : $result = "Payment Server System Error"; break;
          case "8" : $result = "Transaction Type Not Supported"; break;
          case "9" : $result = "Bank declined transaction (Do not contact Bank)"; break;
          case "A" : $result = "Transaction Aborted"; break;
          case "C" : $result = "Transaction Cancelled"; break;
          case "D" : $result = "Deferred transaction has been received and is awaiting processing"; break;
          case "F" : $result = "3D Secure Authentication failed"; break;
          case "I" : $result = "Card Security Code verification failed"; break;
          case "L" : $result = "Shopping Transaction Locked (Please try the transaction again later)"; break;
          case "N" : $result = "Cardholder is not enrolled in Authentication scheme"; break;
          case "P" : $result = "Transaction has been received by the Payment Adaptor and is being processed"; break;
          case "R" : $result = "Transaction was not processed - Reached limit of retry attempts allowed"; break;
          case "S" : $result = "Duplicate SessionID (OrderInfo)"; break;
          case "T" : $result = "Address Verification Failed"; break;
          case "U" : $result = "Card Security Code Failed"; break;
          case "V" : $result = "Address Verification and Card Security Code Failed"; break;
          default  : $result = "Unable to be determined";
      }
      return $result;
  }



  //  -----------------------------------------------------------------------------

  // This method uses the verRes status code retrieved from the Digital
  // Receipt and returns an appropriate description for the QSI Response Code

  // @param statusResponse String containing the 3DS Authentication Status Code
  // @return String containing the appropriate description

  function getStatusDescription($statusResponse) {
      if ($statusResponse == "" || $statusResponse == "No Value Returned") {
          $result = "3DS not supported or there was no 3DS data provided";
      } else {
          switch ($statusResponse) {
              Case "Y"  : $result = "The cardholder was successfully authenticated."; break;
              Case "E"  : $result = "The cardholder is not enrolled."; break;
              Case "N"  : $result = "The cardholder was not verified."; break;
              Case "U"  : $result = "The cardholder's Issuer was unable to authenticate due to some system error at the Issuer."; break;
              Case "F"  : $result = "There was an error in the format of the request from the merchant."; break;
              Case "A"  : $result = "Authentication of your Merchant ID and Password to the ACS Directory Failed."; break;
              Case "D"  : $result = "Error communicating with the Directory Server."; break;
              Case "C"  : $result = "The card type is not supported for authentication."; break;
              Case "S"  : $result = "The signature on the response received from the Issuer could not be validated."; break;
              Case "P"  : $result = "Error parsing input from Issuer."; break;
              Case "I"  : $result = "Internal Payment Server system error."; break;
              default   : $result = "Unable to be determined"; break;
          }
      }
      return $result;
  }

  //  -----------------------------------------------------------------------------

  // If input is null, returns string "No Value Returned", else returns input
  function null2unknown($data) {
      if ($data == "") {
          return "No Value Returned";
      } else {
          return $data;
      }
  }

}
?>