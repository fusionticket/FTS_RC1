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
  {gui->input type='text' name='user_firstname' size='30' maxlength='50' value=$user_data.user_firstname required=true}
  {gui->input type='text' name='user_lastname' size='30'  maxlength='50' value=$user_data.user_lastname}
  {gui->input type='text' name='user_address' size='30'  maxlength='75' value=$user_data.user_address}
  {gui->input type='text' name='user_address1' size='30'  maxlength='75' value=$user_data.user_address1}
  {gui->input type='text' name='user_zip' size='8'  maxlength='20' value=$user_data.user_zip}
  {gui->input type='text' name='user_city' size='30'  maxlength='50' value=$user_data.user_city}
  {gui->input type='text' name='user_state' size='30' maxlength="50" value=$user_data.user_state}
  {gui->selectcountry name='user_country' value=$user_data.user_country}
  {gui->input type='text' name='user_phone' size='30'  maxlength='50' value=$user_data.user_phone}
  {gui->input type='text' name='user_fax' size='30'  maxlength='50' value=$user_data.user_fax}
  {gui->input readonly=$user_data.user_id type='text' name='user_email' size='30' maxlength='50' value=$user_data.user_email required=true validate=['email'=> true]}
  {if !$user_data.user_id}
    {gui->input autocomplete='off' type='text' name='user_email2' size='30'  maxlength='50' value=$user_data.user_email2 required=true validate=['email'=> true, 'equalTo'=> "#user_email"]}
  {/if}