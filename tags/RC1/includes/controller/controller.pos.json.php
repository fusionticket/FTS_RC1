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

/**
 * AJAJ will return JSON only!
 *
 * The class will follow strict rules and load the settings to see if a session is present
 * if not then will return false with a bad request status
 *
 * JSON Requests should allways use json_encode(mixed, JSON_FORCE_OBJECT)
 * Its allways good practice to turn the var into an object as
 * JSON is 'Object Notifaction'
 *
 */

if (!defined('ft_check')) {die('System intrusion ');}
$fond = 0;

require_once(CLASSES."jsonwrapper.php"); // Call the real php encoder built into 5.2+

  //include_once('CONFIG'.DS.'init_config.php');
//  var_dump($_SHOP->timezone);
  if(function_exists("date_default_timezone_set")) {
    @date_default_timezone_set($_SHOP->timezone);
  }
//error_reporting(E_ALL);


require_once ("controller.pos.checkout.php");


class ctrlPosJson extends ctrlPosCheckout {
  protected $executed   = true;
	protected $isJson = true;


  public function draw() {
    parent::drawJson();
  }

  function image($href='none', $title='', $newwidth=0,$newheight=0) {
    global $_SHOP;
    $file      = $_SHOP->files_dir.is($href,'None');
    $src       = $_SHOP->files_url .$href;
    if ($title) {$title = ' title="'.$title.'"';}
    if (!file_exists($file) || empty($href)){
      $file = ROOT. 'images'.DS. "theme".DS.$_SHOP->theme_name.DS.'na.png';
      $src  = $_SHOP->images_url . "theme/".$_SHOP->theme_name.'/'.'na.png';
    }
    list($width, $height, $type, $attr) = getimagesize($file);
  	if ($newheight==0) {$newheight =$height;}
  	if ($newwidth==0)   {$newwidth =$height;}

    if (($width>$height) and ($width > $newwidth)) {
      $attr = "width='{$newwidth}'";
    } elseif ($height > $newheight) {
      $attr = "height='{$newheight}'";
    }
    return  "<img {$attr} src='{$src}'{$title}>";
  }


	/**
	 * PosAjax::getEvents()
	 *
	 * @param datefrom ('yyyy-mm-dd') optional
	 * @param dateto ('yyyy-mm-dd') optional
	 * @param return_dates_only (true|false) If set to true, event_dates will only be returned.
	 *
	 * Will Return:
	 * 	- events
	 * 		| - id (event_id)
	 *			| - html (option html)
	 * 		  	- free_seats (tot free seats)
	 * 		| - id ....
	 * 	- event_dates
	 * 		| - date ('yyyy-mm-dd')
	 * 		| - date ('yyyy-mm-dd')
	 * 		  - date ...
	 *
	 *
	 * @return boolean : if function returned anything sensisble.
	 */
	protected function getEvents(){
	  global $_SHOP;
	  $this->json['sEcho'] = (int)  $this->request['sEcho'];
	  $this->json['aaData']     = array();
	  $str = clean( $this->request['sSearch']);
	  $fromDate = date('Y-m-d');
	  if (($timestamp = strtotime($str)) !== false) {
	   	$fromDate = date('Y-m-d', $timestamp);
	    $str = '';
	  }
	  $this->json['sSearched_for']= array($str,$fromDate);
    $where='';
	  if ($str) {
	    $where= 'AND (event_name like "'._esc($str, false).'%" or event_short_text like "'._esc($str, false).'%")';
	  }
	  $eventlinks = $_SHOP->admin->getEventLinks();
	  $eventlinks = ($eventlinks)?"FIELD(event_id,{$eventlinks})>0":'1=1';
    $this->json['events'] = array(); //assign a blank array.
	  $sql = "SELECT  SQL_CALC_FOUND_ROWS *
				FROM Event left join Ort on ort_id = event_ort_id
				           left join PlaceMap2 on pm_id = event_pm_id
				where {$eventlinks}
				AND event_date >= "._esc($fromDate)."
        AND (event_view_begin = '0000-00-00 00:00:00' OR (event_view_begin <= now()))
        and (event_view_end   = '0000-00-00 00:00:00' OR (event_view_end >= now()))
				and event_rep LIKE '%sub%'
				AND event_status = 'pub'
				AND event_free > 0
				and event_pm_id is not null
				and (select count(*) from Category c WHERE c.category_pos = 1	AND c.category_event_id = event_id) > 0
				{$where}
				ORDER BY event_date,event_time, event_name, event_id";
  if ($_POST['iDisplayLength']>0) {
    $sql .=" LIMIT {$_POST['iDisplayStart']},{$_POST['iDisplayLength']}";
  }
		if($query = ShopDB::query($sql)){
		//Load html and javascript in the json var.

  		//Break down cats and array up with additional details.
  		while($evt = ShopDB::fetch_assoc($query)){
        		$date = formatDate($evt['event_date'],con('shortdate_format'));
        		$time = formatTime($evt['event_time']);
            $open = formatTime($evt['event_open']);

  		  $this->smarty->assign('shop_event',$evt);
  		  $grid = $this->smarty->fetch('order_events.tpl');

  		  $img = $this->image( $evt['event_image'],"{$evt['event_name']} in {$evt['ort_city']}",70,95);

  			$this->json['aaData'][] = array ('image'=>$img, 'grid'=>$grid, 'free_seats'=>$evt['event_free'],'DT_RowId'=>$evt['event_id'],'DT_RowClass'=>'payment_form');

  		}
		  $this->json['iTotalRecords']= count($this->json['aaData']);
		  $this->json['iTotalDisplayRecords'] = ShopDB::foundRows();

    }
		return true;
	}

