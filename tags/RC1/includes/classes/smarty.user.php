<?php
/**
%%%copyright%%%
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

if (!defined('ft_check')) {die('System intrusion ');}

class User_Smarty {

  var $logged;

  function User_Smarty ($smarty)
  {
    if(isset($_SESSION['_SHOP_USER'])){
      $this->load_f($_SESSION['_SHOP_USER']);
    }
    $smarty->register_object("user",$this);
    $smarty->assign_by_ref("user",$this);
  }
  function mode(){
    global $_SHOP;
    return (int)$_SHOP->shopconfig_user_activate;
  }

  function load($params,$smarty){
   $this->load_f($params['user_id']);
  }

  function load_f($user_id){
    $user = User::loadArr($user_id);
    $this->_fill($user);
    $this->logged=($user)?($user['active']):false;
    if ($this->active) {
      $_SESSION['_NEW_MEMBER']= false;
    }
  }

  function login ($params,$smarty){
    $url = is($params['uri'], $_SERVER["REQUEST_URI"]);
    $this->login_f($params['username'],$params['password'],$url);
  }

  function login_f ($username, $password, $url){
    if ($user = User::Login($username, $password)) {
    	$this->_fill($user);
    	$this->logged=true;
    	$this->is_member  = true;
      $this->new_member = false;
      $_SESSION['_NEW_MEMBER']= false;
      if ($url && strrpos($url,"activation.php") == false) {
      echo "<script>window.location.href='{$url}';</script>";
      exit;
      }
    }
  }

  function logout ($params,$smarty){
    $this->logout_f();
  }

  function logout_f (){
    $this->new_member = false;
    $_SESSION['_NEW_MEMBER']= false;
    User::logout();
    $this->_clean();
  }

 /* User data gets subbmitted to here */
  function register ($params, $smarty){
    if (!$this->register_f($params['ismember'], $params['data'], $params['mandatory'], $params['secure'],$params['short'] ) || hasErrors()) {
      $smarty->assign('user_errors',true);
    }
  }

/*The next bit of code creates users */
  function register_f ($ismember, &$member, $mandatory_l=0, $secure='', $short=0 ){
    if (is_string($ismember)){
      $type =($ismember =='true')?2:3;
    } elseif (is_bool($ismember)){
      $type =($ismember)?2:3;
    } elseif (is_integer($ismember)) {
      $type = $ismember;
    } else {
      addwarning('Invalid_Member_type', gettype($ismember).' '.$ismember );
      return false;
    }

    if($res = User::register($type, $member, convMandatory($mandatory_l) , $secure, $short)){ /* $res == the returned $user_id from create_member in user_func.php */
      $_SESSION['_NEW_MEMBER']= $ismember;
      $this->load_f($res);
      $this->new_member = $ismember;
      return $res;
    }
    $this->new_member = false;
    $_SESSION['_NEW_MEMBER']= false;
    return false;
  }
///////////////////
//Update Member Function!
/////////////////////

  function update($params,$smarty){
    if(!$this->update_f($params['data'],$params['mandatory'],$params['short'])){
      $smarty->assign('user_errors',true);
  	}
  }

  function update_f (&$member, $mandatory_l=0, $short=0){
    if ($this->user_id <> $member['user_id']) {
      addWarning('bad_user_id');
      die('System error while changing user data');
    }
    $mandatory = convMandatory($mandatory_l);

		if (User::UpdateEx($member, $mandatory, $short)) {
		  $user = User::loadArr($this->user_id);
      $this->_fill($user);
      $this->logged=$user['active'] ;
      addNotice('successfully_updated_user_details');
      return true;
   	} else {
      return false;
   	}
 	}

/////////////////
/////////////////
  function forgot_password ($params,$smarty){
    $smarty->assign('result',$this->forgot_password_f($params['email']));
  }

  function forgot_password_f ($email){
    return User::forgot_password($email);
  }

  function resend_activation($params,$smarty){
  	$this->resend_activation_f($params['email']);
	}

	function resend_activation_f($email){
   	return User::resend_activation($email);
	}

  function _fill ($user){ ///????
    $this->_clean();
    if (is_array($user)) {
      foreach($user as $k=>$v){
        $this->$k=$v; /// What does this do? Sets User_Smary->$k as $v ?
      }
    }
    $this->new_member = is($_SESSION['_NEW_MEMBER'],false);

  }

  function _clean (){
    $user=(array)$this;
    foreach($user as $k=>$v){
      unset($this->$k);
    }
  }

  function activate(){
    global $smarty;
    if (!isset($_REQUEST['uar'])) {
      return false;
    }
    if ($actived = User::activate($_REQUEST['uar'])) {
      $_SESSION['_NEW_MEMBER']= false;
      $this->active = true;
      $this->logged = true;
    }
    return $actived;
  }

  function asArray(){
    return (array)$this;
  }
}
?>