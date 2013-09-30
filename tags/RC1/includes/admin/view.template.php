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


class TemplateView extends AdminView{
  private $types = array('systm','email', 'swift', 'pdf2','pdf');
  var $tabitems = array(
    0=>"templ_System|admin",
    1=>"templ_email|admin",
 //   2=>"templ_swift|adminz",  //the newshift system needs to be extended
    3=>"templ_pdf2|admin",
//    4=>"templ_files|adminz"
  );

  function show_pdf() {
    if ($_GET['action'] == 'view' and $_SESSION['_TEMPLATE_tab']=='3'){
      $query = "SELECT * FROM Template WHERE template_id="._esc($_GET['template_id']);
      if ($row = ShopDB::query_one_row($query)){
        $this->template_view($row, $row['template_type']);
        return 1;
      }
    }
    return 0;
  }

  function template_view ($data, $type) {
    global $_SHOP,  $_COUNTRY_LIST;
    if (!isset($_COUNTRY_LIST)) {
      if (file_exists($_SHOP->includes_dir."/lang/countries_". $_SHOP->lang.".inc")){
        include_once("lang/countries_". $_SHOP->lang.".inc");
      }else {
        include_once("lang/countries_en.inc");
      }
    }
   	$name = $data['template_name'];
    switch ($data['template_type']) {
      case 'systm':
        include('admin/templatedata.php');
      	$order['is_member']     = ($order['user_status']==2);
        $order['active']        = (empty($order['active']));
        $order['link']          = '{HTML-ActivationCode}';
        $order['activate_code'] = '{ActivationCode}';
        $order['new_password']  = '{NewPassword}' ;
     	case 'email':

      case 'swift':
        //include('templatedata.php');
        include('admin/templatedata.php');
        require_once("classes/model.template.php");
        if (!$tpl = Template::getTemplate($name)) {
          return false;
       	}

        $lang = is($_GET['lang'], $_SHOP->lang);
        if (!in_array($lang, $tpl->langs )) {
          $lang = $tpl->langs[0];
        }
        $_GET['lang'] = $lang;

        $tpl->write($swift, $order, $lang);

        $langs = array();
        foreach($tpl->langs as $lng) {
          $langs[$lng] = (isset($_SHOP->langs_names[$lng]))?$_SHOP->langs_names[$lng]:$lng;
        }

        echo "<form method='GET' name='frmEvents' action='{$_SERVER['PHP_SELF']}'  autocomplete='off'>\n";
        echo "<table class='admin_form' width='$this->width' cellspacing='1' cellpadding='4'>\n";
        echo "<tr><td colspan='2' class='admin_list_title' >" . $data["template_name"] . "</td></tr>";
        $this->print_select_assoc ("lang", $_GET, $err, $langs, "onchange='javascript: document.frmEvents.submit();'");
        echo "<tr><td colspan='2' class='admin_name'>" .con('email_header'). "</td></tr>";
        echo "<tr><td colspan='2' class='admin_value' style='border:#cccccc 2px dashed;padding:10px;'>" .
				nl2br(htmlspecialchars($swift->getHeaders()->toString())) . "</td></tr>";

        echo "<tr><td colspan='2' class='admin_name'>" .con('email_body'). "</td></tr>";
        echo "<tr><td colspan='2' class='admin_value' style='border:#cccccc 2px dashed;padding:10px;'>" .
				nl2br(htmlspecialchars($swift->toString())) . "</td></tr>";

        echo "<tr><td colspan='1'>";
        echo $this->show_button("{$_SERVER['PHP_SELF']}","admin_list",3);
        echo "</td><td align='right'>";
        echo $this->show_button("{$_SERVER['PHP_SELF']}?action=test&template_id={$data['template_id']}","emailtest",3);
        echo $this->show_button("{$_SERVER['PHP_SELF']}?action=edit&template_id={$data['template_id']}","edit",3);
        echo " </td></tr>";
       	echo "</table>\n";
        echo "<input type='hidden' name='action' id='action' value='view'>
          <input type='hidden' name='template_id' id='' value='{$data['template_id']}'>
          </form>";

        break;
      case 'pdf2':
        require_once("classes/model.template.php");
        require_once(LIBS."html2pdf/html2pdf.class.php");
        include('admin/templatedata.php');

        $paper_size=$_SHOP->pdf_paper_size;
			  $paper_orientation=$_SHOP->pdf_paper_orientation;
			  $_SHOP->lang = is($_SHOP->lang,'en');
			  $pdf = new html2pdf(($paper_orientation=="portrait")?'P':'L', $paper_size, $_SHOP->lang);

			  // file_put_contents  ( 'test.txt'  , print_r(array($order, $seat),true));
			  if($tpl =& Template::getTemplate($name)){
          $tpl->write($pdf, $order, false); //
        }else{
		      return addwarning("no_template");
		    }
		    $order_file_name = "pdf_{$data['template_name']}.pdf";
        $pdf->output($order_file_name, 'I');
		    break;
      default:
        echo "<table class='admin_form' width='$this->width' cellspacing='1' cellpadding='4'>\n";
        echo "<tr><td colspan='2' class='admin_list_title' >" . $data["template_name"] . "</td></tr>";

        $this->print_field('template_ts', $data);
        $this->print_field('template_status', $data);

        echo "<tr><td colspan='2' class='admin_value' style='border:#cccccc 2px dashed; padding:10px;'>" .
          nl2br(htmlspecialchars($data["template_text"])) . "</td></tr>";

        echo "</table>\n";
        echo "<br><center><a class='link' href='{$_SERVER['PHP_SELF']}'>" . con('admin_list') . "</a></center>";
    	}
	}

