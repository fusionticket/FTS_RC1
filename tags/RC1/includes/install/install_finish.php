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

class install_finish {
  static function precheck($Install) {
     return true;
  }

  static function postcheck($Install) {
    return true;
  }

  static function display() {
    Install_Form_Open (BASE_URL."/index.php",'', false);
    echo "<h2>Installation Completed!</h2>  You are now ready to start using Fusion Ticket.<br />\n";
    echo "For security reasons you should set the configuration file and folder to read-only by the webserver user:<br>
          - <i>includes/config/init_config.php</i>";
    if (is_writable(ROOT.DS."includes".DS."config")) {
      echo "<br>-  <i>includes/config</i>";
    }

    echo "
          <br><br><br>
          <ul>
            <li><a href='".BASE_URL."/admin/index.php'   target='_blank'>Go to Admin</a>.</li>
            <li><a href='".BASE_URL."/pos/index.php'     target='_blank'>Go to Point Of Sale</a></li>
            <li><a href='".BASE_URL."/control/index.php' target='_blank'>Go to Ticket Control Point</a></li>
          </ul>";

    Install_Form_Buttons ();
    Install_Form_Close ();
//        session_destroy();
  }
}
?>