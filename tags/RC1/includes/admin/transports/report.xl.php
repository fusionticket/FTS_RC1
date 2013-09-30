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
require_once 'Spreadsheet/Excel/Writer.php';

class report_xl extends AdminView {

  function xl_form (&$data,&$err){
    global $_SHOP;
		   $query="SELECT * FROM Event
            WHERE event_rep LIKE '%sub%'
            and field(event_status, 'trash')=0
              and event_pm_id IS NOT NULL
            {$_SHOP->admin->getEventRestriction()}
            order by find_in_set(event_status,'pub,unpub,nosal'), event_date, event_time";

		if($res=ShopDB::query($query)){
		  while($row=shopDB::fetch_assoc($res)){
			  $event[$row['event_id']]=formatDate($row['event_date']).'-'.formatTime($row['event_time']).' '.$row['event_name'];
			}
		}


    echo "<form method='post' action='{$_SERVER['PHP_SELF']}'>";
    echo "<table class='admin_list' border='0' width='$this->width' cellspacing='1' cellpadding='5'>";
    echo "<tr><td colspan='2' class='admin_list_title'>".con('xl_view_title')."</td></tr>";
		$this->print_select_assoc('export_entrant_event',$data,$err,$event);
    $data['xl_start'] = is($data['xl_start'], date('Y-m-d'));
    $this->print_date('xl_start',$data,$err);
    $this->print_date('xl_end',$data,$err);
    echo "<tr><td align='right' class='admin_value' colspan='2'>

		  	<input type='hidden' name='run' value='{$_REQUEST['run']}'>


		<input type='submit' name='submit' value='".con('generate_xl')."'>
		<input type='reset' name='reset' value='".con('res')."'></td></tr>";
    echo "</table></form>";
  }

  function xl_check (&$data) {
    $this->set_date('xl_start', $data, $err);
    $this->set_date('xl_end',   $data, $err);
    return empty($err);
  }

