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

{assign var='order_search' value=$smarty.post.order_search}
{assign var='length' value='15'}

{assign var='dates' value="from=`$smarty.request.from`&to=`$smarty.request.to`"}
{assign  var='firstpos' value="first=`$smarty.get.first`"}

{if $smarty.request.from}
    {assign var='from' value="$smarty.request.from 00:00:00.000000"}
{/if}

{if $smarty.request.to}
    {assign var='to' value="`$smarty.request.to` 23:59:59.999999"}
{/if}
      {if $order_search}
        {include file='process_orderselect.tpl'}
      {else}
        {include file='process_dateselect.tpl'}
      {/if}

      <table width='100%' id='order_overview' class="scroll display" >
      <thead>
      <tr>
          <th> </th>
          <th>ID</th>
          <th>{!order_place_header!}</th>
          <th>{!Customer!}</th>
          <th>{!total_price!}</th>
          <th>{!tickets!}</th>
          <th>{!timestamp!}</th>
          <th> </th>
        </tr>
        </thead>
        {assign var='orderby' value='order_id desc'}
        {if $place =='pos'}
        	{$ownerid=$pos->user_id}
        {else}
          {$ownerid=null}
        {/if}
        <tbody>
        {order->order_list not_hand_payment=$not_hand_payment hand_shipment=$hand_shipment place=$place status=$status not_status=$not_status not_sent=$not_sent start_date=$from end_date=$to order=$orderby owner_id=$ownerid order_search=$order_search}
          {counter print=false assign=count}
          {if $count lt ($length+1)}
             <tr>
            {if $shop_order.order_shipment_status eq "send"}
              {$class= "admin_order_{$shop_order.order_shipment_status}"}
            {elseif $shop_order.order_payment_status eq "paid"}
              {$class= "admin_order_{$shop_order.order_payment_status}"}
            {else}
              {$class= "admin_order_{$shop_order.order_status}"}
            {/if}
              <td class='admin_list_buttons '><img src='{$_SHOP_images}dot.gif' class='{$class}' width='15' height='15' /> </td>
              <td class='admin_list_item' align="right">{$shop_order.order_id}</td>
              <td class='admin_list_item'>{$shop_order.order_place}</td>
              <td class='admin_list_item'>{$user_order.user_firstname} {$user_order.user_lastname}</td>
              <td class='admin_list_item' align="right">{valuta value=$shop_order.order_total_price}</td>
              <td class='admin_list_item' align="right">{$shop_order.order_tickets_nr}</td>
              <td class='admin_list_item'>{$shop_order.order_date}</td>
              <td class='admin_list_buttons' style='padding:1; margin:0px;white-space: nowrap;' align="right">
                <a title="{!view_details!}"  href='view.php?order_id={$shop_order.order_id}'>
                  <img src='{$_SHOP_images}view.png' border='0'>
                </a>
                {include file='process_actions.tpl' shop_order=$shop_order}
              </td>
            </tr>
          {/if}
        {/order->order_list}
      </table>
      </tbody>
      {*gui->navigation offset=$smarty.get.offset count=$shop_orders_count length=$length*}

    {include file='process_menu.tpl'}


      <script>
 tblUser = $('#order_overview').dataTable({
    //   bProcessing: true,
    sScrollY: '300px',
    bJQueryUI: true,
    sDom: '<l<rt>p>',
    bSort: false,
    bAutoWidth: true,
    oLanguage: {
      sEmptyTable: 'No data available in table',
      sLoadingRecords : 'Loading...',
      sZeroRecords:  'No matching records found.'
    },
    bPaginate: false,
    bSortClasses: false,

    bScrollCollapse: false,
    aoColumns : [ { 'sWidth':'5px', },
                  { 'sWidth':'50px', },
                  { 'sWidth':'120px',  },
                  {  },
                  { 'sWidth':'100px',  },
                  { 'sWidth':'50px',  },
                  { 'sWidth':'140px', },
                  { 'sWidth':'1px',  }]
  });

      </script>
