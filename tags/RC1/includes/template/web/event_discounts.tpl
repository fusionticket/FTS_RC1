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
{assign var='event_id'    value=$smarty.post.event_id}
{assign var='category_id' value=$smarty.post.category_id}
{theme set=false}
{if $event_id}

  <div>
    <label><input class='checkbox_dark' type='radio' name='discount' value='0' checked>{!no_discount!}</label>
    {$hasPromoCode=0}
    {discount event_id=$event_id category_id=$category_id cat_price=$shop_category.category_price}
        {if $shop_discounts.discount_promo}
          {$hasPromoCode= 1}
        {else}
          <label><input class='checkbox_dark discount_{$shop_discounts.discount_id}' type='radio' name='discount' value='{$shop_discounts.discount_id}'>{$shop_discounts.discount_name}&nbsp;({valuta value=$shop_discounts.discount_price|string_format:"%.2f"}) </label>
        {/if}
    {/discount}
  </div>
  {if hasPromoCode}
     <table width='100%' border=0>
        <tr id='discount_promo_{$shop_discounts.discount_id}_tr' >
          <td width='40%' class='TblLower' >
             {!discount_promo_for!}{$shop_discounts.discount_name}
          </td>
          <td class='TblHigher'>
            <input name='discount_promo'>{printMsg key='discount_promo'}
          </td>
        </tr>
     </table>
  {/if}
{/if}