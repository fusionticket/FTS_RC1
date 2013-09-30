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
<table width='100%'  cellspacing='2' style='border-top:#45436d 1px solid;border-bottom:#45436d 1px solid;'>
  <tr>
  <td class='admin_list_buttons'><img src='{$_SHOP_images}dot.gif' class='admin_order_res' width='15' height='15' /> {!order_status_reserved!}</td>
  <td class='admin_list_buttons'><img src='{$_SHOP_images}dot.gif' class='admin_order_ord' width='15' height='15' /> {!order_status_ordered!}</td>
  <td class='admin_list_buttons'><img src='{$_SHOP_images}dot.gif' class='admin_order_send' width='15' height='15' /> {!order_status_sent!}</td>
  <td class='admin_list_buttons'><img src='{$_SHOP_images}dot.gif' class='admin_order_paid' width='15' height='15' /> {!order_status_paid!}</td>
  <td class='admin_list_buttons'><img src='{$_SHOP_images}dot.gif' class='admin_order_cancel' width='15' height='15' /> {!order_status_cancelled!}</td>
  </tr>
  <tr><td class='admin_list_buttons' ><img src='{$_SHOP_images}view.png' border='0'>
  {!order_details!}
</td><td class='admin_list_buttons'><img src='{$_SHOP_images}printer.png' border='0'>
  {!print_order!}
</td><td class='admin_list_buttons' colspan=2><img src='{$_SHOP_images}trash.png' border='0'>
  {!cancel_and_delete!}
</td></tr>
  </table>