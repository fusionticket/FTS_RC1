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
require_once("admin/class.adminview.php");

class HandlingsView extends AdminView{
  var $showmailtypes = array (
  	'email' =>     array('ord','ord_invoice',//'ord_tickets',
		  		               'paid','paid_invoice','paid_tickets',
		  		               //'send','send_invoice','send_tickets'
		  		               ),
  	'post' =>      array('ord','ord_invoice',//'ord_tickets',
		  		               'paid','paid_invoice',//'paid_tickets',
		  		               'send','send_invoice',//'send_tickets'
		  		               ),
  	'entrance' =>  array('ord','ord_invoice',//'ord_tickets',
		  		               'paid','paid_invoice',//'paid_tickets',
		  		               //'send','send_invoice','send_tickets'
		  		               ),
  	'sp' =>        array('ord','ord_invoice',//'ord_tickets',
		  		               'paid','paid_invoice',//'paid_tickets',
		  		               //'send','send_invoice','send_tickets'
  		               )
  	);
  function table (){
		global $_SHOP;
		$alt=1;
    $this->grid_head('handling_title',array('payment'=>array( 'colspan'=>2, 'type'=>'select' ),
                                            'shipment'=>array( 'width'=>220, 'type'=>'select'),
                                            'fees'=>array( 'width'=>120, ),
                                            'handling_www'=>array( 'width'=>40,'sort'=>false, 'type'=>'null'),
                                            'handling_sp'=>array( 'width'=>40 ,'sort'=>false, 'type'=>'null'),
                                            'actions_header'=>array('width'=>45)),
                                      array(
                                               'addaction'=>"{$_SERVER['PHP_SELF']}?action=add",
                                               'width'=>$this->width,
                                               'height'=>'300px'));//"{$_SERVER['PHP_SELF']}?action=add",$this->width,'300px',true);

		if($hands=Handling::loadAll()){
			foreach($hands as $hand){
				$handling_mode_pos=(strpos($hand->handling_sale_mode,'sp')!==false);
				$handling_mode_web=(strpos($hand->handling_sale_mode,'www')!==false);

        echo "<tr class='admin_list_row_$alt'>";
				if($hand->handling_id==1){
				 	echo "<td class='admin_list_item'>".con('reserved')."</td>";
				 	echo "<td class='admin_list_item'>".con('reserved')."</td>\n";
				}else{
					echo "<td class='admin_list_item' title='{$hand->handling_text_payment}'>".$hand->handling_payment."</td>";
					echo "<td class='admin_list_item' title='{$hand->handling_text_shipment}'>".$hand->handling_shipment."</td>\n";
				}
				echo "<td align='right'>";
    				$perc=$hand->handling_fee_percent;
    				$fixe=$hand->handling_fee_fix;
    				if($perc > 0 ){
    					echo $perc." % ";
    				}
    				if ($perc >0 and $fixe >0 ){
    					echo "+";
    				}
    				if($fixe > 0){
    					echo valuta($fixe);
    				}
  				echo "</td>\n";
				echo "<td  align='center'>";
				  if ($handling_mode_web ) {
				    echo $this->show_button("javascript:if(confirm(\"".con('handling_www_deactivate')."\")){ location.href=\"{$_SERVER['PHP_SELF']}?action=active_hand&handling_id={$hand->handling_id}&mode=www&do=remove\"; }",('handling_www_activated'),2,
				         array('image'=>'shop_web_pub.png'));
				  } else {
				    echo $this->show_button("javascript:if(confirm(\"".con('handling_www_activate')."\")){ location.href=\"{$_SERVER['PHP_SELF']}?action=active_hand&handling_id={$hand->handling_id}&mode=www&do=add\"; }",('handling_www_deactivated'),2,
				         array('image'=>'shop_web_unpub.png'));
				  }
				  echo "</td>";
        echo "<td align='center'>";
				  if ($handling_mode_pos ) {
				    echo $this->show_button("javascript:if(confirm(\"".con('handling_pos_deactivate')."\")){ location.href=\"{$_SERVER['PHP_SELF']}?action=active_hand&handling_id={$hand->handling_id}&mode=sp&do=remove\"; }",('handling_pos_activated'),2,
				         array('image'=>'shop_pos_pub.png'));
				  } else {
				    echo $this->show_button("javascript:if(confirm(\"".con('handling_pos_activate')."\")){ location.href=\"{$_SERVER['PHP_SELF']}?action=active_hand&handling_id={$hand->handling_id}&mode=sp&do=add\"; }",('handling_pos_deactivated'),2,
				         array('image'=>'shop_pos_unpub.png'));
				  }
				  echo "</td>";

          echo "<td class='admin_list_item' width='45' align='right' nowrap><nowrap>";
          echo $this->show_button("{$_SERVER['PHP_SELF']}?action=edit&handling_id={$hand->handling_id}","edit",2);
        echo $this->show_button("javascript:if(confirm(\"".con('delete_item')."\")){location.href=\"{$_SERVER['PHP_SELF']}?action=remove&handling_id={$hand->handling_id}\";}","remove",2,array('tooltiptext'=>"Delete {$hand->handling_id}?",'disable'=>$hand->handling_id==1));
          echo "</nowrap></td>\n";

				echo "</tr>";
				$alt=($alt+1)%2;
			 }
		 }
    $this->grid_footer();

  }

