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
// $id$

if (!defined('ft_check')) {die('System intrusion ');}

require(LIBS.'smarty3/Smarty.class.php');

//$smarty->force_compile = true;

class MySmarty extends Smarty {
  public $layoutName = 'theme.tpl';
  public $deprecation_notices = false;
  public $_SHOP_db_res = array();
  public $ShowThema  = false;
  public $title ='Fusion Ticket';
  public $debugging = false;

  public $headerNote;
  public $footNote;
  public $buttons;
  public $controller;
  public $theme_name;
  public $themeAssign;
  public $my_template_source;

  public function __construct($controller = null) {
    global $_SHOP;

  //  $this->exception_handler = array($this, 'exception_handler');
    $this->controller = $controller;

    parent::__construct();
    $this->muteExpectedErrors();

    $this->caching        = false;
    $this->cache_id       = 'cache_';
    $this->cache_lifetime = 120;
    $this->config_dir   = INC . 'lang'.DS;
    if (is_object($controller)) {
      $controller->loadPlugins(array('gui'));
      $this->theme_name = $controller->theme_name;
    }
    $this->cache_dir      = INC.'temp'.DS;
    $this->compile_id     = 'template_';
    $this->compile_dir    = $this->cache_dir;

    $this->register_function('theme', array(&$this,'_SetTheme'));
    $this->register_function('redirect', array(&$this,'_ReDirect'));
    $this->register_prefilter(array(&$this,'Con_prefilter'));
    $this->register_function('url', array(&$this,'_Url'));
 //   $_SHOP->smarty = $this;
  }

  public function init($context='web') {
    global $_SHOP;

    $this->controller->__gui->gui_name  ='TblLower';
    $this->controller->__gui->gui_value ='TblHigher';

    $this->setTemplateDir(array($_SHOP->tpl_dir . "theme".DS. $this->theme_name.DS,
                                $_SHOP->tpl_dir.$context.DS.'custom'.DS,
                                $_SHOP->tpl_dir.$context.DS));
 //   $this->default_resource_type = 'mysql';

    $this->setCacheDir($_SHOP->tmp_dir);
    $this->setCompileDir($_SHOP->tmp_dir); // . '/web/templates_c/';
    $this->compile_id   = $context.'_'.$_SHOP->lang;

    $this->addPluginsDir(INC . "shop_plugins".DS);

    $this->assign('action', $_REQUEST['action']);   // This needs to be added later .'_action'

    $this->assign('_SHOP_root', $_SHOP->root);
    $this->assign('_SHOP_root_secured', $_SHOP->root_secured);
  	$this->assign('_SHOP_lang', $_SHOP->lang);
    $this->assign('_SHOP_theme', $_SHOP->tpl_dir . "theme".DS. $this->theme_name.DS );
    $this->assign('_SHOP_themeimages', $_SHOP->images_url . "theme/".$this->theme_name.'/' );
    $this->assign("_SHOP_files", $_SHOP->files_url );//ROOT.'files'.DS
    $this->assign("_SHOP_images", $_SHOP->images_url);
    $this->assign( config::asArray());
    $this->assign('hideGoogleAds',IS($_SHOP->hideGoogleAds,false));
   // $this->debugging = true;
    $this->debugging_ctrl = false;

  	$_SESSION['_SHOP_THEME_NAME'] = $this->theme_name;
  }

  public function _setMenuBlock($params, $content, $smarty, $repeat) {
    $this->menuBlock =$content;
    return '';
  }

  public function _SetTheme( $params, $smarty){
    If (isset($params['name'])) {
      $this->layoutName = $params['name'];
    }
    If (isset($params['title'])) {
      $this->title = $params['title'];
    }
    If (isset($params['header'])) {
      $this->headerNote = $params['header'];
    }
    If (isset($params['footer'])) {
      $this->footNote = $params['footer'];
    }
    If (isset($params['set'])) {
      $this->ShowThema = $params['set'];
    } else {
      $this->ShowThema = true;
    }
  }

  function Con_prefilter($source, $smarty) {
 //  echo preg_replace('/\!(\w+)\!/', "con('$1')", $source) ,"\n<br><hr><br>\n";
  //  echo  $source ,"\n<br><hr><br>\n";
     return preg_replace('/\!(\w+)\!/', 'con("$1")', $source);
  }

  public function Loadplugins($pluginList) {
    foreach ($pluginList as $plugin) {
      $filename = 'smarty.'.strtolower($plugin).'.php';
      require_once ($filename);
      $classname = $plugin.'_smarty';
      $plugin = "__{$plugin}";
      $this->$plugin  = new $classname($this);
    }
  }
  public function _URL( $params, &$smarty, $skipnames= array()){
    Global $_SHOP;
    If (isset($params['url'])) {
      return $_SHOP->root_secure.$params['url'];
    } elseIf (isset($params['url'])) {
      return $_SHOP->root.$params['url'];
    } else {
      If (!is_array($skipnames)) {$skipnames= array();}
    //  print_r($params);
      $urlparams =array();
      foreach ($params as $key => $value) {
        if (!in_array($key,array('file')) and
            !in_array($key,$skipnames)) {
          $urlparams[$key] = $value;
        }
      }
   //   $urlparams = substr($urlparams,1);
     // print_r($urlparams);
      return makeURL($params['file'], $urlparams);
    }
  }
  public function _ReDirect( $params, &$smarty){
    If (isset($params['status'])) {
      $status = $params['status'];
      unset($params['status']);
    }
    redirect($this->_URL($params, $smarty), $status);
    die;
  }
  public function pushBlockData($dbrec){
    array_push($this->_SHOP_db_res, $dbrec );
  }
  public function popBlockData(){
    return array_pop($this->_SHOP_db_res);
  }
  function exception_handler ($exception) {
     addWarning($exception->getMessage());
  }
  public function register_function($function, $function_impl, $cacheable=true, $cache_attrs=null)
  {
    $this->registerPlugin('function', $function, $function_impl, $cacheable, $cache_attrs);
  }
  public function register_object($object, $object_impl, $allowed = array(), $smarty_args = true, $block_methods = array())
  {
    settype($allowed, 'array');
    settype($smarty_args, 'boolean');
    $this->registerObject($object, $object_impl, $allowed, $smarty_args, $block_methods);
  }
  public function register_modifier($modifier, $modifier_impl)
  {
    $this->registerPlugin('modifier', $modifier, $modifier_impl);
  }
  public function register_prefilter($function)
  {
    $this->registerFilter('pre', $function);
  }
  public function assign_by_ref($tpl_var, &$value)
  {
    $this->assignByRef($tpl_var, $value);
  }
}
?>