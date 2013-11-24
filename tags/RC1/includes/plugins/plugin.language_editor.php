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
 * @version $Id: plugin.custom_adminpages.php 1808 2012-06-02 10:45:18Z nielsNL $
 * @copyright 2010
 */

/*
To extend the admin menu you need to add atliest 2 functions:
   function do[adminviewname]_items($items)  // to add new tabs at the
     parameter: $items is the list of tab's to be extends with new tabs.
     return: the new array;
   the value $this->plugin_acl is a counter that can be used to identify
   the right tab is selected. When you need more tabs need in the same admin page
   you need to add a value to the above property
     like:
       function do[adminviewname]_Items($items){
         $items[$this->plugin_acl+00] ='yournewtab|admin'; // <== admin is for usermanagement
         $items[$this->plugin_acl+01] ='yournewtab2|admin'; // <== admin is for usermanagement
         return $items;
       }
   function do[adminviewname]_Draw($id, $view) // will be used to handle
    parameter: $id is the value used above with ($this->plugin_acl+00)
    parameter: $view is the $view object so you can use it within the plugin.
    return: none;


*/
error_reporting(E_ALL ^ E_NOTICE);
require_once("classes/class.restservice.client.php");

class plugin_language_editor extends baseplugin {

	public $plugin_info		  = 'Language Editor';
	/**
	 * description - A full description of your plugin.
	 */
	public $plugin_description	= 'With this plugin you can setup a custom language';
	/**
	 * version - Your plugin's version string. Required value.
	 */
	public $plugin_myversion		= '0.0.1';
	/**
	 * requires - An array of key/value pairs of basename/version plugin dependencies.
	 * Prefixing a version with '<' will allow your plugin to specify a maximum version (non-inclusive) for a dependency.
	 */
	public $plugin_requires	= null;
	/**
	 * author - Your name, or an array of names.
	 */
	public $plugin_author		= 'Fusion Ticket Solutions Limited';
	/**
	 * contact - An email address where you can be contacted.
	 */
	public $plugin_email		= 'info@fusionticket.com';
	/**
	 * url - A web address for your plugin.
	 */
	public $plugin_url			= 'http://www.fusionticket.org';

  public $plugin_actions  = array ('install','uninstall');
  protected $directpdf = null;

  function getTables(& $tbls){
      /*
         Use this section to add new database tables/fields needed for this plug-in.
      */
   }

  function config(& $config){

    $config['group_common']['language_editor_plugin_userref'] = array('','view','');
  }


  function doUtilitiesView_Items($items){
      $items[$this->plugin_acl] ='language_editor|admin';
      return $items;
    }

  function doUtilitiesView_Draw($id, $view){
  global $_SHOP;
  // this section works the same way as the draw() function insite the views it self.
    if ($id==$this->plugin_acl) {
      if (!isset($_SESSION['langedit']['lang']))     $_SESSION['langedit']['lang']       = '';
      if (!isset($_SESSION['langedit']['package']))    $_SESSION['langedit']['package']    = '';
      if (!isset($_SESSION['langedit']['version']))    $_SESSION['langedit']['version']    = '0';

      if ($_POST['action'] == 'editlang') {
        $_SESSION['langedit']['diff1']   = null;
        //  var_dump($_SESSION['langedit']);
        $lang =    $_SESSION['langedit']['lang'];
        $package = $_SESSION['langedit']['package'];

        $editfile = dirname(dirname(__FILE__)).DS.'lang'.DS."{$package}_{$lang}.inc";
        $deffile  = dirname(dirname(__FILE__)).DS.'lang'.DS."{$package}_en.inc";
        if ($lang ==='en') {
          $diff1 = $this->_send('language/download.xml', array('ftrevision'=>$this->getRevision(), 'ftlang'=>'en', 'ftpackage'=>$package, 'ftuser'=>0));
          //      var_dump($diff1);

          //  $diff1 = $diff1['data'];
        } else {
          $string1  = file_get_contents($deffile);
          $diff1 = $this->findinside($string1);
          $string1 ='';
        }
        if (file_exists( $editfile)) {
          $settings = getLanginfo($editfile);
          $string2  = file_get_contents($editfile);
          $diff2 = $this->findinside($string2);
        } else {
          $diff2 = array();
          $settings = array();
        }
        $_SESSION['langedit']['diff1'] = $diff1;
        $_SESSION['langedit']['diff2'] = $diff2;
        $_SESSION['langedit']['settings'] = $settings;

        $this->_tablelangedit($view);

      }elseif ($_POST['action'] == 'download') {
        $lang =    $_SESSION['langedit']['lang'];
        $package = $_SESSION['langedit']['package'];
        $version = $_SESSION['langedit']['version'];
        $editfile = dirname(dirname(__FILE__)).DS.'lang'.DS."{$package}_{$lang}.inc";

        if (file_exists( $editfile)) {
          $settings = getLanginfo($editfile);
          $string2  = file_get_contents($editfile);
          $diff1 = $this->findinside($string2);
        } else {
          $diff1 = $this->_send('language/download.xml', array('ftrevision'=>$this->getRevision(), 'ftlang'=>'en', 'ftpackage'=>$package, 'ftuser'=>0));
        }
        list ($diff2,$settings) = $this->_send('language/download.xml', array('ftrevision'=>$this->getRevision(), 'ftlang'=>$lang, 'ftpackage'=>$package, 'ftuser'=>$version,'ftextend'=>true));

        $_SESSION['langedit']['diff1'] = $diff1;
        $_SESSION['langedit']['diff2'] = $diff2;
        $_SESSION['langedit']['settings'] = $settings;
    //    var_dump($_SESSION['langedit']);

        $this->_tablelangedit($view);


      }elseif ($_POST['action']=='update_2') {

        $editfile = dirname(dirname(__FILE__)).DS.'lang'.DS."{$_SESSION['langedit']['package']}_{$_SESSION['langedit']['lang']}.inc";
        $diff2 = $_SESSION['langedit']['diff2'];

        if (!is_writable($editfile)) {
          addwarning('This file is not writable.');
        } else {
          $string2 = "<"."?php\n";
          $string2 .= "/**\n";
          $string2 .= file_get_contents (ROOT."licence.txt")."\n";
          $string2 .= "**/\n\n";
          $string2 .= "//Defines added at: ".date('c')."\n";
          $_SESSION['langedit']['settings']['revision'] = $this->getRevision();
          foreach($_SESSION['langedit']['settings'] as $key => $value) {
            $string2 .= "//".ucfirst($key).': '.$value;
            $string2 .= "\n";
          }
          $oldkey='';
          foreach ($diff2 as $key =>$value) {
            if (is_null($value) || empty($key)) { continue;}
            if ($key{0} <> $oldkey) {
              $string2 .= "\n";
              $oldkey = $key{0};
            }
            $string2 .= "define('$key', \"".$value."\");\n";
          }
          $string2 .= "?>";
          $_SESSION['langedit']['diff2'] = $diff2;


          file_put_contents($editfile,$string2, FILE_TEXT );
          addNotice('Translation is saved');
        }
        redirect('view_utils.php' ,array('action'=>'editlang'));
      }elseif ($_POST['action']=='update') {
        $this->_tablelangedit($view);
        return true;
      } else {
        unset($_SESSION['langedit']['diff1']);
        unset($_SESSION['langedit']['diff2']);
        unset($_SESSION['langedit']['diff3']);
        $this->_tableLangages($view);
      }
    } else {
       return false;
    }
  }

