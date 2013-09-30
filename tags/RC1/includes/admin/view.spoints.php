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
require_once("admin/class.adminview.php");

class SpointsView extends AdminView{

  function table (){
    global $_SHOP;
    $query="SELECT * FROM User where user_status=1";
    if (!empty($_SHOP->admin->admin_country)) {
      $query .=" and user_country = "._esc($_SHOP->admin->admin_country);
    }
	$query .=" order by user_country, user_city asc";
    if(!$res=ShopDB::query($query)){
      user_error(shopDB::error());
      return;
    }

    $alt=0;
    $this->grid_head('pos_user_title',array(
                                       'user_posname_header'=>array(),
                                       'user_city_header'=>array(  'width'=>150),
                                       'user_country_header'=>array( 'width'=>150,'type'=>'select'),
                                       'actions_header'=>array('width'=>50)),
                                      array(
                                         'addaction'=>"{$_SERVER['PHP_SELF']}?action=add",
                                         'width'=>$this->width,
                                         'height'=>'300px',
                                         'addfilter'=>true));//    "{$_SERVER['PHP_SELF']}?action=add",$this->width,'300px',true);


    while($row=shopDB::fetch_assoc($res)){
      echo "<tr class='admin_list_row_$alt'>";
      echo "<td class='admin_list_item'  >{$row['user_lastname']}</td>\n";
      echo "<td class='admin_list_item'  >{$row['user_city']}</td>\n";
      echo "<td class='admin_list_item' align='left'>{$this->GetCountry($row['user_country'])}</td>\n";
      echo "<td class='admin_list_item' align='right' nowrap><nowrap>";
      echo $this->show_button("{$_SERVER['PHP_SELF']}?action=edit&user_id={$row['user_id']}","edit",2);
      echo $this->show_button("javascript:if(confirm(\"".con('delete_item')."\")){location.href=\"{$_SERVER['PHP_SELF']}?action=remove&user_id={$row['user_id']}\";}","remove",2,array('tooltiptext'=>"Delete {$row['user_lastname']}?"));
      echo "</nowrap></td>\n";
      echo "</tr>\n";
      $alt=($alt+1)%2;
    }
    $this->grid_footer();
  }

  function tableAdmins ($user_id){
    global $_SHOP;
    $query="SELECT * FROM Admin where admin_user_id ={$user_id} and admin_status like 'pos%'";
    if(!$res=ShopDB::query($query)){
      user_error(shopDB::error());
      return;
    }

    $alt=0;
  	$isACL = plugin::call('isACL');
  	if (!$isACL) {
    $this->grid_head('admin_posuser_title',array(
                                               'admin_login_header'=>array(),
        																		   'uadmin_inuse_header'=>array( 'width'=>75,'type'=>'select'),
        																		   'actions_header'=>array('width'=>25)),
                                           array(
                                               'addaction'=>false,
                                               'width'=>$this->width,
                                               'height'=>'100px',
                                               'addfilter'=>false));//    null,$this->width,'100px',false);

  	} else {
	    $this->grid_head('admin_posuser_title',array(
      	                                       'admin_login_header'=>array(),
      	                                       'admin_status_header'=>array(  'width'=>150,'type'=>'select'),
      																			   'uadmin_inuse_header'=>array( 'width'=>75,'type'=>'select'),
												      							   'actions_header'=>array('width'=>25)),
                                            array(
                                               'addaction'=>false,
                                               'width'=>$this->width,
                                               'height'=>'100px',
                                               'addfilter'=>false));//	    null,$this->width,'100px',false);
  	}
    while($row=shopDB::fetch_assoc($res)){
      echo "<tr class='admin_list_row_$alt'>";
      echo "<td class='admin_list_item' >{$row['admin_login']}</td>\n";
    	if ($isACL) {
      echo "<td class='admin_list_item' >".con($row['admin_status'])."</td>\n";
    	}
      echo "<td class='admin_list_item' >".con($row['admin_inuse'])."</td>\n";
    	echo "<td class='admin_list_item' align='right' nowrap><nowrap>";
    	echo $this->show_button("{$_SERVER['PHP_SELF']}?action=admin_unlink&admin_id={$row['admin_id']}&user_id={$user_id}","unlink",2, array('image'=>'unlink.png'));
    	echo "</nowrap></td>\n";
      echo "</tr>\n";
      $alt=($alt+1)%2;
    }
    $this->grid_footer();
  }

