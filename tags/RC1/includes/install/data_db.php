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

$tbls = array();

/* */
$tbls['Admin']['fields'] = array(
  'admin_id'       => " int(11) NOT NULL AUTO_INCREMENT ",
  'admin_login'    => " varchar(50) NOT NULL DEFAULT ''",
  'admin_realname' => " varchar(65) NOT NULL DEFAULT ''",
  'admin_password' => " varchar(45) NOT NULL DEFAULT ''",
  'admin_status'   => " varchar(15) NOT NULL DEFAULT 'admin'",
  'admin_created'  => " timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP",
  'admin_user_id'  => " int(11) DEFAULT NULL",
  'admin_email'    => " varchar(50) NOT NULL DEFAULT ''",
  'admin_ismaster' => " enum('No','Yes') DEFAULT 'No'",
  'admin_inuse'    => " enum('No','Yes') DEFAULT 'Yes'",
  'admin_country'  => " varchar(5) NOT NULL DEFAULT ''",
  'control_event_ids' => " varchar(100) DEFAULT ''");
$tbls['Admin']['key'] = array(
  "PRIMARY KEY (`admin_id`)",
  "UNIQUE KEY `admin_login` (`admin_login`)");
$tbls['Admin']['engine'] = 'InnoDB';
$tbls['Admin']['remove'] = array ('control_organizer_id','admin_level')   ;


$tbls['adminlink']['fields'] = array(
  'adminlink_id'          => " int(11) NOT NULL AUTO_INCREMENT ",
  'adminlink_event_id'    => " int(11) DEFAULT NULL",
  'adminlink_admin_id'    => " int(11) DEFAULT NULL",
  'adminlink_pos_id'      => " int(11) DEFAULT NULL",
  'adminlink_admgroup_id' => " int(11) DEFAULT NULL");

$tbls['adminlink']['key'] = array(
  "PRIMARY KEY (`adminlink_id`)",
  "UNIQUE KEY `adminlink_records` (`adminlink_event_id`,`adminlink_admin_id`,`adminlink_pos_id`,`adminlink_admgroup_id`)"
  );
$tbls['adminlink']['engine'] = 'InnoDB';
$tbls['adminlink']['remove'] = array ();

$tbls['admingroups']['fields'] = array(
  'admingroup_id' => " int(11) NOT NULL AUTO_INCREMENT ",
  'admingroup_name' => " varchar(50) NOT NULL DEFAULT ''",
  'admingroup_ismaster' => " enum('No','Yes') DEFAULT 'No'",
  'admingroup_inuse' => " enum('No','Yes') DEFAULT 'Yes'");
$tbls['admingroups']['key'] = array(
  "PRIMARY KEY (`admingroup_id`)");
$tbls['admingroups']['engine'] = 'InnoDB';


$tbls['auth']['fields'] = array(
  'auth_id' => " int(11) NOT NULL AUTO_INCREMENT",
  'user_id' => " int(11) NOT NULL DEFAULT '0'",
  'username' => " varchar(50) NOT NULL DEFAULT ''",
  'password' => " varchar(45) NOT NULL DEFAULT ''",
  'active' => " varchar(38) DEFAULT 'not null'",
  'user_group' => " int(11) NOT NULL DEFAULT '0'");
$tbls['auth']['key'] = array(
  "PRIMARY KEY (`auth_id`)",
  "UNIQUE KEY `username` (`username`)",
  "KEY `password` (`password`)",
  "KEY `user_id` (`user_id`)");
$tbls['auth']['engine'] = 'InnoDB';
$tbls['auth']['remove'] = array ('owner_id','userlevel');

