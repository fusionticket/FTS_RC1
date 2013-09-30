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
 *}
{if !$smarty.request.ajax and !$no_header}
  {strip}
    {include file='order.tpl' nofooter=true}
  {/strip}
{/if}

  <div id="checkout_result" class="gui_form" style='text-align:left; width:500px' title='{if $pm_return.approved}{!pay_accept!}{else}{!pay_refused!}{/if}'>
  <fieldset>
    {if $pm_return.approved}
      <p style='padding:4px'><b>{!pay_reg!}</b></P>
    {/if}
    {gui->view name=order_id value=$order_id}
    {if $shop_handling.handling_id eq 1}
      <p style='padding:4px'><b>{!reserve_tickets!}</b></P>
    {else}
      {eval var=$shop_handling.handling_text_payment assign=test}
      {gui->view name=payment value=$test}
      {eval var=$shop_handling.handling_text_shipment  assign=test}
      {gui->view name=shipment value=$test}
    {/if}
    {gui->valuta value=$order_total_price assign=test}
    {gui->view name=total_price value=$test}

    {if $pm_return.transaction_id}
      {gui->view name=trx_id value="<b>{$pm_return.transaction_id}</b>"}
    {/if}
    {if $pm_return.response}
        <p {if !$pm_return.approved}class='error'{/if}>
          {eval var=$pm_return.response}
        </p>
    {/if}

    {if $pm_return.approved}<br />
      <div class='gui_footer' style='display: table; table-layout: fixed; width:100%'>
        <div style='display: table-row;padding:4px'>
        <div style='display: table-cell; width:50%'>
          {gui->button url="checkout.php?action=print&{$order->EncodeSecureCode($order_id)}&mode=2" target='invoicepdf' name='printinvoice'}
          </div>
          {if $shop_order.handling->handling_shipment eq "sp" or $pos}
           <div style='display: table-cell;width:50%;text-align:right'>
            <span id="waiting">
              <p style="float:left; display:inline; margin:0;">{!pos_waitingmessage!}</p>
              <img style="float:right; display:inline;" src="{$_SHOP_themeimages}LoadingImageSmall.gif" width="16" height="16" alt="{!pos_waitingmessage!}"/>
            </span>
            {gui->button url="checkout.php?action=print&{$order->EncodeSecureCode($order_id)}&mode=1" target='ticketpdf' style='display:none;' name='printtickets'}
            </div>
    {/if}
  </div>
        </div>
          {/if}
</fieldset>  </div>
  <script type="text/javascript">
  var orderid = {$shop_order.order_id};
  {if !$smarty.request.ajax && !$no_header && !$no_footer}
      jQuery(document).ready(function(){
        jQuery("#checkout_result").dialog({
          bgiframe: false,
          autoOpen: true,
          height: 'auto',
          width: 'auto',
          modal: true,
          position:['middle',50],
          close: function(event, ui) {
            if (timerid) {
              clearTimeout(timerid);
              timerid = -1;
            }
            window.location = '{$_SHOP_root}index.php';
          }
        });
      });
  {/if}
  {if $shop_order.handling->handling_shipment eq "sp" or $pos}
      //The refresh orderpage, the ajax manager SHOULD ALLWAYS be used where possible.
      var checkpaint = function(){
        if (timerid > -1) {
          ajaxQManager.add({
            type:      "POST",
            url:      "ajax.php?x=canprint",
            dataType:   "json",
            data:      { "pos":true, "action":"Canprint", 'orderid':orderid },
            success:function(data, status){
              if(data.status){
                if (data.show){
                  jQuery('#printtickets').show();
                }
                jQuery('#waiting').hide();
              } else {
                timerid = setTimeout('checkpaint()', 1000);
              }
            }
          });
        }
      }
      checkpaint();
  {/if}
  </script>
{if !$smarty.request.ajax and !$no_footer}
    {include file="footer.tpl"}
{/if}