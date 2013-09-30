{*
/**         %%%copyright%%%
 *
 * FusionTicket - Free Ticket Sales Box Office
 * Copyright (C) 2007-2010 Christopher Jenkins. All rights reserved.
 *
 * Original Design:
 * phpMyTicket - ticket reservation system
 * Copyright (C) 2004-2005 Anna Putrino, Stanislav Chachkov. All rights reserved.
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
 *
 * The "GNU General Public License" (GPL) is available at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * Contact info@fusionticket.com if any conditions of this licencing isn't
 * clear to you.
 * Please goto fusionticket.org for more info and help.
 */
 *}
{if $smarty.request.ajax neq "yes"}
<!doctype html>
<html>
	<head>
		<meta http-equiv="Content-Language" content="English" />
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <link rel="shortcut icon" type="images/png" href="{$_SHOP_images}favicon.png" />
		<title>FusionTicket: Box Office / Sale Point </title>
    {minify type='css'  base=''}
    {minify type='css'  base='css' files='formatting.css,jquery.datatables.css,pos.css'}

    {minify type='js' base='scripts/jquery'}
    {minify type='js' base='scripts/jquery' files='jquery-ui-timepicker-addon.js,jquery.dataTables.js,jquery.dataTables.columnFilter.js,jquery.dataTables.KeyTable.min.js,jquery.notify.js'}

    <!--[if lt IE 9]>
    <script src="http://ie7-js.googlecode.com/svn/version/2.1(beta4)/IE9.js"></script>
    <![endif]-->
		<script type="text/javascript">
      var address = '{$_SHOP_root}';
			var lang = new Object();
			lang.required = '{!mandatory!}';        lang.phone_long = '{!phone_long!}'; lang.phone_short = '{!phone_short!}';
			lang.fax_long = '{!fax_long!}';         lang.fax_short = '{!fax_short!}';
			lang.email_valid = '{!email_valid!}';   lang.email_match = '{!email_match!}';
			lang.not_number = '{!not_number!}';     lang.add_tickets = '{!add_tickets!}';
			lang.chart_title = '{!select_seat_pos!}';
      lang.discount_none ='{!discount_none!}';
      lang.select_event_first ='{!select_event_first!}';
		</script>
    {minify type='js' base='pos/scripts' files='pos.jquery.style.js,pos.jquery.ajax.js,pos.jquery.order.functions.js,pos.jquery.order.js,pos.jquery.order.user.js,pos.jq.forms.js,pos.jq.current.js,pos.jq.current.functions.js'}
    <style>
      .ui-state-highlight {
      background: #cdefeb url(../css/flick/images/ui-bg_flat_55_d2e2fe_40x100.png) 50% 50% repeat-x !important ;
      }
    </style>
	</head>

	<body>
      <!-- Message Divs -->
    <div id='messagebar' style='text-align:left; display: block; position: fixed; left:0px; top: 0px; z-index: 1031; width:100%;'> </div>
   <div id=body>
      <!-- End Message Divs -->
		<div id="wrap">
			<div id="header">
				<div class="loading">
					<img src="{$_SHOP_themeimages}LoadingImageSmall.gif" width="16" height="16" alt="Loading data, please wait" />
				</div>
				  <p>
       		<img src='{$_SHOP_images}logo.png'  border='0' style='float:left;'/><br/>	<br><div style='float:right'>{!box_office!}</div>
       		</p><br/>
 				<h4>{$pos->user_firstname} {$pos->user_lastname} - {$pos->user_city}</h4>

			</div>
			<div id="navbar">
				<ul>
					<li><a href="index.php" accesskey="b" tabindex="11">{!pos_booktickets!}</a></li>
					<li><a href="view.php" accesskey="t" tabindex="12">{!pos_currenttickets!}</a></li>
					<li><a href='?action=logout' >{!logout!}</a></li>

				</ul>
			</div>
      <div style="display:none" id='showdialog'>&nbsp;</div>
			<div id="right">
{/if}