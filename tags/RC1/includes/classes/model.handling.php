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
class Handling Extends Model {

  protected $_idName    = 'handling_id';
  protected $_tableName = 'Handling';
  protected $_columns   = array('#handling_id', '*handling_payment', '*handling_shipment', '=handling_fee_type','handling_fee_perseat',
                                'handling_fee_percent', 'handling_fee_fix', 'handling_email_template',
                                '=handling_pdf_template', 'handling_pdf_ticket_template', 'handling_html_template',
                                'handling_sale_mode', 'handling_extra', '=handling_text_shipment', '=handling_text_payment',
                                'handling_expires_min', '#handling_alt', 'handling_alt_only', 'handling_only_manual_send' );

  protected $_pment = null;
  protected $_sment = null;

  public $templates;
  public $extra = array();
  public $sale_mode;

	function clear() {
	  parent::clear();
    if (isset($this->_pment)){
      $this->_pment->free;
    }
    if (isset($this->_sment)) {
      $this->_sment->free;
      unset($this->_sment);
    }
	}

	function load ($handling_id){
  	global $_SHOP;

  	if(isset($_SHOP->_handling_cache[$handling_id])){
    		return $_SHOP->_handling_cache[$handling_id];
  	}

	  $query="SELECT *
            FROM `Handling`
            WHERE handling_id=".ShopDB::quote($handling_id);
  	if($res=ShopDB::query_one_row($query)){
    		$hand=new Handling;
    		$hand->_fill($res);
        $hand->_unser_templates('handling_email_template');
  			$hand->_unser_extra();
    		$_SHOP->_handling_cache[$handling_id]=&$hand;
    		return $hand;
  	}
  	return null;
	}

  function loadAll ($handling_sale_mode=''){
    global $_SHOP;
    $sale=($handling_sale_mode)?"where handling_sale_mode=".ShopDB::quote($handling_sale_mode):'';


    $query="select * from Handling $sale";
    if($res=ShopDB::query($query)){
      while($data=shopDB::fetch_assoc($res)){
        $hand=new Handling;
        $hand->_fill($data);
        $hand->_unser_templates('handling_email_template');
				$hand->_unser_extra();
        $hands[]=$hand;
      }
    }
    return $hands;
  }

  function save (){
		$this->_ser_extra();
    $this->_ser_templates('handling_email_template');
    $exclude = ($this->handling_id)? array('handling_payment','handling_shipment'): null;

    return parent::save(null, $exclude);
  }

  /* Remember when concreting that you cant change the parent access! */
  function delete (){
    global $_SHOP;
 		$query="SELECT count(order_id) AS count
            FROM `Order`
            WHERE order_handling_id="._esc($this->id);
		if($res=ShopDB::query_one_row($query) and $res['count']==0){
		  return parent::delete();
		}else{
			return addWarning('in_use');
		}
  }

// Calculates fee for tickets
  function calculate_fee ($total, $seats=1){
    if ($this->handling_fee_perseat=='Yes') {
      $x = $seats * $this->handling_fee_fix;
    } else
      $x = $this->handling_fee_fix;
    $y = ($total/100.00)*$this->handling_fee_percent;
    switch ($this->handling_fee_type) {
      case 'min':
          return round(($x < $y)?$x : $y,2);
          break;
      case 'max':
          return round(($x > $y)?$x : $y,2);
          break;
      default:
        return round($x+$y,2);
    }
  }

