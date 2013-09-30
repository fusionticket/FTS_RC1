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

class Cart {

	// sessions.docx for cart layout
  public $event_list; //array, indexed by event_id
  public $cat_list;
  public $disc_list;
  public $items;
  public $ts;

  function __construct(){
    Global $_SHOP;
    $this->event_list = array(); //array, indexed by event_id
    $this->cat_list   = array();
    $this->disc_list  = array();
    $this->items      = array();
    $this->ts         = time()+$_SHOP->cart_delay;
  }

  public function add($event_id, $cat_id, $seat_ids, $discount_id=0, $mode='mode_web', $reserved =false, $force=false){
    if(empty($this->event_list[$event_id])){
      $this->event_list[$event_id]= $this->loadEvent($event_id);
    }
    if(empty($this->cat_list[$cat_id])){
      $this->cat_list[$cat_id]= $this->loadCat($event_id,$cat_id);
    }
    $this->items[] = new placeItem($this, $event_id, $cat_id, $seat_ids);

    $id=end(array_keys($this->items));
    $item =& $this->items[$id];
    $item->id=$id;
    if ($discount_id) {
      $this->set_discounts($event_id, $cat_id, $id, $discount_id);
    }
    return $item;
  }

  function set_discounts($event_id, $cat_id, $id, $discount_id){
    $item = $this->items[$id];
    if (!is_array($discount_id)) {
      $discount_id = (int)$discount_id;
      if($discount_id && empty($this->disc_list[$discount_id])){
        $this->disc_list[$discount_id]= $this->loadDisc($discount_id);
      }

      foreach( $item->seats as $key => &$value) {
        $value->discount_id = $discount_id;
      }
    } elseif(is_array($discount_id) and (count($discount_id)==$item->count()))  {
      foreach( $item->seats as $key => &$value) {
        if($discount_id[$key] && empty($this->disc_list[$discount_id[$key]])){
          $this->disc_list[$discount_id[$key]]= $this->loadDisc($discount_id[$key]);
        }
        $value->discount_id =$discount_id[$key];
      }
    }
  }

  public function remove($place_id, $event_id=null, $cat_id = null ){
    foreach ($this->items as  $key => $item ){
      $freeme = true;
      $freeme = $freeme && (($event_id==null) || ($event_id==$item->event_id));
      $freeme = $freeme && (($cat_id==null)   || ($cat_id  ==$item->category_id));
      $freeme = $freeme && (($place_id==null) || ($place_id==$item->id));
      if ($freeme) {
        $item->remove();
        unset($this->items[$key]);
      }
    }
  }

  function total_price(){
    $total_price=0;
    foreach($this->items as $item){
      $total_price+=$item->total_price();
    }
    return $total_price;
  }

  function use_alt(){
    $use_alt=0;
    foreach($this->items as $event){
      $use_alt += $event->useAlter();
    }
    if($use_alt>=1){
    	return true;
    }else{
   		return false;
  	}
  }
  function min_date (){
    $min_date=true;
    foreach($this->items as $item){
      $event = $this->event_list[$item->event_id];
      $min_date=min($event->event_date.' '.$event->event_time, $min_date);
    }
    return $min_date;
  }

  function total_places($event_id=0,$cat_id=0,$only_valid=TRUE){
    $total_places=0;
    foreach ($this->items as  $key => $item ){
      $freeme = true;
      $freeme = $freeme && (($event_id==null) || ($event_id==$item->event_id));
      $freeme = $freeme && (($cat_id==null) || ($cat_id==$item->category_id));
      if ($freeme) {
        $total_places += $item->total_places ($only_valid=TRUE);
      }
    }
    return $total_places;
  }
  function count(){
    $count=0;
    foreach($this->items as $item) {
      if (!$item->is_expired ()) {
        $count += $item->count();
      }
    }
    return $count;
  }

  function is_empty (){
    $count=0;
    foreach($this->items as $item) {
      if (!$item->is_expired ()) {
        $count++;
      }
    }
    return $count==0;
  }

  function can_checkout (){
    $count=0;
    foreach($this->items as $item) {
      if (!$item->is_expired ()) {
        $count++;
      }
    }
    return $count > 0;
  }

  function iterate ($iter_func, &$data, $all= false){
    $x = 0;
    foreach($this->items as $key => $item){
       call_user_func_array($iter_func,array(&$this->event_list[$item->event_id], &$this->cat_list[$item->category_id], &$item, &$data));
       if ($item->is_expired ()){
         $this->items[$key]->remove();
       }
    }
  }

