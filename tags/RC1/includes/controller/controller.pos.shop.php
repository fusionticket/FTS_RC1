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

require_once ("controller.web.shop.php");

class ctrlPosShop extends ctrlWebShop {
  protected $body_width =  '100%';

  public $auth_required=TRUE;
  public $auth_status="pos";
  public $session_name="SalesSession";
  public $theme_name = '_admin2';
  protected $useSSL = true;
  protected $section    = 'pos';

  public function __construct($context='pos', $page, $action) {
    parent::__construct($context, $page, $action);
//    $this->smarty->ShowThema = false;
    $this->Loadplugins(array('POS'));

  }
	public function drawMenu() {
		if (true || $this->authed) {
	    $this->smarty->assign('topmenu', array(array('href'=>'index.php', 'title'=>con("pos_booktickets"), 'active'=>$this->current_page=='order'),
		                                         array('href'=>'view.php', 'title'=>con("pos_currenttickets"), 'active'=>$this->current_page=='view'),
				  															//		 array('href'=>'reports.php', 'title'=>con("pos_reports"), 'active'=>$this->current_page=='statistic'),
					  																 array('href'=>'?action=logout', 'title'=>con("logout"))));

		}
	}

  public function drawContent() {
    $this->__POS->_load();

    if (file_exists(INC."admin/view.{$this->current_page}.php")) {
    //  echo 'view loading';
      require_once (INC."admin/view.{$this->current_page}.php");
      $fond = str_replace('.','' ,$this->current_page );
      $classname = "{$fond}View";
      $body = new $classname($this->body_width);
      $this->executed = $body->execute();
      if (!$this->executed) {
        $this->width = $body->page_width;
        $this->title = $body->title;
        $this->body = $body;
      }
      plugin::call('*Pageload', $this);
    }
    if (isset($this->body)) {
      $this->init();
      if (!$this->executed) {
        $this->body->draw();
      }
    } else {
      parent::drawcontent();
    }
  }
}

?>