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

<table width="100%" cellpadding="3" class="main">
  <tr>
    <td class="title">
      <h3>{!personal!}</h3>
    </td>
    <td class="title">
      <h3>{!pers_orders!}</h3>
    </td>
  </tr>
  <tr>
    <td><p>{!pers_mess!}</p></td>
    <td><p>{!pers_mess2!}</p></td>
  </tr>
  <tr>
    <td width="50%" valign="top">

      <table class="table_dark shop-table">
        {gui->view name='user_firstname' value=$user->user_firstname|clean}
        {gui->view name='user_lastname' value=$user->user_lastname|clean}
        {gui->view name='user_address' value=$user->user_address|clean}
        {gui->view name='user_address1' value=$user->user_address1|clean}
        {gui->view name='user_zip' value=$user->user_zip|clean}
        {gui->view name='user_city' value=$user->user_city|clean}
        {gui->view name='user_state' value=$user->user_state|clean}
        {gui->viewcountry name='user_country' value=$user->user_country}
        {gui->view name='user_phone' value=$user->user_phone|clean}
        {gui->view name='user_fax' value=$user->user_fax|clean}
        {gui->view name='user_email' value=$user->user_email|clean}
      </table>

	  </td>
    <td valign="top">

		  <table class="table_dark shop-table">
        <thead>
          <tr>
            <th>{!order_id!}</th>
            <th>{!order_date!}</th>
            <th>{!tickets!}</th>
            <th>{!total_price!}</th>
            <th>{!status!}</th>
          </tr>
        </thead>
        <tbody>
        {order->order_list user_id=$user->user_id order_by_date="DESC" length=6}
  	    {if $shop_order.order_status eq "cancel"}
  				<tr class='user-order-{$shop_order.order_status}'>
  			{elseif $shop_order.order_status eq "reemit" or $shop_order.order_status eq "reissue"}
  				<tr class='user-order-{$shop_order.order_status}'>
  			{elseif $shop_order.order_status eq "res"}
  				<tr class='user-order-{$shop_order.order_status}'>
        {elseif $shop_order.order_payment_status eq "paid"}
  				<tr class='user-order-{$shop_order.order_payment_status}'>
  			{elseif $shop_order.order_shipment_status eq "send"}
  				<tr class='user-order-{$shop_order.order_shipment_status}'>
  			{elseif $shop_order.order_status eq "ord"}
  				<tr class='user-order-{$shop_order.order_status}'>
  			{else}
  				<tr class='user_order_cancel'>
  			{/if}
            <td class='admin_info' align="center">{$shop_order.order_id}</td>
      			<td class='admin_info' align="center">{$shop_order.order_date}</td>
      			<td class='admin_info' align="center">{$shop_order.order_tickets_nr}</td>
      			<td class='admin_info' align="center">{$shop_order.order_total_price}</td>
      			<td class='admin_info' align="center">
      			{if $shop_order.order_status eq "cancel"}{!pers_cancel!}
      			{elseif $shop_order.order_status eq "reemit" or $shop_order.order_status eq "reissue"}{!pers_reissue!}
      			{elseif $shop_order.order_status eq "res"}{!pers_res!}
      			{elseif $shop_order.order_shipment_status eq "send"}{!pers_send!}
      			{elseif $shop_order.order_payment_status eq "paid"}{!pers_paid!}
      			{elseif $shop_order.order_status eq "ord"}{!pers_ord!}
      			{else}{!pers_unknown!}
      			{/if}</td>
 	      </tr>
 	      {/order->order_list}
        </tbody>
      </table></td>
    </tr>
</table>