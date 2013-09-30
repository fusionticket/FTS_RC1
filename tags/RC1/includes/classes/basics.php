<?php
/**
%%%copyright%%%
 *
 * FusionTicket - ticket reservation system
 *  Copyright (C) 2007-2013 FusionTicket Solution Limited . All rights reserved.
 *
 * Original Design:
 *  phpMyTicket - ticket reservation system
 *   Copyright (C) 2004-2005 Anna Putrino, Stanislav Chachkov. All rights reserved.
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

/**
 * @author Chris Jenkins
 * @copyright 2008
 */
/**
 * some often used constants that should be part of PHP
 */
define('SECOND', 1);
define('MINUTE', 60 * SECOND);
define('HOUR', 60 * MINUTE);
define('DAY', 24 * HOUR);
define('WEEK', 7 * DAY);
define('MONTH', 30 * DAY);
define('YEAR', 365 * DAY);

/**
 * the two error-level constants
 */
define('FT_DEBUG', 2);
define('FT_ERROR', 1);

function diff_date($date1, $date2){
  $date1 = (is_int($date1))?$date1:strtotime($date1);
  $date2 = (is_int($date2))?$date1:strtotime($date2);
  return round(($date1-$date2)/60/60/24);
}

/**
 * print out type and content of the given variable if DEBUG-define (in config/core.php) > 0
 * @param mixed $var     Variable to debug
 * @param boolean $escape  If set to true variables content will be html-escaped
 */
function debug($var = false, $escape = false)
{
   if (DEBUG > 0) {
      print '<pre class="debug">';
      $var = print_r($var, true);
      if ($escape) {
         $var = htmlspecialchars($var);
      }
      print $var . '</pre>';
   }
}

/**
 * Recursively strips slashes from all values in an array
 * @param mixed $value
 * @return mixed
 *
function stripslashes_deep($value)
{
   if (is_array($value)) {
      return array_map('stripslashes_deep', $value);
   } else {
      return stripslashes($value);
   }
}
/**
 * Recursively urldecodes all values in an array
 * @param mixed $value
 * @return mixed
 */
function urldecode_deep($value)
{
   if (is_array($value)) {
      return array_map('urldecode_deep', $value);
   } else {
      return urldecode($value);
   }
}

/** write a string to the log in tmp/logs
 *@param string $what string to write to the log
 *@param int $where log-level to log (default: KATA_DEBUG)
 */
function writeLog($what, $where = FT_DEBUG){
  Global $_SHOP;

   if ($where < 0) { return; }
   if (empty($_SHOP->tmp_dir)) {
     print_r( debug_backtrace());
   }
   $logname = 'error';
   if ($where == FT_DEBUG) {
      $logname = 'debug';
   }
   $errorfield = 'filefor_'.$logname;
   if (isset($_SHOP->$errorfield)) {
     $file = $_SHOP->$errorfield;
   } else {
     $file = $_SHOP->tmp_dir . $logname.'.'.@date('Y-m-d') . '.log';
   }

   if (!isset($_SHOP->hasloged[$where])){
     $what = date('d.m.Y H:i --- ').$_SERVER['REQUEST_URI'] ." --------------------------------\n". $what;
     $_SHOP->hasloged[$where] =1;
   }
   $h = fopen($file, 'a');
   if ($h) {
      fwrite($h,utf8_encode($what . "\n"));
      fclose($h);
   }
}

/**
 * Loads files from the from LIB-directory
 * @param string filename without .php
 */
function uses()
{
  $args = func_get_args();
  foreach ($args as $arg) {
    if (!class_exists($arg)) {
      require_once (CLASSES . strtolower($arg) . '.php');
    }
  }
}

function FindClass(&$class_name) {
  $class_name = strtolower($class_name);
//  echo CLASSES . 'model.'. $class_name . '.php','|';
  If (file_exists(CLASSES . $class_name . '.php')) {
    return CLASSES ;
  }  elseIf (file_exists(CLASSES . 'class.'. $class_name . '.php')) {
    $class_name = 'class.'. $class_name;
    return CLASSES;
  }  elseIf (file_exists(CLASSES . 'model.'. $class_name . '.php')) {
    $class_name = 'model.'. $class_name;
    return CLASSES;
  } elseIf (file_exists(CLASSES . 'payments' . DS . $class_name . '.php')) {
    return CLASSES . 'payments' . DS;
  }
}

