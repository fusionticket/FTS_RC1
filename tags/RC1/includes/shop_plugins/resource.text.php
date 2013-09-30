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


function smarty_resource_text_source ($tpl_name, &$tpl_source, $smarty_obj)
{
  $tpl_source = $smarty_obj->my_template_source;
 // writeLog('7.'.print_r ($smarty_obj,true));
  return true;
}

function smarty_resource_text_timestamp($tpl_name, &$tpl_timestamp, $smarty_obj)
{
  $tpl_timestamp =time();//$this->timestamp;
  return true;
}

function smarty_resource_text_secure($tpl_name, $smarty_obj)
{
  // assume all templates are secure
  return true;
}

function smarty_resource_text_trusted($tpl_name, $smarty_obj)
{
    // not used for templates
}
?>