  function form ($data, $err, $title){
		global $_SHOP;


		echo "<form method='POST' action='{$_SERVER['PHP_SELF']}'>\n";
		echo "<input type='hidden' name='action' value='save'/>\n";
		if($data['handling_id']){
      $h = Handling::load($data['handling_id']);
			echo "<input type='hidden' name='handling_payment' value='{$data['handling_payment']}'/>\n";
			echo "<input type='hidden' name='handling_shipment' value='{$data['handling_shipment']}'/>\n";
			echo "<input type='hidden' name='handling_id' value='{$data['handling_id']}'/>\n";
		}else{
      $h = new Handling();
		}

  	$this->form_head($title);

		if(!$data['handling_id']){
      $this->print_select_assoc('handling_payment', $data, true, Handling::getPayment());
      $this->print_select_assoc('handling_shipment', $data, true, Handling::getShipment());
      $this->form_foot(2,$_SERVER['PHP_SELF'],'continue');
		}else{
      $this->print_field('handling_payment', con($data['handling_payment']));
      $this->print_field('handling_shipment', con($data['handling_shipment']));

  		if( $data['sale_mode']['sp']){$chk_sp='checked';}
  		if($data['sale_mode']['www']){$chk_www='checked';}
  		echo "<tr><td class='admin_name'>".self::print_label('handling_sale_mode','',self::check_required($name, $err)) ."</td>
  			<td class='admin_value'>
          <input type='checkbox' name='sale_mode[www]' value='www' $chk_www> ".con('www')."&nbsp;
          <input type='checkbox' name='sale_mode[sp]' value='sp' $chk_sp> ".con('sp')."&nbsp;
  			</td></tr>";
  		$this->print_input('handling_expires_min',$data,$err,10);

      echo "<tr ><td colspan='2' class='admin_name'>" . con('handling_alt_settings') ."</td></tr>";

  		//This is for the alt payments if nothing is slected alt wont be used when close to an event.
      $data['handling_alt'] = is($data['handling_alt'],$data['handling_id']);
      $this->print_select_assoc('handling_alt',$data,$err,
             Handling::getHandlings(con('handling_no_alt'),$data['handling_id']));

  		//This to ask if the handling is alturnative only this could be an auto proccess but then you would only be
  		//able to use the handling when close to the event.
      $this->print_select_assoc('handling_alt_only',$data,$err,array('No'=>'no','Yes'=>'yes'));

      echo "<tr ><td colspan='2' class='admin_name'>" . con('handling_fee_settings') ."</td></tr>";
      $this->print_select_assoc('handling_fee_type',$data,$err,array('sum'=>'handling_fee_sum','min'=>'handling_fee_min','max'=>'handling_fee_max'));
		  $this->print_select_assoc('handling_fee_perseat',$data,$err,array('No'=>'no','Yes'=>'yes'));
		  $this->print_input('handling_fee_fix',$data,$err,5,10);
  		$this->print_input('handling_fee_percent',$data,$err,5,10);

      //print_r($data['handling_email_template']);

  		$temps=explode(",",$data['handling_email_template']);
  		foreach($temps as $temp){

  			$t=explode("=",$temp);
  			$data["handling_email_template_{$t[0]}"]=$t[1];
  		}

      echo "<tr ><td colspan='2' class='admin_name'>" . con('hanging_pdf_settings') ."</td></tr>";
  		$this->print_select_tpl('handling_pdf_template',"'pdf2'",$data,$err);
  		$this->print_select_tpl('handling_pdf_ticket_template',"'pdf2'",$data,$err);
  /* the order sould be only set to send when the tickets are really send to the patron.
  This is now handled by the order_print method as that is the plase the system know for sure the tickets are created.
     The manual option is because of this not needed anymore.
        $this->print_select_assoc('handling_only_manual_send',$data,$err,array('No'=>'no','Yes'=>'yes'));
*/
      echo "<tr ><td colspan='2' class='admin_name'>" . con('hanging_email_settings') ."</td></tr>";
  		 	if (in_array('ord', $this->showmailtypes[$data['handling_shipment']])) {
  		 		$this->print_select_tpl('handling_email_template_ord',"'email','swift'",$data, $err,
  		 			in_array('ord_invoice', $this->showmailtypes[$data['handling_shipment']]),
  		 			in_array('ord_tickets', $this->showmailtypes[$data['handling_shipment']])
  		 			);
  		 	}
  		 	if (in_array('paid', $this->showmailtypes[$data['handling_shipment']])){
  		 		$this->print_select_tpl('handling_email_template_paid',"'email','swift'",$data,$err,
  		 			in_array('paid_invoice', $this->showmailtypes[$data['handling_shipment']]),
  		 			in_array('paid_tickets', $this->showmailtypes[$data['handling_shipment']])
  		 			);
  		 	}
			  if (in_array('send', $this->showmailtypes[$data['handling_shipment']])){
          $this->print_select_tpl('handling_email_template_send',"'email','swift'",$data, $err,
          	in_array('send_invoice', $this->showmailtypes[$data['handling_shipment']]),
          	in_array('send_tickets', $this->showmailtypes[$data['handling_shipment']])
          	);
			  }

  //		$this->print_paper_format('pdf_paper',$data,$err);

			$this->print_area('handling_text_payment',$data,$err,3,80,'');
			$this->print_area('handling_text_shipment',$data,$err,3,80,'');
			$this->print_large_area('handling_html_template',$data,$err,10,80,'');
		  $this->extra_form($h, $data, $err);
      $this->form_foot(2,$_SERVER['PHP_SELF']);

		}

  }