  /**
   * Handling::handle()
   *
   * Function will send try and send the handling method emails and any post proccessing that needs doing.
   *
   *
   * @param mixed $order
   * @param mixed $new_state
   * @param string $old_state
   * @param string $field
   * @return
   */
  function handle ($order,$new_state,$old_state='',$field=''){
    global $_SHOP;//print_r($this);
    include_once(INC.'classes'.DS.'model.template.php');

    $sentEmail=TRUE;

    ShopDB::begin('proc_on_handle_for_eph_esh');

    if($pm = $this->pment()){
			if(method_exists($pm, 'on_handle')){
				if(!$pm->on_handle($order,$new_state,$old_state,$field)){
          return self::_abort('eph_on_handle_failed');
				}
			}
		}

		if($sm = $this->sment()){
			if(method_exists($sm,'on_handle')){
				if(!$sm->on_handle($order,$new_state,$old_state,$field)){
          return self::_abort('esh_on_handle_failed');
				}
			}
		}
    list($dummy,$field) = explode('_', $field);
    if ($field=='status') { $field='Order'; }
    if(!plugin::call('checkOrder'.$field.'StatusChanging', $new_state, $order)) {
      return false;
    }

    if($template_name=$this->templates[$new_state] and $order->user_email){

      $tpl= &Template::getTemplate($template_name);

      $order_d=(array)$order;   //print_r( $order_d);
      $link= $_SHOP->root_base . "checkout.php?action=view&id={$order->order_id}&".$order->EncodeSecureCode();
      $order_d['order_link']=$link;
      $order_d['order_old_status'] = $old_state;
 //     $order_d['note_subject']=empt($order->emailSubect,"");
 //     $order_d['note_body']=empt($order->emailNote,"");

      $handTemps=explode(",",$order->handling->handling_email_template);
      if(!is_array($handTemps)){
        $handTemps = array();
      }
      foreach($handTemps as $temp){
        $t=explode("=",$temp);
        $tempStatus = substr($t[0],0,strlen($new_state));

        if(strcasecmp($tempStatus,$new_state)===0){
          $tempPdfName = substr($t[0],(strlen($tempStatus)+1));

          if( empt($tempPdfName,false) ){
            $order_d["handling_{$tempPdfName}"] = $t[1];
          }
        }
      }

      $order_d = array_merge($order_d,(array)$order->handling);
      $order_d['action']= is($order_d['action'],'Handle: '.$new_state.'->'.$template_name);

      if(!Template::sendMail($tpl, $order_d, "", $_SHOP->lang)){
        $sentEmail=FALSE;
      }
    }

    if (!$sentEmail) {
      addWarning('status_change_handling_error', $new_state);
    }else{
      trace("order_is_set_to_{$new_state}");
    }

    //If the tickets can be sent email  can be sent upon payment automaticaly go for it!;
    /* the order sould be only set to send when the tickets are really send to the patron.
       This is now handled by the order_print method as that is the plase the system know for sure the tickets are created.
    $status = strtolower($new_state);
    $manSend = strtolower($order->handling->handling_only_manual_send);
    if($status=='paid'
     //  && $order->handling->handling_shipment=='email'
       && $sentEmail
     //  $manSend=='no'
     ){
      $order->set_shipment_status('send');
    }
 */
    ShopDB::commit('proc_on_handle_for_eph_esh'); // No DB objects are handled below. <= not true, there is still a set_shipment_status db action ;)

		return ($sentEmail);
  }

	function on_order_delete($order_id){
    $ok = true;
		if($ok and $pm = $this->pment()){
			if(method_exists($pm,'on_order_delete')){
				$ok=$pm->on_order_delete($order_id);
      }
    }
		if($ok and $sm = $this->sment()){
			if(method_exists($sm,'on_order_delete')){
				$ok= $sm->on_order_delete($order_id);
			}
    }
    return $ok;
	}

	/**
	 * @return true if the handling uses an extended payment handler (ie paypal, ideal)
	 * @access public
	 */
	public function is_eph() {
    	return ($this->pment())?true:false;
  	}

  // Loads default extras for payment method eg.
  function admin_init(){
    $this->handling_text_payment=  con($this->handling_payment);
   	$this->handling_text_shipment=  con($this->handling_shipment);
  	if($pm=$this->pment()){
      $pm->admin_init();
    }
  	if($sm=$this->sment()){
      $sm->admin_init();
    }
    $this->_columns   = str_replace( '=', '*', $this->_columns );
  }

	function admin_view(){
		if($pm=$this->pment()){
  		return $pm->admin_view();
		}
	}

	function admin_form($view, &$data){
		if($pm=$this->pment()){
    	return $pm->admin_form($view, $data);
		}
	}


	/**
	 * Handling::isValidCallback()
	 *
	 * Will ask the eph to verify its details set in the encodeCallback method.
	 *
	 * @param string $code
	 * @return boolean : true
	 * @since 1.0b5
	 */
	public function isValidCallback($code){
		if($pm=$this->pment()){
			return $pm->decodeCallback($code);
  		}
	}

