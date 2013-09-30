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

// Include all the required files
if (!defined('ft_check')) {die('System intrusion ');}

require_once('google/library/googlecart.php');
require_once('google/library/googleitem.php');
require_once('google/library/googleshipping.php');
require_once('google/library/googletax.php');
require_once('google/library/googleresponse.php');
require_once('google/library/googleresult.php');
require_once('google/library/googlerequest.php');

require_once('classes/class.payment.php');

class EPH_google extends Payment{

	public $extras = array('pm_google_merchant_id', 'pm_google_merchant_key',
                         'pm_google_sandbox','pm_google_callback_link',
		                     'pm_on_google_cancel_cancel_order','pm_on_refund_cancel_order',
                         'pm_on_user_cancel_cancel_order');
	public $mandatory = array('pm_google_merchant_id', 'pm_google_merchant_key'); // is only used in project vazant.

  public function admin_init(){
 		$this->handling_text_payment    = "Google Checkout";
		$this->handling_text_payment_alt= "Google Checkout";
		//$this->handling_html_template  .= "";
		$this->pm_google_sandbox = true;
		$this->pm_on_google_cancel_cancel_order = true;
		$this->pm_on_user_cancel_cancel_order = false;
		$this->pm_on_refund_cancel_order = false;
		$this->pm_google_callback_link = "Generated On Save";
	}

	public function admin_form (){
		global $_SHOP;

		$form = "{gui->input name='pm_google_merchant_id'}".
           "{gui->input name='pm_google_merchant_key'}".
           "{gui->checkbox name='pm_google_sandbox'}".
		   "{gui->view name='pm_google_callback_link'}".
		   "{gui->checkbox name='pm_on_google_cancel_cancel_order'}".
		   "{gui->checkbox name='pm_on_user_cancel_cancel_order'}".
		   "{gui->checkbox name='pm_on_refund_cancel_order'}";
           return $form;
	}

	public function admin_view (){
    	return "{gui->view name='pm_google_merchant_id'}".
           "{gui->view name='pm_google_sandbox'}";
	}

	public function admin_check(&$data){
		global $_SHOP;
		$data['pm_google_callback_link'] = $_SHOP->root_secured. 'checkout_notify.php?'.$this->encodeCallback();
		return parent::admin_check($data);
	}

	public function on_confirm(&$order){
		global $_SHOP;

    $merchant_id = $this->pm_google_merchant_id;  // Your Merchant ID
    $merchant_key = $this->pm_google_merchant_key;  // Your Merchant Key
    $server_type = "sandbox";
    $currency = $_SHOP->organizer_currency;

    if($this->pm_google_sandbox){
			$server_type = "sandbox";
		}else{
			$server_type = "live";
		}

    $googleCart = new GoogleCart($merchant_id, $merchant_key, $server_type, $currency);
   	$total_count = 1;
   	$googleItem = new GoogleItem($_SHOP->organizer_name,      // Item name
                               $order->order_description(), // Item      description
                               $total_count, // Quantity
                               $order->order_total_price); // Unit price
		$googleCart->AddItem($googleItem);

		// Add shipping options
		$ship_1 = new GoogleFlatRateShipping("See Box Office", 0.0);

      	$Gfilter = new GoogleShippingFilters();
      	$Gfilter->SetAllowedCountryArea('ALL');
      	$Gfilter->SetAllowedWorldArea(true);

      	$ship_1->AddShippingRestrictions($Gfilter);

      	$googleCart->AddShipping($ship_1);

      	// Add tax rules
      	$tax_rule = new GoogleDefaultTaxRule(0.00);
      	$tax_rule->SetWorldArea(true);

      	$googleCart->AddDefaultTaxRules($tax_rule);

      	// Specify <edit-cart-url>
      	//$googleCart->SetEditCartUrl($_SHOP->root_secured.'checkout.php');

		// Specify "Return to xyz" link
		$googleCart->SetContinueShoppingUrl($_SHOP->root_secured. 'checkout_accept.php?'.$order->EncodeSecureCode());

		// Request buyer's phone number
		$googleCart->SetRequestBuyerPhone(true);

		$ftData = array('order-id'=>$order->order_id);
		$merchantPrivateData = new MerchantPrivateData($ftData);
		$googleCart->SetMerchantPrivateData($merchantPrivateData);


		// Display Google Checkout button
		return $googleCart->CheckoutButtonCode("SMALL");
	}

