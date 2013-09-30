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

require_once ("controller.admin.main.php");

class ctrlControlMain extends ctrlAdminMain {
  public $auth_required=TRUE;
  public $auth_status="control";
  public $session_name="ControlSession";
  protected $section    = 'control';
  public $title = 'tickettaker';

  public function __construct($context='control', $page, $action) {
    parent::__construct($context, $page, $action);
  }

  function loadMenu(){
    $this->addACLs(array('control_admin|control',
                           'search_admin|control'),true);
  	if (empty($_SESSION['tickettaker_event']) && $this->current_page !== 'control' ) {
  		return redirect('index.php');
  	}
    if (!$this->isAllowed($this->current_page.'_admin') ||
        !file_exists( INC ."{$this->section}/view.{$this->current_page}.php")) {
      $this->showForbidden();
      return false;
    }
    require_once ("{$this->section}/view.{$this->current_page}.php");
    $fond = str_replace('.','' ,$this->current_page );
    $classname = "{$fond}View";
    //  var_dump($this);
    $body = new $classname($this->body_width);
    $this->executed = $body->execute();
    if (!$this->executed) {
      $this->width = $body->page_width + $this->menu_width;
      $this->title = $body->title;
      $this->setbody($body);
    }
    return true;

  }


  function drawOrganizer(){
    global $_SHOP;
    if (empty($_SHOP->admin)) {
      parent::drawOrganizer();
      return;
    }
    echo "
		<ul>
      <li><a class='link_head' href='index.php?action=change_event'>". con('tickettaker_change_event')."</a></li>";
    if (!empty($_SESSION['tickettaker_event'])) {
    	echo "
              <li><a class='link_head' href='index.php'>" . con('tickettaker_scan')."</a></li>
      		  <li><a class='link_head' href='view_search.php'>" . con('tickettaker_search')."</a></li>";
    }
  	echo "
      <li><a class='link_head' href='?action=logout'>" . con('tickettaker_logout') . "</a>&nbsp;&nbsp;</td></li>
    </ul>
    ";
  }
}

?>