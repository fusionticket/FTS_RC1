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

# In PHP 5.2 or higher we don't need to bring this in
if (!defined('ft_check')) {die('System intrusion ');}
if (!function_exists('json_encode')) {
  require_once LIBS.'json/JSON.php';
  function json_encode($arg)
  {
    global $services_json;
    if (!isset($services_json)) {
      $services_json = new Services_JSON();
    }
    return $services_json->encode($arg);
  }

  function json_decode($arg)
  {
    global $services_json;
    if (!isset($services_json)) {
      $services_json = new Services_JSON();
    }
    return $services_json->decode($arg);
  }
}
?>