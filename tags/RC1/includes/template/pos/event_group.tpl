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
 {event_group  group_status='pub' group_id=$smarty.get.group_id}
  <table width="100%" border="0" cellspacing="0" cellpadding="3">
    <tr><td class='title'>
       {$shop_event_group.event_group_name}
    </td></tr>
  </table><br>
  <table cellpadding='5' cellspacing='0' width='100%'>
    {event event_group=$shop_event_group.event_group_id	start_date=$smarty.now|date_format:"%Y-%m-%d" ort='on' sub='on' cats='on' order="event_date,event_time"}
      {if $shop_event.event_id neq $old_id}
        <tr >
          <td class='festival' ><a class='cal_link' href='index.php?event_id={$shop_event.event_id}'>{$shop_event.event_name}</a></td>
          <td class='festival' >{$shop_event.event_date|date_format:"%e %B"} - {$shop_event.event_time|date_format:" %Hh%M"}</td>
          <td class='festival' >{$shop_event.ort_name}</td>
          <td class='festival' >{$shop_event.ort_city}</td>
          <td class='festival' >
            {if $shop_event.event_free gt 0}
              {if $shop_event.event_free/$shop_event.event_total ge 0.2}
                <img src='{$_SHOP_themeimages}green.png'> {$shop_event.event_free}/{$shop_event.event_total}
              {else}
                <img src='{$_SHOP_themeimages}orange.png'> {$shop_event.event_free}/{$shop_event.event_total}
              {/if}
            {else}
              <img src='{$_SHOP_themeimages}red.png'> {!event_sold!}
            {/if}
          </td>
          <td class='festival' >
          {if $shop_event.event_mp3}<a href='{$shop_event.event_mp3}'>
            <img src='{$_SHOP_themeimages}audio-small.png' border='0'></a>
          {else}
            &nbsp;
          {/if}
          </td>
        </tr>
        {assign var='old_id' value=$shop_event.event_id}
      {/if}
      <tr>
      <td class='calendar'>&nbsp;</a></td>
      <td class='calendar'>
      {if $shop_event.category_free gt 0}
        <a href='index.php?category_id={$shop_event.category_id}&category_numbering={$shop_event.category_numbering}'>
          {$shop_event.category_name}
        </a>
      {else}
        {$shop_event.category_name}
      {/if}
      </td>
      <td class='calendar'>{$shop_event.category_price}</td>
      <td class='calendar'>
        {if $shop_event.category_free gt 0}
          {if $shop_event.category_free/$shop_event.category_size ge 0.2}
            <img src='{$_SHOP_themeimages}green.png'> {$shop_event.category_free}/{$shop_event.category_size}
          {else}
            <img src='{$_SHOP_themeimages}orange.png'> {$shop_event.category_free}/{$shop_event.category_size}
          {/if}
        {else}
          <img src='{$_SHOP_themeimages}red.png'> {!category_sold!}
        {/if}
      </td>
      <td colspan='2'>&nbsp;</td>
      </tr>
    {/event}
  </table>
{/event_group}