  function form ($data, $err, $title, $add='add'){
    global $_SHOP;
    echo "<form method='POST' action='{$_SERVER['PHP_SELF']}'  autocomplete='off'>\n";
    echo "<input type='hidden' name='user_id' value='{$data['user_id']}'/>\n";
    echo "<input type='hidden' name='action' value='save'/>\n";
    $this->form_head($title);
		$this->print_field_o( 'user_id', $data );
    if (!$data["kasse_name"]) $data["kasse_name"] = $data["user_lastname"];
		$this->print_input( 'kasse_name', $data, true, 30, 50 );

		$this->print_input( 'user_address', $data, $err, 30, 75 );
		$this->print_input( 'user_address1', $data, $err, 30, 75 );
		$this->print_input( 'user_zip', $data, $err, 8, 20 );
		$this->print_input( 'user_city', $data, $err, 30, 50 );
		//$this->print_input( 'user_state', $data, $err, 30, 50 );
  	if(empty($data['user_country'])) {
  		$data['user_country'] = $_SHOP->admin->admin_country;
  	}

  	if (!empty($_SHOP->admin->admin_country)) {
  		$this->print_hidden('user_country', $data);
  		$this->print_field_o('user_country', $this->GetCountry($data['user_country']));
  	} else {
  		$this->print_countrylist('user_country', $data, $err);
  	}

		$this->print_input( 'user_phone', $data, $err, 30, 50 );
		$this->print_input( 'user_fax', $data, $err, 30, 50 );
		$this->print_input( 'user_email', $data, $err, 30, 50 );

		$this->print_select( 'user_prefs_print', $data, $err, array('pdt', 'pdf') );
    $this->print_checkbox( 'user_prefs_strict', $data, $err );
    $this->print_checkbox( 'user_prefs_store_now', $data, $err );
   // var_dump($_SESSION);
    $this->form_foot(2,$_SERVER['PHP_SELF']);
    if ($data['user_id']) {
      $this->tableAdmins ($data['user_id']);
    }
  }

  function draw () {
    global $_SHOP;
    if ($_GET['action'] == 'add') {
       $adm = new User(true);
       $row = (array)$adm;
       $this->form($row, $adm, con('pos_add_title'));
    } elseif ($_GET['action'] == 'edit' && $_GET['user_id']){
      if ($adm = User::load($_REQUEST['user_id'], 1)) {
         $row = (array)$adm;
         $this->form($row, $adm, con('pos_update_title'));
      } else $this->table();
    } elseif ($_GET['action'] == 'admin_unlink' && (int)$_GET['admin_id']){
      if ($adm = Admins::load($_GET['admin_id'])) {
      	$adm->admin_user_id = null;
      	if ($adm->save()) {
      	  addnotice('spoint_removed_adminuser');
      	};
      }
    	if ($adm = User::load($_REQUEST['user_id'], 1)) {
    		$row = (array)$adm;
    		$this->form($row, $adm, con('pos_update_title'));
    	} else $this->table();
    } elseif ($_POST['action'] == 'save') {
      if (!$adm = User::load($_POST['user_id'], 1)) {
         $adm = new User(true);
         $adm->user_status = 1;
      }
      if ($adm->fillPost() && $adm->saveEx()) {
        if ($adm->user_prefs_store_now) {
    //      $myDomain1 = ereg_replace('^[^\.]*\.([^\.]*)\.(.*)$', '\1.\2', $_SERVER['HTTP_HOST']);
        	$myDomain = preg_replace('#^[^\.]*\.([^\.]*)\.(.*)$#', '\1.\2', $_SERVER['HTTP_HOST']);
     //   	var_dump($myDomain1,$myDomain2);
          $setDomain = ($_SERVER['HTTP_HOST']) != "localhost" ? ".$myDomain" : false;
          $done = setcookie ('test'.$adm->id, $adm->user_prefs_strict.$_SHOP->secure_id.$adm->id.$adm->user_lastname , time()+600, '/', "$setDomain", 0 );
          $done = setcookie ('use'.$adm->id, hash('ripemd160',$adm->user_prefs_strict.$_SHOP->secure_id.$adm->id.$adm->user_lastname) , time()+3600*24*(60), '/', "$setDomain", 0 );
          addNotice('POS registration Cookie placed: ',($done?'Yes':'no'));
        }
        $this->table();
      } else {
        $this->form($_POST, $adm, con(((isset($_POST['user_id']))?'pos_update_title':'pos_add_title')));
      }

    } elseif($_GET['action']=='remove' and $_GET['user_id']){
      if($adm = User::load($_GET['user_id'], 1))
        $adm->delete();
      $this->table();
    } else {
      $this->table();
    }
  }

}
?>