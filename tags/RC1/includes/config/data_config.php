<?php
/**
 *               %%%copyright%%%
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

/**
 *
 *
 * @version $Id$
 * @copyright 2011
 */

$config = array();

$config['group_organizer'] = array(
  'organizer_name' =>     array('Demo Owner',  'text','*'),
  'organizer_address' =>  array('5678 Demo St',  'text','*'),
  'organizer_plz' =>      array('11001',  'text','*'),
  'organizer_ort' =>      array('Demo Town',  'text','*'),
  'organizer_state' =>    array('DT',  'text','*'),
  'organizer_country' =>  array('US',  'select','*','getCountries'),
  'organizer_email' =>    array('info@fusionticket.test',  'text','*'),
  'organizer_fax' =>      array('(555) 555-1215',  'text',''),
  'organizer_phone' =>    array('(555) 555-1214',  'text','*'),
 // 'organizer_place' =>    array('',  'text',''),
  'organizer_currency' => array('USD',  'select','*', 'getCurrencies'),
'organizer_logo' =>     array('', 'file','^'),
);

$config['group_common'] = array(
  'secure_site'      => array(0,'yesno','*'),
  'UseRewriteURL'    => array(0,'yesno','*'),
  'theme_name'       => array('default','select','*','getThemeList'),
//  'useUTF8'          => array(0,'yesno'),
  'input_time_type'  =>  array(24,'hidden','-',array('12'=>'time_type_12','24'=>'time_type_24')),
  'input_date_type'  =>  array('dmy','hidden','-',array('dmy'=>'date_type_dmy',
                                                      'mdy'=>'date_type_mdy',
                                                      'ymd'=>'date_type_ydm',
                                                      'ydm'=>'date_type_dmy')),
  'timezone'         => array(null,'select','*', 'gettimezones'),
//  'rowcount'         => array(null,'number','*'),
  'pdf_paper_size'   => array("A4",'text','*'),
  'pdf_paper_orientation' => array("portrait",'select','*', array('P'=>"portrait", 'L'=>'landscape')),
	'address_label_template' => array(null,'select','*', 'getTemplates'),
	'address_label_always' => array(0,'yesno'),
  'system_online'    => array('1','yesno','*'),
  'offline_message'  => array('System temperary offline','area','',array('rows'=>5,'cols'=>92)),
  'trace_on'         => array(null,'select','*', array('ALLSEND' => 'Email orphan errors with history',
                                                       'SEND'    => 'eMail orphan errors',
                                                       'ALL'     => 'Always log traces',
                                                       'TRACEONLY'=>'Trace without orphan check',
                                                       '0'       => 'Disable (faster response)')),

  'shopconfig_run_as_demo' => array(0,'yesno','*'), // " int(3) NOT NULL DEFAULT '0'"
  'notifytimeout' => array(20,'number','*'),
);

$config['group_eventsettings'] = array(
  'archive_oldevents'    => array('0','yesno','*'),
  'allow_free_tickets'   => array('0','yesno','*'),
  'event_type_enum'  =>  array(null,'area','#@',array('rows'=>5,'cols'=>92)),
  'event_group_type_enum'  =>  array(null,'area','#@',array('rows'=>5,'cols'=>92)),
);
$config['group_autocleanup'] = array(
  'shopconfig_lastrun' => array(0,'view','','datetime'),
  'shopconfig_lastrun_int' => array(10,'number'),
  'shopconfig_check_pos' =>  array(0,'yesno'),//" enum('No','Yes') NOT NULL DEFAULT 'No'",
  'shopconfig_delunpaid' =>  array(0,'yesno'),//" enum('Yes','No') NOT NULL DEFAULT 'Yes'",
  'shopconfig_delunpaid_pos' =>  array(0,'yesno'),//" enum('Yes','No') NOT NULL DEFAULT 'Yes'",
  'cart_delay'  =>  array(600,'number','*'),// " int(11) NOT NULL DEFAULT '600'",
  'res_delay'   =>  array(660,'number','*'),// " int(11) NOT NULL DEFAULT '660'",

);