  function template_form_swift(&$data, &$err, $title, $type) {
    global $_SHOP;

    $this->codeInsert();

    echo "<form method='POST' action='{$_SERVER['PHP_SELF']}'   autocomplete='off'>\n";
    if ($data['template_id']){
      echo "<input type='hidden' name='template_id' value='{$data['template_id']}'/>\n";
      echo "<input type='hidden' name='action' value='update'/>\n";
    }else{
      echo "<input type='hidden' name='action' value='insert'/>\n";
    }

    $this->form_head($title);

    $data['template_array'] = unserialize($data['template_text']);
    //$data['template_text'] = htmlspecialchars($data['template_text'], ENT_QUOTES);

    $this->print_field_o('template_id', $data);
    $this->print_field('template_type', $type );

    if ($data['template_id']) {
      $this->print_field('template_name', $data, $err, 30, 100);
    } else {
    $this->print_input('template_name', $data, $err, 30, 100);
    }

    $this->print_input("email_to_name", $data['template_array'], $err, 30, 100,"",'template_array');
    $this->print_input("email_to_email", $data['template_array'], $err, 30, 100,"",'template_array');
    $this->print_input("email_from_name", $data['template_array'], $err, 30, 100,"",'template_array');
    $this->print_input("email_from_email", $data['template_array'], $err, 30, 100,"",'template_array');

    $this->print_multiRowField('emails_cc',$data['template_array'], $err, 30, 100, true,'template_array');
    $this->print_multiRowField('emails_bcc',$data['template_array'], $err, 30, 100, true,'template_array');

    $this->print_input("email_def_lang", $data['template_array'], $err, 10, 5,"",'template_array');

    $fields = array('template_subject'=>array('type'=>'text','size'=>'60','max'=>'150'),
      'template_text'=>array('type'=>'textarea','cols'=>'92','rows'=>'10'),
      'template_html'=>array('type'=>'textarea','cols'=>'92','rows'=>'10')
    );
    //$data['template_array']['email_templates'] = array('en'=>array('template_group'=>'en','template_text'=>'hello email body'));
    $this->print_multiRowGroup('email_templates',$data['template_array'],$err , $fields,'template_array',array('add_arr'=>$_SHOP->langs_names));

    $this->form_foot(2, $_SERVER['PHP_SELF']);
  }

  function template_form (&$data, &$err, $title, $type) {
    global $_SHOP;

    $this->codeInsert();

    echo "<form method='POST' action='{$_SERVER['PHP_SELF']}'  autocomplete='off'>\n";
    if ($data['template_id']){
      echo "<input type='hidden' name='template_id' value='{$data['template_id']}'/>\n";
      echo "<input type='hidden' name='action' value='update'/>\n";
    }else{
      echo "<input type='hidden' name='action' value='insert'/>\n";
    }

    $this->form_head($title);
    $data['template_text'] = htmlspecialchars($data['template_text'], ENT_QUOTES, 'UTF-8');

    $this->print_field_o('template_id', $data);
    $this->print_field('template_type', $type );
    If ($type == 'systm') {
      $this->print_field('template_name', $data);
      echo "<input type='hidden' name='template_name' value='{$data['template_name']}'/>\n";
    } elseif ($data['template_id']) {
      $this->print_field('template_name', $data);
      $this->print_hidden('template_name', $data);
    } else {
      $this->print_input('template_name', $data, true, 30, 100);
    }
//    $this->print_select ("template_type", $data, $err, array("email", "pdf2"));   //"pdf",

    //cols = 96 is too big in ff and ie 92 is the max size before you misshape the table, this is because opera adds the scrollbar.
    $this->print_large_area('template_text', $data, true, 20,92,'',array('escape'=>false) );
    $this->form_foot(2, $_SERVER['PHP_SELF']);
  }



