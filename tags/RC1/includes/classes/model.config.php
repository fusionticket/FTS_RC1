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
 * @copyright 2011
 */
/**
 *
 *
 */
class config extends model{


  protected $_idName    = 'config_field';
  protected $_tableName = 'configuration';
  protected $_columns   = null;

  /**
   * Constructor
   */
  function __construct($filldefs= false){
    global $_SHOP;

    //  if (!isset($_SESSION['CONFIG_FIELDS']) || empty($_SESSION['CONFIG_FIELDS'])) {
    $this->_columns =  $this->loadConfig();
    //   }
    // $_SESSION['CONFIG_FIELDS'];
    // var_dump($this);
    if ($filldefs) {
      include('config'.DS.'data_config.php');
      self::loadPluginConfig($config);
      foreach ($config as $fields){
        foreach ($fields as $key => $row ){
          if (isset($_SHOP->$key)) {
            $this->$key = $_SHOP->$key;
          } else {
            $this->$key = $row[0];
          }
        }
      }
    }
  }

  static function ApplyConfig($defaults=false) {
    global $_SHOP;
    if (file_exists(self::getConfigFile())) {
      $file  = file_get_contents(self::getConfigFile());
      $config = unserialize($file);
      //   var_dump($config);
      foreach ($config as $key => $row ){
        $_SHOP->$key = $row;
      }
    } else {
      if ($defaults) {
        include('config'.DS.'data_config.php');
        self::loadPluginConfig($config);

        foreach ($config as $fields){
          foreach ($fields as $key => $row ){
            if (!isset($_SHOP->$key)) {
              $_SHOP->$key = $row[0];
              $data[$key]  = $row[0];
            }
          }
        }
      }
      $tmp = ShopDB::query("select config_field, config_value from configuration");
      while ($row = ShopDB::fetch_row($tmp)) {
        //  echo $row[1][1];
        if (($row[1][1]==':') or ($row[1][1]==';')) {
          $row[1] = unserialize($row[1]);
        }

        if (!is_null( $row[1])) {
          $_SHOP->$row[0] =  $row[1];
          $data[$row[0]]  =  $row[1];
        }
      }

      file_put_contents(self::getConfigFile(), serialize($data), LOCK_EX);
    }
    //  MySQL::freeResult($tmp);
  }

  static function getConfigFile() {
    global $_SHOP;
    return $_SHOP->tmp_dir.'cached_config_data.dat';
  }

  static function load(){
    $config = new config(true);
    $tmp = ShopDB::query("select config_field, config_value from configuration");
    while ($row = ShopDB::fetch_row($tmp)) {
      if (($row[1][1]==':') or ($row[1][1]==';')) {
        $row[1] = unserialize($row[1]);
      }
      if (!is_null($val)) {
        $config->$row[0] = $row[1];
      }
    }
    return $config;

  }

  static function loadConfig () {
    include('config'.DS.'data_config.php');
    self::loadPluginConfig($config);

    $_SESSION['CONFIG_FIELDS'] = array();
    foreach ($config as $fields){
      foreach ($fields as $key => $row ){
        if (!is_array($row)) { continue; }
        if (count($row)<=2) $row[2]='';
        $x = $row[2].'$'.$key;
        $_SESSION['CONFIG_FIELDS'][$key]=self::getFieldtype($x);
      }
    }
    return $_SESSION['CONFIG_FIELDS'];
  }

  static function loadPluginConfig( & $config){
    $plugs = plugin::loadAll(false);
    foreach ($plugs as $plugin){
      $plugin->config($config);
    }
  }

  static function asArray($group='') {
    $cnf = new Config(true);
    $cnfa = (array)$cnf;
    array_shift($cnfa);
    array_shift($cnfa);
    array_shift($cnfa);
    $cnf->fillArrays($cnfa, true);

    return $cnfa;
  }

  static function loadOrganizer() {
    global $_SHOP;
    $cnf = array();
    foreach ($_SHOP as $key => $field){
      //  echo $key,strpos($key,'organizer_'),' ';
      if (strpos($key,'organizer_')===0) {
        $cnf[$key] = $field;
      }

    }
    return $cnf;
  }

  function fillPost($nocheck=false){
    if (!$nocheck) {
      $arr = $_POST;
      $this->fillArrays($arr, false);
      return $this->_fill($arr,$nocheck);
    } else
      parent::fillPost($nocheck);
  }

  function fillArrays(&$arr, $toString= false){

    foreach($this->_columns as $key => $type){
      if (is_numeric($key)) {
        $key = $type;
        $type = $this->getFieldtype($key);
      }

      if ($type & self::MDL_ARRAY) {
        if ($toString && is_array(is($arr[$key],false))) {
          $result = '';
          $values = $arr[$key];
          foreach($values as $key1 =>$value) {
            if (!is_numeric($key1)) {$result .= "{$key1}=";}
            $result .= $value.";";
          }
          $arr[$key] = $result;
        } elseif (!$toString && is_string($arr[$key])) {
          $values = str_replace("\n" ,'' ,$arr[$key] );
          $values = explode(';',$values);
          $result = array();
          foreach($values as $value){
            if (!empty($value)) {
              if (strpos($value, '=')===false) {
                $result[] = $value;
              } else {
                list($id,$value) = explode('=',$value);
                $result[$id] = $value;
              }
            }
          }
          $arr[$key] = $result;
        }
      }
    }
  }

  function Save($id = null, $exclude=null) {
    ShopDB::Begin('Save config');
    foreach ($this->_columns as $key =>$type ){
      $key1 = $key;
      if (is_numeric($key)) {
        $key = $type;
        $type = $this->getFieldtype($key);
      }
      if (($type & self::MDL_SKIPSAVE)){ continue;}
      if (is_array($exclude) && in_array($key,$exclude )) { continue; }
      if ($type & self::MDL_FILE) {
        if (!$this->fillFilename ($_REQUEST, $key)) {return self::_abort('cant_save_configvalue_filename', $key); }
      } else
      if (($val= $this->_set($key1, '~~~',$type))) {

        if (!ShopDB::query("insert into configuration (config_field, config_value) VALUES ("._esc($key).",{$val})
                           on duplicate key update config_value={$val}")) {
          return self::_abort('cant_save_configvalue', $key);
        }
      }
    }
    ShopDB::commit('Config saved');
    if (file_exists(self::getConfigFile())) {
      @unlink(self::getConfigFile());
    }
    addNotice('configuration_is_saved');
    return true;
  }

  function updateFile($name, $value, $esc = true){
    if ($esc) {
      $value = serialize($value);
      $value = _esc($value);
    }

    if (!ShopDB::query("insert into configuration (config_field, config_value) VALUES ("._esc($name).",".$value.")
                       on duplicate key update config_value=".$value)) {
      return self::_abort('cant_save_configvalue', $name);
    }
    if (file_exists(self::getConfigFile())) {
      @ unlink(self::getConfigFile());
    }
    return true;
  }

}
?>