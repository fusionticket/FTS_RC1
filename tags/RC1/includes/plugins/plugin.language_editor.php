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

  function doUtilitiesView_Items($items){
      $items[$this->plugin_acl] ='language_editor|admin';
      return $items;
    }

  function doUtilitiesView_Draw($id, $view){
  global $_SHOP;
  // this section works the same way as the draw() function insite the views it self.
    if ($id==$this->plugin_acl) {
      if ($_POST['action'] == 'editlang' && is($_POST['lang'],false) && is($_POST['package'],false)) {
         $this->_tablelangedit($view,$_POST['lang'],$_POST['package']);
      } else
        $this->_tableLangages($view);
    } else return false;
  }

  function doUtilitiesView_Execute($id, $view){
    global $_SHOP;
    // this section works the same way as the draw() function insite the views it self.

    if ($id==$this->plugin_acl) {
    //  die('here '.$id.' '.$this->plugin_acl);
      // do your tasks here.
      $lang = !empty($_POST['lang'])?$_POST['lang']: $_SESSION['lang'];
      $editfile = dirname(dirname(__FILE__)).DS.'lang'.DS."site_{$lang}.inc";
      $deffile = dirname(dirname(__FILE__)).DS.'lang'.DS."site_en.inc";

      if ($_POST['action'] || is($_POST['lang'],false)) {
        If (!isset($_SESSION['diff1']) or $_SESSION['lang']<>$_POST['lang'] ) {
          $string1 = file_get_contents($deffile);
          $diff1 = $this->findinside($string1);
          if (file_exists( $editfile)) {
            $string2 = file_get_contents($editfile);
            $diff2 = $this->findinside($string2);
          } else {
            $diff2 = array();
          }

          $_SESSION['diff1'] = $diff1;
          $_SESSION['diff2'] = $diff2;

      //    var_dump($diff1,$diff2);
          if ($_POST['lang']) {
            $_SESSION['lang']  = $_POST['lang'];
          }
        }
      }
      $diff1= $_SESSION['diff1'];
      $diff2= $_SESSION['diff2'];

      if ($_POST['action']=='new_language') {
        $lang = strtolower($_POST['lang']);
        if (strlen($lang)<>2) {
          die('Language code needs to be 2 characters');

        } elseif (!is_string($lang)) {
          die('Language code needs to be 2 characters');
        } elseif (file_exists()){
          die('Language code already exist.');
        } else {
          $string2 = "<"."?php\n";
          $string2 .= "// defines added at: ".date('c')."\n\n";
          $string2 .= "?>";
          file_put_contents($editfile,$string2, FILE_TEXT );
        }
        echo ("done");
        return true;
      } elseif ($_POST['action']=='edit') {
        if (!is_writable($editfile)) {
          die('This file is not writable. : '.$editfile);
        } else {
          $_SESSION['diff2'][$_POST['key']] = $_POST['value'];

          $string2 = "<"."?php\n";
          $string2 .= "// defines added at: ".date('c')."\n";
          foreach ($_SESSION['diff2'] as $key =>$value) {
            $umlautArray = Array("/ä/","/ö/","/ü/","/Ä/","/Ö/","/Ü/","/ß/");
            $replaceArray = Array("&auml;","&ouml;","&uuml;","&Auml;","&Ouml;","&Uuml;","&szlig;");
            $value = preg_replace($umlautArray , $replaceArray , $value);
            $string2 .= "define('$key', '".addslashes($value)."');\n";
          }
          $string2 .= "?>";
          file_put_contents($editfile,$string2, FILE_TEXT );
        }
        echo $_POST['value'];
        return true;

      }elseif ($_POST['action']=='update_2') {
        if (count($diff1)===0) {
          die('noting to update');
        } elseif (!is_writable($editfile)) {
          die('This file is not writable.');
        } else {
          $string2 = "<"."?php\n";
          $string2 .= "'.DS.'/ defines added at: ".date('c')."\n";
          foreach ($diff2 as $key =>$value) {
            $string2 .= "define('$key', '".addslashes($value)."');\n";
          }
          $diff= array_diff_key($diff1, $diff2);
          foreach ($diff as $key =>$value) {
            $string2 .= "define('$key', '".addslashes($value)."');\n";
          }
          $string2 .= "?>";
          $_SESSION['diff2'] = array_merge($diff2, $diff );
          file_put_contents($editfile,$string2, FILE_TEXT );
        }
        echo ("done");
        return true;

      } elseif ($_POST['action']=='grid')  {
        $responce = array();
        $responce['userdata'] = array();
        $responce['aaData'] = array();
        $i=0;

        foreach ($diff1 as $key =>$value) {
          $responce['aaData'][$i]=array('data'=>'<table style="width:100%" border=0  cellspacing=1 cellpadding=2><tr><th colspan=2 style="text-align:left;">'.$key.'</th></tr><tr><td width="30"><i><b>EN:</b></i></td><td>'.htmlentities($value).'</td></tr><tr><td><i><b>'.strtoupper($_SESSION['lang']).':</b></i></td><td class="editable">'.htmlentities($diff2[$key]).'</td></tr></table>');
          $responce['aaData'][$i]['DT_RowId']=$key;
          $i++;
        }
        foreach ($diff2 as $key =>$value) {
          if(!array_key_exists($key, $diff1 )){
            $responce['aaData'][$i]=array('data'=>'<table style="width:100%" border=0  cellspacing=0 cellpadding=0><tr><th colspan=2 style="text-align:left;">'.$key.'</th></tr><tr><td><i><b>EN:</b></i></td><td><i>'.con('langedit_missing').'</td></tr><tr><td><i><b>'.strtoupper($_SESSION['lang']).':</b></i>'.htmlentities($value).'</td></tr></table>');
            $responce['aaData'][$i]['DT_RowId']=$key;
            $i++;
          }
        }
        $responce["sEcho"] = $_POST['sEcho'];
        $responce[  "iTotalRecords"] = $i;
        $responce[  "iTotalDisplayRecords"] = $i;
        echo json_encode($responce);
        return true;
      }elseif ($_POST['action']=='languages')  {
        $responce = array();
        $responce['userdata'] = array();
        $responce['aaData'] = array();
        $dir = dirname(dirname(__FILE__)).DS."lang";
        //  echo "<option value=''>Select</option>\n";
        $resp = array('en'=>array());
        if ($handle = opendir($dir)) {
          while (false !== ($file = readdir($handle))) {
            if ($file != "." && $file != ".." && !is_dir($dir.$file) && preg_match("/^site_(.*?\w+).inc\z/", $file, $matches)){
              $resp[$matches[1]]['DT_RowId']=$matches[1];
              $resp[$matches[1]]['language'] = '['.$matches[1].'] '.$_SHOP->langs_names[$matches[1]];
              $resp[$matches[1]]['origin']='local';
              $resp[$matches[1]]['complete']='100%';
              $resp[$matches[1]]['customized']='100%';
              $resp[$matches[1]]['actions']='';
            }
          }
          closedir($handle);
        }
        $langs = $this->_send('language/getlanguage.xml',array('ftrevision'=>$this->getRevision()));
    //    var_dump($langs);
        foreach ($langs as $row) {
          if (!isset($resp[$row['language']])) {
            $resp[$row['language']]['DT_RowId']=$row['language'];
            $resp[$row['language']]['language'] = '['.$row['language'].'] '.$row['value'];
            $resp[$row['language']]['origin']='available';
            $resp[$row['language']]['complete']='100%';
            $resp[$row['language']]['customized']='100%';
            $resp[$row['language']]['actions']='';
          }

        }
    //    var_dump($resp);
        foreach ($resp as $value ) {
          $responce['aaData'][] = $value;
        }

        $responce["sEcho"] = $_POST['sEcho'];
        $responce[  "iTotalRecords"] = count($responce['aaData']);
        $responce[  "iTotalDisplayRecords"] = count($responce['aaData']);
        echo json_encode($responce);
        return true;
      }elseif ($_POST['action']=='packages')  {
        $content = array();
        $responce = array();
        $responce['userdata'] = array();
        $responce['aaData'] = array();
        $responce["sEcho"] = $_POST['sEcho'];
        $responce[  "iTotalRecords"] = 0;
        $responce[  "iTotalDisplayRecords"] = 0;
        if (empty($_POST['lang'])) {
          echo json_encode($responce);
          return true;
        }
        $dir = dirname(dirname(__FILE__)).DS."lang";
        //  echo "<option value=''>Select</option>\n";
        $resp = array();
        $resp['site']['DT_RowId']='site';
        $resp['site']['language'] = con('langedit_mainfile');
        $resp['site']['origin']='';
        $resp['site']['complete']='';
        $resp['site']['customized']='';
        $resp['site']['actions']='';

        $plugs = plugin::loadAll(false);
        foreach ($plugs as $key => $row) {
            $key = get_class( $row->plug('xx'));
            $resp[$key]['DT_RowId']=$key;
            $resp[$key]['language'] = $row->plugin_info;
            $resp[$key]['origin']='';
            $resp[$key]['complete']='';
            $resp[$key]['customized']='';
            $resp[$key]['actions']='';
        }
        $query = 'select DISTINCT handling_payment from `Handling`';
        if ($res = ShopDB::query($query)) {
          while ($row = shopDB::fetch_array($res)) {
            if ($row['handling_payment']) {
              $key = 'eph_'. $row['handling_payment'];
              $resp[$key]['DT_RowId']=$key;
              $resp[$key]['language'] =  $row['handling_payment'];
              $resp[$key]['origin']='';
              $resp[$key]['complete']='';
              $resp[$key]['customized']='';
              $resp[$key]['actions']='';
            }
          }
        }

        $langs = $this->_send('language/getpackages.xml',array('ftrevision'=>$this->getRevision(), 'ftlang'=>$_POST['lang'], 'ftpackages'=>array_keys($resp) ));

        $lang = $_POST['lang'];
        foreach ($resp as $key => $value ) {
          $editfile = dirname(dirname(__FILE__)).DS.'lang'.DS.$key."_{$lang}.inc";
          $deffile = dirname(dirname(__FILE__)).DS.'lang'.DS.$key."_en.inc";
          $diff1 =  $diff2 = array();
          if ($lang=='en' && $langs[$key]) {
            $diff1 =  $langs[$key]['master'];
          }elseif (file_exists($deffile)) {
            $string1 = file_get_contents($deffile);
            $diff1 = $this->findinside($string1);
            $string1 = '';
          }
          if (file_exists($editfile)) {
            $value['origin'] = 'local';
            $string1 = file_get_contents($editfile);
            $diff2 = $this->findinside($string1);
            $string1 = '';
          } elseif(isset($langs[$key])) {
            $value['origin'] = 'availeble';
            $diff2 = ($langs[$key]['slave'])?$langs[$key]['slave']:$langs[$key]['master'];
          }
          $diff = array_intersect($diff1, $diff2);
          if (count($diff1)>0 && $value['origin']) {
            $value['complete'] = round((count($diff)/count($diff1))*100).'%';
          } else {
            $value['complete'] = 'na.';

          }

          if (count(array_diff( $diff2,$diff))>0) {
        //    var_dump(count(array_diff($diff2,$diff)));
            $value['complete'] = $value['complete'].' &plus;';
          }
          if (!$value['origin']) {
            $value['origin'] ='missing';
          }
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
    //    var_dump($data);

    $rsc = new RestServiceClient('http://cpanel.fusionticket.org/'.$action);
    //    echo $action, '= ';
    try{
      $rsc->setArray($data);
      $rsc->ftUser  = base64_encode($_SHOP->plugin_languser.'||'.$_SHOP->plugin_langemail.'||'.date('c'));
      $rsc->checksom = sha1($rsc->json .$rsc->josUser);
      $rsc->excuteRequest();
      $value  = $rsc->getResponse();
      $return = json_decode($value, true);
      //      var_dump('return:', $value);
      if (is_array($return) && $return['error']) {
        $this->errors =$return['reason'];
        return false;
      }
      return is($return, $value);
    }catch(Exception $e){
      $this->errors =   var_export($e->getMessage(), true);
      return false;// " - Could not check for new version.";
    }

  }

  function _tableLangages($view){
    global $_SHOP;
    ?>
 		<script type="text/javascript">
       $(document).ready(function() {
          var lastsel;
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
                                  {'sWidth':'135px', "mData": "origin" },
                                  {'sWidth':'135px', "mData": "complete" },
                                  {'sWidth':'135px', "mData": "customized" },
                                  {'sWidth':'135px', "mData": "actions" }],
                    fnGridComplete:  function(aoData){
                      var oTT1 = TableTools.fnGetInstance( 'table1' );
                      oTT1.fnSelect( $('#table1 tbody tr')[0] );
                    },
                    oTableTools: {
                      "sRowSelect": "single",
                      "aButtons": [],
                       "fnRowSelected": function ( nodes ) {
                         $('#lang').val(nodes[0].id);
                         mygrid2.fnDraw(true);
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
                      aoData.push( { "name":  'lang', "value": $('#lang').val()} );
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
                                  {'sWidth':'135px', "mData": "origin" },
                                  {'sWidth':'135px', "mData": "complete" },
                                  {'sWidth':'135px', "mData": "customized" },
                                  {'sWidth':'135px', "mData": "actions" }],
                    fnGridComplete:  function(aoData){
                    },
                    oTableTools: {
                      "sRowSelect": "single",
                      "aButtons": [],
                      "fnRowSelected": function ( nodes ) {
                         $('#package').val(nodes[0].id);
                    }
                 }
        });

      	$( "#new_language" ).button({
      			text: true,
      			icons: {
      				primary: "ui-icon-plusthick"
      			}
    		});
          $('#new_language').click(function(){
            var reply = prompt("Please enter the 2 token code of the new language?", "xx");
            var name  = prompt("Please enter the full name the new language?", "");
            if (name!=null && name!="") {
              $.post("view_utils.php", { action: "new_language", lang: reply, name: name }, function(data){
                if (data== 'done') {
                  location.reload();
                } else alert(data);}, "text");
              }
          });

   	  	$( "#sved4" ).button({
    			text: true,
    			icons: {
    				primary: "ui-icon-disk"
    			}
    		});
        jQuery("#selectlang").submit( function() {
        	if( $('#package').val()  != '' && $('#lang').val() !='' ) {return true;}
        	else {
        	  alert("Please, Select language amd package first.");
        	  return false;
        	}
        });
      });
		</script>

	<style>
	#toolbar {
  padding: 10px 4px;
  }
</style>
  <form method='post' id=selectlang>
  <input type=hidden name='action' value='editlang'/>
  <input type=hidden name='lang' id='lang' value=''/>
  <input type=hidden name='package' id='package' value=''/>

  <table id="table1">
    <thead>
    <tr class='admin_list_header'>
      <th class='' id='language_key' style='text-align:left'>Langague</th>
      <th class='' id='language_key' style='text-align:left'>Orgin</th>
      <th class='' id='language_key' style='text-align:left'>Completed</th>
      <th class='' id='language_key' style='text-align:left'>Customized</th>
      <th class='' id='language_key' style='text-align:left'></th>
     </tr>
  </thead>
  <tbody></tbody>
   </table>
 <table id="table2">
    <thead>
    <tr class='admin_list_header'>
      <th class='' id='language_key' style='text-align:left'>Package</th>
      <th class='' id='language_key' style='text-align:left'>Orgin</th>
      <th class='' id='language_key' style='text-align:left'>Completed</th>
      <th class='' id='language_key' style='text-align:left'>Customized</th>
      <th class='' id='language_key' style='text-align:left'></th>
     </tr>
  </thead>
  <tbody></tbody>
   </table>
	<div id="toolbarz" class="ui-widget-header ui-corner-all"  style="width:<?php echo $view->width; ?>">
    <button id='new_language'>new language</button>
    <button type=submit id="sved4">Edit language</button>
  </div>
  </form>
    <?php
  }

  function _tablelangedit($view, $lang, $package){
    global $_SHOP;
    ?>
 		<script type="text/javascript">
       $(document).ready(function() {
          var mycombo = $("#combo");
          var lang = mycombo.val();
          var lastsel;
          var mygrid1 = $("#table1").dataTable({
 //   bProcessing: true,
    bServerSide: true,
    sAjaxSource: "view_utils.php?x=grid",
    sServerMethod: "POST",
    fnServerParams: function ( aoData ) {
      aoData.push( { "name":  'action', "value": "grid" } );
      aoData.push( { "name":  'lang', "value":  lang});
    },
    sScrollY: '400px',
    bJQueryUI: true,
    sDom: '<l<t>p>',
    bSort: false,
    bAutoWidth: true,
    oLanguage: {
      sEmptyTable: 'No data available in table',
      sLoadingRecords : 'Loading...',
      sZeroRecords:  'No matching records found.'
    },
    bPaginate: false,
    bScrollCollapse: false,
    aoColumns : [ {'sWidth':'135px', "mData": "data" }],
                    oTableTools: {
                      "sRowSelect": "single",
                      "aButtons": [],
                      "fnRowSelected": function ( nodes ) {
                   ;//      $('#package').val(nodes[0].id);
                    },
    fnGridComplete:  function(aoData){
//      $('#cart_table td').addClass('payment_form');
    },
           fnDrawCallback: function () {
            $('#table1 .editable').editable( 'view_utils.php', {
                type      : 'textarea',
                tooltip   : 'Click to edit...',
                select : true,
                cssclass : "editable" ,
                "height": "34px",
                submit  : 'OK',
//                style   : 'display: inline',
          //      onblur : submit,
                submitdata : function(value, settings) {
                  return {key: lastsel, action:'edit'};
                },
                "callback": function( sValue, y ) {
                    /* Redraw the table from the new data on the server */
//                    mygrid1.fnDraw();
                }

            } );
        }
  });

         $( "#update_2" ).button({
      			text: true,
      			icons: {
      				primary: "ui-icon-transferthick-e-w"
      			}
      		});

      		$('#update_2').click(function(){
             $.post("view_utils.php", { action: "update_2", lang: lang }, function(data){
                if (data== 'done') {
                  mygrid1.fnDraw(true);
                } else alert(data);}, "text");
      		});

     	  	$( "#sved4" ).button({
      			text: true,
      			icons: {
      				primary: "ui-icon-disk"
      			}
      		});
          jQuery("#sved4").click( function() {
          	if( lastsel != null ) {;}
          	else alert("Please Select Row");
          });
       });
		</script>

	<style>
	#toolbar {
  padding: 10px 4px;
  }
</style>
  <table id="table1">
    <thead>
    <tr class='admin_list_header'>
      <th class='' id='language_key' style='text-align:left'>Translations</th>
     </tr>
  </thead>
  <tbody></tbody>
   </table>
  <div align='right' id="toolbarz" class="ui-widget-header ui-corner-all" style="width:<?php echo $view->width; ?>">
  <input type="BUTTON" id="sved4" value="Edit row" />
  </div>
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
    preg_match_all('|^[\s]*define\(["\'](.*)["\'],[\s]*["\'](.*)["\']\);|imU', $string, $m);
//    print_r($langtemp);
    if (count($m[1])>0) {
      return array_combine( $m[1],$m[2]);

    } else
      return array();
  }


}
?>