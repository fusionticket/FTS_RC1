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

error_reporting(E_ALL);


if (!defined('ft_check')) {die('System intrusion ');}

if(!isset($_SHOP)) $_SHOP = new stdClass();


//error_reporting(E_ALL);
$states = array('install_welcome',   'install_license',  'install_login', 'install_database', 'install_mode',
                'install_adminuser', 'install_merchant', 'install_settings', 'install_mail',  'install_register',
                'install_execute',   'install_finish');
ini_set('magic_quotes_runtime', 0);
define('ROOT2',(dirname(dirname(dirname(__FILE__)))).DIRECTORY_SEPARATOR);

require_once(ROOT2."includes".DIRECTORY_SEPARATOR."config".DIRECTORY_SEPARATOR."defines.php");
require_once(ROOT2."includes".DS."classes".DS."basics.php");
require_once('shop_plugins/function.minify.php');
session_start();
if (empty($_REQUEST)) {
  session_destroy();
  session_start();
}

include_once 'install_version.php';


if(function_exists("date_default_timezone_set") and
  function_exists("date_default_timezone_get")) {
  @date_default_timezone_set(@date_default_timezone_get());
}

$_SHOP->tmp_dir= ROOT."includes".DS."temp".DS;
$_SHOP->includes_dir = ROOT."includes".DS;

$root = "http://" . $_SERVER['HTTP_HOST'];

$root .= substr($_SERVER['SCRIPT_NAME'], 0, - 15);
define ('BASE_URL',$root);
$_SHOP->images_url= $root."/images/"; //init_common.php not called yet so images_url is otherwise not defined
$_SHOP->root_base = $root.'/';
require_once(ROOT."includes".DS."classes".DS."basics.php");
require_once(ROOT."includes/classes/class.shopdb.php");
require_once(ROOT."includes/install/install_base.php");

if (isset($_REQUEST['do']) && $_REQUEST['do']=='testhttps'){
  die( file_get_contents($_SHOP->tmp_dir.'ssl_instal.txt'));
} elseif (isset($_REQUEST['do']) and $_REQUEST['do']=='Cancel'){
  session_destroy();
  echo "<script>window.location.href='{$_SERVER['PHP_SELF']}';</script>";
  exit;
}

?><!DOCTYPE html>
<html>
<head>
   <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
  <title>Fusion Ticket Installation</title>
  <?php echo minify('css','','');?>
    <link rel='stylesheet' href='../admin/admin.css' />

  <?php echo minify('js','','scripts/jquery')."
    ".minify('js','jquery.caret.js','scripts/jquery'); ?>

  <script language="JavaScript">
    function Confirm_Inst_Cancel(){
      if(window.confirm('Cancel The Installation Process ?')){
        window.close ();
        return true;
      }
      return false;
    }
    function Validate_Inst_Upgrade(){
      if (!document.install.radio[0].checked ||
          window.confirm("Full installation removes all tables before the installation starts.\n\nContinue The Installation Process ?"))
        {return true} else {return false};
    }
    function Validate_License_page(){
      if (!document.install.sla_radio[0].checked){
        window.alert("You must accept the terms of the software license agreement to install and use this software.");
        return false;
      } else {
        return true;
      };
    $(function() {
            $("input[type='submit'] :enabled:first").focus();
        });
    }

    $(document).ready(function(){
      $("td[class*='has-tooltip']").tooltip({
        delay:40,
        showURL:false,
        opacity: 0,
        bodyHandler: function() {
          if($(this).children('div').html() != ''){
            return $(this).children('div').html();
          }else{
            return false;
          }
        }
      });
    });
  </script>

  <style>
    .err {color:#dd0000;}
    .warn {color:#cc9900;}

    .ok {color:#00dd00;}
  </style>

</head>
<body>
<?php
foreach($states as $id => $name) {
  define(strtoupper($name), $id);
  require_once(dirname(__FILE__).DS."{$name}.php");
}

$_SERVER['PHP_SELF']   = clean($_SERVER['PHP_SELF']   ,'HTML');
if (!defined('PHP_SELF')){
  define('PHP_SELF',$_SERVER['PHP_SELF']);
}

if(!isset($_SESSION['is_started']) or !isset($_REQUEST['inst_pg'])){
  $_REQUEST['inst_pg'] = 0;
  $first = true;
} else $first= false;

$_SESSION['is_started'] = True;

$Install = new stdClass();
$Install->Errors   = array ();
$Install->Warnings = array ();
$Install->return_pg  = $_REQUEST['inst_pg'];

//var_dump($Install);
//echo $_REQUEST['inst_mode'],':',$_REQUEST['continue'];
  echo "
    <div id=\"wrap\">
      <div id=\"header\">
        <img src=\"".BASE_URL."/images/logo.png\" border=\"0\"/>
        <h2>Installation Procedure <span style=\"color:red; font-size:14px;\"><i>[".INSTALL_VERSION."]</i></span></h2>
      </div>
";

if ($first) {
  selectnext($Install);
} else {
  switch(is($_REQUEST['inst_mode'],'disp')){
    case 'pre':
      selectnext($Install, isset($_REQUEST['continue']));
      break;

    case 'disp':
      $test = call_user_func(array ($states[$Install->return_pg], 'postcheck'),$Install);
      if((ShowResults($Install,'post')== null) ) {
        if ($test) $Install->return_pg++;
        selectnext($Install);
      }

      break;

    case 'post':
      if (isset($_REQUEST['continue'])){
        $Install->return_pg++;
        selectnext($Install);
      } else {
        call_user_func(array ($states[$Install->return_pg], 'display'),$Install);
      }
      break;
  }
}

function selectnext($Install,$continue = false) {
  global $states;
  $first = true;
  while ($first and $Install->return_pg <= count($states)) {
    if ($continue or call_user_func(array ($states[$Install->return_pg], 'precheck'),$Install)) {
      if(!ShowResults($Install,'pre')) {
        call_user_func(array ($states[$Install->return_pg], 'display'),$Install);
      }
      return;
    } elseif (!ShowResults($Install,'pre')) {
      $Install->return_pg ++;
    } else return;
    $continue  = false;
  }
}
?>
      <div id="footer">
        Powered by <a href="http://fusionticket.org">Fusion Ticket</a> - The Free Open Source Box Office
      </div>
      </div>
    </div>
  </body>
</html>