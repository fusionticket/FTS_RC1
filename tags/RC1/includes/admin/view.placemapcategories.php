<?php
/**
* %%%copyright%%%
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

require_once("admin/class.adminview.php");

class PlaceMapCategoryView extends AdminView {
  function table ($pm_id, $live = false) {
	$img_pub = $this->fill_images();
    $alt = 0;
    $addif = (!$live)?"{$_SERVER['PHP_SELF']}?action=add_category&pm_id={$pm_id}":null;
    $this->grid_head('categories',array( 'status_header'=>array( 'width'=>10, 'sort'=>false ),
                                          'category_numbering_header'=>array( 'width'=>150 ),
                                          'category_name_header'=>array(  ),
                                          'category_size_header'=>array( 'width'=>75  ),
                                          'category_price_header'=>array( 'width'=>75 ),
                                          'category_useweb_header'=>array( 'width'=>25, 'sort'=>false  ),
                                          'category_usepos_header'=>array( 'width'=>25, 'sort'=>false ),
                                          'actions_header'=>array('width'=>65 )),
                                  array(
                                     'addaction'=>$addif,
                                     'width'=>$this->width,
                                     'height'=>'75px',
                                     'addfilter'=>false));//                      $addif,$this->width,'75px',false);
    if ($cats = PlaceMapCategory::LoadAll($pm_id)){
      foreach($cats as  $category) {
        echo "<tr class='admin_list_row_$alt'>";
        echo "<td class='admin_list_item' bgcolor='{$category->category_color}'>&nbsp;</td>\n";
        echo "<td class='admin_list_item'>" . con($category->category_numbering) . " </td>\n";
        echo "<td class='admin_list_item' width='50%'>{$category->category_name}</td>\n";
        echo "<td class='admin_list_item'>{$category->category_size} </td><td>".valuta($category->category_price) . "</td>\n";

        echo "<td class='admin_list_item' width=16>";
		if ($category->category_web == '1') {
			echo $this->show_button("{$_SERVER['PHP_SELF']}?action=webunpub_category&pm_id={$pm_id}&category_id={$category->category_id}",
				$img_pub['webpub']['title'],2,
				array('image'=>$img_pub['webpub']['src'],
				'alt'  =>con($img_pub['webpub']['alt'])));

		} else {
			echo $this->show_button("{$_SERVER['PHP_SELF']}?action=webpub_category&pm_id={$pm_id}&category_id={$category->category_id}",
				$img_pub['webunpub']['title'],2,
				array('image'=>$img_pub['webunpub']['src'],
				'alt'  =>con($img_pub['webunpub']['alt'])));
		}
		echo "</td><td class='admin_list_item' width=16>";

		if ($category->category_pos == '1') {
			echo $this->show_button("{$_SERVER['PHP_SELF']}?action=posunpub_category&pm_id={$pm_id}&category_id={$category->category_id}",
				$img_pub['pospub']['title'],2,
				array('image'=>$img_pub['pospub']['src'],
				'alt'  =>con($img_pub['pospub']['alt'])));

		} else {
			echo $this->show_button("{$_SERVER['PHP_SELF']}?action=pospub_category&pm_id={$pm_id}&category_id={$category->category_id}",
				$img_pub['posunpub']['title'],2,
				array('image'=>$img_pub['posunpub']['src'],
				'alt'  =>con($img_pub['posunpub']['alt'])));
		}
        echo "<td class='admin_list_item' width=40 align=right>";
        echo $this->show_button("{$_SERVER['PHP_SELF']}?action=edit_category&pm_id={$pm_id}&category_id={$category->category_id}","edit",2);
        echo $this->show_button("javascript:if(confirm(\"".con('delete_item')."\")){location.href=\"{$_SERVER['PHP_SELF']}?action=remove_category&pm_id={$pm_id}&category_id={$category->category_id}\";}","remove",2,
                                  array('tooltiptext'=>"Delete {$category->category_name}?",
                                        'disable'=>$live ));
        echo'</td></tr>';
        $alt = ($alt + 1) % 2;
      }
    }
   // echo '</table>';
    	  $this->grid_footer();

  }

  function form ( $data, $err)
  {
    if (!isset($data['event_status'])) {
      $query = "select event_status, ort_country
                from `PlaceMap2`
                left join `Event` on pm_event_id = event_id
                left join Ort on pm_ort_id = ort_id
                where pm_id = "._esc($_REQUEST['pm_id']);
      $row = ShopDB::query_one_row($query);
      $data['event_status'] = $row['event_status'];
      $data['ort_country'] = $row['ort_country'];
    }
    echo "<form action='{$_SERVER['PHP_SELF']}' method='post' autocomplete='off'>";
    echo "<input type=hidden name=action value=save_category>";
    if (!empty($data['category_id'])) {
       echo "<input type=hidden name=category_id value={$data['category_id']}>";
    } else {
      $data['category_pm_id'] =(isset($data['category_pm_id']))?$data['category_pm_id']:$_REQUEST['pm_id'];
      $pm = PlaceMap::load($_REQUEST['pm_id']);//print_r($pm);
      $data['category_event_id'] = $pm->pm_event_id;
    }
    echo "<input type=hidden name=category_pm_id value={$data['category_pm_id']}>";
    echo "<input type=hidden name=pm_id value={$data['category_pm_id']}>";
    echo "<input type=hidden name=category_event_id value={$data['category_event_id']}>";

    $this->form_head(con('categories'));

    $this->print_field_o('category_id', $data);
    $this->print_input('category_name', $data, $err, 30, 100);
    if (!$data['event_status'] or ($data['event_status'] != 'pub')) {
        $this->print_input('category_price', $data, $err, 6, 6);
    } else {
        $this->print_field('category_price', $data);
    }
    $this->print_select_tpl('category_template', $data, $err);
    $this->print_color('category_color', $data, $err);
//    $this->print_field('event_status', $data);
    if (!$data['event_status'] or ($data['event_status'] === 'unpub')) {
      $this->print_select('category_numbering', $data, $err, array('none', 'rows', 'seat', 'both'),'');
      $script = "
      $('#category_numbering-select').change(function(){
        if($(this).val() == 'none'){
          $('#category_size-tr').show();
        }else{
          $('#category_size-tr').hide();
        }
      });
      $('#category_numbering-select').change();";
      $this->addJQuery($script);
      $this->print_input('category_size', $data, $err, 6, 6);
    } elseif (($data['event_status'] === 'nosal') && empty($data['category_id'])) {
      echo "<input type='hidden' name='category_numbering' value='none'>";
      $this->print_input('category_nosale_size', $data, $err, 6, 6);
    } else {
      $this->print_field('category_numbering', $data);
      $this->print_field('category_size', $data);
      $taken = $data['category_size'] - $data['category_free'];
      $this->print_field('number_taken', $taken);
    }

    $this->print_area('category_data', $data, $err, 3, 40);

    if ($data['event_status'] == 'nosal' && $data['category_numbering'] == 'none'&& !empty($data['category_id'])) {
      $this->form_foot();
      echo "<br>";
      echo "<form action='{$_SERVER['PHP_SELF']}' method=post autocomplete='off'>";
      echo "<input type=hidden name=pm_id value={$data['category_pm_id']}>";
      echo "<input type=hidden name=category_id value={$data['category_id']}>";
      echo "<input type=hidden name=action value=resize_category>";
      $this->form_head(con('category_new_size_title'));
      $this->print_input('category_new_size', $data, $err, 6, 6);
    }
    $this->form_foot(2, "{$_SERVER['PHP_SELF']}?action=edit_pm&pm_id={$data['category_pm_id']}");
  }


  function draw () {
    if ($_GET['action'] == 'add_category' and $_GET['pm_id'] > 0) {
      $pmc = new PlaceMapCategory(true);
      $this->form( (array)$pmc, $pmc);
    } elseif ($_GET['action'] == 'edit_category' and $_GET['category_id'] > 0) {
      $pmc = PlaceMapCategory::load( (int)$_GET['category_id']);
      $data = (array)$pmc;
      $this->form( $data, $pmc);
    } elseif ($_POST['action'] == 'save_category' && $_POST['category_pm_id'] > 0) {
      if (!$pmc = PlaceMapCategory::load((int)is($_POST['category_id'],-1))) {
         $pmc = new PlaceMapCategory(true);
      }
      if (!$pmc->fillPost() || !$pmc->saveEx()) {
        $this->form( $_POST, $pmc);
      } else {
        $pmc = PlaceMapCategory::load( $pmc->id);
        if ( ( $pmc->event_status == 'nosal') &&
            (int)$_POST['category_new_size'] && !$pmc->change_size( (int)$_POST['category_nosale_size'])) {
          $data = (array)$category;
          addError('category_nosale_size', 'error');
          $data['category_nosale_size'] = $_POST['category_nosale_size'];
          $this->form( $data, $pmc);
          return false;
        }
//        $pmc = new PlaceMapCategory( true);
//        $this->form( (array)$pmc, null);
//        return false;
         return true;
      }


    } elseif ($_GET['action'] == 'remove_category' and $_GET['category_id'] > 0) {
      if($pmc = PlaceMapCategory::load($_GET['category_id']))
        $pmc->delete();
      return true;
    } elseif ($_POST['action'] == 'resize_category' and $_POST['category_id'] > 0) {
      $pmc = PlaceMapCategory::load( (int)$_POST['category_id']);

      if ( !$pmc->change_size( (int)$_POST['category_new_size'])) {
        $data = (array)$pmc;
        addError('category_new_size', 'error');
        $data['category_new_size'] = $_POST['category_new_size'];
        $this->form( $data, $pmc);
      } else {
        $data = (array)$pmc;
        $this->form( $data, $pmc);
      }

     } elseif ($_GET['action'] == 'webpub_category' and $_GET['category_id'] > 0) {

		if($pmc = PlaceMapCategory::load($_GET['category_id'])) {
			$this->publish_category(1);
         return true;
		}

    } elseif ($_GET['action'] == 'webunpub_category' and $_GET['category_id'] > 0) {
		if($pmc = PlaceMapCategory::load($_GET['category_id'])) {
			$this->publish_category(2);
			return true;
		}

    } elseif ($_GET['action'] == 'pospub_category' and $_GET['category_id'] > 0) {
		if($pmc = PlaceMapCategory::load($_GET['category_id'])) {
			$this->publish_category(3);
			return true;
		}
    } elseif ($_GET['action'] == 'posunpub_category' and $_GET['category_id'] > 0) {
		if($pmc = PlaceMapCategory::load($_GET['category_id'])) {
			$this->publish_category(4);
			return true;
		}
    }
  }

  // ################# petits fonctions speciales ##################
  function print_select_tpl ( $name, &$data, &$err)
  {
    $query = "SELECT template_name
              FROM Template
              WHERE template_type='pdf2'
              ORDER BY template_name";
    if (!$res = ShopDB::query($query)) {
        user_error(shopDB::error());
        return false;
    }

    $sel[$data[$name]] = " selected ";

    echo "<tr><td class='admin_name'  width='40%'>" . self::print_label($name,'-input',self::check_required($name, $err)) . "</td>
            <td class='admin_value'>
             <select name='$name'>
             <option value=''></option>\n";

    while ($v = shopDB::fetch_row($res)) {
        $value = htmlentities($v[0], ENT_QUOTES);
        echo "<option value='$value' " . is($sel[$v[0]],'') . ">{$v[0]}</option>\n";
    }

    echo "</select>" . printMsg( $name) . "</td></tr>\n";
  }

  function publish_category ($categoryChange){
	$setCategory ='';
	$setting ='';
	$categoryID ='';

	if ($categoryChange == 1) { //Publish to webshop

		$setCategory ='category_web';
		$setting ='1';

	} elseif ($categoryChange == 2) { //Unpublish to webshop

		$setCategory ='category_web';
		$setting ='0';

	} elseif ($categoryChange == 3) { //Publish to POS

		$setCategory ='category_pos';
		$setting ='1';

	} elseif ($categoryChange == 4) { //Unpublish to POS

		$setCategory ='category_pos';
		$setting ='0';
	}

	if (count($_GET['category_id']) > 0) { //If there are 1 or more events selected do the following:
		$categoryID=$_GET['category_id'];
		$query = "UPDATE Category
             SET $setCategory ='$setting'
             WHERE category_id = '$categoryID'";

			if (!$res = ShopDB::query($query)) {
			return;
			}
		//}
	}
  }

}

?>