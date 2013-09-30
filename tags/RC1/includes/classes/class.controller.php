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
/**
 *
 *
 * @version $Id$
 * @copyright 2011
 */

require_once("classes/class.component.php");

/**
 *
 *
 */
class Controller extends Component{
  protected $smarty ;
  protected $HelperList = array();
  protected $context = '';
  protected $current_page = '';
  protected $title      = '';
  protected $executed   = false;
  protected $menu = null;
  protected $view = NULL;
  protected $action = null;
  protected $useSSL = False;
	protected $isJson = false;

  public $auth_required=false;
  public $auth_status="";
	public $authed= false;
  public $session_name = "ShopSession";

  public    $json = array();
  public    $ErrorsAsWarning = false;


  function __construct($context='web', $page= '',$action=''){
    global $_SHOP;
    parent::__construct();

    $_SHOP->session_name  = $this->session_name;
    $_SHOP->auth_status   = $this->auth_status;
    $_SHOP->auth_required = $this->auth_required;
    $_SHOP->section       = $context;
    $this->context = $context;
    $this->current_page = $page;
    $this->action = $action;
  }

  public function preinit(){
  	if ($this->useSSL) {
  		$this->checkSSL();
  	}
  }

  public function init() {
    $this->loadMenu();
    plugin::call('doPageload', $this);
  }

  public function draw() {
    $this->preinit();
    ob_start();
    if ($this->drawAuth(!$this->executed)) {
      $this->init();
      if (!$this->executed) {
        $this->drawContent();
      }
    }
    $this->content = ob_get_contents();
    ob_end_clean();

     plugin::call('doPageFinish', $this);
    if (!$this->executed) {
      if (!$this->drawHeader()){
        echo $this->drawbody( $this->content);
      }
      $this->drawFooter();
    } else {
      echo $this->content;

    }
  }

  function drawBody($content) {
    echo  $content;
  }

  function drawHeader() {
    return false;
  }

  function drawFooter(){
    global $_SHOP;
    if (strpos(constant('CURRENT_VERSION'),'svn') !== false) {
      echo "<div style='text-align:left'>";
      print_r($_SHOP->Messages);
      echo "</div>";
    }
  }

  function drawContent(){
  }

  function drawAuth($allowLogin = true){
    global $_SHOP;
    if ($this->auth_required) {

      require_once "Auth/Auth.php";
      require_once "classes/model.admin.php";

      //authentication starts here
      $params = array("advancedsecurity"=>false,
                      'sessionName'=> $_SHOP->session_name,
                      'allowLogin'=> $allowLogin
                      );

      $auth_container = new CustomAuthContainer($this->auth_status);
      $_auth = new Auth($auth_container,$params);//,'loginFunction'
      $_auth ->setLoginCallback(array($this,'loginCallback'));
      if ($this->action == 'logout') {
        $_auth->logout();
        session_unset();
        $_SESSION = array();
      @  session_destroy();
      }
      $_auth->start();

      if (!$_auth->checkAuth()) {
        orphancheck();
      	$this->authed = false;
        return false;
      }

      if(isset($_auth->admin)){
        $_SHOP->admin = $_auth->admin;
    //    unset($res->admin_password);
      } elseif($res = Admins::load($_SESSION['_SHOP_AUTH_ADMIN_ID'])) {
      	$_SHOP->admin = $res;
        unset($res->admin_password);
      } else {
        session_unset();
        $_SESSION = array();
        session_destroy();
        header("location:{$_REQUEST['href']}");
        die;
      }
      $_SHOP->event_ids = $_SHOP->admin->getEventLinks();
      // print_r($_SESSION);
    }
  	$this->authed = true;
    return true;
  }
  function logincallback ($username, $auth){
    global $_SHOP;
    if($res = $auth->admin){
      $_SESSION['_SHOP_AUTH_USER_NAME']=$username;
      $_SESSION['_SHOP_AUTH_ADMIN_ID']=$res->admin_id;
      //  $res = empt($res->user,$res);
      $_SHOP->admin = $res;
      unset($res->admin_password);
      //  unset($res->_columns);
      $_SESSION['_SHOP_AUTH_USER_DATA']= (array)$res;
    }	else {
      session_destroy();
      orphancheck();
      exit;
    }

    $_SESSION['_SHOP_AUTH_USER_NAME']=$username;
    // echo ini_get("session.gc_maxlifetime");
  }

  function loadMenu(){
    return true;
  }

  function addACLs($menu_items, $tabs=false){
    global $_SHOP;

    $results = array();
    foreach($menu_items as $link => $text){
      //  var_dump($text,explode('|',$text.'|',3 ));
      $roles = explode('|',$text.'|' );
      $txt = array_shift($roles);
      foreach($roles as $role) {
        if (empty($role)) { continue; }
        plugin::call('doAddACLResource',$txt, $role );
      }
      if (count($roles)==1 && $roles[0]=='') {
        plugin::call('doAddACLResource',$txt, '' );
      }
      if (isset($_SHOP->admin) && is_object($_SHOP->admin) && $_SHOP->admin->isAllowed($txt)) {
        if ($tabs) {
          $results[$txt] = $link;
        } else {
          $results[$link] = $txt;
        }
      }
    }
    return $results;
  }

  function setTitle($title){
    $this->title = $title;
  }