/*
function __autoload ($class_name ) {
//  echo $class_name ,'~';
  $class_name = strtolower($class_name);
  If ($path = FindClass($class_name)) {
     require ($path . $class_name . '.php');
  }
}
*/
function autoLoader ($class_name ) {
//  echo $class_name ,'|';
  $class_name = strtolower($class_name);
  If ($path = FindClass($class_name)) {
     require ($path . $class_name . '.php');
  }
}

spl_autoload_register('autoLoader');
/**
 * Gets an environment variable from available sources.
 * Used as a backup if $_SERVER/$_ENV are disabled.
 *
 * @param  string $key Environment variable name.
 * @return string Environment variable setting.
 */
function env($key){
   if ($key == 'HTTPS') {
      if (isset($_SERVER) && !empty($_SERVER)) {
         return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on');
      } else {
         return (strpos(env('SCRIPT_URI'), 'https://') === 0);
      }
   }

   if (isset($_SERVER[$key])) {
      return $_SERVER[$key];
   } elseif (isset($_ENV[$key])) {
      return $_ENV[$key];
   } elseif (getenv($key) !== false) {
      return getenv($key);
   }

   if ($key == 'DOCUMENT_ROOT') {
      $offset = 0;
      if (!strpos(env('SCRIPT_NAME'), '.php')) {
         $offset = 4;
      }
      return substr(env('SCRIPT_FILENAME'), 0, strlen(env('SCRIPT_FILENAME')) -
                                               (strlen(env('SCRIPT_NAME')) + $offset));
   }
   if ($key == 'PHP_SELF') {
      return r(env('DOCUMENT_ROOT'), '', env('SCRIPT_FILENAME'));
   }
   return null;
}


function constructBase($secure=null, $useroot=false) {
  if ($secure == null) $secure = env('HTTPS');
  $dir  = dirname(env('PHP_SELF'));
  $file = basename($dir);
  if ((($file=='admin') ||  ($file=='pos') ||  ($file=='control')) && $useroot ) {
    $dir = dirname($dir);
  }
  $dir = str_replace('\\','/' , $dir);
  $base = 'http' . (($secure) ? 's' : '') . '://' . env('SERVER_NAME');
  // thanks to Nasi it will now also works with different port numbers
  if (!in_array(env('SERVER_PORT'), array('80', '443'))) {
		$base .= ':' . env('SERVER_PORT');
	}
	$base .= $dir;
  if (substr($base, -1, 1) != '/') {
    $base .= '/';
  }
  return $base;
}

function isValidURL($url)
{
  return preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $url);
}

function isValidURLEx($url)
{
  // SCHEME
  $urlregex = "^(https?)\:\/\/"; // |ftp

  // USER AND PASS (optional)
 // $urlregex .= "([a-z0-9+!*(),;?&=\$_.-]+(\:[a-z0-9+!*(),;?&=\$_.-]+)?@)?";

  // HOSTNAME OR IP
  $urlregex .= "[a-z0-9+\$_-]+(\.[a-z0-9+\$_-]+)*"; // http://x = allowed (ex. http://localhost, http://routerlogin)
   //$urlregex .= "[a-z0-9+\$_-]+(\.[a-z0-9+\$_-]+)+"; // http://x.x = minimum
   //$urlregex .= "([a-z0-9+\$_-]+\.)*[a-z0-9+\$_-]{2,3}"; // http://x.xx(x) = minimum
   //use only one of the above

   // PORT (optional)
   $urlregex .= "(\:[0-9]{2,5})?";
  // PATH (optional)
  $urlregex .= "(\/([a-z0-9+\$_-]\.?)+)*\/?";
  // GET Query (optional)
  $urlregex .= "(\?[a-z+&\$_.-][a-z0-9;:@/&%=+\$_.-]*)?";
  // ANCHOR (optional)
  $urlregex .= "(#[a-z_.-][a-z0-9+\$_.-]*)?\$";

  // check
  return (eregi($urlregex, $url));
  }

/**
 * Merge a group of arrays
 * @param array First array
 * @param array etc...
 * @return array All array parameters merged into one
 */
function am() {
   $result = array();
   foreach (func_get_args() as $arg) {
      if (!is_array($arg)) {
         $arg = array($arg);
      }
      $result = array_merge($result, $arg);
   }
   return $result;
}


/**
 * Convenience method for htmlspecialchars. you should use this instead of echo to avoid xss-exploits
 * @param string $text
 * @return string
 */
function h($text)
{
   if (is_array($text)) {
      return array_map('h', $text);
   }
   return htmlspecialchars($text);
}

