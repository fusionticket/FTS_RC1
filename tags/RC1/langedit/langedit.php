<?php
/**
%%%copyright%%%
 *
 * FusionTicket - ticket reservation system
 *  Copyright (C) 2007-2009 Christopher Jenkins, Niels, Lou. All rights reserved.
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
  session_name('langedit');
if (!defined('DS')) {
/**
 * shortcut for / or \ (depending on OS)
 */
  define('DS', DIRECTORY_SEPARATOR);
}
  error_reporting(0);
  session_start();
  if(function_exists("date_default_timezone_set")) {
    @date_default_timezone_set(date_default_timezone_get());
  }

  function fillxml ($key, $field1, $field2) {
  	echo "<row id='{$key}'>";
  	echo "<cell>{$key}</cell>";
  	echo "<cell><![CDATA[{$field1}]]></cell>";
  	echo "<cell><![CDATA[{$field2}]]></cell>";
  	echo "</row>";
  }

  function findinside( $string) {
     // preg_match_all('/define\(["\']([a-zA-Z0-9_]+)["\'],[ ]*(.*?)\);/si',  $string, $m); //.'/i'
      preg_match_all('|^[\s]*define\(["\'](.*)["\'],[\s]*["\'](.*)["\']\);|imU', $string, $m);
      print_r($langtemp);
      return array_combine( $m[1],$m[2]);
  }

  $lang = !empty($_POST['lang'])?$_POST['lang']: $_SESSION['lang'];
  $editfile = dirname(__FILE__).DS.'..'.DS.'includes'.DS.'lang'.DS."site_{$lang}.inc";
  $deffile = dirname(__FILE__).DS.'..'.DS.'includes'.DS.'lang'.DS."site_en.inc";

  if ($_POST['load'] || $_POST['lang']) {
    If (!isset($_SESSION['diff1']) or $_SESSION['lang']<>$_POST['lang'] ) {
      $string1 = file_get_contents($deffile);
      $diff1 = findinside($string1);
      if (file_exists( $editfile)) {
        $string2 = file_get_contents($editfile);
        $diff2 = findinside($string2);
      } else {
          $diff2 = array();
      }

      $_SESSION['diff1'] = $diff1;
      $_SESSION['diff2'] = $diff2;
      if ($_POST['lang']) {
        $_SESSION['lang']  = $_POST['lang'];
      }
    }
  }
  $diff1= $_SESSION['diff1'];
  $diff2= $_SESSION['diff2'];

  if ($_POST['load']=='new_language') {
    $lang = strtolower($_POST['lang']);
    if (strlen($lang)<>2) {
      die('Language code needs to be 2 characters');

    } elseif (!is_string($lang)) {
      die('Language code needs to be 2 characters');
    } elseif (file_exists()){
      die('Language code already exist.');
    } else {
      $string2 = "<"."?php\n";
      $string2 .= "// defines added at: ".date('c')."\n\n";
      $string2 .= "?>";
      file_put_contents($editfile,$string2, FILE_TEXT );
    }
    die("done");
  } elseif ($_POST['oper']=='edit') {
      if (!is_writable($editfile)) {
        die('This file is not writable. : '.$editfile);
      } else {
        $text =  $_POST['lang2'];
        $_SESSION['diff2'][$_POST['id']] = $_POST['lang2'];

        $string2 = "<"."?php\n";
        $string2 .= "// defines added at: ".date('c')."\n";
        foreach ($_SESSION['diff2'] as $key =>$value) {
      		$umlautArray = Array("/ä/","/ö/","/ü/","/Ä/","/Ö/","/Ü/","/ß/");
      		$replaceArray = Array("&auml;","&ouml;","&uuml;","&Auml;","&Ouml;","&Uuml;","&szlig;");
      		$value = preg_replace($umlautArray , $replaceArray , $value);
          $string2 .= "define('$key', '".addslashes($value)."');\n";
        }
        $string2 .= "?>";
        file_put_contents($editfile,$string2, FILE_TEXT );
      }
     die("done");

  }elseif ($_POST['load']=='update_2') {
     if (count($diff1)===0) {
       die('noting to update');
     } elseif (!is_writable($editfile)) {
       die('This file is not writable.');
     } else {
       $string2 = "<"."?php\n";
       $string2 .= "'.DS.'/ defines added at: ".date('c')."\n";
       foreach ($diff2 as $key =>$value) {
         $string2 .= "define('$key', '".addslashes($value)."');\n";
       }
       $diff= array_diff_key($diff1, $diff2);
       foreach ($diff as $key =>$value) {
         $string2 .= "define('$key', '".addslashes($value)."');\n";
       }
       $string2 .= "?>";
       $_SESSION['diff2'] = array_merge($diff2, $diff );
       file_put_contents($editfile,$string2, FILE_TEXT );
     }
     die("done");
  } elseif ($_POST['load']=='grid')  {
    $responce = array();
    $responce['page'] = 0;
    $responce['total'] = 0;
    $responce['records'] = count($diff1);
    $responce['userdata'] = array();
    $i=0;

    foreach ($diff1 as $key =>$value) {
      $responce['rows'][$i]['id']=$key;
      $responce['rows'][$i]['cell']=array($key, htmlentities($value), htmlentities($diff2[$key]));
      $i++;
    }
    foreach ($diff2 as $key =>$value) {
      if(!array_key_exists($key, $diff1 )){
      $responce['rows'][$i]['id']=$key;
      $responce['rows'][$i]['cell']=array($key, "&nbsp;", htmlentities($value));
      $i++;
    }
    }
    echo json_encode($responce);
    exit;
  };
?>