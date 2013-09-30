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


if (!defined('ft_check')) {die('System intrusion ');}

require_once (LIBS.'swift'.DS.'swift_required.php');
class EmailSwiftCompiler {

  var $mode=0; //0 normal 1 text

  var $vars=array(); //variables are collected for informative purposes
  var $args='data'; //name of the parameter array where variables are stored

  var $errors=array();

  private $data = null;
  private $varsBuilt = false;

  //Email Vars.
  private $emailArray = null;
  private $emailTo = null; //$email => $firstname $lastname
  private $emailCC = null; //array($email=>$name,$email2)
  private $emailBCC = null;
  private $emailFrom = null; //$email => $firstname $lastname
  private $emailDefLang = 0;
  public $langs = null;
  private $emailTemplates = null;
  private $emailAttachments = null;
  private $emailOrderPDF = null;
  private $emailOrderSent = false;

  function EmailSwiftCompiler (){
  }
  public function compile ($emailArray, $newClassName){
    $ret=
'/*this is a generated file. do not edit!
  produced '.date("l dS of F Y h:i:s A").'
*/
require_once("classes/compiler.email.swift.php");

class '.$newClassName.' extends EmailSwiftCompiler {
  public $langs = array('.$this->getLangs($emailArray).');

}';

    return $ret;
  }

  public function write ( $swiftInstance, $data, $lang=0, $testme=false){
    $emailArray = $this->sourcetext;
    $this->data = $data;
    //Build vars
    $this->buildVars($emailArray);

    if(!is_object($swiftInstance)){
      $swiftInstance = Swift_Message::newInstance();
    }
    $swift = &$swiftInstance;

    $swift->setFrom($this->emailFrom);
    $swift->setTo($this->emailTo);
    $swift->setCc($this->emailCC);
    $swift->setBcc($this->emailBCC);

    $lang = trim($lang);
    //No Lang passed pull the default lang
    if($lang===0 || empty($lang)){
      $lang=$this->emailDefLang;
    }
    //No deflang pull the first lang
    if($lang===0 || empty($lang)){
      $lang = $this->langs[0];
    }
    $swift->setSubject($this->buildSmarty($this->emailTemplates[$lang]['template_subject'],$this->data,'subject'));
    $swift->setBody($this->buildSmarty($this->emailTemplates[$lang]['template_html'],$this->data,'html'),'text/html');
    $swift->addPart($this->buildSmarty($this->emailTemplates[$lang]['template_text'],$this->data,'text'),'text/plain');
    return $swift;
  }

  private function buildVars($emailArray){
    global $_SHOP;
    if(is_string($emailArray)){
      $emailArray = unserialize($emailArray);
    }
    $this->emailArray = &$emailArray;
    //To

    $this->emailTo = array(
      $this->varsToValues(empt($emailArray['email_to_email'],'$user_email'))=>
      $this->varsToValues(empt($emailArray['email_to_name'],'$user_firstname $user_lastname')));
    //From
    $this->emailFrom = array(
      $this->varsToValues(empt($emailArray['email_from_email'],$_SHOP->organizer_email))=>
      $this->varsToValues(empt($emailArray['email_from_name'],$_SHOP->organizer_name)));
    //CC
    foreach($emailArray['emails_cc'] as $email=>$name){
      if(trim($email)<>'' && trim($name)<>''){
        $this->emailCC[$this->varsToValues($email)]=$this->varsToValues($name);
      }elseif(trim($email)<>''){
        $this->emailCC[]=$this->varsToValues($email);
      }
    } unset($email,$name);
    //BCC
    foreach($emailArray['emails_bcc'] as $email=>$name){
      if(trim($email)<>'' && trim($name)<>''){
        $this->emailBCC[$this->varsToValues($email)]=$this->varsToValues($name);
      }elseif(trim($email)<>''){
        $this->emailBCC[]=$this->varsToValues($email);
      }
    }
    //Default Lang
    $this->emailDefLang = is($emailArray['email_def_lang'],0);
    if(trim($this->emailDefLang)==''){
      $this->emailDefLang = 0;
    }
    //check templates
    if(is($emailArray['email_templates'],false)){
      foreach($emailArray['email_templates'] as $lang=>$fields){
        $this->langs[] = $lang;
        array_walk($fields,array(&$this,'recVarToVals'));
        $this->emailTemplates[$lang]=$fields;
      }
    }
    $this->varsBuilt = true;
  }

  private function buildSmarty ($code, $data, $name='', $testme=false){
    global $_SHOP;
    require_once(CLASSES."class.smarty.php");

    require_once("classes/smarty.gui.php");

    $smarty = new MySmarty;
    $gui = new gui_smarty($smarty);

    $smarty->compile_id   = "smail_".$_SHOP->lang;
    $smarty->assign($data);

    $smarty->setCacheDir($_SHOP->tmp_dir);
    $smarty->setCompileDir($_SHOP->tmp_dir); // . '/web/templates_c/';

    $smarty->addPluginsDir(INC . "shop_plugins".DS);

    $smarty->assign('_SHOP_lang', $_SHOP->lang);
    $smarty->assign("_SHOP_images", $_SHOP->images_url);
    $smarty->assign( config::asArray());
    $smarty->assign("_SHOP_files", $_SHOP->files_url );//ROOT.'files'.DS
    $smarty->assign("_SHOP_images", $_SHOP->images_url);

//    $smarty->my_template_source = $code;
    $compiledCode = $smarty->fetch("string:".$code);//get_class($this).$name
    unset($smarty);
    unset($gui);
    return $compiledCode;
  }


  private function getLangs($emailArray){
    if(is_string($emailArray)){
      $emailArray = unserialize($emailArray);
    }

    if(is($emailArray['email_templates'],array())){
      foreach($emailArray['email_templates'] as $lang=>$fields){
        if(!empt($lang,false)){
          continue;
        }
        $this->langs[] = $lang;
        $langs[] = "'".$lang."'";
      }
    }else{
      $this->langs = array();
      $langs = array();
    }

    $langs = implode(',',$langs);


    return $langs;
  }

  private function recVarToVals(&$value,$key){
    $value = $this->varsToValues($value);
  }

  /**
   * EmailSwiftCompiler::varsToValues()
   *
   * Takes a string with $varibles and replaces the var with the $data['varible'] value
   *
   * @return String with $varbles converted to values.
   */
  private function varsToValues($string){
    return preg_replace_callback('/{\$(\w+)}/',array(&$this,'replaceVar'),$string);

  }

  /**
   * EmailSwiftCompiler::replaceVar()
   *
   * Will replace the matched string with the value from data.
   *
   * @param mixed $matches
   * @return
   */
  private function replaceVar($matches){
    //array_push($this->vars,$matches[1]);
    $value = is($this->data[$matches[1]],$matches[0]);
    return $value;
  }
}
?>