/**
 * convenience method to check if given value is set. if so, value is return, otherwise the default
 * @param mixed $arg value to check
 * @param mixed $default value returned if $value is unset
 */
function is(&$arg, $default = null)
{
   if (isset($arg)) {
      return $arg;
   }
   return $default;
}


function empt(&$arg , $default=null){
  if(is_string($arg)){
    $arg=trim($arg);
    if(!empty($arg)){
      return $arg;
    }
  }elseif(empty($arg)){
    return $default;
  }else{
    return $arg;
  }
  return $default;
}


/**
 * loadLanguage() show translation text
 *
 * @param string $name the name of the definded language constant
 * @param string $default default value when $name is not defined.
 * @return
 */
function loadLanguage($model='site', $nameonly=false){
global $_SHOP;
   $newlang = '';
  //loading language file
  if (isset($_SHOP->lang)) {
    // do noting
    $newlang=$_SHOP->lang;
  } elseif(isset($_REQUEST['setlang']) ) {
    if ($lang=$_REQUEST['setlang'] and in_array($lang,$_SHOP->langs)){
      //  setcookie("lang",$lang,time()+60*60*24*30);
      $newlang=$lang;
      $_SESSION['_SHOP_LANG']=$newlang;

      If (isset($_REQUEST['href'])) {
        Redirect($_REQUEST['href']);
        die;
      }
    }
  }elseif(isset($_SESSION['_SHOP_LANG'])){
    $newlang=$_SESSION['_SHOP_LANG'] ;
  }elseif(isset($_SERVER["HTTP_ACCEPT_LANGUAGE"]) && $_SERVER["HTTP_ACCEPT_LANGUAGE"]){

    $lpat=implode($_SHOP->langs,"|");
    if(preg_match_all("/$lpat/",$_SERVER["HTTP_ACCEPT_LANGUAGE"],$res )){
      $langid = 9999;
      foreach ($res[0] as $lang) {
        $x =  array_search($lang, $_SHOP->langs );
        if (file_exists(INC."lang".DS."{$model}_". $lang.".inc")) {
          $newlang = $lang;
          break;
        }
      }
    }
  }
  if ($newlang) {
    $_SHOP->lang=$newlang;
  }else{
    $_SHOP->lang=$_SHOP->langs[0];

  }
  $_SESSION['_SHOP_LANG']=$_SHOP->lang;
  $return = false;

  if ($nameonly) {
    return  INC."lang".DS."{$model}_". $_SHOP->lang.".inc";
  } else {
//    $error = error_reporting(0);
    if (file_exists(INC."lang".DS."{$model}_". $_SHOP->lang.".inc")){
      if (file_exists(INC."lang".DS."{$model}~custom_". $_SHOP->lang.".inc")){
        include_once(INC."lang".DS."{$model}~custom_". $_SHOP->lang.".inc");
      }
      include_once(INC."lang".DS."{$model}_". $_SHOP->lang.".inc");
      $_SHOP->langfile = INC."lang".DS."{$model}_". $_SHOP->lang.".inc";
      $return = true;
    }
    if ($_SHOP->lang<>'en' and file_exists(INC."lang".DS."{$model}_en.inc")) {
      if (file_exists(INC."lang".DS."{$model}~custom_en.inc")){
        include_once(INC."lang".DS."{$model}~custom_en.inc");
      }
      include_once(INC."lang".DS."{$model}_en.inc");
    }
//    error_reporting($error);
  }
  return $return;
}

function con($name, $default='') {
  global $_SHOP;
  if (defined($name)) {
    return constant($name);
  } elseif ($default) {
    return $default;
  } elseif ($name && preg_match('|^[a-z]+[a-z0-5_-]+$|', $name) ) {
    if (is($_SHOP->AutoDefineLangs, false)) {
  //    echo loadLanguage('site', true);
      if (is_writable(loadLanguage('site', true))){
        $namex= _esc($name, false);
        $addcon = "\n<?php\ndefine('{$namex}','!!{$namex}!!');\n?>";
        file_put_contents(loadLanguage('site', true), $addcon, FILE_APPEND);
        define($name,'!!'.$name.'!!');
      }// else echo "****$name|".print_r( debug_backtrace(),true).'|';
    }
    return '!!'.$name.'!!';
  }
    return $name;
}

/**
 * redirect to the given url. if relative the base-url to the framework is added.
 * @param string url to redirect to
 * @param int status http status-code to use for redirection (default 303=get the new url via GET even if this page was reached via POST)
 */
