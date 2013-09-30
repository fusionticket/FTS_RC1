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
{event event_id=$event_id ort='on' place_map='on' cat_web='on' event_status='pub' limit=1}
  {if $shop_event.event_pm_id}
    {include file="header.tpl" name=!event_details! header=!shop_info! footer=!shop_condition!}
  {else}
    {include file="header.tpl" name=!event_details!  footer=!shop_condition!}
  {/if}
  {$my_event_short_text = $shop_event.event_short_text scope='root'}
  {$my_event_name = $shop_event.event_name scope='root'}
  {$my_event_keywords = $shop_event.event_keywords scope='root'}
  {if $shop_event.event_rep!="main"} {$my_event_name = $my_event_name + " / {$shop_event.event_date|date_format:!date_format!} / {$shop_event.ort_city}"} {/if}

  {include file="event_header.tpl"}
  {include file="event_description.tpl"}

{/event}
{if $shop_event.event_id <= 0}
  {include file="header.tpl" name=!event! header=!shop_no_event!}
{/if}