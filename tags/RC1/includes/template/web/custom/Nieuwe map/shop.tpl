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
 *}{strip}{* include file="header.tpl" *}
{if  $smarty.post.action eq 'resendpassword'}
   {user->forgot_password_f($smarty.post.email)}
{/if}

{if $smarty.request.action eq 'login' and $smarty.request.type != 'block'}
	{include file="user_login.tpl"}

{elseif $smarty.request.action eq 'register'}
   {if $smarty.request.submit_info}
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
  {cart->remove_item_f($smarty.get.event_id,$smarty.get.cat_id,$smarty.get.item)}
  {include file="cart_view.tpl"}

{elseif $smarty.request.action eq "addtocart"}

  {if !$smarty.post.category_id}
    {assign var='mycats' value=$smarty.post.mycart}
    {foreach from=$mycats item=discounts key=catid}
      {foreach from=$discounts item=qty key=discountid}
        {if $qty > 0}
          {assign var='doaction' value=1}
          {cart->maxSeatsAlowed event=$smarty.post.event_id}
          {if $qty <=$seatlimit}
            {assign var='last_item' value=$cart->add_item_f($smarty.post.event_id  ,$catid  ,$qty, $discountid, 'mode_web')}
            {if !$last_item}
              {assign var='carterror' value=$carterror|cat:$cart->error}
            {/if}
          {else}
            {assign var='carterror' value=$carterror|cat:!order_limit_exceeded!}
          {/if}

        {/if}
      {/foreach}
    {/foreach}

    {if !$carterror and $doaction}
      {include file="cart_view.tpl" event_id=$smarty.post.event_id}
    {else}
      {if !$doaction}
        {assign var='carterror' value=$carterror|cat:!no_seats_selected!}
      {/if}
      {include file="event.tpl" event_id=$smarty.post.event_id carterror=$carterror}
    {/if}

  {else}
    {assign var='last_item' value=$cart->add_item_f($smarty.post.event_id, $smarty.post.category_id, $smarty.post.place, 0, 'mode_web')}
    {if $last_item}
      {include file="discount.tpl"}
    {else}
      {include file="category.tpl"}
    {/if}
  {/if}
{elseif $smarty.request.action eq "adddiscount"}
  {cart->set_discounts event_id=$smarty.post.event_id
    category_id=$smarty.post.category_id item_id=$smarty.post.item_id
    discounts=$smarty.post.discount}
  {include file="cart_view.tpl"}

{elseif $smarty.request.action eq "view_cart"}
  {include file="cart_view.tpl"}

{elseif $smarty.request.category_id}
  {if $smarty.request.qty}
    {assign var='last_item' value=$cart->add_item_f($smarty.request.event_id,$smarty.request.category_id,$smarty.request.qty)}
    {if $last_item}
      {include file="discount.tpl"}
    {else}
      {include file="event.tpl" event_id=$smarty.request.event_id}
    {/if}
  {else}
    {include file="category.tpl"}
  {/if}

{elseif $smarty.request.event_id}
  {include file="event.tpl"  event_id=$smarty.request.event_id}

{elseif $smarty.request.event_group_id}
  {include file="event_group.tpl"}

{elseif $smarty.request.event_groups}
  {include file="event_groups.tpl"}

{elseif $smarty.request.event_type}
  {include file="event_type.tpl"}

{elseif $smarty.request.personal_page}
	{if $user->logged}
    {include file="header.tpl"}
    {if $smarty.request.personal_page eq 'details'}
      {assign var='user_data' value=$user->asarray()}
      {if $smarty.request.action eq 'update'}
  		  {if $smarty.post.submit_update}
          {*assign var='user_data' value=$smarty.post*}
    			{user->update data=$smarty.post}
     		{/if}
			  {if $user_errors}
          {include file="user_update.tpl"}
        {else}
		      {include file="personal_page.tpl"}
        {/if}

      {else}
        {include file="user_update.tpl"}

      {/if}

    {elseif $smarty.request.personal_page eq 'orders'}
      {if $smarty.request.action eq 'order_res'}
        {order->res_to_order order_id=$smarty.post.order_id handling_id=$smarty.post.handling}
  		  {if $order_success}
				  {include file="personal_orders.tpl"}
 		  	{else}
		      <div class='error'>Error</div>
 		  	{/if}
 		  {elseif $smarty.request.action eq 'reorder'}
        {include file="personal_reorder.tpl"}
  	  {elseif $smarty.get.id}
    	  {include file="personal_order.tpl"}
      {else}
        {include file="personal_orders.tpl"}
	  	{/if}
    {else}
		  {include file="personal_page.tpl"}
    {/if}
  {else}
    {include file="last_event_list.tpl"}
  {/if}
{else}
  {include file="last_event_list.tpl"}
{/if}

<!-- End of massive Elseif -->
{if !$nofooter}
  {include file="footer.tpl"}
{/if}
{/strip}