function Redirect($url, $status = 303, $message='') {
  GLOBAL $_SHOP;

  $_SESSION['Last_Messages'] = $_SHOP->Messages;

  if (function_exists('session_write_close')) {
    session_write_close();
  }
  @ob_end_clean();
  $pos = strpos($url, '://');
  if ($pos === false) { // is relative url, construct rest
    $url = $_SHOP->root . $url;
  }
  if ($status===true) {
    echo   "<script type=\"text/javascript\" language=\"JavaScript\">\nwindow.location='".trim($url)."';\n</script>";
  }else{
    if (is_numeric($status) && ($status >= 100) && ($status < 505)) {
      header('HTTP/1.1 ' . $status);
    }
    header('Location: ' . $url);
  }
  exit;
}

/**
 * _esc()
 *
 * @param mixed $str
 * @param mixed $quote
 * @return
 */
function _esc ($str, $quote=true){
  return shopDB::quote($str, $quote);
}

/**
 * check_event()
 *
 * @param mixed $event_date
 * @return
 */
function check_event($event_date){
  require_once("classes/class.time.php");

  global $_SHOP;
  if($_SHOP->shopconfig_altpayment_time>0){
    $time=Time::StringToTime($event_date);
    $remain=Time::countdown($time);
   // echo $remain["justmins"]."-".$_SHOP->shopconfig_altpayment_time;
    if($remain["justmins"]<=($_SHOP->shopconfig_altpayment_time)){
      return 1;
    }else{
      return 0;
    }
  }
}

/**
 * check_system()
 *
 * @return
 */
function check_system() {
  global $_SHOP;
  require_once("classes/class.time.php");

  $query="SELECT UNIX_TIMESTAMP() as current_db_time";
  $res=ShopDB::query_one_row($query);
 // var_dump($_SHOP,$res);

  if ((int)$_SHOP->shopconfig_lastrun_int == 0) {
      return;
  } elseif ( $res['current_db_time'] <= (int) $_SHOP->shopconfig_lastrun) {
      return;
  }
  $_SHOP->silintAbort = true;

  plugin::call('doSystemCleanup');
  //print_r('run');
  //Checks to see if res time is enabled anything more than 9 will delete
  if ((int) $_SHOP->shopconfig_restime >= 1 ) {

    $query = "SELECT order_id FROM `Order`
              WHERE order_status = 'res'
              AND order_payment_status  = 'none'
              AND order_shipment_status = 'none'
              AND order_date_expire <= NOW()";
    if ( $_SHOP->shopconfig_check_pos == 0 ) {
      $query .= " AND order_place != 'pos' ";
    }
    if ( $res = ShopDB::query($query) ) {
      while ( $row = ShopDB::fetch_assoc($res) ) {
        Order::delete( $row['order_id'], 'autocancel_order', true);
      }
    }
  }

  if ( $_SHOP->shopconfig_delunpaid == 1) {
    $query = "SELECT order_id, order_place
              FROM `Handling` left join `Order` on order_handling_id = handling_id
              WHERE handling_expires_min >= 1
              AND order_date_expire IS NOT NULL
              AND order_date_expire <= NOW()
              AND order_status = 'ord'
              AND order_payment_status  = 'none'
              AND order_shipment_status != 'send'";

    if($resultOrder=ShopDB::query($query)){
      //Cycles through orders to see if they should be cancelled!
      while ( $roword = ShopDB::fetch_assoc($resultOrder) ) {
        if ( !Order::Check_payment($roword['order_id'])) {
          if ( $_SHOP->shopconfig_delunpaid_pos == 1 or $roword['order_place'] != 'pos'){
            Order::delete( $roword['order_id'], 'autocancel_paying', true);
          }
        }
      }
    }
  }

  $time=time();
  $query="UPDATE Seat SET
            seat_status='free',
            seat_ts=NULL,
            seat_sid=NULL
         where seat_status='res'
         and seat_ts<"._esc($time);
  ShopDB::query($query);

  config::updateFile('shopconfig_lastrun',"CONCAT('i:',UNIX_TIMESTAMP(TIMESTAMPADD( MINUTE , {$_SHOP->shopconfig_lastrun_int}, now( ) )),';')",false);
  //    echo "store";
  $_SHOP->silintAbort = false;
  return true;

}

/**
 * formatDate()
 *
 * @param mixed $edate
 * @param string $format
 * @return
 */
