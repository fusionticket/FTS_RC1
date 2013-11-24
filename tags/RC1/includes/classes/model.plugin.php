<?PHP
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
class plugin extends model {
	public static $MyPlugins   = array(0=>false);
  protected $isLocked = false;
  protected $_idName    = 'plugin_id';
  protected $_tableName = 'plugins';
  protected $_columns   = array( '#plugin_id', '*plugin_name', '*plugin_version','*plugin_enabled',
                                 '*plugin_protected', 'plugin_settings', '*plugin_priority');
  protected $_plug = null;
  public $plugin_id = 0;

  static function GetPlugins($plugin= null) {

    if (is_bool($plugin)) {
      self::$MyPlugins[0] = self::$MyPlugins[0] || $plugin;
    } elseif (is_object($plugin)) {
      self::$MyPlugins[$plugin->plugin_name] = $plugin;
    }
    return self::$MyPlugins;
  }

  static function load($plugin_name) {
    $plugs = self::Getplugins();
    if (isset($plugs[$plugin_name])) {
      return $plugs[$plugin_name];
    }
    $query = "select * from `plugins` where plugin_name = "._esc($plugin_name);
    $plugclass = "plugin_".$plugin_name;
    $plugin = null;
    if($plug_d=ShopDB::query_one_row($query) && file_exists(INC.'plugins'.DS.'plugin.'.$plugin_name.'.php')) {
      $plugin = new plugin;
      $plugin->plug($plug_d['plugin_name'],$plug_d['plugin_id'] );
      $plugin->_fill($plug_d, false);
      self::Getplugins($plugin);
    }
    return $plugin;
  }

  static function loadAll($allrecord=true ) {
    $plugs = self::getPlugins();
    if (count($plugs)>1) {
    	$isall = $plugs[0];
    	unset($plugs[0]);

      if ($isall && !$allrecord) {
        $plug = reset($plugs);
        while (is_object($plug)) {
          if ($plug->plugin_id == 0) {
            unset($plugs[$plug->plugin_name]);
            $plug = current($plugs);
          } else {
            $plug = next($plugs);
          }
        }
        $isall = $allrecord;
      }
      if ($isall == $allrecord) {
        reset($plugs);
        return $plugs;
      }
    }

    self::getPlugins($allrecord);
//    echo '(((',count($plugs),'))))',"<br>\n";print_r(debug_backtrace(false));
    $query = "select * from `plugins`
              where ".($allrecord?'1=1':'plugin_enabled=1')."
              order by plugin_priority, plugin_name ";
    $plugins = array();
    if($res=ShopDB::query($query)) {
      while($plug_d=shopDB::fetch_assoc($res)){
        if (isset($plugs[$plug_d['plugin_name']])) {
          $plugins[$plug_d['plugin_name']] =  $plugs[$plug_d['plugin_name']];
        } elseif (file_exists(INC.'plugins'.DS.'plugin.'.$plug_d['plugin_name'].'.php')){
          $plugin = new plugin;
          $plugin->plug($plug_d['plugin_name'],$plug_d['plugin_id'] );
          $plugin->_fill($plug_d, false);
          $plugins[$plug_d['plugin_name']] = $plugin;
          self::Getplugins($plugin);
        }
      }
    }
    if ($allrecord) {
      $dir = INC .'plugins';
  	  if ($handle = opendir($dir)) {
  		  while (false !== ($file = readdir($handle))){
          if (!is_dir($dir.$file) && preg_match("/^plugin.(.*?\w+).php\z/", $file, $matches)) {
            $content = $matches[1];
            if (isset($plugs[$content])) {
              $plugins[$content] =  $plugs[$content];
            } elseif (!isset($plugins[$content])) {
              $plugin = new plugin(true);
              $plugin->plugin_name = $content;
              $plugin->plugin_id = 0;
              $plugin->plugin_enabled = false;
              $plugin->plugin_priority = 999;
              $plugin->plugin_protected = false;
              $plugin->plug($content);
              $plugins[$content] = $plugin;
              self::Getplugins($plugin);
            }
          }
        }
  		  closedir($handle);
    	}
    }
///    echo '++++++ ',"<br>\n";
    return $plugins;

//    var_dump(self::getPlugins());

  }

