<?php
  /**
   *               %%%copyright%%%
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
   */

/**
 *
 *
 * @version $Id$
 * @copyright 2010
 */
class plugin_custom_barcode extends baseplugin {

	public $plugin_info		  = 'Custom Barcode';
	/**
	 * description - A full description of your plugin.
	 */
	public $plugin_description	= 'This plugin can be used to create a different Barcode';
	/**
	 * version - Your plugin's version string. Required value.
	 */
	public $plugin_myversion		= '0.0.5';
	/**
	 * requires - An array of key/value pairs of basename/version plugin dependencies.
	 * Prefixing a version with '<' will allow your plugin to specify a maximum version (non-inclusive) for a dependency.
	 */
	public $plugin_requires	= null;
	/**
	 * author - Your name, or an array of names.
	 */
	public $plugin_author		= 'The FusionTicket team';
	/**
	 * contact - An email address where you can be contacted.
	 */
	public $plugin_email		= 'info@fusionticket.com';
	/**
	 * url - A web address for your plugin.
	 */
	public $plugin_url			= 'http://www.fusionticket.org';

  public $plugin_actions  = array ('install','uninstall');
  function config(& $config){

    $config['barcode_plugin'] = array(
      'barcode_plugin_size'=>array(16,'number','*'),
      'Barcode_plugin_format'=>array(10,'select','*',array('10'=>'barcode_plugin_decimal','16'=>'barcode_plugin_hexidacimal','26'=>'barcode_plugin_alphaonly','36'=>'barcode_plugin_alphanumeric'))
      );
  }

  function updateOrderencodebarcode($barcode, $order, $ticket) {
    global $_SHOP;
    return str_pad(base_convert($barcode,10,$_SHOP->Barcode_plugin_format), $_SHOP->barcode_plugin_size, "0", STR_PAD_LEFT);
  }

  function updateOrderdecodebarcode($barcode) {
  	global $_SHOP;
  	return (sscanf(str_pad(base_convert($barcode,$_SHOP->Barcode_plugin_format,10), 16, "0", STR_PAD_LEFT),"%08d%08d"));
  }

}

?>