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
{if !$update->can_reserve()}
  {$tabview=[1=>"{!pos_unpaidlist!}",2=>"{!pos_unsentlist!}",3=>"{!pos_yourtickets!}",4=>"{!pos_alltickets!}"]}
{else}
  {$tabview=[0=>"{!pos_reservedlist!}",1=>"{!pos_unpaidlist!}",2=>"{!pos_unsentlist!}",3=>"{!pos_yourtickets!}",4=>"{!pos_alltickets!}"]}
{/if}
{gui->Tabbar menu=$tabview}

{if $TabBarid == 0} {* eq "reserved" *}
  {if $smarty.request.order_id}
    {include file="process_view.tpl" status="res"}
  {else}
    {include file="process_list.tpl" status="res"}
  {/if}

{elseif $TabBarid == 1} {*  eq "unpaid" *}
  {if $smarty.request.order_id}
    {include file="process_view.tpl" status="ord" not_status="paid" place='' not_hand_payment='entrance'}
  {else}
    {include file="process_list.tpl" status="ord" not_status="paid" place='' not_hand_payment='entrance'}
  {/if}

{elseif $TabBarid == 2} {*  eq "unsent" *}
  {if $smarty.request.order_id}
    {include file="process_view.tpl" not_status="send" status="paid" hand_shipment='post,sp'}
  {else}
    {include file="process_list.tpl" not_status="send" status="paid" hand_shipment='post,sp'}
  {/if}

{elseif $TabBarid == 3} {*  eq "pos owned orders" *}
  {if $smarty.request.order_id}
    {include file="process_view.tpl" place='pos'}
  {else}
    {include file="process_list.tpl" place='pos'}
  {/if}

{elseif $TabBarid == 4} {*  eq "all paid orders" *}
  {if $smarty.request.order_id}
    {include file="process_view.tpl" status="paid,send" orderby="order_date DESC" cur_order_dir="DESC"}
  {else}
    {include file="process_list.tpl" status="paid,send" orderby="order_date DESC"}
  {/if}

{elseif $TabBarid == 5} {*  eq "search other orders" *}
  {if $smarty.request.order_id}
    {include file="process_view.tpl" status="" orderby="order_date DESC" cur_order_dir="DESC" order_search='on'}
  {else}
    {include file="process_list.tpl" status="" orderby="order_date DESC" order_search='on'}
  {/if}

{/if}
{include file="footer.tpl"}