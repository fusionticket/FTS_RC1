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
class PlaceMapZone Extends Model {
  protected $_idName    = 'pmz_id';
  protected $_tableName = 'PlaceMapZone';
  protected $_columns   = array( '#pmz_id','*pmz_pm_id','*pmz_ident','*pmz_name', '*pmz_color','pmz_short_name');

  function create ($pmz_pm_id=null, $pmz_name=null, $pmz_short_name=null, $pmz_color=null){
    $new = new PlaceMapZone;
    if($pmz_pm_id){
      $new->pmz_pm_id=$pmz_pm_id;
      $new->pmz_name=$pmz_name;
      $new->pmz_short_name=$pmz_short_name;
      $new->pmz_color=$pmz_color;
    }
    return $new;
  }

   function load ($pmz_id){
    global $_SHOP;
    $query="select *
            from PlaceMapZone
            where pmz_id="._esc($pmz_id);

    if($res=ShopDB::query_one_row($query)){
      $new_pmz=new PlaceMapZone;
      $new_pmz->_fill($res);

      return $new_pmz;
    }
  }

  function loadAll ($pm_id){
    global $_SHOP;

    $query="select *
            from PlaceMapZone
            where pmz_pm_id="._esc($pm_id);

    if($res=ShopDB::query($query)){
      while($data=shopDB::fetch_assoc($res)){
        $new_pmz=new PlaceMapZone;
        $new_pmz->_fill($data);
        $zones[$new_pmz->pmz_ident]=$new_pmz;
      }
    }

    return $zones;
  }

  function delete (){
    $seats = shopDB::query_one_row("select count(*) from Seat
                                   where seat_zone_id ="._esc($this->id), false);
    if (empty($seats) || $seats[0]>0 ) {
      return addWarning('zone_delete_failed_seats_exists');
    }

    if(ShopDB::begin('delete zone: '.$this->id)){
      if($pmps=PlaceMapPart::loadAll($this->pmz_pm_id) and is_array($pmps)){
        foreach($pmps as $pmp){
          if ($pmp->delete_zone($this->pmz_ident) && !$pmp->save()){
            return self::_abort('zone_delete_failed_on_pmps');
          }
        }
      }
      if (!parent::delete()) {
         return self::_abort('zone_delete_failed');
      }
      return ShopDB::commit('Zone deleted');
    }
  }


  /**
   * PlaceMapZone::_find_ident()
   * Search the first not used ident value in the table within the given placemap
   * @param mixed $pmz_pm_id
   * @return Integer the new value
   */
  function _find_ident ($pmz_pm_id){
    $ident = array();
    $query="select pmz_ident
            from PlaceMapZone
            where pmz_pm_id="._esc($pmz_pm_id)."
            order by pmz_ident";
    if(!$res=ShopDB::query($query)){return;}
    while($i=shopDB::fetch_array($res)){
      $ident[] = $i['pmz_ident'];
    }

    $pmz_ident=1;
    while(in_array($pmz_ident, $ident)){$pmz_ident++;}
    return $pmz_ident;
  }

  function _fill(&$arr,$nocheck= true){
    if(empty($arr['pmz_id']) && empty($arr['pmz_ident'])){
        $arr['pmz_ident']=$this->_find_ident($arr['pmz_pm_id']);
      }
    return parent::_fill($arr, $nocheck);
  }

}

?>