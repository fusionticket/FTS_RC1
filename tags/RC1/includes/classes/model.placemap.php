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
 * the packaging of this file.p
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

class PlaceMap Extends Model {
  protected $_idName    = 'pm_id';
  protected $_tableName = 'PlaceMap2';
  protected $_columns   = array( '#pm_id','*pm_ort_id','#pm_event_id','*pm_name', '^pm_image');

  static function create ($pm_ort_id, $pm_name){
    $pm = new PlaceMap;
    $pm->pm_ort_id=$pm_ort_id;
    $pm->pm_name=$pm_name;
    return $pm;
  }

  function load ($pm_id){
    $query="select *
           from PlaceMap2 left join Ort on pm_ort_id=ort_id
           where pm_id="._esc($pm_id);
    if($res=ShopDB::query_one_row($query)){
      $new_pm=new PlaceMap;
      $new_pm->_fill($res);
      return $new_pm;
    }
  }

  function loadAll ($ort_id){
    $query="select *
            from PlaceMap2 left join Ort on pm_ort_id=ort_id
            where ort_id="._esc($ort_id);
    if($res=ShopDB::query($query)){
      while($data=shopDB::fetch_assoc($res)){
        $new_pm=new PlaceMap;
        $new_pm->_fill($data);
        $pms[]=$new_pm;
      }
    }
    return $pms;
  }
  function save($id = null, $exclude=null){
    if (!empty($this->pm_ort_id)) {
      return parent::save($id, $exclude);
    } else {
      addwarning('missing_pm_ord_id');
    }
  }

  function publish($pm_id, $event_id, &$stats, &$pmps, $dry_run=FALSE){
    if(!$dry_run){ShopDB::begin('Publish placemap');}

    $parts=PlaceMapPart::loadAll($pm_id, true);
    if(!empty($parts)){
      foreach($parts as $part){
        if (! $part->publish($event_id, 0, $stats, $pmps, $dry_run)) {
          return false;
        }
      }
    }

    $cats=PlaceMapCategory::loadAll($pm_id);
    if(!$cats){
      return self::_abort('No Categories found');
    }

    foreach($cats as $cat_ident=>$cat){
      if($cat->category_numbering=='none' and $cat->category_size>0){
        if(!$dry_run){
          for($i=0;$i<$cat->category_size;$i++){
            if( !Seat::publish($event_id,0,0,0,0,$cat->category_id)) {
               return self::_abort('pm_publish4_a');
            }
          }
        }
        $stats[$cat->category_ident]+= $cat->category_size;
      }
      if(!$dry_run){
        $cat->category_free = $stats[$cat->category_ident];
        if($cat->category_numbering !== 'none'){
          $cat->category_size   = $stats[$cat->category_ident];
          $cat->category_pmp_id = $pmps[$cat->category_ident];
        }
        if ($cat->category_size ==0) {
           return self::_abort('cant_publish_event_cat_no_size');
        } elseif($cat->category_numbering !=='none' and !$cat->category_pmp_id){
           return self::_abort('cant_publish_event_cat_not_connect');
        }
        if (!$cat->save())
          return self::_abort('pm_publish4_b');
      }

    }

    if($dry_run or ShopDB::commit('placemap publised')){
      return TRUE;
    }

  }

