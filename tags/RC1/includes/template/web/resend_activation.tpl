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
{if $smarty.post.email and $user->resend_activation_f($smarty.post.email)}
  {include file="user_activate.tpl"}
{else}
  {include file="header.tpl" name=!resend_activation!}
  <form action='index.php' method='post' class="yform full">
    {ShowFormToken name='ResendActivation'}
    <input type='hidden' name='action' value='resend_activation' />

    <table class="full" width='80%' align='center'>
      <tr>
        <td class='title' colspan='2' align='center'>
          {!act_notarr!}
        </td>
      </tr>
      <tr>
        <td colspan='2'>
          {!act_note!}<br />
        </td>
      </tr>
      <tr>
        <td>
          {!email!}
        </td>
        <td>
          <input type='text' name='email' size='36' /> &nbsp; <input type='hidden' name='submit' value="{!act_send!}" />
          <button type='submit' class="ft-ui-button">{!act_send!}</button>
        </td>
      </tr>
    </table>
  </form>
{/if}