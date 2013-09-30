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

define('SQL2XML_OUT_RETURN',0);
define('SQL2XML_OUT_ECHO',1);

class XMLData {
  /**
   * export mysql query results to xml format
   */
  function sql2xml($query,$table,$out=SQL2XML_OUT_RETURN,$pk=''){
    $total  ='';
  	if(empty($query)){user_error('cannot export "'.$table.'": empty query');return;}
  	if($res=ShopDB::query($query)){

  	  $nf=shopDB::fieldCount($res);

  		$pc=-1;
  		for($i=0;$i<$nf;$i++){
  			if(!$pk){
  				if(strpos(shopDB::fieldFlags($res,$i),'primary_key')!==false){
  					$pc=$i;
  				}
  			}
  			$names[$i]=shopDB::fieldname($res,$i);
  			$tables[$i]=(strcasecmp($table,shopDB::fieldTable($res,$i))==0);

  			if($names[$i]==$pk){
  			  $pc=$i;
  			}
  		}
  		if($pc<0){user_error('cannot export "'.$table.'": no primary key defined');return;}

  		while($row=shopDB::fetch_row($res)){
  		  $ret='<'.$table.'>'."\n";
  			foreach($row as $i=>$val){
  			  if($tables[$i]){
  				  $ret.='  <'.$names[$i].($pc==$i?' pk="1"':'').(is_null($val)?' null="1"':'').'>'.
  					htmlspecialchars($val,ENT_NOQUOTES).'</'.$names[$i].'>'."\n";
  				}
  			}
  			$ret.='</'.$table.'>'."\n";

  			if($out==SQL2XML_OUT_RETURN){
          $total.=$ret;
        }else{
          echo $ret;
        }
  		}
  	}
    return $total;

  }

  function sql2xml_all($what,$out=SQL2XML_OUT_RETURN){
  	$ret.='<?xml version="1.0" encoding="UTF-8" ?>'."\n";
  	$ret.='<sql2xml>'."\n";

  	if($out==SQL2XML_OUT_ECHO){
  		echo $ret;
  	}

    foreach($what as $w){
  	  $query=$w['query'];
  		$table=$w['table'];
  		$pk=$w['pk'];

  		$ret.= XMLData::sql2xml($query,$table,$out,$pk);
  	}

  	if($out==SQL2XML_OUT_ECHO){
      echo '</sql2xml>';
  	}else{
  	  $ret.='</sql2xml>';
  		return $ret;
  	}
  }

  /**
   * read xml file and writes into mysql database.
   * if the record is already in db uses update,
   * otherwise uses insert
   */
  function xml2sql($file, $asArray= false){
    $tmp=&new _xmltmp($asArray);

  	$xml_parser = xml_parser_create();
  	xml_parser_set_option($xml_parser,XML_OPTION_CASE_FOLDING,FALSE);
  	xml_parser_set_option($xml_parser,XML_OPTION_TARGET_ENCODING,'utf-8');
  	xml_set_element_handler($xml_parser, array(&$tmp,"startElement"), array(&$tmp,"endElement"));
  	xml_set_character_data_handler($xml_parser, array(&$tmp,"characterData"));

  	if (!($fp = fopen($file, "r"))) {
  		 addWarning("could_not_open_xml_file",$file);
  		 return;
  	}

  	while ($data = fread($fp, 4096)) {
  		 if (!xml_parse($xml_parser, $data, feof($fp))) {
  				 addWarning('',sprintf("XML error: %s at line %d",
  										 xml_error_string(xml_get_error_code($xml_parser)),
  										 xml_get_current_line_number($xml_parser)));
  				 return;
  		 }
  	}
  	addNotice("<br>Inserted {$tmp->inserted} row(s), updated {$tmp->updated} row(s)<br>");

  	xml_parser_free($xml_parser);
  	return ($asArray)?$tmp->results:TRUE;
  }

