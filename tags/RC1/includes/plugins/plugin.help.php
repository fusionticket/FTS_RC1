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
 * @version $Id: _plugin.solution_manager.php 1785 2012-05-12 07:10:13Z nielsNL $
 * @copyright 2010
 */
class plugin_help extends baseplugin {
	public $plugin_info		  = 'Help system';
	/**
	 * description - A full description of your plugin.
	 */
	public $plugin_description	= 'Help system enables you to add a help menuitem in the menu at the left side.';
	/**
	 * version - Your plugin's version string. Required value.
	 */
	public $plugin_myversion		= '1.0.0';
	/**
	 * requires - An array of key/value pairs of basename/version plugin dependencies.
	 * Prefixing a version with '<' will allow your plugin to specify a maximum version (non-inclusive) for a dependency.
	 */
	public $plugin_requires	= null;
	/**
	 * author - Your name, or an array of names.
	 */
	public $plugin_author		= 'FusionTicket Solutions Limited';
	/**
	 * contact - An email address where you can be contacted.
	 */
	public $plugin_email		= 'info@fusionticket.com';
	/**
	 * url - A web address for your plugin.
	 */
	public $plugin_url			= 'http://www.fusionticket.com';

  public $plugin_actions  = array ('install', 'uninstall', 'named', 'config');
  protected $CalcData = array(0 =>array('seats'=>0, 'amount'=>0));
  protected $MaxData = array('seats'=>0, 'amount'=>0);
  protected $errors ='';

  function init(  ){
    global $_SHOP;
  }

  function config(& $config){
    $config['help_plugin'] = array(
      'plugin_help_hiddenhelp'=>array(1,'yesno','')
      );
  }

  function doExtendMenuItems($menu){
    //var_Dump($menu);
    $menu["#"] = '';
    $menu["#help"] = 'plugin_help_helpbutton';
    return $menu;
  }

  function actionUpdate(){
    config::updateFile('plugin_help_hiddenhelp', $_GET['set']);
    return true;
  }


  function actionShow(){
//    echo "here i am.";
    echo json_encode($this->ShowHelpbox($_GET['menu']));
    return true;
  }

  function doPageFinish($ctrl){
    global $_SHOP;
    $list = explode('~',$ctrl->getCurrentPage());
   // var_dump($list);
    if ($list[0] ==='admin' and !empty($_SHOP->admin)) {
      $x = '<script>$("#plugin_help_helpbutton").click( function() {
                        jQuery.getJSON( "json.php", {page:"help/show", menu:"'.$ctrl->getCurrentPage().'"},
                         function(json) {$("#mybox-helpbox").remove();   $("#helpbox").html(json);});
                        return false;} );</script>';
      $ctrl->set('FooterBelow', $x);
      $echo = '<form id="helpbox"></form>';
      $ctrl->set('FooterBelow', $echo);

      if (!is($_SHOP->plugin_help_hiddenhelp, false)) {
         $ctrl->set('FooterBelow', $this->ShowHelpbox($ctrl->getCurrentPage()));
      }
    }
  }

  function ShowHelpbox($helppage){
    global $_SHOP;
    $val = $this->_getJSONAction('load',array('pagename'=>$helppage,'lang'=>$_SHOP->lang));
    if (!$this->errors) {
      if (is_array($val) && !empty($val['content'])) {
        $val['closeaction'] = 'jQuery.getJSON( "json.php", {page:"help/update", menu:"'.$helppage.'", set:$("#hiddenhelp:checked").val() != undefined});';
        $val['closeaction'] = '$("#helpbox").html(""); ';
        $echo = $this->ShowMessage($val, 'helpbox');
        $echo .= '<script> ';
        $echo .= ' $(".ui-dialog-buttonset").prepend("<label for=\'hiddenhelp\'>'.con('help_hiddenhelpmessage').'<input type=\'checkbox\' id=\'hiddenhelp\' name=\'hiddenhelp\''.(is($_SHOP->plugin_help_hiddenhelp, false)?' checked':"").'></label>");';
        $echo .= '</script>';
        return $echo;
      } else {
        return '<script>alert("'.con('plugin_help_nohelp').'");</script>';
      }
    }
    return $this->errors;
  }

  function _getJSONAction($action='', $data=false){
    global $_SHOP;
    require_once("classes/class.restservice.client.php");
    $rsc = new RestServiceClient('www.fusionticket.com?option=com_fusionticketsolution&task=help.'.$action);
    try{
      $rsc->json     = json_encode($data);
      $rsc->excuteRequest();
      $value  = $rsc->getResponse();
      $return = json_decode($value, true);
      if (is_array($return) && $return['error']) {
        $this->errors =$return['reason'];
        return false;
      }
      return is($return, $value);
    } catch(Exception $e) {
      $this->errors =   var_export($e->getMessage(), true);
      return false;// " - Could not check for new version.";
    }

  }

  function ShowMessage($message, $form='PublishEvents'){
//   var_dump($message);
    $message['closeaction'] = is($message['closeaction'],'');

	 $return = "<div id='mybox-{$form}' align='left'>{$message['content']}</div><script>
           $('#mybox-{$form}').
              dialog({
 	          		resizable: false,
                bgiframe: false,
                closeOnEscape: false,
                position:['middle',50],
                height: 500 ,
                width: 670,
                title: '{$message['title']}',
                modal: true,
                appendTo: '#{$form}' ,
          			buttons: {";
    if (isset($message['buttons']) && is_array($message['buttons'] )) {
      foreach ($message['buttons']  as $caption => $action ){
        $return .= "'{$caption}': function() { {$action} },\n";
      }
    }
    $return .="Cancel: function() { $( this ).dialog( 'close' ); {$message['closeaction']} }
          			}
               }).dialog('open');</script>";
    return $return;
  }
}

?>