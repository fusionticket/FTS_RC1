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
class OrderNote Extends Model {

  const TYPE_NOTE = "note";
  const TYPE_CUST = "cust";
  const TYPE_TODO = "todo";
  const TYPE_ADMIN = "admin";
  const TYPE_RESERVE = "reserved";
  const TYPE_PAYMENT = "payment";
  const TYPE_SHIP = "ship";
  const PRIVATE_ADMINS = 1;
  const PRIVATE_PUBLIC = 0;

  protected $_idName    = 'onote_id';
  protected $_tableName = 'order_note';
  protected $_columns   = array('#onote_id', '*onote_order_id', '#onote_user_id', '#onote_admin_id',
                                'onote_timestamp', 'onote_private', 'onote_type',
                                '*onote_subject','*onote_note');

  /*
  public function __construct($data, $message) {
    parent::__construct();
    $this->el_order_id = is($data['order_id']);
    $this->el_user_id  = is($data['user_id']);
    $this->el_action   = is($data['action'],'unknown'); //need to add action to the data.
    $this->el_email_uid = is($message->getId());
    $this->el_email_to  = serialize(is($message->getTo()));
    $this->el_email_cc  = serialize(is($message->getCc()));
    $this->el_email_message = is($message->toString());
    $this->el_bad_emails = '';
    $this->el_failed = 'unknown';
  }
  */


  public function addNote($data){
    parent::CheckValues($data);
  }

  public function save(){
    return parent::save();
  }

  public function saveEx(){
    return parent::saveEx();
  }

  public function fillRequest($noCheck = false){
    $this->onote_admin_id = is($_SESSION['_SHOP_AUTH_USER_DATA']['admin_id']);
    return parent::fillRequest($noCheck);
  }

  public function sendNote($orderObj, $statusType='note'){
    global $_SHOP;
/*
    if(!is_object($orderObj)){
      addWarning('no_order_for_note');
      return false;
    }
    OrderStatus::statusChange($orderObj->order_id,false,NULL,'OrderNote::sendNote',"Send Type: $statusType to order");

    $tpl= &Template::getTemplate('OrderNote');

    if(!$tpl){
      addWarning('no_template_for_note');
      return;
    }

    $order_d=(array)$orderObj;   //print_r( $order_d);
    $link= $_SHOP->root."index.php?personal_page=orders&id=";
    $order_d['order_link']       = $link;
    $order_d['order_old_status'] = $old_state;
    $order_d['note_subject']     = empt($this->onote_subject,"");
    $order_d['note_body']        = empt($this->onote_note,"");

    if(!Template::sendMail($tpl, $order_d, "", $_SHOP->lang)){
      addWarning('failed_to_send_note');
    }
*/
    return false;
  }

  public function load($onote_id){

    if(! is_numeric($onote_id) || $onote_id <= 0){
      addWarning('bad_id');
      return false;
    }

    $query = "SELECT *
              FROM `order_note`
              WHERE onote_id ="._esc($onote_id);

    if($row = ShopDB::query_one_row($query)){
      $orderNote = new OrderNote();
      $orderNote->_fill($row);
      return $orderNote;
    }
    return false;
  }
}
?>