  function doUtilitiesView_Execute($id, $view){
    global $_SHOP;
    // this section works the same way as the draw() function insite the views it self.

    if ($id==$this->plugin_acl) {
    //  die('here '.$id.' '.$this->plugin_acl);
      // do your tasks here.

        //------------------------------------------------------------------------------
      if ($_POST['action']=='setlang') {
        //------------------------------------------------------------------------------
        if (isset($_POST['lang']))       $_SESSION['langedit']['lang']       = clean($_POST['lang']);
        if (isset($_POST['package']))    $_SESSION['langedit']['package']    = clean($_POST['package']);
        if (isset($_POST['version']))    $_SESSION['langedit']['version']    = clean($_POST['version']);
  //      echo json_encode($_SESSION['langedit']);
        return true;


        //------------------------------------------------------------------------------
      } elseif ($_POST['action']=='new_language') {
        //------------------------------------------------------------------------------

        $lang = strtolower(clean($_POST['lang']));
        $editfile = dirname(dirname(__FILE__)).DS.'lang'.DS."site_{$lang}.inc";
        if (!is_string($lang)) {
          die('Language code needs to be 2 characters');
        } elseif (strlen($lang)<>2) {
          die('Language code needs to be 2 characters');

        } elseif (file_exists($editfile)){
          die('Language code already exist.');
        } else {
          $string2 = "<"."?php\n";
          $string2 .= "//CreateDate: ".date('c')."\n";
          $string2 .= "//Creator: ".clean($_POST['creator'])."\n";
          $string2 .= "//Language: ".clean($_POST['name'])."\n";
          $string2 .= "//Revision: ".$this->getRevision()."\n";
          $string2 .= "?>";
          file_put_contents($editfile,$string2, FILE_TEXT );
        }
        $_SESSION['langedit']['lang']       = $lang;
        $_SESSION['langedit']['package']    = null;
        $_SESSION['langedit']['version']    = 0;

        echo ("done");
        return true;

        //------------------------------------------------------------------------------
      } elseif ($_POST['action']=='edit') {
        //------------------------------------------------------------------------------

        $editfile = dirname(dirname(__FILE__)).DS.'lang'.DS."{$_SESSION['langedit']['package']}_{$_SESSION['langedit']['lang']}.inc";
        if (!is_writable($editfile)) {
          die('This file is not writable. : '.$editfile);
        } else {
          $_SESSION['langedit']['diff2'][$_POST['key']] = addslashes($_POST['value']);
        }
        echo 'done';
        return true;

        //------------------------------------------------------------------------------
      } elseif ($_POST['action']=='remove') {
        //------------------------------------------------------------------------------

        $_SESSION['langedit']['diff1'][$_POST['key']] = null;
        $_SESSION['langedit']['diff2'][$_POST['key']] = null;
        unset($_SESSION['langedit']['diff1'][$_POST['key']]);
        unset($_SESSION['langedit']['diff2'][$_POST['key']]);
   //     var_dump($_SESSION['langedit']['diff2'][$_POST['key']]);
        echo 'done';
        return true;

        //------------------------------------------------------------------------------
      } elseif ($_POST['action']=='downcase') {
        //------------------------------------------------------------------------------


      $_SESSION['langedit']['diff2'][strtolower($_POST['key'])] = $_POST['value'];

      $_SESSION['langedit']['diff1'][$_POST['key']] = null;
      $_SESSION['langedit']['diff2'][$_POST['key']] = null;
      unset($_SESSION['langedit']['diff1'][$_POST['key']]);
      unset($_SESSION['langedit']['diff2'][$_POST['key']]);

   //   var_dump($_SESSION['langedit']['diff2'][$_POST['key']]);
  //    var_dump($_SESSION['langedit']['diff2'][strtolower($_POST['key'])]);

        echo 'done';
        return true;
        //------------------------------------------------------------------------------
      } elseif ($_POST['action']=='register')  {
        //------------------------------------------------------------------------------
        //        print_r($_POST); print_r( $_SHOP->language_editor_plugin_userref);
        if (!isset($_SHOP->language_editor_plugin_userref) || empty($_SHOP->language_editor_plugin_userref)) {
          $diff1 = $this->_send('language/register.xml',  array('ftuser'=>clean($_POST['owner']),
                                                                'ftemail'=>clean($_POST['email']),
                                                                'ftcountry'=>$_SHOP->organizer_country,
                                                                'ftuserrev'=>clean($_POST['username'])));
          if ($diff1{0}!=='*') {
            echo 'Error: ',$diff1;
            return true;
          }
          config::updateFile('language_editor_plugin_userref',$diff1);
        } else {
          echo 'Error: You are already registered';
          return true;
        }
        echo 'done';
        return true;
        //------------------------------------------------------------------------------
      } elseif ($_POST['action']=='upload_files')  {
        //------------------------------------------------------------------------------
        $result = 'You dont have access.';
        if (isset($_SESSION['langedit']['canupload']) && $_SESSION['langedit']['canupload']|| !empty($_SHOP->language_editor_plugin_userref)) {


          $lang =    $_SESSION['langedit']['lang'];
          $package = $_SESSION['langedit']['package'];

          $editfile = dirname(dirname(__FILE__)).DS.'lang'.DS."{$package}_{$lang}.inc";
          $string1  = file_get_contents($editfile);
          $data = json_encode( $this->findinside($string1));
          $settings = getlanginfo($editfile);
          // print_r($data);
          if (!isset($settings['language'])|| empty($settings['language'])) {
            $settings['language'] = $_SHOP->langs_names[$lang];
          }
          if (!isset($settings['language'])|| empty($settings['language'])) {
            $settings['language'] = $lang;
          }

          echo $this->_send('language/upload.xml',  array('ftuser'=>$_SHOP->language_editor_plugin_userref,
                                                                   'ftlang'=>$lang,
                                                                   'ftlangname'=>$settings['language'],
                                                                   'ftpackage'=>$package,
                                                                   'ftrevision'=>$this->getRevision(),
                                                                   'ftdata'=>$data,
                                                                   'ftsettings'=>$settings));

        }
        echo 'done';
        return true;

        //------------------------------------------------------------------------------
      } elseif ($_POST['action']=='upload')  {
        //------------------------------------------------------------------------------
        if (!isset($_SESSION['langedit']['canupload']) || !$_SESSION['langedit']['canupload']) {
          $diff1 = $this->_send('language/login.xml',  array('ftuser'=>$_SHOP->language_editor_plugin_userref));
           if ($diff1===true) {
            $_SESSION['langedit']['canupload'] = true;
          } else {
            die ('<h1 style="color:red;">'.$diff1[1]."</h1>");
          }
        }
        $lang = $_SESSION['langedit']['lang'];
        $package = $_SESSION['langedit']['package'];
        if ($_SESSION['langedit']['uploads'][$package]) {
          echo "<h1>Upload agrement</h1>";
          echo "<p><b style='color:red;'>Read the following text carefully.</b></p>";
          echo "<p>You are about to upload a new version of <i>{$package}</i> in the language {$_SHOP->langs_names[$lang]}.</p>";
          echo "<p>By uploading this language file you are sure that:<ul>";
          echo "<li> This language file is at least 60% off your own work.</li>";
          echo "<li>You don't have any unfriendly text added.</li>";
          echo "<li>There are no references to your own website in it.</li>";
          echo "<li>When you upload the language file is part of FusionTicket community.</li>";
          echo "<li>You allow everyone to use and modify your version for their own use.</li>";
          echo "<li>There will be NO rewards on uploading you version, exept a big thank you.</li>";
          echo "</ul>When you dont agree on one of the above please cancel this action.</p>";
          echo "<p><b>Thank you for your time creating this language file.</B></P>";
        }
        return true;

        //------------------------------------------------------------------------------
      } elseif ($_POST['action']=='grid')  {
        //------------------------------------------------------------------------------

        $diff1= $_SESSION['langedit']['diff1'];
        $diff2= $_SESSION['langedit']['diff2'];

        $responce = array();
        $responce['aaData'] = array();
        $i=-1;
        $class = (is_writable($editfile))?'class="editable"':'test="'.$editfile.'"';
        $diff3 =  array_unique(array_merge(array_keys($diff1), array_keys($diff2)));

        usort($diff3,array($this,'lowercmp'));
        $count = count($diff3);
        foreach ($diff3 as $key) {
          if (empty($key) || (is_null($diff1[$key]) && is_null($diff2[$key]))) {
            $count = $count -1;
            continue;
          }
          if (($key != strtolower($key)) && array_key_exists($key, $diff1 ) && !array_key_exists($key, $diff2 )) {
            $count = $count -1;
            continue;
          }
          if ($_POST['iDisplayLength']==0) {
            continue;
          }
          if (!empty($_POST['sSearch']) && strpos($key, trim($_POST['sSearch']) ) ===false) {
            continue;
          }
          $i++;
          if ($_POST['iDisplayStart']>0) {
            $_POST['iDisplayStart'] = $_POST['iDisplayStart'] -1;
            continue;
          }
          $red = ($diff1[$key] === $diff2[$key])?'border: 1px solid green;':$red;
          $red = (!array_key_exists($key, $diff1 ))?'border: 1px solid red;':$red;
          $red = (!array_key_exists($key, $diff2 )|| empty($diff2[$key]))?'border: 1px solid blue;':$red;
          $red .= ($key != strtolower($key))?'color:red;':'';
          if(!array_key_exists($key, $diff1 )){
            $responce['aaData'][$i]=array('data'=>'<table style="width:100%; '.$red.'" border=0  cellspacing=0 cellpadding=0>
                                                      <tr>
                                                         <th colspan=2 style="text-align:left; margin:10; padding:5; " id="'.$key.'_key">'.$key.'</th>
                                                      </tr>
                                                      <tr>
                                                        <td valign=top width="30"><i><b>EN:</b></i></td>
                                                        <td valign=top style="margin:0;padding:0; margin:10;padding:0;" id="'.$key.'_orgin"><i>'.con('langedit_missing').'</td>
                                                      </tr>
                                                      <tr>
                                                        <td valign=top width="30"><i><b>'.strtoupper($_SESSION['langedit']['lang']).':</b></i></td>
                                                        <td valign=top style="margin:0;padding:0;" id="'.$key.'_edit">'.htmlentities(stripslashes($diff2[$key])).'</td>
                                                      </tr>
                                                    </table>');
          } else {
            $responce['aaData'][$i]=array('data'=>'<table style="width:100%;'.$red.'" border=0  cellspacing=0 cellpadding=0>
                                                       <tr>
                                                         <th colspan=2 style="text-align:left; margin:10; padding:5; " id="'.$key.'_key">'.$key.'</th>
                                                       </tr>
                                                       <tr>
                                                         <td valign=top width="30"><i><b>EN:</b></i></td>
                                                         <td valign=top style="margin:0;padding:0;" id="'.$key.'_orgin" >'.htmlentities(stripslashes($diff1[$key])).'</td>
                                                       </tr>
                                                       <tr style="border:1px solid white;">
                                                         <td valign=top ><i><b>'.strtoupper($_SESSION['langedit']['lang']).':</b></i></td>
                                                         <td valign=top style="margin:0;padding:0;" '.$class.' id="'.$key.'_edit">'.htmlentities(stripslashes($diff2[$key])).'</td>
                                                       </tr>
                                                     </table>');
          }
          $red = '';
          $red .= (!isset($diff2[$key]) || empty($diff2[$key]))?'missing ':'';
          $red .= ($key != strtolower($key))?'case ':'';

          $responce['aaData'][$i]['DT_RowClass']=$red;
          $responce['aaData'][$i]['DT_RowId']=$key;

          if ($_POST['iDisplayLength']>0) {
            $_POST['iDisplayLength'] = $_POST['iDisplayLength'] -1;
          }

        }
        $responce["sEcho"] = $_POST['sEcho'];
        $responce[  "iTotalRecords"] = $count;
        $responce[  "iTotalDisplayRecords"] = $i;
        echo json_encode($responce);
        return true;

        //------------------------------------------------------------------------------
      }elseif ($_POST['action']=='copymissing')  {
       // echo 'we doing it';
        $diff1= $_SESSION['langedit']['diff1'];
        //------------------------------------------------------------------------------

        foreach ($diff1 as $key =>$value) {
          if (!isset($_SESSION['langedit']['diff2'][$key])) {
            $_SESSION['langedit']['diff2'][$key] = $value;
          }
        }
        echo 'done';
        return true;

        //------------------------------------------------------------------------------
      }elseif ($_POST['action']=='languages')  {
        //------------------------------------------------------------------------------

        $responce = array();
        $responce['userdata'] = array();
        $responce['aaData'] = array();
        $dir = dirname(dirname(__FILE__)).DS."lang";
        //  echo "<option value=''>Select</option>\n";
        $resp = array('en'=>array());
        $langs = $this->_send('language/getlanguage.xml',array('ftrevision'=>$this->getRevision()));
        // var_dump($langs);
        foreach ($langs as $row) {
            $resp[$row['language']]['DT_RowId']=$row['language'];
            $resp[$row['language']]['code'] = $row['language'];
            $resp[$row['language']]['name'] = $row['value'];
            $resp[$row['language']]['versions']=$row['versions'];
            $resp[$row['language']]['local']   = false;
        }
        if ($handle = opendir($dir)) {
          while (false !== ($file = readdir($handle))) {
            if ($file != "." && $file != ".." && !is_dir($dir.$file) && preg_match("/^site_(.*?\w+).inc\z/", $file, $matches)){
              if (!isset($resp[$matches[1]])) {
                $settings = getLanginfo($dir.$file);
                if (!isset($settings['language']) && isset($_SHOP->langs_names[$matches[1]])) {
                  $settings['language'] = $_SHOP->langs_names[$matches[1]];
                }
                $resp[$matches[1]]['DT_RowId']=$matches[1];
                $resp[$matches[1]]['code'] = $matches[1];
                $resp[$matches[1]]['name'] = $settings['language'];
                $resp[$matches[1]]['versions']= array();
              }
              $resp[$matches[1]]['local']= true;
            }
          }
          closedir($handle);
        }
     //   var_dump($resp);
        foreach ($resp as $value ) {
          $versiontxt ='<span id="'.$value['code'].'_span" data-versions=\''.json_encode($value['versions']).'\' data-count='.count($value['versions']).'>';
          if ($value['local']) {
            $versiontxt .= 'local';
            if (count($value['versions'])>0) {
              $versiontxt .= ' + '.(count($value['versions'])).' version(s) on the server';
            } else
              $versiontxt .= ' version only ';
          } else {
            $versiontxt .= (count($value['versions'])).' version(s) on the server';
          }

          $value['versions'] = $versiontxt.'</span>';

          $responce['aaData'][] = $value;
        }

        $responce["sEcho"] = $_POST['sEcho'];
        $responce[  "iTotalRecords"] = count($responce['aaData']);
        $responce[  "iTotalDisplayRecords"] = count($responce['aaData']);
        echo json_encode($responce);
        return true;

        //------------------------------------------------------------------------------
      }elseif ($_POST['action']=='packages')  {
        //------------------------------------------------------------------------------

        $content = array();
        $responce = array();
        $responce['userdata'] = array();
        $responce['aaData'] = array();
        $responce["sEcho"] = $_POST['sEcho'];
        $responce[  "iTotalRecords"] = 0;
        $responce[  "iTotalDisplayRecords"] = 0;
        if (empty($_SESSION['langedit']['lang'])) {
          echo json_encode($responce);
          return true;
        }
        $dir = dirname(dirname(__FILE__)).DS."lang";
        //  echo "<option value=''>Select</option>\n";
        $resp = array();
        $resp['site']['DT_RowId']='site';
        $resp['site']['language'] = '[Main language file]';
        $resp['site']['server']='';
        $resp['site']['local']='';
        $resp['site']['customized']='';
        $plugs = plugin::loadAll(true);
        foreach ($plugs as $key => $row) {
            $key = get_class( $row->plug('xx'));
            $resp[$key]['DT_RowId']=$key;
            $resp[$key]['language'] = $row->plugin_info;
            $resp[$key]['local']='';
            $resp[$key]['server']='';
            $resp[$key]['customized']='';
        }
        $query = 'select DISTINCT handling_payment from `Handling`';
        if ($res = ShopDB::query($query)) {
          while ($row = shopDB::fetch_array($res)) {
            if ($row['handling_payment']) {
              $key = 'eph_'. $row['handling_payment'];
              $resp[$key]['DT_RowId']=$key;
              $resp[$key]['language'] =  $row['handling_payment'];
              $resp[$key]['local']='';
              $resp[$key]['server']='';
              $resp[$key]['customized']='';
            }
          }
        }
        $langs = $this->_send('language/getpackages.xml',array('ftrevision'=>$this->getRevision(), 'ftlang'=>$_SESSION['langedit']['lang'], 'ftpackages'=> array_keys($resp), 'ftuser'=>$_SESSION['langedit']['version'] ));
        $lang = $_SESSION['langedit']['lang'];
  //      var_dump($langs);
        foreach ($resp as $key => $value ) {
          $settings = array('edit'=>true,'download'=>false,'upload'=>false);
          if (array_key_exists($key, $langs )) {
            $diff1 = $langs[$key]['keys'];
            $value['server'] = $langs[$key]['versions'];
            $value['language'] = $langs[$key]['name'];
            $settings['download'] = $langs[$key]['available'];
          }elseif (($lang!='en') && file_exists(dirname(dirname(__FILE__)).DS.'lang'.DS.$key."_en.inc")) {
            $editfile = dirname(dirname(__FILE__)).DS.'lang'.DS.$key."_en.inc";
            $string1 = file_get_contents($editfile);
            $diff1 = array_keys($this->findinside($string1));
            $value['server'] ='[local only]';
          } else {
            $diff1 = array();
            $value['server'] ='[not available]';
            $settings['edit'] = false;
          }
  //        $responce[  "test"][$key] = $diff1;

          if (file_exists(dirname(dirname(__FILE__)).DS.'lang'.DS.$key."_{$lang}.inc")) {
            $settings['edit'] = true;
            $editfile = dirname(dirname(__FILE__)).DS.'lang'.DS.$key."_{$lang}.inc";
            $string1 = file_get_contents($editfile);
            $diff2 = array_keys($this->findinside($string1));
            $string1 = '';
            $diff = array_intersect($diff1, $diff2);
            if (count($diff1)>0) {
              $diffm = array_diff( $diff1,$diff);
              if (count($diffm )>10) {
                 $diffm = array_slice($diffm, 0,10);
                 $diffm[]='[More...]';
              }
              $value['local'] = '<span title=\''.implode("\n",$diffm).'\'>'.round(max(0,(count($diff)/count($diff1))-0.004)*100).'%' .'</span>';
              if (!empty($_SHOP->language_editor_plugin_userref) && round(max(0,(count($diff)/count($diff1))-0.004)*100)>50) {
                $settings['upload'] = true;
              }
            } else {
              $value['local'] = '[New]';
              if (!empty($_SHOP->language_editor_plugin_userref)) {
                $settings['upload'] = true;
              }
            }
            $diffm = array_diff( $diff2,$diff);
            if (($cnt = count($diffm))>10) {
                 $diffm = array_slice($diffm, 0,10);
                 $diffm[]='[More...]';
            }
            if (count($diffm)>0) {
              //    var_dump(count(array_diff($diff2,$diff)));
              $value['local'] = $value['local'].'<span title="'.implode("\n",$diffm).'"> &plus;'. $cnt .'</span>';
            }

          } else {
            $value['local'] = 'na.';
          }

          $value['DT_RowClass'] .= ($settings['edit'])?'canedit ':'';
          $value['DT_RowClass'] .= ($settings['download'])?'candownload ':'';
          $value['DT_RowClass'] .= ($settings['upload'])?'canupload ':'';
          $_SESSION['langedit']['uploads'][$key] = $settings['upload'];


          $responce['aaData'][] = $value;
        }
        $responce[  "iTotalRecords"] = count($responce['aaData']);
        $responce[  "iTotalDisplayRecords"] = count($responce['aaData']);
        echo json_encode($responce);
        return true;
      }
    }
    return false;
  }


  function getRevision(){
    $rev = explode(" ",INSTALL_REVISION);
    return (int)$rev[1];
  }

  function _send($action='', $data=false){
    global $_SHOP;
       // var_dump($data);
    $this->sended =$data;

    $rsc = new RestServiceClient('http://cpanel.fusionticket.org/'.$action);
    //    echo $action, '= ';
    try{
      $rsc->setArray($data);
 //     $rsc->ftUser  = base64_encode($_SHOP->plugin_languser.'||'.$_SHOP->plugin_langemail.'||'.date('c'));
 //     $rsc->checksom = sha1($rsc->json .$rsc->josUser);
      $rsc->excuteRequest();
      $value  = $rsc->getResponse();
      $return = json_decode($value, true);
/*  //     var_dump(json_last_error(),$return, $value);
      if (is_array($return) && $return['error']) {
        $this->errors =$return['reason'];
        return false;
      }
   */
      return is($return, $value);
    }catch(Exception $e){
      $this->errors =   var_export($e->getMessage(), true);
      return false;// " - Could not check for new version.";
    }

  }

  function _tableLangages($view){
    global $_SHOP;
    ?>
    <style>
      label, input { display:block; text-align:left;}
      input.text { margin-bottom:12px; width:95%; padding: .4em; }
      h1 { font-size: 1.2em; margin: .6em 0; }
      fieldset { padding:0; border:0; margin-top:0; }

      .ui-dialog .ui-state-error { padding: .3em; }
      .ui-menu { width: 100px; }
      .validateTips { border: 1px solid transparent; padding: 0.3em; }
    </style>
 		<script type="text/javascript">
       $(document).ready(function() {
          var lastsel;
          var doaction;
          var mygrid1 = $("#table1").dataTable({
                 //   bProcessing: true,
                    bServerSide: true,
                    sAjaxSource: "view_utils.php?x=languages",
                    sServerMethod: "POST",
                    fnServerParams: function ( aoData ) {
                      aoData.push( { "name":  'action', "value": "languages" } );
                    },
                    sScrollY: '120px',
                    bJQueryUI: true,
                        sDom: 'T<"H"<"lang_toolbar"><"lang_addbutton">r>t',
   //                "sDom": 'T<"clear">lfrtip',
                    bSort: false,
                    bAutoWidth: true,
                    oLanguage: {
                      sEmptyTable: 'No data available in table',
                      sLoadingRecords : 'Loading...',
                      sZeroRecords:  'No matching records found.'
                    },
                    bPaginate: false,
                    bScrollCollapse: false,
                    aoColumns : [ {'sWidth':'35px', "mData": "code" },
                                  { "mData": "name" },
                                  {'sWidth':'405px', "mData": "versions" }],
                    fnGridComplete:  function(aoData){
                      var oTT1 = TableTools.fnGetInstance( 'table1' );

                      if ($('#lang').val()) {
                        oTT1.fnSelect( $('#table1 tbody #'+$('#lang').val() ));
                      } else {
                        oTT1.fnSelect( $('#table1 tbody tr')[0] );
                      }
                    },
                    oTableTools: {
                      "sRowSelect": "single",
                      "aButtons": [],
                  fnRowDeselected: function ( nodes ) {
                      if (nodes.length !=0) {
                         /* prevent calling reset twice when blurring */
                          if (this.editing) {
                              this.buttonbar.remove();
                              this.ftsmenu.remove();
                              this.editing   = false;
                          }
                      }
                  },
                  fnRowSelected: function ( nodes ) {
                    var TableTool = this;

                    if (nodes.length ==0 || this.editing ) { return; }

                    $.post("view_utils.php?x=setlang",
                      { action: "setlang", lang: nodes[0].id },
                      function(data){ mygrid2.fnDraw(true);},
                      "html");

                    currentRow = nodes[0].id;

                    var editfield   = $('#'+nodes[0].id+'_span');//.get(1);
                    if (editfield.data('count')<=1) {
                      return false;
                    }
                    var selection  = editfield.data('versions');

                    this.buttonbar  = $('<div id="buttonbar" style="z-index:9999; margin:0px; position:relative; float:right;" />');
                    var button;

                    button = $('<button title="View other language version from the server." />').button({
                              icons: {
                                primary: "ui-icon-search"
                              },
                              text: false
                            }).click(function () {
                                      var menu = $( '#'+nodes[0].id+'_menu').show();
                                      var $table = menu.children("li").children("a");
                                      $table.css({ position: "absolute", visibility: "hidden", display: "block" });
                                      var tableWidth = $table.outerWidth();
                                      $table.css({ position: "", visibility: "", display: "" });
                                      menu.width(tableWidth+10);
                                      menu.position({
                                        my: "right top",
                                        at: "right bottom",
                                        of: $( this )
                                      });
                                      $( document ).one( "click", function() {
                                        menu.hide();
                                      });
                                      return false;
                            } );
                        button.height(16).width(16);
                        this.buttonbar.append(button);

                        var menu = $( '<ul style="text-align:left;" />' );
                        menu.attr('id',nodes[0].id+'_menu');
                        $.each(selection , function(index, value) {
                          var row = $('<a href="#" class="versionmenu" style="white-space: nowrap;"  />').data('id',index).text(value);
                          menu.append($('<li  style="white-space: nowrap;" />').append(row));
                        });
                        menu.menu().hide();
                        $('.versionmenu').click(function(e) {
                          $('#version').val($(this).data('id'));
                          $.post("view_utils.php?x=setversion",
                            { action: "setlang", version: $(this).data('id') },
                            function(data){ mygrid2.fnDraw(true);},
                            "html");
                          return false;
                        });
                        $(document.body).append(menu);
                        editfield.parent().prepend(this.buttonbar);
                     TableTool.ftsmenu = menu
                    TableTool.editing    = true;
                  }

              }
          });
           var mygrid2 = $("#table2").dataTable({
                 //   bProcessing: true,
                    bServerSide: true,
                    sAjaxSource: "view_utils.php?x=package",
                    sServerMethod: "POST",
                    fnServerParams: function ( aoData ) {
                      aoData.push( { "name":  'action', "value": "packages" } );
                     },
                    sScrollY: '240px',
                    bJQueryUI: true,
                        sDom: 'T<l<t>p>',
   //                "sDom": 'T<"clear">lfrtip',
                    bSort: false,
                    bAutoWidth: true,
                    oLanguage: {
                      sEmptyTable: 'No data available in table',
                      sLoadingRecords : 'Loading...',
                      sZeroRecords:  'No matching records found.'
                    },
                    bPaginate: false,
                    bScrollCollapse: false,
                    aoColumns : [ { "mData": "language" },
                                  {'sWidth':'155px', "mData": "server" },
                                  {'sWidth':'155px', "mData": "local" },
                             //     {'sWidth':'155px', "mData": "customized" }
                                  ],
                    fnGridComplete:  function(aoData){
                      var oTT1 = TableTools.fnGetInstance( 'table2' );
                      if (oTT1.editing ) {
                        oTT1.buttonbar.remove();
//                        this.ftsmenu.remove();
                        oTT1.editing   = false;
                      }
                      if ($('#package').val()) {
                        oTT1.fnSelect( $('#table2 tbody #'+$('#package').val() ));
                      } else {
                        oTT1.fnSelect( $('#table2 tbody tr')[0] );
                      }
                    },
                    oTableTools: {
                      "sRowSelect": "single",
                      "aButtons": [],
                      fnRowDeselected: function ( nodes ) {
                        if (nodes.length ==0 || !this.editing ) { return; }
                        this.buttonbar.remove();
//                        this.ftsmenu.remove();
                        this.editing   = false;
                       },

                      "fnRowSelected": function ( nodes ) {
                         if (nodes.length ==0 || this.editing ) { return; }
                         $('#package').val(nodes[0].id);
                         var TableTool = this;
                             TableTool.buttonbar  = $('<div id="buttonbar" style="z-index:9999; margin:0px;  position:relative; float:right;" />');
                        currentRow = nodes[0].id;
                              var editfield   = $(nodes[0]);//.get(1);
                              var classes = editfield.prop('class');

                              var button;

                              button = $('<button title="Edit this translation." />').button({
                                        icons: {
                                          primary: "ui-icon-wrench"
                                        },
                                        disabled: ! editfield.hasClass('canedit'),
                                        text: false
                                      });//.click(function () { alert('edit');} );
                              button.height(16).width(16);
                              TableTool.buttonbar.append(button);

                              button = $('<button  title="Download this translation." />').button({
                                        icons: {
                                          primary: "ui-icon-arrowreturnthick-1-s"
                                        },
                                        disabled:! editfield.hasClass('candownload'),
                                        text: false
                                      }).click(function (e) {
                                        if (!confirm('You are about to download and preview a translation from the language server\n'+
                                                    'The contend will be previewed after downloading.\n'+
                                                    'When you want to have this translation hit [Save tramslation].\n'+
                                                    'Do you want to continue?')) {
                                          e.preventDefault();
                                          return false;
                                        }
                                        $('#myaction').val('download');
                                      });


                              button.height(16).width(16);
                              TableTool.buttonbar.append(button);

                              button = $('<button  type=button title="Upload this translation." />').button({
                                        icons: {
                                          primary: "ui-icon-arrowreturnthick-1-n"
                                        },
                                        disabled: !editfield.hasClass('canupload'),

                                        text: false
                                      }).click(function () {
                                                  doaction = 'upload';
                                                  $( "#upload-form" ).dialog( "open" );
                                                  return false;
                                      } );
                              button.height(16).width(16);
                              TableTool.buttonbar.append(button);


                                $('#table2 tbody #'+currentRow+' td:last').append(TableTool.buttonbar);

                             TableTool.editing = true;

                         $.post("view_utils.php?x=setpackage",
                            { action: "setlang", 'package': nodes[0].id },
                            function(data){


                            ;},
                            "html");

                    }
                 }
        });
        $('div.lang_toolbar').addClass('admin_list_title').css('float','left').html('Language Editor - Language selection');
        $('div.lang_addbutton').css('float','right').html('<?php echo _esc($view->show_button('button',"add",3,array('id'=>'new_language')),false);?>');

    $( "#register" )
      .button().click(function(){
            $( "#register-form" ).dialog( "open" );
            return false;
        });
        $('#new_language').click(function(){
            $( "#newlang-form" ).dialog( "open" );
            return false;
        });
      function checkLength( o, n, min, max ) {
      if ( o.val().length > max || o.val().length < min ) {
        o.addClass( "ui-state-error" );
        updateTips( "Length of " + n + " must be between " +
          min + " and " + max + "." );
        return false;
      } else {
        return true;
      }
    }

    function checkRegexp( o, regexp, n ) {
      if ( !( regexp.test( o.val() ) ) ) {
        o.addClass( "ui-state-error" );
        updateTips( n );
        return false;
      } else {
        return true;
      }
    }
        function updateTips( t ) {
      tips
        .text( t )
        .addClass( "ui-state-highlight" );
      setTimeout(function() {
        tips.removeClass( "ui-state-highlight", 1500 );
      }, 500 );
    }
    var myname = $( "#myname" ),
      mylang = $( "#mylang" ),
      mycreator = $( "#mycreator" ),
      myowner = $('#myowner'),
      myemail = $('#myemail'),
      myusername = $('#myusername'),
      allFields = $( [] ).add( myname ).add( mylang ).add( mycreator ),
      allRegFields = $( [] ).add( myowner ).add( myemail ).add( myusername );
      var tips = $( ".validateTips" );
  $( "#newlang-form" ).dialog({
      autoOpen: false,
      height: 310,
      width: 400,
      modal: true,
      bgiframe: false,
      position:['middle',50],
      buttons: {
        "Create": function() {
          var bValid = true;
          allFields.removeClass( "ui-state-error" );

          bValid = bValid && checkLength( mylang, "Language Code", 2, 2 );
          bValid = bValid && checkRegexp( mylang, /^([a-zA-Z])+$/i, "The Language code needs to have only of consist of a-z." );
          bValid = bValid && checkLength( myname, "Language Name", 3, 26 );
          bValid = bValid && checkLength( mycreator, "The Creator", 5, 26 );

          // From jquery.validate.js (by joern), contributed by Scott Gonzalez: http://projects.scottsplayground.com/email_address_validation/
//          bValid = bValid && checkRegexp( email, /^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i, "eg. ui@jquery.com" );
//          bValid = bValid && checkRegexp( password, /^([0-9a-zA-Z])+$/, "Password field only allow : a-z 0-9" );

          if ( bValid ) {
               $.post("view_utils.php", { action: "new_language", lang: mylang.val(), name: myname.val(), creator: mycreator.val() }, function(data){
                if (data== 'done') {
                  location.reload();
                } else alert(data);}, "text");

          }
        },
        Cancel: function() {
          $( this ).dialog( "close" );
        }
      },
      close: function() {
        allFields.val( "" ).removeClass( "ui-state-error" );
      }
    });
  $( "#register-form" ).dialog({
     autoOpen: false,
      height: 410,
      width: 600,
      modal: true,
      bgiframe: false,
      position:['middle',50],
      buttons: {
        "Register": function() {
          var bValid = true;
          var bValid = true;
          allRegFields.removeClass( "ui-state-error" );

          bValid = bValid && checkLength( mylang, "Marchant name", 5, 54 );
    //      bValid = bValid && checkRegexp( mylang, /^([a-zA-Z])+$/i, "The Language code needs to have only of consist of a-z." );
          bValid = bValid && checkLength( myusername, "User Name", 3, 54 );
          bValid = bValid && checkLength( myemail, "Your email address", 5, 26 );

          if ( bValid ) {
               $('#filelist').append('<h1>Please wait files will be '+doaction+'ed now.</h1>');
               $.post("view_utils.php",
                      {  action: "register", myowner: myowner.val(), myusername: myusername.val(), myemail: myemail.val() },
                      function(data){ alert(data); $( "#upload-form" ).dialog( "close" ); mygrid1.fnDraw(true);  },
                      "html");

          }
        },
        Cancel: function() {
          $( this ).dialog( "close" );
        }
      },
      open: function (){
               $( "#upload-form" ).dialog( "option", 'title', doaction+" files dialog." );
               $.post("view_utils.php", { action: doaction }, function(data){
                 $('#filelist').html(data);
               }, "html");
      }
    });
  $( "#upload-form" ).dialog({
     autoOpen: false,
      height: 410,
      width: 600,
      modal: true,
      bgiframe: false,
      position:['middle',50],
      buttons: {
        "Ok": function() {
          var bValid = true;
          if ( bValid ) {
               $('#filelist').html('<h1 style="color:green">Please wait files will be '+doaction+'ed now.</h1>');
               $.post("view_utils.php",
                      { action: doaction+"_files", files: $('#uploadform').serializeArray() },
                      function(data){ alert(data); mygrid2.fnDraw(true); $( "#upload-form" ).dialog( "close" ); },
                      "html");

          }
        },
        Cancel: function() {
          $( this ).dialog( "close" );
        }
      },
      open: function (){
               $( "#upload-form" ).dialog( "option", 'title', doaction+" files dialog." );
               $.post("view_utils.php", { action: doaction }, function(data){
                 $('#filelist').html(data);
               }, "html");
      }
    });

         });
		</script>

	<style>
	#toolbar {
  padding: 10px 4px;
  }
</style>
<?php
  //$view->
?>
  <form method='post' id=selectlang>
  <input type="hidden" id='myaction' name='action' value='editlang'/>
  <input type=hidden name='lang' id='lang' value='<?php echo $_SESSION['langedit']['lang'];?>'/>
  <input type=hidden name='version' id='version' value='<?php echo $_SESSION['langedit']['version'];?>'/>
  <input type=hidden name='package' id='package' value='<?php echo $_SESSION['langedit']['package'];?>'/>

  <table id="table1">
    <thead>
    <tr class='admin_list_header'>
      <th class='' id='language_key' style='text-align:left'>Code</th>
      <th class='' id='language_key' style='text-align:left'>Language Name</th>
      <th class='' id='language_key' style='text-align:left'>Available Version</th>
     </tr>
  </thead>
  <tbody></tbody>
   </table>
 <table id="table2">
    <thead>
    <tr class='admin_list_header'>
      <th class='' id='language_key' style='text-align:left'>Package</th>
      <th class='' id='language_key' style='text-align:left'>Server</th>
      <th class='' id='language_key' style='text-align:left'>Local</th>
 <!--     <th class='' id='language_key' style='text-align:left'>Customized</th> -->
     </tr>
  </thead>
  <tbody></tbody>
   </table>
	<div id="toolbarz" class="ui-widget-header ui-corner-all"  style="display: inline-block; text-align:right; width:<?php echo $view->width; ?>">
      <button type=button id="register">Register/login</button>
  </div>
  </form>

  <div id="newlang-form" title="Create new language">
    <form  id=uploadform>
    <fieldset>
      <label for="lang">Language Code</label>
      <input type="text" name="lang" id="mylang" class="text ui-widget-content ui-corner-all" />
      <label for="name">Language Name</label>
      <input type="text" name="name" id="myname" value="" class="text ui-widget-content ui-corner-all" />
      <label for="creator">Creator</label>
      <input type="text" name="creator" id="mycreator" value="<?php echo $_SHOP->organizer_name; ?>" class="text ui-widget-content ui-corner-all" />
    </fieldset>
    <span class="validateTips">All form fields are required.</span>
    </form>
  </div>

  <div id="register-form" title="Register for uploading">
    <form>
    <fieldset>
      <label for="lang">Marchent name</label>
      <input type="text" name="owner" id="myowner" value='<?php echo $_SHOP->organizer_name; ?>' class="text ui-widget-content ui-corner-all" />
      <label for="name">User Name</label>
      <input type="text" name="username" id="myusername" value="<?php echo $_SHOP->admin->admin_realname; ?>" class="text ui-widget-content ui-corner-all" />
      <label for="creator">email address</label>
      <input type="text" name="email" id="myemail" value="<?php echo $_SHOP->organizer_email; ?>" class="text ui-widget-content ui-corner-all" />
    </fieldset>
    <span class="validateTips">All form fields are required.</span>
    </form>
  </div>


  <div id="upload-form" style='text-align:left;' title="Upload changed language files.">
  <form id=uploadform>
  <fieldset>
    <span id=filelist></span>
  </fieldset>
  <span class="validateTips"></span>
  </form>
</div>
    <?php
  }

  function _tablelangedit($view){
    global $_SHOP;
    ?>
 		<script type="text/javascript">
       $(document).ready(function() {
          var currentRow = -1;
          var mygrid1 = $("#table3").dataTable({
             //   bProcessing: true,
                bServerSide: true,
                sAjaxSource: "view_utils.php?x=grid",
                sServerMethod: "POST",
                fnServerParams: function ( aoData ) {
                  aoData.push( { "name":  'action', "value": "grid" } );
                },
                fnGridComplete:  function(aoData){
                  if (currentRow != -1) {
                    var oTT1 = TableTools.fnGetInstance( 'table3' );
                    oTT1.fnSelect( $('#'+currentRow) );
                  }
                },

                sScrollY: '400px',
                bJQueryUI: true,
             //  sDom: 'T<l<t>p>',
                sDom: 'Tlfiptr',
                bSort: false,
                bAutoWidth: true,
                bPaginate: false,
                bScrollCollapse: false,
             //   bScrollInfinite: true,
            //    iScrollLoadGap : 3,

                aoColumns : [ { "mData": "data" }],
                    fnGridComplete:  function(aoData){
                      var oTT1 = TableTools.fnGetInstance( 'table3' );
                      if (oTT1.editing ) {
                        oTT1.buttonbar.remove();
//                        this.ftsmenu.remove();
                        oTT1.editing   = false;
                      }
/*                      if ($('#package').val()) {
                        oTT1.fnSelect( $('#table2 tbody #'+$('#package').val() ));
                      } else {
                        oTT1.fnSelect( $('#table2 tbody tr')[0] );
                      }*/
                    },

                oTableTools: {
                  sRowSelect: "single",
                  aButtons: [],
                  fnRowDeselected: function ( nodes ) {
                      if (nodes.length !=0) {
                         /* prevent calling reset twice when blurring */
                          if (this.editing) {
                              /* before reset hook, if it returns false abort reseting */
 //                             $('#'+nodes[0].id+' table th:first').detach(this.buttonbar);
                              this.buttonbar.detach();
                              var editfield = $('#'+nodes[0].id+'_edit');//.get(1);
                              if (this.revert != $('#'+nodes[0].id+'_input').val()) {
                                this.form.submit();
                              }
                              $(editfield).text(this.revert);
                              this.editing   = false;
                          }
                      }
                  },
                  fnRowSelected: function ( nodes ) {
                    var TableTool = this;

                    if (nodes.length ==0) { return; }

                    currentRow = nodes[0].id;

                    var editfield = $('#'+nodes[0].id+'_edit');//.get(1);

                    var form = $('<form />');
                     this.revert  = inputvalue   = $(editfield).text();

                      this.buttonbar = $('<div id="buttonbar" style="z-index:9999; margin:0px; position:relative; float:right;" />');
                      var button, buttonA, buttonB;

                      button = $('<button title="Lowercase the keyvalue." />').button({
                              icons: {
                                primary: "ui-icon-arrowthickstop-1-s"
                              },
                              disabled: ! $(nodes[0]).hasClass('case'),
                              text: false
                            }).click(function () {
                             var ajaxoptions = {
                                  type    : 'POST',
                                  data    : { action: "downcase", key: nodes[0].id, value: intput.val() },
                                  dataType: 'html',
                                  url     : 'view_utils.php?x=downcase',
                                  success : function(result, status) {
                                      if (result !== 'done') { alert(result); } else {
                                        mygrid1.fnDeleteRow(nodes[0].id);
                                        currentRow = nodes[0].id.toLowerCase();
                                      }
                                        //
                                  }
                              };
                              $.ajax(ajaxoptions);
                            } );
                        button.height('16px').width('16px');
                        this.buttonbar.append(button);

                        button = $('<button title="Copy translation." />').button({
                            icons: {
                              primary: "ui-icon-copy"
                            },
                            disabled : ! $(nodes[0]).hasClass('missing'),
                            text: false
                          }).click(function () {
                            $('#'+nodes[0].id+'_input').val($('#'+nodes[0].id+'_orgin').html())
                          } );
                      button.height('16px').width('16px');

                      this.buttonbar.append(button);
                      buttonA = $('<button  title="Remove this translation." />').button({
                              icons: {
                                primary: "ui-icon-circle-minus"
                              },
                              text: false
                            }).click(function () {
                              /* defaults for ajaxoptions */
                              var ajaxoptions = {
                                  type    : 'POST',
                                  data    : { action: "remove", key: nodes[0].id },
                                  dataType: 'html',
                                  url     : 'view_utils.php?x=remove',
                                  success : function(result, status) {
                                      if (result !== 'done') { alert(result); } else
                                         mygrid1.fnDeleteRow(nodes[0].id);
                                  }
                              };
                              $.ajax(ajaxoptions);
                            } );
                        buttonA.height('16px').width('16px');
                        this.buttonbar.append(buttonA);

                      button = $('<button  title="Restore translation." />').button({
                            icons: { primary: "ui-icon-circle-close" },
                            text: false
                          }).click(function () {
                              $('#'+nodes[0].id+' textarea').val(inputvalue);
                          } );
                      button.height('16px').width('16px');
                      this.buttonbar.append(button);
                      button = $('<button  title="Save translation." />').button({
                            icons: {
                              primary: "ui-icon-circle-check"
                            },
                            text: false
                          }).click(function () {
                           TableTool.form.submit();
                          } );
                      button.height('16px').width('16px');
                      this.buttonbar.append(button);

                    $('#'+nodes[0].id+'_key').prepend(this.buttonbar);

             //       return true;
                    TableTool.editing    = true;
                     $(editfield).html('');

                    /* create the form object */
                    form.attr('style', editfield.attr('style'));

                    TableTool.form = form;
                    editfield.append(form);

//                    form.attr('class',
                    var intput = $('<textarea />');
                    intput.height('38px')
                          .width('840px')
                          .attr('id', nodes[0].id+'_input')
                          .attr('style', "min-width:840px;max-width:840px;min-height:38px;margin:0;width:840px;height:34px;")
                          .val(inputvalue)
                          .keydown(function(e) {
                                if (e.keyCode == 27) {
                                    e.preventDefault();
                                    intput.val(inputvalue);
                                }
                          });

                    form.append(intput);
                    intput.focus();

                    form.submit(function(e) {

                      /* do no submit */
                      e.preventDefault();

                      TableTool.revert = intput.val();

                      var ajaxoptions = {
                          type    : 'POST',
                          data    : {action: 'edit', key:nodes[0].id, value: intput.val() },
                          dataType: 'html',
                          url     : 'view_utils.php?x=edit',
                          success : function(result, status) {
                              if (result !== 'done') { alert(result); }
                              this.editing = false;
                          }
                      };
                      $.ajax(ajaxoptions);
                      return false;
                    });

                  }
           }
              });
              $('#goback').button({
          			text: true,
          			icons: {
          				primary: "ui-icon-triangle-1-w"
          				}
          			});

              $( "#sved4x" ).button({
          			text: true,
          			icons: {
          				primary: "ui-icon-transferthick-e-w"
          			}
          		});

     	  	$( "#copymissing" ).button({
      			icons: {
      				primary: "ui-icon-check"
      			}
      		});
          jQuery("#copymissing").click( function() {
                      /* defaults for ajaxoptions */
                      var ajaxoptions = {
                          type    : 'POST',
                          data    : {action:'copymissing'},
                          dataType: 'html',
                          url     : 'view_utils.php?x=edit',
                          success : function(result, status) {
                                       if (result !== 'done') { alert(result); }
                                       else  mygrid1.fnDraw(true);
                                    }

                      };
                      $.ajax(ajaxoptions);
              return false;

         	});
         $( "#sved4" ).button({
      			icons: {
      				primary: "ui-icon-disk"
      			}
      		});
       });
		</script>

	<style>
	#toolbar {
  padding: 10px 4px;
  }
</style>

  <table id="table3">
    <thead>
    <tr class='admin_list_header'>
      <th class='' id='language_key' style='text-align:left'>Translations</th>
     </tr>
  </thead>
  <tbody></tbody>
   </table>
   <form method='post'>
 <div id="toolbarz" class="ui-widget-header ui-corner-all" style="width:<?php echo $view->width; ?>">

  <div style='display:blockinline; text-align:right; '>
  <a style='float:left;' id='goback' href='view_utils.php'>List</a>
  <input name='action' type='hidden' value='update_2'/>
  <button type="button" id='copymissing'>Copy All Missing</button>
  <input type="Submit" id="sved4x" value="Save changes" />
  </div></div>
  </form>
    <?php
  }

  function fillxml ($key, $field1, $field2) {
    echo "<row id='{$key}'>";
    echo "<cell>{$key}</cell>";
    echo "<cell><![CDATA[{$field1}]]></cell>";
    echo "<cell><![CDATA[{$field2}]]></cell>";
    echo "</row>";
  }

  function findinside( $string) {
    // preg_match_all('/define\(["\']([a-zA-Z0-9_]+)["\'],[ ]*(.*?)\);/si',  $string, $m); //.'/i'
    preg_match_all('/|^[\s]*define[\s]*\([\s]*["\'](.+)["\'][\s]*,[\s]*["\']([\s\S]*)["\']\);|/mUu', $string, $m);
//    print_r($langtemp);
    if (count($m[1])>0) {
      $rows = array_combine( $m[1],$m[2]);
      unset($rows['']);
      unset($rows[0]);
//      ksort($rows, SORT_STRING);
      return $rows;

    } else
      return array();
  }
  function lowercmp($a, $b)
  {
    return strcmp(strtolower($a), strtolower($b));
  }
}
?>