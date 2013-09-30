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



/*/Check page is secure
if($_SERVER['SERVER_PORT'] != 443 || $_SERVER['HTTPS'] !== "on") {
$url = $_SHOP->root_secured.$_SERVER['REQUEST_URI'];
echo "<script>window.location.href='$url';</script>"; exit;
//header("Location: https://"$_SHOP->root_secured.$_SERVER['SCRIPT_NAME']);exit;}
}
//remove the www. to stop certificate errors.
if(("https://".$_SERVER['SERVER_NAME']."/") != ($_SHOP->root_secured)) {
$url = $_SHOP->root_secured.$_SERVER['REQUEST_URI'];
echo "<script>window.location.href='$url';</script>"; exit;
}*/

require_once (CLASSES.'class.smarty.php');
  require_once (CLASSES.'class.controller.php');


// remove the # below under linux to get a list of locale tags.

#  print_r(list_system_locales());



class ctrlWebShop extends controller  {
  protected $smarty ;
  public    $session_name = "ShopSession";

  public function __construct($context='web', $page= '', $action) {
    global $_SHOP;

    parent::__construct($context, $page, $action);
    $this->theme_name = is($this->theme_name,$_SHOP->theme_name);

    $this->smarty = new MySmarty($this);
    $this->Loadplugins(array('plugin','MyCart','User','Order','Update'));
  }

  function preinit(){
    parent::preinit();
    $this->initPlugins();
    $this->smarty->init($this->context);
  }

	public function drawMenu() { }

  public function drawHeader() {
    global $_SHOP;
    if (file_exists($_SHOP->tpl_dir . "theme".DS. $this->theme_name.DS . $this->smarty->layoutName)) {
    	$this->drawMenu();
      $this->smarty->assign('WebContent', $this->content);
      $this->smarty->assign($this->smarty->themeAssign);
      $this->smarty->display( $_SHOP->tpl_dir . "theme".DS. $this->theme_name.DS . $this->smarty->layoutName);
    } else
       echo $this->content;
    return true;
  }

  public function drawContent() {
    $this->assign('action',$this->action);
//    $this->assign('isAjax',$isAjax);
    $this->assign('page','shop');
  	$this->smarty->display(is($this->current_page,'shop').'.tpl');
    $this->executed = !$this->smarty->ShowThema;
  }


  public function assign($tpl_var, $value = null) {
    return $this->smarty->assign($tpl_var, $value);
  }
}

?>