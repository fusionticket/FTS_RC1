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
{literal}
<script  type="text/javascript">
jQuery().ready(function(){
  jQuery("#ft-checkout-edit-user").live('click',function(e){
    e.preventDefault();
    ajaxQManager.add({
//      dataType: 'HTML',
      url:  '?action=useredit',
      success: function(data, status){
        if (jQuery.isPlainObject( data)) {
          if(json.status == false){
            showErrorMsg(data.msg);
            return;
          }
        }else{
          jQuery("#showdialog").html(data).modal({
            minWidth : 480,
            minHeight : 500,
            onShow : contact.show
          });
        }
      }
    });
  });
  var contact = {
    'show': function (dialog) {
      $('label.required').append('&nbsp;<strong>*&nbsp;</strong>');
      updateUserRules = {'submitHandler' : function(form){
         jQuery("#update_user").ajaxSubmit({
          dataType: "json",
          type:'POST',
          success: function(data, status){
            if(data.saved){
              ajaxQManager.add({
                dataType: 'HTML',
                url:  '?action=useraddress',
                success: function(html, status){
                  jQuery("#ft-user-details").html(jQuery(html).filter("#ft-user-details"));
                }
              });
              jQuery.modal.close();
              showNoticeMsg(data.msg);
            }else{
              jQuery("#error-text-user").html(data.msg);
              jQuery("#error-message-user").show();
              setTimeout(function(){jQuery("#error-message-user").hide();}, 10000);
            }
            if(data.status == false){
              jQuery.modal.close();
              showErrorMsg(data.msg);
            }
          }
        });
        return false;
       }}
      //We use the default updateValidation rules but we add our custom submit handler to it, clever eh?
      jQuery("#update_user").validate(updateUserRules);
    }
  };
});
</script>
{/literal}
<div id="ft-user-details">
<table border='0' cellpadding="3" width='100%'>
  <tr>
    {if $title eq "on"}
      <td class='TblHeader'>
        <h4 style='margin:0px;'>{!your_addr!}</h4>
      </td>
    {/if}
  </tr>
  <tr>
    <td class='TblHigher' nowrap="" >
      {$user->user_firstname|clean} {$user->user_lastname|clean}
    </td>
  </tr>
  <tr>
    <td class='TblHigher' nowrap="" >
     {$user->user_address|clean}
    </td>
  </tr>

  {if $user->user_address1|clean}
  <tr>
    <td class='TblHigher' nowrap="" >
      {$user->user_address1|clean}
    </td>
  </tr>
  {/if}

  <tr>
    <td class='TblHigher' nowrap="" >
     {$user->user_zip|clean} {$user->user_city|clean}
    </td>
  </tr>
  <tr>
    <td class='TblHigher' nowrap="" >
      {gui->viewcountry value=$user->user_country|clean nolabel=true}
    </td>
  </tr>
  <tr>
    <td class='TblHigher' nowrap="" >
     {$user->user_email}
    </td>
  </tr>
  <tr>
    <td class='TblHigher' >
      <div align='right'><a id="ft-checkout-edit-user" target='editaddress' href='#'>{!edit!}</a></div>
    </td>
  </tr>

</table>
</div>