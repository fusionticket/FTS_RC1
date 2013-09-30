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
 *
 *
 * @version $Id$
 * @copyright 2011
 */
  abstract class Secure{
    static $isTokenChecked = false;

    public static function isTokenChecked() {
      return self::$isTokenChecked;
    }

    protected static function getHash($seed) {
      GLOBAL $_SHOP;
      return md5($_SHOP->secure_id.$seed);
    }

    /**
     * Create a token-string
     *
     * @param	int	length of string
     * @return  string  generated token
     */
    protected static function _createToken($length = 32)
    {
      static $chars	=	'0123456789abcdef';
      $max			=	strlen($chars) - 1;
      $token			=	'';
      $name			=  session_name();
      for ($i = 0; $i < $length; ++$i) {
        $token .=	$chars[ (rand(0, $max)) ];
      }
      return md5($token.$name);
    }
    /**
     * Method to determine a hash for anti-spoofing variable names
     *
     * @return	string  Hashed var name
     * @static
     */
    public static function getFormToken($formname, $forceNew = false)
    {
    $token = self::getToken($forceNew);
    $hash	 = self::getHash($formname. $token);
    $hash  = base_convert($hash, 16,36);

      return $hash;
    }

    /**
     * Get a session token, if a token isn't set yet one will be generated.
     *
     * Tokens are used to secure forms from spamming attacks. Once a token
     * has been generated the system will check the post request to see if
     * it is present, if not it will invalidate the session.
     *
     * @param	boolean  If true, force a new token to be created
     * @return  string	The session token
     */
    public static function getToken($forceNew = false)
    {
      $token = is($_SESSION['tokens']['n'],null);
      //create a token
      if ($token === null || $forceNew) {
        $token	=	self::_createToken(12);
        $_SESSION['tokens']['n'] = $token;
      }
      $_SESSION['tokens']['t'] = time();

      return $token;
    }

    /**
     * Method to determine if a token exists in the session. If not the
     * session will be set to expired
     *
     * @param  string	Hashed token to be verified
     * @param  boolean  If true, expires the session
     */
    public function hasToken($formname, $tCheck) {
      // check if a token exists in the session
      $tStored = self::getFormToken($formname);
      //check token
      if (($tStored !== $tCheck)) {
        return false;
      }
    //  var_dump($_COOKIE);
      self::$isTokenChecked = true;
      return true;
    }

    public static function checkTokens() {
      foreach ($_POST as  $key => $value) {
        if (substr($key,0,3) === '___') {
          $key  = substr($key, 3) ;
          list($name,$key) = explode('_',$key);
          return self::hasToken($name,$key );
        }
      }
      return true;
    }

  }
?>