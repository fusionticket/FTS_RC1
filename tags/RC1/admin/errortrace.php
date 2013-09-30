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
 * @copyright 2010
 */
  define('ft_check','langcheck');
  include_once('includes/config/init_common.php');
  header ("content-type: text/xml");
  echo "<?xml version='1.0' encoding='utf-8'?>\n";
  echo "<tracelog>\n";
  echo "<data><![CDATA[";

  if (isset($_POST['traceid'])== md5($_SHOP->secure_ID)) {
    if (!empty($_SHOP->trace_name) && file_exists($_SHOP->trace_dir.$_SHOP->trace_name)) {
      echo file_get_contents($_SHOP->trace_dir.$_SHOP->trace_name);
      unlink($_SHOP->trace_dir.$_SHOP->trace_name);
    }
  }
  echo "]]></data>\n</tracelog>\n";
?>