  function template_list ($type)  {
    global $_SHOP;
    $query = "SELECT * FROM Template
             where template_type = '{$type}'
             order by template_type, template_name";
    if (!$res = ShopDB::query($query)){
      return;
    }

    $addnew = (($type== 'systm')or ($type== 'pdf'))?null:"{$_SERVER['PHP_SELF']}?action=add";

    $alt = 0;
    $this->grid_head('template_title',array(
                                          'status_header'=>array( 'width'=>25, 'sort'=> false, 'type'=>'null'),
                                          'template_name_header'=>array( 'width'=>($this->width-110) ),
                                          'actions_header'=>array('width'=>65, 'type'=>'null' )),
                                      array(
                                         'addaction'=>$addnew,
                                         'width'=>$this->width,
                                         'height'=>'300px',
                                         'addfilter'=>true));//    $addnew, $this->width,'300px',true);

    $img_pub['new']   = '../images/new.png';
    $img_pub['error'] = '../images/error.png';
    $img_pub['comp']  = '../images/compiled.png';

    while ($row = shopDB::fetch_assoc($res)){
      echo "<tr class='admin_list_row_$alt'>";
      echo "<td class='admin_list_item'><img src='{$img_pub[$row['template_status']]}'></td>\n";
//      echo "<td class='admin_list_item'>{$row['template_id']}</td>\n";
      //echo "<td class='admin_list_item' width='10%'>{$row['template_type']}</td>\n";
      echo "<td class='admin_list_item' >{$row['template_name']}</td>\n";
      $target = ($type=='pdf2')?'target="_blank"':'';
      echo "<td class='admin_list_item' width='65' align='right' nowarp=nowarp'><nowrap>";
      if ($row['template_type'] !=='pdf2') {
        echo $this->show_button("{$_SERVER['PHP_SELF']}?action=view&template_id={$row['template_id']}","view",2);
      } else {
        echo $this->show_button("{$_SERVER['PHP_SELF']}?action=view&template_id={$row['template_id']}","view",2,array('target'=>'pdfdoc'));
      }
      if ($row['template_type'] !=='pdf') {
        echo $this->show_button("{$_SERVER['PHP_SELF']}?action=edit&template_id={$row['template_id']}","edit",2);
      }
      echo $this->show_button("javascript:if(confirm(\"".con('delete_item')."\")){location.href=\"{$_SERVER['PHP_SELF']}?action=remove&template_id={$row['template_id']}\";}","remove",2,array(
        'disable'=>$row['template_type']==='systm',
        'showtooltip'=>$row['template_type']!=='systm',
        'tooltiptext'=>con('remove')." {$row['template_name']}"))."\n";
      echo "</nowrap></td>\n";
      echo "</tr>";
      $alt = ($alt + 1) % 2;
    }
   $this->grid_footer();
    echo "<table width= '{$this->width}'>
      <tr align='right'>
        <td colspan='5'>";
          echo $this->show_button("{$_SERVER['PHP_SELF']}?action=compile_all","compile_all",1);
          if($type=="swift" || $type=='email' || $type=='systm'){
           // echo $this->show_button("{$_SERVER['PHP_SELF']}?action=sendtest","send_test",1);
          }
    echo "</td></tr>\n";

    echo "</table>\n";


  }


  private function codeInsert(){
    $script = "
      //Add Listeners for the selected field.
      var curInput = false;
      $('input[name*=\"template\"]').live('click',function(){
        curInput = this;
      });
      $('textarea[name*=\"template\"]').live('click',function(){
        curInput = this;
      });

      //Add Listener for the option field.
      $('#template-vars').dblclick(function(e){
        var name = $(this).val();
        name = '{'+'$'+name+'}';
        if(curInput){
          $(curInput).insertAtCaret(name);
        }
      });
    ";
    $this->addJQuery($script);
  }

