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

function getOphanQuerys() {

  $orphancheck = array();
  /**/

  $orphancheck[]="
      SELECT 'Order', order_id, 'Seats', CONCAT_WS('/', order_tickets_nr ,  count( S.seat_id )) , (order_tickets_nr - count( S.seat_id )) delta
      FROM `Order`
      LEFT JOIN `Seat` S ON order_id = S.seat_order_id
      where order_status NOT IN ('reissue', 'cancel', 'trash')
      GROUP BY order_id
      HAVING delta <> 0
      order by seat_event_id, order_id
  ";


  /*******************************************************************/
  $orphancheck[]="
  select 'Category', category_id, 'event_id' l1 , category_event_id, event_id
  from Category left join Event         on category_event_id = event_id
  where  (category_event_id is not null and event_id is null) and event_status != 'trash'
  ";

  $orphancheck[]="
  select 'Category', category_id, 'pm_id'    l2 , category_pm_id, pm_id
  from Category left join PlaceMap2     on category_pm_id    = pm_id
  where  (pm_id is null)
  ";

  $orphancheck[]="
  select 'Category', category_id, 'pmp_id'   l3 , category_pmp_id, pmp_id
  from Category left join PlaceMapPart  on category_pmp_id   = pmp_id
  where  (category_pmp_id is not null and pmp_id is null)
  ";

  $orphancheck[]="
  select 'Category', category_id, 'shadow'  , category_event_id, null
  from Category left join Event    on category_event_id = event_id
  where  (category_pm_id <> event_pm_id) and event_status != 'trash'
  ";


  $orphancheck[]="
  SELECT 'Category', category_id,
     'Total',  CONCAT_WS('/', `category_size`, (select count(*) from `Seat` where seat_category_id = category_id)) seat_total, null
  	FROM Event e  left join Category c on category_event_id = event_id
    where lower(e.event_status) not in ('unpub','trash')
  		AND lower(e.event_rep) LIKE ('%sub%')
  		AND category_size != (SELECT count(seat_id)
                            FROM Seat s
                            WHERE s.seat_event_id = e.event_id
                            and s.seat_category_id = category_id )
  ";

  $orphancheck[]="
  SELECT 'Category', category_id,
     'Free' ,  CONCAT_WS('/', `category_free` , (select count(*) from `Seat` where seat_category_id = category_id and seat_status in ('res', 'free','trash') and seat_user_id IS NULL and seat_order_id IS NULL )) seat_free, null
   FROM `Category`
  where `category_free`  - (select count(*) from `Seat` where seat_category_id = category_id and seat_status in ('res', 'free','trash') and seat_user_id IS NULL and seat_order_id IS NULL ) <> 0
  ";

  /**/
  $orphancheck[]="
  select 'Discount', discount_id, 'event_id' , ifnull(discount_event_id,'null'), event_id
  from Discount left join Event on discount_event_id = event_id
  where  (event_id is null and discount_promo is null) and event_status != 'trash'
  ";
  /**/
  $orphancheck[]="
  select 'Event', e.event_id,  'ort_id'   , ifnull(e.event_ort_id,'null'),    ort_id
  from Event e left join Ort            on e.event_ort_id = ort_id
  where  (ort_id is null and e.event_ort_id is not null  and e.event_rep !='main' ) and event_status != 'trash'
  ";
  $orphancheck[]="
  select 'Event', e.event_id,  'pm_id'    , e.event_pm_id,     pm_id
  from Event e left join PlaceMap2      on e.event_pm_id = pm_id
  where  (pm_id is null  and e.event_pm_id is not null) and event_status != 'trash'
  ";
  $orphancheck[]="
  select 'Event', e.event_id,  'group_id' , e.event_group_id,  eg.event_group_id group_id
  from Event e left join Event_group eg on e.event_group_id = eg.event_group_id
  where  (e.event_group_id is not null and eg.event_group_id is null)  and event_status != 'trash'
  ";

  $orphancheck[]="
  	SELECT 'Event', event_id, 'cat_id', category_id, null,
                              category_numbering,  CONCAT_WS('/',category_size ,(SELECT count(seat_id)
                                                                   FROM Seat s
                                                                   WHERE s.seat_event_id = e.event_id
                                                                   and s.seat_category_id = category_id )) , null
  	FROM Event e  left join Category c on category_event_id = event_id
  	WHERE e.event_id > 0
  		AND lower(e.event_status) not in ('unpub','trash')
  		AND lower(e.event_rep) LIKE ('%sub%')
  		AND category_size != (SELECT count(seat_id)
                            FROM Seat s
                            WHERE s.seat_event_id = e.event_id
                            and s.seat_category_id = category_id )
  ";

  $orphancheck[]="
  select 'Event', e.event_id,  'main_id'  , e.event_main_id,  me.event_id  main_id
  from Event e left join Event me on e.event_main_id = me.event_id
  where  (e.event_main_id is not null and me.event_id is null and e.event_status != 'trash')
  ";

  $orphancheck[]="
  SELECT 'Event', event_id,
     'Total',  CONCAT_WS('/', `event_total`,(select count(*) from `Seat` where seat_event_id = event_id)) seat_total, null
   FROM `Event`
  where `event_total` - (select count(*) from `Seat` where seat_event_id = event_id) <> 0 and event_status != 'trash'
  ";
  $orphancheck[]="
  SELECT 'Event', event_id,
     'Free',  CONCAT_WS('/', `event_free`, (select count(*) from `Seat` where seat_event_id = event_id and seat_status in ('res', 'free','trash') and seat_user_id IS NULL and seat_order_id IS NULL )) seat_free, null
   FROM `Event`
  where `event_free`  - (select count(*) from `Seat` where seat_event_id = event_id and seat_status in ('res', 'free','trash') and seat_user_id IS NULL and seat_order_id IS NULL ) <>0
   and event_status != 'trash'
  ";


  $orphancheck[]="
  select 'pos_user', SPoint.admin_id, 'user_id' , SPoint.admin_user_id, '[None]'
  from Admin SPoint left join User  on SPoint.admin_user_id = User.user_id
  where  (User.user_id is null)
  and    admin_status like 'pos%'
  ";

  /**/
  $orphancheck[]="
  select 'Order', o.order_id, 'user_id'  l1 ,o.order_user_id, u.user_id
  from `Order` o left join User u on o.order_user_id = u.user_id
  where  (u.user_id is null)
  ";
  $orphancheck[]="
  select 'Order', o.order_id, 'handling_id' l2 , o.order_handling_id, handling_id
  from `Order` o left join Handling on o.order_handling_id = handling_id
  where  (handling_id is null) and o.order_handling_id is not null
  ";
  $orphancheck[]="
  select 'Order', o.order_id, 'reemited_id' l3 , o.order_reemited_id, o2.order_id
  from `Order` o left join `Order` o2 on o.order_reemited_id = o2.order_id
  where  (o.order_reemited_id is not null and o2.order_id is null)
  ";
  $orphancheck[]="
  select 'Order', o.order_id, 'discount_id', 'empty', null,'promo', CONCAT_WS('', '|',o.order_discount_promo,'|'),null
  from `Order` o
  where  (o.order_discount_promo is not null and o.order_discount_id <> '' and o.order_discount_id is null)
  ";
  $orphancheck[]="
  select 'Order', o.order_id, 'discount_id', order_discount_id, null
  from `Order` o  left join `Discount` d on o.order_discount_id = d.discount_id and discount_event_id is null
  where  (o.order_discount_id is not null and o.order_discount_id <> '' and d.discount_id is null)
  ";

  /*
  $orphancheck[]="
  select 'Order', o.order_id, 'owner_id' l4 ,    o.order_owner_id, POS.user_id
  from `Order` o left join admin POS on o.order_owner_id = POS.admin_user_id
  where  (o.order_owner_id is not null and POS.user_id is null)
  ";
  /**/
  $orphancheck[]="
  select 'PlaceMap', pm_id, 'ort_id' ,pm_ort_id, ort_id
  from `PlaceMap2` left join Ort on pm_ort_id = ort_id
  where  (ort_id is null)
  ";
  $orphancheck[]="
  select 'PlaceMap', pm_id, 'event_id' ,pm_event_id, event_id
  from `PlaceMap2` left join Event on pm_event_id = event_id
  where (pm_event_id is not null and event_id is null) and event_status != 'trash'
  ";
  $orphancheck[]="
  select 'PlaceMap', pm_id, 'shadow' ,pm_event_id, null
  from `PlaceMap2` left join Event on pm_event_id = event_id
  where (pm_event_id is not null and event_pm_id != pm_id) and event_status != 'trash'
  ";
  /**/
  $orphancheck[]="
  select 'PlaceMapPart', pmp_id,'pm_id' , pmp_pm_id, pm_id
  from `PlaceMapPart` left join PlaceMap2 on pmp_pm_id = pm_id
  where (pm_id is null)
  ";
  $orphancheck[]="
  select 'PlaceMapPart', pmp_id, 'ort_id' ,pmp_ort_id, ort_id
  from `PlaceMapPart` left join Ort on pmp_ort_id = ort_id
  where  (pmp_ort_id is not null  and ort_id is null)
  ";
  $orphancheck[]="
  select 'PlaceMapPart', pmp_id, 'event_id' ,pmp_event_id, event_id
  from `PlaceMapPart` left join Event on pmp_event_id = event_id
  where  (pmp_event_id is not null and event_id is null) and event_status != 'trash'
  ";
  /**/
  $orphancheck[]="
  select 'PlaceMapZone', pmz_id, 'pm_id' ,pmz_pm_id, pm_id
  from `PlaceMapZone` left join PlaceMap2 on pmz_pm_id = pm_id
  where  (pm_id is null)
  ";
  /**/
  $orphancheck[]="
  select 'Seat', seat_id, 'event_id' ,seat_event_id, event_id
  from `Seat`      left join Event on seat_event_id = event_id
  where  (event_id is null) and event_status != 'trash'
  ";
  $orphancheck[]="
  select 'Seat', seat_id, 'cat_id' ,seat_category_id, category_id
  from `Seat`      left join Category on seat_category_id = category_id
  where  (category_id is null)
  ";
  $orphancheck[]="
  select 'Seat', seat_id, 'user_id' ,seat_user_id, user_id
  from `Seat`      left join User on seat_user_id = user_id
  where  (seat_user_id is not null and  user_id is null)
  ";
  $orphancheck[]="
  select 'Seat', seat_id, 'order_id' , seat_order_id, order_id
  from `Seat`      left join `Order` on seat_order_id = order_id
  where  (seat_order_id is not null and order_id is null)
  ";
  $orphancheck[]="
  select 'Seat', seat_id, 'pmz_id' , seat_zone_id, pmz_id
  from `Seat`      left join PlaceMapZone on seat_zone_id = pmz_id
  where  (seat_zone_id is not null and  pmz_id is null)
  ";
  $orphancheck[]="
  select 'Seat', seat_id, 'pmp_id'  l5  , seat_pmp_id,pmp_id
  from `Seat`      left join PlaceMapPart on seat_pmp_id = pmp_id and pmp_event_id = seat_event_id
  where  (seat_pmp_id is not null and pmp_id is null)
  ";
  $orphancheck[]="
  select 'Seat', seat_id, 'disc_id' l6 , seat_discount_id, discount_id
  from `Seat`      left join Discount on seat_discount_id = discount_id and discount_event_id = seat_event_id
  where  (seat_discount_id is not null and discount_id is null)
  ";
$orphancheck[]="
select 'Auth', auth_id, 'user_id' , `auth`.user_id, `User`.user_id
from `auth`      left join `User` on `auth`.user_id = `User`.user_id
where  (`auth`.user_id is not null and `User`.user_id is null)
";

$orphancheck[]="
select 'User', `User`.user_id, 'user_id' , `auth`.user_id, `User`.user_id
from `User`      left join `auth`  on `auth`.user_id = `User`.user_id
where  (`auth`.user_id is null and `User`.user_id is not null)
and `User`.user_status = 2
";

  return $orphancheck;

}
/**/
class orphans {
  static $fixes = array(
       'Category~event_id'=>'Remove this category, event is already removed',
       'Category~pm_id'=>'Remove this category, Placemap is already removed',
       'Category~pmp_id'=>'Clear the link to the removed placemapPart',
       'Category~zeros'=>'Clear all zero identifiers in the Catagory table',
       'Category~Total'=>'Reset the category statics from seat count',
       'Category~Free'=>'Reset the category statics from seat count',
       'Discount~event_id'=>'Remove this Discount, event is already removed',
       'Event~Total'=>'Reset the event statics from seat count',
       'Event~Free'=>'Reset the event statics from seat count',
       'Event~group_id'=>'Clear the link to this removed eventgroup',
       'Event~zeros'=>'Clear all zero identifiers in the event table',
       'Event~cat_id'=>'Recreate missing seats for this category',
       'Event~pm_id'=>'Clear the link to the removed placemap',
       'Event~main_id'=>'Clear the link to the removed main event',
       'Order~user_id'=>'Recreate missing user info for this order',
       'Order~owner_id'=>'Recreate missing POS login for this pos',
       'Order~discount_id'=>'Recreate missing discount identifiers',
       'Order~handling_id'=>'Clear the handling fields, we dont know what orderhandler it was.',
       'Order~Seats'=>'This order has less tickets then ordered!!!',
       'Order~zeros'=>'Clear all zero identifiers in the order table',
       'PlaceMapPart~event_id'=>'Remove this placemapPart, event is already removed',
       'PlaceMapPart~zeros'=>'Clear all zero identifiers in the PlaceMapPart table',
       'PlaceMap~event_id'=>'Remove this placemap, event is already removed',
       'PlaceMap~zeros'=>'Clear all zero identifiers in the PlaceMap table',
       'Seat~zeros'=>'Clear all zero identifiers in the seat table',
       'Seat~disc_id'=>'Clear the discount_id in from this seat',
       'Seat~order_id'=>'Release the order lock from this seats',
       'Seat~event_id'=>'Remove the seats with the already deleted event',
       'Seat~user_id'=>'Recreate missing user info for this seats',
       'Seat~cat_id'=>'Remove the seats with the deleted category',
       'Seat~pmz_id'=>'clear the seat_zone_id where the zone is deleted',
       'Spoint~user_id'=>'Recreate missing user info for this pos',
       'Auth~user_id'  =>'Remove old Auth record',
       'User~user_id'  =>'Auth record missing; make member a guest user'
  );

