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

	/**
	 * Default Configuration Variables
	 *
	 * This file should not be changed. If you want to override any of the values
	 * defined here, define them in a file called init_config.php, which will
	 * be loaded after this file.
	 *
	 * In general a value of OFF means the feature is disabled and ON means the
	 * feature is enabled.  Any other cases will have an explanation.
   **/
 error_reporting(E_ALL ^ E_NOTICE);

if (!defined('ft_check')) {die('System intrusion ');}

  header('Content-Type: text/html; charset=utf-8');

  global $_SHOP;
  if(function_exists("mb_internal_encoding")) {
    mb_internal_encoding("UTF-8");
  }

  ini_set('memory_limit','64M');
  ini_set('magic_quotes_runtime', 0);
  ini_set('allow_call_time_pass_reference', 0);

  // mb_

  //check if the site is online
  require_once('defines.php');
  require_once(INC.'install'.DS."install_version.php");
  require_once("classes/basics.php");

  if(!isset($_SHOP)) $_SHOP = new stdClass();

  $_SHOP->jScript ='';
  $_SHOP->jFiles = array();
  /**
   * Minimum PHP version; can't call PMA_fatalError() which uses a
   * PHP 5 function, so cannot easily localize this message.
   */
  if (version_compare(PHP_VERSION, '5.2.0', 'lt')) {
    die('PHP 5.2+ is required');
  }

  /**
   * Backward compatibility for PHP 5.2
   */
  if (!defined('E_DEPRECATED')) {
    define('E_DEPRECATED', 8192);
  }

 /*
     * This setting was removed in PHP 5.3. But at this point PMA_PHP_INT_VERSION
     * is not yet defined so we use another way to find out the PHP version.
  */
  if (version_compare(phpversion(), '5.3', 'lt')) {
    /**
     * Avoid object cloning errors
     */
    @ini_set('zend.ze1_compatibility_mode', false);
  }

  /**
   * This setting was removed in PHP 5.4. But at this point PMA_PHP_INT_VERSION
   * is not yet defined so we use another way to find out the PHP version.
   */
  if (version_compare(phpversion(), '5.4', 'lt')) {
    /**
     * Avoid problems with magic_quotes_runtime
     */
    @ini_set('magic_quotes_runtime', false);
  }

  /**
   * protect against possible exploits - there is no need to have so much variables
   */
  if (count($_REQUEST) > 1000) {
    die(con('possible_exploit'));
					 }

  /**
   * Check for numeric keys
   * (if register_globals is on, numeric key can be found in $GLOBALS)
   */
  foreach ($GLOBALS as $key => $dummy) {
    if (is_numeric($key)) {
      die(con('numeric_key_detected'));
			 }
		}
  unset($dummy);

/**
 * PATH_INFO could be compromised if set, so remove it from PHP_SELF
 * and provide a clean PHP_SELF here
 */
/*
echo  $FT_PHP_SELF = getenv('PHP_SELF');
echo ' - ';
echo  $_PATH_INFO  = getenv('PATH_INFO');
  if (! empty($_PATH_INFO) && ! empty($PMA_PHP_SELF)) {
    $path_info_pos = strrpos($PMA_PHP_SELF, $_PATH_INFO);
    if ($path_info_pos + strlen($_PATH_INFO) === strlen($FT_PHP_SELF)) {
      $FT_PHP_SELF = substr($FT_PHP_SELF, 0, $path_info_pos);
    }
	}
  echo ' - ';
  echo  $_SERVER['PHP_SELF'] = htmlspecialchars($FT_PHP_SELF);
*/

  /**
   * just to be sure there was no import (registering) before here
   * we empty the global space (but avoid unsetting $variables_list
   * and $key in the foreach (), we still need them!)
*/
  $variables_whitelist = array (
      'GLOBALS',
      '_SERVER',
      '_GET',
      '_POST',
      '_REQUEST',
      '_FILES',
      '_ENV',
      '_COOKIE',
      '_SESSION',
      '_SHOP',
      'variables_whitelist',
      'key'
  );

  foreach (get_defined_vars() as $key => $value) {
    if (! in_array($key, $variables_whitelist)) {
      unset($$key);
    }
  }
  unset($key, $value, $variables_whitelist);
    $_REQUEST = array_merge($_GET, $_POST);

  //emulates magic_quotes_gpc off
  if (get_magic_quotes_gpc()) {
    function stripslashes_deep($value) {
      if(is_array($value)) {
        foreach($value as $k => $v) {
          $return[$k] = stripslashes_deep($v);
        }
      } elseif(isset($value)) {
        $return = stripslashes($value);
      }
      return $return;
    }
    $_POST    = array_map('stripslashes_deep', $_POST);
    $_GET     = array_map('stripslashes_deep', $_GET);
    $_COOKIE  = array_map('stripslashes_deep', $_COOKIE);
    $_REQUEST = array_map('stripslashes_deep', $_REQUEST);
  }

  if (!file_exists(INC.'config'.DS."init_config.php")){
    echo "<a href='".constructBase(null,true)."inst/index.php'>Install me now!</a>";
    exit;
  }



  //Shopping cart and place reservation delay
  //how many times the place can stay reserved
//  $_SHOP->res_delay=660;

  //the same value for the shopping cart, usually smaller
//  $_SHOP->cart_delay=$_SHOP->res_delay-60;


