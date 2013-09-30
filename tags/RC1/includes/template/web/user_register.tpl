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
 <!-- user_register.tpl -->

   {if !$ManualRegister}
      <div class="art-content-layout-br layout-item-0"></div>
      <div class="art-content-layout layout-item-1">
        <div class="art-content-layout-row">
          <div class="art-layout-cell layout-item-3">
            <b>
            {if $user->mode() <= '1'}
              {!becomemember!}
            {elseif $user->mode() eq '2'}
              {!becomememberorguest!}
            {else}
              {!becomeguest!}
            {/if}
            </b><br>
            {!guest_info!}
          </div>
        </div>
      </div>
      <div class="art-content-layout-br layout-item-0"></div>
    {/if}

{if $ManualRegister}
  {include file="header.tpl" name=!becomemember! header=!memberinfo!}
  {gui->StartForm action="{$_SHOP_root_secured}index.php" method='post' model='user' id="userregister"}
{else}
  {include file="header.tpl" name=!pers_info! header=!user_notice!}
  {gui->StartForm action="{$_SHOP_root_secured}checkout.php" method='post' model='user' id="userregister"}
{/if}
    <input type='hidden' name='action' value='register' />
    <input type='hidden' name='register_user' value='on' />
      {if $user->mode() <= '1' or $ManualRegister}
        <input type='hidden' name='ismember' id='type' value='true'/>
      {elseif $user->mode() eq '2'}
        {gui->checkbox name='ismember' id='type' value= $smarty.post.ismember caption=!becomemember!}
      {/if}
      {include file="user_form.tpl"}
      {gui->input autocomplete='off' type='password' name='password1' size='15' id="password1" required=true validate=['minlength' => 6]}
      {gui->input autocomplete='off' type='password' name='password2' size='15' required=true validate=['minlength' => 6, 'equalTo'=> '#password1']}

           {* !pwd_min! *}
     {gui->captcha name='user_nospam' id='user_nospam' size='10' maxlength="10" required=true}
    {eval var='{!user_condition!}' assign='result'}
    {gui->label nolabel=true}
          <a onclick='showDialog(this);return false;'  href='agb.php' style="float:left; display:block;">{eval var=!agrement!}</a><span style="float:left;">&nbsp;*</span>
          <input type='checkbox' class='checkbox' name='check_condition' value='check' required=true />{printMsg key='check_condition'}
    {/gui->label}
    </fieldset>
    {* gui->endform id="userregister"  title="{!continue!}" *}
      <div class="art-content-layout-br layout-item-0"></div>
  <div class="art-content-layout layout-item-1">
    <div class="art-content-layout-row" style='padding:10px;'>
      <div class="art-layout-cell layout-item-3"  style='text-align:right; width: 100%;padding:10px;'>
        {gui->button url="submit"  name="continue" type=1}
      </div>
    </div>
  </div>
</form>
<br />
<script  type="text/javascript">



  /**
   *
   * @access public
   * @return void
   **/
  function showPasswords(show){
    if(show == true){
      $('#password1-tr').show();
      $('#password2-tr').show();
        $("input[name='password1']").rules("add",{ required : true, minlength : 6 });
        $("input[name='password2']").rules("add",{ required : true , minlength : 6, equalTo: "#password1" });
    }else{
      $('#password1-tr').hide();
      $('#password2-tr').hide();
        $("input[name='password1']").rules("remove");
        $("input[name='password2']").rules("remove");
    }
  }

$(window).load(function(){
  $('#ismember').click(function(){
    if($(this).is(':checked')){
      showPasswords(true);
    }else{
      showPasswords(false);
    }
  });
  {if $user->mode() <= '1' OR $ManualRegister}
    showPasswords(true);
  {elseif $user->mode() eq '2'}
    showPasswords($('#ismember-checkbox').is(':checked'));
  {else}
    showPasswords(false);
  {/if}
});
</script>

{if !$ManualRegister}
  {include file='footer.tpl'}
{/if}{/strip}