  function generate_xl ($res,$start,$end){
    GLOBAL $_SHOP;
    $start=substr($start,0,10);
    $end=substr($end,0,10);

    // Creating a workbook
    $workbook = new Spreadsheet_Excel_Writer();

    // sending HTTP headers
    $workbook->setTempDir($_SHOP->tmp_dir);
    $workbook->send("report_sales_".$start.($end?"_".$end:"").".xls");

    // Creating a worksheet
    $worksheet =& $workbook->addWorksheet (con('export_xl'));

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
    $format_titler->setBgColor(26);

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
    $format_header2->setSize(12);
    $format_header2->setAlign('merge');
    $format_header2->setAlign('top');

    $format_left =&$workbook->addFormat(array('Align'=>'left'));
    $format_center =&$workbook->addFormat(array('Align'=>'center'));


    $format_leftb =&$workbook->addFormat(array('Align'=>'left'));
    $format_leftb->setBold();

    $format_rightb =&$workbook->addFormat(array('Align'=>'right'));
    $format_rightb->setBold();


    // The actual data
    $worksheet->hideGridLines();

    $worksheet->setrow(0,25);
    $worksheet->setrow(1,20);
    $worksheet->setrow(2,15);

    $worksheet->setcolumn(0, 0,7);
    $worksheet->setcolumn(1, 1,7);
    $worksheet->setcolumn(2, 2,7);
    $worksheet->setcolumn(3, 3,7);
    $worksheet->setcolumn(4, 4,20);
    $worksheet->setcolumn(5, 5,20);
    $worksheet->setcolumn(6, 6,20);
    $worksheet->setcolumn(9, 9,20);
    $worksheet->write(0, 0, con('export_xl'), $format_header);
    $worksheet->write(0, 1, "", $format_header);
    $worksheet->write(0, 2, "", $format_header);
    $worksheet->write(0, 3, "", $format_header);
    $worksheet->write(0, 4, "", $format_header);
    $worksheet->write(0, 5, "", $format_header);
    $worksheet->write(0, 6, "", $format_header);
    $worksheet->write(0, 7, "", $format_header);
    $worksheet->write(0, 8, "", $format_header);
    $worksheet->write(0, 9, "", $format_header);
    $worksheet->write(0, 10, "", $format_header);
    $worksheet->write(0, 11, "", $format_header);
    $worksheet->write(0, 12, "", $format_header);
    $worksheet->write(0, 13, "", $format_header);
    $worksheet->write(0, 14, "", $format_header);
    $worksheet->write(0, 15, "", $format_header);
    $worksheet->write(0, 16, "", $format_header);
    $worksheet->write(0, 17, "", $format_header);
    $worksheet->write(0, 18, "", $format_header);
    $worksheet->write(0, 19, "", $format_header);
    $worksheet->write(0, 20, "", $format_header);
    $worksheet->write(0, 21, "", $format_header);
    $worksheet->write(0, 22, "", $format_header);
    $worksheet->write(0, 23, "", $format_header);
    $worksheet->write(0, 24, "", $format_header);
    $worksheet->write(0, 25, "", $format_header);
    $worksheet->write(0, 26, "", $format_header);


			$worksheet->write(1, 0, "--", $format_header2);
    		$worksheet->write(1, 1, "", $format_header2);
    $worksheet->write(1,2, "", $format_header2);

    $worksheet->write(2, 0, con('seat_id'),$format_bold);
    $worksheet->write(2, 1, con('rep_ord_id'),$format_bold);
    $worksheet->write(2, 2, con('order_payment'),$format_bold);

    	    $worksheet->write(1, 2, con('user_title'), $format_header2);
    		$worksheet->write(1, 3, "", $format_header2);
    		$worksheet->write(1, 4, "", $format_header2);
    		$worksheet->write(1, 5, "", $format_header2);
    		$worksheet->write(1, 6, "", $format_header2);
    		$worksheet->write(1, 7, "", $format_header2);
    		$worksheet->write(1, 8, "", $format_header2);
    		$worksheet->write(1, 9, "", $format_header2);
    		$worksheet->write(1, 10, "", $format_header2);
    		$worksheet->write(1, 11, "", $format_header2);
    		$worksheet->write(1, 12, "", $format_header2);
    		$worksheet->write(1, 13, "", $format_header2);

    $worksheet->write(2, 3, con('cust_id'),$format_bold, $format_center);
    $worksheet->write(2, 4, con('user_lastname'),$format_bold);
    $worksheet->write(2, 5, con('user_firstname'),$format_bold);
    $worksheet->write(2, 6, con('user_address'),$format_bold);
    $worksheet->write(2, 7, con('user_address1'),$format_bold);
    $worksheet->write(2, 8, con('user_zip'),$format_bold);
    $worksheet->write(2, 9, con('user_city'),$format_bold);
    $worksheet->write(2, 10, con('user_country'),$format_bold);
    	$worksheet->setcolumn(11, 11,13);
    $worksheet->write(2, 11, con('user_phone'),$format_bold);
    	$worksheet->setcolumn(12, 12,13);
    $worksheet->write(2, 12, con('user_fax'),$format_bold);
    	$worksheet->setcolumn(13, 13,30);
    $worksheet->write(2, 13, con('user_email'),$format_bold);

    	    $worksheet->write(1, 14, con('ort'), $format_header2);
    		$worksheet->write(1, 15, "", $format_header2);

    $worksheet->write(2, 14, con('ven_id'),$format_bold);
    	$worksheet->setcolumn(15, 15,30);
    $worksheet->write(2, 15, con('ven_name'),$format_bold);

		    $worksheet->write(1, 16, con('evnt_title'), $format_header2);
    		$worksheet->write(1, 17, "", $format_header2);
    		$worksheet->write(1, 18, "", $format_header2);
    		$worksheet->write(1, 19, "", $format_header2);

    $worksheet->write(2, 16, con('evnt_id'),$format_bold);
		$worksheet->setcolumn(17, 17,30);
    $worksheet->write(2, 17, con('evnt_name'),$format_bold);
    	$worksheet->setcolumn(18, 18,10);
    $worksheet->write(2, 18, con('evnt_date'),$format_bold);
    $worksheet->write(2, 19, con('evnt_time'),$format_bold);

    		$worksheet->write(1, 20, con('cat_title'), $format_header2);
    		$worksheet->write(1, 21, "", $format_header2);
    		$worksheet->write(1, 22, "", $format_header2);
    		$worksheet->write(1, 23, "", $format_header2);
        $worksheet->write(1, 24, "", $format_header2);

    $worksheet->write(2, 20, con('cat_id'),$format_bold);
    	$worksheet->setcolumn(21, 21,20);
    $worksheet->write(2, 21, con('cat_name'),$format_bold);
    $worksheet->write(2, 22, con('cat_price'),$format_bold);
    $worksheet->write(2, 23, con('cat_sold'),$format_bold);

    		$worksheet->write(1, 24, "Discounts", $format_header2);
    		$worksheet->write(1, 25, "", $format_header2);
    		$worksheet->write(1, 26, "", $format_header2);

    $worksheet->write(2, 24, con('disc_name'),$format_bold);
    $worksheet->write(2, 25, con('disc_type'),$format_bold);
    $worksheet->write(2, 26, con('disc_value'),$format_bold);

        $worksheet->write(1, 27, "Handled", $format_header2);
        $worksheet->write(1, 28, "", $format_header2);
    $worksheet->write(2, 27, con('xl_order_place'),$format_bold);
    $worksheet->write(2, 28, con('xl_handling_payment'),$format_bold);

    $i=3;
    while($row=shopDB::fetch_assoc($res)){
      $worksheet->write($i, 0, $row['seat_id']);
      $worksheet->write($i, 1, $row['seat_order_id']);
      $worksheet->write($i, 2,$row['order_payment_status'], $format_price);

      $worksheet->write($i, 3, $row['user_id']);
      $worksheet->write($i, 4, $row['user_lastname']);
      $worksheet->write($i, 5, $row['user_firstname']);
      $worksheet->write($i, 6, $row['user_address']);
      $worksheet->write($i, 7, $row['user_address1']);
      $worksheet->write($i, 8, $row['user_zip']);
      $worksheet->write($i, 9, $row['user_city']);
      $worksheet->write($i, 10, $row['user_country']);
      $worksheet->write($i, 11, $row['user_phone']);
      $worksheet->write($i, 12,$row['user_fax']);
      $worksheet->write($i, 13,$row['user_email']);

      $worksheet->write($i, 14,$row['ort_id']);
      $worksheet->write($i, 15,$row['ort_name']);

      $worksheet->write($i, 16,$row['event_id']);
      $worksheet->write($i, 17,$row['event_name']);
      $worksheet->write($i, 18,$row['event_date']);
      $worksheet->write($i, 19,$row['event_time']);


      $worksheet->write($i, 20,$row['category_id']);
      $worksheet->write($i, 21,$row['category_name']);
      $worksheet->write($i, 22,$row['category_price'], $format_price);

      $worksheet->write($i, 23,$row['seat_price'], $format_price);
      if($row['discount_id']){
        $worksheet->write($i, 24,$row['discount_name']);
        $worksheet->write($i, 25,$row['discount_type']);
        $worksheet->write($i, 26,$row['discount_value'], $format_price);

      }
      $worksheet->write($i, 27,$row['order_place']);
      $worksheet->write($i, 28,$row['handling_payment']);
      $i++;
    }
    // Let's send the file
    $workbook->close();
  }

