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
class EmailLog Extends Model {

  protected $_idName    = 'el_id';
  protected $_tableName = 'email_log';
  protected $_columns   = array('#el_id', '#el_order_id', '#el_user_id', 'el_failed',
                                'el_received', 'el_timestamp', '*el_action',
                                'el_email_uid', 'el_email_to', 'el_email_cc',
                                'el_email_message', 'el_log');


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
}
?>