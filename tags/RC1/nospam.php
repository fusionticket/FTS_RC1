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

// het random nr. aanmaken en gecodeerd opslaan in php sessie
define('ft_check','shop');
//require_once('includes/config/defines.php');
//require_once('includes/classes/basics.php');
error_reporting(0);

session_name('ShopSession');
session_start();

if (isset($_POST['name']) && isset($_POST[$_POST['name']])) {
//  writelog(print_r($_SESSION['_NoSpam'],true));
//  writelog(print_r($_POST,true));
  $check = $_SESSION['_NoSpam'][clean($_POST['name'])] == md5(strtoupper ($_POST[$_POST['name']]));
  echo json_encode($check);
  exit;
}

$randomnr = '';

// captcha plaatje met nummer maken - afmetingen kun je aanpassen gebruikte font

$im = imagecreatetruecolor(100, 46);

// Kleurenbepaling

$grey = imagecolorallocate($im, 198, 198, 198);
$black = imagecolorallocate($im, 0, 0, 0);

// zwarte rechthoek tekenen - afmetingen kun je aanpassen aan verschillende fonts

imagefilledrectangle($im, 0, 0, 100, 46, imagecolorallocate($im, rand(120,255), rand(120,255), rand(120,255)));

 for ($i = 1; $i < 10; $i++) {
    imagefilledrectangle($im,rand(0,50),rand(0,23),rand(50,100),rand(23,46),imagecolorallocate($im,mt_rand(120,255),mt_rand(120,255),mt_rand(120,255)));
    imagefilledellipse($im,rand(0,100),rand(0,60),rand(25,50),rand(25,50),imagecolorallocate($im,mt_rand(120,255),mt_rand(120,255),mt_rand(120,255)));
 }
// hier - font.ttf' vervangen met de locatie van je eigen font bestand
$font = 'includes/fonts/Gibberish.ttf';
$text = '2346789ABCEFGHKNPRT';
// schaduw toevoegen
// voorkomen dat afbeelding ge-cached wordt
 for ($i = 0; $i < 2500; $i++) {
  	$color_pixel  = imagecolorallocatealpha ($im, mt_rand(200, 255), mt_rand(200, 255), mt_rand(200, 255),64);
		ImageSetPixel($im, rand(0, 100), rand(0, 46), $color_pixel);
 }
 $white = imagecolorallocate($im,0,0,0); //mt_rand(010,120), mt_rand(010,120), mt_rand(010,120)); //

  for ($i = 0; $i < 5; $i++) {
   $char = substr($text,rand(0,strlen($text)-1),1);
   $randomnr .= $char;
   $angle = rand(-15,15);
   $y = rand(-15,5);
   if (function_exists('imagettftext')) {
     imagettftext($im, 16, $angle, 7+($i*19), 34+$y, $grey, $font, $char);
     imagettftext($im, 16, $angle, 5+($i*19), 36+$y, $white, $font, $char);
   } else {
     imagestring($im, 5,  7+($i*19), 14+$y, $char, $grey);
     imagestring($im, 5,  5+($i*19), 16+$y, $char, $white);
   }
 }

if (!isset($_GET['name'])) {$_GET['name'] ='RandomNr';}
$_SESSION['_NoSpam'][clean($_GET['name'])] = md5($randomnr);

header("Expires: Wed, 1 Jan 1997 00:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// plaatje verzenden naar browser

header ("Content-type: image/gif");
imagegif($im);
imagedestroy($im);
// =====================================
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
function writeLog($what, $where = FT_DEBUG){
  Global $_SHOP;
  if ($where < 0) { return; }

  $logname = 'error';
  if ($where == FT_DEBUG) {
    $logname = 'debug';
  }
  if (!isset($_SHOP->hasloged[$where])){
    $what = date('d.m.Y H:i ') ."--------------------------------\n". $what;
    $_SHOP->hasloged[$where] =1;
  }
  $h = fopen('includes/temp/' . $logname.'.'.date('Y-m-d') . '.log', 'a');
  if ($h) {
    fwrite($h,utf8_encode($what . "\n"));
    fclose($h);
  }
}

?>