function formatDate($edate, $format="%m/%d/%Y" ){
  $pdate = strftime ($format, strtotime($edate));
  If ($pdate == false) {
    $format = str_replace('%e','%d',$format);
    $pdate = strftime ($format, strtotime($edate));
  }
  return $pdate;
 }

/**
 * formatAdminDate()
 *
 * @param mixed $edate
 * @param mixed $year4
 * @return
 */
function formatAdminDate($edate,$year4=true){
  if (empty($edate) or $edate=='0000-00-00') return '0000-00-00';
  $format =(defined('admin_date_format'))?constant('admin_date_format'):(($year4)?'Y':'y').'-m-d';
  if (@intval($edate)<=3000) {
   $edate = strtotime($edate);
  }
  return date($format,$edate);
}

function formatAdminTime($edate){
  if (empty($edate)) return '00:00';
  $format =(defined('admin_time_format'))?constant('admin_time_format'):'H:i';
  if (@intval($edate)<=3000) {
    $edate = strtotime($edate);
  }
  return date($format,$edate);
}
function formatAdminDatetime($edate,$year4=true){
  if (empty($edate) or $edate=='0000-00-00 00:00:00') return '0000-00-00 00:00';
  $format =(defined('admin_datetime_format'))?constant('admin_datetime_format'):'Y-m-d H:i';
  if (@intval($edate)<=3000) {
    $edate = strtotime($edate);
  }
  return date($format,$edate);
}

/**
 * formatTime()
 *
 * @param mixed $time
 * @return
 */
function formatTime($time){
  list($h,$m,$s)=explode(":",$time);

  if(strlen($h) or strlen($m)){
    //return strftime("%X",mktime($h,$m));
    return $h."h".$m;
  }
}

/**
 * stringDatediff()
 *
 * @param mixed $datefrom
 * @param mixed $dateto
 * @return
 */
function stringDatediff($datefrom, $dateto) {
   $datefrom   = strtotime($datefrom, 0);
   $dateto     = strtotime($dateto, 0);

   $difference = $dateto - $datefrom; // Difference in seconds
   return $difference;
}

/**
 * subtractDaysFromDate()
 *
 * @param mixed $date
 * @param mixed $no_days
 * @return
 */
function subtractDaysFromDate($date,$no_days) {
  $time1  = strtotime($date);
  $res = strtotime((date('Y-m-d', $time1)." -$no_days"."days"));
  return date('Y-m-d', $res);
}

/**
* trace()
*
* Will print full traces to file when enabled in config_common.php
*
* Tempted to remove dblogging and do both in trace function.
* Like trace($content,$dblog=false){}
*
* @param mixed $content
* @return void
*/
function trace($content, $addDate=false, $addtrace=false){
  global $_SHOP;

  if(is($_SHOP->trace_on,false)){
    if ($addtrace){
      $traceArr = debug_backtrace(false);
      if(isset($traceArr) && count($traceArr) > 2) {
        $x = (strpos('shopdb.', $traceArr[1]['file'])===false)?1:2;
        $errString = '=> '.basename($traceArr[$x]['file']).' '.$traceArr[$x]['line'].':';
        $content = $errString ."\n". $content;
      }
    }
    if ($addDate){
      $_SHOP->trace_subject = 'ErrorLog: '.$_SHOP->root.$content;
      $_SHOP->tracelog = '';
      if(is($_SHOP->trace_on,'') != 'TRACEONLY') {
        if (file_exists($_SHOP->trace_dir.'last_orpanckeck.log')){
          $_SHOP->TraceOrphan = file_get_contents($_SHOP->trace_dir.'last_orpanckeck.log');
        } else {
          $_SHOP->TraceOrphan = getOphanData();
        }
      }
    }
    $_SHOP->tracelog .= $content."\n";
  }
}

/**
 * getOphanData()
 *
 * @return
 */
function getOphanData(){
  global $_SHOP;
  require_once("classes/redundantdatachecker.php");
  //Turn Off trace to run the Orphan check so we only get querys
  $traceme = $_SHOP->trace_on;
  $_SHOP->trace_on=false;
  $data = Orphans::getlist($keys, false);
  $keys =array_merge(array('_table            ','_id     ' ),$keys);
  $text = "\n".implode('|',$keys)."\n";
  foreach($data as $row) {
    $send = array();
    foreach($keys as $key) {
      $send[] = str_pad ( (isset($row[trim($key)]))?$row[trim($key)]:'',strlen($key));
    }
    $text .= implode('|',$send)."\n";
  }
  $_SHOP->trace_on=$traceme;
  if (!$data) { $text = "none";}
  return $text;
}

