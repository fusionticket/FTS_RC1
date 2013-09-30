<?php
  /**
   *               %%%copyright%%%
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


/**
 *
 *
 * @version $Id$
 * @copyright 2010
 */
class plugin_reminders extends baseplugin {

	public $plugin_info		  = 'Order reminder system';
	/**
	 * description - A full description of your plugin.
	 */
	public $plugin_description	= 'This plugin will add the order reminder services';
	/**
	 * version - Your plugin's version string. Required value.
	 */
	public $plugin_myversion		= '0.0.1';
	/**
	 * requires - An array of key/value pairs of basename/version plugin dependencies.
	 * Prefixing a version with '<' will allow your plugin to specify a maximum version (non-inclusive) for a dependency.
	 */
	public $plugin_requires	= null;
	/**
	 * author - Your name, or an array of names.
	 */
	public $plugin_author		= 'Fusion Ticket Solutions Limited';
	/**
	 * contact - An email address where you can be contacted.
	 */
	public $plugin_email		= 'info@fusionticket.com';
	/**
	 * url - A web address for your plugin.
	 */
	public $plugin_url			= 'http://www.fusionticket.org';

  public $plugin_actions  = array ('install','uninstall');
  protected $directpdf = null;

    function getTables(& $tbls){
      $tbls['Order']['fields']['order_reminder_id'] = " int(11) DEFAULT NULL";
      $tbls['Order']['fields']['order_reminder_date'] = " datetime DEFAULT NULL";
      $tbls['reminders']['fields'] = array(
          'reminder_id' => " int(11) NOT NULL AUTO_INCREMENT",
          'reminder_name' => " varchar(50) NOT NULL DEFAULT ''",
          'reminder_ident' => " tinyint(4) NOT NULL DEFAULT '0'",
          'reminder_days' => "  int(11) NOT NULL DEFAULT '0'",
          'reminder_activate' => " enum('0','1') NOT NULL DEFAULT '1'",
          'reminder_email' => " varchar(30) DEFAULT NULL",
          'reminder_pdf' => " varchar(30) DEFAULT NULL",
          'reminder_fee' => " decimal(10,2) DEFAULT NULL",
          'reminder_color' => " varchar(10) DEFAULT NULL",
          'reminder_text' => "text");
      $tbls['reminders']['key'] = array(
          "PRIMARY KEY (`reminder_id`)",
          "KEY `reminder_days` (`reminder_days`)"
                      );
      $tbls['reminders']['engine'] = 'InnoDB';
    }

    function doHandlingsView_Items($items){
    //  var_dump($this);
      $items[$this->plugin_acl] ='reminders_list|admin';
      return $items;
    }

  function doHandlingsView_Draw($id, $view){
    global $_SHOP;
  //  var_dump($id);
   // var_dump($view);
    if ($_GET['action'] == 'add_reminder') {
      $pmz = new Reminder(true);
      $this->list_form($view, (Array)$pmz);
      return false;
    } elseif ($_GET['action'] == 'active_reminder' and $_GET['reminder_id'] > 0) {
      $pmz = Reminder::load($_GET['reminder_id']);
      $pmz->reminder_activate = $_GET['active']?'1':'0';
      if (!$pmz->save()) {
        addwarning('reminder_active_not_saved');
      }

    } elseif ($_GET['action'] == 'edit_reminder' and $_GET['reminder_id'] > 0) {
      $pmz = Reminder::load($_GET['reminder_id']);
      $this->list_form($view,(array)$pmz);
      return false;
    } elseif ($_POST['action'] == 'save_reminder') {
      if (!$pmc = Reminder::load((int)$_POST['reminder_id'])) {
        $pmc = new Reminder(true);
      }
      if (!$pmc->fillPost() || !$pmc->saveEx()) {
        $this->list_form($view, $_POST, null);
        return false;
      }
    }elseif ($_GET['action'] == 'remove_reminder' and $_GET['reminder_id'] > 0) {
      if ($pmc = Reminder::load((int)$_GET['reminder_id'])) {
        $pmc->delete();
      }
    }
    $this->list_table($view);
  }

