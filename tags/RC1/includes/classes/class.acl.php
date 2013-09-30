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


class Awf_Acl {

    /**
     * Hier worden de roles (rollen) opgeslagen
     *
     * @var array
     */
    protected $roles = array();

    /**
     * Opslag van de resources
     *
     * @var array
     */
    protected $resources = array();

    /**
     * Snelmethod om een role of resource toe te voegen
     *
     * @param Awf_Acl_Role|Awf_Acl_Resource $instance
     * @return Awf_Acl instance van zichzelf voor chaining
     */
    public function add($instance){
        if(is_string($instance)){
            return $this->addResource($instance);
        }elseif($instance instanceof Awf_Acl_Role){
            return $this->addRole($instance);
        }
        throw new Exception('This is not a valid instance to add to Awf_Acl!');
    }

    /**
     * Voeg een role toe
     *
     * @param Awf_Acl_Role $role Instantie van een role
     * @return Awf_Acl instance van zichzelf voor chaining
     */
    public function addRole(Awf_Acl_Role $role){
        $this->roles[(string)$role] = $role;
        return $this;
    }

    /**
     * Voeg een resource toe
     *
     * @param Awf_Acl_Resource $resource instantie van een resource
     * @return Awf_Acl instance van zichzelf voor chaining
     */
    public function addResource( $resource){
        if (!in_array((string)$resource, $this->resources))
          $this->resources[] = (string)$resource;
        return $this;
    }

    /**
     * Haal een role uit de $this->roles array
     *
     * @param string $role
     * @return Awf_Acl_Role
     */
    protected function getRole($role){
        if($role instanceof Awf_Acl_Role){
            return $role;
        }
        if(isset($this->roles[(string)$role])){
            $role = $this->roles[(string)$role];
            if($role instanceof Awf_Acl_Role){
                return $role;
            }
        }
        var_dump($role);
        throw new Exception('This role ('.$role.') does not exists!');
    }
    // returns the list of Role names defined in this ACL class.
    public function getRolenames($role=''){
    	if (empty($role)) {
      return array_keys($this->roles);
    	}
    	$return = array();
    //	var_dump($this->roles);
    	foreach ($this->roles as $key => $data) {
    		if ($this->isAllowed($role,$data)) {
    			$return[] = $key;
    		}
    	}
    	return $return;
    }
    /**
     * Alle rechten die de $extend heeft, worden ook aan de $role gegeven
     *
     * @param string $role
     * @param string $extendRole De rechten die moeten worden overgenomen
     * @return Awf_Acl om chaining mogelijk te maken
     */
    public function extend($role,$extendRole){
        $role       = $this->getRole($role);
        $extendRole = $this->getRole($extendRole);
        $role->setExtend($extendRole);
        return $this;
    }

    /**
     * Voegt een resource toe aan de 'rules' lijst
     * Voegt de bits samen: 0100 + 1010 => 1110
     *
     * @param string $role
     * @param string $resource
     *
     * @return Awf_Acl om chaining mogelijk te maken
     */
    public function allow($role,$resource){
        $role = $this->getRole($role);
        $role->setRule($resource);
        return $this;
    }

    /**
     * Haalt een resource weer uit de rules lijst.
     * Trekt de bits uit elkaar: 1101 - 1011 => 0100
     * @todo Is hier geen beter oplossing voor dan deze loop ??
     *
     * @param string $role
     * @param string $resource
     * @return Awf_Acl om chaining mogelijk te maken
     */
    public function deny($role,$resource){
        $role = $this->getRole($role);
        $role->setRule($resource, false);
        return $this;
    }


    /**
     * Checkt of een bepaalde role een bepaalde resource mag uitvoeren
     *
     * @param string $role De role naam
     * @param string $resource De resource naam
     * @return boolean True als het goegestaan is, anders false
     */
    public function isAllowed($role,$resource){
        $role = $this->getRole($role);
        return $role->isAllowed($resource);
    }

    public function hasResources(){
      return count($this->resources) > 0;
    }
}

class Awf_Acl_Role {

    /**
     * Naam van de role
     *
     * @var string
     */
    protected $name;
    protected $rules = array();
    protected $extends = array();

    /**
     * Maakt instantie aan met de naam
     *
     * @param string $name
     */
    public function __construct($name){
        $this->name = $name;
    }

    public function getRule(){
        return $this->rules;
    }

    public function setRule($value, $add= true){
        if (is_array($value)) {
          $this->rules =  array_merge($this->rules, array_diff( $value, $this->rules));
        //   array_combine($this->rules, $value);
        } elseif ($add && !in_array($value, $this->rules )) {
          $this->rules[] = $value;
        } elseif (!$add && in_array($value, $this->rules )) {
          $key = array_search($value, $this->rules);
          unset($this->rules[$key]);
        }
    }
    public function setExtend($extendRole) {
       $this->extends[] = $extendRole;
    }

    /**
     * De naam van de Role
     *
     * @return string
     */
    public function __toString(){
        return (string)$this->name;
    }

    public function isAllowed($resource){
      $return  = (bool)in_array($resource, $this->getRule());
      if (!$return) {
        foreach ($this->extends as $role) {
          $return  = $role->isAllowed($resource);
          if ($return) break;
        }
      }
      return $return;
    }

}
?>