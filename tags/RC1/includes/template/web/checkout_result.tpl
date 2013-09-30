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
 *}<!-- $Id$ -->{if $pm_return.caption}
    {include file="header.tpl" name=$pm_return.caption header=$pm_return.header noHeader=$no_header}
  {elseif $shop_order.order_status eq 'res'}
      {include file="header.tpl" name=!order_reserved! noHeader=$no_header}
  {elseif $pm_return.approved}
    {if $shop_order.order_payment_status eq 'paid'}
      {include file="header.tpl" name=!pay_accept! noHeader=$no_header}
    {else}
      {include file="header.tpl" name=!pay_ispending! noHeader=$no_header}
    {/if}
  {else}
    {include file="header.tpl" name=!pay_refused! noHeader=$no_header}
  {/if}

  {if $shop_order.order_payment_status eq 'paid'}
    <div class="art-content-layout layout-item-1">
      <div class="art-content-layout-row">
        <div class="art-layout-cell layout-item-3">
          {!pay_reg!}!
        </div>
      </div>
    </div>
    <div class="art-content-layout-br layout-item-0"></div>

  {/if}

  <div class="art-content-layout-wrapper layout-item-5">
    <div class="art-content-layout layout-item-6">
      <div class="art-content-layout-row">
        <div class="art-layout-cell layout-item-7 gui_form" style="width: 100%;">

            {gui->view name=order_id value=$order_id}
            {if $shop_order.order_status eq 'res'}
                {order->countdown order_id=$shop_order.order_id reserved=true}
                {$message=!buytimeleft!|replace:'~DAYS~':$order_remain.days|replace:'~HOURS~':$order_remain.hours|replace:'~MINS~':$order_remain.mins|replace:'~SECS~':$order_remain.seconds}
              {gui->view name=reservtime value=$message}
            {else}
              {eval var=$shop_handling.handling_text_payment assign=paymentVal}
              {gui->view name=payment value=$paymentVal}

              {eval var=$shop_handling.handling_text_shipment assign=shipmentVal}
              {gui->view name=shipment value=$shipmentVal}
            {/if}
            {if $order_discount_price  neq 0.0 || $order_fee neq 0.0}
              {gui->valuta value=$order_partial_price+$order_discount_price assign=orderPreDis}
              {gui->view name=order_partial_price value=$orderPreDis}
            {/if}

            {if $order_fee neq 0.0}
              {gui->valuta value=$order_fee assign=orderFee}
              {gui->view name=order_fee value=$orderFee}
            {/if}

            {if $order_discount_price neq 0.0}
              {gui->valuta value=$order_discount_price assign=orderDis}
              {gui->view name=order_discount_price value=$orderDis}
            {/if}

            {gui->valuta value=$order_total_price assign=orderTot}
            {gui->view name=total_price value=$orderTot}
            {gui->view name=orderdescription value=$order_description}

            {if $pm_return.transaction_id}
              {gui->view name=trx_id value=$pm_return.transaction_id}
            {/if}
        </div>
      </div>
    </div>
  </div>
  {if $pm_return.response}
    <div class="art-content-layout-br layout-item-0"></div>
    <p>{eval var=$pm_return.response}</p>
  {/if}

  <div class="art-content-layout-br layout-item-0"></div>
  <div class="art-content-layout layout-item-1">
    <div class="art-content-layout-row" style='padding:10px;'>
      <div class="art-layout-cell layout-item-3"  style='text-align:left; width: 30%;padding:10px;'>
        <form method='post' action='{$_SHOP_root}index.php'>
          {gui->button name="go_home" value="{!order_more_tickets!}" url="submit" }
        </form>
   	  </div>
      <div class="art-layout-cell layout-item-3"  style='text-align:right; width: 70%;padding:10px;'>
        {if $pm_return.approved}
           {if $shop_order.order_status neq 'res'}
             {gui->button url="{$_SHOP_root_secured}checkout.php?action=print&mode=2&{$order->EncodeSecureCode($order->obj)}" target='_blank' name='printinvoice'}
           {else}
             {gui->button url="{$_SHOP_root_secured}checkout.php?action=view&id={$shop_order.order_id}&{$order->EncodeSecureCode($order->obj)}" name='payreservenow'}
           {/if}
           {if $shop_order.order_payment_status eq 'payed' || $shop_order.order_payment_status eq 'paid'}
             {gui->button url="{$_SHOP_root_secured}checkout.php?action=print&mode=1&{$order->EncodeSecureCode($order->obj)}" target='_blank' name='printticket'}
           {/if}
        {/if}
   	  </div>
    </div>
  </div>
  <br/>
{include file="footer.tpl" noFooter=$no_footer}