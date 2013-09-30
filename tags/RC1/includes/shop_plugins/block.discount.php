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

function smarty_block_discount ($params, $content, $smarty, &$repeat)
{
    if ($repeat) {

	  	$smarty->assign("shop_discounts", Null);
    	$smarty->assign("shop_discounts_count", Null);
    	$smarty->assign("shop_discounts_times", Null);
    	$smarty->assign("shop_discount", Null);

        $from = 'from Discount';
        $place = is($params['place'],'www');
        if ($params['discount_id']) {
          $where = " where  discount_id=" . _esc($params['discount_id']);
        } else {
          $where = " where (FIND_IN_SET('yes', discount_active)>0  or  FIND_IN_SET('{$place}', discount_active)>0) ";
        }

        if ($params['order']) {
            $order_by = "order by {$params['order']}";
        }

//        if($params['category_id']){
//            $where .= " AND (discount_category_id="._esc($params['category_id'])." OR discount_category_id is null)";
//        }

        if ($params['event_id']) {
            $where .= " and discount_event_id=" . _esc($params['event_id']);
        }


        if ($params['discount_name']) {
            $d_names = explode(",", $params['discount_name']);
            $first = 0;
        	  $wherex = array ();
            foreach($d_names as $name) {
               $wherex[] = " discount_name=" . _esc($name);
            }
            $where .= " and (". implode(' or ',$wherex ). " ) ";
        }

        $query = "select * $from $where $order_by $limit";

        $res = ShopDB::query($query);

        $discount = shopDB::fetch_assoc($res);
    } else {
        $res = $smarty->popBlockData();
        $discount = shopDB::fetch_assoc($res);
    }
    if ($params['all']) {
        if (!empty($discount)) {
            $c = 1;
            calcprice($discount, $params);
            $discounts[] = $discount;

            while ($discount = shopDB::fetch_assoc($res)) {
                calcprice($discount, $params);
                $discounts[] = $discount;
                $c++;
            }

            $smarty->assign("shop_discounts", $discounts);
            $smarty->assign("shop_discounts_count", $c);
       			$smarty->assign("shop_discounts_times", $_REQUEST['qty']);

        }

        $repeat = false;
        return $content;
    } else {
        $repeat = !empty($discount);

        if ($discount) {
            calcprice($discount, $params);
            $smarty->assign("shop_discount", $discount);
            $smarty->pushBlockData($res);
        }
    }
    return $content;
}

function calcprice(&$discount, $params){
  If ($params['cat_price']) {
    if($discount['discount_type']=='fixe'){
      $discount['discount_price'] = $params['cat_price']-$discount['discount_value'];
    }else if($discount['discount_type']=='percent'){
      $discount['discount_price'] = $params['cat_price']*(1.0-$discount['discount_value']/100.0);
    }else{
      $discount['discount_price'] =  FALSE;
      return;
    }
    $discount['discount_pricetxt'] = valuta($discount['discount_price']);
  }
}
?>