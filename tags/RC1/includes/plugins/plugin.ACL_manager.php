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
 * @version $Id: plugin.ACL_manager.php 1872 2012-10-11 21:12:23Z nielsNL $
 * @copyright 2010
 */
class plugin_ACL_manager extends baseplugin {

	public $plugin_info		  = 'ACL Adminitration Manager plugin';
	/**
	 * description - A full description of your plugin.
	 */
	public $plugin_description	= 'This plugin is used to handle the Admin ACL.';
	/**
	 * version - Your plugin's version string. Required value.
	 */
	public $plugin_myversion		= '0.0.6';
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

  public $plugin_priority = 1;

  public $plugin_actions  = array ('install','uninstall','protected');

  private $acl;
  private $acl_loaded = false;

  function init() {
    require_once(CLASSES.'class.acl.php'); //echo "[ACL_init]";
    $this->acl = new Awf_Acl();
    parent::init();
  }

  function checkLoadACL() {
 // echo "[ACL_load]";
    if ($this->acl_loaded) return true;
    $this->acl->add(new Awf_Acl_Role('control'))
              ->add(new Awf_Acl_Role('pos'))
              ->add(new Awf_Acl_Role('posman'))
              ->add(new Awf_Acl_Role('organizer'))
              ->add(new Awf_Acl_Role('accountant'))
              ->add(new Awf_Acl_Role('manager'))
              ->add(new Awf_Acl_Role('admin'));

    if (!$this->acl->hasResources()) {
    // Setup the list of roles.
    // 'admin','organizer','control','pos'
      $this->acl->add('control')
                ->add('pos')
                ->add('posman')
                ->add('organizer')
                ->add('accountant')
                ->add('manager')
                ->add('admin');
    }

    // Vertel de Awf_Acl instantie welke role wat mag
    $this->acl->allow('control','control')
              ->extend('pos','control')
                 ->allow('pos','pos')
                 ->allow('pos','shop')
                 ->allow('pos','view')
              ->extend('posman','pos')
                 ->allow('posman','posman');
    $this->acl->extend('organizer','control')
                 ->allow('organizer','organizer')
              ->extend('accountant','organizer')
                 ->allow('accountant','accountant')
              ->extend('manager', 'accountant')
                ->allow('manager','manager')
                ->extend('manager','posman')
              ->extend('admin', 'manager')
                 ->allow('admin','admin')
      ;
    return $this->acl_loaded = true;
  }

  function isAllowedACL($Role, $Resource) {
    $this->checkLoadACL();
    return $this->acl->isAllowed($Role,$Resource);
  }

  function isACL() {
    return $this->checkLoadACL();
  }

  function updateRolesACL($roles = array(), $role=''){
    if (!is_array($roles)) $roles = array();
    $return = array_merge($roles, $this->acl->getRolenames($role));
    return $return;
  }

  function doAddACLResource($name, $role='') {
     $this->checkLoadACL();
     $this->acl->add($name);
     If (!empty($role)) {
       $this->acl->allow($role, $name);
     } else {
       $x = $this->acl->getRolenames();
       $this->acl->allow(reset($x), $name);
     }
     return '';
  }

  function doACLShow() {
    print_r($this->acl);
    return true;
  }
}

?>