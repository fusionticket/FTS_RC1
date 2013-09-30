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
<table width="150" border="0" cellspacing="0" cellpadding="0" class="menu_table">
  <tr>
    <td  align='center' class="menu_title">
	    {!event_groups!}
    </td>
  </tr>
  <tr>
    <td align='center' valign='top' class="menu_content">
      <table width="90%">
        {counter start="0" assign="count"}
        {event_group  group_status='pub'}
          {counter}
          {assign var='num' value=$count%2}
          <tr class="tr_{$num}">
            <td>
              <a  href='index.php?event_group_id={$shop_event_group.event_group_id}'>{$shop_event_group.event_group_name}</a>
            </td>
          </tr>
        {/event_group}
      </table>
    </td>
  </tr>
</table>