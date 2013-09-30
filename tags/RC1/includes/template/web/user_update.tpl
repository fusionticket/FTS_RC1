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
 *}<!-- $Id$ -->{include file="header.tpl" name=!personal!}
    {gui->StartForm action="{$_SHOP_root_secured}index.php" method='post' model='user' id="userregister"}
    <input type='hidden' name='action' value='person_user_edit' />
    <input type='hidden' name='user_id' value='{$user->user_id}' />
    <input type="hidden" name="submit_update" value="yes" />
		  {include file='user_form.tpl'}

      {if $user->is_member}
        {gui->input autocomplete='off'  type='password' name='old_password' size='10'  maxlength='10' required=true}
          {gui->input autocomplete='off' type='password' name='password1' size='10' maxlength='10' id="password" caption=!new_password!}
          {gui->input autocomplete='off' type='password' name='password2' size='10'  maxlength='10'}
		  {/if}
	    <div class="art-content-layout-br layout-item-0"></div>
      <div class="art-content-layout layout-item-1">
        <div class="art-content-layout-row" style='padding:10px;'>
          <div class="art-layout-cell layout-item-3"  style='text-align:right; width: 100%;padding:10px;'>
         	  {gui->button url='submit' id='submit' name='submit' value="{!user_update!}"}
       	  </div>
        </div>
      </div>
  </form>