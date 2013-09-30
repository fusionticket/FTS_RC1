<?PHP
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

require_once (LIBS.'swift'.DS.'swift_required.php');
class EmailSwiftXMLCompiler {

  private $res=array(); //result of execution, indexed by language

  private $mode=0; //0 normal 1 text
  private $stop_tag; //that stops the mode nr 1

  private $stack=array(); // local stack for various purposes
  private $vars=array(); //variables are collected for informative purposes
  private $args='data'; //name of the parameter array where variables are stored
  public $langs = array();

  public $deflang=0;
  public $errors=array();
  private $xmlParsed = false;

  function EmailSwiftXMLCompiler (){
  }

  function compile ($xml, $className){

    if (!$this->errors) {
      $xyz =
'/*this is a generated code. do not edit! produced '.date("C").' */

require_once("classes/compiler.email.swift.xml.php");

class '.$className.' extends EmailSwiftXMLCompiler {
  public $object_id;
  public $engine;
  public $langs = array('.$this->getEmailLangs($xml).');
  public $deflang = "'.$this->deflang.'";

}';
      //    echo ($xyz);
      return $xyz;
    }else{
      return FALSE;
    }
  }
  private function addParam ($key,$val,$lang=0){
    if(!$lang){
      $lang=0; //insure that this is '0' and not other 'null' values
    }

    $this->res[$lang][$key]=$val;
  }

  private function addToParam ($key,$val,$lang=0){
    if(!$lang){
      $lang=0; //insure that this is '0' and not other 'null' values
    }

    $this->res[$lang][$key][]=$val;
  }

  public function write (&$swiftInstance, &$data, $lang=0, $testme=false){
    $xml = $this->sourcetext;
    $this->data = &$data;
 //   if (empty($lang)) { $lang = $this->deflang; }
    //Let smarty run
    $xml = $this->buildSmarty($xml,$data);

    //Pass XML to Compiler.
    $this->parseXml($xml); //Should Fill this->res

    if(!empty($this->errors)){
      return false;
    }
    //Check if $swiftMessage exsists.
    if(!is_object($swiftInstance)){
      $swiftInstance = Swift_Message::newInstance();
    }
    $swift = &$swiftInstance;

    //Build Langs
    foreach($this->res as $lang=>$vals){
      if($lang){
        $this->langs[] = $lang;
      }
    }
    $lang = trim($lang);
    //No Lang passed pull the default lang
    if($lang===0 || empty($lang)){
      $lang=$this->deflang;
    }
    //No deflang pull the first lang
    if($lang===0 || empty($lang)){
      $lang = $this->langs[0];
    }

    //Build Message into $swift
    $this->buildMessage($swift,$data,$lang,$testme);

    return $swift;
  }

  private function buildSmarty ($code, $data, $name='', $testme=false){
    global $_SHOP;
    require_once(CLASSES."class.smarty.php");
    require_once("classes/smarty.gui.php");

    $smarty = new MySmarty;
    $gui = new gui_smarty($smarty);

    $smarty->compile_id   = "xmlmail_".$_SHOP->lang;
    $smarty->assign($data);
    $smarty->setCacheDir($_SHOP->tmp_dir);
    $smarty->setCompileDir($_SHOP->tmp_dir); // . '/web/templates_c/';

    $smarty->addPluginsDir(INC . "shop_plugins".DS);

    $smarty->assign('_SHOP_lang', $_SHOP->lang);
    $smarty->assign("_SHOP_images", $_SHOP->images_url);
    $smarty->assign( config::asArray());
    $smarty->assign("_SHOP_files", $_SHOP->files_url );//ROOT.'files'.DS
    $smarty->assign("_SHOP_images", $_SHOP->images_url);
    //   var_dump($smarty); die();
 //   $smarty->my_template_source = $code;
    $compiledXML = $smarty->fetch("string:".$code);
    unset($smarty);
    unset($gui);
    return $compiledXML;
  }

