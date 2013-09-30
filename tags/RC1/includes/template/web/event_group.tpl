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
{assign var='length' value='10'}
{event_group group_id=$smarty.get.event_group_id group_status='pub'}
  <table class="table_midtone">
    <tr>
      <td>
        {if  $shop_event.event_group_image}
          <img src="files/{$shop_event_group.event_group_image}" align='left' style="margin:15px" />
        {/if}
        <div class='title'>{$shop_event_group.event_group_name}</div>
        <div>{$shop_event_group.event_group_description}</div>
      </td>
    </tr>
  </table>
{/event_group}
<br>
{assign var="empty" value="1"}
{event order="event_date,event_time" ort='on' main='on' event_group=$smarty.get.event_group_id first=$smarty.get.first length=$length}
  {include file="event_description.tpl"}<br><br>
  {assign var="empty" value="0"}
{/event}
{if $empty eq 0}
  {include file="navigation.tpl" first=$smarty.get.first  tot_count=$shop_event.tot_count part_count=$shop_event.part_count length=$length}
{/if}