  function execute (){
   global $_SHOP;

   if($_POST['submit']){
     if(!$this->xl_check($_POST, $this->err)){
       return FALSE;
     }else{
       $query="
               select *, u.*, p.user_lastname pos_office,
                      p.user_city pos_city, p.user_country pos_country
               from Seat LEFT JOIN `Discount` ON seat_discount_id=discount_id
                        LEFT JOIN `Order` on seat_order_id=order_id
                        LEFT JOIN `User` p on order_owner_id= p.user_id
                        LEFT JOIN `User` u on seat_user_id= u.user_id
                        LEFT JOIN `Event` on seat_event_id=event_id
                        LEFT JOIN `Category` on seat_category_id=category_id
                        LEFT JOIN `Ort` on  event_ort_id=ort_id
                        LEFT JOIN `Handling` on order_handling_id = handling_id
               ";
       $where = array('order_id is not null');
       if ($_POST['export_entrant_event']) {
         $where[] = 'event_id = '._esc($_POST['export_entrant_event']);
       }// else {
         if ($_POST['xl_start']) {
           $where[] = 'date(order_date) >= '._esc($_POST["xl_start"]);
         }
         if ($_POST['xl_end']) {
           $where[] = "date(order_date) <= "._esc($_POST["xl_end"]);
         }
      // }
       $where = implode(' and ', $where);
       if ($where) $query .= 'where '.$where;

       if(!$res=ShopDB::query($query)){
         return 0;
       }
       $this->generate_xl($res,$_POST["xl_start"],$_POST["xl_end"]);
       return TRUE;
     }
   }
  }

  function draw (){
    $this->xl_form($_POST, $this->err);
  }
}
?>