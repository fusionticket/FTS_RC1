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
 *}<!-- $Id$ -->{if $smarty.get.inframe == 'yes'}
 <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
	<head>
		<title>FusionTicket</title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<!-- link rel="stylesheet" type="text/css" href="css/formatting.css" media="screen"  -->

		<link rel='stylesheet' href='style.php' type='text/css' />

		<!-- Must be included in all templates -->
		{include file="required_header.tpl"}
		<!-- End Required Headers -->
	</head>
	<body class='main_side'>
{else}
  {include file="header.tpl" name=!calendar!}
{/if}
{assign var='length' value='15'}
{assign var=start_date value=$smarty.now|date_format:"%Y-%m-%d"}

<table class='table_dark'>
   {event start_date=$start_date sub='on' ort='on' place_map='on' order="event_date,event_time" first=$smarty.get.offset length=$length}
    {assign var='month' value=$shop_event.event_date|date_format:"%B"}
    {if $month neq  $month1}
     <tr><td colspan='4' class='title' style='text-decoration:underline;'><br>{$shop_event.event_date|date_format:"%B %Y"}</td></tr>
     {assign var='month1' value=$month}
    {/if}
    <tr class='tr_{cycle values="0,1"}'>
      <td><a  target='_parent' href='index.php?event_id={$shop_event.event_id}'>
            {if $shop_event.event_pm_id}<img src='{$_SHOP_themeimages}ticket.gif' border="0">
            {else}<img src='{$_SHOP_themeimages}info.gif' border="0">{/if}
          </a>
      </td>
      <td ><a target='_parent' href='index.php?event_id={$shop_event.event_id}'>{$shop_event.event_name}</a></td>
      <td>{$shop_event.event_date|date_format:!date_format!} - {$shop_event.event_time|date_format:!time_format!}</td>
      <td >{$shop_event.ort_name} {$shop_event.ort_city} {$shop_event.pm_name}</td>
      <td width="20">{if $shop_event.event_mp3}<a href='files/{$shop_event.event_mp3}'><img src='{$_SHOP_themeimages}audio-small.png' border='0' /></a>{/if}</td>
    </tr>
  {/event}
</table>
{gui->navigation offset=$smarty.get.offset count=$shop_event.tot_count length=$length}
{if $smarty.get.inframe == 'yes'}
  	<div class="footer">
  		<hr width="100%" />
  		<!-- To comply with our GPL please keep the following link in the footer of your site -->
      <!-- Failure to abide by these rules may result in the loss of all support and/or site status. -->
      Copyright 2010<br />
      Powered By <a  target='_blank' href="http://www.fusionticket.org"> Fusion Ticket</a> - Free Open Source Online Box Office
  	</div>
  </body>
  </html>
{else}
  {include file='footer.tpl'}
{/if}