/**
 * orphanCheck()
 *
 * @return
 */
function orphanCheck(){
  global $_SHOP;
  if(is($_SHOP->trace_on,false)){
    $file = $_SHOP->trace_dir.'trace'.'.'.date('Y-m-d') . '.log';
    $content = date('c',time()).' : '.$_SHOP->trace_subject."\n". $_SHOP->tracelog."\n";
    if ($_SHOP->trace_on=='TRACEONLY') {
      file_put_contents($file, $content ,FILE_APPEND);
    } else {
      $text =getOphanData();
      file_put_contents($_SHOP->trace_dir.'last_orpanckeck.log',md5($text),LOCK_EX );
      if ($_SHOP->TraceOrphan <> md5($text)) {
        trace("\n\nOrphan Check Dump: ".$text);
      }
      if ($_SHOP->TraceOrphan <> md5($text) || stripos($_SHOP->trace_on,'ALL') !== false) {

        $content = date('c',time()).' : '.$_SHOP->trace_subject."\n". $_SHOP->tracelog."\n";
        $file = $_SHOP->trace_dir.'trace'.'.'.date('Y-m-d') . '.log';
        file_put_contents($file, $content ,FILE_APPEND);

        if ($_SHOP->TraceOrphan <> md5($text) && stripos($_SHOP->trace_on, 'SEND') !== false) {
          require_once("classes/class.restservice.client.php");
          try{
            $rsc = new RestServiceClient('http://cpanel.fusionticket.org/reports/orphans.xml'); //cpanel.fusionticket.org
            $rsc->subject    = $_SHOP->trace_subject;
            $rsc->data  = gzcompress (file_get_contents($file));
            $rsc->orphan = $text;
            $rsc->excuteRequest();
          //  print_r($rsc->getResponse());
          }catch(Exception $e){
            writeLog(print_r($e->getMessage(), true));
          }
          unlink($file);
        }
      }
    }
  }
  if (isset($_SHOP->filefor_error) && file_exist($_SHOP->filefor_error)) {
    require_once("classes/class.restservice.client.php");
    try{
      $rsc = new RestServiceClient('http://cpanel.fusionticket.org/reports/errors.xml'); //cpanel.fusionticket.org
      $rsc->subject    = (file_get_contents($_SHOP->filefor_error));
      $rsc->excuteRequest();
      //  print_r($rsc->getResponse());
    }catch(Exception $e){
      writeLog(print_r($e->getMessage(), true));
    }
    unlink($_SHOP->filefor_error);
  }
}

/**
 * SendMail()
 *
 * @param mixed $message
 * @param mixed $subject
 * @param mixed $toaddress
 * @return
 */
function SendMail($message, $subject, $toaddress) {
  global $_SHOP;
  require_once (ROOT."includes/classes/email.swift.sender.php");
  $message = Swift_Message::newInstance($subject )
    ->setFrom(array($_SHOP->organizer_email=>$_SHOP->organizer_name))
    ->setTo(array($toaddress))
    ->setBody($message)
    ;
  EmailSwiftSender::send($message, "", $logger, $failedAddr, array('action' => 'Errorlog'));
}

/**
 * addDaysToDate()
 *
 * @param mixed $date
 * @param mixed $no_days
 * @return
 */
function addDaysToDate($date,$no_days) {
  $time1  = strtotime($date);
  $res = strtotime((date('Y-m-d', $time1)." +$no_days"."days"));

  return date('Y-m-d', $res);
}

function strip_tags_in_big_string($textstring){
  $safetext = '';
  while (strlen($textstring) != 0)
      {
      $temptext = strip_tags(substr($textstring,0,1024));
      $safetext .= $temptext;
      $textstring = substr_replace($textstring,'',0,1024);
      }
  return $safetext;
}

function wp_entities($string, $encode = 1){
  $a = (int) $encode;

  if($a == 1) {
    $original = array("'"=>"&%39;",  "\""=> "&%34;" ,"("=>"&#40;"    ,")"=> "&#41;", "`" =>"&apos;" );//,"#"=>"&%35;"
    return strtr( $string, $original);
  } else {
    $original = array("'"   ,"\""   ,"#"    ,"("    ,")", "`"  );
    $entities = array("&%39;","&%34;","&%35;","&#40;","&#41;","&apos;");
    return str_replace($entities, $original, $string);
  }
}

