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

//Manages templates.
//Usage:
//$engine = new TemplateEngine();
//$template = Template::getTemplate('ticket',$_SHOP->organizer_id);
//$res=$template->write($data);

if (!defined('ft_check')) {die('System intrusion ');}
class Template Extends Model {
  protected $_idName    = 'template_id';
  protected $_tableName = 'Template';
  protected $_columns   = array('template_id', 'template_name', 'template_type', 'template_text', 'template_ts', 'template_status');

  function __construct (){}

	//internal function: loads, initializes the template object, and updates cache
  function &try_load ($name, $t_class_name, $code, $test=false){
    global $_SHOP;
    //print_r($code['template_code']);
    if(file_exists($_SHOP->templates_dir.$t_class_name.'.php')){
      require_once($_SHOP->templates_dir.$t_class_name.'.php');

      if(class_exists($t_class_name)){
        $tpl = new $t_class_name;

        $tpl->sourcetext = $code['template_text'];
        $tpl->template_type = $code['template_type'];
        $_SHOP->templates[$name]=&$tpl;
        if($test and in_array($tpl->template_type,array('swift','systm','email'))){
          $tpl = self::tryBuildEmail($tpl);
          return $tpl;
        }else{
          return $tpl;
        }
      }
    }
    return false;
	}

  private function tryBuildEmail(&$orgTpl){
    global $_SHOP;

    $tpl = $orgTpl;
    $err=0;
    include('admin/templatedata.php');

    $lang = is($_GET['lang'], $_SHOP->lang);
    if (!in_array($lang, $tpl->langs )) {
      $lang = $tpl->langs[0];
    }
    try{
      $tpl->write($swift, $order, $lang);
    }catch(exception $e){
      addWarning('ClassCatch:',$e->getMessage());
      $err++;
    }
    if(!empty($tpl->errors)){
      foreach($tpl->errors as $error){
        addWarning('email_compile_error', $error);
        $err++;
      }
    }
    if($err>0){
      return false;
    }
    return $orgTpl;
  }

	//returns the template object or false
  function &getTemplate($name, $recompile=false){
    global $_SHOP;

    //check if the template is in cache
    if(isset($_SHOP->templates[$name])){
        $res=&$_SHOP->templates[$name];
        return $res;
    }

    //if not: load the template record from db
    $query="SELECT * FROM Template WHERE template_name='$name'";
    if(!$data=ShopDB::query_one_row($query)){
        return FALSE; //no template
    }

    //create template class name
    $t_class_name= str_replace(' ','_',"TT_{$data['template_name']}_{$data['template_type']}");

    //trying to load already compiled template
    if(!$recompile and ($data['template_status']=='comp')){
      if($tpl = self::try_load($name, $t_class_name, $data)) {
        return $tpl;
      }
    }

    //no complied template, need to compile: loading compiler
    switch ($data['template_type']) {
      case 'systm':
      case 'email':
        require_once("classes/compiler.email.swift.xml.php");
        $comp = new EmailSwiftXMLCompiler;
        break;
      case 'pdf2':
        require_once("classes/compiler.pdf.php");
        $comp = new PDF2TCompiler;
        break;
      case 'swift':
        require_once("classes/compiler.email.swift.php");
        $comp = new EmailSwiftCompiler;
        break;
      default:
        user_error("unsupported template type: ".$data['template_type']);
    }

    //try to compile, pass template and name to compiler.
    if(!$code = $comp->compile($data['template_text'],$t_class_name)){
      //if failed to compile set error.
      $this->errors = $comp->errors;
      $query="UPDATE Template SET template_status='error' WHERE template_id='{$data['template_id']}'";
      ShopDB::query($query);
      return FALSE;
    }

    if(file_exists($_SHOP->templates_dir.$t_class_name.'_swift.php')){
      unlink($_SHOP->templates_dir.$t_class_name.'_swift.php');
    }

    if(file_exists($_SHOP->templates_dir.$t_class_name.'.php')){
      unlink($_SHOP->templates_dir.$t_class_name.'.php');
    }

    $fileStream = fopen($_SHOP->templates_dir.$t_class_name.'.php', 'w');
    if($fileStream){
      $res=fwrite($fileStream,utf8_encode("<?php \n".$code."\n?>"));
      $close=fclose($fileStream);
    }

    //trying to load just compiled template
    if($tpl = self::try_load($name, $t_class_name, $data, true)){

      //compilation ok: saving the code in db
      //$query="UPDATE Template SET template_status='comp', template_code="._esc($code)." WHERE template_id='{$data['template_id']}'";
      $query="UPDATE Template SET template_status='comp' WHERE template_id='{$data['template_id']}'";

      if(!ShopDB::query($query)){
        return FALSE;
      }
      return $tpl;
    }else{
      //compilation failed
      $query="UPDATE Template SET template_status='error' WHERE template_id='{$data['template_id']}'";
      ShopDB::query($query);
    }
    return false;
  }

  public function sendMail(&$template, &$data, $testMail='', $lang=''){
    //Get $template Type
    if(!is_object($template)){
      return false;
    }
    $type = is($template->template_type, 'swift');

    //Create the email
    require_once('classes/email.swift.sender.php');
    $template->write($message, $data, $lang);

    // Include Pdfs if told to.
    $includeInvoice = is($data['handling_incl_inv_pdf'],0) ;
    $includeTickets = is($data['handling_incl_ticket_pdf'],0) ;

    if($includeInvoice==1){
		// Added by Lxsparks 11/06/2011
		$includePDF=$data['handling_pdf_template'];  //Get the name of the PDF template that is being used

		if (stristr($includePDF, 'invoice') !=FALSE) { //If it has 'invoice' int he title - apend 'invoice'
		$includeType='invoice';
		} elseif (stristr($includePDF, 'receipt') !=FALSE) { //If it has 'receipt' int he title - apend 'receipt'
		$includeType='receipt';
		} elseif (stristr($includePDF, '_') !=FALSE) {  //If it has neither of the above - take the first word/s upto the underscore mark and apend that
		list($name,$extension)=explode("_",$includePDF);
		$includeType=$name;
		} else {$includeType=$includePDF;}  //If all else fails call it by the name of the file


      $message->attach(Swift_Attachment::newInstance(Order::printOrder($data['order_id'], '', 'data', FALSE, 2), "order_{$data['order_id']}_".con($includeType).".pdf", 'application/pdf')); //Use the new name
    }
    if($includeTickets==1){
      $message->attach(Swift_Attachment::newInstance(Order::printOrder($data['order_id'], '', 'data', FALSE, 1), "order_{$data['order_id']}_".con('tickets').".pdf", 'application/pdf'));
    }

    //We want to log the email proccess so users can debug easier.
    return EmailSwiftSender::send($message, "", $log, $failedAddr, $data);

  }
}
?>