  private function buildMessage(&$message, &$data, $lang=0, $testme=false){
    global $_SHOP;

    if(!$this->xmlParsed){
      $this->errors[] = con('xml_parse_fail');
      return false;
    }
    if(empty($this->res)){
      $this->errors[] = con('no_template_tags');
      return false;
    }
    if(empty($this->res[$lang])){
      $this->errors[] = con('no_template_body');
      return false;
    }
    $res = $this->res[0];
    $langRes = $this->res[$lang];

    if(isset($res['from'])){
   	  $message->setFrom((array)$res['from']);
    }else{
    	$message->setFrom(array(utf8_decode ( $_SHOP->organizer_email) => utf8_decode ( $_SHOP->organizer_name) ));
    //  writeLog('email from '.print_r($message,true));
    }

    if(is($res['cc'],false)){
      $ccArr = array();
      foreach($res['cc'] as $arr){
        $ccArr = array_merge($ccArr,(array)$arr);
      }
     	$message->setCc($ccArr);
      unset($arr);
    }

    if(isset($res['bcc'])){
      $bccArr = array();
      foreach($res['bcc'] as $arr){
        $bccArr = array_merge($bccArr,$arr);
      }
     	$message->setBcc($bccArr);
      unset($arr);
    }

    if(isset($res['to'])){
      $message->setTo($res['to']);
    }

    if(isset($langRes['subject'])){
      $message->setSubject($langRes['subject']);
    }

    if(isset($res['return'])){
      $message->setReturnPath($res['return']);
    }

    //defaults to UTF
    if(isset($res['head_charset'])){
      $message->setCharset($res['head_charset']);
    }

    if(isset($langRes['html'])){
      $message->setBody($langRes['html'],'text/html',is($langRes['html_charset'],is($res['html_charset'],null)));
    }
    if(isset($langRes['text'])){
      $message->addPart($langRes['text'],'text/plain',is($langRes['text_charset'],is($res['text_charset'],null)));
    }

    return $message;

  }

  private function characterData ($parser, $data) {
    if($this->mode==1){
      $this->text.=$data;
    }
  }


  private function parseXml($xml){
    $this->xml_parser = xml_parser_create('UTF-8');
    xml_set_object($this->xml_parser, $this);
    xml_parser_set_option($this->xml_parser, XML_OPTION_CASE_FOLDING, 1);
    xml_parser_set_option($this->xml_parser, XML_OPTION_TARGET_ENCODING, "UTF-8");
    xml_set_element_handler($this->xml_parser, "startElement", "endElement");
    xml_set_character_data_handler($this->xml_parser, "characterData");

    if (!(xml_parse($this->xml_parser, $xml))) {
      $this->error('('.xml_get_error_code($this->xml_parser).') '.xml_error_string(xml_get_error_code($this->xml_parser)));
      return false;
    }
    xml_parser_free($this->xml_parser);
    $this->xmlParsed = true;
    return true;
  }

  /**
   * EmailSwiftXMLCompiler::emailToParam()
   * Will try to turn email xml into an array format.
   * @return array($email) or array($email => $names)
   */
  private function emailToParam($val){
    preg_match_all("/(.*?)(<)([^>]+)(>)/",$val,$matches);
    if(is($matches[3][0])){
      $email = utf8_decode (trim($this->varsToValues($matches[3][0])));
      if(is($matches[1][0])){
        $names = utf8_decode ($this->varsToValues(trim($matches[1][0])));
        $ret[$email] = $names;
        return $ret;
      }else{
        $ret[] = $email;
      }
    } else
      $ret[] = utf8_decode ($this->varsToValues($val));
    return $ret;
  }

  function error ($message){
    $this->errors[]='email '.$message." line ".xml_get_current_line_number($this->xml_parser).":".
                                               xml_get_current_column_number($this->xml_parser);
  }

  private function getEmailLangs($xml){
    $this->parseXml($xml);

    $this->langs = array();
    $langs = array();
    foreach($this->res as $lang=>$vals){
      if($lang){
        $this->langs[] = $lang;
        $langs[] = "'".$lang."'";
      }
    }

    $langs = implode(',',$langs);
    return $langs;
  }

