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
require_once("admin/class.adminview.php");
require_once 'Spreadsheet/Excel/Writer.php';

class report_eventbarcodes extends AdminView {

   var $query = '';

  function xl_form (&$data,&$err){
		global $_SHOP;

    $query = "select * from Event
              where event_rep LIKE '%sub%'
              {$_SHOP->admin->getEventRestriction()}
              and event_pm_id IS NOT NULL
              and field(event_status, 'trash','unpub')=0
              ORDER BY event_date,event_time,event_name";

		if($res=ShopDB::query($query)){
		  while($row=shopDB::fetch_assoc($res)){
			  $event[$row['event_id']]=formatDate($row['event_date']).'-'.formatTime($row['event_time']).' '.$row['event_name'];
			}
		}
  //  var_dump($event);
		echo "<form action='' method='post'>";
		$this->form_head(con('export_eventbarcodes_title'));
		$this->print_select_assoc('export_eventbarcodes_event',$data,$err,$event);
		echo "
		<tr><td align='right' class='admin_value' colspan='2'>
  		  	<input type='hidden' name='run' value='{$_REQUEST['run']}'>
      <input type='submit' name='submit' value='".con('export_xml_event_submit')."'>
	  	<input type='reset' name='reset' value='".con('res')."'></td></tr>
		</table></form>";
  }

  function generate_xl($res, $event, $event_id){
    global $_SHOP;
//print_r($this->query);


    $workbook = new Spreadsheet_Excel_Writer();
    // sending HTTP headers
    $workbook->setTempDir($_SHOP->tmp_dir);
    $workbook->send("Barcodes_for_".(int)$event.".xls");
    // Creating a worksheet
    $worksheet =& $workbook->addWorksheet('barcodes');

    $format_bold =& $workbook->addFormat();
    $format_bold->setBold();

    $format_title =& $workbook->addFormat();
    $format_title->setBold();
    $format_title->setPattern(1);
    $format_title->setFgColor(26);
    $format_title->setbottom(1);

    $format_titler =& $workbook->addFormat(array('Align'=>'right'));
    $format_titler->setBold();
    $format_titler->setPattern(1);
    $format_titler->setFgColor(26);
    $format_titler->setbottom(1);

    $format_price =& $workbook->addFormat();
    $format_price->setNumFormat('#,##0.00;-#,##0.00');
    $format_price->setAlign('right');

    $format_priceb =& $workbook->addFormat();
    $format_priceb->setNumFormat('#,##0.00;-#,##0.00');
    $format_priceb->setAlign('right');
    $format_priceb->setBold();


    $format_header =& $workbook->addFormat();
    $format_header->setBold();
    $format_header->setSize(15);
    $format_header->setAlign('merge');
    $format_header->setAlign('top');

    $format_header2 =& $workbook->addFormat();
    $format_header2->setBold();
    $format_header2->setAlign('merge');
    $format_header2->setAlign('top');

    $format_left =&$workbook->addFormat(array('Align'=>'left'));

    $format_leftb =&$workbook->addFormat(array('Align'=>'left'));
    $format_leftb->setBold();

    $format_rightb =&$workbook->addFormat(array('Align'=>'right'));
    $format_rightb->setBold();
    $format_titler->setBgColor(26);

    $query = "select * from Event where event_id ="._esc($event);

		$row=ShopDB::query_one_row($query);
//    $dta = print_r($row, true);
    // The actual data
    $worksheet->hideGridLines();

    $worksheet->setrow(0,30);
    $worksheet->setrow(1,15);
    $worksheet->setcolumn(0, 0,20);
    $worksheet->setcolumn(1, 2,25);
    $worksheet->setcolumn(4, 4,18);
    $worksheet->write(0, 0, con('export_eventbarcodes_title'), $format_header);
    $worksheet->write(0, 1, "", $format_header);
    $worksheet->write(0, 2, "", $format_header);
    $worksheet->write(0, 3, "", $format_header);


    $worksheet->write(2, 0, con('eventbarcodes_date'),$format_bold);
    $worksheet->write(2, 1, formatDate($row['event_date']));
    $worksheet->write(3, 0, con('eventbarcodes_time'),$format_bold);
    $worksheet->write(3, 1, formatTime($row['event_time']));

    $worksheet->write(4, 0, con('export_eventbarcodes_code'),$format_title);
    $worksheet->write(4, 1, con('export_eventbarcodes_fullname'),$format_title);
    $worksheet->write(4, 2, con('export_eventbarcodes_order'),$format_title);
    $worksheet->write(4, 3, con('export_eventbarcodes_seat'),$format_title);
    $i = 5;
    $orders = array();

    while($row=shopDB::fetch_assoc($res)){
      if (!in_array($row['seat_order_id'], $orders )) {
        $orders[$row['seat_order_id']] = Order::load($row['seat_order_id']);
      }

      $row['barcode_text']= Seat::encodeBarcode($orders[$row['seat_order_id']], $row);

      $worksheet->writeString($i, 0, $row['barcode_text'],$format_left );
      $worksheet->writeString($i, 1, $row['user_firstname'].' '.$row['user_lastname'],$format_left);
      $worksheet->writeString($i, 2, $row['seat_order_id'],$format_left);
      if ($row['category_numbering'] == 'both') {
        $seat = "{$row['seat_row_nr']} - {$row['seat_nr']}";
      } elseif ($row['category_numbering'] == 'rows') {
        $seat = "{$row['seat_row_nr']}";
      } elseif ($row['category_numbering'] == 'seat'){
        $seat = "{$row['seat_nr']}";
      } else $seat = con('export_eventbarcodes_seat_free');

      $worksheet->writeString($i, 3, $seat, $format_left);

      $i++;
    }
    $workbook->close();
  }

  function execute (){
    global $_SHOP;

    if(isset($_POST['submit'])) {// and $_GET['export_xl2_event']>0){

	    $event_id=_esc((int)$_POST['export_eventbarcodes_event']);
      $this->query="SELECT Seat.*,  User.*, category_numbering
                    FROM `Event` left join `Seat` on `seat_event_id` = `event_id`
                                 left join `User` on `seat_user_id` = `user_id`
                                 left join `Category` on `seat_category_id` = `category_id`
                    WHERE event_id = {$event_id}
                    and   seat_status = 'com'
                    order by seat_order_id, seat_id";
      if(!$res=ShopDB::query($this->query)){
        return 0;
      }
      $this->generate_xl($res, (int)$_POST['export_eventbarcodes_event'],$event_id);
      return TRUE;
    }
  }

  function draw (){
    $this->xl_form($_GET,$this->err);
  }
}
?>