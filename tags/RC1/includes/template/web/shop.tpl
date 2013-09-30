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
 *}{strip}<!-- $Id$ -->

{if  $smarty.post.action eq 'resendpassword'}
   {$user->forgot_password_f($smarty.post.email)}
{/if}

{if $smarty.request.action eq 'login' and $smarty.request.type != 'block'}
	{include file="user_login.tpl"}

{elseif $smarty.request.action eq 'register'}
  {if $smarty.request.register_user}
    {user->register ismember=true data=$smarty.post secure='user_nospam' login=true}
    {assign var='user_data' value=$smarty.post}
    {if $user_errors}
      {include file="user_register.tpl" ManualRegister=true}
    {else}
      {include file="user_activate.tpl"}
    {/if}
  {else}
    {include file="user_register.tpl" ManualRegister=true}
  {/if}

{elseif $smarty.request.action eq 'activate'}
  {include file="user_activate.tpl"}

{elseif $smarty.request.action eq 'resend_activation'}
  {include file="resend_activation.tpl"}
{elseif $smarty.get.action eq "remove"}
  {$cart->remove_item_f($smarty.get.event_id,$smarty.get.cat_id,$smarty.get.item)}
  {include file="cart_view.tpl"}

{elseif $smarty.request.action eq "addtocart"}
  {if $smarty.post.place}
    {assign var='last_item' value=$cart->add_item_f($smarty.post.event_id, $smarty.post.category_id, $smarty.post.place, $smarty.post.discount, 'mode_web')}
  {else}
    {assign var='last_item' value=$cart->add_item_f($smarty.post.event_id, $smarty.post.category_id, $smarty.post.places, $smarty.post.discount, 'mode_web')}
  {/if}
  {if $last_item}
    {redirect url="index.php?action=view_cart&event_id={$smarty.post.event_id}"}
  {else}
    {include file="event_ordering.tpl"}
  {/if}
{elseif $smarty.request.action eq "buy"}
  {include file="event_ordering.tpl"}


{elseif $smarty.request.action eq "view_cart"}
   {include file="cart_view.tpl" event_id={$smarty.request.event_id}}


{elseif !empty($smarty.request.event_group_id)}
  {include file="event_group.tpl"}

{elseif !empty($smarty.request.event_groups)}
  {include file="event_groups.tpl"}

{elseif !empty($smarty.request.event_type)}
  {include file="event_type.tpl"}


{elseif $user->logged && $smarty.request.action eq "person_user"}
  {include file="personal_user.tpl"}
{elseif $user->logged && $smarty.request.action eq "person_user_edit"}
  {assign var='user_data' value=$user->asarray()}
  {if $smarty.post.submit_update}
  	{user->update data=$smarty.post}
	{/if}
  {if $user_errors || !$smarty.post.submit_update}
      {include file="user_update.tpl"}
  {else}
    {redirect url="?action=person_user"}
  {/if}
{elseif $user->logged && $smarty.request.action eq 'person_orders'}
  {if $smarty.get.id}
  	  {include file="personal_order.tpl"}
  {else}
      {include file="personal_orders.tpl"}
  {/if}

{elseif $user->logged && $smarty.request.action eq 'order_res'}
  {order->res_to_order order_id=$smarty.post.order_id handling_id=$smarty.post.handling}
  {redirect url="?action=person_order"}

{elseif !empty($smarty.request.event_id)}
  {include file="event.tpl"  event_id=$smarty.request.event_id}


{else}
  {include file="page_{$page}.tpl"}
{/if}

<!-- End of massive Elseif -->
{if !$nofooter}
  {include file="footer.tpl"}
{/if}
{/strip}