  function execute(){
    $tab = $this->drawtabs(null,false);
    if (! $tab) { return; }
    if (isset($this->types[$tab-1])) {
      $type =  $this->types[$tab-1];
    } else {
      return plugin::call('is'.get_class($this).'_Execute', $tab-1, $this);
    }
    if (($_GET['action'] == 'view') and ($type=='pdf2')){
      $query = "SELECT * FROM Template WHERE template_id="._esc($_GET['template_id']);
      if (!$row = ShopDB::query_one_row($query)){
        return 0;
      }
      $this->template_view($row, $type);
      return 1;
    }
  }

  function draw (){
    global $_SHOP;
    $tab = $this->drawtabs();
    if (! $tab) { return; }
    if (isset($this->types[$tab-1])) {
      $type =  $this->types[$tab-1];
    } else {
      plugin::call('is'.get_class($this).'_Draw', $tab-1, $this);
      return false;
    }

    if ($_GET['action'] == 'add'){
      if($type=='swift'){
        $this->template_form_swift($row, $err, con('template_add_title'), $type);
      }else{
        $this->template_form($row, $err, con('template_add_title'), $type);
      }
    }elseif ($_POST['action'] == 'insert'){
      $this->preInsertEmailTemp();
      if ($this->template_check($_POST, $err)){
        $query = "INSERT Template (template_name, template_type, template_text, template_status)
     					    VALUES ("._esc($_POST['template_name']) . ",
                  "._esc($type).",
   					      "._esc($_POST['template_text']).",
                  'new')";
			  ShopDB::begin('Inserting new template');
 		    if (ShopDB::query($query)){
 		      if ($this->compile_template($_POST['template_name'])){
 		        ShopDB::commit('Inserted the new template');
 		        $this->template_list($type);
 		        return 1;
 		      }
        }
		  }
      ShopDB::rollback('Inserted the new template');
      if($type == "swift" ){
		    $this->template_form_swift($_POST, $err, con('template_add_title'), $type);
		  }else{
		    $this->template_form      ($_POST, $err, con('template_add_title'), $type);
		  }
    }elseif ($_GET['action'] == 'edit'){
      $query = "SELECT * FROM Template WHERE template_id="._esc($_GET['template_id']);
      if (!$row = ShopDB::query_one_row($query)){
        return 0;
      }
      if($type=='swift'){
        $this->template_form_swift($row, $err, con('template_add_title'), $type);
      }else{
        $this->template_form($row, $err, con('template_add_title'), $type);
      }
    }elseif ($_POST['action'] == 'update'){
      $this->preInsertEmailTemp();
      if ($this->template_check($_POST, $err)){
    		$query = "UPDATE Template SET
  		   template_text=" . _esc($_POST['template_text']) . ",
  		   template_status='new'
  		   WHERE template_id="._esc((int)$_POST['template_id']);
         ShopDB::begin('updating the template');

        if (ShopDB::query($query)){
       		if ($this->compile_template($_POST['template_name'])){
       		  ShopDB::commit('updated the template');
       		  $this->template_list($type);
       		  return 1;
       		}
        }
      }
      ShopDB::rollback('updating the new template');

		  if($type == "swift" ){
		    $this->template_form_swift($_POST, $err, con('template_add_title'),$type);
		  }else{
		    $this->template_form($_POST, $err, con('template_add_title'), $type);
		  }
    }elseif ($_GET['action'] == 'view'){
      		$query = "SELECT * FROM Template WHERE template_id="._esc($_GET['template_id']);
      		if (!$row = ShopDB::query_one_row($query)){
        		return 0;
      		}
      		$this->template_view($row, $type);
    }elseif ($_GET['action'] == 'test' and $_GET['template_id'] > 0){
      $query = "SELECT * FROM Template WHERE template_id="._esc($_GET['template_id']);
      if (!$row = ShopDB::query_one_row($query)){
        return 0;
      }
      $this->sendtest_email($row['template_name']);
      $this->template_view($row, $type);

    }elseif ($_GET['action'] == 'remove' and $_GET['template_id'] > 0){
      		$query = "DELETE FROM Template WHERE template_id="._esc($_GET['template_id']);
      		if (!ShopDB::query($query)){
        		return 0;
      		}
      		$this->template_list($type);
    }elseif ($_GET['action'] == 'compile_all'){
      		$this->compile_all();
      		$this->template_list($type);
    }else{
      		$this->template_list($type);
    }
  }

