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


require_once("classes/class.mycart.php");
	require_once("shop_plugins".DS."block.discount.php");

	require_once("shop_plugins".DS."function.placemap.php");


require_once(CLASSES."jsonwrapper.php"); // Call the real php encoder built into 5.2+
require_once (CLASSES.'class.controller.php');

class ctrlWebJson Extends Controller  {
  public    $session_name = "ShopSession";
	protected $isJson = true;

  public function draw() {
    return parent::drawJson();
  }

  public function getDiscountpromo() {
    $discount = Discount::load($this->request['id']);
    if (!empty($discount->discount_promo)) {
      $promo = $this->request[$this->request['name']];
      $this->json = (strtoupper($promo) == strtoupper($discount->discount_promo));
    } else {
      $this->json = false;
    }
    return true;
  }

	public function getCheckEventPromo() {
		$discount = Discount::loadPromoCode($this->request['promo'],(int)$this->request['event_id'],'www');
		if ($discount) {
			if (!isset($_SESSION['_EVENTDISCOUNTS']) || !is_array($_SESSION['_EVENTDISCOUNTS']) ) {
				$_SESSION['_EVENTDISCOUNTS'] = array();
			}
			if (in_array($discount->discount_id,$_SESSION['_EVENTDISCOUNTS'] )) {
				$_SESSION['_EVENTDISCOUNTS'][] = $discount->discount_id;
			}
			$this->json['discount_id'] =$discount->discount_id;
		} else {
			$this->json['discount_id'] = false;
			$this->jsonreason = con('eventdiscount_invalid');
			return false;
		}

	  $result = shopdb::query_one_row('select category_price cat_price from `Category` where category_id ='._esc($this->request['category_id']));
		$this->_loadDiscountlist($this->request['event_id'],$this->request['category_id'],$result);
		return true;
	}

  public function getPlacemap(){
    global $_SHOP;
    error_reporting(0);

    if(!isset($this->request['category_id'])){
      addWarning('bad_category_id');
      return true;
    }else{
      $catId = &$this->request['category_id'];
    }
    if(!is_numeric($catId)){
      addWarning('bad_category_id');
      return true;
    }
    $sql = "SELECT *
    	FROM Category c
    	WHERE 1=1
    	AND c.category_id = "._esc($catId);
    $result = ShopDB::query_one_row($sql);

    $cart = is( $_SESSION['_SMART_cart'], null);
    if (isset($cart)) {
      $has = $cart->total_places($result['category_event_id'],$result['category_id']);
      $result['category_free'] = $result['category_free'] - $has ;
    }
    $this->json['cat'] =$result;
    $this->json['placemap'] = placeMapDraw($result, false, true, 'www', 16, $this->request['seatlimit']); //return the placemap

  	$this->_loadDiscountlist($result['category_event_id'],$result['category_id'],array('cat_price'=>$result['category_price']));
    return true;
  }

  function _loadDiscountlist($event_id, $category_id, $params){
  	$promos = array();
  	if (isset($_SESSION['_EVENTDISCOUNTS']) && is_array($_SESSION['_EVENTDISCOUNTS'])) {
  		foreach ($_SESSION['_EVENTDISCOUNTS'] as $key ) {
  			$promos[] = _esc($key);
  		}
  	}
  	$where = " where (FIND_IN_SET('yes', discount_active)>0  or  FIND_IN_SET('www', discount_active)>0) ";
  	$where .= " and discount_event_id=" . _esc($event_id);
  	$where .= " and (discount_category_id is null or discount_category_id = " . _esc($category_id).')';

  	if (!empty($promos)) {
  		$where .= " and (discount_promo is null OR discount_promo ='' OR field(discount_id , " . implode(', ',$promos).'))';
  	} else {
  		$where .= " and (discount_promo is null OR discount_promo ='')";
  	}

  	$query = "select * from Discount $where";

  	$res = ShopDB::query($query);
  	$discounts = array();
  	while ($discount = shopDB::fetch_assoc($res)) {
  		calcprice($discount, $params);
  		$discounts[$discount['discount_id']] = $discount;
  	}
  	$this->json['discounts'] = $discounts;
  }

  /**
   * @name add to cart function
   *
   * Used to add seats to the cart. Will check if the selected seats are free.
   *
   * @param event_id : required
   * @param category_id : required
   * @param seats : int[] (array) or int : required
   * @param mode : where the order is being made options('mode_web'|'mode_kasse')
   * @param reserved : set to true if you want to reserve only.
   * @param discount_id
   * @return boolean : will return true if that many seats are avalible.
   */
  protected function doAddToCart() {
    global $_SHOP;

    $event_id = is($this->request['event_id'],0);
    $category_id = is($this->request['category_id'],0);
    $discount_id = is($this->request['discount_id'],0);
    if($event_id <= 0){
      addWarning('wrong_event_id');
      return false;
    }
    $this->json['rev'] = $_SERVER ;//Secure::getFormToken(is($this->request['name'],'orderevent'), true);
    $res = $this->__MyCart->CartCheck($event_id, $category_id, $this->request['place'], $discount_id, 'mode_web', false, false);
    if($res){
      //    $this->json['reason']=$res;
      $this->json['status']=true;

      return true;
    }else{
      return false;
    }
  }

  protected function doRemoveItemCart (){
    $event_id = is($this->request['event_id'],0);
    $cat_id = is($this->request['category_id'],0);
    $item = is($this->request['item'],0);

    if($event_id < 1 || $cat_id < 1 || !is_numeric($item)){
      addWarning('wrong_input_ids');
      return false;
    }
    $this->__MyCart->remove_item_f($event_id,$cat_id,$item);
    return true;
  }
}
?>