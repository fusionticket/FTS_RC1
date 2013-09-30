<?php

# Start of configuration

#
# The file with your private key
#

$key_file='/Users/vertigo/Sites/test/pure5/server.key';


#
# Type of encryption: 'seal' or 'encrypt' 
#

global $_SHOP;
$_SHOP->crypt_mode='encrypt';

#
# End of configuration
#



/*
%%%copyright%%%
 * phpMyTicket - ticket reservation system
 * Copyright (C) 2004-2005 Anna Putrino, Stanislav Chachkov. All rights reserved.
 *
 * This file is part of phpMyTicket.
 *
 * This file may be distributed and/or modified under the terms of the
 * "GNU General Public License" version 2 as published by the Free
 * Software Foundation and appearing in the file LICENSE included in
 * the packaging of this file.
 *
 * Licencees holding a valid "phpmyticket professional licence" version 1
 * may use this file in accordance with the "phpmyticket professional licence"
 * version 1 Agreement provided with the Software.
 *
 * This file is provided AS IS with NO WARRANTY OF ANY KIND, INCLUDING
 * THE WARRANTY OF DESIGN, MERCHANTABILITY AND FITNESS FOR A PARTICULAR
 * PURPOSE.
 *
 * The "phpmyticket professional licence" version 1 is available at
 * http://www.phpmyticket.com/ and in the file
 * PROFESSIONAL_LICENCE included in the packaging of this file.
 * For pricing of this licence please contact us via e-mail to 
 * info@phpmyticket.com.
 * Further contact information is available at http://www.phpmyticket.com/
 *
 * The "GNU General Public License" (GPL) is available at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * Contact info@phpmyticket.com if any conditions of this licencing isn't 
 * clear to you.
 
 */


	function usage(){
		echo "
cc_open.php : credit card decryption for phpMyTicket
Usage: php cc_open.php <input-file>

";
	}

	function ssl_decrypt($data,$key){
		global $_SHOP;
		
		if(strlen($data)==0){
		  user_error('empty data');
			return FALSE;
		}
		
		if($_SHOP->crypt_mode=='seal'){
		  return _open($data,$key);
		}else{
			return _decrypt($data,$key);
		}
	}

	function _openssl_error(){
	  while($err=openssl_error_string()){
		  user_error($err);
		}
		return FALSE;
	}
	
	function _str_split($string,$length=1){
		$parts = array();
		while ($string) {
			array_push($parts, substr($string,0,$length) );
			$string = substr($string,$length);
		}
		return $parts;
	}
	
	
	function _decrypt($cinfo,$pk){		

			$crypts=explode(',',$cinfo);
			
			foreach($crypts as $crypt){
				if(!openssl_private_decrypt(base64_decode($crypt), $i, $pk)){
					return _openssl_error();
				}
				$info.=$i;
			}
			
			
			return $info;
		
	}


	function _open($cinfo_ekey,$pk){		

			list($cinfo,$ekey)=explode(',',$cinfo_ekey);
			$cinfo=base64_decode($cinfo);
			$ekey=base64_decode($ekey);
			
			if(!$sealres=openssl_open($cinfo, $info, $ekey, $pk)){
			  return _openssl_error();
			}			
			
			return $info;
	}

	
	
  if(!file_exists($key_file)){
	  echo "Please edit {$argv[1]} and specify the file whith your private key\n";
		return -1;
	}
	
	if(count($argv)!=2){
		usage();
		return -1;
	}

	$in=$argv[1];
	$out="open_$in";
	
	if(!$contents=file($in)){
	  echo "Error: cannot read input file $in\n";
		usage();
		return -1;
	}

	fwrite(STDOUT,"Type private key password: ");
	fscanf(STDIN,"%s\n",$key_pwd);
	
  $key_s = file_get_contents($key_file);

  if(!$pk = openssl_get_privatekey(array($key_s,$key_pwd))){
		echo "Error: cannot open private key\n";
	  return -1;
	}
			
	if(!$fh=fopen($out,"w")){
	  echo "Error: cannot open ouptut file $out";
		return -1;
	}
	
	foreach($contents as $line){
		if($line=rtrim($line)){
		
			list($order_id,$cinfo)=explode(':',$line);

			if(!$info=ssl_decrypt($cinfo,$pk)){
				echo "Error: cannot decrypt line ".($lc+1)."\n";
				return -1;
			}
			fwrite($fh,$order_id.",".$info);
			$lc++;
		}
	}
	
	fclose($fh);
	openssl_free_key($pk);
	
	echo "Written $lc line(s) to $out.\n";
?>