	 function plug($plugname='',$id=0) {
	  if (empty($plugname)) return;
    if (!isset($this->_plug)){
  		$file = INC.'plugins'.DS.'plugin.'.$plugname.'.php';
  		if (file_exists($file)){
        require_once ($file);
    		$name = 'plugin_'. $plugname;
        $this->_plug = new $name($this, $id);
        $vars = get_object_vars($this->_plug);
        foreach ($vars as $key => $value) {
          if (strpos($key, 'plugin_') ===0) {
            $this->$key = $value;
          }
        }

    		return $this->_plug;
      }
		} else {
      return $this->_plug;
  	}
  }

  function save($id = null, $exclude= false){
    return parent::save($id, $exclude);
  }

  static function call($aEventname) {
    global $_SHOP;
    $pluginname = false;
    $eventname = $aEventname;
    $args = func_get_args();
    array_shift($args);// print_r($args);
    if (is_array($aEventname)) {
     list($pluginname, $eventname, $section) = $aEventname;
      $type= '%';
    } else {
    	$section ='';
    if (preg_match("/^(update|is|check|list|read|do)(.*?\w+)\z/", $eventname, $matches)) {
    //    var_dump($matches); die();
      $type= array_search($matches[1], array('update','is','check','list','read','do'));
      $type= substr('_%!?* ',$type,1);
        $eventname = substr($eventname, strlen($matches[1]));
      } else {
      $type= substr($eventname,0,1);
        if (strpos('_%!?* ',$type ) !== false) {
          $eventname = substr($eventname,1);
        }
      }
    }

    $prefix = '';
    if (strpos('_%!?* ',$type ) !== false) {
      switch ($type){
        case '_':
           $return = $args[0];
           $prefix = 'update';
           break;
        case '%':
           $return = false;
           $prefix = 'is';
           break;
        case '!':
           $return = true;
           $prefix = 'check';
           break;
        case '?':
           $return = array();
           $prefix = 'list';
           break;
        case '*':
          $return = null;
          $prefix = 'read';
          break;
       default :
          $return = '';
          $prefix = 'do';

      }

    } else {
      trigger_error("Plugin prefix is missing or unknown. ",E_USER_ERROR);
      return;
    }

    $plugins = false;
    if ($pluginname) {
      if (($plug = plugin::load($pluginname)) && in_array('named', $plug->plugin_actions) && $plug->plugin_enabled) {
        $plugins = array($pluginname => $plug);
        $eventname = ucfirst($eventname);
        $prefix = 'action';
      }
    } else {
      $plugins   = plugin::loadAll(false);
      $eventname = ucfirst($eventname);
    }
    if (!is_array($plugins)) return $return;

    foreach ($plugins as $key => $plugin) {
      if (!$plugin->plugin_enabled) {
        continue;
      }
      $plugin = $plugin->_plug;
    //  var_dump($prefix, $eventname, get_class_methods(get_class($plugin)));
    	if (method_exists($plugin, $prefix.$section.$eventname )) {
    		$ret = call_user_func_array(array($plugin, $prefix.$section.$eventname ),$args) ;
    	} elseif (method_exists($plugin, $prefix.$eventname )) {
        $ret = call_user_func_array(array($plugin, $prefix.$eventname ),$args) ;
      } elseif (method_exists($plugin, 'do'.$eventname )) {
        $ret = call_user_func_array(array($plugin, 'do'.$eventname ),$args) ;
      } else continue;
    //  echo $ret;
      switch ($type){
        case '_':
           $return  = $ret;
           $args[0] = $ret;
           break;
        case '%':
           $return = $return || $ret;
           break;
        case '!':
           if (!$ret) return false;
           break;
        case '?':
           $return[$key] = $ret;
           break;
        case '*':
      		 if( !is_null( $ret ) ) {
      			 return $ret;
           }
           break;
        default:
          $return .= (string)$ret;
      }
    }
    return $return;
  }

  static function getTables( $tbls= null ){
    if(is_null($tbls)){
      include (INC.'install'.DS.'data_db.php');
    }
    $plugins = self::loadAll(true);
    foreach($plugins as $key => $plugin) {

 //     var_dump($plugin->plugin_id);
  //    var_dump($plugin->plugin_name);
      if (!is_array($plugin->plugin_actions)) {
        continue;
      }
      if ($plugin->plugin_id) {
        if (!is_object($plugin->_plug)) { continue; }
        $plugin->_plug->getTables($tbls );
      }
    }
    return $tbls;
  }

