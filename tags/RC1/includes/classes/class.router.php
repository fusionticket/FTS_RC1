<?php
/*********************** %%%copyright%%% *****************************************
 *
 * FusionTicket - ticket reservation system
 * Copyright (C) 2007-2008 Christopher Jenkins. All rights reserved.
 *
 * Original Design:
 *	phpMyTicket - ticket reservation system
 * 	Copyright (C) 2004-2005 Anna Putrino, Stanislav Chachkov. All rights reserved.
 *
 * This file is part of fusionTicket.
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
 *
 * The "GNU General Public License" (GPL) is available at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * Contact info@noctem.co.uk if any conditions of this licencing isn't
 * clear to you.
 */

/**
 * Contains the dispatcher-class. Here is where it all starts.
 *
 */
if(function_exists("date_default_timezone_set") and
   function_exists("date_default_timezone_get")) {
  @date_default_timezone_set(@date_default_timezone_get());
}

require_once ( dirname(dirname(__FILE__)).'/config/defines.php' );
require_once ( INC.'config'.DS."init_common.php");


header('X-Powered-By: FusionTicket/'.CURRENT_VERSION);
if (file_exists(LIBS.'FirePHPCore'.DS.'fb.php' )) {
  require_once ( LIBS.'FirePHPCore'.DS.'fb.php' );
  require_once ( LIBS.'FirePHPCore'.DS.'FirePHP.class.php' );
  FB::setEnabled(true);
}


class router {
	/**
	 * placeholer-array for all relevant variables a class may need later on (e.g. controller)
	 * [isAjax] => false (boolean, tells you if view got called with /ajax/)
	 * [url] => array (
	 *       [url] => locations
	 *       [foo] => bar (if url read ?foo=bar)
	 * )
	 * [form] => array (
	 * 	  (all post-variables, automatically dequoted if needed)
	 * )
	 * [controller] => main (name of the controller of this request)
	 * [action] => index (name of the view of this request)
	 * @var array
	 */

  static function draw($page, $module = 'web', $action=null) {
    GLOBAL $_SHOP;
    if (strpos($module,'/') === false) {
      $controller = 'shop';
    } else {
      $controller = substr($module,   strpos($module,'/')+1);
      $module     = substr($module,0, strpos($module,'/'));
    }
    if (strpos($page,'/') !== false) {
      $action = substr($page ,    strpos($page,'/')+1 );
      $page   = substr($page , 0, strpos($page,'/') );
    }
    if (isset($_SERVER["REDIRECT_SHOP_SEO"])) {
      self::unSeoUrl($_SERVER["REDIRECT_SHOP_SEO"]);
    }
    if(is_null($action)){
      if (isset($_POST['action'])) {
        $action=$_POST['action'];
      } elseif (isset($_GET['action'])) {
        $action=$_GET['action'];
      } else
        $action=false;
    }

    $_REQUEST['action'] = $action;
    $_GET['action']     = $action;
    $_POST['action']    = $action;
    //echo $controller,'-',$module, '-',$action;
/*
  	if ($action { 0 } == '_') {
  		throw new Exception('Controller [' . $params['controller'] . '] does not allow execution of action [' . $params['action'] . ']');
  	}
*/

		$classname = 'ctrl'.ucfirst($module).ucfirst($controller);

    trace( $_SERVER["PHP_SELF"]. " [{$action}]", true);
    trace( '====================================================================');

    if (file_exists(INC.'controller'.DS.'controller.'.$module.'.'.$controller.'.php' )) {
      require_once ( INC.'controller'.DS.'controller.'.$module.'.'.$controller.'.php' );
      if (class_exists($classname)) {
        $_SHOP->controller = new $classname($module, $page, $action);
        require_once ( INC.'config'.DS."init.php");
        $_SHOP->controller->draw();
        $isdone = true;
      }else {
        header('HTTP/1.1 501');
        trace("Class {$classname} was not present in file: ".INC.'controller'.DS.'controller.'.$module.'.'.$controller.'.php');
      }
    } else {
      header('HTTP/1.1 501');
      trace("File not found: ".INC.'controller'.DS.'controller.'.$module.'.'.$controller.'.php');
    }
    trace("End of shop \n\n\r");
    orphanCheck();
  }

  function unSeoUrl($seo){
    if ($seo=='none') {
      return;
    }
    $test = unserialize(base64_decodex($seo));
    foreach ($test as $key => $value) {
      $_GET[$key] = $value;
      $_REQUEST[$key] = $value;
    }
  }
}
?>