	/**
	 * PosAjax::getCategories()
	 *
	 * @param categories_only (true|false) will only return the categories if set true else grabs discounts too.
	 *
	 * Will return:
	 *  - categories
	 * 		|- id (number)
	 * 			|- html (category option)
	 * 			|- numbering (true|false)
	 * 			|- placemap (placemap html)
	 * 			|- price (number)
	 *       - free_seats (int)
	 * 		|- id.. (number)
	 * |- enable_discounts (true|false)
	 * |- discounts
	 * 		|- id (number)
	 * 			|- html (discount option)
	 * 			|- type (fixed|percent)
	 * 			 - price (number)
	 * 		|- id.. (number)
	 *
	 * @return boolean as to whether the JSON should be compiled or not.
	 */
	protected function getCategories(){
		if(!isset($this->request['event_id'])){
			return false;
		}else{
			$eventId = &$this->request['event_id'];
		}
		if(!is_numeric($eventId)){
	 		return false;
		}
		$sql = "SELECT pm_image, pm_name
			FROM PlaceMap2 pm
			WHERE pm.pm_event_id = "._esc($eventId);
		$image = ShopDB::query_one_row($sql);
		if (!empty( $image['pm_image'])) {
			$this->json['placemap'] = $this->image( $image['pm_image'],"{$image['pm_name']}");

		} else
  		$this->json['placemap'] = false; //assign a blank array.


  	$sql = "SELECT *
			FROM Category c
			WHERE c.category_pos = 1
			AND   c.category_event_id = "._esc($eventId);
		$query = ShopDB::query($sql);

		//Load html and javascript in the json var.
		$this->json['categories'] = array(); //assign a blank array.

		//Break down cats and array up with additional details.

  while($cat = ShopDB::fetch_assoc($query)){

            $this->smarty->assign('shop_category',$cat);
 			$option = $this->smarty->fetch('order_categories.tpl');
 			  //"<option value='".$cat['category_id']."'>".$cat['category_name']." - seats: {$cat['category_free']} ( ".valuta($price)." )</option>";

			$numbering = (strtolower($cat['category_numbering']) != 'none');

      if (!isset($this->request['cat_id'])) $this->request['cat_id'] = $cat['category_id'];

			$this->json['categories'][strval($cat['category_id'])] = array(
			  'html'=>$option,
              'numbering'=>$numbering,
			  'free_seats'=>$cat['category_free'],
			  'title'=>$cat['category_name']." -  ".valuta($cat['category_price'] ));
		}
		return true;
	}

