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
class Discount  Extends Model {
  protected $_idName    = 'discount_id';
  protected $_tableName = 'Discount';
  protected $_columns   = array( '#discount_id', '*discount_type', '*discount_value', '*discount_name','#discount_category_id',
                                 '#discount_event_id', 'discount_promo', 'discount_cond', 'discount_active');
  function __construct($filldefs= false, $event_id=null){
    parent::__construct($filldefs);
    if ($filldefs) {
      $query="SELECT event_pm_id
              FROM Event where event_id="._esc($event_id);
      if($row=ShopDB::query_one_row($query)){
        $row['discount_event_id'] = $event_id;
        $this->_fill($row);
      }
    }
  }

  //static
  function load ($id){
    $query="SELECT Discount.*, event_pm_id
            FROM Discount left join Event on event_id=discount_event_id
            WHERE discount_id="._esc($id);
    if($row=ShopDB::query_one_row($query)){
      $new = new Discount;
      $new->_fill($row);
      $new->_unser_extra();
      return $new;
    }
  }

  function loadAll ($event_id){
    $query="SELECT *
            FROM Discount
            Where discount_event_id ="._esc($event_id);
    if($res=ShopDB::query($query)){
      $discounts = array();
      while($row=shopDB::fetch_assoc($res)){
        $new = new Discount;
        $new->_fill($row);
        $new->_unser_extra();
        $discounts[]= $new;
      }
      return $discounts;
    }
  }

  static function loadPromoCode($promocode, $order = null, $place='www') {
  	$promocode = plugin::call('updatePromoCode', $promocode, $order, $place);
  	if (is_string($promocode)) {
  		$query="SELECT Discount.*
            FROM Discount
            WHERE discount_event_id ".(is_numeric($order)?'= '._esc($order):'is null')."
            and (FIND_IN_SET('yes', discount_active)>0 or FIND_IN_SET('{$place}', discount_active)>0)
						and discount_promo =" ._esc($promocode);
  		if($row=ShopDB::query_one_row($query)){
  			$new = new Discount;
  			$new->_fill($row);
  			$new->_unser_extra();
  			return $new;
  		}
  		return false;
  	} else {
  		return $promocode;
  	}
  }

  static function hasGlobals($place='www') {
    $query = "SELECT count(*) count
              from Discount
              where discount_event_id is null
              and (FIND_IN_SET('yes', discount_active)>0 or FIND_IN_SET('{$place}', discount_active)>0)";
    $count = ShopDB::query_one_row($query);
    return (is($count['count'], 0) != 0);
  }

  function delete(){
    if (ShopDB::begin('Delete discount')) {
      $query = "SELECT count(*) count
                from Seat
                where seat_discount_id="._esc($this->id);
      if (!($count = ShopDB::query_one_row($query)) || (int)$count['count']) {
        return addWarning('in_use');
      }
      $query = "SELECT count(*) count
                from `Order`
                where order_discount_id="._esc($this->id);
      if (!($count = ShopDB::query_one_row($query)) || (int)$count['count']) {
        return addWarning('in_use');
      }
      if (!parent::delete()){
        return self::_abort('cant delete discount');
      } else
        return ShopDB::commit('Deleted discount');
    }
  }

  function save($id = null, $exclude=null){
    $this->_ser_extra();
    return parent::save($id, $exclude);
  }

  function copy($event_main_id, $event_sub_id, $copylist) {
    $discs = self::LoadAll($event_main_id);
    foreach ($discs as $disc) {
      $disc->discount_event_id = $event_sub_id;
      unset($disc->discount_id);
      if ($disc->discount_category_id) {
        $disc->discount_category_id = $copylist[$disc->discount_category_id];
      }
      $disc->save();
    }
  }

	function CheckValues($data){
 		if(empty($data['discount_event_id']) && empty($data['discount_promo']) ){addError('discount_promo','mandatory');}
    if(empty($data['discount_event_id']) && !empty($data['discount_promo'])) {
      $query = "select count(*) count from Discount where discount_promo ="._esc($data['discount_promo'])." and discount_id !="._esc($data['discount_id']);
      $count = ShopDB::query_one_row($query);
      if ((int)$count['count']) {
        return addError('discount_promo','promo_in_use', ' global');
      }
    } elseif(!empty($data['discount_event_id']) && !empty($data['discount_promo'])) {
      $query = "select count(*) count from Discount where (discount_event_id is null or discount_event_id ="._esc($data['discount_event_id']).") and discount_promo ="._esc($data['discount_promo'])." and discount_id !="._esc($data['discount_id']);
      $count = ShopDB::query_one_row($query);
      if ((int)$count['count']) {//
        return addError('discount_promo','promo_in_use',' Event');
      }
    }
		return parent::CheckValues($data);
	}

  function apply_to ($price){
    if($this->discount_type=='fixe'){
      return $price-$this->discount_value;
    }elseif($this->discount_type=='percent'){
      return $price*(1.0-$this->discount_value/100.0);
    }else{
      user_error("unknown discount type ".$disc['discount_type']);
      return FALSE;
    }
  }

  function value ($price){
    if($this->discount_type=='fixe'){
      return $this->discount_value;
    }elseif($this->discount_type=='percent'){
      return $price*($this->discount_value/100.0);
    }else{
      user_error("unknown discount type ".$disc['discount_type']);
      return FALSE;
    }
  }

  function total_value ($price,$qty=1){
    if($this->discount_type=='fixe'){
      return $qty*$this->discount_value;
    }elseif($this->discount_type=='percent'){
      return $qty*$price*$this->discount_value/100.0;
    }else{
      user_error("unknown discount type ".$disc['discount_type']);
      return FALSE;
    }
  }

  function isUsed($count= 1) {
    $query = "update Discount set discount_used = discount_used + ".(int)$count."
              where discount_id="._esc($this->id);
    ShopDB::query($query);
  }

  function _ser_extra(){
    If (is_array($this->discount_active)) {
      $this->discount_active = implode(",", $this->discount_active);
    }
  }

  function _unser_extra(){
    If (is_string($this->discount_active)) {
      if ($this->discount_active=='yes') {
        $this->discount_active = array('www','pos');
      } elseif ($this->discount_active=='no') {
        $this->discount_active = array();
      } elseif (!empty($this->discount_active))  {
        $this->discount_active = explode(",", $this->discount_active);
      } else {
        $this->discount_active = array();
      }
    }
  }
}
?>