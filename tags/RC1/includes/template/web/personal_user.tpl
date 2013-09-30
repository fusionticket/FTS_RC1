{*                  %%%copyright%%%
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
 *}<!-- $Id$ -->
{include file="header.tpl" name=!personal! header=!pers_mess!}
      <div class="art-content-layout-br layout-item-0"></div>
  <div class="art-content-layout-wrapper layout-item-5">
    <div class="art-content-layout layout-item-6">
      <div class="art-content-layout-row">
        <div class="art-layout-cell layout-item-7 gui_form" style="width: 100%;">
        {gui->view name='user_firstname' value=$user->user_firstname|clean}
        {gui->view name='user_lastname' value=$user->user_lastname|clean}
        {gui->view name='user_address' value=$user->user_address|clean}
        {gui->view name='user_address1' value=$user->user_address1|clean}
        {gui->view name='user_zip' value=$user->user_zip|clean}
        {gui->view name='user_city' value=$user->user_city|clean}
        {gui->view name='user_state' value=$user->user_state|clean}
        {gui->viewcountry name='user_country' value=$user->user_country}
        {gui->view name='user_phone' value=$user->user_phone|clean}
        {gui->view name='user_fax' value=$user->user_fax|clean}
        {gui->view name='user_email' value=$user->user_email|clean}
      </div>
</div>
</div>
</div>
  <div class="art-content-layout-br layout-item-0"></div>
  <div class="art-content-layout layout-item-1">
    <div class="art-content-layout-row" style='padding:10px;'>
      <div class="art-layout-cell layout-item-3"  style='text-align:right; width: 100%;padding:10px;'>
     	  {gui->button url='?action=person_user_edit' id='checkout-commit' name='submit' value="{!edit_user!}"}
   	  </div>
    </div>
  </div>
	  </form>