function clean($string, $type='ALL') {

  switch (strtolower($type)) {
    case 'revert':
       return  htmlspecialchars_decode(wp_entities($string,0),ENT_QUOTES );
       break;
    case 'all'  : $string = strip_tags_in_big_string ($string);
    case 'strip': $string = html_entity_decode($string, ENT_QUOTES,'UTF-8');
    case 'html' : $string = htmlentities($string, ENT_QUOTES, "UTF-8");
    case 'htmlz' : $string = wp_entities($string);

  }
  return $string;
}

/**
 * This function creates a md5 password code to allow login true WWW-Authenticate
 *
 */
/**
 * md5pass()
 *
 * @param mixed $user
 * @param mixed $pass
 * @return
 */
function md5pass($user,$pass) {
  return '*'.md5($user.':'.AUTH_REALM.':'.$pass);
}

//added in not sure why it was removed?
//Still has code reliant on it in smarty.gui.php
//TODO: remove if not needed
function MakeUrl($file='', $params=array()) {
  Global $_SHOP;

  If ($_SHOP->UseRewriteURL) {
    list($file,$ext) = explode('.',$file);
    $result = $_SHOP->user_root.$file.'-';

    IF ($params) {
      $result .= base64_encodex(serialize($params)).'.htx';
    }  else {
      $result .= 'none.seo';
    }
  } else {
    $result = $_SHOP->user_root.$file;
    IF ($params) {
      $result .= '?'.http_build_query($params);
    }
  }
  return $result;
}

/**
 * valuta()
 *
 * @param string $value
 * @param mixed $code
 * @return
 */
function valuta($value='', $code=null) {
  global $_SHOP;
  if (is_numeric($value)) { $value = number_format($value,2);}
  $code = is($code,$_SHOP->organizer_currency);
  $list = plugin::call('updateValutaConversion',array($value, $code));
  if (is_array($list)) { list($value, $code) = $list;}
  return (isset($_SHOP->valutas[$code]))?$_SHOP->valutas[$code].' '.$value :$value.' '.is($_SHOP->valutas["*$code"],$code);
}

/**
 * This function creates a md5 password code to allow login true WWW-Authenticate
 *
 */

/**
 * sha1pass()
 *
 * @param mixed $user
 * @param mixed $pass
 * @return
 */
function sha1pass($user, $pass) {
  return '*'.sha1(md5($user.':'.AUTH_REALM.':'.$pass).'~'.$user);
}

/**
 * is_base64_encoded()
 *
 * @param mixed $data
 * @return
 */
function is_base64_encoded($data){
  return preg_match('%^[a-zA-Z0-9/+]*={0,2}$%', $data) || preg_match('%^[a-zA-Z0-9_-]*~{0,2}$%', $data);
}

/**
 * base64_decodex()
 *
 * @param mixed $data
 * @return
 */
function base64_decodex($data){
  return base64_decode(strtr($data, '-_~', '+/='));
}

/**
 * base64_encodex()
 *
 * @param mixed $data
 * @return
 */
function base64_encodex($data){
  return strtr(base64_encode($data), '+/=', '-_~'); //  }
}


/**
 * addError()
 *
 * @param mixed $key
 * @param mixed $const
 * @return
 */
function addError($key, $const, $varb='') {
  Global $_SHOP;
  $_SHOP->Messages['__Errors__'][$key][] = con($const).' '.$varb;
  return false;
}

/**
 * addNotice()
 *
 * @return
 */
function addNotice($const, $varb='') {
  Global $_SHOP;
  $_SHOP->Messages['__Notice__'][] = con($const).' '.$varb;
  return false;
}

/**
 * addWarning()
 *
 * @return
 */
function addWarning($const, $varb='') {
  Global $_SHOP;
  if (is_array($const)) {
    $_SHOP->Messages['__Warning__'] = array_merge(is($_SHOP->Messages['__Warning__'], array()), $const);
  } else
    $_SHOP->Messages['__Warning__'][] = con($const).' '.$varb;
  return false;
}

/**
 * hasErrors()
 *
 * @param string $key
 * @return
 */
function hasErrors($key=''){
  Global $_SHOP;
  if ($key)  {
    return (isset($_SHOP->Messages['__Errors__'][$key]) && count($_SHOP->Messages['__Errors__'][$key])>0);
  } else {
    return (isset($_SHOP->Messages['__Errors__']) && count($_SHOP->Messages['__Errors__'])>0);
  }
}

/**
 * printMsg()
 *
 * @param mixed $key
 * @param mixed $err
 * @param mixed $addspan
 * @return
 */
