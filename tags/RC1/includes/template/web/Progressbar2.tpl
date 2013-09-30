<body>

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
 {literal}
<style type="text/css">
.pagination{
  TEXT-ALIGN: center;
  width:100%;
}
.pagination td{
  padding:0;
}


.done{ {/literal}
  background: url('{$_SHOP_themeimages}bar_center.png') 0 100% repeat-x;
  color: #FFFFFF;
  TEXT-ALIGN: center;
  border-style: none; border-width: medium;
{literal}
}
.current{
{/literal}
	background: url('{$_SHOP_themeimages}bar_center.png') 0 100% repeat-x;
   	font-weight: bold;
   	color: #000000;
   	border-right: 0px solid #5FE3E0;
    border-style: none; border-width: medium;
{literal}
}
.next{
{/literal}
  TEXT-ALIGN: center;
    background: url('{$_SHOP_themeimages}bar_center_wht.png') 0 100% repeat-x;
   	background-color: white;
    border-style: none; border-width: medium
{literal}
}

</style> {/literal}
{*
.pagination tr:hover td{
  background:none !important;
}
.pagination  tr:hover td.current{
  background:none #BdC9D5 !important;
}
*}

{if $name==!shop! or $name==!select_seat! or $name==!discounts!
    or $name==!shopping_cart! or $name==!pers_info! or $name==!shopping_cart_check_out!
    or $name==!order_reg! or $name==!pay_accept! or $name==!pay_refused!}

  <table cellspacing=0 cellpadding=0 class="full pagination" style="border-top:1px solid #FFFFFF;" height="20">
    <tr>
      {if $name==!shop! and $shop_event.event_pm_id}
        <td width='5'><img src='{$_SHOP_themeimages}bar_left.png' height='20'></td>
        <td class='current'> {!prg_order!} </td>
        <td width='5'><img src='{$_SHOP_themeimages}bar_right.png' height='20'></td>
        <td width='5'><img src='{$_SHOP_themeimages}bar_left_wht.png' height='20'></td>
        <td class='next'>{!prg_review!}</td>
        {if !$user->logged}
          <td class='next' style="border-style: none; border-width: medium">
            {!prg_signin!}
          </td>
        {/if}
        <td class='next' style="border-right: 1px solid #5FE3E0">{!prg_payment!}</td>
        <td class="next" >{!prg_complete!}</td>
        <td width='5'><img src='{$_SHOP_themeimages}bar_right_wht.png' height='20'></td>
      {elseif $name==!select_seat!}
        <td width='5'><img src='{$_SHOP_themeimages}bar_left.png' height='20'></td>
        <td class='done' style="border-style: none; border-width: medium">{!prg_order!} </td>
        <td class='current' style="border-style: none; border-width: medium">{!prg_seat!} </td>
        <td width='5'><img src='{$_SHOP_themeimages}bar_right.png' height='20'></td>
        <td width='5'><img src='{$_SHOP_themeimages}bar_left_wht.png' height='20'></td>
        <td class='next' style="border-style: none; border-width: medium">{!prg_review!}</td>
        {if !$user->logged}
          <td class='next' style="border-style: none; border-width: medium">
            {!prg_signin!}
          </td>
        {/if}
        <td class='next' style="border-style: none; border-width: medium">{!prg_payment!}</td>
        <td class="next" style="border-style: none; border-width: medium">{!prg_complete!}</td>
        <td width='5'><img src='{$_SHOP_themeimages}bar_right_wht.png' height='20'></td>

      {elseif $name==!discounts!}
        <td width='5'><img src='{$_SHOP_themeimages}bar_left.png' height='20'></td>
        <td class='done' style="border-style: none; border-width: medium">{!prg_order!} </td>
        <td class='current'>{!prg_discounts!}</td>
        <td width='5'><img src='{$_SHOP_themeimages}bar_right.png' height='20'></td>
        <td width='5'><img src='{$_SHOP_themeimages}bar_left_wht.png' height='20'></td>
        <td class='next' style="border-style: none; border-width: medium">{!prg_review!}</td>
        {if !$user->logged}
          <td class='next' style="border-style: none; border-width: medium">{!prg_signin!}</td>
        {/if}
        <td class='next' style="border-style: none; border-width: medium">{!prg_payment!}</td>
        <td class="next" style="border-style: none; border-width: medium">{!prg_complete!}</td>
        <td width='5'><img src='{$_SHOP_themeimages}bar_right_wht.png' height='20'></td>
      {elseif $name==!shopping_cart!}
      <td width='5'><img src='{$_SHOP_themeimages}bar_left.png' height='20'></td>
        <td class='done' style="border-right: 1px solid #5FE3E0">{!prg_order!} </td>
        <td class='current' >{!prg_review!} </td>
        <td width='5'><img src='{$_SHOP_themeimages}bar_right.png' height='20'></td>
        <td width='5'><img src='{$_SHOP_themeimages}bar_left_wht.png' height='20'></td>

        {if !$user->logged}
          <td class='next' style="border-right: 1px solid #5FE3E0">{!prg_signin!}</td>
        {/if}
        <td class='next' style="border-right: 1px solid #5FE3E0">{!prg_payment!}</td>
        <td class="next">{!prg_complete!}</td>
        <td width='5'><img src='{$_SHOP_themeimages}bar_right_wht.png' height='20'></td>
      {elseif $name==!pers_info!}
      <td width='5'><img src='{$_SHOP_themeimages}bar_left.png' height='20'></td>
        <td class='done' style="border-right: 1px solid #5FE3E0">{!prg_order!} </td>
        <td class='done' style="border-right: 1px solid #5FE3E0">{!prg_review!} </td>
        <td class='current'>{!prg_signin!} </td>
		<td width='5'><img src='{$_SHOP_themeimages}bar_right.png' height='20'></td>
		<td width='5'><img src='{$_SHOP_themeimages}bar_left_wht.png' height='20'></td>

        <td class='next'  style="border-right: 1px solid #5FE3E0">{!prg_payment!}</td>
        <td class="next">{!prg_complete!}</td>
        <td width='5'><img src='{$_SHOP_themeimages}bar_right_wht.png' height='20'></td>
      {elseif $name==!shopping_cart_check_out!}
      <td width='5'><img src='{$_SHOP_themeimages}bar_left.png' height='20'></td>
        <td class='done' style="border-right: 1px solid #5FE3E0">{!prg_order!} </td>
        <td class='done' style="border-right: 1px solid #5FE3E0">{!prg_review!} </td>
        <td class='done' style="border-right: 1px solid #5FE3E0">{!prg_signin!}</td>
        <td class='current' style="border-style: none; border-width: medium">{!prg_payment!} </td>
        <td width='5'><img src='{$_SHOP_themeimages}bar_right.png' height='20'></td>
        <td width='5'><img src='{$_SHOP_themeimages}bar_left_wht.png' height='20'></td>

        <td class="next" style="border-style: none; border-width: medium">{!prg_complete!}</td>
        <td width='5'><img src='{$_SHOP_themeimages}bar_right_wht.png' height='20'></td>
      {elseif $name==!order_reg!}
      <td width='5'><img src='{$_SHOP_themeimages}bar_left.png' height='20'></td>
        <td class='done' style="border-right: 1px solid #5FE3E0">{!prg_order!} </td>
        <td class='done' style="border-right: 1px solid #5FE3E0">{!prg_review!} </td>
        <td class='done' style="border-right: 1px solid #5FE3E0">{!prg_signin!}</td>
        <td class='done' style="border-right: 1px solid #5FE3E0">{!prg_payment!} </td>
        <td class="current">{!prg_complete!}</td>
        <td width='5'><img src='{$_SHOP_themeimages}bar_right.png' height='20'></td>
      {elseif $name==!pay_accept! or $name==!pay_refused!}
        <td class='done' style="border-right: 1px solid #5FE3E0">{!prg_order!} </td>
        <td class='done' style="border-right: 1px solid #5FE3E0">{!prg_review!} </td>
        <td class='done' style="border-right: 1px solid #5FE3E0">{!prg_signin!}</td>
        <td class='done' style="border-right: 1px solid #5FE3E0">{!prg_payment!} </td>
        <td class="current">{!prg_complete!}</td>
        <td width='5'><img src='{$_SHOP_themeimages}bar_right.png' height='20'></td>
      {/if}
    </tr>

  </table>

 {else}
   <table cellspacing=0 cellpadding=0 >
    <tr>
    <td valign="top">
    &nbsp;</td>
    </tr>
    </table>

{/if}