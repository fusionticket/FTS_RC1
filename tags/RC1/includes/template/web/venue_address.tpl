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
 *}<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<!-- $Id$ -->
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" >
<meta http-equiv="Content-Language" content="nl" >

<link REL='stylesheet' HREF='style.php' TYPE='text/css' >

</head>
<body>
    {event event_id=$smarty.get.event_id ort='on' limit=1}
     <table border="0" cellpadding="0" cellspacing="0" width="600">
      <tr>
        <td valign=top>
    	    {if $shop_event.ort_image}
    		    <img src="files/{$shop_event.ort_image}" width='160' align='left' border="0">
          {else}
    		    <img src="images/na.png" align='left' style="margin:20px"  border="0">
          {/if}
          <br>
        </td>
        <td valign='top' align='left' width='400' >
          <h3>{$shop_event.ort_name} </h3><br>
          <table border=0 cellSpacing=2 cellPadding=3 width='90%' bgcolor='white'>
          {gui->setData data=$shop_event}
          {gui->label name='ort_address'}
            {gui->view name='ort_address' nolabel=true}
            {if $shop_event.ort_address1}
              <br>{gui->view name='ort_address1' nolabel=true}
            {/if}
            {/gui->label}
            {gui->view name='ort_city'}
            {gui->view name='ort_zip'}
            {gui->view name='ort_state' option=true}
            {if $shop_event.ort_country and $shop_event.ort_country neq $organizer->organizer_country}
              {gui->view name='ort_country' option=true}
            {/if}
            {gui->view name='ort_phone' option=true}
            {gui->view name='ort_fax' option=true}
            {gui->viewUrl name='ort_url' option=true}
          </table>
        </td>
      </tr>
      {if $shop_event.ort_pm}
        <tr>
          <td colspan=2 >
            <hr>
            <center>
             {$shop_event.ort_pm}
             </center>
            <hr>
          </td>
        </tr>
     {/if}
    </table>
  {/event}
  <div align='right'>
  <button onclick="jQuery.modal.close();">Close</button>
  </div>

</body>