$tbls['User']['fields'] = array(
  'user_id' => " int(11) NOT NULL AUTO_INCREMENT",
  'user_status' => " tinyint(4) NOT NULL DEFAULT '0'",
  'user_lastname' => " varchar(50) NOT NULL DEFAULT ''",
  'user_firstname' => " varchar(50) NOT NULL DEFAULT ''",
  'user_address' => " varchar(75) NOT NULL DEFAULT ''",
  'user_address1' => " varchar(75) DEFAULT ''",
  'user_zip' => " varchar(10) NOT NULL DEFAULT ''",
  'user_city' => " varchar(50) NOT NULL DEFAULT ''",
  'user_state' => " varchar(50) DEFAULT NULL",
  'user_country' => " varchar(5) NOT NULL DEFAULT ''",
  'user_phone' => " varchar(50) DEFAULT NULL",
  'user_fax' => " varchar(50) DEFAULT NULL",
  'user_email' => " varchar(50) NOT NULL DEFAULT ''",
  'user_prefs' => " text",
  'user_created' => " timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP",
  'user_custom1' => " varchar(50) DEFAULT ''",
  'user_custom2' => " text",
  'user_custom3' => " int(11) DEFAULT '0'",
  'user_custom4' => " datetime DEFAULT '0000-00-00 00:00:00'",
  'user_owner_id' => " int(11) DEFAULT NULL",
  'user_lastlogin' => " datetime NOT NULL DEFAULT '0000-00-00 00:00:00'",
  'user_order_total' => " int(11) NOT NULL DEFAULT '0'",
  'user_current_tickets' => " int(6) NOT NULL DEFAULT '0'",
  'user_total_tickets' => " int(11) NOT NULL DEFAULT '0'");
$tbls['User']['key'] = array(
  "PRIMARY KEY (`user_id`)");
$tbls['User']['engine'] = 'InnoDB';
$tbls['User']['remove'] = array ('user_organizer_ids') ;
//$tbls['User']['AUTO_INCREMENT'] = 48;


$tbls['Category']['fields'] = array(
  'category_id' => " int(11) NOT NULL AUTO_INCREMENT",
  'category_event_id' => " int(11) DEFAULT NULL",
  'category_price' => " decimal(10,2) DEFAULT NULL",
  'category_name' => " varchar(100) DEFAULT NULL",
  'category_web' => " tinyint(1) NOT NULL DEFAULT '1'",
  'category_pos' => " tinyint(1) NOT NULL DEFAULT '1'",
  'category_pm_id' => " int(11) DEFAULT NULL",
  'category_pmp_id' => " int(11) DEFAULT NULL",
  'category_ident' => " tinyint(4) DEFAULT NULL",
  'category_numbering' => " varchar(5) NOT NULL DEFAULT 'none'",
  'category_size' => " int(11) NOT NULL DEFAULT '0'",
  'category_free' => " int(11) NOT NULL DEFAULT '0'",
  'category_max' => " int(11) DEFAULT NULL",
  'category_min' => " int(11) DEFAULT NULL",
  'category_template' => " varchar(30) DEFAULT NULL",
  'category_color' => " varchar(10) DEFAULT NULL",
  'category_data' => " text");
$tbls['Category']['key'] = array(
  "PRIMARY KEY (`category_id`)",
  "KEY `category_event_id` (`category_event_id`)");
$tbls['Category']['engine'] = 'InnoDB';
$tbls['Category']['remove'] = array ('category_organizer_id');
//$tbls['Category']['AUTO_INCREMENT'] = 87;

$tbls['CC_Info']['fields'] = array(
  'cc_info_order_id' => "  int(11) NOT NULL AUTO_INCREMENT",
  'cc_info_data' => "  mediumtext NOT NULL",
  'cc_info_key' => "  mediumtext NOT NULL");
$tbls['CC_Info']['key'] = array(
  "PRIMARY KEY (`cc_info_order_id`)");
$tbls['CC_Info']['engine'] = 'InnoDB';
$tbls['CC_Info']['remove'] = array ('cc_info_organizer_id');
//$tbls['CC_Info']['AUTO_INCREMENT'] = 1;

$tbls['Discount']['fields'] = array(
  'discount_id' => " int(11) NOT NULL AUTO_INCREMENT",
  'discount_event_id' => " int(11) DEFAULT NULL",
  'discount_category_id' => " int(11) DEFAULT NULL",
  'discount_name' => " varchar(50) NOT NULL DEFAULT ''",
  'discount_type' => " varchar(7) NOT NULL DEFAULT ''",
  'discount_value' => " decimal(10,2) NOT NULL DEFAULT '0.00'",
  'discount_promo' => " varchar(15) DEFAULT ''",
  'discount_used' => " int(11) DEFAULT '0'",
  'discount_active' => " set('pos','www','yes') DEFAULT ''",
  'discount_cond' => " text"
  );
