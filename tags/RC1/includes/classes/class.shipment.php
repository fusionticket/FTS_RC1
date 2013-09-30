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
class Shipment {

  public $handling;
  public $extras    = array();
  public $mandatory = array();
  protected $emailOptions = array();
  private $defaultEmailOptions = array(
     "ordered"=>array("opt","opt"),
     "reserved"=>array("opt","opt"),
     "paid"=>array("opt","opt"),
     "unpaid"=>array("opt","opt"),
     "sent"=>array("opt","opt"),
     "unsent"=>array("opt","opt"),
     "cancelled"=>array("opt","opt")
  );
  public $requiteduserfields = array();

	function __construct (&$handling) {
 		$this->handling = &$handling;
  }

  function __get($name) {
    if ($this->handling and ($result = $this->handling->$name)) {
      return $result;
    } else {
      return false;
    }
  }

	function __set($name, $value) {
		if ($this->handling) {
	  		return $this->handling->$name = $value;
		} else {
	  		return false;
		}
	}

  public function getEmailOptions(){

  }

	public function admin_view ( ){}

 	public function admin_form ( ){}

	function admin_init (){}

	/**
	 * Used to check the manditory fields defined in the manditory array
	 */
	public function admin_check (&$data){
		foreach($this->mandatory as $field){
			if(empty($data[$field])){
        addError($field, 'mandatory');
      }
		}
  	return true;
	}

	function on_handle($order, $new_status, $old_status, $field){
    return true;
  }

	function on_order_delete($order_id){
    return true;
  }

  function on_confirm(&$order){return '';}

  function on_submit(&$order){}

  function on_return(&$order, $accepted ){
     return array('approved'=>$accepted,
                  'transaction_id'=>false,
                  'response'=> '');
  }

  function on_notify(&$order){}

  function on_check(&$order){ return false;}

  public function getOrder(){}

  public function encodeCallback(){return "";}

  public function decodeCallback(){return true;}
}
?>