  /**
   * PosAjax::getDiscounts()
   *
   * @param categories_only (true|false) will only return the categories if set true else grabs discounts too.
   *
   * Will return:
   * |- enable_discounts (true|false)
   * |- discounts
   * 		|- id (number)
   * 			|- html (discount option)
   * 			|- type (fixed|percent)
   * 			 - price (number)
   * 		|- id.. (number)
   *
   * @return boolean as to whether the JSON should be compiled or not.
   */
  protected function getDiscounts(){
    if(!isset($this->request['event_id'])){
      return false;
    }else{
      $eventId = &$this->request['event_id'];
      $catId   = &$this->request['cat_id'];
    }
    if(!is_numeric($eventId)){
      return false;
    }

    //Select Events Discounts
    $sql = "select discount_id, discount_name, discount_value, discount_type
    	FROM Discount d
    	WHERE  (FIND_IN_SET('yes', discount_active)>0 or FIND_IN_SET('pos', discount_active)>0)
    	and discount_event_id = "._esc($eventId);
    if ($catId) {
       $sql .=  " AND (discount_category_id="._esc($catId)." OR discount_category_id is null)";
    }
    $query = ShopDB::query($sql);

    //We count the number of rows to see if we should bother running through discounts.
    $numRows = ShopDB::num_rows($query);

    //		if($numRows > 0){
    //Define json array for discounts
    $this->json['enable_discounts'] = false; //enable discounts.
    $this->json['discounts'] = array(); //assign a blank array.
    //Add the  "None Discount"
    $this->json['discounts'][] = array('html'=>"<option value='0' selected='selected'> ".con('normal')." </option>",'type'=>'fixed','price'=>0);
    while($disc = ShopDB::fetch_assoc($query)){
      //Check to see if percent or fixed
      $this->json['enable_discounts'] = true; //enable discounts.
      if(strtolower($disc['discount_type']) == 'percent' ){
        $option = "<option value='".$disc['discount_id']."'>".$disc['discount_name']." - ".$disc['discount_value']."%</option>";
        $type = "percent";
      }else{
		$price = valuta($disc['discount_value']);
        $option = "<option value='".$disc['discount_id']."'>".$disc['discount_name']." - ".$price."</option>";
        $type = "fixed";
      }
      //Load up each row
      $this->json['discounts'][] = array('html'=>$option,'type'=>$type,'price'=>$price);
    }
    //		}else{
    //			$this->json['enable_discounts'] = false; //disable discounts.
    //		}
    return true;
  }



	/**
	 * PosAjax::_pre_items()
	 *
	 * This is part of the cartlist
	 * @return n one.
	 */
  function _pre_items (&$event_item,&$cat_item,&$place_item,&$data){
    $data[]=array($event_item,$cat_item,$place_item);
  }

