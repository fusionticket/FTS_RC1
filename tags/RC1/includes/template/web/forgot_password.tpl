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
 {if  $smarty.post.action eq 'resendpassword'}
    {$user->forgot_password_f($smarty.post.email)}
{/if}
  <h1>{!forgot_password!}</h1>
  <br />
  <div id="error-message-dialog" class="ui-state-error ui-corner-all" style="padding: 1em; margin-bottom: .7em; display:none; " >
     <p><span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;"></span>
        <span id='error-text-dialog'>ffff</span>
     </p>
  </div>
  <div id="notice-message-dialog" class="ui-state-highlight ui-corner-all" style=" padding: 1em; margin-bottom: .7em; display:none; " >
     <p><span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>
        <span id='notice-text-dialog'>fff</span>
     </p>
  </div>
  {if $smarty.post.submit AND $user->forgot_password_f($smarty.post.email)}
    <center><button onclick="jQuery.modal.close();">{!close!}</button></center>
  {else}
    {gui->StartForm width="100%" id='ft-forgot-password-form' class="login_table" action='forgot_password.php' method='post' name='resendpassword' onsubmit='this.submit.disabled=true;return true;'}
      {gui->hidden name='action' value='resendpassword'}
      <tr>
        <td colspan='2'>{!pwd_note!}<br/><br/></td>
      </tr>
      <tr>
        <td width='100'>{!user_email!}</td>
        <td><input type='text' name='email' size='30'/>{printMsg key='email'}</td>
      </tr>
      {gui->EndForm title=!pwd_send! noreset=true align='center'}
    </table>
    </form>
  {/if}
<script type="text/javascript">
    jQuery('#ft-forgot-password-form').unbind('submit');
    jQuery('#ft-forgot-password-form').validate({
        rules: { email 	: 	{ required : true, email :true }
    		        },
    		errorClass: "form-error",
    		success: "form-valid",
    		errorPlacement: function(error, element) {
    	 		if (element.attr("name") == "check_condition")
    		   		error.insertAfter(element);
    		 	else
    		   		error.insertAfter(element);
    		},
    invalidHandler: function(form, validator) {
       $('#submit').attr("disabled",false);
    },

      submitHandler: function(form) {
        jQuery(form).ajaxSubmit({
          success: function(data){
            jQuery("#showdialog").html(data);
          }
        });
      }
    });
  var msg = '{printMsg key='__Warning__' addspan=false}';
    if(msg) {
      $("#error-text-dialog").html(msg);
      $("#error-message-dialog").show();
      setTimeout(function(){ $("#error-message-dialog").hide(); }, 10000);
    }
    var msg = '{printMsg key='__Notice__' addspan=false}';
    if(msg) {
      $("#notice-text-dialog").html(msg);
      $("#notice-message-dialog").show();
    }
</script>