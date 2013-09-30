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

class Seat  Extends Model {

  const STATUS_FREE = 'free';
  const STATUS_ORDERED = 'com';
  const STATUS_RESERVED = 'resp';
  const STATUS_HOLD = 'res';
  const STATUS_CANCELLED = 'cancel';
  const STATUS_TRASH = 'trash';
  const STATUS_SENT = 'sent';


  protected $_idName    = 'seat_id';
  protected $_tableName = 'Seat';
  protected $_columns   = array('#seat_id', '*seat_event_id', '*seat_category_id', '#seat_user_id', '#seat_order_id',
                                '#seat_row_nr', '#seat_zone_id', '#seat_pmp_id', 'seat_nr', 'seat_ts', 'seat_sid',
                                'seat_price', 'seat_discount', '#seat_discount_id', 'seat_code', '*seat_status',
                                '#seat_old_order_id', 'seat_old_status');


  function ticket ($event_id,$category_id,$seat_id, $user_id, $sid, $cat_price, $discount=null) {
    //$seat = self::load($seat_id);
    $seat = new Seat();
    $seat->_columns   = array('#seat_id', '#seat_user_id', '#seat_order_id', 'seat_ts', 'seat_sid',
                              'seat_price', '#seat_discount_id', 'seat_code', '*seat_status');
    $seat->seat_event_id=$event_id;
    $seat->seat_category_id=$category_id;
    $seat->seat_id=$seat_id;
    $seat->seat_user_id=$user_id;
    $seat->seat_sid = $sid;
    if(isset($discount)){
      $seat->seat_price=$discount->apply_to($cat_price);
      $seat->seat_discount_id=$discount->discount_id;
    }else{
      $seat->seat_price=$cat_price;
    }
    return $seat;
  }

  function load($seatId){
  }

  function loadAllEvent ($event_id){
    global $_SHOP;

    $query="SELECT seat_id, seat_user_id, seat_order_id, seat_ts, seat_sid, seat_price, seat_discount_id, seat_code, seat_status, seat_row_nr, seat_nr
            from Seat
            where seat_event_id="._esc($event_id);
    if($res=ShopDB::query($query)){
      while($rec=shopDB::fetch_assoc($res)){
        $seat = new Seat;
        $seat->_columns   = array('#seat_id', '#seat_user_id', '#seat_order_id', 'seat_ts', 'seat_sid',
                              'seat_price', '#seat_discount_id', 'seat_code', '*seat_status');
        $seat->_fill($rec);
        $pmp[$seat->seat_id]=$seat;
      }
    }
    return $pmp;
  }

  public function loadAllOrder($order_id){
    global $_SHOP;

    $query="SELECT seat_id, seat_user_id, seat_order_id, seat_ts, seat_sid, seat_price, seat_discount_id, seat_code, seat_status, event_name, category_name, seat_row_nr, seat_nr
            FROM Seat left join `Event` on seat_event_id = event_id
                      left join `Category` on seat_category_id = category_id
            WHERE seat_order_id="._esc($order_id);
    if($res=ShopDB::query($query)){
      while($rec=shopDB::fetch_assoc($res)){
        $seat = new Seat;
        $seat->_columns   = array('#seat_id', '#seat_user_id', '#seat_order_id', 'seat_ts', 'seat_sid',
                              'seat_price', '#seat_discount_id', 'seat_code', '*seat_status');
        $seat->_fill($rec);
        $pmp[$seat->seat_id]=$seat;
      }
    }
    return $pmp;
  }

  function load_pmp_all ($pmp_id){
    global $_SHOP;

    $query="select seat_id, seat_status, seat_ts
            from Seat
            where seat_pmp_id="._esc($pmp_id);
    if($res=ShopDB::query($query)){
      while($seat=shopDB::fetch_assoc($res)){
        $pmp[$seat['seat_id']]=$seat;
      }
    }
    return $pmp;
  }

  function order_id ($order_id=0){
    if($order_id){
      $this->seat_order_id=$order_id;
    }
    return $this->seat_order_id;
  }

  function save ($reservate= false, $discount=null){
    if(!$this->seat_order_id){ return FALSE; }
    $this->seat_code=$this->generate_code(8);
    $this->seat_status=($reservate)?'resp':'com';

    $this->seat_price=number_format($this->seat_price, 2, '.', '');
    return parent::save();
  }