  function compile_all() {
    global $_SHOP;
    $query = "SELECT template_name FROM Template where template_type <> 'PDF' order by template_name ";
    if (!$res = ShopDB::query($query)){
      return;
    } while ($row = shopDB::fetch_assoc($res)){
      //echo "compile: {$row['template_name']}<br>\n";
      $this->compile_template($row['template_name']);
    }
  }
  function template_check (&$data, &$err){
    // echo nl2br(htmlspecialchars(print_r($data,true)));
    if (empty($data['template_id'])) {
      if (empty($data['template_name'])){
        adderror('template_name', 'mandatory');
      } elseif(!preg_match("/^[_0-9a-zA-Z-]+$/", $data['template_name'])){
        adderror('template_name', 'invalid');
      }
    }
    if (empty($data['template_text'])){
      adderror('template_text', 'mandatory');
    }
    return !hasErrors();
  }

  function compile_template ($name){
    global $_SHOP;
    require_once("classes/model.template.php");

    if(!Template::getTemplate($name, true)){
      addWarning('compilation_failed', $name);
      return false;
    }else{
      addNotice('compilation_succeed',$name);
      return true;
    }
  }

  private function preInsertEmailTemp(){
    if(is($_POST['template_array'],false)){
      $tempArr = $_POST['template_array'];

      $tempArr['emails_cc'] = is($tempArr['emails_cc'],array());
      foreach($tempArr['emails_cc'] as $key=>$array){
        $tempArr['emails_cc'][$array['key']]=$array['value'];
        unset($tempArr['emails_cc'][$key]);
      }

      $tempArr['emails_bcc'] = is($tempArr['emails_bcc'],array());
      foreach($tempArr['emails_bcc'] as $key=>$array){
        $tempArr['emails_bcc'][$array['key']]=$array['value'];
        unset($tempArr['emails_bcc'][$key]);
      }


      $_POST['template_array'] = $tempArr;
      $_POST['template_text']=serialize($_POST['template_array']);
    }
  }

  function extramenus(&$menu) {
    if ( ($_REQUEST['action']!=='edit') && ($_REQUEST['action']!=='add') ) {return;}
    $include="
    <table width='190' class='menu_admin' cellspacing='2' style='padding-left: 0px;'>
      <tr><td class='menu_admin_title'>".con('legende')."</td></tr>
      <tr><td  style='padding-right: 0px;'>
         <select id='template-vars' name='choicefield'  multiple='multiple' size='15'class='menu_admin' style='border: none; width:100% '>";

    include('templatedata.php');

    $select ='';
    foreach($order as $key => $value) {
      $test = substr($key,0,strpos($key,'_'));
      if ($key == 'bill') {
        $include .= "<OPTGROUP LABEL='".con('bill')."'/>";
        $value = reset($value);
        foreach($value as $key => $valuex) {
           $include .= "<option value='bill[].{$key}'>bill[].{$key}</option>\n";
        }
        continue;
      } elseif ($select <> $test) {
        //TODO: is test ment to be set? As it never is...
        $select = $test;
        $include .= "<OPTGROUP LABEL='".con($select)."'/>";
      }
      $include .= "<option value='{$key}'>{$key}</option>\n";
    }
  	$orderx['is_member']     = ($order['user_status']==2);
    $orderx['active']        = (empty($order['active']));
    $orderx['link']          = '{HTML-ActivationCode}';
    $orderx['activate_code'] = '{ActivationCode}';
    $orderx['new_password']  = '{NewPassword}' ;
    $select ='';
    $include .= "<OPTGROUP LABEL='".con('others')."'/>";
    foreach($orderx as $key => $value) {
      $include .= "<option value='{$key}'>{$key}</option>\n";
    }
    $include .= "
        </select>
      </td></tr>
    </table><br>";
    $menu[] = $include;
  }

  function sendtest_email($template_name){
    global $_SHOP;
    include ('templatedata.php');

    $tpl= &Template::getTemplate($template_name);

    $order_d=(array)$order;   //print_r( $order_d);
    $link= $_SHOP->root_base . "checkout.php?action=view&id={$order['order_id']}";
    $order_d['order_link']=$link;
    $order_d['note_subject']=empt($order['emailSubject'],"");
    $order_d['note_body']   =empt($order['emailNote'],"");

    $order_d['action']= is($order_d['action'],'Handle: '.$new_state.'->'.$template_name);
    $order_d['user_email']= $_SHOP->organizer_email;
    $order_d['user_firstname']= 'test';
    $order_d['user_lastname']= 'email';

    if(!Template::sendMail($tpl, $order_d, "", $_SHOP->lang)){
      addwarning('testemail_notsend');
    } else
      addnotice('testemail_was_send_to',$_SHOP->organizer_email);
  }
}
?>