	/**
	 * PosAjax::getCartInfo()
	 *
	 * @param categories_only (true|false) will only return the categories if set true else grabs discounts too.
	 *
	 * @return boolean as to whether the JSON should be compiled or not.
	 */
	protected function getCartInfo(){
	  global $_SHOP;
    $this->json['sEcho'] = $_POST['sEcho'];
	  $this->json['aaData']     = array();
	  $this->json['userdata'] = array('can_cancel' => false, 'handlings' => array(), 'total' => valuta(0.0), 'requered_fields'=>array());

	  $this->json['userdata']['requered_fields'] = false;//$_SHOP->grapes_requiredPosFields;

	  $this->json['userdata']['can_cancel'] = !$this->__MyCart->is_empty_f() or isset($_SESSION['_SHOP_order']);

	  if (!isset($_SESSION['_SMART_cart'])) return true;

    $mycart=$_SESSION['_SMART_cart'];
    $cart_list  =array();
    if($mycart and !$this->__MyCart->is_empty_f()){
      $mycart->iterate(array(&$this,'_pre_items'),$cart_list);
    }


    $counter  = 0;
    $subprice = 0.0;
	  $act = false;
    foreach ($cart_list as $cart_row) {
      $event_item    = $cart_row[0];
      $category_item = $cart_row[1];
      $seat_item     = $cart_row[2];
      $seat_item_id  = $seat_item->id;
      $seats         = $seat_item->seats;
      $disc          = $seat_item->discount(reset($seats)->discount_id);
      $seatinfo = '';
      if ($seat_item->is_expired()) { continue; }

      if($category_item->cat_numbering=='rows'){
        $rcount=array();
        foreach($seats as $seat){
          $rcount[$seat->seat_row_nr]++;
        }
        foreach($rcount as $row => $count){
          $seatinfo .= ", {$count} x ".con('row')." {$row}";
        }
      } elseif (!$category_item->cat_numbering or $category_item->cat_numbering == 'both'){
        foreach($seats as $places_nr){
 					$seatinfo .= ", {$places_nr->seat_row_nr} - {$places_nr->seat_nr}";
        }
      }
      if($category_item->cat_numbering!=='none'){// && is_array($_SHOP->grapes_requiredPosFields)
        $this->json['userdata']['requered_fields'] = true;//array_merge($this->json['userdata']['requered_fields'], $_SHOP->grapes_requiredPosFields );
      }

      $seatinfo = substr($seatinfo,2);
//      var_dump($seat_item); die();
      if (!empty($seat_item->ordered)) {
            $col = "<font color='red'>".con('ordered').'</font>';
      } else {
        if ($seat_item->is_expired()) {
            $col = "<font color='red'>".con('expired').'</font>';
      	} else {
      	    $col = $seat_item->ttl()." min.";
        }
        $col ="<form id='remove' class='remove-cart-row' name='remove{$seat_item_id}' action='ajax.php?x=removeitemcart' method='POST' >".
     		 		 "<input type='hidden' value='{$event_item->event_id}' name='event_id' />".
      		 	 "<input type='hidden' value='{$category_item->cat_id}' name='category_id' />".
      		 	 "<input type='hidden' value='{$seat_item_id}' name='item' />".
             "<button type='submit' class='ui-widget-content jqgrow remove-cart-row-button'
                      style='display: inline; cursor: pointer; padding:0; margin: 0; border: 0px'> ".
             "<img src='../images/trash.png' style='display: inline; cursor: pointer;padding:0; margin: 0; border: 0px' width=16></button> ".
 //            $col.
  			     "</form>";
  //  			 "<input type='hidden' value='remove" name="action" />
      }
      $row = array('Expire_in'=>$col);
      $row['Event'] = "<b>{$event_item->event_name}</b> - {$event_item->ort_name}<br>".
               formatdate($event_item->event_date,con('shortdate_format'))."  ".formatdate($event_item->event_time,con('time_format'));
 //     $row[] = count($seats);
      $col = "{$category_item->cat_name}";
      if ($seatinfo) {
        $col = "<acronym title='{$seatinfo}'>$col</acronym>";
      }
      $val ='';
      if ($disc) {
        $col .= ' ('. valuta($disc->apply_to($category_item->cat_price)).')';
      } else {
        $col .= ' ('. valuta($category_item->cat_price).')';
      }
      if ($disc) {
   	    $col .= "<br><i>".con('discount_for')." ".$disc->discount_name.'</i>';
      }
      $row['Tickets'] = count($seats).' x '. $col;
    	$subprice += $seat_item->total_price($category_item->cat_price);

  		$row['Total'] = valuta($seat_item->total_price($category_item->cat_price));
      $row['DT_RowId'] = "{$event_item->event_id}|{$category_item->cat_id}|{$seat_item_id}";
      $row['DT_RowClass'] = 'payment_form';
  		$this->json['aaData'][] = $row; //array('DT_RowId'=> "{$event_item->event_id}|{$category_item->cat_id}|{$seat_item_id}", 'cell'=> $row);
  		$counter++ ;
		}
    include_once('shop_plugins'.DS.'block.handling.php');
    $sql = 'SELECT `handling_id`, `handling_fee_fix`, `handling_fee_percent`, `handling_fee_type`, `handling_fee_perseat`
            FROM `Handling`
            WHERE handling_sale_mode LIKE "%sp%"';

 		if(check_event($this->__MyCart->min_date_f())){
			$sql .= " and handling_alt <= 3";
		} else {
   		$sql .= " and handling_alt_only='No'";
		}

    $res=ShopDB::query($sql);
		$totalprice = ($_SHOP->shoppos_allow_without_cost ||($_POST['no_cost']!=='1'))?$subprice:0.0;
    $handlings = array();
    while ($pay=shopDB::fetch_assoc($res)){
      $fee = calculate_fee($pay, $subprice, (!$mycart)?0:$mycart->count());
      if (($_POST['handling_id']== $pay['handling_id'] and $counter and $_POST['no_fee']!=='1')) { // and !$counter and $_POST['no_fee']!==1
        $totalprice += $fee;
      }
      $fee = ($fee == 0.00)? '': '+ '.valuta($fee);
      $handlings[] = array('index'=>"#price_{$pay['handling_id']}", 'value'=>$fee);
    }
    $this->json['userdata']['handlings'] = $handlings;
    $this->json['userdata']['total']     = valuta($totalprice);
    $this->json['userdata']['can_order'] = $counter !== 0;
	  $this->json['iTotalRecords'] = $this->json['iTotalDisplayRecords'] = count($this->json['aaData']);
//    print_r($this->json);
		return true;
	}


