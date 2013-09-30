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
 *}{include file="header.tpl"}<!-- $Id$ -->
<style>
  .dataTables_filter {
	width: 100%;
	}
</style>

<form id="order-form" name='addtickets' action='ajax.php?x=addtocart' method='post'>
  <input type='hidden'  id="event-id" name="event_id" value=0 />
  <div class="art-content-layout">
    <div class="art-content-layout-row">
      <div class="art-layout-cell" style="width: 40%; padding-right:4px;" >
        <div class='title' align='left'>{!select_event!}</div>
        <div class='user_item' style='text-align:right; width:100%' >  <label for='event-search'>{!filter!}:&nbsp;</label><input style='margin:2px' type="text" id="event-search"  /></div>
        <table id="event_table" >
          <thead style='display:none'>
            <tr style='display:none'>
              <td></td>
            </tr>
          </thead>
          <tbody>
          </tbody>
        </table>
      </div>
      <div class="art-layout-cell" style="width: 60%;padding-left:4px;" >
        <div class='title' align='left'>{!select_category!}</div>
        <table width="100%" cellpadding='2' cellspacing='2' bgcolor='white'>
          <tbody>
            <tr style='height:255px'>
              <td colspan=2 class='user_value' style='overflow: hidden; position: relative;'>
                <div id='cat-select' style='overflow-y: auto;height:230px;width:100%; overload:'>
                  {!select_event_first!}
                </div>
              </td>
            </tr>
            <tr {* style="display:none;" *}>
            <td class='user_item'  style='width:100px;' >{!discounts!}:</td>
            <td class='user_value'>
              <select name='discount_id' id='discount-select' style="width:100%;">
                <option value='0'>{!discount_none!}</option>
              </select>
            </td>
            </tr>
            <tr>
              <td class='user_item' style='width:100px; height:26px;' >{!select_seats!}:</td>
              <td class='user_value' class="seat-selection" >
                <div id="show-seats-div" style="display:none;float:left;">
                  {gui->button url="button" name='show_seats'}
                </div>
                <div id="seat-qty-div" style="display:none;float:left;">
                  <input type='number' id="seat-qty" name='place' min='1' max='99' style='width:50px;' />
                  {gui->button url="submit" id="continue" name='add_tickets' type=1}
                </div>
                <div style='float:right;'>
                  {gui->button url="button" id="clear-button" name='clear_selection'}
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    </div>
</form>
  <div class="art-content-layout clearfix">
    <div class="art-content-layout-row">
      <div class="art-layout-cell" style="width: 100%;padding-top:8px;" >

<table id="cart_table"  class="scroll display" cellpadding="0" cellspacing="2" style='font-family: "Verdana"; font-size: 12px;'>
  <thead>
    <tr>
      <th>{!pos_event_name_title!}</th>
      <th>{!pos_tickets_title!}</th>
      <th>{!pos_total_title!}</th>
      <th>&nbsp;</th>
    </tr>
  </thead>
  <tbody></tbody>
</table>
</div>
</div>
    </div>

<div id="cart-pager"></div>
<div id="seat-chart" title="{!select_seat_pos!}"></div>
<div id="order_action" title="{!pos_order_page!}"></div>

  <div class="art-content-layout clearfix">
   <div class="art-content-layout-row">
      <div class="art-layout-cell " style="width: 50%; padding-right:4px;" >
        <div class='title' align='left'>{!pers_info!}</div>
	      <form id="pos-user-form" name="pos-user">
	        {include file='order_user.tpl'}
	      </form>
      </div>
      <div class="art-layout-cell" style="width: 50%; padding-leftt:4px;" >
        <div class='title' align='left'>{!handlings!}</div>
      <form>
        <table id='handling-table' width='100%' cellspacing='2' cellpadding='2'  bgcolor='white' style='margin:0px;'>
          <tbody id='handling-block'>
            {include file='handlings.tpl'}
          </tbody>
        </table>
      </form>
    </div>
    </div>
    </div>
  <div class="art-content-layout">
    <div class="art-content-layout-row">
      <div class="art-layout-cell" style="width: 100%; padding-top:8px;" >
     <div style="text-align:right;">
      <form id="ft-pos-order-form">
        {gui->button url='button' id='checkout' name='order_action' value='order_it' style="float:none;"}
        &nbsp;
        {gui->button url='button' id='cancel'   name='order_cancel_action' value='cancel' style="float:none;"}
      </form>
    </div>
  </div>
  </div>
  </div>
<script type="text/javascript">
  {literal}
    $(document).ready(function(){
      loadOrder();
    });
  {/literal}
</script>
{include file="footer.tpl"}
