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

class export_xml_event extends AdminView {

  function cp_form (&$data,&$err){
		global $_SHOP;

		$query = "select * from Event where event_rep LIKE '%sub%' ORDER BY event_date,event_time,event_name";

		if($res=ShopDB::query($query)){
		  while($row=shopDB::fetch_assoc($res)){
			  $event[$row['event_id']]=$row['event_name'].' ('.formatDate($row['event_date']).'-'.formatTime($row['event_time']).')';
			}
		}

		echo "<form action='{$_SERVER["PHP_SELF"]}' method='GET'>";
		$this->form_head(con('export_xml_event_title'));
//function print_select_assoc ($name,&$data,&$err,$opt,$mult=false){

		$this->print_select_assoc('export_xml_event_event',$data,$err,$event);
		$this->print_input('export_xml_event_file',$data,$err);
		echo "
		<tr><td align='center' class='admin_value' colspan='2'>
  		  	<input type='hidden' name='run' value='{$_REQUEST['run']}'>

		<input type='submit' name='submit' value='".con('export_xml_event_submit')."'></td></tr>
		</table></form>";
  }


  function execute (){
    global $_SHOP;

    if($_GET['submit'] and $_GET['export_xml_event_event']>0){
			require_once('classes/class.xmldata.php');
			$event_id=_esc((int)$_GET['export_xml_event_event']);

			$what[]=array(
			'table'=>'Ort',
      'pk'=>'user_id',
			'query'=>"SELECT Ort.* FROM `Event`,Ort WHERE `event_ort_id`=ort_id and event_id=$event_id");

			$what[]=array(
			'table'=>'Event_group',
      'pk'=>'event_group_id',
			'query'=>"SELECT Event_group.* FROM `Event` left join Event_group on `Event`.`event_group_id`= Event_group.event_group_id
                WHERE `Event`.`event_group_id` is not null and event_id=$event_id");

			$what[]=array(
			'table'=>'Event',
      'pk'=>'event_id',
			'query'=>"select * from Event where event_id=$event_id");

			$what[]=array(
			'table'=>'PlaceMap2',
      'pk'=>'pm_id',
			'query'=>"SELECT * FROM `PlaceMap2` WHERE `pm_event_id`=$event_id");

			$what[]=array(
			'table'=>'Category',
      'pk'=>'category_id',
			'query'=>"SELECT * FROM `Category` WHERE `category_event_id`=$event_id");

			$what[]=array(
			'table'=>'PlaceMapZone',
      'pk'=>'pmz_id',
			'query'=>"SELECT PlaceMapZone.* FROM `PlaceMapZone` left join PlaceMap2 on `pmz_pm_id`=pm_id
                WHERE `pmz_pm_id` is not null and pm_event_id=$event_id");

			$what[]=array(
			'table'=>'PlaceMapPart',
      'pk'=>'pmp_id',
			'query'=>"SELECT * FROM `PlaceMapPart` WHERE `pmp_event_id`=$event_id");

			$what[]=array(
			'table'=>'Discount',
      'pk'=>'discount_id',
			'query'=>"SELECT * FROM `Discount` WHERE `discount_event_id`=$event_id");

			$what[]=array(
			'table'=>'User',
			'pk'=>'user_id',
			'query'=>"SELECT  DISTINCT User.* FROM Seat left join User on seat_user_id=user_id
                WHERE seat_event_id=$event_id  and seat_user_id is not null");

			$what[]=array(
			'table'=>'Order',
			'pk'=>'order_id',
			'query'=>"SELECT  DISTINCT `Order`.* FROM Seat left join `Order` on seat_order_id=order_id
                WHERE seat_event_id=$event_id and seat_order_id is not null");

			$what[]=array(
			'table'=>'Seat',
			'pk'=>'seat_id',
			'query'=>"SELECT * FROM `Seat` WHERE `seat_event_id`=$event_id");

			$filename=$_GET['export_xml_event_file'];
			if(empty($filename)){
			  $filename='event'.(int)$_GET['export_xml_event_event'].'.xml';
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