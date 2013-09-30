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
 {if !$no_header}
{include file="header.tpl" name=!order_reg! header=!tnx_order_mess!}
{/if}
  <br>
  <div class="art-content-layout-wrapper layout-item-5">
    <div class="art-content-layout layout-item-6">
      <div class="art-content-layout-row">
        <div class="art-layout-cell layout-item-7 gui_form" style="width: 50%;">
          {gui->view name=order_id value=$order_id}

          {eval var=$shop_handling.handling_text_payment assign=paymentVal}
          {gui->view name=payment value=$paymentVal}

          {eval var=$shop_handling.handling_text_shipment assign=shipmentVal}
          {gui->view name=shipment value=$shipmentVal}

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
        </div>
      </div>
    </div>
  </div>
  <div class="art-content-layout-br layout-item-0"></div>
  {eval var=$confirmtext}


{if !$no_footer}
  {if $shop_order.order_payment_status eq 'none' || $shop_order.order_payment_status eq 'pending'}
    <div class="art-content-layout layout-item-1">
      <div class="art-content-layout-row" style='padding:10px;'>
        <div class="art-layout-cell layout-item-3"  style='text-align:right; width: 70%;padding:10px;'>
               {gui->button url="checkout.php?action=cancel&{$order->EncodeSecureCode($order->obj)}" name='cancel_order'}
     	  </div>
      </div>
    </div>
  {/if}

 {include file="footer.tpl"}
{/if}