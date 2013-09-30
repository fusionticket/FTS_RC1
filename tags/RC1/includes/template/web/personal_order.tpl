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
{include file="header.tpl" name=!order_info!}
	{order->order_list user_id=$user->user_id order_id=$smarty.request.id limit='1' handling=true}
	{if $shop_order.order_status eq "res"}
			{$status ="<font style='color:orange'>{!reserved!}</font>"}
	{elseif $shop_order.order_status eq "ord"}
 		{$status ="<font style='color:blue'>{!ordered!}</font>"}
	{elseif $shop_order.order_status eq "cancel"}
 		{$status ="<font style='color:#cccccc'>{!cancelled!}</font>"}
	{elseif $shop_order.order_status eq "reemit" or $shop_order.order_status eq "reissue"}
 		{$status ="<font style='color:#ffffcc'>{!reissued!}</font>
			           <a href='index.php?action=view_order&order_id={$shop_order.order_reemited_id}'>
      			    	 {$shop_order.order_reemited_id}</a>"}
	{/if}
  {gui->valuta value=$shop_order.order_total_price assign=orderPreDis}

  {if $shop_order.order_shipment_status eq "none"}
    {$shipment ="<font color='#FF0000'>{!notsent!}</font>"}
  {elseif $shop_order.order_shipment_status eq "send"}
    {$shipment ="<font color='green'>{!sent!}</font>"}
  {/if}

 	{if $shop_order.order_payment_status eq "none"}
   	{$payment ="<font style='color:#FF0000'>{!notpaid!}</font>"}
 	{elseif $shop_order.order_payment_status eq "pending"}
 		{$payment ="<font style='color:orange'>{!pending!}</font>"}
 	{elseif $shop_order.order_payment_status eq "paid"}
 		{$payment ="<font style='color:green'>{!paid!}</font>"}
 	{/if}

  <div class="art-content-layout-wrapper layout-item-5">
    <div class="art-content-layout layout-item-6">
      <div class="art-content-layout-row">
        <div class="art-layout-cell layout-item-7 gui_form" style="width: 100%;">
          {gui->view name='order_id' value="#{$shop_order.order_id}"}

          {gui->view name='number_tickets' value="#{$shop_order.order_tickets_nr}"}
          {gui->view name='total_price' value="{$orderPreDis}"}
          {gui->view name='order_date' value="{$shop_order.order_date}"}
          {gui->view name='status' value="{$status}"}
          {eval var=$shop_order.handling_text_payment assign=paymentVal}
          {gui->label name='payment'}
            {gui->view value=$paymentVal nolabel=true}
            {gui->view value=$payment nolabel=true}
          {/gui->label}
          {eval var=$shop_order.handling_text_shipment assign=shipmentVal}
          {gui->label name='shipment'}
            {gui->view value=$shipmentVal nolabel=true}
            {gui->view value=$shipment nolabel=true}
          {/gui->label}
        </div>
      </div>
    </div>
  </div>
  <div class="art-content-layout layout-item-1">
    <div class="art-content-layout-row" style='padding:10px;'>
      <div class="art-layout-cell layout-item-3"  style='text-align:right; width: 100%;padding:10px;'>
        {if  $shop_order.order_status eq "ord"}
           {gui->button url="checkout.php?action=print&mode=2&{$order->EncodeSecureCode($shop_order.order_id)}" target='_blank' name='printinvoice'}
           {if $shop_order.order_payment_status eq 'payed' || $shop_order.order_payment_status eq 'paid'}
             {gui->button url="checkout.php?action=print&mode=1&{$order->EncodeSecureCode($shop_order.order_id)}" target='_blank' name='printticket'}
           {/if}
        {/if}
   	  </div>
    </div>
  </div>




        {* Reserve to Order *}
        {if $shop_order.order_status eq "res"}
          <div class="art-content-layout-br layout-item-0"></div>
          <div class="art-content-layout layout-item-1">
            <div class="art-content-layout-row" style='padding:10px;'>
              <div class="art-layout-cell layout-item-3"  style='width: 100%;padding:10px;'>
                {order->countdown order_id=$shop_order.order_id reserved=true}
                {!buytimeleft!|replace:'~DAYS~':$order_remain.days|replace:'~HOURS~':$order_remain.hours|replace:'~MINS~':$order_remain.mins|replace:'~SECS~':$order_remain.seconds}
                {!autocancel!}
           	  </div>
            </div>
          </div><br>
          <div class="art-content-layout-wrapper layout-item-5">
            <div class="art-content-layout layout-item-6">
              <div class="art-content-layout-row">
                <div class="art-layout-cell layout-item-7 gui_form" style="width: 100%;">
                  {gui->startform action="{$_SHOP_root_secured}checkout.php"}
            			  <input type="hidden" name="id" value='{$shop_order.order_id}' />
            	  		<input type='hidden' name='sor' value='{$order->EncodeSecureCode($shop_order.order_id,'')}' />
             	  		<input type='hidden' name='action' value='order_res' />

                    <table width='100%' border='0' cellspacing='0' cellpadding='1'style='padding:5px; border:#45436d 1px solid;'>
          				    <tr>
          				      <td rowspan='7'><img src='{$_SHOP_themeimages}dot.gif' width='1' height='100'></td>
          				      <td colspan='3' align='left'><font size='2'> <b>{!payment!}</b></font></td>
          				    </tr>
          		        {order->tickets order_id=$shop_order.order_id min_date='on'}
                        {assign var="event_date" value=$shop_ticket_min_date}
          				    {/order->tickets}

                      {handling www='on' event_date=$event_date total=$shop_order.order_total_price}

                      <tr>
                        <td class='payment_form'>
                          <input style='border:0px;' type='radio' id='{$shop_handling.handling_id}_check' name='handling' value='{$shop_handling.handling_id}' />
                        </td>
              				  <td class='payment_form'>
                          <label for='{$shop_handling.handling_id}_check'>{!payment!}
              				      {if $shop_handling.handling_text_payment}{eval var=$shop_handling.handling_text_payment}{/if}
              		          <br />{!shipment!}
              				      {if $shop_handling.handling_text_shipment}{eval var=$shop_handling.handling_text_shipment}{/if}
              				    </label>
              				  </td>
              				  <td class='payment_form'>
                          + {$shop_handling.fee|string_format:"%.2f"} {$organizer_currency}
              				  </td>
              				</tr>

                      {/handling}

                    </table>
              {gui->EndForm}
        </div>
      </div>
    </div>
  </div>

        {/if}


        {* Pay for unpaid order *}
        {if ($shop_order.order_status eq "ord") and ($shop_order.order_payment_status neq "paid" && $shop_order.order_payment_status neq "payed") }
          <div class="art-content-layout-br layout-item-0"></div>
          <div class="art-content-layout layout-item-1">
            <div class="art-content-layout-row" style='padding:10px;'>
              <div class="art-layout-cell layout-item-3"  style='width: 100%;padding:10px;'>
        	      {order->countdown order_id=$shop_order.order_id}
        	      {if $order_remain.forever}
        	        {!paytimeforever!}
        	      {else}
          	      {!paytimeleft!|replace:'~DAYS~':$order_remain.days|replace:'~HOURS~':$order_remain.hours|replace:'~MINS~':$order_remain.mins|replace:'~SECS~':$order_remain.seconds}
                {/if}
          	    {!autocancel!}
           	  </div>
            </div>
          </div><br>
          <div class="art-content-layout-wrapper layout-item-5">
            <div class="art-content-layout layout-item-6">
              <div class="art-content-layout-row">
                <div class="art-layout-cell layout-item-7 gui_form" style="width: 100%;">

            {order->paymentForOrder order_id=$shop_order.order_id}
            {eval var=$payment_tpl}
              </div>
            </div>
          </div>
        </div>
     {/if}

  	{/order->order_list}
  	<br>
      {if $user->logged}
   	     <a href="?action=person_orders">{!go_back!}</a>
   	  {/if}
   	  <div class="art-content-layout-br layout-item-0"></div>
		  	  <H3 style='margin:0px;'>{!tickets!}</h3>
  	  <table width='100%' cellspacing='1' cellpadding='4'>
			<tr>
			  <th >{!event!}</th>
        <th >{!event_date!}</th>
			  <th >{!category!}</th>
			  <th >{!zone!}</th>
			  <th >{!seat!}</th>
			  <th >{!discount!}</th>
			  <th >{!price!}</th>
			</tr>
			{order->tickets order_id=$shop_order.order_id}
			{counter assign='row' print=false}
			<tr class="{cycle name='events' values='tr_0,tr_1'}">
			  <td><a href="index.php?event_id={$shop_ticket.event_id}" title="{!open_event!}">{$shop_ticket.event_name}</a></td>
        <td>{$shop_ticket.event_date}  {$shop_ticket.event_time}</td>
			  <td>{$shop_ticket.category_name}</td>
			  <td>{$shop_ticket.pmz_name}</td>
			  <td>
  			  {if not $shop_ticket.category_numbering or $shop_ticket.category_numbering eq "both"}
  			  	{$shop_ticket.seat_row_nr}  -  {$shop_ticket.seat_nr}
  			  {elseif $shop_ticket.category_numbering eq "rows"}
  			  	{!row!}{$shop_ticket.seat_row_nr}
  			  {else}
            na
  			  {/if}
			  </td>
			  <td>
			  {if $shop_ticket.discount_name}
			  {$shop_ticket.discount_name}
			  {else}
			  {!none!}
			  {/if}
			  </td>
			  <td style='text-align:right'>{gui->valuta value=$shop_ticket.seat_price}</td>
			</tr>
			{/order->tickets}
		  </table>
          <div class="art-content-layout-br layout-item-0"></div>
      {if $user->logged}
   	     <a href="?action=person_orders">{!go_back!}</a>
   	  {/if}