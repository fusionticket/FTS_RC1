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

class install_license {
  static function precheck($Install) {
    if (!file_exists(ROOT."includes/config/init_config.php")) {
      if (!is_writable(ROOT."includes/config")) {
        array_push($Install->Errors, ROOT."includes/config should be temporarily writable by the webserver user.");
      }
    } elseif (!is_writable(ROOT."includes/config/init_config.php")){
      array_push($Install->Errors, ROOT."includes/config/init_config.php should be temporarily writable by the webserver user.");
    } else {
      $_SESSION['ConfigExist'] = file_exists(ROOT."includes/config/init_config.php") and
                                 (filesize(ROOT."includes/config/init_config.php")>100);
    }
    return true;
  }

  static function postcheck($Install) {//int_r($_REQUEST);
    $ver = explode('.', phpversion());

    if ((version_compare(PHP_VERSION, '5.2.0')<0) && (version_compare(PHP_VERSION, '5.4.0')>= 0)){
      array_push($Install->Errors,"FAILED: PHP version 5.2.x is required, You have php version " .phpversion() . ". Please update your server." );
    }elseif (version_compare(PHP_VERSION, '5.2.1')<0){
      array_push($Install->Warnings,"WARNING: To take full advantage of the power of Fusion Ticket your server must have php 5.2.x .<br> You have php version " . phpversion() . ". Please update your server.");
    }elseif (version_compare(PHP_VERSION, '5.4')>=0){
      array_push($Install->Warnings,"WARNING: This program works best with php version 5.3.x.<br> You have php version " . phpversion() . ". This should not be a problem.");
    }

    if (!function_exists('mysqli_connect')){
      array_push($Install->Errors,"MySQL-Exstentions have not been enabled, this is required for security reasons and should be fairly standard on any PHP 5 Build. It will be replacing standard mysql functions.");
    }
    if (!function_exists('curl_init')){
      $_SESSION['SHOP']['url_post_method'] ='fso';
      array_push($Install->Warnings,"<a href='http://ch2.php.net/manual/en/ref.curl.php'>cURL</a> is not compiled in your php. cURL is used by paypal or authorize.net payment methods - you will be unable to use those. Install curl, or configure another network library in <i>'includes/config/init_common.php'");
    }
    if (!extension_loaded('openssl')) {
      array_push($Install->Warnings,"OpenSSL is not loaded in your php configuration. OpenSSL is used by paypal or authorize.net payment methods - you will be unable to use those. Please install OpenSSL.");
    }
    if (!function_exists('iconv')){
      array_push($Install->Warnings,"<a href='http://ch2.php.net/manual/en/ref.iconv.php'>iconv</a> is not compiled in your php. iconv is used to enable non-latin alphabets in PDF templates.");
    }
    if (!function_exists('xml_parser_create')){
      array_push($Install->Warnings,"<a href='http://ch2.php.net/manual/en/ref.xml.php'>xml parser</a> is not compiled in your php. xml parser is used by email and pdf templates - you will be unable to print tickets and send emails");
    }

    if (!function_exists('openssl_seal')){
      array_push($Install->Warnings,"<a href='http://ch2.php.net/manual/en/ref.openssl.php'>openssl</a> is not compiled in your php. openssl is used to encrypt credit card information - you will be unable to use this feature.");
    }

    if (ini_get('register_globals')){
      array_push($Install->Warnings,"For security reasons 'register_globals' should be 'OFF'. Read documentation for explanations.");
    }

    if (ini_get('magic_quotes_gpc')){
      array_push($Install->Warnings," 'magic_quotes_gpc' should be 'OFF' to use fusion ticket safely. All quoting will be done ".
                                    "inside the program. Read documentation for explanations.");
    }

    if (ini_get('safe_mode')){
      array_push($Install->Warnings,"Your php is in 'SAFE MODE'. This can lead to problems during install (unable to create directory errors). Please read documentation.");
    }
    //if (!file_exists(ROOT."includes/temp")){
     // mkdir (ROOT.'includes/temp');
    //  chmod (ROOT.'includes/temp',"0775");
    //}
    if (!file_exists(ROOT."includes/temp")){
      array_push($Install->Errors,ROOT."includes/temp need to be created and made writable by the webserver user.");
    } elseif (!is_writable(ROOT."includes/temp")){
      array_push($Install->Errors,ROOT."includes/temp should be writable by the webserver user.");
    }
//    if (!file_exists(ROOT."files")){
//      mkdir (ROOT.'files');
//      chmod (ROOT.'files',"0775");
//    }
    if (!file_exists(ROOT."files")){
      array_push($Install->Errors,ROOT."files need to be created and made writable by the webserver user.");
    } elseif (!is_writable(ROOT."files")){
      array_push($Install->Errors,ROOT."files should be writable by the webserver user.");
    }
    return true;
  }

  static function display($install) {
    Install_Form_Open ($install->return_pg,'','Software License Agreement');
    $license = @file_get_contents(ROOT."LICENCE");
    echo "<table cellpadding=\"1\" cellspacing=\"2\" width=\"100%\">
            <tr>
              <td colspan=\"2\">
                <textarea rows=\"22\" cols=\"45\" readonly=readonly class=\"UI_TextArea\" style=\"width:100%;height:100%;\"  >
                  {$license}
                </textarea>
              </td>
            </tr>
          </table>\n";
    Install_Form_Buttons ();
    Install_Form_Close ();
  }
}
?>