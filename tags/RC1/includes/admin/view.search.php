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

class SearchView extends AdminView{
  var $tabitems = array(
                    0 => "patron_tab",
                    1 => "seat_tab" ,
                    2 => "order_tab",
                    3 => "barcode_tab");

  function patronForm (&$data){
    global $_SHOP;
    echo "<form method='POST' action='{$_SERVER['PHP_SELF']}'  autocomplete='off'>
            <input type='hidden' name='action' value='search_patron'/>\n";

    $this->form_head(con("search_title_user"));
	$this->print_input('user_firstname',$data, $err,25,100);
    $this->print_input('user_lastname',$data, $err,25,100);
    $this->print_input('user_zip',$data, $err,25,100);
    $this->print_input('user_city',$data, $err,25,100);
    $this->print_input('user_phone',$data, $err,25,100);
    $this->print_input('user_email',$data, $err,25,100);
    echo "<tr><td class='admin_name'>".con('user_status')."</td><td class='admin_value'>
          <select name='user_status'>
            <option value='0'></option>
          	<option value='1'>".con('sale_point')."</option>
            <option value='2'>".con('member')."</option>
          	<option value='3'>".con('guest')."</option>
          </select></td></tr>";
    $this->form_foot(2,'','search');
  }

  function patronTable (&$data){
    global $_SHOP;
    $count = 0;
	if($data["user_firstname"]){
      $query_type[]= "user_firstname LIKE "._esc($data['user_firstname'].'%');
      $count++;
    }
    if($data["user_lastname"]){
      $query_type[]= "user_lastname LIKE "._esc($data['user_lastname'].'%');
      $count++;
    }
    if($data["user_zip"]){
      $query_type[]= "user_zip LIKE "._esc($data['user_zip'].'%');
      $count++;
    }
    if($data["user_city"]){
      $query_type[]= "user_city LIKE "._esc($data['user_city'].'%');
      $count++;
    }
    if($data["user_phone"]){
      $query_type[]= "user_phone LIKE "._esc($data['user_phone'].'%');
      $count++;
    }
    if($data["user_email"]){
      $query_type[]= "user_email LIKE "._esc($data['user_email'].'%');
      $count++;
    }
    if($data["user_status"]){
      $query_type[]= "user_status="._esc($data['user_status']);
    //  $count++;
    }
    if ($count <2) {
      return addWarning('search_choice_two_fields');
    }

    $query="select * from User where ". implode("\n AND ",$query_type);

    if(!$res=ShopDB::query($query)){
       user_error(shopDB::error());
       return;
    }
    if (!ShopDB::num_rows($res)) {
      return addWarning('no_result');
    }
    $this->grid_head('search_result',array(
                                       'user_id_header'=>array( 'width'=>50  ),
                                        'user_name_header'=>array(   ),
                                        'user_city_header'=>array(   ),
                                        'user_country_header'=>array(  'width'=>70,'type'=>'select' ),
                                        'user_status_header'=>array( 'width'=>120,'type'=>'select'  )),
                                      array(
                                         'addaction'=>false,
                                         'width'=>$this->width,
                                         'height'=>'300px'));//                     "",$this->width,'300px',true);

     $alt=0;
    while($row=shopDB::fetch_assoc($res)){
      $flag=1;
      echo "<tr class='admin_list_row_$alt'>
            <td class='admin_list_item'>".$row["user_id"]."</td>
  	        <td class='admin_list_item'>
              <a class='link' href='{$_SERVER['PHP_SELF']}?action=user_detail&user_id=".$row["user_id"]."'>".
                 $row["user_lastname"]." ".$row["user_firstname"]."
              </a>
            </td>
            <td class='admin_list_item'>".$row["user_city"]."</td>
            <td class='admin_list_item'>".$row["user_country"]."</td>
  	        <td class='admin_list_item'>".$this->print_status($row["user_status"])."</td></tr>" ;
      $alt=($alt+1)%2;
    }
    $this->grid_footer();
    return true;
  }

  function placesForm (&$data){
    global $_SHOP;
    echo "<form method='POST' action='{$_SERVER['PHP_SELF']}'  autocomplete='off'>\n
          <input type='hidden' name='action' value='search_place'/>\n";
    $this->form_head(con("search_title_place"));
    $query="SELECT event_id,event_name,event_date,event_time, event_status
            FROM Event
            WHERE event_rep LIKE '%sub%'
            and field(event_status, 'trash','unpub')=0
            and event_pm_id IS NOT NULL
            {$_SHOP->admin->getEventRestriction()}
            order by event_date,event_time";
    if(!$res=ShopDB::query($query)){
      user_error(shopDB::error());
      return;
    }
    echo "<tr><td class='admin_name'>".self::print_label('event_list','-input',true) ."</td>
              <td class='admin_value'>
          <select name='event_id'><option value='' selected>".con('choice_please')."</option>";
    while($event=shopDB::fetch_assoc($res)){
      $date=formatAdminDate($event["event_date"]);
      $time=formatTime($event["event_time"]);
      echo "<option value='{$event["event_id"]}'>".$event['event_status'].'|'.$event["event_name"]." - $date - $time </option>";
    }

    echo "</select>".printMsg('event_id')."</td></tr>";

    $this->print_input('seat_row_nr',$data, true,4,4);
    $this->print_input('seat_nr',$data, $err,4,4);
    $this->form_foot(2,'','search');
  }

