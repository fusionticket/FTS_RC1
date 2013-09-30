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
 *}
{if $shop_event.event_rep eq 'main'}
  <table class="table_dark" cellpadding="5" border=0>
    <tr>
    	{if $shop_event.event_image}
        <td width="30%" valign="top" colspan="2">
    	  	<!-- a href='index.php?event_id={$shop_event.event_id}' -->
            <img src="files/{$shop_event.event_image}" align='left' class="magnify" border="0" style="margin:3px;" alt="{$shop_event.event_name} in {$shop_event.ort_city}" title="{$shop_event.event_name} in {$shop_event.ort_city}" border="0" width="100">
          <!-- /a -->
    	  </td>
    	{/if}
      <td colspan='4' valign='top' align=left'>
        <a  class="title_link" href='index.php?event_id={$shop_event.event_id}'>
          {if $shop_event.event_pm_id}
            <img border='0' src='{$_SHOP_themeimages}ticket.gif'>
          {else}
            <img border='0' src='{$_SHOP_themeimages}info.gif' />
          {/if}
          &nbsp;{$shop_event.event_name}
        </a>
        {if $shop_event.event_mp3}
          <a  href='files/{$shop_event.event_mp3}'>
            <img src='{$_SHOP_themeimages}audio-small.png' border='0' valign='bottom'>
          </a>
        {/if}<br>
        <span class="date">{$shop_event.event_date|date_format:!shortdate_format!}
          {$shop_event.event_time|date_format:!time_format!}
          {$shop_event.pm_name}
          {if $info_plus}
            {!doors_open!} {$shop_event.event_open|date_format:!time_format!}
          {/if}
        </span><br>
        {$shop_event.event_text}
      </td>
    </tr>
    <tr>
      <td colspan='6'>
        {if $info_plus eq "on"}
          {!dates_localities!}:
          {event event_main_id=$shop_event.event_id ort='on' stats='on' sub='on' event_status='pub' place_map='on'}
            <li>
              <a href="index.php?event_id={$shop_event.event_id}">
                {$shop_event.event_date|date_format:!date_format!}
              </a>
	            {$shop_event.event_time|date_format:!time_format!} {$shop_event.pm_name}
            </li>
          {/event}
          {if !$shop_event.event_main_id}
            <p>{!no_sub_events!}</p>
          {/if}
        {else}
          {!various_dates!}
        {/if}
      </td>
    </tr>
  </table>
{else}
  <table class="table_dark">
    <tr>
      {if $shop_event.event_image}
        <td width="30%" valign="top" >
          <!-- a href='index.php?event_id={$shop_event.event_id}'-->
            <img src="files/{$shop_event.event_image}" align='left' class="magnify" border="0" width="100"  style="margin:15px;" border="0">
          <!-- /a -->
        </td>
      {/if}
      <td valign='top' align=left'>
        <a  class="title_link" href='index.php?event_id={$shop_event.event_id}'>
          {if $shop_event.event_pm_id}
            <img border='0' src='{$_SHOP_themeimages}ticket.gif' align="middle">
          {else}
            <img border='0' src='{$_SHOP_themeimages}info.gif' align="middle">
          {/if}
          &nbsp;{$shop_event.event_name}
        </a>
        {if $shop_event.event_mp3}
          <a  href='files/{$shop_event.event_mp3}'>
            [<img src='{$_SHOP_themeimages}audio-small.png' border='0' valign='bottom'>]
          </a>
        {/if}<br>
        {if $info_plus eq "on"}
          <span class="date">
            {!date!}:
            {$shop_event.event_date|date_format:!date_format!} -
            {$shop_event.event_time|date_format:!time_format!} <br>
            {!venue!}:
            <a onclick='showDialog(this);return false;' href='address.php?event_id={$shop_event.event_id}'>{$shop_event.ort_name}</a> - {$shop_event.ort_city} - {$shop_event.pm_name}  <br>
            {!doors_open!} {$shop_event.event_open|date_format:!time_format!}
          </span>
        {else}
          <span class="date">{$shop_event.event_date|date_format:!shortdate_format!}
            {$shop_event.event_time|date_format:!time_format!} {$shop_event.ort_name}
          </span>
        {/if}
        <br><br>
        {$shop_event.event_text}
      </td>
    </tr>
  </table>
{/if}