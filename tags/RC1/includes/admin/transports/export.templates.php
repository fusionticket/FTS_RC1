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

class export_templates extends AdminView {

  function cp_form (&$data,&$err){
		global $_SHOP;

		$query = "select template_id, template_name, template_type from Template
              order by template_name, template_type";

		if($res=ShopDB::query($query)){
		  while($row=shopDB::fetch_assoc($res)){
			  $event[$row['template_id']]=$row['template_name'].' ('.$row['template_type'].')';
			}
		}

		echo "<form action='".PHP_SELF."' method='GET'>";
		$this->form_head(con('export_xml_event_title'));
//function print_select_assoc ($name,&$data,&$err,$opt,$mult=false){

		$this->print_select_assoc('export_template_id',$data,$err,$event);
		$this->print_input('export_template_file',$data,$err);
		echo "
		<tr><td align='center' class='admin_value' colspan='2'>
  		  	<input type='hidden' name='run' value='{$_REQUEST['run']}'>

		<input type='submit' name='submit' value='". con('export_submit') ."'></td></tr>
		</table></form>";
  }


  function execute (){
    global $_SHOP;

    if($_GET['submit'] and $_GET['export_template_id']>0){
			$id=_esc((int)$_GET['export_template_id']);


    	if($res=ShopDB::query_one_row("select template_name, template_type, template_text from Template where template_id={$id}")){
  			$filename=$_GET['export_template_file'];
  			if(empty($filename)){
  			  $filename='template_'.$res['template_type'].'_'.$res['template_name'].'.xml';
  			}
   			$this->write_header($filename);

  		  $ret ="<templatefile type='{$res['template_type']}'>". $res['template_text'] .'</templatefile>'."\n";
  			echo $ret;
  			return TRUE;
      }
    }
  }

  function draw (){
    $this->cp_form($_GET,$this->err);
  }

	function write_header($filename){
		header('Content-type: text/xml');
		header('Content-Disposition: attachment; filename="'.$filename.'"');
	}
}
?>