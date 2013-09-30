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

class import_xml extends AdminView {

  function cp_form (&$data,&$err){
		global $_SHOP;

    echo "<form method='POST' action='{$_SERVER['PHP_SELF']}' enctype='multipart/form-data'>\n";
		$this->form_head(con('import_xml_title'));

		echo "<tr><td class='admin_name'  width='40%'>".con('import_xml_file')."</td>
					<td class='admin_value'><input type='file' name='import_xml_file'></td></tr>";

		echo "
		<tr><td align='center' class='admin_value' colspan='2'>
    	<input type='hidden' name='run' value='{$_REQUEST['run']}'>
  		<input type='submit' name='submit' value='".con('import_xml_submit')."'></td></tr>
		</table></form>";
  }


  function execute (){
    global $_SHOP;
//    print_r($_FILES);echo 'testing';
		if(!empty($_FILES['import_xml_file']) and !empty($_FILES['import_xml_file']['name']) and !empty($_FILES['import_xml_file']['tmp_name'])){
			require_once('classes/class.xmldata.php');
			echo con('import_xml_title')." : ".$_FILES['import_xml_file']['name']." ... ";
	//		flush();
			$result = xmldata::xml2sql($_FILES['import_xml_file']['tmp_name'], true);

			echo con('done');;
			return false;
    }
  }

  function draw (){
    $this->cp_form($_GET,$this->err);
  }

}
?>