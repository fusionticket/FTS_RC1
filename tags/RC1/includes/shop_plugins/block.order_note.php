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

function smarty_block_order_note ($params, $content, $smarty, &$repeat) {

  if ($repeat) {
    $select = "SELECT * ";
    $from = 'FROM `order_note`';
    $where = "WHERE 1=1 ";

    if($params['order_id']){
      $where .= "AND onote_order_id="._esc($params['order_id']);
    }else{
      $where .= "AND onote_order_id=0";
    }

    if($params['order']){
      $order_by="order by "._esc($params['order'],false);
    }else{
      $order_by="ORDER BY onote_timestamp DESC ";
    }

    $query="$select $from $where $order_by";
    $res=ShopDB::query($query);

    $onote=ShopDB::fetch_assoc($res);

    $smarty->_noteTypes = array(
      OrderNote::TYPE_NOTE=>"on_type_note",
      OrderNote::TYPE_ADMIN=>"on_type_admin",
      OrderNote::TYPE_PAYMENT=>"on_type_payment",
      OrderNote::TYPE_SHIP=>"on_type_ship",
      OrderNote::TYPE_TODO=>"on_type_todo"
    );

    if($params['order_var']){
      if(is_array($params['order_var'])){
        $order = $params['order_var'];
      }
      if($order['order_payment_status'] <> 'none'){
        unset($smarty->_noteTypes[OrderNote::TYPE_PAYMENT]);
      }
      if($order['order_shipment_status'] <> 'none'){
        unset($smarty->_noteTypes[OrderNote::TYPE_SHIP]);
      }
    }


  }else{
    $res=$smarty->popBlockData();
    $onote=ShopDB::fetch_assoc($res);
  }

  $smarty->_noteCounts[$onote["onote_type"]] += 1;

  $noteTypes = $smarty->_noteTypes;
  if($onote["onote_type"] == OrderNote::TYPE_PAYMENT){
    unset($noteTypes[OrderNote::TYPE_PAYMENT]);
  }elseif($onote["onote_type"] == OrderNote::TYPE_SHIP){
    unset($noteTypes[OrderNote::TYPE_SHIP]);
  }
  $smarty->_noteTypes = $noteTypes;

  $repeat=!empty($onote);

  if($onote){
    $onote["onote_privatetxt"] = ($onote["onote_private"])?con('yes'):con('no');
    $smarty->assign("order_onote",$onote);
    $smarty->pushBlockData($res);
  }
  $smarty->assign("order_onote_types",$noteTypes);

  return $content;

}
?>