$tbls['Discount']['key'] = array(
  "PRIMARY KEY (`discount_id`)");
$tbls['Discount']['engine'] = 'InnoDB';
$tbls['Discount']['remove'] = array ();
//$tbls['Discount']['AUTO_INCREMENT'] = 4;

$tbls['Event']['fields'] = array(
  'event_id' => " int(11) NOT NULL AUTO_INCREMENT",
  'event_created' => " timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP",
  'event_name' => " varchar(100) NOT NULL DEFAULT ''",
  'event_text' => " text",
  'event_short_text' => " text",
  'event_url' => " varchar(100) DEFAULT NULL",
  'event_image' => " varchar(100) DEFAULT NULL",
  'event_webshop' => " varchar(10) DEFAULT NULL",
  'event_ort_id' => " int(11) DEFAULT NULL",
  'event_pm_id' => " int(11) DEFAULT NULL",
  'event_timestamp' => " timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'",
  'event_date' => " date DEFAULT NULL",
  'event_time' => " time DEFAULT NULL",
  'event_open' => " time DEFAULT NULL",
  'event_end' => " time DEFAULT NULL",
  'event_status' => " varchar(5) NOT NULL DEFAULT ''",
  'event_order_limit' => " int(4) NOT NULL DEFAULT '0'",
  'event_template' => " varchar(30) DEFAULT NULL",
  'event_group_id' => " int(11) DEFAULT NULL",
  'event_mp3' => " varchar(200) DEFAULT NULL",
  'event_rep' => " set('main','sub') NOT NULL DEFAULT 'main,sub'",
  'event_main_id' => " int(11) DEFAULT NULL",
  'event_type' => " varchar(25) DEFAULT NULL",
  'event_keywords' => " varchar(100) DEFAULT ''",
  'event_custom1' => " varchar(50) DEFAULT NULL",
  'event_custom2' => " text",
  'event_custom3' => " int(11) DEFAULT NULL",
  'event_custom4' => " datetime DEFAULT '0000-00-00 00:00:00'",
  'event_view_begin' => " datetime DEFAULT '0000-00-00 00:00:00'",
  'event_view_end'   => " datetime DEFAULT '0000-00-00 00:00:00'",
  'event_total' => " int(11) NOT NULL DEFAULT '0'",
  'event_free' => " int(11) NOT NULL DEFAULT '0'");
$tbls['Event']['key'] = array(
  "PRIMARY KEY (`event_id`)",
  "KEY `event_date` (`event_date`)",
  "KEY `event_timestamp` (`event_timestamp`)");
$tbls['Event']['engine'] = 'InnoDB';
$tbls['Event']['remove'] = array ('event_organizer_id');
//$tbls['Event']['AUTO_INCREMENT'] = 4;

$tbls['Event_group']['fields'] = array(
  'event_group_id' => " int(11) NOT NULL AUTO_INCREMENT",
  'event_group_name' => " varchar(100) NOT NULL DEFAULT ''",
  'event_group_description' => " text",
  'event_group_image' => " varchar(100) DEFAULT ''",
  'event_group_status' => " varchar(5) NOT NULL DEFAULT 'unpub'",
  'event_group_start_date' => " date DEFAULT NULL",
  'event_group_end_date' => " date DEFAULT NULL",
  'event_group_type' => " varchar(25) DEFAULT NULL");
$tbls['Event_group']['key'] = array(
  "PRIMARY KEY (`event_group_id`)");
$tbls['Event_group']['engine'] = 'InnoDB';
$tbls['Event_group']['remove'] = array ('event_group_organizer_id');
//$tbls['Event_group']['AUTO_INCREMENT'] = 3;

$tbls['email_log']['fields'] = array(
  'el_id' => " int(11) NOT NULL AUTO_INCREMENT",
  'el_order_id' => " int(11) DEFAULT NULL",
  'el_user_id' => " int(11) NOT NULL DEFAULT '0'",
  'el_failed' => " enum('no','yes','unknown') DEFAULT 'unknown'",
  'el_received' => " enum('no','yes') DEFAULT 'yes'",
  'el_timestamp' => " timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP",
  'el_action' => " varchar(50) NOT NULL",
  'el_email_uid' => " varchar(255) DEFAULT NULL",
  'el_email_to' => " text",
  'el_email_cc' => " text",
  'el_email_message' => " text",
  'el_log' => " text",
  'el_bad_emails' => " text");
