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
class plugin_Custom_OrderDescription extends baseplugin {

	public $plugin_info		  = 'Custom Order Description';
	/**
	 * description - A full description of your plugin.
	 */
	public $plugin_description	= 'This plugin allows you to change the order description used in the payment handler';
	/**
	 * version - Your plugin's version string. Required value.
	 */
	public $plugin_myversion		= '0.0.2';
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
    $config['orderdiscription_plugin'] = array(
      'orderdiscription_text'=>array('en=Order payment for order# :','area','#@',array('rows'=>5,'cols'=>92)),
      'orderdiscription_showid'=>array('1','yesno','*')
    );

  }

  function updateOrderDescription( $discription, $order) {
    global $_SHOP;
    $text = is($_SHOP->orderdiscription_text[$_SHOP->lang],$discription);
    if ($_SHOP->orderdiscription_showid) {
      $text .= $order->order_id;
    }
    return $text;
  }

}

?>