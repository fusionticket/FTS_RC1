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

<tr class="{$class}">
  <td  valign='top'>
    <span class='has-tooltip' id='seatitem_{$seat_item->id}'>
      &nbsp;&nbsp;{$seat_item->count()} x {$category_item->cat_name}
      {assign var='seats' value=$seat_item->seats}
      {assign var='disc' value=$seat_item->discount()}
      {if $disc}
          {!with_discount!}: {$disc->discount_name}
      {/if}
      <div style='float:right'>
        {if $disc}
          {valuta value=$disc->apply_to($category_item->cat_price)}
        {else}
          {valuta value=$category_item->cat_price}
        {/if}
      </div>
      <div id='seatitem_{$seat_item->id}-tooltip' class='is-tooltip' style='display:none;'>
        {if $category_item->cat_numbering neq 'none'}
          {if $category_item->cat_numbering eq 'rows'}
            <b>{!row!}:</b><br>
          {else}
            <b>{!seat!}:</b><br>
          {/if}

          {foreach from=$seats item=seat name=foo}
              {if $category_item->cat_numbering eq 'both'}
                 {$seat->seat_row_nr} - {$seat->seat_nr}
              {elseif $category_item->cat_numbering eq 'rows'}
                 {$seat->seat_row_nr}
              {elseif $category_item->cat_numbering eq 'seat'}
                 {$seat->seat_nr}
              {/if}
              {if !$seat@last},&nbsp;{/if}

           {/foreach}
         {else}
           {!category_numbering_none!}
         {/if}
      </div>
    </span>
  </td>
  <td  valign='top' align='right' style='text-align:right'>
    {valuta value=$seat_item->total_price()}
  </td>
  {if $three_cols neq "on"}
    <td  valign='top'>
      {if $seat_item->is_expired()}
        <span style="color:#ff0000;">{!expired!}</span> <br>
      {/if}
      {if $check_out neq "on"}
        <a href='index.php?action=remove&event_id={$event_item->event_id}&cat_id={$category_item->cat_id}&item={$seat_item->id}'>
          {!remove!}
        </a>
      {/if}
    </td>
  {/if}
</tr>