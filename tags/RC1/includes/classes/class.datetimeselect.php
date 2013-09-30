<?php

/************************************************************************************************************
 ************************************************************************************************************
 **                                                                                                        **
 ** Copyright (c) 2008, Joshua Bettigole                                                                   **
 ** All rights reserved.                                                                                   **
 **                                                                                                        **
 ** Redistribution and use in source and binary forms, with or without modification, are permitted         **
 ** provided that the following conditions are met:                                                        **
 **                                                                                                        **
 ** - Redistributions of source code must retain the above copyright notice, this list of conditions       **
 **   and the following disclaimer.                                                                        **
 ** - Redistributions in binary form must reproduce the above copyright notice, this list of               **
 **   conditions and the following disclaimer in the documentation and/or other materials provided         **
 **   with the distribution.                                                                               **
 ** - The names of its contributors may not be used to endorse or promote products derived from this       **
 **   software without specific prior written permission.                                                  **
 **                                                                                                        **
 ** THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR         **
 ** IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND       **
 ** FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR              **
 ** CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL      **
 ** DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,      **
 ** DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER     **
 ** IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT      **
 ** OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.                        **
 **                                                                                                        **
 ************************************************************************************************************
 ************************************************************************************************************/
if (!defined('ft_check')) {die('System intrusion ');}
class DateTimeSelect
{

	private $month_arr = array();

	private $day_arr;

	private $suffix = array('st','nd','rd','th');

	private $selected;
	private $fieldname;
	private $yearstart = 0;
	private $yearend = 9;
	private $yearrev = 0;

	public $selectbox;

	public function __construct($format,$fieldname="datetime",$selected="",$range=0){
    global $_SHOP;
    If (!isset($_SHOP->month_arr) or empty($_SHOP->month_arr)) {
      if (defined('dts_month_arr')) {
  		  $_SHOP->month_arr  = explode('|',dts_month_arr);
       } else {
         $_SHOP->month_arr  = array(1,2,3,4,5,6,7,8,9,10,11,12);
       }
    }
    $this->month_arr  = & $_SHOP->month_arr;

		$this->selectbox = "";
		$this->fieldname = $fieldname;
		if($range)
		{
			if(strpos($range,"-")!==false)
			{
				$rangearr = explode("-",$range,2);
				if(intval($rangearr[0]) > intval($rangearr[1]))
				{
					$this->yearstart = (intval($rangearr[1]) - intval(date('Y')));
					$this->yearend = (intval($rangearr[0]) - intval(date('Y')));
					$this->yearrev = 1;
				}
				else
				{
					$this->yearstart = (intval($rangearr[0]) - intval(date('Y')));
					$this->yearend = (intval($rangearr[1]) - intval(date('Y')));
				}
			}
			elseif(is_numeric($range)) {
				$this->yearend = ($range-1);
      }
	  }
		$this->selectbox = "<input type='hidden' name='$fieldname' value='$selected'>\n";
		$selected = strtotime($selected);
		switch($format)
		{
			case 'My':
			case 'my':
        If ($selected) {
   			  $this->selected['month'] = date('n',$selected);
    			$this->selected['year']  = date('y',$selected);
  			}
				$this->selectbox .= ($format=='my')?$this->buildMonth('F'):$this->buildMonth('n');
				$this->selectbox .= $this->buildYear('y');
				$this->selectbox .= $this->buildDateScript();
				break;
			case 'MY':
			case 'mY':
        If ($selected) {
   			  $this->selected['month'] = date('n',$selected);
    			$this->selected['year']  = date('Y',$selected);
  			}
				$this->selectbox .= ($format=='mY')?$this->buildMonth('F'):$this->buildMonth('n');
				$this->selectbox .= $this->buildYear('Y');
				$this->selectbox .= $this->buildDateScript();
				break;
		// Time
			case 'd':
			case 'D':
        If ($selected) {
    			$this->selected['day']   = date('j',$selected);
    			$this->selected['month'] = date('n',$selected);
    			$this->selected['year']  = date('Y',$selected);
  			}
				$this->selectbox .= $this->buildDay('j');
				$this->selectbox .= ($format=='d')?$this->buildMonth('F'):$this->buildMonth('n');
				$this->selectbox .= $this->buildYear('Y');
				$this->selectbox .= $this->buildDateScript();
				break;
		// Time
			case 't':
			case 'T':
        If ($selected) {
  				$this->selected['hour']   = date('H',$selected);
          $this->selected['minute'] = date('i',$selected);
        }

	      $this->selectbox .= $this->buildHour('h');
  			$this->selectbox .= $this->buildMinute('i');
				$this->selectbox .= $this->buildTimeScript();
				break;
  	}
  		//	print_r($this->selected);
	}


/*********************************************************************************
\* Date Functions                                                                *
 *********************************************************************************/
	private function buildDateScript() {
    return "<script>\n".
           "function update_{$this->fieldname}_date() {\n".
//           "  var obj  = document.all['{$this->fieldname}']; \n".
//           "  var objd = document.all['{$this->fieldname}_d']; \n".
//           "  var objm = document.all['{$this->fieldname}_m']; \n".
//           "  var objy = document.all['{$this->fieldname}_y']; \n".
//           "  obj.value = '';\n".
//           "  if (objy) {obj.value = obj.value + objy.value +'-';}\n".
//           "  if (objm) {obj.value = obj.value + objm.value +'-';}\n".
//           "  if (objd) {obj.value = obj.value + objd.value;}\n".
           "  return 1; \n".
           " } </script> \n";
	}
	private function buildDay($field)
	{
		$s = '';
		$string = "<select name='{$this->fieldname}_d' onchange='update_{$this->fieldname}_date()'>\n";
		$string .= "<option value='".date('d')."'></option>\n";
		for($v=1;$v<=31;$v++)
		{
			$s = (intval($this->selected['day']) === $v) ? ' selected="selected"' : '';
			$value = ($field == 'd') ? sprintf("%02d",$v) : $v;
			$string .= '<option value="'.sprintf("%02d",$v).'"'.$s.'>'.$value.'</option>'."\n";
		}
		$string .= '</select>'."\n";
		return $string;
	}