  function PlacesTable (&$data){
    global $_SHOP;
    if(empty($data["event_id"])){
      addError("event_id", 'mandatory');
    }

    if(empty($data["seat_row_nr"])){
      addError("seat_row_nr", 'mandatory');;
    }
    if (hasErrors()) return false;

    if($data["event_id"]){
      $query_type["event_id"]="event_id="._esc($data["event_id"]);
    }

    if($data["seat_row_nr"]){
      $query_type["seat_row_nr"]="seat_row_nr="._esc($data["seat_row_nr"]);
    }
    if($data["seat_nr"]){
      $query_type["seat_nr"]="seat_nr="._esc($data["seat_nr"]);
    }

    $query="select *
            from Seat left join Category ON seat_category_id=category_id
                      left join Event    ON seat_event_id=event_id
                      left join User     ON seat_user_id=user_id
                      left join `Order`  ON seat_order_id=order_id
            where ". implode("\n AND ",$query_type). '
                  '. $_SHOP->admin->getEventRestriction();

    if(!$res=ShopDB::query($query)){
       user_error(shopDB::error());
       return;
    }
    if (!ShopDB::num_rows($res)) {
      return addWarning('no_result');
    }
    $this->grid_head('search_result',array(
                                            'event'=>array(   ),
                                            'category'=>array(   ),
                                            'place'=>array(   ),
                                            'user_name'=>array(  ),
                                            'price'=>array(   ),
                                            'bs'=>array(   ),
                                            'status'=>array(  )),
                                        array(
                                           'addaction'=>false,
                                           'width'=>$this->width,
                                           'height'=>'300px',
                                           'addfilter'=>true));//    "",$this->width,'300px',true);

     $alt=0;
     while($row=shopDB::fetch_assoc($res)){
      $flag=1;
      if((!$row["category_numbering"]) or $row["category_numbering"]=='both'){
        $place=$row["seat_row_nr"]."-".$row["seat_nr"];
      }else if($row["category_numbering"]=='rows'){
        $place=con('place_row')." ".$row["seat_row_nr"];
      }else if($row["category_numbering"]=='seat'){
        $place=con('place_seat')." ".$row["seat_nr"];
      }else{
        $place='---';
      }

      echo "<tr class='admin_list_row_$alt'>
            <td class='admin_list_item'>".$row["event_name"]."</td>
            <td class='admin_list_item'>".$row["category_name"]."</td>
            <td class='admin_list_item'>".$place."</td>
            <td class='admin_list_item'>".$row["seat_price"]."</td>
  	        <td class='admin_list_item'>
              <a class='link' href='{$_SERVER['PHP_SELF']}?action=user_detail&user_id=".$row["user_id"]."'>".
                 $row["user_lastname"]." ".$row["user_firstname"]."</a></td>
            <td class='admin_list_item'>
              <a class='link' href='{$_SERVER['PHP_SELF']}?action=details&order_id=".$row["order_id"]."'>".$row["order_id"]."</a></td>
            <td class='admin_list_item'>".$this->print_order_status($row["order_status"])."</td>

  	  </tr>" ;
      $alt=($alt+1)%2;
    }
    $this->grid_footer();
    return true;
  }

  function orderForm (&$data){
    global $_SHOP;
    echo "<form method='GET' action='{$_SERVER['PHP_SELF']}'  autocomplete='off'>
      <input type='hidden' name='action' value='details'/>\n";
    $this->form_head(con("search_title_order"));
    $this->print_input('order_id', $data, true,11,11);
    $this->form_foot(2,'','search');
  }

  function barcodeForm(&$data){
    global $_SHOP;
    echo "<form method='POST' action='{$_SERVER['PHP_SELF']}'  autocomplete='off'>
            <input type='hidden' name='action' value='search_codebar'/>\n";
    $this->form_head(con("search_title_codebar"));
    $this->print_input('codebar',$data, true,25,21);
    $this->form_foot(2,'','search');
  }

