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
  {if $shop_event.event_text}
    <div class="art-content-layout-br layout-item-0"></div>
    <b>{!event_description!}</b><br>
    <div class="art-content-layout">
      <div class="art-content-layout-row">
        <div class="art-layout-cell layout-item-4" style="width: 100%;">
           {$shop_event.event_text}
        </div>
      </div>
    </div>
{/if}
{if $shop_event.event_rep eq 'main'}
    <div class="art-content-layout-br layout-item-0"></div>
    <b>{!dates_localities!}</b><br>
    <div class="art-content-layout">
      <div class="art-content-layout-row">
        <div class="art-layout-cell layout-item-4" style="width: 100%;"><ul>
          {$start_date=$smarty.now|date_format:"%Y-%m-%d"}
          {event event_main_id=$shop_event.event_id ort='on' stats='on' sub='on' event_status='pub' place_map='on'  start_date=$start_date order="event_date,event_time"}
            <li>
              <a href="index.php?event_id={$shop_event.event_id}">
                {$shop_event.event_date|date_format:!date_format!}
              </a>
	            {$shop_event.event_time|date_format:!time_format!} {$shop_event.pm_name}
            </li>
          {/event}
          </ul>
          {if !$shop_event.event_main_id}
          	  <div class="art-content-layout-br layout-item-0"></div>
              <div class="art-content-layout layout-item-1">
                <div class="art-content-layout-row" style='padding:10px;'>
                  <p><center>{!no_sub_events!}</center></p>
               </div>
              </div>
          {/if}
        </div>
      </div>
    </div>

{else}
	{if !$shop_event.event_pm_id}
	  <div class="art-content-layout-br layout-item-0"></div>
    <div class="art-content-layout layout-item-1">
      <div class="art-content-layout-row" style='padding:10px;'>
        <p><center>{!no_placemap_available!}</center></p>
      </div>
    </div>
    <br/>
	{elseif $shop_event.category_web >= 1}
		{include file="event_prices.tpl"}
  {else}
	  <div class="art-content-layout-br layout-item-0"></div>
    <div class="art-content-layout layout-item-1">
      <div class="art-content-layout-row" style='padding:10px;'>
    <p><center>{!no_categories_available!}</center></p>
     </div>
    </div>
    <br/>
  {/if}
{/if}