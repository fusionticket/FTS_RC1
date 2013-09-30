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

class report_entrant extends AdminView {

   var $query = '';

  function xl_form (&$data,&$err){
		global $_SHOP;

    $query = "select * from Event left join `Ort` on event_ort_id= ort_id
              where event_rep LIKE '%sub%'
              {$_SHOP->admin->getEventRestriction()}
              and event_pm_id IS NOT NULL
              and field(event_status, 'trash','unpub','nosal')=0
              ORDER BY event_date,event_time,event_name";

		if($res=ShopDB::query($query)){
		  while($row=shopDB::fetch_assoc($res)){
			  $event[$row['event_id']]=formatDate($row['event_date']).'-'.formatTime($row['event_time']).' '.$row['event_name'];
			}
		}

		echo "<form action='{$_SERVER[PHP_SELF]}' method='get'>";
    if (empty($data['export_entrant_NotSended'])) $data['export_entrant_NotSended'] = '1';
		$this->form_head(con('export_entrant_title'));
		$this->print_select_assoc('export_entrant_event',$data,$err,$event);
		$this->print_checkbox('export_entrant_NotSended',$data,$err);
		$this->print_checkbox('export_entrant_withseats',$data,$err);

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
    $workbook->send("Tickets_for_".$event.".xls");
    // Creating a worksheet
    $worksheet =& $workbook->addWorksheet('Tickets');

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
    $worksheet->setcolumn(0, 0,15);
    $worksheet->setcolumn(1, 2,25);
    $worksheet->setcolumn(4, 4,18);
    $worksheet->write(0, 0, con('export_entrant_title'), $format_header);
    $worksheet->write(0, 1, "", $format_header);
    $worksheet->write(0, 2, "", $format_header);
    $worksheet->write(0, 3, "", $format_header);
    $worksheet->write(0, 4, "", $format_header);
    if (is($_GET['export_entrant_withseats'], false)) {
      $worksheet->write(0, 5, "", $format_header);
    }
    $worksheet->write(1, 0, $row['event_name'],$format_header2 );
    $worksheet->write(1, 1, "",$format_header2 );
    $worksheet->write(1, 2, "",$format_header2 );
    $worksheet->write(1, 3, "",$format_header2 );
    $worksheet->write(1, 4, "",$format_header2 );
    if (is($_GET['export_entrant_withseats'], false)) {
      $worksheet->write(1, 5, "", $format_header2);
    }


    $worksheet->write(2, 0, "");
    $worksheet->write(2, 1, con('entrant_date'),$format_bold);
    $worksheet->write(2, 2, formatDate($row['event_date']));
    $worksheet->write(2, 3, con('entrant_time'),$format_bold);
    $worksheet->write(2, 4, formatTime($row['event_time']));

    $worksheet->write(3, 0, con('export_entrant_order_id'),$format_title);
    $worksheet->write(3, 1, con('export_entrant_fullname'),$format_title);
    $worksheet->write(3, 2, con('export_entrant_city'), $format_title);
    $worksheet->write(3, 3, con('export_entrant_tickets'),$format_title);
    $worksheet->write(3, 4, con('export_entrant_price'),$format_title);

    $export_entrant_withseats = is($_GET['export_entrant_withseats'], false);
    if ($export_entrant_withseats) {
      $worksheet->write(3, 5, con('export_entrant_seats'),$format_title);
    }

    $totprice=0.0;
    $totseats=0;
    $i=4;
    while($row=shopDB::fetch_assoc($res)){
      $worksheet->write($i, 0, $row['order_id'],$format_left );
      $worksheet->write($i, 1, $row['user_firstname'].' '.$row['user_lastname'],$format_left);
      $worksheet->write($i, 2, $row['user_city'], $format_left);

      $worksheet->write($i, 3 ,$row['seat_count'], $format_left);
      $totseats += $row['seat_count'];
      $price ='';
      If ($row['order_payment_status'] == 'paid') {
        $price .= con('order_type_paid');
      } else {
        $price .= $row['seat_totall_price'];
        $totprice += $row['seat_totall_price'];
      }
      If ($row['order_shipment_status'] == 'send') {
        $price .= ' ('.con('order_status_send').')';
      }

      $worksheet->write($i, 4,$price, $format_price);

      $seats = '';
      if ($export_entrant_withseats) {
        $query="SELECT DISTINCT `seat_row_nr`, `seat_nr`, `category_numbering`
                      FROM `Seat` left join `Category` on `seat_category_id` = `category_id`
                      WHERE seat_order_id = {$row['order_id']}
                      and   seat_event_id = "._esc($event);
        if ($res_seat=ShopDB::query($query)){
          while($seat=shopDB::fetch_assoc($res_seat)){
      //      $seats .= "|{$seat['category_numbering']}";
            if ($seat['category_numbering'] == 'both') {
              $seats .= "{$seat['seat_row_nr']}-{$seat['seat_nr']} ";
            } elseif ($seat['category_numbering'] == 'seat') {
              $seats .= "{$seat['seat_nr']} ";
            } elseif ($seat['category_numbering'] == 'rows') {
              $seats .= "{$seat['seat_row_nr']} ";
            }
          } //echo $seats;
        }
      }
      $worksheet->write($i, 5, $seats, $format_left);

      $i++;
    }
    $worksheet->write($i, 0, "");
    $worksheet->write($i, 1, "",$format_bold);
    $worksheet->write($i, 2, con('export_entrant_totalprice')." :",$format_bold);

    $worksheet->write($i, 3, $totseats, $format_leftb);
    $worksheet->write($i, 4, $totprice, $format_priceb);

/*
    for ($i  = 1; $i <= 65; $i++) {
      $worksheet->write($i, 7 ,$i,$workbook->addFormat(array('FgColor'=>$i, 'pattern'=>1)));
    }
*/
    // Let's send the file

    $workbook->close();
  }

  function execute (){
    global $_SHOP;

    if($_GET['submit']) {// and $_GET['export_xl2_event']>0){

			$event_id=_esc((int)$_GET['export_entrant_event']);
      if ($_GET['export_entrant_NotSended']== 1) $org = " and `Order`.order_shipment_status='none'";
      $this->query="SELECT DISTINCT `Order`.order_id,`Order`.order_tickets_nr,`Order`.order_total_price,`Order`.order_shipment_status,`Order`.order_payment_status,`Order`.order_fee,
                              User.user_firstname, User.user_lastname, User.user_city, count(Seat.seat_order_id) AS seat_count, sum(seat_price) as seat_totall_price
              FROM  Seat left JOIN `Order` ON (`Order`.order_id = Seat.seat_order_id)
                         left JOIN User ON (`Order`.order_user_id = User.user_id)
              WHERE `Order`.order_status = 'ord' and seat_event_id={$event_id}
              {$org}
              GROUP BY `Order`.order_id, `Order`.order_tickets_nr, `Order`.order_total_price, `Order`.order_shipment_status, `Order`.order_payment_status, `Order`.order_status,
                       `Order`.order_fee, User.user_firstname, User.user_lastname, User.user_city
              ORDER BY User.user_firstname, User.user_lastname";
      if(!$res=ShopDB::query($this->query)){
        return 0;
      }
    $this->generate_xl($res, (int)$_GET['export_entrant_event'],$event_id);
    return TRUE;
    }
  }

  function draw (){
    $this->xl_form($_GET,$this->err);
  }
}
?>