  function list_table ($view) {
    global $_SHOP;
    $query = "SELECT *
                FROM `reminders`
                order by reminder_ident, reminder_id";
    if (!$res = ShopDB::query($query)) {
      user_error(shopDB::error());
      return;
    }
    $alt = 0;
    echo "<table class='admin_list' width='$view->width' cellspacing='1' cellpadding='2'>\n";
    echo "  <tr>
              <td class='admin_list_title' colspan='6' align='left'>". con('reminder_title') . "</td>";
    echo "    <td align='right'>".$view->show_button("{$_SERVER['PHP_SELF']}?action=add_reminder","add",3)."</td>";
    echo "<tr class='admin_list_header'>";
    echo "<th width=40 >".con('reminder_ident_th')."</th>";
    echo "<th>".con('reminder_name_th')."</th>";
    echo "<th width=40 >".con('reminder_days_th')."</th>";
    echo "<th width=40 >".con('reminder_email_th')."</th>";
    echo "<th width=40 >".con('reminder_pdf_th')."</th>";
    echo "<th width=90  align='right'>".con('reminder_fee_th')."</th>";
    echo "<th width=70 >&nbsp;</th>";
    echo "</tr>\n";

    while ($row = shopDB::fetch_assoc($res)) {
      echo "<tr class='admin_list_row_$alt'>";
      echo "<td class='admin_list_item' align='center' width=30 bgcolor='{$row['reminder_color']}'>{$row['reminder_ident']}</td>\n";
      echo "<td class='admin_list_item'>{$row['reminder_name']}</td>\n";
      echo "<td class='admin_list_item' align='right'>{$row['reminder_days']}</td>\n";
      echo "<td class='admin_list_item' align='center'>".
              ((empty($row['reminder_email']))?'&nbsp;':"<img SRC='{$_SHOP->images_url}ok.png' title='{$row['reminder_email']}'>").
           "</td>\n";
      echo "<td class='admin_list_item' align='center'>".
              ((empty($row['reminder_pdf']))?'&nbsp;':"<img SRC='{$_SHOP->images_url}ok.png' title='{$row['reminder_pdf']}'>").
           "</td>\n";
      echo "<td class='admin_list_item' align='right'>".valuta($row['reminder_fee'])."</td>\n";
      echo "<td class='admin_list_item' align='right'>\n";
      if ($row['reminder_activate'] ) {
        echo $view->show_button("javascript:if(confirm(\"".con('reminder_deactivate_question')."\")){ location.href=\"{$_SERVER['PHP_SELF']}?action=active_reminder&active=0&reminder_id={$row['reminder_id']}\"; }",('reminder_activated'),2,
             array('image'=>'checked.gif'));
      } else {
        echo $view->show_button("javascript:if(confirm(\"".con('reminder_activate_question')."\")){ location.href=\"{$_SERVER['PHP_SELF']}?action=active_reminder&active=1&reminder_id={$row['reminder_id']}\"; }",('reminder_deactivated'),2,
             array('image'=>'unchecked.gif'));
      }
      echo $view->show_button("{$_SERVER['PHP_SELF']}?action=edit_reminder&reminder_id={$row['reminder_id']}","edit",2);
      echo $view->show_button("javascript:if(confirm(\"".con('delete_item')."\")){location.href=\"{$_SERVER['PHP_SELF']}?action=remove_reminder&reminder_id={$row['reminder_id']}\";}","remove",2,
                               array('tooltiptext'=>"Delete {$row['reminder_name']}?"));
      echo "</td></tr>";
      $alt = ($alt + 1) % 2;
    }
    echo "</table>\n";
  }

  function list_form ($view, $data ) {
    echo "<form method='POST' action='{$_SERVER['PHP_SELF']}'>\n";
    echo "<input type='hidden' name='action' value='save_reminder' />\n";
    if ($data['reminder_id']) {
      echo "<input type='hidden' name='reminder_id' value='{$data['reminder_id']}' />\n";
    }

    echo "<table class='admin_form' width='$this->width' cellspacing='1' cellpadding='4'>\n";
    echo "<tr><td class='admin_list_title' colspan='2' align='left'>" . con('reminder_form_title') . "</td></tr>";

    $view->print_field_o('reminder_id', $data);
    $view->print_input('reminder_ident', $data, $err, 3, 5);

    $view->print_input('reminder_name', $data, $err, 30, 50);
    $view->print_input('reminder_days', $data, $err, 15, 15);
    $view->print_color('reminder_color', $data, $err, 15, 15);

    $this->print_select_tpl ("reminder_email", $data, 'email');
    $this->print_select_tpl ("reminder_pdf", $data, 'pdf2');

    $view->print_input('reminder_fee', $data, $err, 6, 5);
    $view->print_checkbox('reminder_activate', $data);

    $view->print_area('reminder_text', $data, $err, 6);

    $view->form_foot(2,"{$_SERVER['PHP_SELF']}");
  }

