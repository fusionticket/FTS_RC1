{*                  %%%copyright%%%
 *
 * FusionTicket - ticket reservation system
 *  Copyright (C) 2007-2013 FusionTicket Solution Limited . All rights reserved.
 *
 * Original Design:
 *  phpMyTicket - ticket reservation system
 *   Copyright (C) 2004-2005 Anna Putrino, Stanislav Chachkov. All rights reserved.
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
<div class="art-box art-block">
  <div class="art-box-body art-block-body">
    <div class="art-bar art-blockheader">
      <h3 class="t">
        {!shopcart!}&nbsp;
        {if $cart->is_empty_f()}
          <img src="{$_SHOP_themeimages}caddie.gif">
        {else}
          <img src="{$_SHOP_themeimages}caddie_full.png" border='0'>
        {/if}
      </h3>
    </div>
    <div class="art-box art-blockcontent">
      <div class="art-box-body art-blockcontent-body">
        {if $cart->is_empty_f()}
          <div class='cart_content' align="center">{!no_tick_res!}</div>
        {else}
          {assign var="cart_overview" value=$cart->overview_f()}
          <div style='text-align:center;'>
            {if $cart_overview.expired}
              <img src='{$_SHOP_themeimages}ticket-expired.png' title="{!expired_tickets!}">  {$cart_overview.expired}<br><br>
            {elseif $cart_overview.valid}
              <img src='{$_SHOP_themeimages}ticket-valid.png' title="{!valid_tickets!}"> {$cart_overview.valid}&nbsp;&nbsp;
              <img src='{$_SHOP_themeimages}clock.gif'>  <span id="countdown1"></span>
              <script>
                $('#countdown1').countdown({
                   until: {$cart_overview.secttl},
                   compact: true,
                   format: 'MS',
                   description: 's',
                   onExpiry: function(){
                        var sURL = unescape(window.location.href);
                        alert('{!cart_expired!}');
                        window.location.href = sURL;
                        //location.reload(true);
                        }
                });
              </script>
            {/if}
          </div>
          <table border=0 style='width:100%;'>
             {$lastevent=''}

            {cart->items perevent=true}
              {if not $seat_item->is_expired()}
                  {if $lastevent neq $event_item->event_id}
                    <tr>
                      <td  colspan='2' style='font-size:10px;'>
                        <b>{$event_item->event_name}</b>
                      </td>
                    </tr>
                    {$lastevent = $event_item->event_id}
                  {/if}
                  <tr>
                  <td class='cart_content' style='font-size:10px;'>
                    &nbsp;{$seat_item->count()}&nbsp;x&nbsp;{$category_item->cat_name}
                  </td>
                  <td width="35%" valign='top'  align='right' class='cart_content' style='font-size:10px;'>
                    <b>{gui->valuta value=$seat_item->total_price()}</b>
                  </td>
                </tr>
              {/if}
             {/cart->items}
            <tr>
              <td align='center' class='cart_content' style='border-top:#cccccc 1px solid; padding-bottom:4px; font-size:10px;'>
                  {!tot_tick_price!}
                </td>
                <td  width="35%" valign='top' align='right' class='cart_content' style='border-top:#cccccc 1px solid; padding-bottom:4px; font-size:10px;'>
                   {gui->valuta value=$cart->total_price_f()}
              </td>
            </tr>
          </table>
          {if $cart_overview.valid}
             {gui->button url='button' onclick="window.location='checkout.php'" name='checkout' type=1 style="float:right;"}
          {/if}
          {gui->button url='button' onclick="window.location=index.php?action=view_cart" name='view_order' type=1 style="float:right;"}
        {/if}
        <div class="cleared"></div>
      </div>
    </div>
    <div class="cleared"></div>
  </div>
</div>