$tbls['email_log']['key'] = array(
  "PRIMARY KEY (`el_id`)",
  "KEY `el_order_id` (`el_order_id`)",
  "KEY `el_user_id` (`el_user_id`)");
$tbls['email_log']['engine'] = 'InnoDB';

//$tbls['Event_stat']['engine'] = 'InnoDB';
// $tbls['Event_stat']['AUTO_INCREMENT'] = 3;
$tbls['Handling']['fields'] = array(
  'handling_id' => " int(11) NOT NULL AUTO_INCREMENT",
  'handling_payment' => " varchar(25) DEFAULT NULL",
  'handling_shipment' => " enum('email','post','entrance','sp') DEFAULT NULL",
  'handling_fee_fix' => " decimal(5,2) DEFAULT NULL",
  'handling_fee_percent' => " decimal(5,2) DEFAULT NULL",
  'handling_fee_type' => " enum('sum','min','max') NOT NULL DEFAULT 'sum'",
  'handling_fee_perseat' => " enum('Yes','No') NOT NULL DEFAULT 'No'",
  'handling_email_template' => " tinytext",
  'handling_pdf_template' => " tinytext",
  'handling_pdf_ticket_template' => " tinytext",
  'handling_pdf_format' => " tinytext",
  'handling_html_template' => " mediumtext",
  'handling_sale_mode' => " set('sp','www') DEFAULT NULL",
  'handling_extra' => " text",
  'handling_text_shipment' => " mediumtext",
  'handling_text_payment' => " mediumtext",
//  'handling_delunpaid' => " enum('Yes','No') NOT NULL DEFAULT 'No'",
  'handling_expires_min' => " int(11) DEFAULT '0'",
  'handling_alt' => " int(11) DEFAULT NULL",
  'handling_alt_only' => " enum('Yes','No') NOT NULL DEFAULT 'No'",
  'handling_only_manual_send' => " enum('Yes','No') NOT NULL DEFAULT 'No'");
$tbls['Handling']['key'] = array(
  "PRIMARY KEY (`handling_id`)");
$tbls['Handling']['engine'] = 'InnoDB';
$tbls['Handling']['remove'] = array ('handling_organizer_id','handling_delunpaid');
//$tbls['Handling']['AUTO_INCREMENT'] = 33;

$tbls['Order']['fields'] = array(
  'order_id' => " int(11) NOT NULL AUTO_INCREMENT",
  'order_user_id' => " int(11) NOT NULL DEFAULT '0'",
  'order_session_id' => " varchar(32) NOT NULL DEFAULT ''",
  'order_tickets_nr' => " int(11) NOT NULL DEFAULT '0'",
  'order_total_price' => " decimal(10,2) NOT NULL DEFAULT '0.00'",
  'order_partial_price' => " decimal(10,2) NOT NULL DEFAULT '0.00'",
  'order_discount_promo' => " varchar(15) DEFAULT NULL",
  'order_discount_price' => " decimal(10,2) NOT NULL DEFAULT '0.00'",
  'order_discount_id' => " int(11) DEFAULT NULL",
  'order_date' => " datetime NOT NULL DEFAULT '0000-00-00 00:00:00'",
  'order_timestamp' => " timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP",
  'order_order_set' => " set('nofee','nocost') DEFAULT ''",
  'order_shipment_status' => " enum('none','send') NOT NULL DEFAULT 'none'",
  'order_payment_status' => " enum('none','pending','paid','payed','canceled','cancelled') NOT NULL DEFAULT 'none'",
  'order_payment_id' => " varchar(255) DEFAULT NULL",
  'order_handling_id' => " int(11) DEFAULT NULL",
  'order_status' => " enum('ord','cancel','reemit','reissue','trash','res','pros') NOT NULL DEFAULT 'ord'", //only got reemit for legacy tables
  'order_reemited_id' => " int(11) DEFAULT NULL",
  'order_fee' => " decimal(10,2) DEFAULT NULL",
  'order_place' => " varchar(11) NOT NULL DEFAULT 'www'",
  'order_owner_id' => " int(11) DEFAULT NULL",
  'order_admin_id' => " int(11) DEFAULT NULL",
  'order_date_expire' => " datetime DEFAULT NULL",
  'order_responce' => " varchar(50) NOT NULL DEFAULT ''",
  'order_responce_date' => " datetime NOT NULL DEFAULT '0000-00-00 00:00:00'",
  'order_note' => " text",
  'order_lock' => " tinyint(1) NOT NULL DEFAULT '0'",
  'order_lock_time' => "timestamp NULL DEFAULT '0000-00-00 00:00:00'",
  'order_lock_admin_id' => "int(11) DEFAULT NULL",
  'order_custom1' => " varchar(200) DEFAULT NULL",
  'order_custom2' => " text",
  'order_custom3' => " int(11) DEFAULT NULL",
  'order_custom4' => " datetime DEFAULT '0000-00-00 00:00:00'",

  'order_lang' => "varchar(2) DEFAULT NULL");