  function extra_form($hand, &$data, &$err){
    Global $_SHOP;

    $extras = $hand->admin_form($this, $data);
    if ( $extras) {
      require_once(CLASSES.'class.smarty.php');
      require_once(CLASSES.'smarty.gui.php');

      $smarty = new MySmarty;
  //    $smarty->plugins_dir = array("plugins", INC . "shop_plugins".DS);
      $smarty->setCacheDir($_SHOP->tmp_dir);
      $smarty->setCompileDir($_SHOP->tmp_dir); // . '/web/templates_c/';
      $smarty->addPluginsDir(INC . "shop_plugins".DS);
      $smarty->compile_id   = 'AdminHandling_'.$_SHOP->lang;

      $gui   = new Gui_smarty($smarty);
      $gui->guidata   = $data;
      $gui->gui_name  = 'admin_name';
  	  $gui->gui_value = 'admin_value';
      $gui->model = $err;
      $smarty->my_template_source = $extras;
      echo "<tr><td colspan=2 class='gui_form'><fieldset>\n";
      $smarty->display('string:'. $extras);
      echo "\n</fieldset>\n</td></tr>\n";
    }
  }

  function draw (){
    global $_SHOP;
    $tab = $this->drawtabs();
    if (! $tab) { return; }
    switch ($tab-1){
      case 0:

        if($_GET['action']=='add'){
          $hand= new Handling(true);
          $this->form((array)$hand, $hand, con('handling_add_title'));
          return 0;
  		  }elseif($_GET['action']=='edit'){
    		  $hand=Handling::load($_GET["handling_id"]);
    		  $this->form((array)$hand, $hand, con('payment_update_title'));
   			  return 0;
  		  }elseif($_POST['action']=='save'){
          $new = false;
          if(!$hand=Handling::load($_POST["handling_id"])){
            $hand= new Handling(true); $new = true;
          }
          if(!$hand->fillPost() || !$hand->saveEx()){
      		  $this->form($_POST, $hand, con('handling_update_title')); //handling_add_title
      			return 0;
          } elseif ($new){
            $hand->admin_init();
            $this->form((array)$hand, $hand, con('handling_update_title'));
            return 0;
          }else{
            addNotice('save_successful');
          }
  		  } elseif ($_GET['action'] == 'active_hand') {
  		    $row = Handling::load($_GET["handling_id"]);
  		    if ($row) {

  		      $mode = ($_GET['mode']==='www')?'www':'sp';
  		      $do = is($_GET['do'],'');
  		      if ($do==='add' and !array_key_exists($mode,$row->sale_mode)) {
  		        $row->sale_mode[$mode] = $mode;
  		        $row->save();
  		      } elseif ($do==='remove' and array_key_exists($mode,$row->sale_mode)) {
  		        $row->sale_mode = array_diff_assoc($row->sale_mode, array($mode=>1));
  		        $row->save();
  		      }
  		    }

    		} elseif($_GET['action']=='remove' and $_GET['handling_id']>0){
       		$hand=new Handling();
          $hand->handling_id=$_GET['handling_id'];
          $hand->delete();
      	}
      	$this->table();
        break;
      default:
        return plugin::call('is'.get_class($this).'_Draw',$tab-1, $this);
    }
	}


