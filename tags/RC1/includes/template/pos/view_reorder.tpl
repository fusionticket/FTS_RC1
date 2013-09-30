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
 *}{include file='header.tpl'}<!-- $Id$ -->
<table width='100%' border='0'>
  <tr>
  	<td width='50%' valign='top'>
    	{order->order_list order_id=$smarty.request.order_id}
    	  <table  cellspacing='1' cellpadding='4' border='0'>
      		<tr>
      		  <td class='title'>{#order_id#} {$shop_order.order_id}</td>
      		  <td align='right'>
        			<table width='100' >
        			  <tr>
    		   		    <td align='center'>
    				        {if $shop_order.order_status neq "cancel" and $shop_order.order_status neq "reemit" and $shop_order.order_status neq "reissue"}
              				<a href='checkout.php?action=print&{$order->EncodeSecureCode($shop_order.order_id)}&mode=3'><img border='0' src='{$_SHOP_themeimages}printer.gif'></a>
              				<a href='javascript:if(confirm("Delete Order?")){literal}{location.href="view.php?action=cancel_order&order_id={/literal}{$shop_order.order_id}{literal}";}{/literal}'>
              				<img border='0' src='{$_SHOP_themeimages}trash.png' /></a>
           					{/if}
        				  </td>
        				</tr>
      			  </table>
      		  </td>
      		</tr>
      		<tr>
    	  	  <td class='admin_info'>{!number_tickets!}</td>
    		    <td class='subtitle'>{$shop_order.order_tickets_nr}</td>
    		</tr>
      	<tr>
    		  <td class='admin_info'>{!user!} {!id!}</td>
    		  <td class='subtitle'>{$shop_order.order_user_id}</td>
    		</tr>
    		<tr>
    		  <td class='admin_info'>{!total_price!}</td>
    		  <td class='subtitle'>{$shop_order.order_total_price|string_format:"%1.2f"} {$organizer_currency}</td>
    		</tr>
    		<tr>
    		  <td class='admin_info'>{!order_date!}</td>
    		  <td class='subtitle'>{$shop_order.order_date}</td>
    		</tr>
    		<tr>
    		  <td class='admin_info'>{!status!}</td>
    		  <td class='subtitle'>
    		  {if $shop_order.order_status eq "res"}
    		    <font color='orange'>{!reserved!}</font>
    		  {elseif $shop_order.order_status eq "ord"}
    		    <font color='blue'>{!ordered!}</font>
    		  {elseif $shop_order.order_status eq "cancel"}
    		    <font color='#cccccc'>{!cancelled!}</font>
    		  {elseif $shop_order.order_status eq "reemit" or $shop_order.order_status eq "reissue"}
    		    <font color='#ffffcc'>{!reemitted!}</font>
    		    (<a href='view.php?action=view_order&order_id={$shop_order.order_reemited_id}'>
    		      {$shop_order.order_reemited_id}</a>)
    		  {/if}
    		  </td>
    		</tr>
    		<tr>
    		  <td class="admin_info">{!pay!} {!status!}</td>
    		  <td class="subtitle">
    		  {if $shop_order.order_payment_status eq "none"}
    		    <font color="#FF0000">{!notpaid!}</font>
    		  {elseif $shop_order.order_payment_status eq "paid"}
    		  	<font color='green'>{!paid!}</font>
    		  {/if}
    		  </td>
    		</tr>
    		<tr>
    		  <td class="admin_info">{!Shipment!} {#status#}</td>
    		  <td class="subtitle">
    		  {if $shop_order.order_shipment_status eq "none"}
    		  	<font color="#FF0000">{!notsent!}</font>
    		  {elseif $shop_order.order_shipment_status eq "send"}
    		  	<font color='green'>{!sent!}</font>
    		  {/if}
      	  	  </td>
    		</tr>
  	  </table>
  	</td>
  	<td width="50%" valign="top">
   	  <table width="100%">
  		<tr>
  	 	  <td class="title" valign="top">{!personal!}</td>
  		  <td class="title" valign="top">&nbsp;</td>
  		</tr>
  	  	<tr>
  		  <td class="admin_info" valign="top">{!user_firstname!}</td>
  		  <td class="sub_title" valign="top">{$user_order.user_firstname}</td>
  	  	</tr>
  	  	<tr>
  		  <td class="admin_info" valign="top">{!user_lastname!}</td>
  		  <td class="sub_title" valign="top">{$user_order.user_lastname}</td>
  	  	</tr>
  		<tr>
  		  <td class="admin_info" valign="top">{!user_address!} </td>
  		  <td class="sub_title" valign="top">{$user_order.user_address}</td>
  	  	</tr>
  	  	<tr>
  		  <td class="admin_info" valign="top">{!user_address1!}</td>
  		  <td class="sub_title" valign="top">{$user_order.user_address1}</td>
  	  	</tr>
  	  	<tr>
  		  <td class="admin_info" valign="top">{!user_zip!}</td>
  		  <td class="sub_title" valign="top">{$user_order.user_zip}</td>
  	  	</tr>
  	  	<tr>
  		  <td class="admin_info" valign="top">{!user_city!}</td>
  		  <td class="sub_title" valign="top">{$user_order.user_city}</td>
  	  	</tr>
  	  	<tr>
  		  <td class="admin_info" valign="top">{!user_state!}</td>
  		  <td class="sub_title" valign="top">{$user_order.user_state}</td>
  	  	</tr>
  		<tr>
  		  <td class="admin_info" valign="top">{!user_country!}</td>
  		  <td class="sub_title" valign="top">{$user_order.user_country}</td>
  	  	</tr>
  	  	<tr>
  		  <td class="admin_info" valign="top">{!user_phone!}</td>
  		  <td class="sub_title" valign="top">{$user_order.user_phone}</td>
  	  	</tr>
  	  	<tr>
  		  <td class="admin_info" valign="top">{!user_email!}</td>
  		  <td class="sub_title" valign="top">{$user_order.user_email}</td>
  	  	</tr>
   	  </table>
  	</td>
  </tr>
  <tr>
    <td colspan="2">
   	  <form name='f' action='view.php' method='post'>
      <table width='100%' border='0' cellspacing='0' cellpadding='1'style='padding:5px; border:#45436d 1px solid;'>
  		  <tr>
  		    <td rowspan='7'><img src='{$_SHOP_themeimages}dot.gif' width='1' height='100'></td>
  		    <td colspan='3' align='left'><font size='2'> <b>{!payment!}</b></font></td>
  		  </tr>
  		  {handling sp='on' total=$shop_order.order_total_price}
    		<tr>
    		  <td class='payment_form'>
    		  {if $shop_handling.handling_shipment eq 'sp'}
    		  	<input style='border:0px;' type='radio' id='{$shop_handling.handling_id}_check' name='handling' value='{$shop_handling.handling_id}'/>
    		  {else}
    		  	<input style='border:0px;' type='radio' id='{$shop_handling.handling_id}_check' name='handling' value='{$shop_handling.handling_id}'/>
    		  {/if}
    		  </td>
    		  <td class='payment_form'><label for='{$shop_handling.handling_id}_check'>{!payment!}
    		  {if $shop_handling.handling_text_payment}{eval var=$shop_handling.handling_text_payment}{/if}
    		  <br />{!shipment!}
    		  {if $shop_handling.handling_text_shipment}{eval var=$shop_handling.handling_text_shipment}{/if}
    		  </label>
    		  </td>
    		  <td class='payment_form'>
          + {$shop_handlingfee|string_format:"%.2f"} {$organizer_currency}
    		  </td>
    		</tr>
  		  {/handling}
      </table>
  	  <br />
  	  <input type="hidden" name="order_id" value='{$shop_order.order_id}' />
  	  <input type='submit' name='submit_payment' value='{!order_it!}' />
  	  <input type='hidden' name='action' value='order_res' />
  	  </form>
  	</td>
    </tr>
    <tr>
    	<td colspan="2">
  	{/order->order_list}
    	  <table width='100%' cellspacing='1' cellpadding='4'>
  		<tr>
  		  <td class='title' colspan='8'>{!tickets!}<br></td>
  		</tr>
  		<tr>
  		  <td class='subtitle'>{!id!}</td>
  		  <td class='subtitle'>{!event!}</td>
  		  <td class='subtitle'>{!category!}</td>
  		  <td class='subtitle'>{!zone!}</td>
  		  <td class='subtitle'>{!seat!}</td>
  		  <td class='subtitle'>{!discount!}</td>
  		  <td class='subtitle'>{!price!}</td>
  		</tr>
  		{order->tickets order_id=$shop_order.order_id}
  		{counter assign='row' print=false}
  		<tr class='admin_list_row_{$row%2}'>
  		  <td class='admin_info'>{$shop_ticket.seat_id}</td>
  		  <td class='admin_info'>{$shop_ticket.event_name}</td>
  		  <td class='admin_info'>{$shop_ticket.category_name}</td>
  		  <td class='admin_info'>{$shop_ticket.pmz_name}</td>
  		  <td class='admin_info'>
  		  {if not $shop_ticket.category_numbering or $shop_ticket.category_numbering eq "both"}
  		  	{$shop_ticket.seat_row_nr}  -  {$shop_ticket.seat_nr}
  		  {elseif $shop_ticket.category_numbering eq "rows"}
  		  	{!row!}{$shop_ticket.seat_row_nr}
  		  {else}
  		  	---
  		  {/if}</td>
  		  <td class='admin_info'>{$shop_ticket.discount_name}</td>
  		  <td class='admin_info' align='right'>{$shop_ticket.seat_price}</td>
  		  <td class='admin_info' align='center'><a href='javascript:if(confirm("{!cancel_ticket!}  {$shop_ticket.seat_id}?")){literal}{location.href="view.php?action=cancel_ticket&order_id={/literal}{$shop_ticket.seat_order_id}&ticket_id={$shop_ticket.seat_id}{literal}";}{/literal}'><img border='0' src='{$_SHOP_themeimages}trash.png'></a></td>
  		</tr>
  		{/order->tickets}
  	  </table>
  	<br />
  	</td>
  </tr>
</table>

{include file='footer.tpl'}