	/**
	 * Handling::decodeEPHCallback()
	 *
	 * It will break down the callback hash, find which eph then check against its validation method
	 * to check that the handling id matches the settings within the eph.
	 * The handling object filled will then be returned on successfull decode and validation.
	 *
	 * @return Handling : Object or null.
	 * @uses Handling
	 * @since 1.0b5
	 */
	public function decodeEPHCallback($callbackCode){

		if (empty($callbackCode) and isset($_REQUEST['cbr'])) $callbackCode =$_REQUEST['cbr'];

		if(!empty($callbackCode)){
			$hand = null; //handling var

      $text = base64_decodex($callbackCode);
      		$code = explode(':',$text);
      		$code[1] = base_convert($code[1],36,10);

      		if(is_numeric($code[1])){
	  			$hand = Handling::load($code[1]);
	  		}
	  		if($hand == null){
        return -98;
			}
	  		if($hand->is_eph()){
				if($hand->handling_payment != $code[0]){
					return -97;
				}
				if($hand->isValidCallback($code[2])){
				  if($pm=$hand->pment()){
				    return $pm->getOrder($code);
				  }else {
				  	return -94;
				  }

				} else {
					return -95;
				}
	  		}
      return -96;
		}
	}

  	/**
  	 * @name OnConfirm
  	 *
  	 * The function is used to get the payment form/method from
  	 * the extended payment handler.
  	 *
  	 * @param order : the order object [Required]
  	 * @return array or html
  	 * @access public
  	 * @author Niels
  	 * @uses Order Object, EPH Object
  	 */
	public function on_confirm($order) {
    	$return ='';
  		if(($pm=$this->pment()) && ((real)$order->order_total_price != 0.00)){
      	$return = $pm->on_confirm($order);
  		} else {
        if((real)$order->order_total_price === 0.00){
          $order->set_payment_status ('paid');
        }
        return array('approved'=>true,
                     'transaction_id'=>false,
                     'response'=> $this->handling_html_template);
    	}
    	return (is_array($return))?$return:$return.'<br>'.$this->handling_html_template;
  	}

  function on_submit(&$order) {
  	if($pm=$this->pment()){
      return $pm->on_submit($order);
  	}
  }

  function on_return(&$order, $accepted) {
  	if($pm=$this->pment()){
      return $pm->on_return($order, $accepted);
  	} else {
      return array('approved'=>$accepted,
                   'transaction_id'=>false,
                   'response'=> '');
  	}
  }

  function on_notify(&$order) {
  	if($pm=$this->pment()){
      return $pm->on_notify($order);
  	}
  }

  function on_check(&$order) {
  	if($pm=$this->pment()){
      return $pm->on_check($order);
  	}
  }

  function getRequiredFields() {
    $result = array();
    if($pm=$this->pment()){
      $result = array_merge($result, $pm->requiteduserfields);
    }
    if($sm=$this->sment()){
      $result = array_merge($result, $sm->requiteduserfields);
    }
    return $result;
  }
	/**
	 * @name PaymentMethod
	 *
	 * Will load the eph file and create the eph object
	 *
	 * @example : eph_paypal.php would be loaded and the eph object would be created like:
	 *  EPH_paypal then added the to this handling object on _pment varible.
	 *
	 * @return EPH Object
	 * @since 1.0
	 * @author Niels
	 * @uses EPH Object
	 * @access private
	 */
	private function pment() {
	    if (!isset($this->handling_payment) or (!$this->handling_payment)) return;

		$file = INC."classes".DS."payments".DS."eph_".$this->handling_payment.".php";

		if (file_exists($file)){
      		if (!isset($this->_pment)){
            require_once ($file);
        		$name = "EPH_".$this->handling_payment;
        		$this->_pment = new $name($this);
        		$this->extras = $this->_pment->extras;
      		}
    	}
    	return $this->_pment;
  }

  function sment() {
    if (!isset($this->handling_shipment) or (!$this->handling_shipment)) return;
    $file = INC."classes".DS."shipments".DS."esm_{$this->handling_shipment}.php";
    if (!isset($this->_sment) and file_exists($file)) {
      require_once ($file);
      $name = "ESM_{$this->handling_shipment}";
      $this->_sment = new $name($this);
    }
    return $this->_sment;
  }

  function getPayment (){
    $types=array('entrance'=>'entrance','invoice'=>'invoice');
    $dir = INC.'classes'.DS.'payments';
	  if ($handle = opendir($dir)) {
		  while (false !== ($file = readdir($handle))){
        if ($file != "." && $file != ".." && !is_dir($dir.$file) && preg_match("/^eph_(.*?\w+).php/", $file, $matches)) {
          $types[$matches[1]] =  $matches[1];
        }
      }
      closedir($handle);
  	}
    return $types;
  }