  // Selects and reserves seats... Very Complex
  /**
   * @param (bool) force - use force to force current reserved ordered seats to back into the cart.
   *
   * On error fill 'place'=>"seat:[lineNo]"
   */
  function reservate ($sid, $event_id, $category_id, $seats, $numbering, $reserved, $force=false, $mode='mode_web'){

    global $_SHOP;
    $_SHOP->seat_error=0;

    //TODO: This needs to use the database_time + res_delay so timezones dont get confused.

  	// if reserved is enabled it lets you book reserved seats handy for splitting big booking.
  	if($reserved==true || $force == true) {
  	  $status="AND seat_status IN ('free','resp') ";
  	}else{
  	  $status="AND seat_status='free' ";
  	}

    if(!ShopDB::begin('Reservate seats')){
      addWarning('internal_error','seat:62');
      return FALSE;
    }

    //numbering none: choose any $seats seats ($seats is a number)
    // Open seating....

    //Forcing seats back into the cart.
    if($force) {
      //TODO: Check for order_id and lock if possible
      //TODO: lock order to stop other users trying to reorder the same order.

      $seats_id=$seats;
      foreach($seats_id as $seat_id){
       $query="SELECT seat_id, seat_pmp_id
                FROM Seat
                WHERE seat_event_id="._esc($event_id)."
                AND seat_category_id="._esc($category_id)."
                AND seat_id="._esc($seat_id)."
                $status
                LIMIT 1 FOR UPDATE";

        if(!$res=ShopDB::query($query)){
          ShopDB::rollback('cant lock seat');
          addWarning('internal_error','seat:90');
          return FALSE;
        }

        if(!$row=ShopDB::fetch_assoc($res)){
          ShopDB::rollback('Cant find seat');
          addWarning('places_occupied');
      	  return FALSE;
      	}else{
      	  $pmps_id[$row['seat_pmp_id']]=1;
      	}
      }
    } elseif($numbering=='none'){
      $seats_id = array();

      $query="SELECT seat_id
              FROM Seat
              WHERE seat_event_id="._esc($event_id)."
              AND seat_category_id="._esc($category_id)."
              $status
              LIMIT ".(int)$seats." FOR UPDATE";

      if(!$res=ShopDB::query($query)){
        ShopDB::rollback('cant lock seats');
        addWarning('internal_error','seat:115');
        return FALSE;
      }

      //register selected seats ids
      while($row=shopDB::fetch_assoc($res)){
        $seats_id[]=$row['seat_id'];
      }

      //is there less seats available that asked for? dono, return error
      if(count($seats_id)<$seats){
        ShopDB::rollback('Not engough seats to reservate');
        addWarning('places_toomuch',' remains:'.count($seats_id));
        return FALSE;
      }

    }elseif($numbering=='both' or $numbering=='rows' or $numbering=='seat') {
      $seats_id=$seats;

      foreach($seats_id as $seat_id){
        $query="SELECT seat_id,seat_pmp_id
                FROM Seat
                WHERE seat_event_id="._esc($event_id)."
                AND seat_category_id="._esc($category_id)."
                AND seat_id="._esc($seat_id)."
                $status
                LIMIT 1 FOR UPDATE";
        if(!$res=ShopDB::query($query)){
          ShopDB::rollback('cant lock seat');
          addWarning('internal_error','seat:142');
          return FALSE;
        }

  	    if(!$row=shopDB::fetch_assoc($res)){
          ShopDB::rollback('Cant find seat');
          addWarning('places_occupied');
      	  return FALSE;
      	}else{
      	  $pmps_id[$row['seat_pmp_id']]=1;
      	}
      }

    //some strange thing happens
    }else{
      ShopDB::rollback("unknown place_numbering $numbering category $category_id");
      addWarning("unknown_place_numbering","$numbering category $category_id");
      return FALSE;
    }

    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////// RESERVE CODE
    //here we have seats_ids to reservate
    //reserving them one by one
    foreach($seats_id as $seat_id){
      $query="UPDATE Seat SET
                seat_old_status = seat_status,
                seat_old_order_id = seat_order_id,
                seat_status='res',
                seat_ts= UNIX_TIMESTAMP()+"._esc($_SHOP->res_delay).",
                seat_sid="._esc($sid)."
              WHERE seat_id="._esc($seat_id);

     if(!ShopDB::query($query)){
        ShopDB::rollback('cant update seat');
        addWarning('internal_error', 'seat:189');
        return FALSE;
      }else{
        //place taken by someone in the middle
        if(ShopDB::affected_rows()!=1){
          ShopDB::rollback('seat not changed');
          addWarning('places_occupied');
          return FALSE;
        }
      }
    }
    $query="UPDATE Seat SET
              seat_ts=UNIX_TIMESTAMP()+"._esc($_SHOP->res_delay)."
            WHERE  seat_ts >=UNIX_TIMESTAMP()
            and seat_sid="._esc($sid);

    if(!ShopDB::query($query)){
      ShopDB::rollback('cant update seat time');
      return FALSE;
   }

    //invalidate cache
    if(!empty($pmps_id) && is_array($pmps_id)){
      foreach($pmps_id as $pmp_id=>$v){
        PlaceMapPart::clear_cache($pmp_id);
      }
    }

    //commit the reservation
    if(!ShopDB::commit('Seats reservated')){
      addWarning('internal_error','seat:211');
      return FALSE;
    }

    return $seats_id;
  }



