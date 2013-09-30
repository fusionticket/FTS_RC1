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

class export_cc extends AdminView {

  function cc_form (&$data,&$err){
		global $_SHOP;
		$query = "select count(*) as export_cc_count from CC_Info";

		$data=ShopDB::query_one_row($query);

		echo "<form action='{$_SERVER["PHP_SELF"]}' method='GET'>";
		$this->form_head(export_cc_title);
		$this->print_field('export_cc_count',$data);
		if($data['export_cc_count']){
      echo "<tr><td align='center' class='admin_value' colspan='2'>

	  	<input type='hidden' name='export_type' value='cc'>
			<input type='submit' name='submit' value='".export_cc_submit."'></td></tr>";
		}
    echo "</table></form>";
  }


  function execute (){
    global $_SHOP;

    if($_GET['submit']){
      $query="select * from CC_Info";

		  if(!$res=ShopDB::query($query) or !shopDB::num_rows($res)){
         return 0;
      }

			$this->write_header();

			while($row=shopDB::fetch_row($res)){
				echo $row[0].':'.$row[1]."\n";
			}
			return TRUE;
    }
  }

  function draw (){
    $this->cc_form($_GET,$this->err);
  }

	function write_header(){
		header('Content-type: text/plain');
		header('Content-Disposition: attachment; filename="cc_info.txt"');

	}
}
?>