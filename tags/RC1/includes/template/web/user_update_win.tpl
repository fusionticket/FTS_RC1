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
  <div id="error-message-user" title="{!order_error_message!}" class="ui-state-error ui-corner-all" style="padding: 1em; margin-top: .7em; display:none;" >
    <p>
      <span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;"></span>
      <span id='error-text-user'>ffff</span>
    </p>
  </div>
  {if $smarty.post.submit_update}
    {if count($user_errors) eq 0}
   	  <script type="text/javascript">
        window.opener.location.href = window.opener.location.href;
	      window.close();
      </script>
   	{/if}
    {assign var='user_data' value=$smarty.post}
  {else}
    {assign var='user_data' value=$user->asarray()}
 	{/if}
  <div id="update-user-div" style="width:100%;">
    {gui->StartForm action="{$_SHOP_root_secured}checkout.php" method='post' model='user' id="update_user"}
 			<input type='hidden' name='action' value='useredit' />
      <input type='hidden' name='user_id' value='{$user->user_id}' />
      <input type="hidden" name="submit_update" value="yes" />

		  {include file='user_form.tpl'}

      {if $user->is_member}
        {gui->input autocomplete='off'  type='password' name='old_password' size='10'  maxlength='10'}
        {if !$usekasse}
          {gui->input autocomplete='off' type='password' name='password1' size='10' maxlength='10' id="password" caption=!new_password!}
          {gui->input autocomplete='off' type='password' name='password2' size='10'  maxlength='10'}
        {/if}
		  {/if}
    {gui->endform id="update_user"  title="{!continue!}"}

</div>
       	  <script type="text/javascript">
      $('#_reset').live('click', function(e){
   //     e.preventDefault(); alert('hoi');
        jQuery.modal.close();
      });
      </script>