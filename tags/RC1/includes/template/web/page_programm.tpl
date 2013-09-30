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
{include file="header.tpl" name=!program!}

<table class="table_trans">
{$start_date=$smarty.now|date_format:"%Y-%m-%d"}

  {event order="event_date,event_time"  main='on' ort='on' start_date=$start_date  event_status='pub'}
    {counter print=false assign=count}
    {if $count is odd}
      <tr style='padding-bottom:30px;'>
    {/if}
    <td  valign="top" align='center' width='50%'>
      <table class="small_table_dark" width="100%">
        {if $shop_event.event_image}
          <tr><td align='left'>
            <a href='index.php?event_id={$shop_event.event_id}'>
              <img src="files/{$shop_event.event_image}" align='middle' style="margin:15px;" width="100">
            </a>
          </td></tr>
        {/if}
        <tr>
          <td valign='top' align='left'>
            <div class='title' align='left' >
              <a  href='index.php?event_id={$shop_event.event_id}'>{$shop_event.event_name}</a>
              {if $shop_event.event_mp3}
                <a href='files/{$shop_event.event_mp3}'>
                  [<img src='{$_SHOP_themeimages}audio-small.png' border='0' valign='bottom'>]</a>
              {/if}
            </div>
            <div  align='left'>{$shop_event.event_short_text}</div><br>
            <div class='date' align='left'>
              {if $shop_event.event_rep eq "main,sub"}
                {$shop_event.event_date|date_format:!shortdate_format!}
                {$shop_event.event_time|date_format:!time_format!}
                {$shop_event.pm_name}
              {elseif $shop_event.event_rep eq "main"}
                {!div_dates!}
              {/if}
            </div>
          </td>
        </tr>
      </table>
      <br>
    </td>
    {if $count is even}
      </tr>
    {/if}
  {/event}
</table>
{include file="footer.tpl"}