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
 *}<!-- $Id: user.tpl 1921 2012-12-18 08:11:53Z nielsNL $ -->
 <style>
  #toolbar {
	padding: 2px;
	display: inline-block;
	margin-bottom:2px;
	}
/* support: IE7 */
*+html #toolbar {
	display: inline;
	}
</style>
<script type="text/javascript">
    $(document).ready(function(){
      loadUser();
      $('form#pos-user-form').keypress(function(e){
        if(e.which == 13){
          if ($('#user_info_new').attr("checked") == "true") $('#pos-user-form').submit();
          else $('#search_user').click();
        }
      });
     $( "#repeat" ).buttonset();
    });
</script>
<div id="toolbar" class="title" style='width:99%;'>
  <span id="repeat">
              <input checked="checked" type='radio' id='user_info_none' class='checkbox_dark' name='user_info' value='0'>
              <label for='user_info_none'> {!none!} </label>
               <input checked="checked" type='radio' id='user_info_new' class='checkbox_dark' name='user_info' value='2'>
               <label for='user_info_new'> {!new_partron!} </label>
              <input type='radio' id='user_info_search' class='checkbox_dark' name='user_info' value='1'>
              <label for='user_info_search'> {!exist_user!} </label>
  </span>
  {gui->button url="button" id="search_user" name='search_action' value='search' style='float:right;'}
  </div>
    <div id='user_data' class='gui_form' style="display:none;padding:0px;" >
      {gui->setdata data=$user_data errors=$user_errors nameclass='user_item' valueclass='user_value' model='user' namewidth='120'}
      {gui->input name='user_firstname' size='30' maxlength='50'}
      {gui->input name='user_lastname'  size='30' maxlength='50'}
      {gui->input name='user_address'  size='30' maxlength='75'}
      {gui->input name='user_address1' size='30' maxlength='75'}
      {gui->input name='user_zip'  size='8' maxlength='20'}
      {gui->input name='user_city'  size='30' maxlength='50'}
      {gui->input name='user_state' size='30' maxlength="50"}
      {gui->selectcountry name='user_country'  DefaultEmpty=true style='width:180px;' all=true}
      {gui->input name='user_phone' size='30' maxlength='50'}
      {gui->input name='user_fax' size='30' maxlength='50'}
      {gui->input name='user_email' size='30' maxlength='50'}
      {gui->hidden id='user_id' name='user_id' value='0' }
</div>

      <script>

      </script>

  <div id="search-dialog" title="{!personal_search_dialog!}">
     <table id="users_table" class="scroll" cellpadding="0" cellspacing="0">
     <thead>
       <tr>
         <th>{!user_name!}</th>
         <th>{!user_phone!}</th>
         <th>{!user_city!}</th>
         <th>{!user_email!}</th>
       </tr>
     </thead>
     <tbody></tbody>
     </table>
  </div>