  function delete (){
  GLOBAL $_SHOP;
    if(ShopDB::begin('delete Placmap: '.$this->pm_id)){
      if (!$this->pm_id) return self::_abort('Cant_delete_without_id');
      if ($this->pm_event_id){
        $seats = shopDB::query_one_row("select count(*) from Seat
                                       where seat_event_id ={$this->pm_event_id}", false);
        if ($seats[0]>0) {
          return self::_abort('placemap_delete_failed_seats_exists');
        }
      }

      $query="delete from PlaceMapZone where pmz_pm_id={$this->pm_id}";
      if(!ShopDB::query($query)){
        return  placemap::_abort('placemapzone_stat_delete_failed');
      }

      $query="DELETE c.*
              FROM Category c
              WHERE c.category_pm_id={$this->pm_id}";
      if(!ShopDB::query($query)){
        return self::_abort('Category_delete_failed');
      }

      $query="delete from PlaceMapPart where pmp_pm_id={$this->pm_id} ";
      if(!ShopDB::query($query)){
        return self::_abort('PlaceMapPart_delete_failed');
      }

      $query="delete from PlaceMap2 where pm_id={$this->pm_id} limit 1";
      if(!ShopDB::query($query)){
        return self::_abort('placemap_delete_failed');
      }
      if (ShopDB::commit('PlaceMap deleted')) { // Lxsparks - moved to after db transaction
  			if(!empty($this->pm_image) && file_exists($_SHOP->files_dir.$this->pm_image)) { // Lxsparks delete PM image
  				if(!unlink($_SHOP->files_dir.$this->pm_image)) {
				    addWarning('cant_delete_image');
				  }
			  }
        return true;
      }
		  return false;
    }
  }

  function copy ($event_id=''){
    global $_SHOP;
    $old_id=$this->pm_id;
    $old_img = $this->pm_image;
    $this->pm_image = null;
    unset($this->pm_id);

    if($event_id){
      $this->pm_event_id=$event_id;
    }

    if(ShopDB::begin('copy Placmap to event: '.$event_id)){
      if($new_id=$this->save()){
        if (!empty($old_img)) {

          if (!preg_match('/\.(\w+)$/', $old_img, $ext)) {
            addwarning('img_loading_problem_match');
          } elseif(file_exists($_SHOP->files_dir .$old_img)) {
            $doc_name =  "pm_image_{$this->id}.{$ext[1]}";
            if (@ !copy ($_SHOP->files_dir .$old_img, $_SHOP->files_dir .$doc_name)) {
              addWarning('img_loading_problem_copy', $name);
            } else {
              @chmod($_SHOP->files_dir . $doc_name, $_SHOP->file_mode);
              $this->save();
            }
          } else {
          	$this->pm_image =  "";
          	addWarning('pm_img_file_not_found');
          }
        }
        if($zones=PlaceMapZone::loadAll($old_id)){
          foreach($zones as $zone){
            unset($zone->pmz_id);
            $zone->pmz_pm_id=$new_id;
            $zone->pmz_event_id=$event_id;
            if (!$zone->save()) {
              return ShopDB::rollback('copying zones Failed, event: '.$event_id);
            }
          }
        }
        $this->copylist = array();
        if($cats=PlaceMapCategory::loadAll($old_id)){
          foreach($cats as $cat){
            $cid = $cat->category_id;
            unset($cat->category_id);
            $cat->category_pm_id=$new_id;
            $cat->category_event_id=$event_id;
            if (!$cat->save()) {
              return ShopDB::rollback('copying Categories Failed, event: '.$event_id);
            }
            $this->copylist[$cid] = $cat->category_id;
          }
        }

        if($zones=PlaceMapPart::loadAll($old_id)){
          foreach($zones as $zone){
            unset($zone->pmp_id);
            $zone->pmp_pm_id=$new_id;
            $zone->pmp_event_id=$event_id;
            if (!$zone->save()) {
              return ShopDB::rollback('copying parts Failed, event: '.$event_id);
            }
          }
        }
      } else
        return ShopDB::rollback('copied Placmap to event: '.$event_id);
      if (ShopDB::commit('copied Placmap to event: '.$event_id)){
        return $new_id;
      }
    }
  }

  function split ($pm_parts=0,$split_zones=true){
    if(!is_array($pm_parts)) { return false; }

    if(ShopDB::begin('Split Placmap')){
      $index=PlaceMapCategory::_find_ident($this->pm_id);
      $parts=PlaceMapPart::loadAll($this->pm_id);

      foreach($parts as $part_small){
        if(!in_array($part_small->pmp_id, $pm_parts)){continue;}

        $part=PlaceMapPart::loadFull($part_small->pmp_id);
        if($part->split($index, $cats, $old_cats, $split_zones)){
          $part->save();
        }
      }

      if($cats){
        foreach($cats as $cat){
          $cat->save();
        }
      }

      if($old_cats){
        foreach($old_cats as $cat){
          $cat->save();
        }
      }
    }
    return ShopDB::commit('copied Placmap to event:');
  }
}
?>