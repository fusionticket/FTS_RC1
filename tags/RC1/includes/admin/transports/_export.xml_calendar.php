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

class export_xml_calendar extends AdminView {

  function cp_form (&$data,&$err){
		global $_SHOP;

		$query = "select * from Event where event_rep LIKE '%main%' AND event_main_id is null ORDER BY event_date,event_time,event_name";

		if($res=ShopDB::query($query)){
		  while($row=shopDB::fetch_assoc($res)){
			  $event[$row['event_id']]=$row['event_name'].' ('.formatDate($row['event_date']).'-'.formatTime($row['event_time']).')';
			}
		}

		echo "<form action='{$_SERVER["PHP_SELF"]}' method='GET'>";
		$this->form_head(con('export_xml_event_title'));
		//function print_select_assoc ($name,&$data,&$err,$opt,$mult=false){

		$this->print_select_assoc('export_xml_calendar_event',$data,$err,$event);// choose an event
		$this->print_input('export_xml_event_file',$data,$err);
		echo "
		<tr><td align='center' class='admin_value' colspan='2'>
  		  	<input type='hidden' name='run' value='{$_REQUEST['run']}'>

		<input type='submit' name='submit' value='".con('export_xml_event_submit')."'></td></tr>
		</table></form>";
  }


  function execute (){
    global $_SHOP;

    if($_GET['submit'] and $_GET['export_xml_calendar_event']>0){
			require_once('classes/class.xmldata.php');
			$event_id=(int)$_GET['export_xml_calendar_event'];


			$what[]=array(
			'table'=>'event',
			'query'=>"select event_id as eventid1 ,event_date as date,event_time as time,event_name as tilte,event_image as image,
                       event_text as description,event_url as link from Event where event_main_id='$event_id'");

			/*echo "select event_id as eventid ,event_date as date,event_time as time,event_name as tilte,event_image as image,event_text as description,event_url as link from Event where event_main_id='$event_id' and event$org";
			exit();*/
			$filename=$_GET['export_xml_event_file'];
			if(empty($filename)){
			  //$filename='event'.$event_id.'.xml';
			  $filename='events.xml';
			}
			//echo $filename;
			$this->write_header($filename);

			xmldata::sql2xml_all_new($what,SQL2XML_OUT_ECHO);

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