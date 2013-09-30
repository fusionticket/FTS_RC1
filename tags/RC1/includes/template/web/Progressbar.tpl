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
<style type="text/css">
.pagination{
  background-color: #99d9ea;
  TEXT-ALIGN: center;
  width:100%;
}
.pagination td{
  padding:0;
}


.done{
  background-color: #42729a;
  color: #FFFFFF;
  TEXT-ALIGN: center;
  border-left: 2px solid #5EA3DB;
}
.current{
  background-repeat: no-repeat;
  background-color: #BdC9D5;
   font-weight: bold;
   color: #000000;
}
.next{
  TEXT-ALIGN: center;
  border-right: 2px solid #9FE1F2;
}

</style>
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
  <table cellspacing=0 cellpadding=0 class="full pagination">
    <tr>
      {if $name==!shop! and $shop_event.event_pm_id}
        <td class='current'> {!prg_order!} </td>
        <td width='25'><img src='{$_SHOP_themeimages}trans_12_11_r.png' height='20'></td>
        <td class='next'>{!prg_review!}</td>
        {if !$user->logged}
          <td class='next'>
            {!prg_signin!}
          </td>
        {/if}
        <td class='next'>{!prg_payment!}</td>
        <td class="next">{!prg_complete!}</td>
      {elseif $name==!select_seat!}
        <td class='done'>{!prg_order!} </td>
        <td width='11'><img src='{$_SHOP_themeimages}trans_12_11_b.png' width='11' height='20'></td>
        <td class='current'>{!prg_seat!} </td>
        <td width='25'><img src='{$_SHOP_themeimages}trans_12_11_r.png' height='20'></td>
        <td class='next'>{!prg_review!}</td>
        {if !$user->logged}
          <td class='next'>
            {!prg_signin!}
          </td>
        {/if}
        <td class='next'>{!prg_payment!}</td>
        <td class="next">{!prg_complete!}</td>
      {elseif $name==!discounts!}
        <td class='done'>{!prg_order!} </td>
        <td width='11'><img src='{$_SHOP_themeimages}trans_12_11_b.png' width='11' height='20'></td>
        <td class='current'>{!prg_discounts!}</td>
        <td width='25'><img src='{$_SHOP_themeimages}trans_12_11_r.png' height='20'></td>
        <td class='next'>{!prg_review!}</td>
        {if !$user->logged}
          <td class='next'>
            {!prg_signin!}
          </td>
        {/if}
        <td class='next'>{!prg_payment!}</td>
        <td class="next">{!prg_complete!}</td>
      {elseif $name==!shopping_cart!}
        <td class='done'>{!prg_order!} </td>
        <td width='11'><img src='{$_SHOP_themeimages}trans_12_11_b.png' width='11' height='20'></td>
        <td class='current'>{!prg_review!} </td>
        <td width='25'><img src='{$_SHOP_themeimages}trans_12_11_r.png' height='20'></td>
        {if !$user->logged}
          <td class='next'>
            {!prg_signin!}
          </td>
        {/if}
        <td class='next'>{!prg_payment!}</td>
        <td class="next">{!prg_complete!}</td>
      {elseif $name==!pers_info!}
        <td class='done'>{!prg_order!} </td>
        <td class='done'>{!prg_review!} </td>
        <td width='11'><img src='{$_SHOP_themeimages}trans_12_11_b.png' width='11' height='20'></td>
        <td class='current'>{!prg_signin!} </td>
        <td width='25'><img src='{$_SHOP_themeimages}trans_12_11_r.png' height='20'></td>
        <td class='next'>{!prg_payment!}</td>
        <td class="next">{!prg_complete!}</td>
      {elseif $name==!shopping_cart_check_out!}
        <td class='done'>{!prg_order!} </td>
        <td class='done'>{!prg_review!} </td>
        <td class='done'>{!prg_signin!}</td>
        <td width='11'><img src='{$_SHOP_themeimages}trans_12_11_b.png' width='11' height='20'></td>
        <td class='current'>{!prg_payment!} </td>
        <td width='25'><img src='{$_SHOP_themeimages}trans_12_11_r.png' height='20'></td>
        <td class="next">{!prg_complete!}</td>
      {elseif $name==!order_reg!}
        <td class='done'>{!prg_order!} </td>
        <td class='done'>{!prg_review!} </td>
        <td class='done'>{!prg_signin!}</td>
        <td class='done'>{!prg_payment!} </td>
        <td width='11'><img src='{$_SHOP_themeimages}trans_12_11_b.png' width='11' height='20'></td>
        <td class="current">{!prg_complete!}</td>
        <td width='25' ><img src='{$_SHOP_themeimages}trans_12_11_r.png' height='20'></td>
      {elseif $name==!pay_accept! or $name==!pay_refused!}
        <td class='done'>{!prg_order!} </td>
        <td class='done'>{!prg_review!} </td>
        <td class='done'>{!prg_signin!}</td>
        <td class='done'>{!prg_payment!} </td>
        <td width='11'><img src='{$_SHOP_themeimages}trans_12_11_b.png' height='20'></td>
        <td class="current">{!prg_complete!}</td>
      {/if}
    </tr>
  </table>
{/if}