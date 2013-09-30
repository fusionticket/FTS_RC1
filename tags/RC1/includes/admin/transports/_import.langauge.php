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
require_once("admin/class.adminview.php");

class import_langauge extends AdminView {

  function cp_form (&$data,&$err){
		global $_SHOP;
		if ($this->result)
      {
      echo $this->result."<br>" ;

      }

    echo "<form method='POST' action='{$_SERVER['PHP_SELF']}' enctype='multipart/form-data'>\n";

		$this->form_head(import_xml_title);
		$this->print_file('import_csv_file', $data, $err, 'xml');
    $this->print_input('langauge', $data, $err);

		echo "
		<tr><td align='center' class='admin_value' colspan='2'>
  	  	<input type='hidden' name='run' value='{$_REQUEST['run']}'>

		<input type='submit' name='submit' value='".import_xml_submit."'></td></tr>
		</table></form>
		<center><span class='error'>{$err['main']}</span></center>";
  }


  function execute (){
    global $_SHOP;
    //print_R($_POST);
    if($_POST['submit']){
      if (empty($_POST['langauge'])){
        $this->err['langauge'] = mandatory;
        return 0;
      }
     // print_r($_FILES['import_xml_file']);
      if(!empty($_FILES['import_csv_file']) and !empty($_FILES['import_csv_file']['name']) and !empty($_FILES['import_csv_file']['tmp_name'])){

        $file = $_FILES['import_csv_file']['tmp_name'];
        $lines = file($file);
        natcasesort($lines);
        $result = "<?php\n";
        foreach  ($lines as $value)
          {
          $v = explode(chr(9),$value,4);
          While ($v[1][0]=="'" or $v[1][0]=="\"") $v[1] = substr($v[1],1);
          While ($v[1][strlen($v[1])-2]==chr(13)) $v[1] = substr($v[1],0,-2);
          While (($v[1][strlen($v[1])-1]=="'" or $v[1][strlen($v[1])-1]=='"') and $v[1][strlen($v[1])-2]<>'/') $v[1] = substr($v[1],0,-1);
          if (isset($lastchar) and $lastchar<>strtolower ($v[0][0]))
            {
            $result .= "\n";
            }
          $line = "define(\"{$v[0]}\",\"".shopDB::escape_string($v[1])."\");\n";
          $pos1=0;
          while (strlen($line)> 130) {
            $pos1 = 90;
//            echo $line;
            while ($pos1>1 and (strpos(' .,<>:;'.chr(10).chr(13).chr(9), $line[$pos1-1]) === false)){ $pos1--;}
//            echo strlen($line).' '.$pos1;
            $pos2 = 90;
            while ($pos2<strlen($line) and (strpos(' .,<>:;'.chr(10).chr(13).chr(9), $line[$pos2-1]) === false)) {$pos2++;}
            echo ' '.$pos2;
            If ((90-$pos1) > ($pos2-90)) $pos1 = $pos2;
//            echo ' '.$pos1.' - ';
            if ($pos1 >105 or $pos1<75) $pos1 = 90;
            $result .= substr($line,0,$pos1)."\n";
            $line   = '        '.substr($line,$pos1);
            }

//          if ($pos1) echo "<br>";
          $result .= $line;
          $lastchar =strtolower($v[0][0]);
          }
        $result .= "?>\n";
        $this->result = $file = $_SHOP->install_dir."/files/site_".strtolower($_POST['langauge']).".inc";
        $this->result = 'File is saved as: '.$this->result;
        file_put_contents($file, $result);
        return false;
      } else {
        $this->err['import_csv_file'] = mandatory;
      }
    }
  }

  function draw (){
    $this->cp_form($_GET,$this->err);
  }

}
?>