$tbls['Order']['key'] = array(
  "PRIMARY KEY (`order_id`)",
  "UNIQUE KEY `order_payments` (`order_handling_id`,`order_payment_id`)",
  "KEY `order_handling_id` (`order_handling_id`)",
  "KEY `order_status` (`order_status`)",
  "KEY `order_shipment_status` (`order_shipment_status`)",
  "KEY `order_payment_status` (`order_payment_status`)",
  "KEY `order_owner_id` (`order_owner_id`)",
  "KEY `order_timestamp` (`order_timestamp`)");
$tbls['Order']['engine'] = 'InnoDB';
$tbls['Order']['remove'] = array ('order_organizer_id');

//$tbls['Order']['AUTO_INCREMENT'] = 1;

$tbls['order_status']['fields'] = array(
  'os_id' => " int(11) NOT NULL AUTO_INCREMENT",
  'os_order_id' => " int(11) NOT NULL DEFAULT '0'",
  'os_changed' => " timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP",
  'os_status_from' => "varchar(50) DEFAULT NULL",
  'os_status_to' => "varchar(50) DEFAULT NULL",
  'os_changed_by' => "int(11) DEFAULT NULL",
  'os_action' => "varchar(50) DEFAULT NULL",
  'os_description' => "text",
  'os_data' => "text"
);
$tbls['order_status']['key'] = array(
  "PRIMARY KEY (`os_id`)",
  "KEY `os_order_id` (`os_order_id`)"
);
$tbls['order_status']['engine'] = 'InnoDB';
$tbls['order_status']['remove'] = array ('id','order_id','changed','status_from','status_to','changed_by','action','description');

$tbls['order_note']['fields'] =array(
  'onote_id' => " int(11) NOT NULL AUTO_INCREMENT",
  'onote_order_id' => " int(11) NOT NULL DEFAULT '0'",
  'onote_user_id' => " int(11) DEFAULT NULL",
  'onote_admin_id' => " int(11) DEFAULT NULL",
  'onote_timestamp' => " timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP",
  'onote_private' => " tinyint(1) NOT NULL DEFAULT '0'",
  'onote_type' => " varchar(20) NOT NULL DEFAULT 'note'",
  'onote_subject' => " varchar(200) NOT NULL DEFAULT ''",
  'onote_note' => " text",
  'onote_todo' => " tinyint(1) NOT NULL DEFAULT '0'",
  'onote_todo_timestamp' => " timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'"
);
$tbls['order_note']['key'] = array(
  "PRIMARY KEY (`onote_id`)",
  "KEY `on_order_id` (`onote_order_id`)"
);
$tbls['order_note']['engine'] = 'InnoDB';
$tbls['order_note']['remove'] = array();

$tbls['Ort']['fields'] = array(
  'ort_id' => " int(11) NOT NULL AUTO_INCREMENT",
  'ort_created' => " timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP",
  'ort_name' => " varchar(100) NOT NULL DEFAULT ''",
  'ort_phone' => " varchar(50) DEFAULT NULL",
  'ort_plan_nr' => " varchar(100) DEFAULT ''",
  'ort_url' => " varchar(100) DEFAULT ''",
  'ort_image' => " varchar(100) DEFAULT NULL",
  'ort_address' => " varchar(75) NOT NULL DEFAULT ''",
  'ort_address1' => " varchar(75) DEFAULT ''",
  'ort_zip' => " varchar(20) NOT NULL DEFAULT ''",
  'ort_city' => " varchar(50) NOT NULL DEFAULT ''",
  'ort_state' => " varchar(50) DEFAULT ''",
  'ort_country' => " varchar(50) NOT NULL DEFAULT ''",
  'ort_pm' => " text",
  'ort_fax' => " varchar(50) DEFAULT NULL");
