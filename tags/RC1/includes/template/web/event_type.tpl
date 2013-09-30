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
<table class="table_trans">
  <tr>
    <td  valign="top" align='center' class='title' >
      {assign var=type value=$smarty.get.event_type}
      {if $type eq "classics"}
          {!et_classics!}
      {elseif $type eq "jazz,blues,funk"}
          {!et_jbf!}
      {elseif $type eq "pop,rock"}
          {!et_pr!}
      {elseif $type eq "folklore"}
          {!et_folk!}
      {elseif $type eq "theater"}
          {!et_theater!}
      {elseif $type eq "humour"}
          {!et_humour!}
      {elseif $type eq "sacred"}
          {!et_church!}
      {elseif $type eq "opera,ballet"}
          {!et_opbal!}
      {elseif $type eq "music"}
          {!et_music!}
      {elseif $type eq "other"}
          {!et_other!}
      {elseif $type eq "cinema"}
          {!et_cinema!}
      {elseif $type eq "exposition"}
          {!et_expo!}
      {elseif $type eq "party"}
          {!et_party!}
          {/if}
    </td>
  </tr>
</table>
<br><br>
{assign var='start_date' value=$smarty.now|date_format:"%Y-%m-%d"}
{assign var="empty" value="1"}
{event order="event_date,event_time"  start_date=$start_date main='on' ort='on' event_type=$smarty.get.event_type first=$smarty.get.first length=$length}
  {include file="event_description.tpl"}<br><br>
  {assign var="empty" value="0"}
{/event}<br>
{if $empty eq 0}
  {include file="navigation.tpl" first=$smarty.get.first  tot_count=$shop_event.tot_count part_count=$shop_event.part_count length=$length}
{/if}