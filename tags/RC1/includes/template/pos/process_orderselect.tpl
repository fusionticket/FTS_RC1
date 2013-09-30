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
         <tr>
          <td colspan='5' align='center'>
            <form action='view.php' method='post'>
              <table border='0' width='100%' style='border-top:#45436d 1px solid;border-bottom:#45436d 1px solid;' >
                <tr>
                  <td class='admin_info' width='12%'>{!search_orderid!}</td>
                  <td class='note'  width='35%' align='left'>
                    <input type='text' name='seach_order_id' value='{$smarty.request.seach_order_id|clean}' size='10'  />
                  </td>
                  <td class='admin_info' width='12%' >{!search_patron!}</td>
                  <td class='note' width='35%' align='left'>
                    <input type='text' name='search_patron' value='{$smarty.request.search_patron|clean}' size='27'  />
                  </td>
                  <td class='admin_info'>&nbsp;</td>
                </tr>
                <tr>
                  <td class='admin_info' width='12%'>{!search_phone!}</td>
                  <td class='note' width='35%' align='left'>
                    <input type='text' name='search_phone' value='{$smarty.request.search_phone|clean}' size='27'   />
                  </td>
                  <td class='admin_info' width='12%'>{!search_email!}</td>
                  <td class='note' width='35%' align='left'>
                    <input type='text' name='search_email' value='{$smarty.request.search_email|clean}' size='27' />
                  </td>
                  <td class='admin_info'>&nbsp;</td>
                </tr>
                <tr>
                  <td class='admin_info' width='12%'>{!date_from!}</td>
                  <td class='note'  width='35%' align='left'>
                    {gui->inputdate name='from' nolabel=true value='{$smarty.get.from|clean}'}
                  </td>
                  <td class='admin_info' width='12%'>{!date_to!}</td>
                  <td class='note'  width='35%' align='left'>
                    {gui->inputdate name='to' nolabel=true value='{$smarty.get.to|clean}'}
                  </td>

                  <td class='admin_info'>
                  	<input type='hidden' name='order_search' value='1'/>
                    <input type='submit' name='search_submit' value='{!submit!}' />
                  </td>
                </tr>
              </table>
            </form>
          </td>
        </tr>