$tbls['Ort']['key'] = array(
  "PRIMARY KEY (`ort_id`)");
$tbls['Ort']['engine'] = 'InnoDB';
$tbls['Ort']['remove'] = array ('ort_organizer_id')   ;
//$tbls['Ort']['AUTO_INCREMENT'] = 1;

$tbls['PlaceMap2']['fields'] = array(
  'pm_id' => " int(11) NOT NULL AUTO_INCREMENT",
  'pm_ort_id' => " int(11) NOT NULL DEFAULT '0'",
  'pm_event_id' => " int(11) DEFAULT NULL",
  'pm_name' => " varchar(30) NOT NULL DEFAULT ''",
  'pm_image' => " varchar(100) DEFAULT NULL");
$tbls['PlaceMap2']['key'] = array(
  "PRIMARY KEY (`pm_id`)");
$tbls['PlaceMap2']['engine'] = 'InnoDB';
$tbls['PlaceMap2']['remove'] = array ('pm_organizer_id')   ;
//$tbls['PlaceMap2']['AUTO_INCREMENT'] = 36;

$tbls['PlaceMapPart']['fields'] = array(
  'pmp_id' => " int(11) NOT NULL AUTO_INCREMENT",
  'pmp_pm_id' => " int(11) NOT NULL DEFAULT '0'",
  'pmp_ident' => " tinyint(4) NOT NULL DEFAULT '0'",
  'pmp_ort_id' => " int(11) DEFAULT NULL",
  'pmp_event_id' => " int(11) DEFAULT NULL",
  'pmp_name' => " varchar(30) NOT NULL DEFAULT ''",
  'pmp_width' => " int(11) NOT NULL DEFAULT '0'",
  'pmp_height' => " int(11) NOT NULL DEFAULT '0'",
  'pmp_scene' => " enum('north','east','south','west','center') NOT NULL DEFAULT 'north'",
  'pmp_shift' => " tinyint(1) NOT NULL DEFAULT '0'",
  'pmp_data' => " longtext NOT NULL",
  'pmp_data_orig' => " longtext",
  'pmp_expires' => " int(11) DEFAULT NULL");
$tbls['PlaceMapPart']['key'] = array(
  "PRIMARY KEY (`pmp_id`)");
$tbls['PlaceMapPart']['engine'] = 'InnoDB';
$tbls['PlaceMapPart']['remove'] = array ('pmp_organizer_id')   ;
//$tbls['PlaceMapPart']['AUTO_INCREMENT'] = 34;

$tbls['PlaceMapZone']['fields'] = array(
  'pmz_id' => " int(11) NOT NULL AUTO_INCREMENT",
  'pmz_pm_id' => " int(11) NOT NULL DEFAULT '0'",
  'pmz_ident' => " tinyint(4) NOT NULL DEFAULT '0'",
  'pmz_name' => " varchar(50) NOT NULL DEFAULT ''",
  'pmz_short_name' => " varchar(10) DEFAULT NULL",
  'pmz_color' => " varchar(10) DEFAULT NULL");
$tbls['PlaceMapZone']['key'] = array(
  "PRIMARY KEY (`pmz_id`)",
  "KEY `pm_id` (`pmz_pm_id`)" ,
  "KEY `pmz_ident` (`pmz_ident`)");
$tbls['PlaceMapZone']['engine'] = 'InnoDB';
$tbls['PlaceMapZone']['remove'] = array ('pmz_organizer_id')   ;
//$tbls['PlaceMapZone']['AUTO_INCREMENT'] = 51;

