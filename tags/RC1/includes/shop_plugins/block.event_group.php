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

function smarty_block_event_group ($params, $content, $smarty, &$repeat) {
	global $_SHOP;


  if ($repeat) {
    $from='from Event_group';
    $where="where 1";

    if($params['order']){
      $order_by="order by {$params['order']}";
    }

    if($params['group_id']){
     $where .= " and event_group_id="._esc($params['group_id']);
    }

     if($params['limit']){
      $limit='limit '.$params['limit'];
    }

    if($params['group_status']){
     $where .= " and event_group_status="._esc($params['group_status']);
    }
    
    if($params['show_active']) {
    	$where .= " and now() <= event_group_end_date";
    }
    
    if($params['first']){
      $limit='limit '.$params['first'];
      if($params['length']){
        $limit.=','.$params['length'];
      }
    }else if($params['length']){
      $limit='limit 0,'.$params['length'];
    }

    $query="select * $from $where $order_by $limit";
    $res=ShopDB::query($query);

    $event=shopDB::fetch_assoc($res);

  }else{
    $res=$smarty->popBlockData();
    $event=shopDB::fetch_assoc($res);
  }

  $repeat=!empty($event);

  if($event){
    $smarty->assign("shop_event_group",$event);

    $smarty->pushBlockData($res);
  }

  return $content;
}


?>