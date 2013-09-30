<?php
/**
%%%copyright%%%
 *
 * FusionTicket - ticket reservation system
 *  Copyright (C) 2007-2013 FusionTicket Solution Limited . All rights reserved.
 *
 * Original Design:
 *  phpMyTicket - ticket reservation system
 *   Copyright (C) 2004-2005 Anna Putrino, Stanislav Chachkov. All rights reserved.
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
class PlaceMapCategory Extends Model {
  protected $_idName    = 'category_id';
  protected $_tableName = 'Category';
  protected $_columns   = array( '#category_id', '#category_event_id', '*category_price', '*category_name',
                                 '*category_pm_id', '#category_pmp_id', 'category_ident', '*category_numbering',
                                 'category_size', 'category_max', 'category_min', 'category_template',
                                 '*category_color', 'category_data', 'category_free', 'category_web', 'category_pos');

  static function create($category_pm_id=0,
                  $category_name=0,
                  $category_price=0,
                  $category_template=0,

                  $category_color=0,
                  $category_numbering=0,
                  $category_size=0,
                  $category_event_id=null,
				  $category_web=1,
				  $category_pos=1)
  {
    $new = new PlaceMapCategory;
      $new->category_pm_id=$category_pm_id;
      $new->category_name=$category_name;
      $new->category_price=$category_price;
      $new->category_template=$category_template;
      $new->category_color=$category_color;
      $new->category_numbering=$category_numbering;
      $new->category_size=$category_size;
      $new->category_event_id=(int)$category_event_id;
	  $new->category_web=$category_web;
	  $new->category_pos=$category_pos;
    return $new;
  }

  static function load ($category_id){
    $query="select c.*, e.event_status
            from Category c 
            LEFT JOIN Event e ON event_id=category_event_id
            where category_id="._esc($category_id);

    if($res=ShopDB::query_one_row($query)){
      $new_category=new PlaceMapCategory;
      $new_category->_fill($res);
      $new_category->category_color = self::resetColor($new_category->category_color);
      return $new_category;
    }
  }

  static function loadFull ($category_id){
    $query="select c.*, e.event_status
            from Category c 
            LEFT JOIN Event e ON event_id=category_event_id
            where category_id="._esc($category_id);

    if($res=ShopDB::query_one_row($query)){
      $new_category=new PlaceMapCategory;
      $new_category->_fill($res);
      $new_category->category_color = self::resetColor($new_category->category_color);
      return $new_category;
    }
  }

  static function loadAll ($pm_id){
    $query="select c.*, e.event_status
            from Category c 
            LEFT JOIN Event e ON event_id=category_event_id
            where category_pm_id=$pm_id";
    $cats = array();
    if($res=ShopDB::query($query)){
      while($data=shopDB::fetch_assoc($res)){
        $new_cat=new PlaceMapCategory;
        $new_cat->_fill($data);
        $new_cat->category_color = self::resetColor($new_cat->category_color);
        $cats[$new_cat->category_ident]=$new_cat;
      }
    }

    return $cats;
  }

  function save($id = null, $exclude=null){
    if(!$this->category_ident){
      $this->category_ident = $this->_find_ident($this->category_pm_id);
    }

    return parent::save($id, $exclude);
  }

  function delete (){
    $seats = shopDB::query_one_row("select count(*) from Seat
                                    where seat_category_id ="._esc($this->id), false);
    if (empty($seats) || $seats[0]>0 ) {
      return  self::_abort('Category_delete_failed_seats_exists');
    }

    if(ShopDB::begin('delete category: '.$this->id)){
      $query="DELETE c.*
              FROM Category c
              WHERE c.category_id="._esc($this->id);
      if(!ShopDB::query($query)){
        return self::_abort('Category_delete_failed');
      }

      if($pmps=PlaceMapPart::loadAll($this->category_pm_id) and is_array($pmps)){
        foreach($pmps as $pmp){
          if($pmp->delete_category($this->category_ident) && !$pmp->save()) {
            return self::_abort('Category_delete_failed_on_pmps');
          }
        }
      }
      return ShopDB::commit('Category deleted');
    }
  }


  function change_size($new_size){
    return $this->increment_size($new_size-$this->category_size);
  }

  function increment_size($delta){
    if($delta==0){
      return addwarning("new_category_size_not_different");
    }
    if($this->event_status!='nosal'){
      return addwarning("event_status_incorrect");
    }
    if($this->category_numbering!='none'){
      return addwarning("category_numbering_incorrect");
    }
    $new_category_size = $this->category_size+$delta;

    if($new_category_size<0 || ($delta+$this->category_free)<0){
      return addwarning('new_category_size_is_to_small');
    }

    if(ShopDB::begin('resize category')){

      $new_cs_total=$new_category_size;
      $new_cs_free=$delta+$this->category_free;

      $query="SELECT event_free, event_total FROM Event
              WHERE event_id='{$this->category_event_id}'
              FOR UPDATE";
      if(!$es=ShopDB::query_one_row($query)){
        return self::_abort('cant_lock_event');
      }

      if(($delta+$es['event_free'])<0){
        return self::_abort('size_to_small_for_event');
      }

      $new_es_total= $delta+$es['event_total'];
      $new_es_free = $delta+$es['event_free'];


      if($delta>0){
        for($i=0;$i<$delta;$i++){
          if(!Seat::publish($this->category_event_id,0,0,0,0,$this->category_id)){
            return false;// self::_abort('Cant publish new seats');
          }
        }
      } else {
        $limit=-$delta;

        $query="DELETE FROM Seat
                  where seat_category_id='{$this->category_id}'
                  and seat_event_id='{$this->category_event_id}'
                  and seat_status='free'
                  LIMIT $limit";

        if(!ShopDB::query($query)){
          return self::_abort('cant_delete_old_seats');
        }
        if(shopDB::affected_rows()!=$limit){
          return self::_abort('different_no_of_seatsremoved');//'Different No off seats removed'
        }
      }

      $query="UPDATE Event SET
                event_free='$new_es_free',
                event_total='$new_es_total'
              WHERE event_id='{$this->category_event_id}'
              LIMIT 1";

      if(!ShopDB::query($query) || shopDB::affected_rows()!=1){
        return self::_abort('cant_update_event_stat');
      }

      $this->category_free =$new_cs_free;
      $this->category_size =$new_category_size;

      if(!$this->save()){
        return self::_abort('cant_save_category');
      }

      return ShopDB::commit('Category resized');
    }
  }

  function _fill(&$arr,$nocheck= true){
    if ($arr['category_numbering']<>'none' && !$arr['category_pmp_id'] &&
        isset($this->event_status) && $this->event_status == 'unpub') {
      $arr['category_size'] = 0;
    }
    $arr['category_color'] = self::resetColor($arr['category_color']);
    return parent::_fill($arr, $nocheck);
  }

  /**
   * PlaceMapZone::_find_ident()
   * Search the first not used ident value in the table within the given placemap
   * @param mixed $pmz_pm_id
   * @return Integer the new value
   */
  function _find_ident ($pm_id){
    $query="select category_ident
            from Category
            where category_pm_id="._esc($pm_id) ."
            order by category_ident";
    if(!$res=ShopDB::query($query)){return;}
    $ident = array();
    while($i=shopDB::fetch_assoc($res)){
      $ident[]=$i['category_ident'];
    }

    $category_ident=1;
    while(in_array($category_ident, $ident)) { $category_ident++; }
    return $category_ident;
  }

  function getCategoryNumbering($category_id = 0){
    if (isset($that) and $this->category_numbering) {
      return $this->category_numbering;
    } else {
      $query="select category_numbering
              from Category
              where category_id="._esc($category_id);
      if(!$res=ShopDB::query_one_row($query)){return;}
      return $res['category_numbering'];
    }
  }

  static function resetColor($color){
    if (is_numeric($color)) {
      if(ShopDB::TableExists('Color') ){
        $row = ShopDB::query_one_row('select color_code from Color where color_id ='._esc($color), false);
        $color = $row[0];
      } else {
        $color = '';
      }
    }
    return $color;
  }

  static function dec_stat ($category_id, $count){
    $query="UPDATE Category SET category_free=category_free-{$count}
            WHERE category_id="._esc($category_id)." LIMIT 1";
    if(!ShopDB::query($query) or shopDB::affected_rows()!=1){
      return FALSE;
    }else{
      return TRUE;
    }
  }

  static function inc_stat ($category_id, $count){
    $query="UPDATE Category SET category_free=category_free+{$count}
            WHERE category_id="._esc($category_id)." LIMIT 1";
    if(!ShopDB::query($query) or shopDB::affected_rows()!=1){
      return FALSE;
    }else{
      return TRUE;
    }
  }
}
?>