  function sql2xml_new($query,$table,$out=SQL2XML_OUT_RETURN,$pk=''){
    Global $_SHOP;
    $total  ='';

  	if(empty($query)){user_error('cannot export "'.$table.'": empty query');return;}

  	if($res=ShopDB::query($query)){

  	  $nf=shopDB::fieldCount($res);
  	  if( empty($nf)){
    	  user_error('Cannot export. No results found!');
    	  return;
  	  }

  		$pc=-1;
  		for($i=0;$i<$nf;$i++){
  			if(!$pk){
  				if(strpos(shopDB::fieldFlags($res,$i),'primary_key')!==false){
  					$pc=$i;
  				}
  			}
  			$names[$i]	= shopDB::aliasFieldname($res,$i);

  			$tables[$i]	= (strcasecmp($table,shopDB::fieldTable($res,$i))==0);

  			if($names[$i]==$pk){
  			  $pc=$i;
  			}
  		}
  		//if($pc<0){user_error('cannot export "'.$table.'": no primary key defined');return;}

  		while($row=shopDB::fetch_row($res)){
  		  $ret='<'.$table.'>'."\n";
  			foreach($row as $i=>$val){

  			 if($names[$i]=='eventid1'){
  			  $myevents = htmlspecialchars($val,ENT_NOQUOTES);
  			  }
  			  if($tables[$i] && $i!=$pc){


  			  	  if($names[$i]=='date'){

  					$curval = htmlspecialchars($val,ENT_NOQUOTES);
  					$date = explode("-",$curval);
  					$val = $date[2]."-".$date[1]."-".$date[0];
  					$timestamp = strtotime($curval);
  					$date = date('F d, Y',$timestamp);
  			  	  }
  				  if($names[$i]=='time'){

  					$curvalt = htmlspecialchars($val,ENT_NOQUOTES);
  					//$time = explode(":",$curvalt);
  					//$val = $time[2]."-".$time[1]."-".$date[0];
  					$timestamp1 = strtotime($curvalt);
  					$val = date('g:i A',$timestamp1);
  			  	  }

  				  $ret.='  <'.$names[$i].($pc==$i?' pk="1"':'').'>'.((($names[$i]=='description') && $val!='') ? '<![CDATA[' : '').(($names[$i]=='description' && $val!='') ? "<font size='18'>".$date."</font>" : '').
  					(($names[$i]=='link') ? '<![CDATA[<font size="18"><a href="'.$_SHOP->root.'index.php?event_id='.$myevents.'">BUY TICKETS NOW</a></font>]]>' : htmlspecialchars($val,ENT_NOQUOTES)).((($names[$i]=='description') && $val!='') ? ']]>' : '').'</'.$names[$i].'>'."\n";
  				}

  				/*if(($names[$i]=='description' || $names[$i]=='link'))
  				{
  				$ret.=' <'.$names[$i].'><![CDATA['.htmlspecialchars($val,ENT_NOQUOTES).']]></'.$names[$i].'>'."\n";
  				}*/

  			}
  			$ret.='</'.$table.'>'."\n";

  			if($out==SQL2XML_OUT_RETURN){$total.=$ret;}
  			else{echo $ret;}
  		}
  	}
    return $total;
  }

  function sql2xml_all_new($what,$out=SQL2XML_OUT_RETURN){
  	$ret.='<?xml version="1.0" encoding="UTF-8" ?>'."\n";
  	$ret.='<events>'."\n";

  	if($out==SQL2XML_OUT_ECHO){
  		echo $ret;
  	}

    foreach($what as $w){
  		$query	= $w['query'];
  		$table	= $w['table'];
  		$pk		= $w['pk'];

  		$ret	.= XMLData::sql2xml_new($query,$table,$out,$pk);
  	}

  	if($out==SQL2XML_OUT_ECHO){
      echo '</events>';
  	}else{
  	  $ret.='</events>';
  		return $ret;
  	}
  }
}

class _xmltmp{
  var $depth=0;
	var $sql=array();
	var $query=array();
	var $value='';
  var $table='';
	var $pk='';
  var $isNull = false;

	var $inserted= 0;
	var $updated = 0;
  var $asArray = false;
  var $result  = array();


  function __construct($isArray=false) {
    $this->asArray = $asArray;
  }

	function startElement($parser, $name, $attrs){
		$this->depth++;
		//row starts
		if($this->depth==2){
			$this->table=$name;
			$this->query=array();

		//field	starts
		}else if($this->depth==3){
			$this->value='';
			if($attrs['pk']){
			  $this->pk=$name;
			}
      $this->isNull= ($attrs['null']==1);
		}
	}

	function endElement($parser, $name){

		//field ends
		if($this->depth==3){
			$this->query[$name]=($this->isNull)?null:$this->value;

		//row ends
		}elseif($this->depth==2){
      if ($this->asArray) {
        $this->result[$this->table][] = $this->query;
      } else $this->write();
		}

		$this->depth--;
	}

	function characterData($parser,$data){
		//field contents
		if($this->depth==3){
			$this->value.=$data;
		}
	}

	function write(){
		global $_SHOP;
		$query='select count(*) from `'.$this->table.
		'` where `'.$this->pk.'`='._esc($this->query[$this->pk]);

		if($res = ShopDB::query_one_row($query, false)){
		  $count=$res[0];
		}

		if($count){
			//update
			$query='update `'.$this->table.'` set ';
			$next=true;
			foreach($this->query as $field=>$value){
				if(!$next){
				  $query.=',';
				}
			  $query.='`'.$field.'`='.ShopDB::quote($value);
				$next=false;
			}

			$query.=' where `'.$this->pk.'`='.ShopDB::quote($this->query[$this->pk]);

			//echo $query."<br>\n";
			ShopDB::query($query);

			$this->updated+=shopDB::affected_rows();

		}else{
			//insert

			$query='insert into `'.$this->table.'` set ';
			$next=true;
			foreach($this->query as $field=>$value){
				if(strpos($field,'organizer_id')!==FALSE){
					$value=$_SHOP->organizer_id;
				}
				if(!$next){
				  $query.=',';
				}
			  $query.='`'.$field.'`='.ShopDB::quote($value);
				$next=false;
			}

			//echo $query."<br>\n";
			ShopDB::query($query);

			$this->inserted+=shopDB::affected_rows();
		}
	}
}

?>