	private function buildMonth($field)
	{
		$s = '';
		$string = "<select name='{$this->fieldname}_m' onchange='update_{$this->fieldname}_date()'>\n";
		$string .= "<option value='".date('m')."'></option>\n";
		foreach($this->month_arr as $k => $v)
		{
			$s = ($this->selected['month'] === strval($k + 1)) ? ' selected="selected"' : '';
			switch ($field) {
					case 'm': { $value = sprintf("%02d",$k+1); break;}
					case 'n': { $value = $k+1; break;}
					case 'F': { $value = $v; break;}
					case 'M': { $value = substr($v,0,3); break;}
			}
			$string .= '<option value="'.sprintf("%02d",$k+1).'"'.$s.'>'.$value.'</option>'."\n";
		}
		$string .= '</select>'."\n";
		return $string;
	}

	private function buildYear($field)
	{
		$s = '';
		$sstring = '';
		$string = "<select name='{$this->fieldname}_y' onchange='update_{$this->fieldname}_date()'>\n";
		$string .= "<option value='".date('Y')."'></option>\n";
		for($v=$this->yearstart;$v<=$this->yearend;$v++)
		{
			$value = (intval(date($field)) + $v);
			$s = (intval($this->selected['year']) == $value) ? ' selected="selected"' : '';
			$data = ($field == 'y') ? sprintf("%02d",$value) : $value;
			if($this->yearrev)
				$sstring = '<option value="'.$value.'"'.$s.'>'.$data.'</option>'."\n" . $sstring;
			else
				$sstring .= '<option value="'.$value.'"'.$s.'>'.$data.'</option>'."\n";
		}
		$string .= $sstring;
		$string .= '</select>'."\n";
		return $string;
	}


/*********************************************************************************
 * Time Functions                                                                *
 *********************************************************************************/
	private function buildTimeScript() {
    return "<script>\n".
           "function update_{$this->fieldname}_date() {\n".
//           "  var obj  = document.all['{$this->fieldname}']; \n".
//           "  var objh = document.all['{$this->fieldname}_h']; \n".
//           "  var objm = document.all['{$this->fieldname}_i']; \n".
//           "  obj.value = objh.value +':'+ objm.value;\n".
           "  return 1; \n".
           " } </script> \n";
	}

 	private function buildHour($field)
	{
		$s = '';
		$string = "<select name='{$this->fieldname}_h' onchange='update_{$this->fieldname}_date()'>\n";
		$string .= '<option value=""></option>'."\n";
		$start =   0;
		$end   =  23;
		for($v=$start;$v<=$end;$v++)
		{
			$s = ($this->selected['hour'] === strval($v)) ? ' selected="selected"' : '';
			$value = $field == 'h' ? sprintf("%02d",$v) : $v;
			$string .= '<option value="'.sprintf("%02d",$v).'"'.$s.'>'.$value.'</option>'."\n";
		}
		$string .= '</select>'."\n";
		return $string;
	}

	private function buildMinute()
	{
		$s = '';
		$string = "<select name='{$this->fieldname}_i' onchange='update_{$this->fieldname}_date()'>\n";
		$string .= '<option value=""></option>'."\n";
		for($v=0;$v<=59;$v++)
		{
			$s = ($this->selected['minute'] === strval($v)) ? ' selected="selected"' : '';
			$value = sprintf("%02d",$v);
			$string .= '<option value="'.$value.'"'.$s.'>'.$value.'</option>'."\n";
		}
		$string .= '</select>'."\n";
		return $string;
	}
}
?>