  function overview (){
    global $_SHOP;

    $data=array('valid'=>0,
                'expired'=>0,
                'minttl'=>$_SHOP->cart_delay,
                'secttl'=>$_SHOP->cart_delay);
    $classname = "Cart";
    $this->iterate(array($classname,'_overview'),$data, true);
    return $data;
  }


  function _overview ($event_item, $cat_item, $place_item, &$data){
    if($place_item->is_expired()){
      $data['expired']++;
    }else{
      $data['valid']+=$place_item->count();
      $data['minttl']=min($data['minttl'],$place_item->ttl());
      $data['secttl']=min($data['secttl'],$place_item->ttlsec());
    }
    return TRUE;
  }

  protected function loadEvent($event_id){
    global $_SHOP;
    $qry="select event_id, event_name, event_date, event_time, event_ort_id, ort_name, ort_city, event_order_limit
            from Event left join Ort on event_ort_id=ort_id
            where event_id='{$event_id}' ";
    $row = ShopDB::query_one_object($qry);
    $row->event_use_alt = check_event($row->event_date);
    return $row;
  }

  protected function loadCat ($event_id, $cat_id){
    $qry="select category_id cat_id, category_event_id, category_name cat_name, category_price cat_price, category_numbering AS cat_numbering
           from Category where category_id='{$cat_id}' and category_event_id='{$event_id}'";
    return ShopDB::query_one_object($qry);
  }

  protected function loadDisc($discount_id){
    return Discount::load($discount_id);
  }
  function event($id){
    return $this->event_list[$id];
  }
  function cat($id){
    return $this->cat_list[$id];
  }
  function disc($id){
    return $this->disc_list[$id];
  }
}


class PlaceItem {
  var $id;
  var $cart;
  var $event_id;
  var $category_id;
  var $seats;
  var $ts;
  var $created;
  function __construct ($cart, $event_id, $category_id, $seat_ids){
    global $_SHOP;
    $this->date = date('c');
    $this->cart = $cart;
    $this->event_id=$event_id;
    $this->category_id=$category_id;
    $this->seats = array();
    $this->expired = false;
    if (is_array($seat_ids)) {
      $this->loadInfo($seat_ids);
    } else {
//      raise;
      die ('oeps');
    }
    $this->cart->ts=time()+$_SHOP->cart_delay;
  }

  function remove(){
    Seat::free(session_id(), $this->event_id, $this->category_id, $this->seatids());
    unset($this->cart->items[$this->id]);
  }

  function use_alt(){
    return $this->cart->event_list[$this->event_id]['event_use_alt'];
  }

  function count (){
    return count($this->seats);
  }

  function is_expired (){
    if (!$this->expired) {
      $this->expired = time()>$this->cart->ts;
    }
    return $this->expired;
  }

  function ttl (){
    return intval(floor(($this->cart->ts-time())/60));
  }
  function ttlsec (){
    return intval(floor(($this->cart->ts-time())));
  }

  function total_price (){
    if($this->is_expired()){
      return 0;
    }else{
      $cat = $this->cart->cat($this->category_id);
      $res= $this->count()*$cat->cat_price;
      foreach ($this->seats as  $seat){
        if ($seat->discount_id) {
          $discount = $this->cart->disc($seat->discount_id);
          $res-=$discount->total_value($cat->cat_price,1);
        }
      }
      return $res;
    }
  }

  function total_places ($only_valid=TRUE){
    if(!$only_valid or !$this->is_expired()){
      return $this->count();
    } else {
      return 0;
    }
  }

  protected function loadInfo ($seats){
    global $_SHOP;
    $places = implode(', ', $seats);
    $qry="select seat_id, seat_row_nr, seat_nr, seat_ts
          from Seat
          where field(seat_id, {$places})
          and   seat_category_id="._esc($this->category_id)."
          and   seat_event_id="._esc($this->event_id);
    if($result=ShopDB::query($qry)) {
      while ($obj=shopDB::fetch_object($result)){
        $this->seats[$obj->seat_id] = $obj;
        $obj->discount_id = 0;
      }
    } else{
  	  $this->invalid=TRUE;
  	  return FALSE;
    }
    return TRUE;
  }
  function seatids(){
    return array_keys($this->seats);

  }
  function discount($discount_id= -1){
    if ($discount_id== -1) {
      $x = reset($this->seats);
      return $this->cart->disc($x->discount_id);
    } elseif ((int)$discount_id) {
      return $this->cart->disc((int)$discount_id);

    } else {
      return null;
    }
  }
}

?>