  function install($updateDB= true) {
    global $_SHOP,$MyPlugins;
    if (!is_object($this->_plug)) { return false; }

    if ($this->_plug->install()) {
      $this->plugin_version = $this->plugin_myversion;
      $this->plugin_enabled = 1;
      if (!$this->save()) {
        addwarning('cant_save_given_plugin');
        return false;
      } elseif ($updateDB) {
        $tbls = self::getTables();
    //    var_dump($tbls); die();
        ShopDB::DatabaseUpgrade($tbls);
      }
      if ($updateDB) {
        unset($MyPlugins);
      }
      unlink($_SHOP->tmp_dir.'smarty_plugins_cache.php');
      return true ;
    }
  }

  function upgrade() {
    global $_SHOP,$MyPlugins;
    if (!is_object($this->_plug)) { return false; }
    if ($this->_plug->upgrade()) {
      $this->plugin_version = $this->plugin_myversion;
      unlink($_SHOP->tmp_dir.'smarty_plugins_cache.php');
      unset($MyPlugins);

      return $this->save();
    }
  }

  function uninstall() {
    global $_SHOP,$MyPlugins;
    if (!is_object($this->_plug)) { return false; }
    if ($this->_plug->uninstall()) {
      $this->delete();
      unset($MyPlugins);
      unlink($_SHOP->tmp_dir.'smarty_plugins_cache.php');
    }
  }

  function config(& $config) {
    if (!is_object($this->_plug)) { return false; }
    return $this->_plug->config($config);
  }
  function loadLanguage() {
    if (!is_object($this->_plug)) { return false; }
    return $this->_plug->loadLanguage();
  }


  function _fill (& $data, $nocheck=true){
    if (!empty($data['plugin_name'])) $this->plug($data['plugin_name']);
    $ok = parent::_fill($data, $nocheck);

    if ($this->_plug && !$this->_plug->isInit) $this->_plug->init();
    return $ok;
  }
}

/**
 * Base class that implements basic plugin functionality
 * and integration with MantisBT. See the Mantis wiki for
 * more information.
 * @package MantisBT
 * @subpackage classes
 */
abstract class basePlugin {
  public $plugin;
	/**
	 * name - Your plugin's full name. Required value.
	 */
  public $plugin_acl  		= null;
  public $plugin_info	  	= null;
	/**
	 * description - A full description of your plugin.
	 */
	public $plugin_description	= null;
	/**
	 * version - Your plugin's version string. Required value.
	 */
	public $plugin_myversion		= null;
	/**
	 * requires - An array of key/value pairs of basename/version plugin dependencies.
	 * Prefixing a version with '<' will allow your plugin to specify a maximum version (non-inclusive) for a dependency.
	 */
	public $plugin_requires	= null;
	/**
	 * author - Your name, or an array of names.
	 */
	public $plugin_author		= null;
	/**
	 * contact - An email address where you can be contacted.
	 */
	public $plugin_email		= null;
	/**
	 * url - A web address for your plugin.
	 */
	public $plugin_url			= null;
  //
  public $plugin_actions = array ('install', 'uninstall', 'upgrade', 'priority', 'smarty', 'named');
  public $plugin_enabled = true;

  public $isInit = false;

	### Core plugin functionality ###
	final public function __construct( $p_base, $id ) {
    $this->plugin = $p_base;
	  $this->plugin_acl = (int)$id << 8;
//	  print_r(debug_backtrace(false));
	}

  function loadLanguage(){
    global $_SHOP;
    if (is($_SHOP->AutoDefineLangs, false) && !file_exists(loadLanguage(get_class($this), true))) {
      file_put_contents(loadLanguage(get_class($this), true), '<'.'?php ?'.'>', FILE_APPEND);
    }
    loadLanguage(get_class($this));
  }
	/**
	 * this function allows your plugin to set itself up, include any necessary API's, declare or hook events, etc.
	 * Alternatively, your can plugin can hook the EVENT_PLUGIN_INIT event that will be called after all plugins have be initialized.
	 */
	public function init() {$this->isInit = true;}

  /**
	 * return an array of default configuration name/value pairs
	 */
	public function config(& $config) {
		return $config;
	}

  public function getTables(& $tbls) {}

	public function install() {
		return true;
	}

	/**
	 * This callback is executed after the normal schema upgrade process has executed.
	 * This gives your plugin the chance to convert or normalize data after an upgrade
	 */
	public function upgrade( ) {
		return true;
	}

	/**
	 * This callback is executed after the normal uninstallation process, and should
	 * handle such operations as reverting database schemas, removing unnecessary data,
	 * etc. This callback should be used only if Mantis would break when this plugin
	 * is uninstalled without any other actions taken, as users may not want to lose
	 * data, or be able to re-install the plugin later.
	 */
	public function uninstall() {
    return true;
	}


}

?>