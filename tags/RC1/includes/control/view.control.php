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


class ControlView extends AdminView{
  var $title = "tickettaker";

  function table(){
    global $_SHOP;
   	$eventlinks = $_SHOP->admin->getEventRestriction();
   //	$eventlinks = ($eventlinks)?$eventlinks:'-1';

    $query="select SQL_CALC_FOUND_ROWS * from Event left join Ort on event_ort_id=ort_id
              where event_status != 'unpub'
              AND   event_rep LIKE '%sub%'
    	       {$eventlinks}
              order by event_date, event_time ";
      if(!$events=ShopDB::query($query)){
        user_error(shopDB::error());
        return 0;
      }

  	$part_count=ShopDB::num_rows($events);

  	if($part_count==0){
  		echo "<br><br><br><br><br><br> <h1><center>",con("no_event_sets"),'</center></h1><br><br><br><br><br><br>';
  	}else{

      echo "
        <table width='100%' class='admin_list'  cellpadding='2'>
          <tr><td colspan='6' class='admin_list_title' align='center'>".con('control_events_list')."</td></tr>
          <tr class='admin_list_header'>
            <td width='200' >".con('event')."</td>
            <td width='200' >".con('date')."</td>
            <td width='200' >".con('ort')."</td>
            <td width='50' align='right'>".con('free')."</td>
            <td width='50' align='right'>".con('com')."</td>
            <td width='50' align='right'>".con('checked')."</td>
          </tr>";
      $alt = 0;
      while($event=shopDB::fetch_assoc($events)){
        $query_ev="SELECT seat_status, COUNT(*) as count FROM Seat where
                   seat_event_id='{$event['event_id']}' GROUP BY seat_status";
        if(!$status=ShopDB::query($query_ev)){
          return 0;
        }

        $ev_stat=array();

        while($stat=shopDB::fetch_assoc($status)){
          $ev_stat[$stat["seat_status"]]=$stat["count"];
        }
        $edate=formatDate($event["event_date"]);
        $etime=formatTime($event["event_time"]);

        echo "
          <tr class='admin_list_row_$alt'>
      	    <td width='200' class='event_list_td' valign='top'>
               <a href='{$_SERVER['PHP_SELF']}?event_id={$event['event_id']}'>{$event['event_name']}</a>
            </td>
            <td width='200' class='event_list_td' valign='top'>$edate - $etime</td>
            <td width='200' class='event_list_td' valign='top'>{$event['ort_name']} - {$event['ort_city']} - {$event['ort_country']}</td>
            <td width='50' class='event_list_td' align='right' valign='top'>".$ev_stat["free"]."</td>
            <td width='50' class='event_list_td' align='right' valign='top'>".$ev_stat["com"]."</td>
            <td width='50' class='event_list_td' align='right' valign='top'>".$ev_stat["check"]."</td>
          </tr>";
        $alt = ($alt + 1) % 2;
      }
      echo "</table>";
    }
  }

