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
{assign var='order_id' value=$smarty.request.order_id}
{if !$order_search}
  {order->order_list curr_order_id="$order_id $cur_order_dir" first=0 length=1 not_hand_payment=$not_hand_payment hand_shipment=$hand_shipment place=$place status=$status not_status=$not_status not_sent=$not_sent order=$orderby order_search=$order_search}
	  {assign var='next_order_id' value=$shop_order.order_id}
	{/order->order_list}
{/if}
  <table width='100%' border='0'>
    {order->order_list order_id=$order_id handling=true}
      <tr>
        <td width='50%' valign='top'>
          <table width='99%' border='0'>
            <tr>
              <td class='title' colspan='1' >
                {!order_id!} {$shop_order.order_id}
              </td>
              <td class='title'  align='right'>&nbsp;
                {include file='process_actions.tpl' shop_order=$shop_order}
              </td>
            </tr>
             <tr>
              <td class='admin_info'>{!order_place!}</td>
              <td class='subtitle'>{$shop_order.order_place}
                </td>
      				</tr>

            <tr>
              <td class='admin_info'>{!number_tickets!}</td>
              <td class='subtitle'>{$shop_order.order_tickets_nr}</td>
            </tr>
            <tr>
              <td class='admin_info'>{!user_id!}</td>
              <td class='subtitle'>{$shop_order.order_user_id}</td>
            </tr>
            <tr>
              <td class='admin_info'>{!total_price!}</td>
              <td class='subtitle'>{valuta value=$shop_order.order_total_price|string_format:"%1.2f"}</td>
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
                {elseif $shop_order.order_status eq "pros"}
                  <font color='blue'>{!processed!}</font>
                {elseif $shop_order.order_status eq "cancel"}
                  <font color='#cccccc'>{!cancelled!}</font>
                {elseif $shop_order.order_status eq "reemit" or $shop_order.order_status eq "reissue"}
                  <font color='#ffffcc'>{!reissued!}</font>
                  (<a href='view.php?action=view_order&order_id={$shop_order.order_reemited_id}'>
                    {$shop_order.order_reemited_id}
                  </a>)
                {/if}

            {* Reserve to Order *}
            {if $shop_order.order_status eq "res"}
      				<form name='f' action='view.php?order_id={$shop_order.order_id}' method='post' style='float:RIGHT'>
                    <input type='hidden' name='personal_page' value='orders' />
                 		{ShowFormToken name='reorder'}

                    {order->tickets order_id=$shop_order.order_id min_date='on'}
                    <input type='hidden' name='min_date' value='{$shop_ticket_min_date}' />
                    {/order->tickets}

                    <input type='hidden' name='action' value='reorder' />
                    <input type="hidden" name="user_id" value="{$shop_order.order_user_id}" />
                    <input type="hidden" name="order_id" value="{$shop_order.order_id}" />
                    {gui->button url='submit' name='submit' value='Order' tooltiptext={!reserv_cancel!}}
                  </form>
                  </td>
                </tr>
              <tr>
                <td colspan="2">
                  {order->countdown order_id=$shop_order.order_id reserved=true}
                  {!buytimeleft!|replace:'~DAYS~':$order_remain.days|replace:'~HOURS~':$order_remain.hours|replace:'~MINS~':$order_remain.mins|replace:'~SECS~':$order_remain.seconds}<br>
                  <br />
      		        {!autocancel!}
                </td>
      				</tr>
            {/if}
            {* End Reserve to Order *}
          	<tr>
    					<td class="admin_info" valign="top">{!handling_payment!}</td>
           		<td class="subtitle" valign="top">{$shop_order.handling_text_payment}</td>
    				</tr>


            <tr>
              <td class="admin_info">{!paymentstatus!}</td>
              <td class="subtitle">
                {if $shop_order.order_payment_status eq "none"}
                  <font color="#FF0000">{!notpaid!}</font>
                {elseif $shop_order.order_payment_status eq "pending"}
          	  		<font color="orange">{!pending!}</font>
                {elseif $shop_order.order_payment_status eq "paid"}
                  <font color='#00CC00'>{!paid!}</font>
                {/if}
            {* Pay for unpaid order *}
            {if ($shop_order.order_status neq "res") and
                ($shop_order.order_status neq "cancel") and
    				    ($shop_order.order_payment_status eq "none") and
    				    ($shop_order.order_payment_status neq "pending")}
                  <form name='manualpayment' action='view.php' method='post' style="float:right">
                    <input type="hidden" name="action" value="setpaid" />
        						<input type="hidden" name="order_id" value="{$shop_order.order_id}" />
                    {gui->button url="submit" name='submit' value="{!change_order_to_paid!}" onclick="return alert(\"{!change_order_to_paid_confirm!}\"))"}
                  </form>
                </td>
              </tr>
              <tr>
                <td colspan="2">

                  {order->countdown order_id=$shop_order.order_id pos=true}
                  {if !$order_remain.forever} {* Orders that dont expire wont complain about being cancelled *}
                    <br />
                    <strong>
                    <span style="font-size:90%;">
                      {!paytimeleft!|replace:'~DAYS~':$order_remain.days|replace:'~HOURS~':$order_remain.hours|replace:'~MINS~':$order_remain.mins|replace:'~SECS~':$order_remain.seconds}<br>
          						{!autocancel!}
          						{!payhere!}
                    </span></strong>
                  {/if}

      			  		<br />
      			  		{order->tickets order_id=$shop_order.order_id min_date='on'}
                    <input type='hidden' name='min_date' value='{$shop_ticket_min_date}' />
                  {/order->tickets}
                  <span style="font-size:90%;">
                  <style>.table_dark { width:100% }</style>
                  {order->paymentForOrder order_id=$order_id}
                  {eval var=$payment_tpl}
                  </span>
               {/if}
                   </td>
              </tr>
         {* End Pay unpaid order... Works better than i thought it would *}
          	<tr>
    					<td class="admin_info" valign="top">{!handling_shipment!}</td>
            	<td class="subtitle" valign="top">{$shop_order.handling_text_shipment}</td>
    				</tr>

            <tr>
              <td class="admin_info">{!shipmentstatus!}</td>
              <td class="subtitle">
                {if $shop_order.order_shipment_status eq "none"}
                  <font color="#FF0000">{!notsent!}</font>
                {elseif $shop_order.order_shipment_status eq "send"}
                  <font color='#00CC00'>{!sent!}</font>
                {/if}
           	{if ($shop_order.order_status neq "res") and
           	    ($shop_order.order_status neq "cancel")	and
           	    ($shop_order.order_payment_status eq "paid") and
           	    ($shop_order.order_shipment_status neq "send") and
           	    (($shop_order.handling_shipment eq 'sp') or ($shop_order.handling_shipment eq 'post'))}
        					<form name='f' action='view.php' method='post' style="float:right">
        						<input type="hidden" name="action" value="setsend" />
        						<input id="order-id" type="hidden" name="order_id" value="{$shop_order.order_id}" />
      				      {gui->button url="submit" name='submit' value="{!change_order_to_send!}"}
      	     			</form>
       			{/if}
      			             </td>
            </tr>

     			</table>
        </td>
        <td width="50%" valign="top" align='right'>
        {if $user_order.user_firstname or $user_order.user_lastname}
          <table width="99%" border=0>
            <tr>
              <td class="title" colspan=2 valign="top">{!pers_info!}</td>
            </tr>
            <tr>
              <td class="admin_info" valign="top">{!user_firstname!}</td>
              <td class="subtitle" valign="top">{$user_order.user_firstname}</td>
            </tr>
            <tr>
              <td class="admin_info" valign="top">{!user_lastname!}</td>
              <td class="subtitle" valign="top">{$user_order.user_lastname}</td>
            </tr>
            <tr>
              <td class="admin_info" valign="top">{!user_address!} </td>
              <td class="subtitle" valign="top">{$user_order.user_address}</td>
            </tr>
            <tr>
              <td class="admin_info" valign="top">{!user_address1!}</td>
              <td class="subtitle" valign="top">{$user_order.user_address1}</td>
            </tr>
            <tr>
              <td class="admin_info" valign="top">{!user_zip!}</td>
              <td class="subtitle" valign="top">{$user_order.user_zip}</td>
            </tr>
            <tr>
              <td class="admin_info" valign="top">{!user_city!}</td>
              <td class="subtitle" valign="top">{$user_order.user_city}</td>
            </tr>
            <tr>
              <td class="admin_info" valign="top">{!user_state!}</td>
              <td class="subtitle" valign="top">{$user_order.user_state}</td>
            </tr>
            <tr>
              <td class="admin_info" valign="top">{!user_phone!}</td>
              <td class="subtitle" valign="top">{$user_order.user_phone}</td>
            </tr>
            <tr>
              <td class="admin_info" valign="top">{!user_email!}</td>
              <td class="subtitle" valign="top">{$user_order.user_email}</td>
            </tr>
          </table>
        {else}
       		<table width="99%" border='0'>
            	<tr>
              		<td class="title" colspan='2' valign="top">{!pers_info!}</td>
            	</tr>
            	<tr>
            		<td colspan="2" style="text-align:center;"><strong>No user data</strong></td>
				</tr>
            </table>
        {/if}
        </td>
      </tr>
    {/order->order_list}
    <tr>
      <td colspan="2">
        <table width='100%' bgcolor="lightgrey" border=0>
          <tr>
            <td width='33%' align="left">{gui->button url="view.php" name="{!pos_goback!}"}</td>
            <td width='34%' align="center"> &nbsp;</td>
            <td width='33%' align="right">

              {if $order_search || empty($next_order_id)}
                &nbsp;
      				{elseif $not_status eq "paid"}
             		{gui->button url="view.php?order_id={$next_order_id}" name="{!pos_nextunpaid!}"}
      				{elseif $not_status eq "send"}
      					{gui->button url="view.php?order_id={$next_order_id}" name="{!pos_nextunsent!}"}
      				{else}
      					{gui->button url="view.php?order_id={$next_order_id}" name="{!pos_nextorder!}"}
      				{/if}
            </td>
          </tr>
        </table>
      </td>
    </tr>
    <tr>
      <td colspan="2">
        <table width='100%' cellspacing='1' cellpadding='4' border=0>
          <tr>
            <td class='title' colspan='9'>{!tickets!}<br></td>
          </tr>
          <tr>
            <td class='subtitle'>{!id!}</td>
            <td class='subtitle'>{!event!}</td>
            <td class='subtitle'>{!event_date!}</td>
            <td class='subtitle'>{!category!}</td>
            <td class='subtitle'>{!zone!}</td>
            <td class='subtitle'>{!seat!}</td>
            <td class='subtitle'>{!discount!}</td>
            <td class='subtitle'>{!price!}</td>
            <td class='subtitle'>&nbsp;</td>
          </tr>
          {order->tickets order_id=$order_id}
            {counter assign='row' print=false}
            <input type='hidden' name='place[]' value='{$shop_ticket.seat_id}'/>
            <tr class='admin_list_row_{$row%2}'>
              <td class='admin_info'>{$shop_ticket.seat_id}</td>
              <td class='admin_info'>{$shop_ticket.event_name}</td>
              <td class='admin_info'><b>{$shop_ticket.event_date}</b></td>
              <td class='admin_info'>{$shop_ticket.category_name}</td>
              <td class='admin_info'>{$shop_ticket.pmz_name}</td>
              <td class='admin_info'>
                {if not $shop_ticket.category_numbering or $shop_ticket.category_numbering eq "both"}
                  {$shop_ticket.seat_row_nr}  -  {$shop_ticket.seat_nr}
                {elseif $shop_ticket.category_numbering eq "rows"}
                  {!row!}{$shop_ticket.seat_row_nr}
                {else}
                   ---
                {/if}
              </td>
              <td class='admin_info'>{$shop_ticket.discount_name}</td>
              <td class='admin_info' align='right'>{$shop_ticket.seat_price}</td>
              <td class='admin_info' align='center'>
                <a href='javascript:if(confirm("{!cancel_ticket!} {$shop_ticket.seat_id}?")){literal}{location.href="view.php?action=cancel_ticket&order_id={/literal}{$shop_ticket.seat_order_id}&ticket_id={$shop_ticket.seat_id}{literal}";}{/literal}'>
                  <img border='0' src='{$_SHOP_images}trash.png' />
                </a>
              </td>
            </tr>
          {/order->tickets}
        </table>
        <br />
      </td>
    </tr>
    {* include file="view_notes.tpl" order=$shop_order *}
  </table>
<br />
<!-- Dialog Box for order -->
<div id="current-order" style="display:none;"></div>
<script type="text/javascript">
  $(document).ready(function(){
    reOrder();
  });
</script>