	protected function getPlaceMap(){
		if(!isset($this->request['category_id'])){
		  addWarning('bad_category_id');
			return false;
		}else{
			$catId = &$this->request['category_id'];
		}
		if(!is_numeric($catId)){
		  addWarning('bad_category_id');
	 		return false;
		}

		$sql = "SELECT *
			FROM Category c
			WHERE c.category_id = "._esc($catId);
		$cat = ShopDB::query_one_row($sql);

		if(strtolower($cat['category_numbering']) != 'none'){
//      $placemap = "<div style='overflow: auto; height: 450px; width:800px;' align='center' valign='center'>";
			$placemap = $this->loadPlaceMap($cat);
//      $placemap .= "</div>";
			$this->json['placemap'] = $placemap;
			return true;
		}
    addWarning('not_placemap');
		return false;
	}

	protected function getUserSearch(){
	  $this->json['sEcho'] = $_POST['sEcho'];
	  $this->json['aaData']     = array();

    $fields = ShopDB::fieldlist('User');
    	$where = '';
   		foreach($_POST as $field => $data) {
      		if (in_array($field,$fields) and strlen(clean($data))>1) {
     			if ($where){ $where.='and ';}
        		$where.= "({$field} like "._esc('%'.clean($data).'%').") \n";
  			}
   		}
   		if (!$where) $where = '1=2';

	   	$this->json['POST'] = $where;

		$sql = "SELECT user_id, CONCAT_WS(', ',user_lastname, user_firstname) AS user_data,
               	user_phone, user_city, user_email
			    	FROM `User`
        		WHERE {$where}";// and user_owner_id =". $_SESSION['_SHOP_AUTH_USER_DATA'][;
		$query = ShopDB::query($sql);
		$numRows = ShopDB::num_rows($query);
	    $this->json['page'] = 1;
	    $this->json['total'] = 1;
	    $this->json['records'] = 0;
	    $this->json['userdata'] = array();

		while($user = ShopDB::fetch_assoc($query)){
		  $user['DT_RowId'] =$user['user_id'];
			$this->json['aaData'][] =  $user;
		}
	  $this->json['iTotalRecords'] = $this->json['iTotalDisplayRecords'] = count($this->json['aaData']);
	  return true;
	}

	protected function getCanprint(){
		if($this->request['orderid']){
			$orderid = $this->request['orderid'];
		}else{
		  addWarning('bad_order_id');
			return false;
		}

		$sql = "SELECT order_payment_status
            FROM `Order`
            WHERE order_id="._esc($orderid);
    	$q = ShopDB::query_one_row($sql);
 	  	$this->json['status'] = $q['order_payment_status']=='paid';
      $this->json['show'] = true;
		return true;
	}

	protected function getUserData(){
		$sql = "SELECT *
            FROM `User`
            WHERE user_id="._esc($_POST['user_id']);
		$query = ShopDB::query($sql);
		$numRows = ShopDB::num_rows($query);
		if($numRows > 0){
  	  $this->json['user'] = ShopDB::query_one_row($sql);
 			return true;
    }
    addWarning('user_not_exsist');
		return false;
	}

	/**
	 * PosAjax::loadPlaceMap()
	 *
	 * @param mixed $category
	 * @return placemap html
	 */
	private function loadPlaceMap($category){
    global $_SHOP;
    require_once("shop_plugins".DS."function.placemap.php");
		return placeMapDraw($category, true, true, 'pos', 16, -1); //return the placemap
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
  	$coco = $_SESSION;
    $event_id = is($this->request['event_id'],0);
    $category_id = is($this->request['category_id'],0);
    $discount_id = is($this->request['discount_id'],0);
    if($event_id <= 0){
      addWarning('wrong_event_id');
      return false;
    }


    $res = $this->__MyCart->CartCheck($event_id, $category_id, $this->request['place'], $discount_id, 'mode_pos', false, false);
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


  protected function doPosConfirm(){
    $fond=null;
//    require ("controller/pos_template.php");
    try{
      $checkoutRes = $this->_posConfirm();
    }catch(Exception $e){
      addWarning('unknown_error',$e->getMessage());
      return false;
    }
		$this->logUserVisit(); // Lxsparks
    $this->json['htmltp'] = $checkoutRes;

    if(is_string($checkoutRes)){
      $this->json['html'] = $this->smarty->fetch($checkoutRes . '.tpl');
      return true;
    }elseif(is_bool($checkoutRes) and !$checkoutRes){
   //   echo "hello";
      return false;
    }
  }

  protected function doPosSubmit(){
    $checkoutRes = $this->actionSubmit();
    if(is_string($checkoutRes)){
      $this->json['html'] = $this->smarty->fetch($checkoutRes . '.tpl');
      return true;
    }else {
      return false;
    }
  }

  protected function doPosCancel () {
    $this->__MyCart->destroy_f(true);
    return true;
  }


  private function _posConfirm () {

    if ((int)$_POST['handling_id']==0) { // Checks handling is selected
        addWarning('no_handling_selected');//.print_r($_POST,true);
        return false;

    } elseif ((int)$_POST['user_id']==-2) { //Checks that a user type is selected.
        addWarning('no_useraddress_selected');
        return false;

    } elseif ((int)$_POST['user_id']==-1) { //if "No User" use the POS user
      // THis is the POS user that the admin account is linked too.
      $user_id = $_SESSION['_SHOP_AUTH_USER_DATA']['admin_user_id'];
      if(!$user_id){
        addWarning('admin_user_id_blank');
        return false;
      }
      $this->__User->load_f($user_id);

    } elseif ((int)$_POST['user_id']==0) {

      $query="SELECT count(*) as count
              from User
              where user_email="._esc($_POST['user_email']);
      if($row = ShopDB::query_one_row($query) and $row['count']>0){
        addWarning('useralreadyexist');
        return false;
      }
      //if new user selected put the pos user as the owner of the order
      $this->json['newuser_id'] = 'new user';
      $_POST['user_owner_id'] = $_SESSION['_SHOP_AUTH_USER_DATA']['admin_id'];
      $user_id = $this->__User->register_f( 4, $_POST, 0, '', true);
     // addwarning( "new id: $user_id");
      $this->ErrorsAsWarning = true;
      if (!$user_id || hasErrors() ) {
      	$this->json['newuser_id'] = $user_id ;
        return false;
      } else {
        $this->assign('newuser_id', $user_id);
				$_SESSION['new_user_id']=$user_id;
      }
    } else {
      $user_id = $_POST['user_id'];

//    	if (!User::UpdateEx($member)) {
 //   		return false;
  //  	}
    }
    $no_fee  = is($_POST['no_fee'], 0);
    $no_cost = is($_POST['no_cost'], 0);
    unset($_SESSION['_SHOP_order']) ;
    if((int)$_POST['handling_id'] === 1){
      $return = $this->actionReserve('pos', $user_id);
    }else{
      $return = $this->actionConfirm('pos', $user_id, $no_fee, $no_cost);
    }
    if ($return == 'checkout_preview' ) {
      return false;
    }else {
      return $return;
    }
  }

	function logUserVisit() { // Lxsparks
		if ($_POST['user_id']) {
			$user_id=$_POST['user_id'];
		} elseif (isset($_SESSION['new_user_id'])) {
			$user_id=$_SESSION['new_user_id'];
			unset($_SESSION['new_user_id']);
		} else {
			addWarning('no_user_set');
			return false;
		}
		$query="UPDATE User SET user_lastlogin=now() WHERE user_id="._esc($user_id);
		if(!$res=ShopDB::query($query)){
      addWarning(shopDB::error());
      return false;
    }
		return false;
	}
}
?>