  function print_select_tpl ($name, &$data, $template='pdf2') {
    global $_SHOP;

    $query = "SELECT template_name FROM Template
              WHERE template_type="._esc($template)."
              ORDER BY template_name";

    if (!$res = ShopDB::query($query)) {
      return false;
    }

    $sel[$data[$name]] = " selected ";

    echo "<tr><td class='admin_name'  width='40%'>" . con($name) . "</td>
            <td class='admin_value'>
              <select name='$name'>
               <option value=''></option>\n";

    while ($v = shopDB::fetch_row($res)) {
      $value = htmlentities($v[0], ENT_QUOTES);
      echo "<option value='$value' " . $sel[$v[0]] . ">{$v[0]}</option>\n";
    }

    echo "</select>". printMsg($name, $err). "
          </td></tr>\n";
  }
  /* -=-=-=- -=-=-=- -=-=-=- -=-=-=- -=-=-=- -=-=-=- -=-=-=- -=-=-=-  */

  function doOrdersView_Items($items){
  //  var_dump($this);
    $items[$this->plugin_acl] ='reminders_orders|admin';
    return $items;
  }

  function doOrdersView_Draw($id, $view){
    if ($_REQUEST['action']=='remind' && isset($_REQUEST['order_id'])) {
      $this->send_reminder($_REQUEST['order_id'],$_REQUEST['reminder']);
    } elseif($_GET['action']=='details'){
      return $view->view($_REQUEST["order_id"]);
    }
    $this->orders_Table($view);
  }

  function orders_Table($view){
    $tr = $view->fill_tr();

    $info = '';
    $where = "order_status <> 'cancel'";
    $where.=" and order_payment_status <> 'paid'";

    $limit=$view->get_limit($_GET["page"]);

    $query='SELECT SQL_CALC_FOUND_ROWS * '.
           'FROM `Order` left join `reminders` on order_reminder_id=reminder_id '.
        	 "WHERE $where ".
        	 "AND Order.order_status!='trash' ".
        	 'ORDER BY ifnull(order_reminder_date, order_date) '.
        	 "LIMIT {$limit['start']},{$limit['end']}";
    $view->list_head(con('reminders_order_title'),5);

    if(!$res=ShopDB::query($query)){return;}
    if(!$count=ShopDB::query_one_row('SELECT FOUND_ROWS()', false)){return;}
    $reminders= reminder::loadAll();
    echo "<tr class='admin_list_header'>";
    echo "<th width=60>".con('reminder_order_id_th')."</th>";
    echo "<th width=80>".con('reminder_order_date_th')."</th>";
    echo "<th width=80>".con('reminder_order_total_th')."</th>";
    echo "<th >".con('reminder_order_reminder_th')."</th>";
    echo "<th width=50 >&nbsp;</th>";
    echo "</tr>\n";

    while($row=shopDB::fetch_assoc($res)){
 //     var_dump($row);
      $days = diff_date(time(),is($row["order_reminder_date"],$row["order_date"]) );

      $bgalt  = '#dddddd';
      $fgalt  = '#ffffff';
      $text   = array();
      $row["reminder_ident"] = is($row["reminder_ident"],0);
      $x = -1; $y= -1; $z = 0;
      foreach($reminders as $remind) {
        if ($remind->reminder_ident > $row["reminder_ident"] ) {
          if ($y==-1 and $remind->reminder_days+$z >= $days) {$y = $remind;}
          if ($x==-1 and $remind->reminder_days+$z <= $days) {$x = $remind;}
          $z = $remind->reminder_days;
        }
      }
      $reminder_id =0;
      if ($row['order_reminder_id']) {
        $fgalt  = $x->reminder_color;
        $text[] = $row['reminder_name'].con('reminder_is_send_on').formatAdminDate($row['order_reminder_date']);
       }
      if (is_object($x)) {
        $bgalt  = $x->reminder_color;
        $text[] = $x->reminder_name .con ('reminder_need_to_be_send');
        $reminder_id =$x->reminder_id;
      } elseif(is_object($y)) {
        $text[] = con('reminder_next_to_be_send_in').($y->reminder_days-$days).con('reminder_next_days');
      }
      $text= implode(",<br>\n", $text);
      if (!$text) { $text='&nbsp;';}

      echo "<tr bgcolor='{$bgalt}'>
      <td class='admin_list_item'>".$row["order_id"]."</td>
      <td class='admin_list_item'>".formatAdminDate($row["order_date"])."</td>
      <td class='admin_list_item' align='right'>".valuta($row["order_total_price"])."</td>
      <td class='admin_list_item' color='{$fgalt}'>".$text."</td>";

      $com=$view->order_commands($row,TRUE);
      echo "<td class='admin_list_item' align='right'>";
      echo $view->show_button("{$_SERVER['PHP_SELF']}?action=remind&reminder={$reminder_id}&order_id={$row['order_id']}",('send_reminder'),2,
           array('image'=>'history.png','disable'=>!$reminder_id));

      echo $com["details"]."</td>";
      echo "</tr>";
    }

    echo "</table>";
    echo "<br>".
         $view->get_nav ($_GET["page"], $count[0]);
  }

