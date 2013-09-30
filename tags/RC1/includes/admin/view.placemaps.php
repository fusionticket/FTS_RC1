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

require_once ( "admin/class.adminview.php" );
require_once ( "admin/view.placemapzones.php" );
require_once ( "admin/view.placemapparts.php" );
require_once ( "admin/view.placemapcategories.php" );

class PlaceMapView extends AdminView {
	function table( $ort_id ) {
		global $_SHOP;

		$mine = true;
    $ort_id = (int)$ort_id;

		$query = "select * from PlaceMap2
              where pm_ort_id="._esc($ort_id)."
              and pm_event_id IS NULL";
		if ( !$res = ShopDB::query($query) ) {
			return;
		}

		$alt = 0;
	    $this->grid_head('place_maps',array(
	     'pm_name_header'=>array(  ),
	     'actions_header'=>array('width'=>65 )),
	    array(
	       'addaction'=>"{$_SERVER['PHP_SELF']}?action=add_pm&pm_ort_id={$ort_id}",
	       'width'=>$this->width,
	       'height'=>'100px',
	       'addfilter'=>fase));//	     "{$_SERVER['PHP_SELF']}?action=add_pm&pm_ort_id={$ort_id}",$this->width,'100px',false);

		while ( $pm = shopDB::fetch_assoc($res) ) {
			echo "<tr class='admin_list_row_$alt'>";

//			echo "<td class='admin_list_item' width='20'>{$pm['pm_id']}</td>\n";
			echo "<td class='admin_list_item' >{$pm['pm_name']}</td>\n";

			echo "<td class='admin_list_item' width=65 align=right>";
      echo $this->show_button("{$_SERVER['PHP_SELF']}?action=edit_pm&pm_id={$pm['pm_id']}","edit",2);
      echo $this->show_button("{$_SERVER['PHP_SELF']}?action=copy_pm&pm_id={$pm['pm_id']}&pm_ort_id={$pm['pm_ort_id']}","copy",2, array('image'=>'copy.png'));
      echo $this->show_button("javascript:if(confirm(\"".con('delete_item')."\")){location.href=\"{$_SERVER['PHP_SELF']}?action=remove_pm&pm_id={$pm['pm_id']}&pm_ort_id={$pm['pm_ort_id']}\";}","remove",2,
                              array('tooltiptext'=>"Delete {$pm['pm_name']}?"));
      echo "</td></tr>";
			$alt = ( $alt + 1 ) % 2;
		}
	  $this->grid_footer();
	}

	function form( $data, $err, $title ) {
    if (is_numeric($data)) {
      $err = Placemap::Load($data);
      $data = (array)$err;
    }
//    print_r($data);
		echo "<form method='POST' action='{$_SERVER['PHP_SELF']}' enctype='multipart/form-data' autocomplete='off'>";
  	echo "<input type=hidden name=action value=save_pm>
          <input type=hidden name=pm_ort_id value='{$data['pm_ort_id']}'>";

		if ( !empty($data['pm_id']) ) {
      echo "<input type=hidden name=pm_id value='{$data['pm_id']}'>";
		}
		$this->form_head( $title );
		if ( isset($data['pm_event_id']) && $event = Event::load( $data['pm_event_id'], false )) {
			$live = $event->event_status != 'unpub';
    	echo "<input type=hidden name=pm_event_id value='{$data['pm_event_id']}'>";
		} else {
			$live = false;
      $event = null;
		}
		$this->print_field_o( 'ort_name', $data );
		$this->print_input( 'pm_name', $data, $err, 30, 100 );
		$this->print_file(  'pm_image', $data, $err, 'img' );
  //	echo "<br>";

		if ( !empty($data['pm_id'] )) {
//			if ( $event &&  $event->event_rep=='main') {
//				$this->form_foot(2,array('name'=>'pm_replace_subs','href'=>"{$_SERVER['PHP_SELF']}?action=replacesubs_pm&pm_id={$data['pm_id']}&pm_event_id={$data['pm_event_id']}" ));
//			} else {
  	   	$this->form_foot();
//			}
			$pmp_view = new PlaceMapCategoryView( $this->width );
			$pmp_view->table( $data['pm_id'], $live  and $event->event_status !== 'nosal');
			echo "<br>";
			$pmz_view = new PlaceMapZoneView( $this->width );
			$pmz_view->table( $data['pm_id'], $live );
			echo "<br>";
			$pmp_view = new PlaceMapPartView( $this->width );
			$pmp_view->table( $data['pm_id'], $live );

  		if ( $event ) {
  			echo "<br>";
        require_once ( "admin/view.discounts.php" );
  			$dist_view = new DiscountView( $this->width );
  			$dist_view->table( $event->event_id, $live );

  			echo "<br>".$this->show_button("{$_SERVER['PHP_SELF']}?event_id={$data['pm_event_id']}",'admin_list',3);
      } else {
  			echo "<br>".$this->show_button("{$_SERVER['PHP_SELF']}?action=edit&ort_id={$data['pm_ort_id']}",'admin_list',3);
      }
		} else {
  		$this->form_foot(2,"{$_SERVER['PHP_SELF']}?action=edit&ort_id={$data['pm_ort_id']}");
		}
	}