  function print_select_tpl ($name, $type, &$data, $err, $inclPdf=false, $inclTicket=true){
    global $_SHOP;

    $query="SELECT template_name FROM Template WHERE template_type IN ({$type}) ORDER BY template_name";
    if(!$res=ShopDB::query($query)){
      return FALSE;
    }

    $sel[$data[$name]]=" selected ";

    echo "<tr><td class='admin_name'  width='40%'>".self::print_label($name,'-input',self::check_required($name, $err))."</td>
    <td class='admin_value'><nowrap>
     <select name='$name'>
     <option value=''></option>\n";

    while($v=shopDB::fetch_row($res)){
      $value=htmlentities($v[0],ENT_QUOTES);
      echo "<option value='$value' ".is($sel[$v[0]],'').">{$v[0]}</option>\n";
    }

    echo "</select>";
    if ($inclPdf) {
      echo "</nowrap>".printMsg($name)."</td></tr>\n";

      echo "<tr><td class='admin_name'  width='40%'>".con($name."_incl_pdf")."</td>";
      echo "<td class='admin_value'>";

      //Include Inv/Ord
      $checked = ($data["{$name}_incl_inv_pdf"]==1)?"selected":"";
      echo "&nbsp;&nbsp;".con("{$name}_incl_inv_pdf")."
             <select name='{$name}_incl_inv_pdf'>
                <option value='0'>".con('confirm_no')."</option>\n
                <option value='1' $checked>".con('confirm_yes')."</option>\n";
      echo "</select>";

      //Include Tickets
      if ($inclTicket) {
        $checked = ($data["{$name}_incl_ticket_pdf"]==1)?"selected":"";
        echo "&nbsp;&nbsp;".con("{$name}_incl_ticket_pdf")."
               <select name='{$name}_incl_ticket_pdf'>
                  <option value='0'>".con('confirm_no')."</option>\n
                  <option value='1' $checked>".con('confirm_yes')."</option>\n";
        echo "</select>";

      }

      echo "</td></tr>\n";

    }else{
      echo "</nowrap>".printMsg($name)."</td></tr>\n";
    }
  }

}
?>