  function codebarTable (){
    global $_SHOP;
    if(empty($_POST['codebar'])){
       return addError('codebar','mandatory' );
    } else {

      list($seat_id, $ticket_code)= seat::decodeBarcode($_POST['codebar']);

      $query="select * from Seat LEFT JOIN `Discount` ON seat_discount_id=discount_id
                                 LEFT JOIN `Category` ON seat_category_id=category_id
                                 LEFT JOIN `PlaceMapZone` ON  seat_zone_id=pmz_id
                           	     LEFT JOIN `Event`        ON  seat_event_id=event_id
                                 LEFT JOIN `User`         ON seat_user_id=user_id
                            	   LEFT JOIN `Order`        ON seat_order_id=order_id
         where seat_id="._esc($seat_id)."
  	     AND seat_code="._esc($ticket_code).'
        '.$_SHOP->admin->getEventRestriction();


      if(!$ticket=ShopDB::query_one_row($query)){
        return addWarning('ticket_not_found');
      }
    	echo "<table class='admin_list' width='$this->width' cellspacing='1' cellpadding='4' >";
    	echo "<tr><td colspan='2' class='admin_list_title'>".con('search_result')."</td></tr>";

      $this->print_field("seat_id",$ticket);
      echo "<tr>
          <td class='admin_name' width='40%'>".con('order_id')."</td>
          <td class='admin_value'><a class='link' href='{$_SERVER['PHP_SELF']}?order_id={$ticket["order_id"]}&action=details'>{$ticket["order_id"]}</a></td></tr>\n";
      echo "<tr>
          <td class='admin_name' width='40%'>".con('user')."</td>
          <td class='admin_value'><a class='link' href='{$_SERVER['PHP_SELF']}?action=user_detail&user_id={$ticket["user_id"]}'>{$ticket["user_firstname"]} {$ticket["user_lastname"]}</a></td></tr>\n";

      $this->print_field("event_name",$ticket);
      $this->print_field("category_name",$ticket);
      $this->print_field("pmz_name",$ticket);

      $this->print_field("event_date",$ticket);
      $this->print_field("event_time",$ticket);

      echo "<tr><td class='admin_name' width='40%'>".con('place')."</td>
      <td class='admin_value'>";

      if($ticket['category_numbering']=='both'){
        $place_nr=con('place_nr')." ".$ticket['seat_row_nr']."-".$ticket['seat_nr'];
      }else if($ticket['category_numbering']=='rows'){
        $place_nr=con('rang_nr')." ".$ticket['seat_row_nr'];
      }else if($ticket['category_numbering']=='seat'){
        $place_nr=con('place_nr')." ".$ticket['seat_nr'];
      }else if($ticket['category_numbering']=='none'){
        $place_nr=con('place_without_nr');
      }
      echo "$place_nr</td></tr>\n";

      $this->print_field("seat_price",$ticket);

      $this->print_field("discount_name",$ticket);
      $this->print_field("discount_type",$ticket);
      $this->print_field("discount_value",$ticket);

      $this->print_field("seat_status",$ticket);
   // $this->print_field("seat_code",$ticket);


      if(isset($ticket[color_code])){
          echo "<tr>
  	<td class='admin_name' width='40%'>".con('color_code')."</td>
  	<td  bgcolor='{$ticket[color_code]}' style='border: #999999 1px dashed;'> &nbsp </td></tr>";
      }
      echo "</table>";
    }
    return true;
  }

  function execute(){
    if(is($_GET['action'],'')=='print' and is($_GET['order_id'],0) > 0){
      Order::printOrder($_GET['order_id'],'','stream');
      return true;
    }
  }

  function draw () {
    global $_SHOP;

    $tab = $this->drawtabs();
    if (! $tab) { return; }

    if ($_REQUEST['action']=='user_detail'){
      require_once("admin/view.patrons.php");
      $view = new PatronView($this->width, $_REQUEST['user_id']);
      $view->draw();
      return;
    }

    switch ((int)$tab-1){
      case 0:
         if($_POST['action']=='search_patron'){
           if($query_type=$this->patronTable($_POST)) return;
         }
         $this->patronForm($_POST);
         break;

      case 1:
         if($_POST['action']=='search_place'){
           if ($this->placesTable($_POST)) return;
         }
         $this->placesForm($_POST);
         break;

      case 2:
        if ((int) is($_REQUEST['order_id'],0)){
          require_once("admin/view.orders.php");
          $view = new OrdersView($this->width);
          if ($view->draw(true)) return;
        }
        $this->orderForm($_POST);
        break;

      case 3:
        if($_POST['action']=='search_codebar'){
          if ($this->codebarTable($_POST)) return;
        }
         $this->barcodeForm($_POST);
         break;
      default:
        plugin::call('is'.get_class($this).'_Draw', $tab-1, $this);
    }
  }

  function print_status ($user_status){
    if($user_status=='1'){
      return con('sale_point');
    }else if ($user_status=='2'){
      return con('member');
    }else if($user_status=='3'){
      return con('guest');
    } else if($user_status=='4'){
      return con('sales_patron');
    } else {
      return con('unkown');
    }
  }

  function print_order_status ($order_status){
    if($order_status=='ord'){
      return "<font color='blue'>".con('order_status_ordered')."</font>";
    }else if ($order_status=='send'){
      return "<font color='red'>".con('order_status_sended')."</font>";
    }else if($order_status=='paid'){
      return "<font color='green'>".con('order_status_paid')."</font>";
    }else if($order_status=='cancel'){
      return "<font color='#787878'>".con('order_status_cancelled')."</font>";
    }
  }
}
?>