//  $_SHOP->dir_mode=0755;
  $_SHOP->file_mode=0644;

  $_SHOP->install_dir =ROOT;
  $_SHOP->includes_dir=INC;

	//where uploaded files lives (event images, ..)
	//should be writable
  $_SHOP->files_dir=ROOT."files".DS;

  //this folder contains font files required by pdf templates
  //it should be writable by php
  $_SHOP->font_dir=INC."fonts".DS;

  //temporary folder
  //should be writeable by php
  $_SHOP->tmp_dir=INC."temp".DS;

  	//Where templates are stored..
	$_SHOP->templates_dir=INC."temp".DS;

  //where smarty templates and other tpl related stuff lives
  $_SHOP->tpl_dir=INC."template".DS;

  //Trace File settings

  $_SHOP->trace_name = 'trace.log';
  $_SHOP->trace_on   = 'ALL';

  // this selects the theme that you like to use.
  $_SHOP->theme_name = "default";
	//default paper size and orientation for pdf files
	//paper size: 'a4', 'legal', etc..or  array(x0,y0,x1,y1), in points
  //or  array(width,height), in centimeters
	//paper orientation: portrait, landscape
	//see ezpdf docs (readme.pdf) for possible values
  $_SHOP->pdf_paper_size="A4";
  $_SHOP->pdf_paper_orientation="portrait";



  //external url connection settings, used by connect_func.php
  //choose one of settings:

  //1.use libCurl (php should be compiled with libCurl)
  $_SHOP->url_post_method='libCurl';

  //2.use php function fsocketopen():
  //$_SHOP->url_post_method='fso';

  //3.use external curl command:
  //$_SHOP->url_post_method='curl';
  //$_SHOP->url_post_curl_location='/usr/bin/curl';

  $_SHOP->input_time_type = 24; //12; //
  $_SHOP->input_date_type = 'dmy'; // 'mdy'


	//accepted languages
	$_SHOP->langs=array('en');
	$_SHOP->langs_names=array('en'=>'English');

	$_SHOP->is_admin = false;
	$_SHOP->event_type_enum = array('','classics','jazz','blues','funk','pop','rock','folklore','theater','sacred','ballet',
                                  'opera','humour','music','other','cinema','party','exposition');
  $_SHOP->event_group_type_enum = array('','festival','tournee','theatre');

  $_SHOP->mail_smtp_host = null;
  $_SHOP->mail_smtp_port = null;
  $_SHOP->mail_smtp_user = null;
  $_SHOP->mail_smtp_pass = null;
  $_SHOP->mail_smtp_security = null; // ""


  $_SHOP->mail_sendmail  = null;

  $_SHOP->valutas  = array( 'EUR' => '&euro;',
                            'AUD' => '&#36;',
                            'CAD' => '&#36;',
                            'USD' => '&#36;',
                            'SGD' => '&#36;',
                            'NZD' => '&#36;',
                            'GBP' => '&pound;',
                            'JPY' => '&yen;');
  $_SHOP->allowed_uploads = array('jpg', 'jpeg', 'png', 'gif', 'mp3' );

  require_once(INC.'config'.DS."init_config.php");


  //$_SHOP->root_base is allways the root and doenst add /pos/ /admin etc
  $_SHOP->root_base = constructBase(null,true);

	//Check if version is uptodate
	// echo INC,'- ', CURRENT_VERSION,';', INSTALL_VERSION;
	if (!defined('CURRENT_VERSION')) {
		define('CURRENT_VERSION','Unknown');
	}
	if (CURRENT_VERSION <> INSTALL_VERSION){
		$_SHOP->offline_message = "<a href='{$_SHOP->root_base}inst/index.php'>Upgrade me now!</a>";
		if (file_exists($thisfolder.DS.'maintenance.php')) {
			include($thisfolder.DS.'maintenance.php');
		} else
			echo "<center>
			      <h1>{$_SHOP->offline_message}</h1>
	          </center>";
		exit;
	}

  require_once("classes/class.shopdb.php");
  require_once("classes/class.model.php");

  ShopDB::init();

  Config::ApplyConfig(true);



  $_SERVER['PHP_SELF']   = clean($_SERVER['PHP_SELF']   ,'HTML');
  $_SERVER['REQUEST_URI']= clean($_SERVER['REQUEST_URI'],'HTML');

  if (isset($_SERVER['SCRIPT_URI'])) {

    $_SERVER['SCRIPT_URI'] = clean($_SERVER['SCRIPT_URI'] ,'HTML');
  }
  if (isset($_SERVER['SCRIPT_URL'])) {
    $_SERVER['SCRIPT_URL'] = clean($_SERVER['SCRIPT_URL'] ,'HTML');
  }

  if (!defined('PHP_SELF')) {
    define('PHP_SELF',$_SERVER['PHP_SELF']);
  }
  //Construct $_SHOP
  $_SHOP->Messages = array();

  if (!isset($_SHOP->root)) $_SHOP->root = constructBase(false);//
  if (!isset($_SHOP->root_secured)) $_SHOP->root_secured = constructBase(isset($_SHOP->secure_site) && $_SHOP->secure_site);
  if ( strtoupper(substr($_SHOP->root_secured,0,5))=='HTTP:' ){
    $_SHOP->secure_site = '0';
  }

  $_SHOP->root_base = constructBase(null,true);
	$_SHOP->root_clear = $_SHOP->root_base; //substr($_SHOP->root,strpos($_SHOP->root_base,':' )+1 );

  $_SHOP->files_url = $_SHOP->root_base."files/";
  $_SHOP->images_url= $_SHOP->root_base."images/";

  $_SHOP->theme_dir = $_SHOP->tpl_dir . "theme".DS.$_SHOP->theme_name.DS;
  if (!isset($_SHOP->trace_dir)) $_SHOP->trace_dir = $_SHOP->tmp_dir ;

  if(function_exists("date_default_timezone_set")) {
    @date_default_timezone_set($_SHOP->timezone);
  }
  @ini_set('date.timezone', $_SHOP->timezone);

  if (!isset($_SHOP->software_updater_enabled)){
    $_SHOP->software_updater_enabled = false;
  }
?>