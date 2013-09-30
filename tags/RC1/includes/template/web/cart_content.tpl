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
 * By USING this file you are agreeing to the above terms of use.
 * REMOVING this licence does NOT remove your obligation to the terms of use.
 *
 * The "GNU General Public License" (GPL) is available at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * Contact help@fusionticket.com if any conditions of this licencing isn't
 * clear to you.
 *}<!-- $Id$ -->
<div class="art-content-layout-br layout-item-0"></div>

 {if $cart->is_empty_f()}
  <div class="art-content-layout layout-item-1">
    <div class="art-content-layout-row" style='padding:10px;'>
      <div class="art-layout-cell layout-item-3"  style='text-align:center; width: 100%;padding:10px;'>
        <span class='title'><br>{!cart_empty!}<br><br> </span>
  	  </div>
    </div>
  </div>
{else}
  {counter start="0" assign="count"}
  <table width="100%" class='table_midtone'>
    <tr class='small_table_dark' >
			<th>{!tickets!}</th>
			<th width='80'>{!total!}</th>
			<th width='70'>{!expires_in!}</th>
    </tr>
    {$lastevent=''}
    {cart->items perevent=true}
      {if $lastevent neq $event_item->event_id}
       {$class="{cycle name='events' values='tr_0,tr_1'}"}
        <tr class="{$class}">
          <td  colspan='3'>
             <ul>
                <li>
                  <b>{!event_name!}:</b> {$event_item->event_name}
                </li>
                <li>
                   <b>{!date!}:</b> {$event_item->event_date|date_format:!shortdate_format!} - {$event_item->event_time|date_format:!time_format!}
                </li>
                <li>
                  <b>{!venue!}:</b> {$event_item->ort_name} - {$event_item->ort_city}
                </li>
              </ul>
			</td>
    </tr>
        {$lastevent = $event_item->event_id}
      {/if}
      {if $check_out eq "on"}
        {if not $seat_item->is_expired()}
          {include file="cart_subcontent.tpl" check_out="on" seat_item=$seat_item class=$class}
        {/if}
      {else}
        {include file="cart_subcontent.tpl" seat_item=$seat_item class=$class}
      {/if}
    {/cart->items}
    <tr class="{cycle name='events' values='TblHigher,TblLower'}">
      <td  colspan='1' align='right'>
        <b>{!total_price!}</b>
      </td>
      <td align='right' style='text-align:right'>
       <b> {valuta value=$cart->total_price_f()}</b> {* //it seems this is not needed: |string_format:"%.2f" *}
      </td>
      <td style='text-align:right;'>
        <img src='{$_SHOP_themeimages}clock.gif' valign="middle" align="middle" style='margin:0px;'> <span id="countdown2">{$seat_item->ttl()} </span> {!minutes!}
        <script>
          $('#countdown2').countdown({ until: {$seat_item->ttlsec()}, compact: true,  format: 'M', description: '' });
        </script>
      </td>
    </tr>
  </table>
{/if}