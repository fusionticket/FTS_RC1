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
   * Smarty {set} compiler function plugin
   *
   * File:     compiler.set.php<br>
   * Type:     compiler function<br>
   * Name:     set<br>
   * Purpose:  Set a value to a variable (also arrays).<br>
   * The optional parameter "if" is used to set the value if the test is true. The test can be: 'empty', '!empty', 'is_null', '!is_null', 'isset', '!isset', 'is_void'.
   * The new command 'is_void' test if the variable is empty and != 0, very useful for test $_REQUEST parameters.
   *
   * @link http://www.dav-muz.net/
   * @version 1.0
   * @copyright Copyright 2006 by Muzzarelli Davide
   * @license http://opensource.org/licenses/LGPL-3.0 GNU LGPL License
   * @author Davide Muzzarelli <info@dav-muz.net>
   *
   * @param array parameters "var": variable. "value": value to assign. "if": assign the value only if this test is true (tests avaiables: 'empty', '!empty', 'is_null', '!is_null', 'isset', '!isset', 'is_void').
   * @param Smarty_Compiler object
   * @return void|string
   */
  function smarty_compiler_set($params, $smarty) {
    // Extract if "value" parameter contain an array
    $regularExpression = '/ value=array\([\'"]?.*[\'"]?\)/';
    if (preg_match($regularExpression, $params, $array)) {
      $array = substr($array[0], 7);
      $params = preg_replace($regularExpression, '', $params);
    }

    $params = $smarty->_parse_attrs($params);
    $functionsPermitted = array('empty', '!empty', 'is_null', '!is_null', 'isset', '!isset', 'is_void'); // Functions permitted in "if" parameter.

    if (!isset($params['var'])) {
      $smarty->_syntax_error("set: missing 'var' parameter", E_USER_WARNING);
      return;
    }
    $array = preg_replace('/\!(\w+)\!/', "@con('$1')", $array);
    if (!empty($array)) {
      $params['value'] = $array;
    }
 //   var_dump($params); die();

    if (!isset($params['value'])) { // Clean setting
      return "{$params['var']} = null;";
    } elseif (isset($params['if'])) { // Setting with "if" parameter
      $params['if'] = substr($params['if'], 1, -1);
      if (in_array($params['if'], $functionsPermitted)) {
        if ($params['if'] == 'is_void') { // "is_void" command
          return "if (empty({$params['var']}) and ({$params['var']} !== 0) and ({$params['var']} !== '0')) {$params['var']} = {$params['value']};";
        } else { // others commands
          return "if ({$params['if']}({$params['var']})) {$params['var']} = {$params['value']};";
        }
      } else { // "if" parameter not correct
        $smarty->_syntax_error("set: 'if' parameter not valid", E_USER_WARNING);
        return;
      }
    } else { // normal setting
      return "{$params['var']} = {$params['value']};";
    }
  }

  ?>