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
 *}<!-- $Id$ -->{strip}

{if $user->mode() eq 0 && !$user->active}
	{include file="user_activate.tpl"}
{else}
  {include file="header.tpl" name=!shopping_cart_check_out! header=!handling_cont_mess!}
   <!-- checkout_preview.tpl -->
  {if  $user->mode() lte 2 && $user->new_member}
     {include file="user_nm_registered.tpl"}
	{/if}

  {include file="cart_content.tpl" check_out="on"}
  {assign var=total value=$cart->total_price_f()}

	<br />
  {if !$update->is_demo()}
    <form method='post' name='handling' id="ft-order-handling"> {*   onsubmit='this.submit.disabled=true;return true;' *}
    {ShowFormToken name='OrderHandling'}
    <input type='hidden' name='action' value='confirm' />
  {/if}

  <div class="art-content-layout-wrapper layout-item-5">
    <div class="art-content-layout layout-item-6">
      <div class="art-content-layout-row">
        <div class="art-layout-cell layout-item-7" style="width: 50%;">
          {include file="user_address.tpl" title="on"}
        </div>
        <div class="art-layout-cell layout-item-7" style="width: 50%;">
        <table border='0' width='100%' cellpadding="5">
   			  <tr>
            <td colspan='3' class='TblHeader' align='left'>
              <h4 style='margin:0px;'>{!handlings!}</h2>
            </td>
     			</tr>
      		{assign var=min_date value=$cart->min_date_f()}
          {update->view event_date=$min_date}

          {handling www='on' event_date=$min_date total=$total count=$cart->total_seats_f()}   {* checked="checked" *}
  				  <tr class="{cycle name='payments' values='TblHigher,TblLower'}">
              <td class='payment_form'>
                <input  type='radio' id='{$shop_handling.handling_id}_check' class='checkbox_dark' name='handling_id' value='{$shop_handling.handling_id}' />
  		  		  </td>
            	<td class='payment_form'>
  	  			    <label for='{$shop_handling.handling_id}_check'>
                  {!payment!}: {eval var=$shop_handling.handling_text_payment}<br/>
              		{!shipment!}: {eval var=$shop_handling.handling_text_shipment}
     		  			</label>
          		</td>
              <td class='payment_form' align='right'>
                {if  $shop_handling.fee}
                  + {gui->valuta value=$shop_handling.fee|string_format:"%.2f"}
                {/if}&nbsp;
              </td>
        		</tr>

   	      {/handling}

        </table>
      </div>
    </div>
  </div>
</div>

{if $order->can_freeTicketCode()}
  <div class="art-content-layout-wrapper layout-item-5">
    <div class="art-content-layout layout-item-6">
      <div class="art-content-layout-row">
        <div class="art-layout-cell layout-item-7 gui_form" style="width: 100%;">
           {gui->input caption=!freeTicketCode! type='text' name='FreeTicketCode'}
        </div>
      </div>
    </div>
  </div>
{/if}

  <div class="art-content-layout-br layout-item-0"></div>
  <div class="art-content-layout layout-item-1">
    <div class="art-content-layout-row" style='padding:10px;'>
      <div class="art-layout-cell layout-item-3"  style='text-align:right; width: 100%;padding:10px;'>
     		{if $update->is_demo()}
         	<div class='error'>For safety issues we have disabled the order button. </div>
      	{elseif $update_view.currentres}
       	  <div class='error'>{!limit!}</div>
       	{else}
       	  {gui->button url='submit' id='checkout-commit' name='submit' value="{!order_it!}"}
       		{if $update_view.can_reserve}
       		      </form>
   		  		    <span style="display:inline-block;">&nbsp;{!orclick!}&nbsp;</span>
                <form  style="display:inline-block;" action='' method='post' name='handling'>
            	    <input type='hidden' name='action' value='reserve' />
              		{ShowFormToken name='ReservHandling'}
              	  {gui->button url='submit' id='checkout-reserve' name='submit_reserve' value="{!reserve!}"}
  				{/if}
        {/if}


   	  </div>
    </div>
  </div>
	{if !$update->is_demo()}
	  </form>
  {/if}
  <br/>
  <script language="javascript" type="text/javascript">
    jQuery(document).ready(function(){
    jQuery("#ft-order-handling").submit(function(){
      var handlingRadio = $("input:radio[name='handling_id']:checked").val();
      if(handlingRadio === undefined){
        showErrorMsg("{!select_handling_option!}");
        return false;
      }
      jQuery('#checkout-commit').
        attr('disabled','true').
        addClass('Buttonloading').
        text("{!please_wait_button!}");
      showNoticeMsg("{!please_wait_message_order!}");


      return true;
    });
  });
  </script>
{/if}
{include file="footer.tpl"}{/strip}