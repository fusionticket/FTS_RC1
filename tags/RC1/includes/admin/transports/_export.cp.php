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

class export_cp extends AdminView {

  function cp_form (&$data,&$err){
		global $_SHOP;

		$query = "select * from Event where event_rep LIKE '%sub%'";

		if($res=ShopDB::query($query)){
		  while($row=shopDB::fetch_array($res)){
			  $event[$row['event_id']]=$row['event_name'].' ('.$row['event_date'].')';
			}
		}

		echo "<form action='{$_SERVER["PHP_SELF"]}' method='GET'>";
		$this->form_head(export_cp_title);
//function print_select_assoc ($name,&$data,&$err,$opt,$mult=false){

		$this->print_select_assoc('export_cp_event',$data,$err,$event);
		$this->print_field('export_cp_file',$data,$err);
		echo "
		<tr><td align='center' class='admin_value' colspan='2'>
  		  	<input type='hidden' name='run' value='{$_REQUEST['run']}'>

		<input type='submit' name='submit' value='".export_cp_submit."'></td></tr>
		</table></form>";
  }


  function execute (){
    global $_SHOP;

    if($_GET['submit'] and $_GET['export_cp_event']>0){
			require_once('classes/class.xmldata.php');
			$event_id=_esc((int)$_GET['export_cp_event'],false);

			$what[]=array(
			'table'=>'Event',
			'query'=>"select * from Event where event_id='$event_id'");

			$what[]=array(
			'table'=>'PlaceMap2',
			'query'=>"SELECT * FROM `PlaceMap2` WHERE `pm_event_id`='$event_id'");

			$what[]=array(
			'table'=>'Category',
			'query'=>"SELECT * FROM `Category` WHERE `category_event_id`='$event_id'");

			$what[]=array(
			'table'=>'PlaceMapPart',
			'query'=>"SELECT * FROM `PlaceMapPart` WHERE `pmp_event_id`='$event_id'");

			$what[]=array(
			'table'=>'PlaceMapZone',
			'query'=>"SELECT PlaceMapZone.* FROM `PlaceMapZone`,PlaceMap2 WHERE `pmz_pm_id`=pm_id and pm_event_id=$event_id");

			$what[]=array(
			'table'=>'Discount',
			'query'=>"SELECT * FROM `Discount` WHERE `discount_event_id`='$event_id'");

			$what[]=array(
			'table'=>'Ort',
			'query'=>"SELECT Ort.* FROM `Event`,Ort WHERE `event_ort_id`=ort_id and event_id='$event_id'");

			$what[]=array(
			'table'=>'Seat',
			'query'=>"SELECT * FROM `Seat` WHERE `seat_event_id`='$event_id'");

			$what[]=array(
			'table'=>'Order',
			'pk'=>'order_id',
			'query'=>"SELECT  DISTINCT `Order`.* FROM Seat,`Order` WHERE seat_event_id='$event_id' and seat_order_id=order_id");

			$what[]=array(
			'table'=>'User',
			'pk'=>'user_id',
			'query'=>"SELECT  DISTINCT User.* FROM Seat,User WHERE seat_event_id='$event_id' and seat_user_id=user_id");

			$filename=$_GET['export_cp_file'];
			if(empty($filename)){
			  $filename='event'.$event_id.'.xml';
			}
			$this->write_header($filename);

			xmldata::sql2xml_all($what,SQL2XML_OUT_ECHO);

			return TRUE;
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