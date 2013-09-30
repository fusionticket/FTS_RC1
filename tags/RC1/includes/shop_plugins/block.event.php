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

function smarty_block_event ($params, $content, $smarty, &$repeat) {
	global $_SHOP;

  if ($repeat) {
  	$smarty->assign("shop_event",null);
    $from='Event';
	  $tbl='Event.*';
    $where="event_status='pub'";

  	if(!empty($params['start_date'])){
  		$where .= " and event_date>="._esc($params['start_date']);
  	}

  	if(!empty($params['end_date'])){
  		$where .= " and event_date<="._esc($params['end_date']);
  	}

    if(!empty($params['order'])){
			$params['order']=_esc($params['order'], false);
      $order_by="order by {$params['order']}";
    }

  	if(!empty($params['ort'])){
  		$tbl.=', Ort.*';
  		$from.=' LEFT JOIN Ort ON ort_id=event_ort_id';
  	}

  	if(!empty($params['place_map'])){
  		$tbl.=', PlaceMap2.*';
  		$from.=' LEFT JOIN PlaceMap2 ON pm_event_id=event_id';
  	}

  	if(!empty($params['cats'])){
  		$tbl.=', Category.*';
  		$from.=' LEFT JOIN Category ON event_id=category_event_id';
  	}

  	if(!empty($params['event_group']) || !empty($params['join_event_group'])){
  		$tbl.=', Event_group.*';
  		$from.=' LEFT JOIN Event_group ON Event.event_group_id=Event_group.event_group_id';
  	}

  	if(!empty($params['amounts'])){
  		$tbl .= ', (select sum(seat_price) from Seat where seat_event_id = event_id and seat_status = "com") as event_paid';
  	}

    if(!empty($params['cat_web'])){
      $tbl .= ' ,((select count(*) from Category where Event.event_id=Category.category_event_id and Category.category_web=1) >0) as category_web';
    }

  	if(!empty($params['event_id'])){
			$where = "({$where} or event_rep = 'main') and event_id="._esc($params['event_id']);
    } else {

	    if(!empty($params['search'])){
	      $params['search']=_ESC($params['search'],false);
	      $where .= " AND
	      (event_name like '%{$params['search']}%'
	      or event_text like '%{$params['search']}%'
	      or event_short_text like '%{$params['search']}%'\n";
	      if($params['ort']){
	        $where .= "or ort_name like '%{$params['search']}%'
	                   or ort_city like '%{$params['search']}%'\n";
	      }
	      $where .= ") ";
	    }

    	if(is($params['useViewTime'],false)) {
    		$where .= " AND
    		   (event_view_begin = '0000-00-00 00:00:00' OR (event_view_begin <= now())) and
    		   (event_view_end   = '0000-00-00 00:00:00' OR (event_view_end >= now()))";
    	}

	    if(!empty($params['event_group'])&& is_numeric($params['event_group'])){
	      $where .= " and Event_group.event_group_id="._esc($params['event_group']);
	    }

	    if(!empty($params['type'])){
	    	$where .= " and event_type = "._esc($params['type']);
	    } elseif(!empty($params['event_type'])){
	      $types=explode(",",$params['event_type']);
	      $par=array();
	      foreach($types as $type){
					$par[] =shopDB::escape_string($type);
	      }
	      $where.=" and FIELD(event_type,".implode(',',$par).")>0";
	    }

	    if(!empty($params['sub'])){
	      $where.=" and event_rep LIKE '%sub%'";

	      if(!empty($params['event_main_id'])){
	        $where.=" and event_main_id="._esc($params['event_main_id']);
	      }
	    }

	    if(!empty($params['main'])){
	      $where  = "(event_rep ='main,sub' and ({$where}))
	             OR     (event_rep = 'main' and (select COALESCE(count(*),0)
	                                                    from Event main
	                                                    where main.event_main_id = Event.event_id
	                                                    and {$where})>0)";
	    }
    }

   	$limit=(!empty($params['limit']))?'limit '._esc($params['limit'],false):'';


    if(!empty($params['first'])){
			$params['first']=(int)$params['first'];
      $limit='limit '.$params['first'];
      if(!empty($params['length'])){
				$params['length']=(int)$params['length'];
        $limit.=','.$params['length'];
      }
    }else if(!empty($params['length'])){
			$params['length']=(int)$params['length'];
      $limit='limit 0,'.$params['length'];
    }

  	$cfr=($limit)?'SQL_CALC_FOUND_ROWS':'';

    $query="select {$cfr} {$tbl} from {$from} where {$where} {$order_by} {$limit}";

	  $res=ShopDB::query($query);

	  $part_count=ShopDB::num_rows($res);

		if($cfr){
		  $query='SELECT FOUND_ROWS();';
      if($row=ShopDB::query_one_row($query, false)){
			  $tot_count=$row[0];
			}
		}else{
		  $tot_count=$part_count;
		}

    $event=ShopDB::fetch_assoc($res);

  } else {
    $res_a=$smarty->popBlockData();

		$res=$res_a[0];
		$tot_count=$res_a[1];
		$part_count=$res_a[2];

    $event=ShopDB::fetch_assoc($res);
  }

  $repeat=!empty($event);

  if($event){

		$event['tot_count']=$tot_count;
    $event['part_count']=$part_count;
    $smarty->assign("shop_event",$event);

    $smarty->pushBlockData(array($res,$tot_count,$part_count));
  }

  return $content;
}

?>