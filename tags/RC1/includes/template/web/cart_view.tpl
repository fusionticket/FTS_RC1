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
{eval var=!cart_cont_mess! assign='cart_cont_mess'}
{include file="header.tpl" name=!shopping_cart! header=$cart_cont_mess}
{include file="cart_content.tpl"}
<br>
  <div class="art-content-layout-br layout-item-0"></div>
  <div class="art-content-layout layout-item-1">
    <div class="art-content-layout-row" style='padding:10px;'>
      <div class="art-layout-cell layout-item-3"  style='width: 50%;padding:10px;'>
      <form method='post' action="index.php">
        {ShowFormToken name='moretickets'}
        {if $event_id}
           <input type='hidden' name='event_id' value='{$event_id}' />
           <input type='hidden' name='action' value='buy' />
        {/if}
        {gui->button name="go_home" value="{!order_more_tickets!}" url="submit"}
      </form>
      </div>
      <div class="art-layout-cell layout-item-3"  style='width: 50%;padding:10px;'>
      {if $cart->can_checkout_f()}
        <form action="{$_SHOP_root_secured}checkout.php" method='post'  style='text-align:right;'>
          {ShowFormToken name='checkout'}
          {gui->button name="go_pay" value="{!checkout!}" url="submit"}
        </form>
      {/if}
  	  </div>
    </div>
  </div>
  <br/>