  function send_reminder($order_id, $reminder_id=null){
    $this->directpdf = null;
    $this->_send_reminder($order_id, $reminder_id);

    if (is_object($this->directpdf)) {
      ob_end_clean ();
      $order_file_name = "order_".$order_id.'.pdf';
      $this->directpdf->pdf->IncludeJS("print({bUI: false, bSilent: true}); this.exit(true)");
      $this->directpdf->output($order_file_name, 'D');
    }
  }

  function _send_reminder($order_id,$reminder_id){
    if (is_array($order_id)) {
      foreach($order_id as $id => $reminder_id) {
        if ($reminder_id) {
          $this->send_reminder($id, $reminder_id);
        }
      }
    } elseif ($reminder_id) {
      $remind = Reminder::load($reminder_id);
      $order  = Order::loadExt($order_id, false);
      if ($remind->reminder_email) {
        if ($this->_send_email($remind->reminder_email, $order, $remind)) {
          $this->_set_reminder($order_id,$reminder_id);
        }
      } else { //var_dump($this->directpdf);
        $this->directpdf = Order::printOrder($order_id, $remind->reminder_pdf, 'bulk', $this->directpdf, 2);
        $this->_set_reminder($order_id,$remind);
      }
    }
  }
  function _set_reminder($order_id,$remind){
    if(ShopDB::begin('set_reminder_id')){
      $query="UPDATE `Order` SET
                order_reminder_id="._esc($remind->reminder_id).",
                order_reminder_date= CURRENT_TIMESTAMP()
              WHERE order_id="._esc($order_id);
      if(!ShopDB::query($query)){
        ShopDB::rollback('set order_payment_id');
        return FALSE;
      }
      OrderStatus::statusChange($order_id,'','Reminder plugin','reminder::send',"Reminder sended for ".$remind->reminder_name,$remind);

      return ShopDB::commit('set_order_payment_id');
    }

  }

  function _send_email($template_name, $order, $reminder){
   // var_dump($order);
    if($template_name and $order->user_email){
   //   var_dump($template_name);

      $tpl= &Template::getTemplate($template_name);

      $order_d=(array)$order;   //print_r( $order_d);
      $order_d['note_subject']=empt($order->emailSubect,"");
      $order_d['note_body']=empt($order->emailNote,"");
      $order_d['send_pdf']=$reminder->reminder_pdf;

      $order_d = array_merge($order_d,(array)$order->handling);
      $order_d = array_merge($order_d,(array)$reminder);
      $order_d['action']= is($order_d['action'],'Reminder: '.$reminder->reminder_name.'->'.$template_name);

      if(Template::sendMail($tpl, $order_d, "", $_SHOP->lang)){
        return true;
      }
    }
  }
}

class Reminder Extends Model {
  protected $_idName    = 'reminder_id';
  protected $_tableName = 'reminders';
  protected $_columns   = array( 'reminder_id','reminder_name','#reminder_ident',
                                 '#*reminder_days', 'reminder_activate','*reminder_color',
                                 'reminder_email','*reminder_pdf','#reminder_fee','reminder_text');

  function load ($id=0){
    $query="select *
            from reminders
            where reminder_id="._esc($id);
    if($res=ShopDB::query_one_row($query)){
      $eg=new Reminder;
      $eg->_fill($res);
      return $eg;
    }
  }

  function loadAll (){
    $query="select *
            from reminders
            where reminder_activate='1'
            order by reminder_ident, reminder_id";
    if($res=ShopDB::query($query)){
      $eg = array();
      while($row=shopDB::fetch_assoc($res)){
        $e=new Reminder;
        $e->_fill($row);
        $eg[] = $e;
      }
      return $eg;
    }
  }

  function save($id = null, $exclude= false) {
    if (empty($this->reminder_ident)) {
      $row = shopdb::query_one_row('select max(reminder_ident) from `reminders` where reminder_id<> '.((int)$this->id),false);
      $this->reminder_ident = (int)$row[0]+1;
    }
    return parent::save($id,$exclude);
  }

  function delete(){
    return parent::delete();
  }

}
?>