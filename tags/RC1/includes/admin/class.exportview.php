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

/**
 *
 *
 */
class ExportView extends AdminView {
  private $current_row = 2;
  private $current_col = 0;
  var $img_pub = array ('pub' => '../images/grun.png',
                        'unpub' => '../images/rot.png',
                        'nosal' => '../images/grey.png');

  function startExport($title, $maxcol =1){
    // Creating a workbook
    GLOBAL $_SHOP; //return '';
    require_once 'Spreadsheet/Excel/Writer.php';

    $this->workbook = new Spreadsheet_Excel_Writer();

    // sending HTTP headers
    $this->workbook->setTempDir($_SHOP->tmp_dir);
    $this->workbook->send($title.".xls");

    // Creating a worksheet
    $this->worksheet =& $this->workbook->addWorksheet (con('export_xl'));

    $this->format['bold'] = $this->workbook->addFormat();
    $this->format['bold']->setBold();

    $this->format['title'] = $this->workbook->addFormat();
    $this->format['title']->setBold();
    $this->format['title']->setPattern(1);
    $this->format['title']->setFgColor(26);
    $this->format['title']->setbottom(1);

    $this->format['titler'] = $this->workbook->addFormat(array('Align'=>'right'));
    $this->format['titler']->setBold();
    $this->format['titler']->setPattern(1);
    $this->format['titler']->setFgColor(26);
    $this->format['titler']->setbottom(1);

    $this->format['price'] = $this->workbook->addFormat();
    $this->format['price']->setNumFormat('#,##0.00;-#,##0.00');
    $this->format['price']->setAlign('right');

    $this->format['priceb'] = $this->workbook->addFormat();
    $this->format['priceb']->setNumFormat('#,##0.00;-#,##0.00');
    $this->format['priceb']->setAlign('right');
    $this->format['priceb']->setBold();


    $this->format['header'] = $this->workbook->addFormat();
    $this->format['header']->setBold();
    $this->format['header']->setSize(15);
    $this->format['header']->setAlign('merge');
    $this->format['header']->setAlign('top');

    $this->format['header2'] = $this->workbook->addFormat();
    $this->format['header2']->setBold();
    $this->format['header2']->setSize(12);
    $this->format['header2']->setAlign('merge');
    $this->format['header2']->setAlign('top');
    $this->format['header2']->setAlign('left');

    $this->format['header2r'] = $this->workbook->addFormat();
    $this->format['header2r']->setBold();
    $this->format['header2r']->setSize(12);
    $this->format['header2r']->setAlign('merge');
    $this->format['header2r']->setAlign('top');
    $this->format['header2r']->setAlign('right');

    $this->format['left']   = $this->workbook->addFormat(array('Align'=>'left'));
    $this->format['center'] = $this->workbook->addFormat(array('Align'=>'center'));
    $this->format['right']  = $this->workbook->addFormat(array('Align'=>'right'));


    $this->format['leftb']  = $this->workbook->addFormat(array('Align'=>'left'));
    $this->format['leftb']->setBold();

    $this->format['rightb'] = $this->workbook->addFormat(array('Align'=>'right'));
    $this->format['rightb']->setBold();
//    $this->format['rightb']->setBgColor(26);


    // The actual data
    $this->worksheet->hideGridLines();

    $this->worksheet->setrow(0,25);
    $this->worksheet->setrow(1,15);
    if (is_array($maxcol)) {
      $x = 0;// print_r($maxcol);
      foreach($maxcol as $size) {
        $this->worksheet->setcolumn($x, $x, $size);
        $x++;
      }
      $maxcol = $x;
    }
    $this->worksheet->write(0, 0, con($title), $this->format['header']);
    for($x=1;$x<$maxcol;$x++){
      $this->worksheet->write(0, $x, "", $this->format['header']);
    }
  }
  function showtitles($header, $format = 'title', $reset=true ){

    foreach ($header as $value){
      $this->worksheet->write(1, $this->current_col, $value, $this->format[$format]);
      $this->current_col++;
    }
    if ($reset) $this->current_col = 0;
  }

