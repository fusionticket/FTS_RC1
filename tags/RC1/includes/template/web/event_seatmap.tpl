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
{assign var="category_id" value=$smarty.request.category_id}

{category category_id=$category_id event='on' placemap='on'}

    {include file="header.tpl" name=!select_seat_title! header="{!select_seat!}<br>{!click_on_reserve!}"}
    {event event_id=$event_id ort='on' place_map='on' event_status='pub' limit=1}
      {include file="event_header.tpl"}
    {/event}

    {cart->maxSeatsAlowed event=$shop_category}

    <form name='f' action='index.php' method='post'>
      {ShowFormToken name='categorys'}
      <input type='hidden' name='category_id' value='{$shop_category.category_id}'>
      <input type='hidden' name='event_id' value='{$shop_category.category_event_id}'>
      <input type='hidden' name='action' value='addtocart'>
        <div class="art-content-layout-br layout-item-0"></div>
           {!order_limit!} {$seatlimit}
        {if $shop_category.category_numbering eq 'none'}
        <div class="art-content-layout-br layout-item-0"></div>
        <div class="art-content-layout">
          <div class="art-content-layout-row">
            <div class="art-layout-cell layout-item-4" style="width: 100%;">
            <table border="0" cellspacing="0" cellpadding="5" style="width: 100%;">
              <tr>
                <td class='event_data'>
                  {!number_seats!}
                </td>
                <td class='title'>
                  <select style="float:none;"  name='place' >
                    {section name="myLoop" start=1 loop=$seatlimit+1}
                      <option value='{$smarty.section.myLoop.index}' > {$smarty.section.myLoop.index} </option>
                    {/section}
                  </select>
                  <input type='hidden' name='numbering' value='none' />
                </td>
              </tr>
            </table>
          </div>
          </div>
          </div>
        {else}
          {if $shop_category.category_numbering eq "rows"}
                <b>{!only_rows_numbered!}</b>
          {/if}
        <div class="art-content-layout-br layout-item-0"></div>
        <div class="art-content-layout">
          <div class="art-content-layout-row">
            <div class="art-layout-cell layout-item-4" style="width: 100%;">
<style type="text/css">
  .seatmapimage {
     width: 16px;
     height:16px;
  }
</style>

          <div style='overflow: auto; height: 350px; width:100%; border: 1px solid #DDDDDD;background-color: #fcfcfc' align='center' valign='middle'>
            {placemap  category=$shop_category seatlimit=$seatlimit}
          </div>
          <center><div valign='top'> {!placemap_image_explanation!}<div></center>
          </div>
          </div>
          </div>
        {/if}
      <br />
      <div style='text-align:right'>
         <input type='submit' name='submit' value='{!reserve!}' />
  	  </div>
    </form>

{/category}