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
{include file='header.tpl' name=!act_name!}
{if !$user->activate()}
   {include file="user_registred.tpl"}
   <form action='{!PHP_SELF!}' method='post'>
     {ShowFormToken name='TryActivateUser'}
     <div class="art-content-layout-wrapper layout-item-5">
        <div class="art-content-layout layout-item-6">
          <div class="art-content-layout-row">
            <div class="art-layout-cell layout-item-7 gui_form" style="width: 100%;">
              <h4>{!act_enter_title!}</h4>
              <p>{!act_enter_code!}<br><br></p>
              {gui->input caption=!act_code! type='text' name='uar' value="{$smarty.request.uar}" size='50'}
              <a href='index.php?action=resend_activation'>{!act_notarr!}</a><br><br>
         	  </div>
          </div>
        </div>
      </div>
      <div class="art-content-layout-br layout-item-0"></div>
      <div class="art-content-layout layout-item-1">
        <div class="art-content-layout-row" style='padding:10px;'>
          <div class="art-layout-cell layout-item-3"  style='text-align:right; width: 100%;padding:10px;'>
         	  {gui->button url='submit' id='submit' name='submit' value="{!act_send!}"}
       	  </div>
        </div>
      </div>
  </form>
{else}
    {!success_activate!}
{/if}