  static function getlist(& $keys, $showlinks= true, $property='') {
    global $_SHOP;
    $data = array();
    $keys = array();
    $trace = is($_SHOP->trace_on,false);
    $_SHOP->trace_on=false;
    $orphancheck = getOphanQuerys();
    foreach( $orphancheck as $query) {
      unset($result);
      $result = ShopDB::query($query);
      while ($row = ShopDB::fetch_row($result)) {

        if (!isset($data["{$row[0]}{$row[1]}"])){
          $r = array ('_table' => $row[0], '_id' => $row[1] );
        } else {
          $r = $data["{$row[0]}{$row[1]}"];
        }

        for( $x=2;$x< count($row); $x+=3) {
          $z = var_export ((!is_null($row[$x+1]) and $row[$x+2]===$row[$x+1])?'':$row[$x+1], true);
          if (!in_array($row[$x],$keys)){
               $keys[] = $row[$x];
            }

          if ($z !='NULL' and $z !="''" )  {
                if ($z == "'0'") {
              $thisfix =  Orphans::$fixes["{$row[0]}~zeros"];
              $fixit = "{$row[0]}~zeros";
            } elseif(isset(Orphans::$fixes["{$row[0]}~{$row[$x]}"])) {
              $thisfix =  Orphans::$fixes["{$row[0]}~{$row[$x]}"];
              $fixit = "{$row[0]}~{$row[$x]}";
            } else {
              $thisfix = '';
              $fixit = '';
            }
            $z = substr($z, 1,-1);
            if (!empty($thisfix) and $showlinks) {
              $z = "<a title='{$thisfix}'
                       href='{$_SERVER['PHP_SELF']}?fix={$fixit}~{$row[1]}~{$row[$x]}~{$row[$x+1]}{$property}'>".$z."</a>\n";
            } elseif (!empty($thisfix) and $showlinks < 0) {
              $z = "<span color='red' title='{$thisfix}'>".$z."</span>\n";
            }

            $r[$row[$x]] = $z;
          } else {
            $r[$row[$x]] = "<span color='Blue'>".$row[$x+2]."</span>\n";
          }
        }
        $data[$row[0].$row[1]] = $r;
      }
    }
    $_SHOP->trace_on= $trace;

    return $data;
  }


