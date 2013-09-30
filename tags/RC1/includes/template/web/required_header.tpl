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
 *}<!-- $Id$ -->  <!-- Required Header .tpl Start -->
  <link rel="icon" href="favicon.ico" type="image/x-icon" />
  {strip}
  <meta name="description" content="{$organizer_name|clean}{if !empty($my_event_short_text)} - {$my_event_short_text|clean} {/if}" />

  <title>{$organizer_name|clean}{if !empty($my_event_name)} - {$my_event_name|clean}{/if}</title>
   {if !empty($my_event_keywords)}
    <META NAME="keywords" CONTENT="{$my_event_keywords|clean}">
   {/if}

 {/strip}
  {minify type='css'}

  {minify type='js' base='scripts/jquery'} {* Shows the default list *}
  {minify type='js' base='scripts/jquery' files='jquery.countdown.pack.js,jquery.imagemapster.js,jquery.metadata.min.js,jquery.notify.js'}

  <!--Start Image Mapping-->
<style type="text/css">
		.Buttonloading {
			background : url('images/theme/default/grid-loading.gif') no-repeat  1px 1px !important;
			padding-left: 20px !important;
		}
</style>
  <!--End Image Mapping-->

  <script type="text/javascript">
  	var lang = new Object();
  	lang.required = '{!mandatory!}';        lang.phone_long = '{!phone_long!}'; lang.phone_short = '{!phone_short!}';
  	lang.fax_long = '{!fax_long!}';         lang.fax_short = '{!fax_short!}';
  	lang.email_valid = '{!email_valid!}';   lang.email_match = '{!email_match!}';
  	lang.pass_short = '{!pass_too_short!}'; lang.pass_match = '{!pass_match!}';
  	lang.not_number = '{!not_number!}';     lang.condition ='{!check_condition!}';

    jQuery(document).ready(function(){
        $("*[class*='has-tooltip']").tooltip({
          delay:0,
          showBody: "~",
          showURL:false,
          track: true,
          opacity: 1,
          fixPNG: true,
          fade: 250
        });
      });

    var showDialog = function(element){
      jQuery.get(jQuery(element).attr('href'),
        function(data){
          jQuery("#showdialog").html(data);
          jQuery("#showdialog").modal({
            autoResize:true,
            maxHeight:500,
            maxWidth:800
          });
        }
      );
      return false;
    }

    function BasicPopup(a) {
      showDialog(a);
      return false;
    }

       var printMessages = function(messages){
  if(messages === undefined){
    return;
  }
  if (messages.warning) {
    showErrorMsg(messages.warning);
  }
  if (messages.notice) {
    showNoticeMsg(messages.notice);
  }
}
  </script>
  <!-- Required Header .tpl  end -->