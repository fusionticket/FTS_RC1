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

function smarty_block_handling ($params, $content, $smarty, &$repeat) {

	global $_SHOP;

  if ($repeat) {

    $use_alt=($params['event_date'])?check_event($params['event_date']):false;
   	if(!$params['handling_id']){
	   	if(!$use_alt){
	   		$where .= " AND h.handling_alt_only='No'";
  		}else{
  			$where .= " AND ((select count(*) from Handling hh where hh.handling_alt = h.handling_id) > 0)";
  		}
  	} else {
     $where .= " and h.handling_id="._esc((int)$params['handling_id']);
    }


    if($params['order']){
      $order_by="order by {$params['order']}";
    }


    if($params['sp']){
     $where .= " and handling_sale_mode LIKE '%sp%'";
    }

    if($params['www']){
     $where .= " and handling_sale_mode LIKE '%www%'";
    }

    // We use the reserve button in the shop.
   	if(!$params['handling_id'] && !$use_alt && $_SHOP->shopconfig_restime > 0 && !$params['www']){
      $where .= " OR h.handling_id = 1";
    }

    $limit= ($params['limit'])?'limit '.$params['limit']:'';

    $query="select * FROM Handling h
            WHERE 1=1
            $where
            $order_by $limit";

    $res=ShopDB::query($query);

    $pay=shopDB::fetch_assoc($res);

  }else{
    $res=$smarty->popBlockData();
    $pay=shopDB::fetch_assoc($res);
  }


  $repeat=!empty($pay);

// Loads the payment file from class's which defines the extra parmiters when someone pays or goes to pay.
  if($pay){

		// if handling_extra exsists unserialize it...
	  	if($pay['handling_extra']){
			  $pay['extra'] = unserialize($pay['handling_extra']);

		  }
      $pay['fee'] = calculate_fee ($pay, is($params['total'],0), is($params['count'],0));
	    $smarty->assign("shop_handling",$pay);

	    $smarty->pushBlockData($res);
	  }



  return $content;
}

  function calculate_fee ($pay, $total, $seats=1){
    if ($pay['handling_fee_perseat'] =='Yes') {
      $x = $seats * $pay['handling_fee_fix'];
    } else
      $x = $pay['handling_fee_fix'];
    $y = ($total/100.00)*$pay['handling_fee_percent'];
    switch ($pay['handling_fee_type']) {
      case 'min':
          return round(($x < $y)?$x : $y,2);
          break;
      case 'max':
          return round(($x > $y)?$x : $y,2);
          break;
      default:
        return round($x+$y,2);
    }
  }
?>