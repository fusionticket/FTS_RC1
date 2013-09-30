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

if (!defined('ft_check')) {die('System intrusion ');}
class Payment {

  public $handling;
  public $extras    = array();
  public $mandatory = array();
  public $requiteduserfields = array();
	function __construct (&$handling) {
 		$this->handling = &$handling;
	  if (is($_SHOP->AutoDefineLangs, false) && !file_exists(loadLanguage(get_class($this), true))) {
	    file_put_contents(loadLanguage(get_class($this), true), '<'.'?php ?'.'>', FILE_APPEND);
	  }
	  loadLanguage(get_class($this));

  }

  function __get($name) {
    if ($this->handling and ($result = $this->handling->$name)) {
      return $result;
    } else {
      return false;
    }
  }

	function __set($name, $value) {
		if ($this->handling) {
	  		return $this->handling->$name = $value;
		} else {
	  		return false;
		}
	}

	public function admin_view ( ){}

 	public function admin_form ( ){}

	function admin_init (){}

	/**
	 * Used to check the manditory fields defined in the manditory array
	 */
	public function admin_check (&$data){
		foreach($this->mandatory as $field){
			if(empty($data[$field])){
        addError($field, 'mandatory');
      }
		}
  	return true;
	}


	function on_handle($order, $new_status, $old_status, $field){
    return true;
  }

	function on_order_delete($order_id){
    return true;
  }

  function on_confirm(&$order){return '';}

  function on_submit(&$order){}

  function on_return(&$order, $accepted ){
     return array('approved'=>$accepted,
                  'transaction_id'=>false,
                  'response'=> '');
  }

  function on_notify(&$order){}

  function on_check(&$order){ return false;}

  public function getOrder(){

  }

  public function encodeCallback(){return "";}

  public function decodeCallback(){return true;}

//****************************************************************************//

	protected function encodeEPHCallback($ephCode){
    $code = base64_encodex($this->handling_payment.':'.base_convert($this->handling_id,10,36).':'.$ephCode);
		return "cbr=".$code;
	}

  protected function url_post ($url, $data){
    global $_SHOP;
    $this->debug = 'ExucuteCall = '.$_SHOP->url_post_method . "\n";
  	switch($_SHOP->url_post_method) {

  		case "libCurl": //php compiled with libCurl support

  			$result=$this->libCurlPost($url,$data);
  			break;


  		case "curl": //cURL via command line

  			$result=$this->curlPost($url,$data);
  			break;


  		case "fso": //php fsockopen();
  		default: //use the fsockopen method as default post method

  			$result=$this->fsockPost($url,$data);
  			break;
  	}

  	return $result;
  }

  //post transaction data using curl

  private function curlPost($url, $data)  {

  	global $_SHOP;

  	//build post string

  	foreach($data as $i=>$v) {
  		$postdata.= $i . "=" . urlencode($v) . "&";
  	}


  	//execute curl on the command line

  	 $this->debug .=  "retExec   = ". print_r(exec("{$_SHOP->url_post_curl_location} -d \"$postdata\" $url", $info, $returnval))."\n";
     $this->debug .=  "returnval = ".print_r($returnval);


  	$info=implode("\n",$info);

  	return $info;

  }

  //posts transaction data using libCurl

  private function libCurlPost($url, $data)  {

  	//build post string
    $postdata=array();
  	foreach($data as $i=>$v) {
  		$postdata[] = $i . "=" . urlencode($v);
  	}
    $postdata = implode('&',$postdata);

  	$ch=curl_init();
	 
    $useragent="Mozilla/5.0 (Windows NT 6.1; WOW64; rv:20.0) Gecko/20100101 Firefox/20.0";
  	curl_setopt($ch, CURLOPT_USERAGENT, $useragent);

  	curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER, TRUE)  ;
  	curl_setopt($ch,CURLOPT_URL,$url);
  	curl_setopt($ch,CURLOPT_POST,1);
  	curl_setopt($ch,CURLOPT_POSTFIELDS,$postdata);

  	//Start ob to prevent curl_exec from displaying stuff.
  	$info =curl_exec($ch);
    if ($info === false)
    {
        $this->debug .=  'curl_error = ' . curl_error($ch)."\n";
        $this->debug .= 'curl_info = '.print_r(curl_getinfo($ch), true)."\n";
    }

  	curl_close($ch);

  	return $info;

  }

  //posts transaction data using fsockopen.
  private function fsockPost($url, $data) {

  	//Parse url
  	$web=parse_url($url);

  	//build post string
  	foreach($data as $i=>$v) {
  		$postdata.= $i . "=" . urlencode($v) . "&";
  	}

  	//Set the port number
  	if($web['scheme'] == "https") { $web['port']="443";  $ssl="ssl://"; } else { $web['port']="80"; }

  	//Create connection
  	$fp=@fsockopen($ssl . $web[host],$web[port],$errnum,$errstr,30);

  	if(!$fp) {
    	//Error checking
      $this->debug .=   "$errnum: $errstr\n";
    }	else {
      //Post Data
  		fputs($fp, "POST $web[path] HTTP/1.1\r\n");
  		fputs($fp, "Host: $web[host]\r\n");
  		fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n");
  		fputs($fp, "Content-length: ".strlen($postdata)."\r\n");
  		fputs($fp, "Connection: close\r\n\r\n");
  		fputs($fp, $postdata . "\r\n\r\n");

  		//loop through the response from the server
  		while(!feof($fp)) {
        $info .= @fgets($fp, 1024);
      }
      //close fp - we are done with it
  		fclose($fp);

      $this->debug .= $info;
      $result = substr($info, (strpos($info, "\r\n\r\n")+4));
      if (strpos(strtolower($info), "transfer-encoding: chunked") !== FALSE) {
        $result = $this->unchunkHttp11($result);
      }

  		//break up results into a string
  		//$info=implode(",",$info);

  	}
  	return $result;
  }

  function unchunkHttp11($data) {
    $fp = 0;
    $outData = "";
    while ($fp < strlen($data)) {
      $rawnum = substr($data, $fp, strpos(substr($data, $fp), "\r\n") + 2);
      $num = hexdec(trim($rawnum));
      $fp += strlen($rawnum);
      $chunk = substr($data, $fp, $num);
      $outData .= $chunk;
      $fp += strlen($chunk);
    }
    return $outData;
  }

  function dyn_load($name){
    $res = include_once($name);
    return $res;
  }
}
?>