  function getTitle(){
    return con($this->title);
  }

  function getCurrentPage($withtap=true){
    if ($withtap && isset($this->body) && isset($this->body->tabindex)) {
   //   var_dump($this->body->tabindex,$this->body->tabitems);
      foreach($this->body->tabitems as $key => $value) {
        if ($value == $this->body->tabindex) {
          $tab = $key;
        }
      }
    }
    return $this->context.'~'.$this->current_page.(isset($tab)?'~'.$tab:'');
  }
  public function setJQuery($script){
    addJQuery($script);
  }

  public function Loadplugins($pluginList) {
    foreach ($pluginList as $plugin) {
      $filename = 'smarty.'.strtolower($plugin).'.php';
      require_once (CLASSES.$filename);
      $this->HelperList[]=$plugin;
    }
  }

  protected function initPlugins() {
    foreach ($this->HelperList as $plugin) {
      $classname = $plugin.'_smarty';
      $plugin = "__{$plugin}";
      $this->$plugin  = new $classname($this->smarty);
    }
  }
  protected function checkSSL(){
    global $_SHOP;
    //    print_r($_SERVER);
    if ($_SHOP->secure_site) {
      $url = $_SHOP->root_secured.basename($_SERVER['SCRIPT_NAME']);
      if($_SERVER['SERVER_PORT'] != 443 || $_SERVER['HTTPS'] !== "on") {
        header("Location: $url");
        exit;
      }
    } elseif (($_SERVER['SERVER_PORT'] != 443 || $_SERVER['HTTPS'] !== "on")&& (!$this->isJson)) {
      addWarning('this_page_is_not_secure');
    }
    /* */
  }

  function setMenu($menu) {
    $this->set("menu",$menu);
    if (is_object($menu)) {$menu->setWidth($this->menu_width-10);}
  }

  function setBody($body) {
    $this->set("body", $body);
    $this->body = $body;
  }

  public function drawJson() {
    if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'){
      $this->executed = true;

      $this->preinit();
      if (!$this->drawAuth(false)) {
        $object = array("status" => false, "reason" => 'Session has been expired', 'sessionexpired'=>true);
        echo json_encode($object);
        return;
      }
      plugin::call('doPageload', $this);
      $this->request    = $_REQUEST;
 //     print_r($this);
      if ($this->current_page == 'json') {
        $this->actionName = $this->action;
        $other = substr($this->action,0,1);

        if(strtolower($other) == '_'){
          $this->action = 'do'.ucfirst(substr($this->action,1));
        }else{
          $this->action = "get".ucfirst(strtolower($this->action));
        }
        $result = $this->callAction();
      } else {// var_dump($this);
      	$this->json = false;
        $result = plugin::call(array($this->current_page, $this->action,$this->context), $this);
      	if ($result && $this->json!==false) {
      		$this->json = am($this->json, array("status" =>true, "reason" => 'success'));
      		$this->loadMessages();
      		$this->json['status'] = true;
      		echo json_encode($this->json);
      	}
      }
      if(!$result){
      	$reason = is($this->jsonreason,'Missing action request');
        $object = array("status" => false, "reason" => $reason);//, 'controller'=>$this,'controllername'=>get_class($this)
        echo json_encode($object);
      }
    }else{
      header("Status: 400");
      echo "This is for AJAX / AJAJ / AJAH requests only, please go else where.";
    }
  }

  public function callAction(){
    if(is_callable(array($this,$this->action))){
      $this->json = am($this->json, array("status" =>true, "reason" => 'success'));
      //Instead of falling over in a heap at least return an error.
      try{
        $return = call_user_func(array($this, $this->action));
      }catch(Exception $e){
        addWarning('json_error',$e->getMessage());
        $return = false;
        $this->json['reason'] = $e->getMessage();
  //      return true;
      }
      $this->loadMessages();
      $this->json['status'] = $return;
//      if($return){
        echo json_encode($this->json);
//      }
      return true;
    }

    return false;
  }

  private function loadMessages() {
    global $_SHOP;
    $this->json['messages']['Error']   = array();
    if (isset($_SHOP->Messages['__Errors__'])) {
      $err = $_SHOP->Messages['__Errors__'];
      foreach ($err as $key => $value) {
        $output = '';
        foreach($value as $val){
          $output .= $val. "</br>";
        }
        if ($this->ErrorsAsWarning ) {
          addWarning('', '* <b>'.con($key) .'</b>: '.$output);
        }
        $this->json['messages']['Error'][$key] = $output;
      }
    }
    $this->json['messages']['warning'] = printMsg('__Warning__', null, false);
    $this->json['messages']['Notice']  = printMsg('__Notice__' , null, false);
  }


    function isAllowed($task, $isAction=false){
      global $_SHOP;
      $okay = isset($_SHOP->admin) && $_SHOP->admin->isallowed($task);
      if (isset($_SHOP->admin) && !$okay) {
        $this->showForbidden();
      }
      return $okay;
    }

  function showForbidden()
  {
    header('HTTP/1.1 403 Forbidden');

    echo("<html>
    <head>
    <title>403 Forbidden</title>
    </head>
    <body>
    <script>
      alert('".con('you_do_not_have_access')."');
      window.location = window.location.pathname;
    </script>
    </body>
    </html>");
    $this->executed = true;
 // throw new Exception('testy');
  }
}
?>