  /**
   * EmailSwiftXMLCompiler::replaceVar()
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
    return preg_replace_callback('/\$(\w+)/',array(&$this,'replaceVar'),$string);

  }


  private function startElement ($parser, $name, $a) {
    if($this->mode==1){
      $this->text.="<".strtolower($name)." ";
      foreach($a as $name=>$value){
        $this->text.="$name=\"$value\" ";
      }
      $this->text.=">";
      return;
    }
    //echo $name."<br>";
    switch(strtolower($name)){

      case "template" :
        if(isset($a["DEFLANG"])){
          $this->deflang=$a["DEFLANG"];
        }
        break;

      case "text" :
        array_push($this->stack,$this->mode);
        array_push($this->stack,$a["LANG"]);
        $this->end_tag="TEXT";
        $this->mode=1;
        break;

      case "html":
        array_push($this->stack,$this->mode);
        array_push($this->stack,$a["LANG"]);
        $this->end_tag="HTML";
        $this->mode=1;
        break;

      case "from" :
        $this->addParam('from',$this->emailToParam($a['EMAIL']));
        break;

      case "to" :
        $this->addParam('to',$this->emailToParam($a['EMAIL']));
        break;

      case "cc" :
        $this->addToParam('cc',$this->emailToParam($a['EMAIL']),$a['LANG']);
        break;

  		case "bcc" :
        $this->addToParam('bcc',$this->emailToParam($a['EMAIL']),$a['LANG']);
        break;

  		case "header" :
        $this->addToParam('header',array(
  					'name'=>$this->varsToValues($a['NAME']),
  					'value'=>$this->varsToValues($a['VALUE']),
  				),$a['LANG']);
        break;

  		case "return" :
        $this->addParam('return',$this->varsToValues($a['EMAIL']),$a['LANG']);
        break;

  		case "text_charset" :
        $this->addParam('text_charset',$this->varsToValues($a['VALUE']),$a['LANG']);
        break;

  		case "html_charset" :
        $this->addParam('html_charset',$this->varsToValues($a['VALUE']),$a['LANG']);
        break;

  		case "head_charset" :
        $this->addParam('head_charset',$this->varsToValues($a['VALUE']),$a['LANG']);
        break;

  		case "subject" :
        $this->addParam('subject',$this->varsToValues($a['VALUE']),$a['LANG']);
        break;
    }

  }

  private function endElement ($parser, $name) {
    if($this->mode==1  and $name!=$this->end_tag){
      $this->text.="</".strtolower($name).">";
      return;
    }

    switch(strtolower($name)){

      case "text":
        $lang=array_pop($this->stack);
        $this->mode=array_pop($this->stack);

        $this->addParam('text',$this->varsToValues($this->text),$lang);

        $this->text='';
        break;

      case "html":
        $lang=array_pop($this->stack);
        $this->mode=array_pop($this->stack);

        $this->addParam('html',$this->varsToValues($this->text),$lang);

        $this->text='';
        break;

      case "template":
        break;
    }
  }

  function _gen_lang ($lang,$data){
    global $_SHOP;
    $pre='      ';
    $post=";\n";
    if(isset($data['from'])){
   		$res.=$pre.'$message->setFrom(array('.$data['from'].'))'.$post;
    }else if ($lang===0){
    	$res.=$pre.'$message->setFrom(array("'.$_SHOP->organizer_email.'"=>"'.$_SHOP->organizer_name.'" ))'.$post;
    }

    if(isset($data['cc'])){
			$cc=implode(',',$data['cc']);
     	$res.=$pre.'$message->setCc(array('. $cc .'))'.$post;
    }

    if(isset($data['bcc'])){
			$bcc=implode(',',$data['bcc']);
     	$res.=$pre.'$message->setBcc(array('. $bcc .'))'.$post;
    }

    if(isset($data['to'])){
      $res.=$pre.'$message->setTo(array('.$data['to'].'))'.$post;
    }

    if(isset($data['subject'])){
      $res.=$pre.'$message->setSubject('. $data['subject'] .')'.$post;
    }

    if(isset($data['return'])){
      $res.=$pre.'$message->setReturnPath('. $data['return'] .')'.$post;
    }

    //defaults to UTF
    if(isset($data['head_charset'])){
      $res.=$pre.'$message->->setCharset('. $data['head_charset'] .')'.$post;
    }

    if(isset($data['html'])){
      $res.=$pre.'$message->setBody('.$data['html'].",'text/html',".is($data['html_charset'],"null").")".$post;
    }
    if(isset($data['text'])){
      $res.=$pre.'$message->addPart('.$data['text'].",'text/plain',".is($data['text_charset'],"null").")".$post;
    }

    if(isset($data['attachment'])){
      foreach($data['attachment'] as $attach){
        $file=$attach['file'];
        $data1=$attach['data'];

        $r_data='$'.$this->args.'['.$data1.']';

        if(isset($data1)){
          $res.=$pre.'if(isset('.$r_data.")){\n";
          $res.=$pre.'  $message->attach(Swift_Attachment::newInstance( '.$r_data.", ".$attach['name'].", ".$attach['type']."))".$post;
          $res.=$pre."}\n";
        }

        if(isset($data1) and isset($file)){
          $res.=$pre."else{\n";
        }

        if(isset($file)){
          $res.=$pre.'$message->attach(Swift_Attachment::fromPath('.$attach['file'].', '.$attach['type']."))".$post;
        }

        if(isset($data1) and isset($file)){
          $res.=$pre."}\n";
        }
      }
    }
    return $res;
  }

  function make_uses ($vars){
    $res="array(";
    $row= 0;
    foreach($vars as $var){
      if(is_array($var)){
        $res.=$sep.$this->make_uses($var);
      }else{
        $res.="$sep'$var'";
      }
      $sep=",";
      If ($row==7) {
        $row=0;
        $res."/n";
      } else {
        $row++;
      }

    }
    return "$res)";
  }
}
?>