function printMsg($key, $err = null, $addspan=true) {
  Global $_SHOP;
  $output ='';
  if (!is_array($err)){
    $block = substr($key,1,1)=='_';

    if (substr($key,1,1)=='_') {
      $err = $_SHOP->Messages;
    } elseif (isset($_SHOP->Messages['__Errors__'])) {
      $err = $_SHOP->Messages['__Errors__'];
    } else
       return '';
  }

  if (is_array($err)) {
  if (isset($err[$key]) && is_array($err[$key])) {
      if ($block) $output .= '<ul>';
    foreach($err[$key] as $value){
      if(is_array($value)){
        foreach($value as $val){
            if ($block) $output .= '<li>'. $val.'</li>';
            if (!$block) $output .= $val.'<br>';
        }
      }else{
          if ($block) $output .= '<li>'. $value.'</li>';
          if (!$block) $output .= $value. '<br>';
      }
    }
      if ($block) $output .= '</ul>';

  }elseif (isset($err[$key]) && is_string($err[$key])) {
    $output .= $err[$key]. "<br>";
  }
  }
  $output =  str_replace("'",'&amp;', str_replace("\r",'', str_replace("\n",'', $output)));
  If ($output && $addspan) {
    switch ($key) {
      case '__Warning__':
        $output = "<h4 class='error'>".$output. "</h4>";
        break;
      case '__Notice__':
        $output = "<h4 class='success'>".$output. "</h4>";
        break;
      default:
        $output = str_ireplace('<br>',' ' , $output);
        $output = "<span class='err error'>{$output}</span>";
    }
  }
  return $output;
}

function showstr( $Text, $len = 20 ) {
	if ( strlen($Text) > $len ) {
		$Text = substr( $Text, 0, $len ) . '&hellip;';
	}
	return $Text;
}

function list_system_locales(){
    ob_start();
    system('locale -a');
    $str = ob_get_contents();
    ob_end_clean();
    return split("\\n", trim($str));
}


function customError($errno, $errstr, $error_file, $error_line, $error_context) {
  GLOBAL $_SHOP;

  if (!(error_reporting() & $errno)) {return;}
  $errortype = array(
    E_ERROR           => 'Error',
    E_WARNING         => 'Warning',
    E_PARSE           => 'Parsing error',
    E_NOTICE          => 'Notice',
    E_CORE_ERROR      => 'Core error',
    E_CORE_WARNING    => 'Core warning',
    E_COMPILE_ERROR   => 'Compile error',
    E_COMPILE_WARNING => 'Compile warning',
    E_USER_ERROR      => 'User error',
    E_USER_WARNING    => 'User warning',
    E_USER_NOTICE     => 'User notice',
    E_RECOVERABLE_ERROR => 'Recoverable error');
  if(defined('E_STRICT'))
    $errortype[E_STRICT] = 'runtime notice';

  $user_errors = E_USER_ERROR | E_USER_WARNING | E_USER_NOTICE | E_ERROR | E_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING;

  //...blah...
  $error = is($errortype[$errno],$errno);

  if ($errno & $user_errors) {
    writeLog( "{$error}: $errstr, $error_file @ $error_line", FT_ERROR);
  }
}

function getIpAddress() {

	$ip = "";

	if($_SERVER) {
		if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])){
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		}elseif(isset($_SERVER['HTTP_CLIENT_IP'])){
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		}else{
			$ip = $_SERVER['REMOTE_ADDR'];
		}
	} else {
		if(getenv('HTTP_X_FORWARDED_FOR')){
			$ip = getenv('HTTP_X_FORWARDED_FOR');
		}elseif(getenv('HTTP_CLIENT_IP')){
			$ip = getenv('HTTP_CLIENT_IP');
		}else{
			$ip = getenv('REMOTE_ADDR');
		}
	}
	return $ip;
}

function isSerialized($str) {
	return ($str == serialize(false) || @unserialize($str) !== false);
}

function addJSFiles($extension, $filename){
  global $_SHOP;
  if (!in_array($filename,$_SHOP->jFiles[$extension])) {
    $_SHOP->jFiles[$extension][] = $filename;
  }
}

function getJSFiles($extension){
  global $_SHOP;
  if (!is_array($_SHOP->jFiles[$extension])) {
    return implode(',', $_SHOP->jScript[$extension]);
  } else {
    return false;
  }
}


function addJQuery($script){
  global $_SHOP;
  $_SHOP->jScript .= "\n".$script;
}

function getJQuery(){
  global $_SHOP;
  return $_SHOP->jScript;
}

?>