	public function on_notify(&$order){
		global $_SHOP;

		define('RESPONSE_HANDLER_ERROR_LOG_FILE', 'googleerror.log');
		define('RESPONSE_HANDLER_LOG_FILE', 'googlemessage.log');

		$merchant_id = $this->pm_google_merchant_id;  // Your Merchant ID
 	  $merchant_key = $this->pm_google_merchant_key;  // Your Merchant Key
    $server_type = "sandbox";
    $currency = $_SHOP->organizer_currency;

    if($this->pm_google_sandbox){
			$server_type = "sandbox";
		}else{
			$server_type = "live";
		}

		$Gresponse = new GoogleResponse($merchant_id, $merchant_key);
		$Grequest = new GoogleRequest($merchant_id, $merchant_key, $server_type, $currency);

		//Setup the log file
		$Gresponse->SetLogFiles(RESPONSE_HANDLER_ERROR_LOG_FILE, RESPONSE_HANDLER_LOG_FILE, L_ALL);
    $Grequest->SetLogFiles(RESPONSE_HANDLER_ERROR_LOG_FILE, RESPONSE_HANDLER_LOG_FILE, L_ALL);

		// Retrieve the XML sent in the HTTP POST request to the ResponseHandler
		$xml_response = isset($HTTP_RAW_POST_DATA) ? $HTTP_RAW_POST_DATA : file_get_contents("php://input");
		if (get_magic_quotes_gpc()) {
			$xml_response = stripslashes($xml_response);
		}
		list($root, $data) = $Gresponse->GetParsedXML($xml_response);
		$Gresponse->SetMerchantAuthentication($merchant_id, $merchant_key);
		$status = $Gresponse->HttpAuthentication();
		if(! $status) {
			die('authentication failed');
		}

		$google_order_id = $data[$root]['google-order-number']['VALUE'];

    /* */
		switch ($root) {
			case "request-received": {
			  break;
			}
			case "error": {
			  break;
			}
			case "diagnosis": {
			  break;
			}
			case "checkout-redirect": {
			  break;
			}
			case "new-order-notification": {
				//Get Order Id
				$order_id = $data[$root]['shopping-cart']['merchant-private-data']['order-id']['VALUE'];
				if(is_numeric($order_id)){
					if(Order::set_payment_id($order_id,"google:".$google_order_id)){
						$order = Order::load($order_id);
						$order->set_payment_status('pending');

						$Gresponse->SendAck(false);
						$Grequest->SendMerchantOrderNumber($google_order_id,$order_id);
					}
				}
        $Gresponse->SendServerErrorStatus("500 The server can't update the order id please try later.", true);
				break;
			}
			case "order-state-change-notification": {
				$Gresponse->SendAck(false);
				$new_financial_state = $data[$root]['new-financial-order-state']['VALUE'];
				$new_fulfillment_order = $data[$root]['new-fulfillment-order-state']['VALUE'];
				$order = Order::loadFromPaymentId("google:".$google_order_id,$this->handling_id);

				switch($new_financial_state) {
					case 'REVIEWING': {
		  		  break;
					}
					case 'CHARGEABLE': {
				  		//$Grequest->SendProcessOrder($data[$root]['google-order-number']['VALUE']);
				  		//$Grequest->SendChargeOrder($data[$root]['google-order-number']['VALUE'],'');
				  		break;
					}
					case 'CHARGING': {
				  		break;
					}
					case 'CHARGED': {
				  		break;
					}
					case 'PAYMENT_DECLINED': {
						$order->set_payment_status('pending');
		  		  $Grequest->SendBuyerMessage($google_order_id,
						   "Sorry, your payment for ".$_SHOP->organizer_name." has been declined.
						   Your order is pending, Please login for more info.", true);
				  		break;
					}
					case 'CANCELLED': {
						if($this->pm_on_user_cancel_cancel_order){
							Order::delete($order->order_id,'user_cancelled_by_google_checkout'); //"Canceled By Google Checkout (User Canceled)."

							$Grequest->SendBuyerMessage($google_order_id,
						   		"Your payment for ".$_SHOP->organizer_name." has been cancelled.
						   		Your order has been cancelled, please login into Google Checkout for more info.", true);
            }else{
				   			$order->set_payment_status('none');
				  			$Grequest->SendBuyerMessage($google_order_id,
						   		"Your payment for ".$_SHOP->organizer_name." has been canceled.
						   		Your order is not paid!, please login into Google Checkout for more info.", true);
			   		}
			  		break;
					}
					case 'CANCELLED_BY_GOOGLE': {
						if($this->pm_on_google_cancel_cancel_order){
							Order::delete($order->order_id,"Fraud_canceled_by_google_checkout"); //"Canceled By Google Checkout (Possible Fraud)."
							$Grequest->SendBuyerMessage($google_order_id,
						   		"Sorry, your order for ".$_SHOP->organizer_name." has been canceled by Google.
						   		Your order has been canceled, please login into Google Checkout more info.", true);
						}else{
							$order->set_payment_status("pending");
							$Grequest->SendBuyerMessage($google_order_id,
						   		"Sorry, your order for ".$_SHOP->organizer_name." has been canceled by Google.
						   		Your order is pending, please login into Google Checkout for more info.", true);
						}
				  		break;
					}
					default:
				  		break;
				}

				switch($new_fulfillment_order) {
					case 'NEW': {
				  		break;
					}
					case 'PROCESSING': {
				  		break;
					}
					case 'DELIVERED': {
				  		break;
					}
					case 'WILL_NOT_DELIVER': {
				  		break;
					}
					default:
				  		break;
				}
			  break;
			}
			case "charge-amount-notification": {

				$google_total_charge_amount = $data[$root]['total-charge-amount']['VALUE'];
				$order = Order::loadFromPaymentId("google:".$google_order_id,$this->handling_id);
			  	//$Grequest->SendDeliverOrder($data[$root]['google-order-number']['VALUE'],
			  	//    <carrier>, <tracking-number>, <send-email>);

			  	if($google_total_charge_amount < $order->order_total_price){
			  		$amount = $order->order_total_price - $google_total_charge_amount;
			  		$Grequest->SendChargeOrder($google_order_id,$amount);
			  	}elseif($google_total_charge_amount >= $order->order_total_price){
			  		$order->set_payment_status("paid");
			  		$Grequest->SendArchiveOrder($google_order_id);
			  	}else{
			  		$Gresponse->SendServerErrorStatus("500 The server couldn't match the amounts paid please try later.", true);
			  	}
			  	$Gresponse->SendAck();
				break;
			}
			case "chargeback-amount-notification": {
			  $Gresponse->SendAck();
			  break;
			}
			case "refund-amount-notification": {
				$Gresponse->SendAck(false);
				$order = Order::loadFromPaymentId("google:".$google_order_id,$this->handling_id);

				if($this->pm_on_refund_cancel_order){
					Order::delete($order->order_id,"refunded_by_google_checkout"); //"Refunded By Google Checkout."
				}
				$Grequest->SendBuyerMessage($google_order_id,
					"Your order for ".$_SHOP->organizer_name." has been refunded.", true);
			  	break;
			}
			case "risk-information-notification": {
			  $Gresponse->SendAck();
			  break;
			}
			default:
			  $Gresponse->SendBadRequestStatus("Invalid or not supported Message");
			  break;
		}
    /* */
	}

	function on_return(&$order, $result){

		return array('approved'=>true,
                   'transaction_id'=>$order->order_payment_id ,
                   'response'=> 'Google Payment Registered. You will be emailed when your order status changes.<br>'
				   				.'Or you may refresh this page to check order status.<br />'
				   				.'Order Status: '.con($order->order_status).'<br />'
				   				.'Payment Status: '.con($order->order_payment_status).'<br />'
				   				.'Shipping Status: '.con($order->order_shipment_status)
 								);
	}

	public function getOrder(){

	}

	public function encodeCallback(){
    	$sha1 = $this->pm_google_merchant_id;
    	$hash = sha1($sha1, true);

    	return $this->encodeEPHCallback($hash);
	}

	public function decodeCallback($ephHash){
		$sha1 = $this->pm_google_merchant_id;

		if($ephHash <> sha1($sha1,true)){
			return false;
		}else{
			return true;
		}
	}

	/* In case the XML API contains multiple open tags
	 with the same value, then invoke this function and
	 perform a foreach on the resultant array.
	 This takes care of cases when there is only one unique tag
	 or multiple tags.
	 Examples of this are "anonymous-address", "merchant-code-string"
	 from the merchant-calculations-callback API
	*/
	function get_arr_result($child_node) {
		$result = array();
		if(isset($child_node)) {
	  		if(is_associative_array($child_node)) {
	    		$result[] = $child_node;
	  		} else {
	    		foreach($child_node as $curr_node){
	      			$result[] = $curr_node;
	    		}
	  		}
		}
		return $result;
	}

	/* Returns true if a given variable represents an associative array */
	function is_associative_array( $var ) {
		return is_array( $var ) && !is_numeric( implode( '', array_keys( $var ) ) );
	}

}
?>