	function draw($showMe=true ) {
		global $_SHOP;
		if ( preg_match('/_disc$/', $_REQUEST['action']) ) {
			require_once ( "admin/view.discounts.php" );
			$pmp_view = new DiscountView( $this->width );
			if ( $pmp_view->draw() ) {
				$event = Event::load( $_REQUEST['discount_event_id'], false );
				if ($showMe) $this->form( $event->event_pm_id, null, con('edit_pm'));
        return $event->event_id;
			}
      $this->addJQuery($pmp_view->getJQuery());

		} elseif ( preg_match('/_pmp$/', $_REQUEST['action']) ) {
			$pmp_view = new PlaceMapPartView( $this->width );
			if ( $pmp_view->draw() ) {
				if ($showMe) $this->form( $_REQUEST['pm_id'], null, con('edit_pm') );
        return  -(int)$_REQUEST['pm_id'];
			}
      $this->addJQuery($pmp_view->getJQuery());

		} elseif ( preg_match('/_pmz$/', $_REQUEST['action']) ) {
			$pmz_view = new PlaceMapZoneView( $this->width );
			if ( $pmz_view->draw() ) {
				if ($showMe) $this->form( $_REQUEST['pm_id'], null, con('edit_pm') );
         return -(int)$_REQUEST['pm_id'];
			}

		} elseif ( preg_match('/_category$/', $_REQUEST['action']) ) {
			$view = new PlaceMapCategoryView( $this->width );
			if ( $view->draw() ) {
				if ($showMe) $this->form( $_REQUEST['pm_id'], null, con('edit_pm') );
        return -(int)$_REQUEST['pm_id'];
			}
      $this->addJQuery($view->getJQuery());

		} elseif ( $_GET['action'] == 'add_pm' ) {
      $pm = new PlaceMap(true);
      $pm->pm_ort_id = $_REQUEST['pm_ort_id'];
//    var_dump($_REQUEST['pm_ort_id']);var_dump($_REQUEST);
			$this->form( (array)$pm, $pm, con('add_pm') );

    } elseif ( $_GET['action'] == 'edit_pm' and (int)$_GET['pm_id'] > 0 ) {
      $pm = PlaceMap::load($_GET['pm_id']);
      if ($showMe) $this->form((array)$pm, $pm, con('edit_pm'));
      return $pm->pm_event_id;

		} elseif ( $_POST['action'] == 'save_pm' ) {
      if (!$pm = PlaceMap::load((int)is($_POST['pm_id']),-1)) {
         $pm = new PlaceMap(true);
      }
      if ( !$pm->fillPost() || !$pm->saveEx() ) {
        $this->form( $_POST, $pm , con((isset($_POST['ort_id']))?'edit_pm':'add_pm') );
        return false;
      } else {
        if ($showMe) $this->form( (array)$pm, $pm , con('add_pm') );
        return $pm->pm_event_id;
      }
/*  } elseif ( $_GET['action'] == 'replacesubs_pm' and (int)$_GET['pm_id'] > 0) {
			if ( $pm = PlaceMap::load($_GET['pm_id']) ) {
				$old_eventid = $pm->pm_event_id;
				$subs = event::loadAllSubs($pm->pm_event_id);
				$changed = false;
				if (ShopDB::Begin()){
					foreach($subs as $event) {
						if ($event->event_status =='unpub' ) {
							if (($del_pm = PlaceMap::load($event->event_pm_id)) && $del_pm->delete()) {
								$pm = PlaceMap::load($_GET['pm_id']);
                if ($new_pm_id=$pm->copy($event->event_id)){
									$query="update `Event` set event_pm_id={$new_pm_id}
		                     where event_id={$event->event_id}";
									ShopDB::query($query);
                	$changed = true;
								} else {
									model::_abort('replacesubs_pm_cant_copy_new_pm');
									return $old_eventid;;
								}
							}else {
								model::_abort('replacesubs_pm_cant_delete_old_pm');
								return $old_eventid;;
							}
						}
					}
				} else {
					addWarning('replacesubs_pm_cant_start_transaction');
					return $old_eventid;
				}
				if ($changed) {
  				addNotice('replacesubs_pm_all_subs_are_uptodate');
				} else {
					addWarning('replacesubs_pm_no_subevents_found');
				}

				shopdb::commit();
			} else {
				addWarning('replacesubs_pm_cant_find_pm');
				return $old_eventid;
			}
			return $old_eventid;
*/

    } elseif ( $_GET['action'] == 'copy_pm' and (int)$_GET['pm_id'] > 0 ) {
      if ( $pm = PlaceMap::load($_GET['pm_id']) ) {
        $pm->copy();
      }
      return true;
    } elseif ( $_GET['action'] == 'remove_pm' and $_GET['pm_id'] > 0 ) {
      if ( $pm = PlaceMap::load($_GET['pm_id']) ) {
        $pm->delete();
      }
      return true;

    } else
      return true;
	}
}

?>