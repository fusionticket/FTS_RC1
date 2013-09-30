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

class Adminlink Extends Model {
  protected $_idName    = 'adminlink_id';
  protected $_tableName = 'adminlink';
  protected $_columns   = array( 'adminlink_id','#adminlink_event_id', '#adminlink_admin_id', '#adminlink_pos_id',
                                 '#adminlink_admgroup_id');

  function create($event_id, $admin_id=null, $pos_id =null, $admgroup_id=null){
      $eg=new Adminlink();
      $eg->adminlink_event_id = $event_id;
      $eg->adminlink_admin_id = $admin_id;
      $eg->adminlink_pos_id = $pos_id;
      $eg->adminlink_admgroup_id = $admgroup_id;
      $eg->save();
      return $eg;
  }

  function load ($id=0){
    $query="select *
            from adminlink
            where adminlink_id="._esc($id);
    if($res=ShopDB::query_one_row($query)){
      $eg=new Adminlink;
      $eg->_fill($res);
      return $eg;
    }
  }

  function loadAll($event_id) {
    $query="SELECT *
            FROM adminlink
            Where adminlink_event_id ="._esc($event_id);
    if($res=ShopDB::query($query)){
      $links = array();
      while($row=shopDB::fetch_assoc($res)){
        $new = new Adminlink;
        $new->_fill($row);
        $links[]= $new;
      }
      return $links;
    }

  }
  function delete(){
    /* This query need to be checked !!!
     $query = "select 1 as inUse
                  from `User`
                  left join   `adminlink` on user_id = adminlink_pos_id
                  left join
	    	(select distinct seat_pos_id, seat_event_id
         from Seat
         where seat_status = 'free'
         and seat_event_id = "._esc($_REQUEST['event_id']).') as sss on user_id = seat_pos_id
         where seat_pos_id is not null and adminLink_id = "._esc((int)$_REQUEST['adminlink_id'])."
	    	and adminlink_event_id = seat_event_id";
	    	if ($row = ShopDB::query_one_row($query)){
	    		if($row != null && $row['inUse'] == 1){
     //this is in use, return
     return addWarning(con("delete_link_error"));
    }
  */
    return parent::delete();
  }

  function copy($event_main_id, $event_sub_id, $deleteFirst= false) {
  	if ($deleteFirst) {
  		$query = "delete from `adminlink` where adminlink_event_id ="._esc($event_sub_id);
  		ShopDB::query($query);
  	}
    $links = self::LoadAll($event_main_id);
    foreach ($links as $link) {
      $link->adminlink_event_id = $event_sub_id;
      unset($link->adminlink_id);
      $link->save();
    }
  }

	function updateLinks($event_id, $admins, $users, $updateSubs = false){
		$existing = array('admin'=>array(), 'pos'=>array());
		$query = "select adminlink_id, adminlink_pos_id, adminlink_admin_id from  `adminlink` where adminlink_event_id ="._esc($event_id);
		$res = ShopDB::query($query);
		$deleted = 0;
		while ($row = shopDB::fetch_assoc($res)) {
			if ($row['adminlink_pos_id']) {
				if (in_array($row['adminlink_pos_id'], $users  )){
					$existing['pos'][] = $row['adminlink_pos_id'];
				} else {
					if ($pmp = self::load($row['adminlink_id'])) {
						$pmp->delete();
						$deleted += 1;
					} else {
						echo "error ";
					}
				}
			}
			if ($row['adminlink_admin_id']) {
				if (in_array($row['adminlink_admin_id'], $admins  )){
					$existing['admin'][] = $row['adminlink_admin_id'];
				} else {
					if ($pmp = self::load($row['adminlink_id'])) {
						$pmp->delete();
						$deleted += 1;

					}
				}
			}
		}
		if ($deleted){
			addwarning('Number of links removed: ',$deleted);
		}
		$added = 0;
		foreach($users as $user_id) {
			if ($user_id >0 and !in_array($user_id, $existing['pos'])){
				self::create($event_id, null, $user_id);
				$added +=1;
			}
		}
		foreach($admins as $user_id) {
			if ($user_id >0 and !in_array($user_id, $existing['admin'])){
				self::create($event_id, $user_id);
				$added +=1;
			}
		}
		if ($added) {
			addNotice('Number of links added: ',$added);
		}
		if ($updateSubs) {
      $subs = event::loadAllSubs($event_id);
			foreach($subs as $event) {
				self::copy($event_id, $event->id, true );
			}
		}
	}
}
?>