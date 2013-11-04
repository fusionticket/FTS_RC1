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
 * This define is used to store the passwords, pleace do not change this after
 * there are uses registrated to the system.
 * This this will invalided all given passwords in the system.
 */
if (!defined('ft_check')) {die('System intrusion ');}

  set_error_handler("customError");

  //ini_set('session.save_handler','user');
  //require_once("classes/class.sessions.php");

 // print_r($_SERVER);


  //starting a new session
  if (isset($_GET['AdminSession']) && !$_SHOP->is_admin) {
  	session_name($_GET['AdminSession']);
  	session_start();
  	if ((isset($_SESSION["_SHOP_AUTH_USER_NAME"]) && isset($_SESSION["_SHOP_AUTH_ADMIN_ID"])) &&
	      ($_SESSION["_SHOP_AUTH_ADMIN_ID"]   == $_SESSION["_SHOP_AUTH_USER_DATA"] ["admin_id"]) &&
      	($_SESSION["_SHOP_AUTH_USER_NAME"] == $_SESSION["_SHOP_AUTH_USER_DATA"] ["admin_login"])) {
  		$session_name = $_SHOP->session_name;
  		$_SHOP->session_name = $_GET['AdminSession'];
  		$_SHOP->controller->auth_required = true;
    	$_SHOP->controller->drawAuth(false);
  		$_SHOP->controller->auth_required = false;
  		$_SHOP->session_name = $session_name;
  		session_name($_SHOP->session_name);
  	} else {
    	session_unset();
    	$_SESSION = array();
    	session_destroy();
    	session_name($_SHOP->session_name);

    	session_start();
    }
  } else {
  	session_name($_SHOP->session_name);

  	session_start();

  }

  If (isset($_SHOP->secure_id) and (!isset($_SESSION['_SHOP_SYS_SECURE_ID']) || ($_SHOP->secure_id <> $_SESSION['_SHOP_SYS_SECURE_ID'] ) )) {
    session_unset();
    $_SESSION = array();
    session_destroy(); //echo 'new session_id';
    session_start();
    $_SESSION['_SHOP_SYS_SECURE_ID'] = $_SHOP->secure_id;
  }
	if (isset( $_SESSION['Last_Messages'])) {
		$_SHOP->Messages = $_SESSION['Last_Messages'];
		unset($_SESSION['Last_Messages']);
	}

	if (!loadLanguage('custom')) {loadLanguage('site');}
	$plugs = plugin::loadAll(false);
	foreach ($plugs as $plugin){
		$plugin->loadlanguage();
	}

	// writeLog($old = setlocale(LC_TIME, NULL));

	$loc = con('setlocale_ALL',' ');
	if(!empty($loc)){
		setlocale(LC_ALL, explode(';',$loc));
	}
	$loc = con('setlocale_TIME',' ');
	if(!empty($loc)){
		setlocale(LC_TIME, explode(';',$loc));
	}


//authentifying (if needed)
  require_once "classes/class.secure.php";
  $accepted = Secure::CheckTokens();
  if (!$accepted) {
     $tokens = print_r($_SESSION['tokens'], true);
     writeLog('% Tokens '.(($tokens)?$tokens:'NOT FOUND !!!'));
//     writeLog("% Token {$name}, {$value}, {$testme}");
     writeLog('% used IP: '.getIpAddress());
     writeLog(print_r($_SERVER,true));
     writeLog(print_r($_ENV,true));
     writeLog('     ---------------------------------------------------');

     orphancheck();
     session_unset();
     session_destroy();
     $string = "<h1>Access Denied</h1>";
     $string .= "<p><strong>Why?</strong> :- Please check you submitted a form within the same domain (website address).</p>";
     $string .= "<p><strong>Or</strong> :- Your session does not match your url.</p>";
     $string .= "<p>Please check your cookie settings and turn it on.</p>";

     die($string);
  }

	if($_SHOP->system_online=='0'){
		$thisfolder = dirname(__FILE__);
		if(!$_SHOP->is_admin && !isset($_SHOP->admin)){
			if (file_exists($thisfolder.DS.'maintenance.html')) {
				include($thisfolder.DS.'maintenance.html');
			} elseif (file_exists($thisfolder.DS.'maintenance.php')) {
				include($thisfolder.DS.'maintenance.php');
			} else
			echo "<center>
			      <h1>{$_SHOP->offline_message}</h1>
	          <h3>Please return later</h3>
	          </center>";
			exit;
		} else {
				addWarning("site_offline_message");// die('testing');
		}
	}


  // check the order system for outdated orders and reservations
  check_system();


  //ini_set("session.gc_maxlifetime", [timeinsec]);
  plugin::call('doSessionStarted');
?>