  //the order is cancelled -> moves places to 'free' status and
  //updates stats
  //$seats = array(array('seat_id'=>,'event_id'=>,category_id=>,pmp_id=>))
  function cancel($seats, $user_id, $nocommit=FALSE){
    global $_SHOP;
    if(!ShopDB::begin('cancel seats')){
      return FALSE;
    }

    foreach($seats as $seat){
      $query="UPDATE `Seat` set seat_status='free',
            		seat_ts=NULL,
            		seat_sid=NULL,
            		seat_user_id=NULL,
            		seat_order_id=NULL,
            		seat_price=NULL,
            		seat_discount_id=NULL,
            		seat_code=NULL
              where seat_id="._esc($seat['seat_id'])."
  	          and seat_event_id="._esc($seat['event_id'])."
	            and seat_category_id="._esc($seat['category_id']);
    //echo "<div class=info>$query</div>";

      if(!ShopDB::query($query)){ //echo a;
        ShopDB::rollback('cant_cancel_seat_1');
        return FALSE;
      }else{
        if(shopDB::affected_rows()!=1){//echo b;
           ShopDB::rollback('cant_cancel_seat_2');
           return FALSE;
        }
      }
      $event_stat[$seat['event_id']]++;
      $category_stat[$seat['category_id']]++;
      $pmp_check[$seat['pmp_id']]=1;
    }

    foreach($category_stat as $cat=>$count){
      if (!PlaceMapCategory::inc_stat($cat, $count)) {
        return ShopDB::rollback('cant_cancel_seat_3');//echo c;
      }
    }

    foreach($event_stat as $event=>$count){
      if (!Event::inc_stat($event, $count)) {
        return ShopDB::rollback('cant_cancel_seat_4');//echo c;
      }
    }

    if(!empty($pmp_check)){//print_r($pmp_check);
      foreach($pmp_check as $pmp_id=>$v){
        if ($pmp_id and !PlaceMapPart::clear_cache($pmp_id)) {
          return self::_abort('cant_cancel_seat_5');//echo d;;
        }
      }
    }
    if(!$nocommit and !ShopDB::commit('Cancelled_seats')){ //echo e;
    	return FALSE;
    }
    return TRUE;
  }

  public function getCount($options){

    $query = "SELECT count(seat_id) ticketcount FROM Seat WHERE 1=1";

    if(is($options['seat_user_id'],false)){
      $query .= " AND seat_user_id="._esc($options['seat_user_id']);
    }
    if(is($options['status'],false)){
      $query .= " AND seat_status="._esc($options['status']);
    }

    if($row = ShopDB::query_one_row($query)){
      return $row['ticketcount'];
    }else{
      return 0;
    }
  }


