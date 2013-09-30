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

if (!defined('ft_check')) {die('System intrusion ');}
require_once ( "classes/class.time.php" );

class Update_Smarty {

	function Update_Smarty( $smarty ) {
		global $_SHOP;
		$smarty->register_object( "update", $this, array('view', 'is_demo','can_reserve') );
		$smarty->assign_by_ref( "update", $this );
	}

  function is_demo() {
  	global $_SHOP; //print_r( $_SHOP  );
    return  $_SHOP->shopconfig_run_as_demo;
  }

  function can_reserve() {
  	global $_SHOP; //print_r( $_SHOP  );
    return  $_SHOP->shopconfig_restime;
  }

	//Used for returning results so a template can know if a button/item should be enabled
	function view( $params, $smarty ) {
    global $_SHOP;

		//check if reserving is enabled
		$enabled['can_reserve'] = false;
		$event_date = $params['event_date'];
		if ( !$event_date ) {
			addwarning( "no_event_data" );
		  $smarty->assign( "update_view", $enabled );
		  return false;
		}
		if ( $_SHOP->shopconfig_restime > 0 ) {
			$enabled['can_reserve'] = true;

			//check to see if can reserve, adds two days before the reservation would expire, stops
			// people reserving tickets that would expire after the event.
			$time = Time::StringToTime( $event_date );
			$remain = Time::countdown( $time );

			// edit number to change the offset for reserving, reserved tickets will allways expire 2 days before the event.
			// I would recommend keeping this above 1440, a day before the event.
			if ( $remain["justmins"] >= ($_SHOP->shopconfig_restime ) ) {
				$enabled['can_reserve'] = true;
				$use_alt = check_event( $event_date );
				if ( $use_alt == true ) {
					$enabled['can_reserve'] = false;
				}
			}
			if ( $_SHOP->shopconfig_maxres > 1 ) {
				if ( isset($_SESSION['_SHOP_USER']) and $user = User::loadArr($_SESSION['_SHOP_USER'])) {
					require_once ( 'classes/smarty.mycart.php' );
					$cart = MyCart_Smarty::overview_f();
          $totalResSeats = User::currentTickets($user['user_id'],'resp');

					$res_total = $totalResSeats + $cart['valid'];

					if ( $res_total > $_SHOP->shopconfig_maxres ) {
						$enabled['can_reserve'] = false;
						$enabled['maxres'] = $_SHOP->shopconfig_maxres;
						$enabled['currentres'] = $res_total;
					} else {
						$enabled['can_reserve'] = true;
					}
				}
			}
		}
		$smarty->assign( "update_view", $enabled );
	}

	/**
	 * Deletes Unpaid Orders
	 *
	 * 'Delete' meaning that the order is canceled and the tickets
	 * are retuned back into curculation.
	 *
	 * Will only delete if the global 'Delete Unpaid Order' = Yes
	 * and if each handling 'Delete Unpaid Orders' = Yes
	 *
	 * Will never delete orders from the POS (Box Office)
	 *
	 * Will delete resevered orders out of time
	 *
	 */

	function secure_url_param( $num = false, $nonum = false ) {
  global $_SHOP;

		if ( $num ) {
			$correct = is_numeric( $num );
			if ( $correct ) {
				return $num;
			} elseif ( !$correct ) {
				echo "No Such ID";
				//$num = cleanNUM($num);
				$num = "1";
				return $num;
			}
		}
		if ( $nonum ) {
			$correct = preg_match( '/^[a-z0-9_]*$/i', $nonum );
			//can also use ctype if you wish instead of preg_match
			//$correct = ctype_alnum($nonum);
			if ( $correct ) {
				return $nonum;
			} elseif ( !$correct ) {
	  	  echo "No Such Variable";
				$nonum = "This";
				return $nonum;
			}
		}
	}

}

?>