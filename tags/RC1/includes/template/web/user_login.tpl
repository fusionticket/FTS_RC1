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
{if $smarty.get.action eq 'login'}
	{user->login username=$smarty.post.username password=$smarty.post.password uri=$smarty.post.uri}
{elseif $smarty.get.action eq 'logout'}
	{user->logout}
{/if}
{if !$user->logged}
  {include file="header.tpl" name=!login! header=!memberinfo!}<br>

  <form method='post' action='index.php' style='margin-top:0px;'>
    <input type="hidden" name="action" value="login">
    {ShowFormToken name='login'}


    {if $smarty.get.action neq "logout" and $smarty.get.action neq "login"}
      <input type="hidden" name="uri" value="{$smarty.server.REQUEST_URI}">
    {/if}
    <center>
      <table border="0" cellpadding="3" class="login_table" bgcolor='white' width='80%'>
      	<tr>
      		<td width='30%' class="TblLower">{!email!}</td>
      		<td class="TblHigher" ><input type='input' name='username' size='20' /> {printMsg key='loginusername'}</td>
      	</tr>
      	<tr>
      		<td  class="TblLower">{!password!}</td>
      		<td class="TblHigher" ><input type='password' name='password' size='20' /> {printMsg key='loginpassword'}
      		<input type='submit' value='{!login_button!}' style='font-size:10px;'/>
      		</td>
      	</tr>
      	<tr>
      		<td colspan='2' class="TblLower">
      			<li><a  href='index.php?action=register'>{!register!}</a></li>
      		</td>
      	</tr>
      	<tr>
      		<td colspan='2' class="TblLower">
      			<li><a onclick='showDialog(this);return false;' href='forgot_password.php'>{!forgot_pwd!}</a></li>
      		</td>
      	</tr>
      	      	<tr>
      		<td colspan='2' class="TblLower">
      			<li><a onclick='showDialog(this);return false;' href='index.php?action=resend_activation'>{!act_notarr!}</a></li>
      		</td>
      	</tr>

      </table>
    </center>
  </form>
{/if}