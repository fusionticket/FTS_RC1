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

                {if $shop_order.order_status neq "cancel" and $shop_order.order_payment_status eq "paid"}
                  <a  title="{!print_invoice!}" target='_blank' href='checkout.php?action=print&{$order->EncodeSecureCode($shop_order.order_id)}&mode=2'>
                    <img border='0' src='{$_SHOP_images}printer_invoice.png' />
                  </a>
                  <a  title="{!print_tickets!}" target='_blank' href='checkout.php?action=print&{$order->EncodeSecureCode($shop_order.order_id)}&mode=1'>
                    <img border='0' src='{$_SHOP_images}printer_ticket.png' />
                  </a>
                  <a title="{!print_both!}"  target='_blank' href='checkout.php?action=print&{$order->EncodeSecureCode($shop_order.order_id)}&mode=3'>
                    <img border='0' src='{$_SHOP_images}printer.png'>
                  </a>
                  {* if $shop_order.payment_status eq 'none' *}
                  <a title="{!cancel_order!}" href='javascript:if(confirm("{!cancel_order!} {$shop_order.order_id}?")){literal}{location.href="view.php?action=cancel_order&place={/literal}{$shop_order.order_place}{literal}&order_id={/literal}{$shop_order.order_id}&{$dates}&{$firstpos}{literal}";}{/literal}'>
                    <img border='0' src='{$_SHOP_images}trash.png'>
                  </a>
                  {*/if*}
                {/if}