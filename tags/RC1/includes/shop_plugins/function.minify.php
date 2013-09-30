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
* Smarty packerload function plugin
*
* File: function.packerload.php<br>
* Type: function<br>
* Name: packerload<br>
* Date: Jan 23, 2008<br>
* Purpose: join togther javascript and css into current file
* Install: Drop into the plugin directory, place into html
* <code>{packerload type='js' files='../js/user.js|../js/project.js'}</code>.
* Specify minify cachedir. Requires Minify ( http://code.google.co... and PHP 5.2.1+ )
* @author William D. Estrada <losthitchhiker [A t) gM ail dot C o m>
*
* @version 1
* @param string
* @param Smarty
*/

/**
* Dual licensed under the MIT and GPL licenses:
* http://www.opensource...
* http://www.gnu.org/li...
*/

function smarty_function_minify($params, &$smarty)
{


  // Retrieve the files to process
  $files = is($params['files'],'');

  //Base Dir
  $base = is($params['base'],'');

  // Retrieve type of file
  $type = is($params['type'],'');

  return minify($type, $files, $base);
}

function minify($type, $files='', $base=''){
  global $_SHOP;

  if (!$files) {
    if ($type=='css') {
      $files = 'css/flick/jquery-ui.css,css/jquery.tooltip.css';
    } else {
      $files = 'jquery.min.js,jquery.migrate.js,jquery.ui.js,jquery.ajaxmanager.js,jquery.json-2.2.min.js,jquery.form.js,jquery.validate.min.js,jquery.validate.add-methods.js,jquery.simplemodal.js,jquery.tooltip.min.js';
    }
  }

  if (true || (defined('CURRENT_VERSION') && strpos(constant('CURRENT_VERSION'),'svn') !== false)) {

    $files = explode(',', $files );
    if ($base) $base = $base. '/';

    $min = "<!-- minify -->\n";
    foreach($files as $file) {
      $url = $_SHOP->root_base .  $base.  $file;
      switch ( $type ) {
        case 'css': $min .= '  <link type="text/css" rel="stylesheet" href="'.$url.'" />'."\n";
          break;

        default:    $min .= '  <script type="text/javascript" src="'.$url.'"></script>'."\n";
          break;
      }
    }
    $min .= "  <!-- \minify -->\n";
  } else {
    $url = $_SHOP->root_base."minify.php?";
    if($base){
      $url .= "b=".$base."&";
    }
    $url .= "f=".$files;

    // Check type and run Minify whether CSS or JS
    switch ( $type ) {
      case 'css': $min = '<link type="text/css" rel="stylesheet" href="'.$url.'" />';
                  break;

      default:    $min = '<script type="text/javascript" src="'.$url.'"></script>';
                  break;
    }
  }
  // Return the packed file to be written to the HTML document
  return $min;
}
?>