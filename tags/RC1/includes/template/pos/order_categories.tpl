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
 *}<div style='margin: 10px;' class='admin-button'>
<label for="category_id_{$shop_category.category_id}">
  <div id="catcolor" style="display:inline-block;vertical-align:top; background-color:{$shop_category.category_color};padding:0px; margin:8px;">
    <input type="radio" style='margin:2px 4px 2px 2px;' class='category_id_radio' id="category_id_{$shop_category.category_id}" name="category_id" value="{$shop_category.category_id}" {if $shop_category.category_free == 0}disabled="true"{/if}>
  </div>
<div style='cursor:hand;display:inline-block;margin-left: 20px;'>{$shop_category.category_name} - <b>{valuta value={$shop_category.category_price}}</b><br/>
{if $shop_category.category_free==0}
  <span style='color:red'><b>{!category_sold!}</b></span>
{elseif $shop_category.category_free/$shop_category.category_size ge 0.2}
  {!free_seat_cat!} <span>{$shop_category.category_free}</span> ({!approx!})
{else}
  {!free_seat_cat!} <span style='color:Orange; '><b>{$shop_category.category_free}</b></span>({!approx!})
{/if}
</div>
</label>
</div>