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
 * @version $Id: plugin.custom_orderdescription.php 1785 2012-05-12 07:10:13Z nielsNL $
 * @copyright 2010
 */
class plugin_smarty_languageselector extends baseplugin {

	public $plugin_info		  = 'Language selector plugin';
	/**
	 * description - A full description of your plugin.
	 */
	public $plugin_description	= 'This plugin shows a Language selector on the webshop.';
	/**
	 * version - Your plugin's version string. Required value.
	 */
	public $plugin_myversion		= '0.0.1';
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

  public $plugin_actions  = array ('install','uninstall','smarty');

  function smartyFunctionshowLanguageSelector( $params, $smarty) {
    global $_SHOP;
    if (count($_SHOP->langs)<=1) { return ''; }

  	$result  = '<ul class="art-hmenu level0" style="float:right"><li><a href="?setlang='.$_SHOP->lang.'">'.$_SHOP->langs_names[$_SHOP->lang].'</a><ul class="level1">';
  	foreach ($_SHOP->langs as $lang) {
  		 if ($lang != $_SHOP->lang) {
  		 	$result  .= '<li><a href="?setlang='.$lang.'">'.$_SHOP->langs_names[$lang].'</a></li>';
  		 }
  	}
  	$result .= '</ul></li></ul>';
//  	echo $result;

//  	die ('test');
    return $result;
  }

}

?>