$tbls['Seat']['fields'] = array(
  'seat_id' => " int(11) unsigned NOT NULL AUTO_INCREMENT",
  'seat_created' => " timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP",
  'seat_event_id' => " int(11) NOT NULL DEFAULT '0'",
  'seat_category_id' => " int(11) NOT NULL DEFAULT '0'",
  'seat_user_id' => " int(11) DEFAULT NULL",
  'seat_order_id' => " int(11) DEFAULT NULL",
  'seat_row_nr' => " varchar(5) DEFAULT NULL",
  'seat_zone_id' => " int(11) DEFAULT NULL",
  'seat_pmp_id' => " int(11) DEFAULT NULL",
  'seat_nr' => " varchar(5) NOT NULL DEFAULT '0'",
  //TODO: Real timestamp. for res_delay

  'seat_ts' => " int(11) DEFAULT NULL",
  'seat_sid' => " varchar(32) DEFAULT NULL",
  'seat_price' => " decimal(10,2) DEFAULT NULL",
  'seat_discount' => " decimal(10,2) DEFAULT '0.00'",
  'seat_discount_id' => " int(11) DEFAULT NULL",
  'seat_code' => " varchar(16) DEFAULT NULL",
  'seat_status' => " varchar(5) NOT NULL DEFAULT 'free'",
  'seat_sales_id' => " int(11) DEFAULT NULL",
  'seat_checked_by' => " int(11) DEFAULT NULL",
  'seat_checked_date' => " timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'",
  'seat_old_order_id' => "int(11) DEFAULT NULL",
  'seat_old_status' => "varchar(5) DEFAULT NULL");

$tbls['Seat']['key'] = array(
  "PRIMARY KEY (`seat_id`)",
  "KEY `seat_event_id` (`seat_event_id`)",
  "KEY `seat_category_id` (`seat_category_id`)",
  "KEY `seat_order_id` (`seat_order_id`)",
  "KEY `seat_ts` (`seat_ts`)",
  "KEY `seat_pmp_id` (`seat_pmp_id`)",
  "KEY `seat_status` (`seat_status`)");
$tbls['Seat']['engine'] = 'InnoDB';
$tbls['Seat']['remove'] = array ('seat_organizer_id')   ;

$tbls['Template']['fields'] = array(
  'template_id' => " int(11) NOT NULL AUTO_INCREMENT",
  'template_type' => " varchar(5) NOT NULL DEFAULT ''",
  'template_context' => " varchar(10) NOT NULL DEFAULT 'shop'",
  'template_name' => " varchar(30) NOT NULL DEFAULT ''",
  'template_text' => " longtext NOT NULL",
  'template_ts' => " timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP",
  'template_status' => " varchar(5) NOT NULL DEFAULT 'new'");
$tbls['Template']['key'] = array(
  "PRIMARY KEY (`template_id`)",
  "UNIQUE KEY `template_name` (`template_name`,`template_context`)");
$tbls['Template']['engine'] = 'InnoDB';
$tbls['Template']['remove'] = array ('template_organizer_id')   ;
//$tbls['Template']['AUTO_INCREMENT'] = 21;

$tbls['plugins']['fields'] = array(
  'plugin_id'        => " int(11) NOT NULL AUTO_INCREMENT",
  'plugin_name'      => " varchar(40) NOT NULL",
  'plugin_version'   => " varchar(40) NOT NULL",
  'plugin_enabled'   => " tinyint(4) NOT NULL DEFAULT '0'",
  'plugin_protected' => " tinyint(4) NOT NULL DEFAULT '0'",
  'plugin_settings'  => " text",
  'plugin_priority'  => " int(10) unsigned NOT NULL DEFAULT '3'");
$tbls['plugins']['key'] = array(
  "PRIMARY KEY (`plugin_id`)",
  "UNIQUE KEY `key_plugin_name` (`plugin_name`)");
$tbls['plugins']['remove'] = array ();
$tbls['plugins']['engine'] = 'InnoDB';

$tbls['configuration']['fields'] = array(
  'config_id' => " int(11) NOT NULL AUTO_INCREMENT",
  'config_field'   => " varchar(65) NOT NULL DEFAULT ''",
  'config_value'   => " text",
  'config_type'    => " varchar(100) NOT NULL DEFAULT 'TEXT'",
);
$tbls['configuration']['key'] = array(
  "PRIMARY KEY (`config_id`)",
  'UNIQUE KEY `config_group_field` (`config_field`)');
$tbls['configuration']['remove'] = array ();
$tbls['configuration']['engine'] = 'InnoDB';
?>