  static function dofix($key) {
    $fix = explode('~',$key);
    $fixit = $fix[0].'~'.$fix[1];
    //print_r( debug_backtrace());
    switch ($fixit) {
      //Fix category issues
      case'Category~Free':
        ShopDB::Query("update Category set
                         category_free = (select count(*) from `Seat`
                                          where seat_category_id = category_id
                                          and seat_status in ('res', 'free','trash')
                                          and seat_user_id IS NULL
                                          and seat_order_id IS NULL)
                       where category_id ='{$fix[2]}'") ;
        break;
      case'Category~Total':
        ShopDB::Query("update Category set
                          category_size = (select count(*) from `Seat` where seat_category_id = category_id)
                       where category_id ='{$fix[2]}'") ;
        break;
      case 'Category~event_id':
      case 'Category~pm_id':
        PlaceMapCategory::load($fix[2])->delete();
        break;
      case 'Category~pmp_id':
        ShopDB::Query("update Category set
                         category_pmp_id = null

                       where Category_id = {$fix[2]}") ;
        break;
      case 'Category~zeros':
        Orphans::clearZeros('Category', array('category_pm_id','category_event_id','category_pmp_id'));
        break;
      // fix event issues
      case 'Discount~event_id':
        ShopDB::Query("delete from Discount
                       where discount_id = '{$fix[2]}'") ;
        break;
      case'Event~Free':
        ShopDB::Query("update Event set
                          event_free = (select count(*) from `Seat` where seat_event_id = event_id and seat_status IN ('res','free','trash') and seat_user_id IS NULL and seat_order_id IS NULL )
                       where event_id ='{$fix[2]}'") ;
        break;
      case'Event~Total':
        ShopDB::Query("update Event set
                          event_total = (select count(*) from `Seat` where seat_event_id = event_id)
                       where event_id ='{$fix[2]}'") ;
        break;
      case'Event~cat_id':
        $sql = "SELECT seat_id, seat_category_id FROM Seat WHERE seat_event_id = {$fix[2]}";
        $result = ShopDB::Query($sql);
        $seats  = array();
        while ($row = ShopDB::fetch_row($result)) {
          $seats[$row[1]][] = $row[0];
        }

        $sql = "SELECT event_pm_id FROM Event e WHERE e.event_id = "._esc($fix[2]);
        $result = ShopDB::Query_one_row($sql, false);
        if (!$result) {
          addwarning('', "cant find selected order placmap");
          exit;
        }
        $pm_id = $result[0];
        $all = PlaceMapPart::loadAllFull( $pm_id);

//        echo "<pre>";
        if ($all) {
          foreach($all as $pmp) {
           // print_r($pmp->categories);
            $changed = false;
            foreach($pmp->data as $x =>&$pmp_row) {
              foreach ($pmp_row as $y=>&$seat) {
                $zone = $pmp->zones[$seat[PM_ZONE]];
                $category = $pmp->categories[$seat[PM_CATEGORY]];
                if (!isset($stats[$category->category_id])) {$stats[$category->category_id]=0;}

                if ($seat[PM_ZONE] > 0 && $seat[PM_CATEGORY] &&
                    $category->category_numbering != 'none'){
                  $stats[$category->category_id]++;
                  if (!in_array($seat[PM_ID], $seats[$category->category_id])){

                    if ($seat_id = Seat::publish($fix[2], $seat[PM_ROW], $seat[PM_SEAT],
                                                 $zone->pmz_id, $pmp->pmp_id, $category->category_id)) {
                      //echo $x,' ',$y,' ',
                      $pmp->pmp_data[$x][$y][PM_ID] = $seat_id;//,'|';
                      $changed = True;
                    }
                  }

                }
              }
            }
            if ($changed) {
              $pmp->save();
//              echo "\n------------------------------------------------------------\n";
            }
          }
        }
        $cats=PlaceMapCategory::loadAll($pm_id);

        if(!$cats){
          return self::_abort('No Categories found');
        }
     //   print_r($cats);
        foreach($cats as $cat_ident=>$cat){

          if($cat->event_status !== 'unpub' && $cat->category_numbering =='none') {
            $stats[$cat->category_id]=$cat->category_size;
            if(count($seats[$cat->category_id]) <> $cat->category_size ){//and $cat->category_size>0
              $stats[$cat->category_id] = count($seats[$cat->category_id]);
              for($i=count($seats[$cat->category_id]);$i<$cat->category_size;$i++){
                if($seat_id = Seat::publish($fix[2],null,0,null,null,$cat->category_id)) {
                  $stats[$cat->category_id]++;
                }
              }
            }
          }
          if ($cat->category_size <> $stats[$cat->category_id]) {
            $cat->category_size = $stats[$cat->category_id];
            $cat->save();
          }
        }
        break;


      case 'Event~group_id':
        ShopDB::Query("update Event set
                         event_group_id = null
                       where event_id = {$fix[2]}") ;
        break;
      case 'Event~main_id':
        ShopDB::Query("update Event set
                         event_main_id = null,
                         event_rep = 'main,sub'
                       where event_main_id = {$fix[4]}") ;

      case 'Event~pm_id':
        ShopDB::Query("update Event set
                         event_pm_id = null
                       where event_id = {$fix[2]}") ;
        break;
      case 'Event~zeros':
        If ($fix[3] =='ort_id') {
          echo "<script> window.alert('Ord_id can not be cleared you need to change this from within database editor like phpmyadmin. Ask your system manager to help');</script>";
        } else {
          Orphans::clearZeros('Event', array('event_group_id','event_main_id'));

        }
        break;
      case 'Auth~user_id':
        ShopDB::Query("delete from `auth`
                       where auth_id = {$fix[2]}") ;

        break;
      case 'User~user_id':
        ShopDB::Query("update 'User' set
                         user_status = 3
                       where user_id = {$fix[2]}") ;

        break;
      case 'Order~handling_id':
        ShopDB::Query("update `Order` set
                         order_handling_id = null
                       where order_handling_id = {$fix[4]}") ;

        break;
      case 'Order~zeros':
        Orphans::clearZeros('Order', array('order_handling_id','order_owner_id','order_reemited_id'));
        break;
      case 'Order~discount_id':
        if ($fix[4] == 'empty') {
          ShopDB::Query("update `Order` set
                           order_discount_id = (select discount_id FROM `Discount` where  discount_promo = order_discount_promo and discount_event_id is null )
                         where order_discount_promo is not null") ;
//        } else {
        }
        break;


      case 'Order~Seats':
        ShopDB::Query("update `Order` set
                         order_tickets_nr = (select count( S.seat_id ) FROM `Seat` S where  S.seat_order_id = order_id)
                       where order_id = {$fix[2]}") ;
        $sql = "SELECT order_tickets_nr FROM `Order` where order_id = {$fix[2]}";
        $resultx = ShopDB::Query_one_row($sql, false);
     //   var_dump($resultx);
        if ($resultx[0] === 0) {
          $ord = Order::load($fix[2]);
          $ord->delete ($fix[2], 'Cleaned up bvy orpahe checker. There where no tickets.');
        }
         break;
      case 'PlaceMap~event_id':
        trace('PlaceMap~event_id',true,true);
        $map = PlaceMap::load($fix[2]);
        $map->delete();
        orphanCheck();

        break;

      case 'PlaceMapPart~event_id':
        $map = PlaceMapPart::load($fix[2]);
        $map->delete();
        break;

      case 'PlaceMapPart~zeros':
        Orphans::clearZeros('PlaceMapPart', array('pmp_pm_id','pmp_ort_id','pmp_event_id'));
        break;

      case 'Seat~event_id':
        ShopDB::Query("delete from Seat where seat_event_id = {$fix[4]}") ;
        break;

      case 'Seat~cat_id':
        ShopDB::Query("delete from Seat where seat_category_id = {$fix[4]}") ;
        break;

      case 'Seat~order_id':
        ShopDB::Query("update Seat set
                         seat_order_id = null,
                         seat_user_id = null,
                         seat_ts = null,
                         seat_sid = null,
                         seat_price = null,
                         seat_discount_id = null,
                         seat_code = null,
                         seat_sales_id = null,
                         seat_status = 'free'
                       where seat_order_id = {$fix[4]}") ;
        break;
      case 'Seat~disc_id':
        ShopDB::Query("update Seat set
                         seat_discount_id = null
                       where seat_id = {$fix[2]}") ;
        break;
      case 'Seat~pmz_id':
        ShopDB::Query("update Seat set
                         seat_zone_id = null
                       where seat_zone_id = {$fix[4]}") ;
        break;
      case 'Seat~zeros':
        Orphans::clearZeros('Seat', array('seat_category_id','seat_zone_id' ,'seat_user_id' ,
                                           'seat_order_id'   ,'seat_pmp_id'  ,'seat_discount_id'));
        break;
      case 'Order~owner_id':
        ShopDB::Query("
                      INSERT INTO `Admin` (`admin_user_id`, `login`, `password`, `admin_status`) VALUES
                                           ({$fix[4]}, 'pos~demo`{$fix[4]}', 'c514c91e4ed341f263e458d44b3bb0a7', 'pos')") ;
        break;
      case 'Order~user_id':
      case 'Seat~user_id':
      case 'Spoint~user_id':
        ShopDB::Query("
                      INSERT IGNORE INTO `User` (`user_id`, `user_lastname`, `user_firstname`, `user_address`, `user_address1`,
                                                 `user_zip`, `user_city`, `user_state`, `user_country`, `user_phone`, `user_fax`,
                                                 `user_email`, `user_status`, `user_prefs`, `user_custom1`, `user_custom2`,
                                                 `user_custom3`, `user_custom4`, `user_owner_id`, `user_lastlogin`, `user_order_total`,
                                                 `user_current_tickets`, `user_total_tickets`) VALUES
                      ({$fix[4]}, 'Demo POS', '', '4321 Demo Street', '', '10000', 'Demo Town', 'DT', 'US', '(555) 555-1212', '(555) 555-1213',
                      'demo@fusionticket.test', 1, 'pdf', '', NULL, 0, '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00', 0, 0, 0)") ;
        break;


    }
  }

  static function clearZeros($table, $fields){
    $sql = "Update `$table` set ";
    $sets ='';
    foreach ($fields as $field) {
      $sets .= ", `$field` = NULLIF(`$field`,0)";
    }
  //  echo $sql.substr($sets,2) ;
    ShopDB::Query($sql.substr($sets,2));
  }

}
?>