$config['group_ordering'] = array(
'shopconfig_user_activate'   =>  array(0,'select','*',  array('-1'=>con('act_restrict_cart'),
             '0'=>con('act_restrict_all'),
             '1'=>con('act_restrict_later'),
             '2'=>con('act_restrict_w_guest'),
             '3'=>con('act_restrict_quest_only'))),//" tinyint(4) NOT NULL DEFAULT '0'",
'shopconfig_maxres'          =>  array(10,'number','*'),//" int(11) NOT NULL DEFAULT '10'",
'shopconfig_maxorder'        =>  array(14,'number','*'), // " int(11) NOT NULL DEFAULT '14'",
'shopconfig_altpayment_time' =>  array(2,'number','*'),//" varchar(20) NOT NULL DEFAULT '2'",
'shopconfig_restime' => array(0,'number','*'),
'shopconfig_restime_remind' => array(0,'hidden'),
'shoppos_settings' => array(null,'hr','-'),
'shoppos_allow_without_fee' =>  array(0,'yesno'),
'shoppos_allow_without_cost' =>  array(0,'yesno'),
'posuser_activate' => array(0,'yesno'),
);

$config['group_upload'] = array(
  'allowed_uploads'    =>  array(null,'text','#@'),
//  'allowed_images'     =>  array(null,'area','#@',array('rows'=>5,'cols'=>92)),
  'allowed_other'      =>  array(null,'text','#@'),
  'file_mode'          =>  array(null,'text'),
  'allowed_maxsize'    =>  array(9000,'number'),
  'allowed_maxwidth'   =>  array(200,'number'),
  'allowed_maxheight'  =>  array(200,'number'),
  );

/*
$config['group_login'] = array(
  'bot_protection_key'   =>  array(null,'text'),
  'bot_protection_email' =>  array(null,'text'),
  'bot_protection_diag'  =>  array(0,'yesno'),
  'max_login_attempts'   =>  array(5,'number')
  );
*/

$config['group_mail'] = array(
  'mail_smtp_host'   =>  array(null,'text'),
  'mail_smtp_user'   =>  array(null,'text'),
  'mail_smtp_pass'   =>  array(null,'password'),
  'mail_smtp_port'   =>  array(null,'number'),
  'mail_smtp_security'  =>  array(null,'select','', array(''=>'smtp_security_none', 'ssl'=>'smtp_security_ssl', 'tls'=>'smtp_security_tls'))
  );

$config['group_language'] = array(
  'langs'            =>  array(null,'text','#@'),
  'langs_names'      =>  array(null,'area','#@',array('rows'=>5,'cols'=>92)),
  'AutoDefineLangs'  =>  array(null,'yesno','*'),

);
$config['group_webshop_ads'] = array(
  'google_ad_client' => array('pub-7366313208188286','text'),
  'google_ad_slot_left' =>array('5400276806','text'),
  'google_ad_slot_right' =>array('1716207720','text'),
  'hideGoogleAds' => array('false', 'hidden'),
);
$config['group_extensions'] = array(
  'enable_multi_country' => array(0,'hidden'),
  'enable_extendedlinks' => array(0,'hidden'),
  'shoppos_allow_without_fee'=> array(0,'hidden'),
  'shoppos_allow_without_cost'=> array(0,'hidden'),
);

/*

$config['group_versioncheck'] = array(
'shopconfig_proxyaddress' => array(0,'hidden'), // " varchar(300) NOT NULL DEFAULT ''",
'shopconfig_proxyport' => array(0,'hidden'), // " int(5) DEFAULT NULL",
'shopconfig_ftusername' => array(0,'hidden'), // " varchar(60) NOT NULL DEFAULT ''",
'shopconfig_ftpassword' => array(0,'hidden'), // " varchar(100) NOT NULL DEFAULT ''",
'shopconfig_keepdetails' => array(0,'hidden'), // " tinyint(1) NOT NULL DEFAULT '0'",
);
*/
?>