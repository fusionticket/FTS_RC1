<?php
/**
 *               %%%copyright%%%
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
require_once('classes/class.shopdb.php');

/**
* Ort
*
* @package
* @author niels
* @copyright Copyright (c) 2010
* @version $Id$
* @access public
*/
class Ort Extends Model {
  protected $_idName = 'ort_id';
  protected $_tableName = 'Ort';
  protected $_columns = array('#ort_id',
    '*ort_name', '*ort_address', 'ort_address1', '*ort_zip',
    '*ort_city', '*ort_country', 'ort_state', 'ort_phone',
    '#ort_fax', '^ort_image', 'ort_url', 'ort_pm');

  function load ($ort_id)
  {
    $query = "select * from Ort where ort_id=" . _esc($ort_id);
    if ($res = ShopDB::query_one_row($query)) {
      $ort = new Ort;
      $ort->_fill($res);

      return $ort;
    }
  }

  function copy () {
    If (ShopDB::begin('Copy Ort')) {
      $old_id = $this->ort_id;
      unset($this->ort_id);

      $new_id = $this->save();

      if ($pms = PlaceMap::loadAll($old_id)) {
        foreach($pms as $pm) {
          $pm->pm_ort_id = $new_id;
          if (!$pm->copy()) {
            return self::_abort('Cant copy Placemap');
          }
        }
      }
      return ShopDB::commit('Copied ort');
    }
  }

  function delete () {
  GLOBAL $_SHOP;
    $query = "SELECT count(*)
              FROM Event
              Where event_ort_id=" . _esc($this->ort_id);
    // var_dump($res = ShopDB::query_one_row($query, false));
    if (!($res = ShopDB::query_one_row($query, false)) || (int)$res[0]) {
      return addWarning('venue_in_use');
    }

    If (ShopDB::begin('Delete Ort')) {
      if ($pms = PlaceMap::loadAll($this->ort_id)) {
        foreach($pms as $pm) {
          if (!$pm->delete()) {
            return false;
          }
        }
      }

      if (!parent::delete()) {
        return self::_abort('cant delete venue');
      }
  		if (ShopDB::commit('venue deleted')) { // Lxsparks - moved to outside of db transaction
	      if (!is_null($this->ort_image) && file_exists($_SHOP->files_dir.$this->ort_image)) { // Lxsparks delete Venue image
		      if (!unlink($_SHOP->files_dir.$this->ort_image)) {
    				addWarning('cant_delete_image');
		      }
	      }
  		  return true;
      }
		  return false;
    }
  }
}

?>