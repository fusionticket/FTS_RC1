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

/*
To extend the admin menu you need to add atliest 2 functions:
   function do[adminviewname]_items($items)  // to add new tabs at the
     parameter: $items is the list of tab's to be extends with new tabs.
     return: the new array;
   the value $this->plugin_acl is a counter that can be used to identify
   the right tab is selected. When you need more tabs need in the same admin page
   you need to add a value to the above property
     like:
       function do[adminviewname]_Items($items){
         $items[$this->plugin_acl+00] ='yournewtab|admin'; // <== admin is for usermanagement
         $items[$this->plugin_acl+01] ='yournewtab2|admin'; // <== admin is for usermanagement
         return $items;
       }
   function do[adminviewname]_Draw($id, $view) // will be used to handle
    parameter: $id is the value used above with ($this->plugin_acl+00)
    parameter: $view is the $view object so you can use it within the plugin.
    return: none;


*/

class plugin_custom_AdminPages extends baseplugin {

	public $plugin_info		  = 'Example to extend AdminPages';
	/**
	 * description - A full description of your plugin.
	 */
	public $plugin_description	= 'This plugin is a example how to extend AdminPages';
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
	public $plugin_author		= 'Fusion Ticket Solutions Limited';
	/**
	 * contact - An email address where you can be contacted.
	 */
	public $plugin_email		= 'info@fusionticket.com';
	/**
	 * url - A web address for your plugin.
	 */
	public $plugin_url			= 'http://www.fusionticket.org';

  public $plugin_actions  = array ('config','install','uninstall','priority','enable','protect');
  protected $directpdf = null;

  function getTables(& $tbls){
      /*
         Use this section to add new database tables/fields needed for this plug-in.
      */
   }

  function doAdminViewName_Items($items){
      $items[$this->plugin_acl] ='yournewtab|admin';
      return $items;
    }

  function doAdminViewName_Draw($id, $view){
    global $_SHOP;
    // this section works the same way as the draw() function insite the views it self.
    if ($id==$this->plugin_acl) {
      // do your tasks here.
    }
  }
}
?>