  function free ($sid, $event_id, $category_id, $seats){
    global $_SHOP;
    if(ShopDB::begin('free seats')){

      foreach($seats as $seat_id){

        if (is_object($seat_id)) {
          $seat_id = $seat_id->places_id;
        }
        $query="select seat_pmp_id
                from `Seat`
          where seat_id="._esc($seat_id)."
          and seat_sid="._esc($sid)."
          and seat_status='res'
          and seat_event_id="._esc($event_id)."
          and seat_category_id="._esc($category_id)."
          FOR UPDATE";

        if(!$row=ShopDB::query_one_row($query)){
          ShopDB::rollback('cant lock seats');
      //    return addWarning('cant lock seats');
        }else{
          $pmps_id[$row['seat_pmp_id']]=1;
        }

        $query="UPDATE `Seat`
                set seat_status='free',
                seat_ts=NULL,
                seat_sid=NULL
                where seat_id="._esc($seat_id)."
                and seat_sid="._esc($sid)."
                and seat_status='res'
                and seat_event_id="._esc($event_id)."
                and seat_category_id="._esc($category_id);

        if(!ShopDB::query($query)){
          ShopDB::rollback('cant update seats');
          return FALSE;

        }else{
          if(shopDB::affected_rows()!=1){
            ShopDB::rollback('seat not changed');
            return FALSE;
          }
        }
      }

      //invalidate cache
      if(!empty($pmps_id)){
        foreach($pmps_id as $pmp_id=>$v){
          PlaceMapPart::clear_cache($pmp_id);
        }
      }
      return ShopDB::commit('Seats freeed');
    }

    return TRUE;
  }

  function publish ($seat_event_id, $seat_row_nr, $seat_nr,
		                $seat_zone_id, $seat_pmp_id, $seat_category_id){
    global $_SHOP;
    $seat_zone_id  =($seat_zone_id===0)?null:$seat_zone_id;
    $seat_event_id =($seat_event_id===0)?null:$seat_event_id;
    $seat_pmp_id   =($seat_pmp_id===0)?null:$seat_pmp_id;
    $seat_category_id =($seat_category_id===0)?null:$seat_category_id;

    $query="INSERT INTO Seat SET
            seat_event_id="._esc($seat_event_id).",
            seat_row_nr="._esc($seat_row_nr).",
            seat_nr="._esc($seat_nr).",
            seat_zone_id="._esc($seat_zone_id).",
            seat_pmp_id="._esc($seat_pmp_id).",
            seat_category_id="._esc($seat_category_id).",
            seat_status='free'";

    if(ShopDB::query($query)){
      return ShopDB::insert_id();
    }
  }

  static function generate_code ($length){
    $chars = "0123456789";

    $code = '' ;

    for($i=0;$i <$length;$i++) {
        $code.=$chars{rand()%10};
    }

    return $code;
  }

	static function encodeBarcode ( $order= null, $ticket= null) {
		global $_SHOP;
		$barcode=sprintf("%08d%08d", $ticket['seat_id'], $ticket['seat_code']);
		$barcode = plugin::call('updateOrderEncodeBarcode', $barcode, $order, $ticket);
		return $barcode;
	}

	static function decodeBarcode ($barcode) { // Lxsparks 2013
		global $_SHOP;
	  $barcode = plugin::call('updateOrderDecodeBarcode', $barcode);
		if (is_array($barcode)) {
			return $barcode;
		}
		return sscanf(str_pad($barcode, 16, "0", STR_PAD_LEFT),"%08d%08d");
	}

  static function reIssue ($order_id,$seat_id,$code_length=8){
    global $_SHOP;

    if(ShopDB::begin('Re-Issue Ticket')){

      $query = "SELECT *
                FROM `Seat`
                WHERE 1=1
                  AND seat_id="._esc($seat_id)."
                  AND seat_order_id="._esc($order_id)."
                FOR UPDATE";

      $res = ShopDB::query($query);
      if(!$res || ShopDB::affected_rows()<>1){
        addWarning('ticket_not_reissued',"Seat ID: $seat_id (1)");
        ShopDB::rollback('Failed to find the re-issue ticket');
        return false;
      }

      $new_code=self::generate_code($code_length);

      $query="UPDATE Seat
              SET seat_code="._esc($new_code)."
              WHERE seat_id="._esc($seat_id)."
              and seat_order_id="._esc($order_id)."
              LIMIT 1";

      if(!ShopDB::query($query) or ShopDB::affected_rows()!=1){
        addWarning('ticket_not_reissued',"Seat ID: $seat_id (2)");
        ShopDB::rollback('Failed to update the re-issue ticket');
        return FALSE;
      }

      if(!OrderStatus::statusChange($order_id,false,null,'Seat::reIssue',"Seat ID: $seat_id, Old Ticket Invalid")){
        addWarning('ticket_not_reissued',"Seat ID: $seat_id (3)");
        return false;
      }

      if(ShopDB::commit("Commit Ticket Re-Issue")){
         addNotice('ticket_reissued',"Seat ID: $seat_id ");
         return True;
      }
    }
    addWarning('ticket_not_reissued',"Seat ID: $seat_id (4)");
    return false;
  }
}
?>