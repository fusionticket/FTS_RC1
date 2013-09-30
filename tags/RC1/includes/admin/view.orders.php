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

class OrdersView extends AdminView{
  var $page_length=15;
  var $tabitems = array(0=> "orders_handlings_tab|manager",
                        1=> "orders_event_tab|manager");

  function tableByHandling (){
    global $_SHOP;
    if (!empty($_SHOP->admin->admin_country)) {
      $where = "AND ((select count(*)
                      from `Seat` left join `Event` on seat_event_id=event_id
                                  left join `Ort` on event_ort_id=ort_id
                      where ort_country= "._esc($_SHOP->admin->admin_country)."
                      and seat_order_id = order_id) > 0)";
    } else $where ='';

    $query="SELECT order_handling_id id, order_shipment_status, order_payment_status, order_status, count( * ) as count,
                   handling_id, handling_shipment, handling_payment, handling_sale_mode
            FROM `Order` left join Handling on order_handling_id=handling_id
            WHERE Order.order_status!='trash' {$where}
            GROUP BY order_handling_id, order_status, order_shipment_status, order_payment_status
            ORDER BY order_handling_id, order_status, order_shipment_status, order_payment_status ";

    if(!$res=ShopDB::query($query)){return;}
    $orders = array();
    while($obj=shopDB::fetch_assoc($res)){
      If (!isset($orders[$obj['id']])) {
        $orders[$obj['id']]['_name'] = $obj;
      } else
        $orders[$obj['id']]['_name']['count'] += $obj['count'];
      $key = $obj['order_status'].$obj['order_shipment_status']. $obj['order_payment_status'];
      $orders[$obj['id']]['_orders'][$key] = $obj;
    }

    $tr = $this->fill_tr();

		$this->grid_head('order_list_title',array('handling'=>array( 'width'=>10,'sort'=>false, 'type'=>'null'),
																						''=>array( 'width'=>10,'sort'=>false, 'type'=>'null'),
                                            'order_payment_status'=>array( 'width'=>200, 'type'=>'select'),
																						'payment_status'=>array( 'width'=>200, 'type'=>'select'),
																						'shipment_status'=>array( 'width'=>200, 'type'=>'select'),
																						'available'=>array( 'width'=>200, 'type'=>'select'),
                                            'total'=>array( 'width'=>60 ,'sort'=>false, 'type'=>'null', 'align'=>'right'),
                                            'actions_header'=>array('width'=>30)),
		array(
		   'addaction'=>false,
		   'width'=>$this->width,
		   'height'=>'300px',
		   'addfilter'=>true,
		   'footerdiv' =>'',
		   'sortable'=> false,
		   'showtitle'=>true,
		   'multi'=>true));//																						false,$this->width,'300px',true,'','',true);

    foreach ($orders as $obj2){
      $hand = $obj2['_name']['id'];
      $obj = $obj2['_name'];

      foreach ($obj2['_orders'] as $obj){
    		$alt=$this->_order_status_color($obj);

        $link = "class=link href='{$_SERVER['PHP_SELF']}?action=list_all&order_handling_id=$hand".
                                                                          "&order_status={$obj['order_status']}".
                                                                          "&order_shipment_status={$obj['order_shipment_status']}".
                                                                          "&order_payment_status={$obj['order_payment_status']}'";
    		//Lxsparks
				echo "<tr class='admin_list_row_$alt'>
							<td><a href='{$_SERVER['PHP_SELF']}?action=list_all&order_handling_id=$hand' class='UITabMenuNavOffPlain'>
							#{$obj['handling_id']} ".con($obj['handling_payment'])." / ".con($obj['handling_shipment'])."
							</a></td>
							<td class='admin_order_$alt'>&nbsp;	</td>
               <td class=admin_list_item align=left class='admin_list_item' title='{$tr[$obj['order_status']]}'>
								{$tr[$obj['order_status']]}
							</td>
							<td class=admin_list_item align=left class='admin_list_item' title='{$tr[$obj['order_payment_status']]}'>
								{$tr[$obj['order_payment_status']]}
							</td>
							<td class=admin_list_item align=left class='admin_list_item' title='{$tr[$obj['order_shipment_status']]}'>
								{$tr[$obj['order_shipment_status']]}
							</td>
							<td class=admin_list_item align=left class='admin_list_item' title='{$tr{$this->showSaleMode($obj['handling_sale_mode'])}}'>
								{$this->showSaleMode($obj['handling_sale_mode'])}
							</td>
               <td class=admin_list_item align=right >
    	          <a $link>{$obj['count']}</a>
               </td>
						<td class='admin_list_item' align=right>".$this->show_button("{$_SERVER['PHP_SELF']}?action=list_all&order_handling_id=$hand".
				  "&order_status={$obj['order_status']}".
				  "&order_shipment_status={$obj['order_shipment_status']}".
				  "&order_payment_status={$obj['order_payment_status']}",'view',2)."</td>
              </tr>\n";
      }
    }
		$this->grid_footer(); // Lxsparks
  }

  function tableByEvent (){
    global $_SHOP;
    $s_order= $where = "";
    if (!empty($_SHOP->admin->admin_country)) {
      $where = "AND ort_country= "._esc($_SHOP->admin->admin_country);
    }
	  if (!isset($_SESSION['order_historty_sort']) && empty($_GET['sort'])) {
       $s_order="asc";
  	} elseif (!empty($_GET['sort'])) {
	     $s_order=$_SESSION['order_historty_sort']=($_GET['sort']=="asc"?"asc":"desc");
	  } else {
	     $s_order=($_SESSION['order_historty_sort']=="asc"?"asc":"desc");
	  }

    $query="SELECT distinct seat_event_id, seat_order_id, order_shipment_status, order_payment_status, order_status,
                   event_id, event_name, event_status, event_date, event_time, ort_name, ort_city
            FROM  Seat left join  Event  on seat_event_id= event_id
                       left JOIN `Order` on seat_order_id=order_id
                       left join `Ort`   on ort_id= event_ort_id
            WHERE Order.order_status!='trash' {$where}
            ORDER BY event_date ".$s_order.", seat_event_id, order_status, order_shipment_status, order_payment_status ";

    if(!$res=ShopDB::query($query)){return;}
    $orders = array();
    while($obj=shopDB::fetch_assoc($res)){
      If (!isset($orders[$obj['event_id']])) {
        $orders[$obj['event_id']]['_name'] = $obj;
        $orders[$obj['event_id']]['_name']['count'] = 0;
      }
      $orders[$obj['event_id']]['_name']['count'] += 1;
      $key = $obj['order_status'].$obj['order_shipment_status']. $obj['order_payment_status'];
      If (!isset($orders[$obj['event_id']]['_orders'][$key])) {
        $orders[$obj['event_id']]['_orders'][$key] = $obj;
        $orders[$obj['event_id']]['_orders'][$key]['count'] = 0;
      }
      $orders[$obj['event_id']]['_orders'][$key]['count'] += 1;
    }
    $tr = $this->fill_tr();

		$this->grid_head('order_event_list_title',array( //Lxsparks
																						'event_name'=>array( 'width'=>10,'sort'=>false, 'type'=>'null'),
																						''=>array( 'width'=>10,'sort'=>false, 'type'=>'null'),
                                            'order_payment_status'=>array( 'width'=>300, 'type'=>'select'),
																						'payment_status'=>array( 'width'=>300, 'type'=>'select'),
																						'shipment_status'=>array( 'width'=>300, 'type'=>'select'),
                                        		'total'=>array( 'width'=>60 ,'sort'=>false, 'type'=>'null', 'align'=>'right'),
                                        		'actions_header'=>array('width'=>30)),
                                            array(
                                        		   'addaction'=>false,
                                        		   'width'=>$this->width,
                                        		   'height'=>'300px',
                                        		   'addfilter'=>true,
                                        		   'footerdiv' =>'',
                                        		   'sortable'=> true,
                                        		   'showtitle'=>true,
                                        		   'multi'=>true));//																						false,$this->width,'400px',true,'','',true);

    foreach ($orders  as $obj){
      $head = $obj['_name'];
      $date = formatAdminDate($head['event_date']).'&nbsp;'.substr($head['event_time'],0,5);

      foreach ($obj['_orders'] as $obj2) {
    		$alt=$this->_order_status_color($obj2);

        $link = "class=link href='{$_SERVER['PHP_SELF']}?action=event_all&event_id={$head['event_id']}".
                                                                           "&order_status={$obj2['order_status']}".
                                                                           "&order_shipment_status={$obj2['order_shipment_status']}".
                                                                           "&order_payment_status={$obj2['order_payment_status']}'";

    	    		//Lxsparks
				echo "<tr class='admin_list_row_$alt'>
							<td><a href='{$_SERVER['PHP_SELF']}?action=event_all&event_id={$head['event_id']}' class='UITabMenuNavOffPlain'
								 title='Location: {$head['ort_name']},&nbsp;{$head['ort_city']}'>
							#{$head['event_id']}: {$head['event_name']}, {$date}
							</a></td>
							<td class='admin_order_$alt'>&nbsp;
							</td>
               <td class=admin_list_item align=left class='admin_list_item' title='{$tr[$obj['order_status']]}'>
								{$tr[$obj2['order_status']]}
							</td>
							<td class=admin_list_item align=left class='admin_list_item' title='{$tr[$obj['order_payment_status']]}'>
								{$tr[$obj2['order_payment_status']]}
							</td>
							<td class=admin_list_item align=left class='admin_list_item' title='{$tr[$obj['order_shipment_status']]}'>
								{$tr[$obj2['order_shipment_status']]}
							</td>
               <td class=admin_list_item align=right >
    	          <a $link>{$obj2['count']}</a>
               </td>
               <td class=admin_list_item align=right>".$this->show_button("{$_SERVER['PHP_SELF']}?action=event_all&event_id={$head['event_id']}".
				  "&order_status={$obj2['order_status']}".
				  "&order_shipment_status={$obj2['order_shipment_status']}".
				  "&order_payment_status={$obj2['order_payment_status']}'",'view',2)."</td>
              </tr>\n";
      }

    }
    $this->grid_footer(); // Lxsparks
  }

  function TableDetails ($type, $id, $order_status,
                                       $order_shipment_status,
                                       $order_payment_status, $page){
    $tr = $this->fill_tr();

    if (!$type) {
      $where= "order_handling_id="._esc($id);
      $type = "action=list_all&order_handling_id=$id".
              "&order_status={$order_status}".
              "&order_shipment_status={$order_shipment_status}".
              "&order_payment_status={$order_payment_status}";

			$query="SELECT handling_id, handling_payment, handling_shipment FROM Handling WHERE handling_id="._esc($id); // Lxsparks
			if(!$res=ShopDB::query($query)){return;}
			$obj=shopDB::fetch_assoc($res);
    } else {
      $where= "seat_event_id="._esc($id);
      $type ="action=event_all&event_id={$id}".
             "&order_status={$order_status}".
             "&order_shipment_status={$order_shipment_status}".
             "&order_payment_status={$order_payment_status}";
    }

    $info = '';
    if($order_status){
      $where.=" and order_status="._esc($order_status);
      $info .= $tr[$order_status];
    }

    if($order_shipment_status){
      $where.=" and order_shipment_status="._esc($order_shipment_status);
      $info .= $tr[$order_shipment_status];
    }

    if($order_payment_status){
      $where.=" and order_payment_status="._esc($order_payment_status);
      $info .= $tr[$order_payment_status];
    }

    $limit=$this->get_limit($page);

		if ($info=='') { $info=con('all');}

    $query="SELECT SQL_CALC_FOUND_ROWS distinct seat_order_id, `Order`.*, User.*
              FROM `Order` left JOIN `Seat` on seat_order_id=order_id
                           LEFT JOIN `User` ON order_user_id = user_id
              WHERE $where
              ORDER BY order_date DESC";
//              LIMIT {$limit['start']},{$limit['end']}";
    $event = Event::load($id);


    $this->grid_head('order_list_title',array(
    																				'table_order_id'=>array( 'width'=>70),
                                            'table_order_date'=>array( 'width'=>140, 'type'=>'date'),
    																				'table_order_user'=>array(  ),
    																				'table_order_price'=>array( 'width'=>100, 'align'=>'right'),
                                            'actions_header'=>array('width'=>130)),
    array(
       'addaction'=>false,
       'width'=>$this->width,
       'height'=>'300px',
       'addfilter'=>true,
       'footerdiv' =>'',
       'sortable'=> true,
       'showtitle'=>true,
       'multi'=>false));//																						false,$this->width,'300px',true,'','',true);

/*
      echo "<table class='admin_list' width='$this->width' cellspacing='1' cellpadding='2' border='0'>\n";
      echo "<tr>
              <td class='admin_list_title' colspan='5' align='left'>
                <a href='view_event.php?action=view&event_id={$event->event_id}' class=link>#{$event->event_id}</a>";

				if ($event->event_name) { // Lxsparks
						echo "{$event->event_name} {$event->event_date} {$event->event_time} ({$info})";
					} else {
						echo "{$obj['handling_id']}: ".con($obj['handling_payment']).' / '.con($obj['handling_shipment']).' ('.$info.')';
					}
							echo"</td>
						</tr>\n";
*/
    if(!$res=ShopDB::query($query)){return;}
//    if(!$count=ShopDB::query_one_row('SELECT FOUND_ROWS()', false)){return;}

    while($row=shopDB::fetch_assoc($res)){
  		$alt=$this->_order_status_color($row);

      echo "<tr class='admin_order_$alt'>
      <td class='admin_list_item' align='right' width=70>".$row["order_id"]."</td>
      <td class='admin_list_item' width=140>".formatAdminDatetime($row["order_date"])."</td>
      <td class='admin_list_item'>".$row["user_firstname"].' '.$row["user_lastname"]."</td>
      <td class='admin_list_item' align='right' width=100>".$row["order_total_price"]."</td>";

      $com=$this->order_commands($row,TRUE);
      echo "<td class='admin_list_item' align='right' width=130>".$com["details"].$com["print"]." ".$com["send"].$com["paid"].$com["reissue"].$com["delete"]."</td>";
      echo "</tr>";
    }
    $this->grid_footer(); // Lxsparks
  }

  /**
   * OrderView::view()
   *
   * Produces Order View + Tabs and Extra info.
   *
   * @param mixed $order_id
   * @return prints out order
   */
  function view($order_id){
    $query="select o.*, u.*, p.user_lastname as kasse_name, h.*
            from `Order` o left join User u on order_user_id= u.user_id
                           left join User p on order_owner_id= p.user_id
                           left join Handling h on order_handling_id= h.handling_id
            where o.order_id="._esc($order_id);
    if(!$order=ShopDB::query_one_row($query)){
      return addWarning('order_not_found');
    }

    $com = implode('',$this->order_commands($order,FALSE));;

    $order["order_status"]        = $this->print_order_status($order);
    $order['order_responce_date'] = ($order['order_responce_date']== '0000-00-00 00:00:00')?'':$order['order_responce_date'];
    $order['order_partial_price'] = ($order['order_discount_price'] + $order['order_total_price']) - $order['order_fee'];
    $order['order_partial_price'] = number_format($order['order_partial_price'] , 2, '.', '');
    echo "<table class='admin_form' width='$this->width' cellspacing='1' cellpadding='2'>\n";
    echo "  <tr><td class='admin_list_title'>".con('order_nr')." ".$order_id."</td>
              <td align='right'>{$com}</td></tr>";

    $this->print_field('order_tickets_nr',$order);
    if ($order['order_discount_price'] <>0.0 || $order['order_fee'] <> 0.0) {
      $this->print_field_o('order_partial_price',$order);
    }
    if ($order['order_fee'] <> 0.0) {
      $this->print_field_o('order_fee',$order);
    }
    if ($order['order_discount_price'] <>0.0) {
      $this->print_field_o('order_discount_price',$order);
    }
    $this->print_field('order_total_price',$order ,'','valuta');
    $this->print_field('order_date',$order,'','date');

    $order['order_shipment_status']=con($order['order_shipment_status']);
    $order['order_payment_status'] =con($order['order_payment_status']);

    $this->print_field('order_place',$order);
    $this->print_field_o('order_owner_id',$order);

    $this->print_field_o('kasse_name',$order);
    $this->print_field('order_status',$order);

    $this->print_field('handling_shipment', con($order['handling_shipment']));
    $this->print_field('order_shipment_status',$order);
    $this->print_field('handling_payment', con($order['handling_payment']));
    $this->print_field('order_payment_status',$order);

    $this->print_field_o('order_payment_id',$order);
    $this->print_field_o('order_responce',con($order['order_responce']));
    $this->print_field_o('order_responce_date',$order);
 //   $this->print_field_o('order_note',clean($order['order_note']));
    echo "</table><br>\n";

  	$query="select * from Seat LEFT JOIN Discount ON Seat.seat_discount_id=Discount.discount_id
                          		 LEFT JOIN Event ON Seat.seat_event_id=Event.event_id
                          		 LEFT JOIN Category ON Seat.seat_category_id=Category.category_id
                          		 LEFT JOIN PlaceMapZone ON Seat.seat_zone_id=PlaceMapZone.pmz_id
      			WHERE Seat.seat_order_id="._esc($order_id);

    if(!$res=ShopDB::query($query)){
       user_error(shopDB::error());
       return true;
    }
    $data = "&action=details&order_id=".(int)$order_id.'&subtab';
    $menu = array( con("order_details_tickettab")=>"0",
                   con("order_details_usertab")=>'1',
                   con("order_details_logtab")=>'2',
                   con("order_details_notetab")=>'3');
    $_REQUEST['subtab'] = (int)$_REQUEST['subtab'];
    echo $this->PrintTabMenu($menu, $_REQUEST['subtab'], "left", $data);

    //Each Order Sub Tab
    if ($_REQUEST['subtab']==0) { //Tickets
      echo "<table class='admin_form' width='$this->width' cellspacing='1' cellpadding='2'>\n";
      echo "<tr><td class='admin_list_title' colspan='8'>".con('tickets')."</td></tr>";
      $alt=0;

      while($ticket=shopDB::fetch_assoc($res)){
        if((!$ticket["category_numbering"]) or $ticket["category_numbering"]=='both'){
          $place=$ticket["seat_row_nr"]."-".$ticket["seat_nr"];
        }else if($ticket["category_numbering"]=='rows'){
          $place=con('place_row')." ".$ticket["seat_row_nr"];
        }else if($ticket["category_numbering"]=='seat'){
          $place=con('place_seat')." ".$ticket["seat_nr"];
         }else{
           $place='---';
         }

        $t_com = implode('',$this->ticket_commands($order_id,$ticket['seat_id']));;

        echo "<tr class='admin_list_row_$alt'>
         	   <td class='admin_list_item'>".$ticket["event_name"]."</td>
         	   <td class='admin_list_item'>".formatAdminDate($ticket["event_date"], false).' '.
                                       	   formatTime($ticket["event_time"])."</td>
         	   <td class='admin_list_item'>".$ticket["category_name"]."</td>

         	   <td class='admin_list_item'>$place</td>
        	   <td class='admin_list_item'>".$ticket["discount_name"]."</td>

         	   <td class='admin_list_item' align='right'>".$ticket["seat_price"]."</td>
         	   <td class='admin_list_item' align='right'>".$t_com."</td>

     	   <tr>\n";
         $alt=($alt+1)%2;

       }
       echo "</table><br>\n";

    } elseif ($_REQUEST['subtab']==1) { //Orders Owner (User)

       $order["user_country_name"]=$this->getCountry($order["user_country"]);
       $order["user_status"]=$this->printUserStatus($order["user_status"]);

       echo "<table class='admin_form' width='$this->width' cellspacing='1' cellpadding='4'>\n";
       echo "<tr><td class='admin_list_title' colspan='2'>".con('user_id')." <a class='link' href='{$_SERVER['PHP_SELF']}?action=user_detail&user_id=".$order["user_id"]."'>".$order["user_id"]."</a></td></tr>";

       $this->print_field('user_lastname',$order);
       $this->print_field('user_firstname',$order);
       $this->print_field('user_address',$order);
       $this->print_field('user_address1',$order);
       $this->print_field('user_zip',$order);
       $this->print_field('user_city',$order);
       $this->print_field('user_state',$order);
       $this->print_field('user_country',$order);
       $this->print_field('user_phone',$order);
       $this->print_field('user_fax',$order);
       $this->print_field('user_email',$order);
       $this->print_field('user_status',$order);

       echo "</table>\n";

    } elseif ($_REQUEST['subtab']==2) { // Orders Status Log
      $query="select *
              from `order_status` where os_order_id="._esc($order_id)."
              order by os_id desc " ;
      if(!$res=ShopDB::query($query)){
        return addWarning('order_not_found');
      }
      echo "<table class='admin_form' width='$this->width' cellspacing='1' cellpadding='2'>\n";
      echo "<tr><td class='admin_list_title' colspan='2'>".con('order_statuslog')."</td></tr>";
      $alt=0;

      while($os=shopDB::fetch_assoc($res)){
        echo "<tr class='admin_list_row_$alt'>
         	   <td class='admin_list_item' width=120 nowrap><nowrap>".formatTime($os["os_changed"])."</nowrap></td>
         	   <td class='admin_list_item' ><div style='overflow:hidden;'>".nl2br($os["os_description"])."</div></td>
       	   <tr>\n";
         $alt=($alt+1)%2;//
      }
     	echo "<form method='POST' action='{$_SERVER['PHP_SELF']}' enctype='multipart/form-data' autocomplete='off'>\n";
			echo "<input type='hidden' name='action' value='printlog' />\n";
			echo "<input type='hidden' name='order_id' value='{$order_id}' />\n";

      $this->form_foot(2,null,'export_statuslog', false);

    } else { //Note Tab

      $query="SELECT *
              FROM `order_note`
              WHERE onote_order_id="._esc($order_id)."
              ORDER BY onote_timestamp DESC ";
      if(!$res=ShopDB::query($query)){
        return addWarning('order_not_found');
      }
      echo "<table class='admin_form' width='$this->width' cellspacing='1' cellpadding='2'>\n";
      echo "<tr><td class='admin_list_title' colspan='2'>".con('order_note_title')."</td></tr>";

      $alt=0;
      $noteTypes = array(
          OrderNote::TYPE_NOTE=>"on_type_note",
          OrderNote::TYPE_ADMIN=>"on_type_admin",
          OrderNote::TYPE_PAYMENT=>"on_type_payment",
          OrderNote::TYPE_SHIP=>"on_type_ship",
          OrderNote::TYPE_TODO=>"on_type_todo"
          );
      while($onote=ShopDB::fetch_assoc($res)){
        $private = ($onote["onote_private"])?con('onone_isprivate'):'';
        $noteCounts[$onote["onote_type"]] += 1;
        if($onote["onote_type"] == OrderNote::TYPE_PAYMENT){
          unset($noteTypes[OrderNote::TYPE_PAYMENT]);
        }elseif($onote["onote_type"] == OrderNote::TYPE_SHIP){
          unset($noteTypes[OrderNote::TYPE_SHIP]);
        }
        echo "<tr class='admin_list_row_$alt'>
         	      <td width='150'>".formatTime($onote["onote_timestamp"])."</td>
         	      <td>".$onote["onote_subject"].$private."</td>
       	      <tr>\n";
        echo "<tr class='admin_list_row_$alt'>
                <td width='150'>".con('onote_type')." ".con('onote_type_'.$onote["onote_type"])."</td>
                <td><div style='overflow:hidden;'>".nl2br($onote["onote_note"])."</div></td>
              <tr>\n";
        $alt=($alt+1)%2;//
      }
      echo "</table><br>\n";

      echo "<form method='POST' action='{$_SERVER['PHP_SELF']}?action=addnote&subtab=3&order_id={$order_id}' enctype='multipart/form-data' autocomplete='off'>\n";
      $this->print_hidden('onote_order_id', $order_id);
      $this->form_head(con('order_add_note'));
      $this->print_select_assoc('onote_type',$_REQUEST,$err,$noteTypes);
      $this->print_checkbox('onote_private',$_REQUEST,$err);
      $this->print_input('onote_subject',$_REQUEST,$err,40);
      $this->print_large_area('onote_note',$_REQUEST,$err,8);
/*
      echo "<tr id=\"on_save_email_ship\" style=\"display:none;\"><td class='' style='text-align:center;'>"
        .$this->Show_button('submit','save_ship',3)."</td><td><label for='onote_set_sent'>".con("onote_set_sent")."</label><input type='checkbox' id='onote_set_sent' name='onote_set_sent' value='1' /></td></tr>";
      echo "<tr id=\"on_save_email_payment\" style=\"display:none;\"><td class='' style='text-align:center;'>"
        .$this->Show_button('submit','save_payment',3)."</td><td><label for='onote_set_paid'>".con("onote_set_paid")."</label><input type='checkbox' id='onote_set_paid' name='onote_set_paid' value='1' /></td></tr>";
      echo "<tr id=\"on_save_email_note\" style=\"display:none;\"><td class='' colspan='2' style='text-align:center;'>"
        .$this->Show_button('submit','save_note',3)."</td></tr>";
*/
      $this->form_foot();
/*
      $script = "
      $('#onote_type-select').change(function(){
        if($(this).val() == '".OrderNote::TYPE_SHIP."'){
          $('#on_save_email_ship').show(); $('#on_save_email_note').hide();
          $('#on_save_email_payment').hide(); $('#onote_set_paid').attr('checked',false);
        }else if($(this).val() == '".OrderNote::TYPE_PAYMENT."'){
          $('#on_save_email_ship').hide(); $('#on_save_email_note').hide();
          $('#onote_set_sent').attr('checked',false); $('#on_save_email_payment').show();
        }else if($(this).val() == '".OrderNote::TYPE_NOTE."'){
          $('#on_ship_note').attr('checked',false); $('#on_payment_note').attr('checked',false);
          $('#on_save_email_ship').hide(); $('#on_save_email_payment').hide();
          $('#on_save_email_note').show();
        }else{
          $('#on_save_email_ship').hide(); $('#on_save_email_payment').hide(); $('#on_save_email_note').hide();
          $('#on_ship_note').attr('checked',false); $('#on_payment_note').attr('checked',false);
        }
      }).change();";
      $this->addJQuery($script);

      if(empt($order['order_note'])){
        echo "<form method='POST' action='{$_SERVER['PHP_SELF']}?action=savenote&subtab=3&order_id=".$order_id."' enctype='multipart/form-data'>\n";
        $this->form_head(con('order_note_title'));
        $this->print_large_area('order_note',$order, $err,10);
        $this->form_foot();
      }
*/
    }
    return true;
  }

  function execute(){
    if(is($_GET['action'],'')=='print' and is($_GET['order_id'],0) > 0){
      $mode = is($_GET['mode'],3);
      Order::printOrder($_GET['order_id'],'','stream', false, $mode, true);
      return true;
    }
  }

  function draw($noTab=false){
    //echo "<pre>";
 //   print_r($_REQUEST);
    //print_r($_SESSION);
    //echo "</pre>";
    if(!$noTab){
      $tab = $this->drawtabs();
      if (!$tab) { return; }
    }

    if(!isset($_REQUEST['order_id'])){
      $_REQUEST['order_id']=is($_REQUEST['order_id1'],0);
    }

    if(preg_match('/^set_status_/',is($_GET['action1'],'')) and $_GET['order_id']>0){
      if(!$order=Order::load($_GET['order_id'])){return;}
      switch($_GET['action1']){
        case 'set_status_shipment_send': $order->set_shipment_status('send');break;
        case 'set_status_shipment_none': $order->set_shipment_status('none');break;
        case 'set_status_payment_paid':  $order->set_payment_status('paid');break;
        case 'set_status_payment_none':  $order->set_payment_status('none');break;
        case 'set_status_ord':           $order->set_status('ord');break;
      }
    }elseif($_GET['action1']=="reissue" and $_GET["order_id"]){
      Order::reissue($_GET["order_id"], 0);
      return $this->view($_GET["order_id"]);

    }elseif($_GET['action1']=="delete_ticket" and $_GET["order_id"] and $_GET['seat_id']){
      Order::delete_ticket($_GET["order_id"], $_GET['seat_id'],0);
    }elseif($_GET['action1']=="reissue_ticket" and $_GET["order_id"] and $_GET['seat_id']){
      Seat::reIssue($_GET["order_id"], $_GET['seat_id']);
    }elseif($_GET['action1']=='delete'){
      Order::delete($_GET["order_id"], 'order_deleted_manual');

    }

    if($_POST['action']=='printlog') {
      $this->printSatusLog($_REQUEST["order_id"]);
      return $this->view($_REQUEST["order_id"]);

    }elseif($_GET['action']=='savenote') {
      order::save_order_note($_REQUEST["order_id"],$_REQUEST["order_note"]);
      return $this->view($_REQUEST["order_id"]);

    }elseif($_GET['action']=='addnote') {
      $orderNote = new OrderNote();
      if (!$orderNote->fillRequest() || !$orderNote->saveEx()) {
        return $this->view($_REQUEST["order_id"]);
			}
      if(!$order=Order::load($_REQUEST['order_id'])){return;}
      $order->emailSubject = $orderNote->onote_subject;
      $order->emailNote = $orderNote->onote_note;
      if(is($_REQUEST['onote_set_sent'])==="1"){
        $order->set_shipment_status('send');
      }elseif(is($_REQUEST['onote_set_paid'])==="1"){
        $order->set_payment_status('paid');
      }elseif(isset($_REQUEST['save_payment'])){
        $orderNote->sendNote($order);
      }elseif(isset($_REQUEST['save_ship'])){
        $orderNote->sendNote($order);
      }elseif(isset($_REQUEST['save_note'])){
        $orderNote->sendNote($order);
      }
      return $this->view($_REQUEST["order_id"]);

    } elseif($_GET['action']=='details' || $_REQUEST['action']=='order_detail'){
      return $this->view($_REQUEST["order_id"]);

    }elseif($_GET['action']=='delete'){
      return $this->order_prepare_delete($_GET["order_id"]);

    }elseif($_GET['action']=='cancel'){
      Order::delete($_GET["order_id"], 'order_deleted_manual');

    } elseif($_GET['action']=='list_all'){
      return $this->tableDetails(False,
                          $_GET["order_handling_id"],
                          $_GET["order_status"],
                          $_GET["order_shipment_status"],
                          $_GET["order_payment_status"],
			                    $_GET["page"]);

      return;
    } elseif($_GET['action']=='event_all'){
      return $this->tableDetails(true,
                          $_GET["event_id"],
                          $_GET["order_status"],
                          $_GET["order_shipment_status"],
                          $_GET["order_payment_status"],
			                    $_GET["page"]);
      return;
  	} else if($_GET['action']=='purge_deleted'){
  		Order::purgeDeleted((int)$_GET['order_handling_id']);
  	} else if($_GET['action']=='purge_reissued'){
  		Order::purgeReissued((int)$_GET['order_handling_id']);
    }
    if (!$noTab) {
      switch ($tab-1){
        case 0:
          $this->tableByHandling();
          break;
        case 1:
          $this->tableByEvent();
          break;
        default:
          plugin::call('is'.get_class($this).'_Draw', $tab-1, $this);
      }
    }
  }

 function link ($action, $order_id, $img, $confirm=FALSE, $con_msg='',$param=null, $hide=false, $config=false, $btnName=false){
   if (!is_array($config)) $config = array();
   $config['image']= $img;
   $config['disable'] = $hide;
   if ($hide) {
     return '';
   }
   if($confirm){
     $param['action1']=$action;
     $param['order_id']=$order_id;
     foreach($param as $key=>$val){
       $par.=$psep."$key=$val";
       $psep="&";
     }

     return $this->show_button("javascript:if(confirm(\"".con($con_msg)."\")){location.href=\"".$_SERVER['PHP_SELF']."?$par\";}",$action , 2,$config);
   }
   if(is_array($param)){
 	 	 foreach($param as $key=>$val){
 		   $par.=$psep."$key=$val";
 			 $psep="&";
 		 }
 	 }
	 if (!$btnName) {
		$name=$action;
		} else {
		$name=$btnName;
		}
 	 return $this->show_button($_SERVER['PHP_SELF']."?action=$action&order_id=$order_id&".$par,$name , 2,$config);
 }

  function _order_status_color($row){
    $alt='';
    if($row['order_status']=='ord' and ($row['order_payment_status']!='none' or $row['order_shipment_status']!='none')){
      if($row['order_payment_status']!='none'){
        $alt=$row['order_payment_status'];
      }
      if($row['order_shipment_status']!='none'){
        $alt.=$row['order_shipment_status'];
      }
    }else{
      $alt=$row['order_status'];
    }
    return $alt;
  }

  function order_prepare_delete ($order_id){
    echo "<center><form action='".$_SERVER["PHP_SELF"]."?action=cancel&order_id=$order_id' method='post' autocomplete='off'>
          <input type='submit' name='cancel' value='".con('cancel')."'></form></center><br>";
    $this->order_details ($order_id);
    echo "<br><center><form action='".$_SERVER["PHP_SELF"]."?action=cancel&order_id=$order_id' method='post' autocomplete='off'>
          <input type='submit' name='cancel' value='".con('cancel')."'></form></center><br>";
  }

  function printUserStatus ($user_status){
    if($user_status=='1'){
      return con('sale_point');
    }else if ($user_status=='2'){
      return con('member');
    }else if($user_status=='3'){
      return con('guest');
    }
  }

  function print_order_status ($order){
    switch($order['order_status']){
      case 'ord':    return "<font color='blue'>".con('order_status_ordered')."</font>";
      case 'send':   return "<font color='red'>".con('order_status_sended')."</font>";
      case 'paid':   return "<font color='green'>".con('paidorder_status_')."</font>";
      case 'cancel': return "<font color='#787878'>".con('order_status_cancelled')."</font>";
      case 'res':    return "<font color='orange'>".con('order_status_reserved')."</font>";
      case 'reissue':return "<font color='#787878'>".con('order_status_reissued')."</font> (
      <a href='{$_SERVER['PHP_SELF']}?action=details&order_id={$order['order_reissued_id']}'>
      {$order['order_reissued_id']}</a> )";
    }
  }

  function extramenus(&$menu) {
    if (!($tabs=$this->drawtabs(nil, false))) { return; }

    if ( $tabs==2) {
      return false;
    }

    if($_GET["action"]=='list_all' or $_GET["action"]=='list_type' or $_GET["action"]=='details'){
      $sty="style='padding-left: 25px; padding-top: 2px; padding-bottom: 2px;'";
      $menu[]="
      <table width='190' class='menu_admin' cellspacing='2'>
      <tr><td align='left' class='menu_admin_title'>".con('possible_actions')."</td></tr>
      <tr><td class='menu_admin_item' $sty><img src='../images/view.png'  border='0'> ".con('view_order_details')."</td></tr>
      <tr><td class='menu_admin_item' $sty><img src='../images/printer.png' border='0'> ".con('print_order')."</td></tr>
      <tr><td class='menu_admin_item' $sty><img src='../images/ord.png' border='0'> ".con('change_order_to_ord')."</td></tr>

      <tr><td class='menu_admin_item' $sty><img src='../images/mail.png' border='0'> ".con('send_order_post')."</td></tr>
      <tr><td class='menu_admin_item' $sty><img src='../images/no_mail.png' border='0'> ".con('no_send_order_post')."</td></tr>

      <!--tr><td class='menu_admin_item' $sty><img src='../images/email.png' border='0'> ".con('send_order_email')."</td></tr-->
      <tr><td class='menu_admin_item' $sty><img src='../images/pig.png' border='0'> ".con('change_order_to_paid')."</td></tr>
      <tr><td class='menu_admin_item' $sty><img src='../images/no_pig.png' border='0'> ".con('change_order_to_no_paid')."</td></tr>

      <tr><td class='menu_admin_item' $sty><img src='../images/remis.png' border='0'> ".con('reissue_order_menu')."</td></tr>
      <tr><td class='menu_admin_item' $sty><img src='../images/trash.png' border='0'> ".con('cancel_order')."</td></tr>

      </table>";
    } else {
      $menu[]="
      <table width='190' class='menu_admin' cellspacing='2'>
      <tr><td align='left' class='menu_admin_title'>".con('legende')."</td></tr>
      <tr><td class='admin_order_res' style='padding-left: 25px; padding-top: 2px; padding-bottom: 2px;'>".con('reserved')."</td></tr>
      <tr><td class='admin_order_ord' style='padding-left: 25px; padding-top: 2px; padding-bottom: 2px;'>".con('ordered')."</td></tr>
      <tr><td class='admin_order_pending' style='padding-left: 25px; padding-top: 2px; padding-bottom: 2px;'>".con('pending')."</td></tr>
      <tr><td class='admin_order_paid' style='padding-left: 25px; padding-top: 2px; padding-bottom: 2px;'>".con('paid')."</td></tr>
      <tr><td class='admin_order_send' style='padding-left: 25px; padding-top: 2px; padding-bottom: 2px;'>".con('sended')."</td></tr>
      <tr><td class='admin_order_paidsend' style='padding-left: 25px; padding-top: 2px; padding-bottom: 2px;'>".con('paid_and_send')."</td></tr>
      <tr><td class='admin_order_cancel' style='padding-left: 25px; padding-top: 2px; padding-bottom: 2px;'>".con('cancelled')."</td></tr>
      <tr><td class='admin_order_reissue' style='padding-left: 25px; padding-top: 2px; padding-bottom: 2px;'>".con('reissue')."</td></tr>
      </table><br>";
    }
  }

  function fill_tr() {
    $tr['ord']     = con('order_type_ordered');
    $tr['send']    = con('order_type_sended');
    $tr['cancel']  = con('order_type_cancelled');
    $tr['reissue'] = con('order_type_reissued');
    $tr['pending'] = con('order_type_pending');
    $tr['paid']    = con('order_type_paid');
    $tr['res']     = con('order_type_reserved');
    $tr['none']    = '-';
    $tr[''] = 'Unknown';
    return $tr;
  }

  function get_limit ($page,$count=0){
    if(!$page){ $page=1; }
    $limit["start"]=($page-1)*$this->page_length;
    $limit["end"]=$this->page_length;
    return $limit;
  }

  function ticket_commands ($order_id,$ticket_id){
    $params=$_GET;
    $params['seat_id']=$ticket_id;
    $com["reissue"]=$this->link("reissue_ticket",$order_id,"remis.png",TRUE,con('reissue_ticket')." $ticket_id",$params);
    $com["delete"]=$this->link("delete_ticket",$order_id,"trash.png",TRUE,con('delete_ticket')." $ticket_id",$params);
    return $com;
  }

  function order_commands ($order,$list=TRUE){
    if($order['order_status']=='reissue' or $order['order_status']=='cancel'){
      $hide = true;
     //$list = true; //Just cause its a list doesnt mean it should be disabled?
    }
    if ($list) {
      $com["details"]=$this->link("details",$order["order_id"],"view.png",false,'',null, !$list);
    }
	  $com["printaddress"]=$this->link("print",$order["order_id"],"address.png",false,'',array('mode'=>'4'), $hide ,array('target'=>'pdfdoc'), 'print_address'); //Lxsparks added 04/08/2013
  	$com["printtickets"]=$this->link("print",$order["order_id"],"pm.png",false,'',array('mode'=>'1'), $hide ,array('target'=>'pdfdoc'), 'print_tickets');
    $com["print"]=$this->link("print",$order["order_id"],"printer.png",false,'',null, $hide ,array('target'=>'pdfdoc'), 'print_order');

    $com["ord"]=$this->link("set_status_ord",$order["order_id"],"ord.png",TRUE,con('change_status_to_ord'),$_GET, $list|| $order['order_status']!='trash'|| $order['order_status']=='ord'|| $order['order_status']=='cancel');
    $com["paid"]=$this->link("set_status_payment_paid",$order["order_id"],"pig.png",TRUE,con('change_status_to_paid'),$_GET,
                              ($order['order_payment_status']=='paid' || $order['order_status']=='res' || $list||  $order['order_status']=='cancel'));

    $com["no_paid"]=$this->link("set_status_payment_none",$order["order_id"],"no_pig.png",TRUE,con('change_status_to_no_paid'),$_GET,
                                 $order['order_payment_status']=='none' || $order['order_status']=='res' || $list);
    if ($order['order_shipment_status']!=='send') {
      $com["send"]=$this->link("set_status_shipment_send",$order["order_id"],"mail.png",TRUE,con('change_status_to_send'),$_GET,
                               ($hide || ( $list) ||  $order['order_status']=='res'||  $order['order_status']=='cancel'));
    } else {
      $com["no_send"]=$this->link("set_status_shipment_none",$order["order_id"],"no_mail.png",TRUE,con('change_status_to_no_send'),$_GET,
                               ($hide || ( $list) ||  $order['order_status']=='res' ||  $order['order_status']=='cancel'));
    }
    $com["reissue"]=$this->link("reissue",$order["order_id"],"remis.png",TRUE,con('reissue_order'),$_GET, $list||  $order['order_status']=='cancel');

    $com["delete"]=$this->link ("delete",$order["order_id"],"trash.png",true,con('order_delete_now') ,$_GET,
                                ($order['order_payment_status']=='paid' || $hide || $list));

    if(empty($com)){$com[]='';}
    return $com;
  }

  function printSatusLog($order_id) {
      $query="select *
              from `order_status` where os_order_id="._esc($order_id)."
              order by os_id desc " ;
      if(!$res=ShopDB::query($query)){
        return addWarning('order_not_found');
      }
      $log = '';
      while($os=shopDB::fetch_assoc($res)){
        $log .= formatTime($os["os_changed"]).":".nl2br($os["os_description"])."\n";
      }
      $file = $_SHOP->trace_dir.'order_'.$order_id.'.'.date('Y-m-d') . '.log';
      file_put_contents($file, $log);
  }

  function showSaleMode($handling_sale_mode) { // Lxsparks
    $modes = explode(',',$handling_sale_mode);

    foreach($modes as &$mode) {
      $mode = con('handling_'.$mode);
    }
			return implode(', ',$modes);
			//return con('for_handling_salemode').implode(', ',$modes);

  }
}
?>