  function checkBarcode(){
    global $_SHOP;
   $query="select * from Event
             where event_id = "._esc($_SESSION['tickettaker_event']);
    $event=ShopDB::query_one_row($query);
    echo "<script type='text/javascript'>
        $(document).ready(function() {
            // focus on the first text input field in the first field on the page
            $(\"input[type='text']:first\", document.forms[0]).focus();
        });
      </script>
      <center>
        <div class='control_form'>
          <br>";

  if ($_SESSION['event']->event_image && file_exists($_SHOP->files_path+$_SESSION['event']->event_image)) {
         echo "
            <img src='$_SHOP->files_url{$_SESSION['event']->event_image}' width='200'>
            <br>
            <br>";
          }
    echo"
            <strong>{$_SESSION['event']->event_name}<strong><br><br>
  	      <form method='POST' action='index.php' name='f' onSubmit='this.submit.disabled=true;return true;'>
            <input id='codebar' type=text name='codebar' value='' size='40' autofocus='autofocus'>
            <input type='submit' name='submit' value='".con('check')."' >
            <input type='reset' name='reset' value='".con('res')."'>
          </form><br>
        </div>
      </center><br><br>";

    if(!empty($_POST['codebar'])){

      list($seat_id,$ticket_code)= Seat::decodeBarcode($_POST['codebar']);

      $query="select seat_event_id, seat_user_id, discount_name, seat_status, seat_nr, seat_row_nr,
                     seat_checked_by, seat_checked_date,
                     category_numbering, category_name, category_color,
                     pmz_name, order_payment_status, order_status,order_id,
                     admin_login, admin_realname, user_firstname, user_lastname
              from `Seat` LEFT JOIN `PlaceMapZone` ON seat_zone_id=pmz_id
                          LEFT JOIN `Category` on seat_category_id=category_id
                          LEFT JOIN `Order` on seat_order_id= order_id
                          LEFT JOIN `Discount` on discount_id= seat_discount_id
                          LEFT JOIN `Admin` on seat_checked_by= admin_id
                          left JOIN `User`   on user_id = seat_user_id
              where seat_id="._esc($seat_id)."
  	          AND   seat_code="._esc($ticket_code);
      if(!$ticket=ShopDB::query_one_row($query)){
        return $this->showerror('ticket_not_found',$ticket);
      } elseif($ticket['seat_event_id'] !==$_SESSION['tickettaker_event']){
        return $this->showerror('not_valid_event',$ticket);
      } elseif($ticket['seat_status']=='check'){
        return $this->showerror('ticket_already_checked',$ticket);
      } elseif($ticket['seat_status']=='free'){
        return $this->showerror('place_not_commanded',$ticket);
      } elseif($ticket['seat_status']=='res'){
        return $this->showerror('place_only_reserved',$ticket);
      } elseif(!in_array($ticket['order_status'],array('ord','pros'))){
        return $this->showerror('order_is_not_valid',$ticket);
      } elseif(!in_array($ticket['order_payment_status'],array('paid'))){
        return $this->showerror('order_is_not_paid',$ticket);
      }
      if ($ticket['discount_name']) {
        $discount_txt = " ( ".$ticket['discount_name']." )";
      }

      if($ticket['category_numbering']=='both'){
        $place_nr=con('place_nr')." ".$ticket['seat_row_nr']."-".$ticket['seat_nr'];
      }else if($ticket['category_numbering']=='rows'){
        $place_nr=con('rang_nr')." ".$ticket['seat_row_nr'];
      }else if($ticket['category_numbering']=='seat'){
        $place_nr=con('place_nr')." ".$ticket['seat_nr'];
      }else if($ticket['category_numbering']=='none'){
        $place_nr=con('place_without_nr');
      }
		$cust_full_name = $cust_fname . " " . $cust_sname;

      echo "<center>
          <table class='check' width='700' cellpadding='5'>
            <tr>
              <td align='center' valign='middle' width='150'><img src='../images/bigsmile.gif'></td>
	            <td align='center' class='success'>
                <table border='0' width='350'>
                  <tr><td align='center' class='success'>".con('check_success')."</td></tr>
                  <tr><td class='value' align='center'>{$ticket['category_name']} {$ticket['pmz_name']} {$discount_txt}</td></tr>
                  <tr><td class='value' align='center'> $place_nr </td></tr>
				  <tr><td class='value' align='center'> Ordered by: {$cust_full_name}</td></tr>
                  <tr><td colspan='2'> &nbsp;</td></tr>";

      if(isset($ticket['category_color'])){
        echo "
                <tr><td  bgcolor='{$ticket['category_color']}' style='border: #999999 1px dashed;'> &nbsp </td></tr>";
      }

      echo  "
                  <tr><td > &nbsp; </td></tr>
                </table>
              </td>
            </tr>
          </table></center><br><br>";
      OrderStatus::statusChange($ticket['order_id'],'TicketTaker',con('seat_checked').$seat_id,'tickettaker::checkticket');
      $query="UPDATE Seat set seat_status='check', seat_checked_date=CURRENT_TIMESTAMP, seat_checked_by="._esc($_SHOP->admin->admin_id)." where seat_id="._esc($seat_id);

      if(!ShopDB::query($query)){
        echo "<div class='contol-err'>".con('place_status_not_updated')."</div>";
        return false;
      }

    }
    return true;
  }

  function draw (){
    global $_SHOP;
    // var_dump($_SHOP->event_ids, $_SHOP->admin->admin_status);
    if($_GET['event_id'] && (in_array($_GET['event_id'],explode(',', $_SHOP->event_ids))||$_SHOP->admin->admin_status=='admin')){
       $_SESSION['tickettaker_event']=$_GET['event_id'];
       require_once ("classes/model.event.php");
       $event = Event::load($_GET['event_id']);
       $_SESSION['event']=$event;
  	}

    if(!isset($_SESSION['tickettaker_event']) || $_GET['action']=="change_event"){
      unset($_SESSION['tickettaker_event']);
      unset($_SESSION['event']);
      $this->table();
    } elseif ($this->checkBarcode()) {
      echo "<embed src='{$_SHOP->images_url}beep_ok.mp3' autostart='true' loop='false' width='0' height='0'></embed>";
    } else {
      echo "<embed src='{$_SHOP->images_url}beep_error.mp3' autostart='true' loop='false' width='0' height='0'></embed>";
    }

  }

  function showerror($message, $ticket){
    GLOBAL $_SHOP;
 //   var_dump($ticket);
    OrderStatus::statusChange($ticket['order_id'],'TicketTaker',$message,'tickettaker::error');
    echo" <center>
              <div width='150' class='control-err' align='left' valing='middle' style='padding-left:20px; vertical-align:middle;'>
                 <img src='{$_SHOP->images_url}attention.png' height='100' style='float:left;margin-right:20px;'>
                    <span style='line-height:normal; vertical-align:middle;'><b>".con($message)."</b></span><br>";
    if ($ticket['seat_checked_by']) {
      echo "<p><span style='line-height:normal; vertical-align:middle;'>".con('checked_by').$ticket['admin_login']."</span></p>";
      echo "<p><span style='line-height:normal; vertical-align:middle;'>".con('checked_at').$ticket['seat_checked_date']."</span></p>";

    }
    echo "
               </div> </center><br>";
  }
}
?>