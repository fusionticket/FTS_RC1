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
 * @author Chris Jenkins
 * @copyright 2008
 */
if (!defined('ft_check')) {die('System intrusion ');}

/**
 * This define is used to store the passwords, pleace do not change this after
 * there are uses registrated to the system.
 * This this will invalided all given passwords in the system.
 */
  try {
define ('AUTH_REALM','Fusion Ticket Login');

define('DS', DIRECTORY_SEPARATOR);
define('ROOT',(dirname(dirname(dirname(__FILE__)))).DS);
define('INC',ROOT.'includes'.DS);
define('LIBS',INC.'libs'.DS);
define('TEMP',INC.'temp'.DS);
define('UPDATES',TEMP."updates".DS);
define('CLASSES',INC.'classes'.DS);
if (!defined('PATH_SEPARATOR')) {
      if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN'){
        define('PATH_SEPARATOR', ';');
    } else {
        define('PATH_SEPARATOR', ':');
    }
}
set_include_path(ROOT.'includes'. PATH_SEPARATOR.
                 LIBS.'pear'.PATH_SEPARATOR.
                 get_include_path());
// system defines

define('PM_ZONE', 0);
define('PM_ROW', 1);
define('PM_SEAT', 2);
define('PM_CATEGORY', 3);
define('PM_ID', 4);
define('PM_STATUS', 5);

define('PM_LABEL', 0);
define('PM_LABEL_TYPE', 1);
define('PM_LABEL_SIZE', 2);
define('PM_LABEL_TEXT', 3);

define('PM_STATUS_FREE', 0);
define('PM_STATUS_OCC', 1);
define('PM_STATUS_RESP', 2);
define('PM_STATUS_HOLD', 3);


define("SEAT_ERR_INTERNAL",1);
define("SEAT_ERR_OCCUPIED",2);
define("SEAT_ERR_TOOMUCH",3);

  }catch (Exception $e) {
    die ('Defines already loaded. '.$e->getMessage());
  }
?>