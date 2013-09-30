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

function smarty_block_country ($params, $content, $smarty, &$repeat) {
        global $_SHOP,  $_COUNTRY_LIST;
  if ($repeat) {
                if (!isset($_COUNTRY_LIST)) {
                        If (file_exists(INC."lang".DS."countries_". $_SHOP->lang.".inc")){
                                include_once(INC."lang".DS."countries_". $_SHOP->lang.".inc");
                        }else {
                                include_once(INC."lang".DS."countries_en.inc");
      }
    }
                $countries = $_COUNTRY_LIST;
                $keys = array_keys($countries);
                $row=0;

  	}

        else {
                list($row,$countries,$keys) =$smarty->popBlockData();
    }
        if ($repeat=count($countries)>$row) {

        $smarty->assign("country",array($keys[$row],$countries[$keys[$row]]));
        $row++;
        $smarty->pushBlockData(array($row,$countries,$keys));
  }

        return $content;

  }


?>