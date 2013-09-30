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

class import_template extends AdminView {

  function cp_form (&$data,&$err){
		global $_SHOP;

    echo "<form method='POST' action='{$_SERVER['PHP_SELF']}' enctype='multipart/form-data'>\n";

		$this->form_head(con('import_xml_title'));
		$this->print_file('import_xml_file', $data, $err, 'xml');
    $this->print_input('template_name', $data, $err);

		echo "
		<tr><td align='center' class='admin_value' colspan='2'>
  	<input type='hidden' name='run' value='{$_REQUEST['run']}'>
		<input type='submit' name='submit' value='".con('import_xml_submit')."'></td></tr>
		</table></form>
		<center><span class='error'>{$err['main']}</span></center>";
  }


  function execute (){
    global $_SHOP;
    if($_POST['submit']){
      if (empty($_POST['template_name'])){
        $this->err['template_name'] = con('mandatory');
        return 0;
      }
     // print_r($_FILES['import_xml_file']);
  		if(!empty($_FILES['import_xml_file']) and !empty($_FILES['import_xml_file']['name']) and !empty($_FILES['import_xml_file']['tmp_name'])){

        $file = $_FILES['import_xml_file']['tmp_name'];
/*  	    if (!($fp = fopen($file, "r"))) {
  		     $this->err['main'] = "could not open XML input file {$_FILES['import_xml_file']['name']}";
  		     return 0;
        } */
        $lines = file_get_contents($file);


        if (!preg_match('#<templatefile type=[\'"](.*?)[\'"]>(.*?)</templatefile>#s', $lines, $m)) {
           $this->err['main'] = "could not read XML: ".preg_last_error();
           echo _ESC(print_r($m, true));
  		     return 0;
        }
        if (empty($m[1])){
          $this->err['main'] = template_type_missing;
        }
        if (empty($m[2])){
          $this->err['main'] = template_text_missing;
        }
        If (isset($this->err['main'])) return 0;

        $query = "INSERT Template (template_name,template_type,template_text,template_status)
                  VALUES (" . _ESC($_POST['template_name']) . "," . _ESC($m[1]) . ",
                          " . _ESC($m[2]) . ",'new')";
        if (!ShopDB::query($query)){
           $this->err['main'] = con('error').':'.$_SHOP->db_error;
           return 0;
        }
  			echo import_template_title." : ".$_FILES['import_template_file']['name']." ... ";
  			echo done;
  			return false;
      } else {
        $this->err['import_xml_file'] = con('mandatory');
      }
    }
  }

  function draw (){
    $this->cp_form($_GET,$this->err);
  }

}
?>