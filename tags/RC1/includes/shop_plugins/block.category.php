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

function smarty_block_category ($params, $content, $smarty, &$repeat) {

  if ($repeat) {
  	$smarty->assign("shop_category",null);

    $from = 'from Category';

    if($params['placemap']){
      $from .= " LEFT JOIN PlaceMap2 ON category_pm_id=pm_id \n";
    }

    if($params['event']){
      $from .= ' left join Event on event_id=category_event_id'."\n".
               ' left join Ort   on event_ort_id=ort_id'."\n";
    }

	$where = 'where 1=1 AND category_web=1'."\n";

    if($params['category_id']){
      $where .= " and category_id="._esc($params['category_id'])."\n";
    }

    if($params['event_id']){
      $where .= " and category_event_id="._esc($params['event_id'])."\n";
    }

    if($params['order']){
      $order_by="order by "._esc($params['order'],false);
    }

    $query="select * $from $where $order_by";
    $res=ShopDB::query($query);

    $cat=shopDB::fetch_assoc($res);

  }else{
    $res=$smarty->popBlockData();
    $cat=shopDB::fetch_assoc($res);
  }

  $repeat=!empty($cat);

  if($cat){
    $smarty->assign("shop_category",$cat);
    $smarty->pushBlockData($res);
  }

  return $content;
}


?>