  function showrow($row, $format = 'left', $reset=true ){

    foreach ($row as $value){
      $this->worksheet->write($this->current_row, $this->current_col, $value, $this->format[$format]);
      $this->current_col++;
    }
    if ($reset) {
      $this->current_col = 0;
      $this->current_row++;
    }

  }

  function finishExport(){
    // Let's send the file
    $this->workbook->close();
  }

  function getSearchDate (&$start_date, &$end_date, &$month, &$year) {
    global $_SHOP;
    $_SESSION['static.month'] = is($_REQUEST['month'],is($_SESSION['static.month'],0));
    $_SESSION['static.year'] = is($_REQUEST['year'],is($_SESSION['static.year'],0));

    if (!(!empty($_SESSION['static.month']) or !empty($_SESSION['static.year']))){
      $date = date('Y-m-1');

      $query = "select event_date from Event
                where event_date>='$date'
                {$_SHOP->admin->getEventRestriction()}
                order by event_date,event_time limit 1";
      if ($row = ShopDB::query_one_row($query, false) and !empty($row[0])){
        list($year, $month) = explode('-', $row[0]);
        $start_date = "$year-$month-01";
        $end_date = "$year-$month-31";
      }else{
        $start_date = date("Y-m-01");
        $end_date = date("Y-m-31");
        $month = date("m");
        $year = date("Y");
      }
    }elseif (!($_SESSION["static.month"] and $_SESSION["static.year"])){
      $start_date = date("Y-m-01");
      $end_date = date("Y-m-31");
      $month = date("m");
      $year = date("Y");
    }else{
      $start_date = $_SESSION["static.year"] . "-" . $_SESSION["static.month"] . "-01";
      $end_date = $_SESSION["static.year"]   . "-" . $_SESSION["static.month"] . "-31";
      $month = $_SESSION["static.month"];
      $year = $_SESSION["static.year"];
    }
  }

  function Showurlplacemap($events){
    $row = shopdb::query_one_row("select pmp_id from `PlaceMapPart` where pmp_event_id={$events['event_id']}");
    if ($row) {
      return "<a target='info_placemap' href='?action=view_pmp&event_id={$events['event_id']}&pmp_id={$row['pmp_id']}&area=admin' ><img src='{$this->img_pub[$events['event_status']]}'></a>";
    }
    return "<img style='' src='{$this->img_pub[$events['event_status']]}'>";
  }

  function showplacemap($event_id){
    global $_SHOP;

    require_once('shop_plugins'.DS.'function.placemap.php');
    $places = placeMapDraw(array('category_pmp_id'=>$_GET['pmp_id'],'category_ident'=>-1), false,true,"admin",16,15,false);
    echo "<div style='overflow: auto; height: 350px; width:{$this->width}px; border: 1px solid #DDDDDD;background-color: #fcfcfc' align='center' valign='middle'>
            {$places}
          </div>";
/*
    echo "<br>
            <a id='admin_list' class='admin-button ui-state-default admin-button-icon-left ui-corner-all link' href='javascript:history.back();' onclick='javascript:history.back();' title='List' alt='List'>
              	<span class='ui-icon' style='background-image:url(\"{$_SHOP->images_url}arrow_left.png\"); background-position:center center; margin:-8px 5px 0 0; top:50%; left:0.6em; position:absolute;' title='List' ></span>List
            </a>&nbsp;";
    */
    echo $this->pmpnamesList($places->pm_id, $_GET['pmp_id'] );
  }
  function pmpnamesList ($pm_id, $pmp_id = 0, $view_only = false) {
    if (!$pmps = PlaceMapPart::loadNames($pm_id) or count($pmps) < 2) {
      return;
    }

    if ($view_only) {
      $action = "view_only_pmp";
    } else {
      $action = "view_pmp";
    }

    //        echo"<br><br><center>";
    foreach($pmps as $pmp) {
      if ($pmp_id != $pmp->pmp_id) {
        echo " ".$this->show_button("{$_SERVER['PHP_SELF']}?action={$action}&pmp_id={$pmp->pmp_id}",$pmp->pmp_name,1);
      } else {
        echo " ".$this->show_button("{$_SERVER['PHP_SELF']}?action={$action}&pmp_id={$pmp->pmp_id}",$pmp->pmp_name,1,
            array('disable'=>true));
      }
    }
    //        echo"</center>";
  }

}
?>