  function getShipment (){
  	$like= " LIKE  'handling_shipment'";
    $query="SHOW  COLUMNS  FROM Handling {$like}";
    if(!$res=ShopDB::query_one_row($query)){return;}
    $types=explode("','",preg_replace("/(enum|set)\('(.+?)'\)/","\\2",$res['Type']));
    foreach($types as $key=>$type){
      $namedTypes[$type]=$type;
    }
    return $namedTypes;
  }

  function getHandlings ($include ='', $handle_id=0){
		$sqli="SELECT handling_id, handling_payment, handling_shipment FROM `Handling` WHERE handling_id not in ('1','{$handle_id}')";
		if(!$result=ShopDB::query($sqli)){echo("Error"); return;}
		$options= array();

    $options["{$handle_id}"] = con('always_show_handling');
    if ($include)
			$options["-1"] = $include;

		while ($row=shopDB::fetch_assoc($result)) {
			$id=$row["handling_id"];
			$payment= $row["handling_payment"];
			$shipping=$row["handling_shipment"];
			$options["{$id}"] = $id." - ".con($payment)." - ".con($shipping);
		}
		return $options;
	}

  function _ser_templates($templates){
    $t0 = array();
    foreach ($this as $key => $value) {
      if(strpos($key, $templates.'_')!== false){
        $key = substr($key,strlen($templates)+1);
        $t0[]="$key=$value";
      }
    }
    $this->$templates = implode(',',$t0);
  }

  function _unser_templates($handling_templates){
    if($this->$handling_templates and $t0=explode(',',$this->$handling_templates)){
      foreach($t0 as $s_t){
        list($state,$template)=explode('=',$s_t);
        $statex = $handling_templates.'_'.$state;
        $this->$statex = $template;
        $this->templates[$state] = $template;
      }
    }
  }

  function _ser_extra(){
    if(!empty($this->extra)){
      $this->handling_extra=serialize($this->extra);
    }
    $this->handling_sale_mode =   '';
    If (is_array($this->sale_mode)) {
      $this->handling_sale_mode = implode(",", array_keys($this->sale_mode));
    }
  }

  function _unser_extra(){
    if(!empty($this->handling_extra)){
      $this->extra=unserialize($this->handling_extra);
    } else {
      $this->extra= array();
    }
    if ( $pm = $this->pment()) {
      foreach($this->extra as $key => $val){
        if(in_array($key, $pm->extras)){
          $this->$key = $val;
        }
      }
    }

    $keys  = explode(",", $this->handling_sale_mode);
    if (count($keys)>0) {
      $this->sale_mode = array_combine($keys,array_fill(0,count($keys),true));
    } else {
      $this->sale_mode = array();
    }
  }

	function CheckValues(&$data){
    if ($data['handling_id']) {
      $this->_columns   = str_replace( '=', '*', $this->_columns );
      if($pm = $this->pment()){
  			$pm->admin_check($data);
  		}
    }
		return parent::CheckValues($data);
	}

  function getFieldtype(&$key){
    //echo $key;
    $testchars = array('='=>512,'+'=>1024);
    $return    = 0;
    foreach($testchars as $check => $value) {
      $pos = strpos($key,$check );
      if ($pos !== false) {
        $return |= $value;
        $key = substr_replace($key, '', $pos, 1);
      }
    }
    //   echo ' ',$key,' ',$return," ~~ \n";
    return $return |= parent::getFieldtype($key);
  }


  function _fill ($data, $nocheck=true){
 		if(!empty($data['handling_sale_mode_a'])){
			$data['handling_sale_mode']=implode(',',$data['handling_sale_mode_a']);
	 	}
    $ok = parent::_fill($data, $nocheck);
    if ($ok and ( $pm = $this->pment())) {
      foreach($pm->extras as $key)
        $this->extra[$key] = is($data[$key], null);
    }
    $this->sale_mode = is($data['sale_mode'],'');
    return $ok;
  }

  function isMandatory($name){
    if($pm=$this->pment()){
      if (in_array($name,$pm->mandatory )) {
        return true;
      }
    }
    return parent::isMandatory($name);
  }
}
?>