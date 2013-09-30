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
require_once("admin/class.exportview.php");


class StatisticView extends ExportView{
    var $img_pub = array ('pub' => '../images/grun.png',
                          'unpub' => '../images/rot.png',
                          'nosal' => '../images/grey.png');
    var $tabitems = array (
      0=>'show_text_stats|control',
      1=>'show_grafik_stats|control',
      2=>'show_stat_reports|admin');


  function plotEventStats ($start_date, $end_date, $month, $year) {
    global $_SHOP;
    $query = "select MAX(event_total) as count from Event";
    if (!$res = ShopDB::query_one_row($query)){
      user_error(shopDB::error());
      return;
    }
    $max_places = $res['count'];
    if (!($max_places > 0)){
      return;
    }

    $query = "select  event_id, event_name, event_date, event_time, event_status, event_total, event_free
              from Event
              where field(event_status, 'trash','unpub')=0
  	           and event_date >="._esc($start_date)."
        	     and event_date <="._esc($end_date)."
        	     and event_rep LIKE '%sub%'
        	     and event_pm_id is not null
        	    order by event_date,event_time";
    if (!$evres = ShopDB::query($query)){
      user_error(shopDB::error());
      return;
    }

    echo "<table class='admin_list' border=0 width='$this->width' cellspacing='0' cellpadding='5'>\n";
    echo "<tr>
            <td class='admin_list_title' colspan=4 align='center'>
               <a class='link' href='{$_SERVER["PHP_SELF"]}?month=" . ($month > 1?$month - 1:12) . "&year=" . ($month > 1?$year:$year - 1) . "'><<<<<</a>
               ". con('event_stats_title') ." " . strftime ("%B %Y", mktime (0, 0, 0, $month, 1, $year)) . "
	             <a class='link' href='{$_SERVER["PHP_SELF"]}?month=" . ($month < 12?$month + 1:1) . "&year=" . ($month < 12?$year:$year + 1) . "'>>>>>></a>
            </td></tr>\n";
 	  echo "</table><br>\n";

    while ($event = shopDB::fetch_assoc($evres)){
      $evtot   = $event["event_total"];
      $evfree  = $event["event_free"];
      $evsaled = ($evtot - $evfree);
      If ($event["event_status"] == 'pub' or $evsaled) {
        echo "<table class='admin_list' width='$this->width' cellspacing='0' cellpadding='5'>\n";
        echo "<tr class='stats_event_item'>
                <td class='admin_list_item' colspan=4 ><img src='{$this->img_pub[$event['event_status']]}'>&nbsp;" .
                    $event["event_name"] . " - " . formatAdminDate($event["event_date"]) . " " . formatTime($event["event_time"]) . "
                </td>
              </TR><tr>";
        $query = "select *
                  from Category
                  where category_event_id=" . _esc($event["event_id"]);

        if (!$res = ShopDB::query($query)){
          user_error(shopDB::error());
          return;
        }
        $alt = 0;
        while ($cat = shopDB::fetch_assoc($res)){
          echo "<tr class='admin_list_row_$alt'>
                  <td class='stats_event_item' width='20' align='right'>&nbsp;</td>
                  <td class='admin_list_item'>
                     " . $cat["category_name"] . "
                  </td>
                  <td class='admin_list_item' align='left' width='250'>";
          $tot = $cat["category_size"];
          $free = $cat["category_free"];
          $this->plotBar($tot, $free);
          echo "</tr>";
          $alt = ($alt + 1) % 2;
        }
        echo "<tr class='stats_event_item'>
               <td class='admin_list_item' colspan=2 >&nbsp;</td>
               <td class='admin_list_item' width='250'>";
        $this->plotBar($evtot, $evfree);
        echo "</tr>";
    	  echo "</table><br>\n";
      }
    }
  }

  function plotBar ($tot, $free){
    $saled = ($tot - $free);
    $percent = ($tot)?(100 * $saled / $tot):0;
    $percent = round($percent, 0);
    echo "<table border='0' cellspacing='0' width='100%'><tr>";//$width
    if ($percent > 0){
      echo "<td bgcolor='#ff0000' width='{$percent}%'><img src='../images/dot.gif' width='0' height='12'></td>";
    }
    if ($percent < 100){
      echo "<td bgcolor='#00aa00'><img src='../images/dot.gif' width='0' height='12'></td>";
    }
    echo "</tr></table><td nowrap='nowrap'  style='width:200px;' align='right'>$percent% ($saled/$tot)</td>";
  }

  function eventStats ($start_date, $end_date, $month, $year){
    global $_SHOP;
    $curr = $_SHOP->organizer_currency;
    $query = "select seat_category_id, SUM(seat_price) as total_sum
              from Seat left join `Event` on seat_event_id = event_id
              where field(event_status, 'trash','unpub')=0
              {$_SHOP->admin->getEventRestriction('seat_')}
              group by seat_category_id";
    if (!$res = ShopDB::query($query)){
      user_error(shopDB::error());
      return;
    } while ($sums = shopDB::fetch_assoc($res)){
      $sum[$sums["seat_category_id"]] = $sums["total_sum"];
    }

    $query = "select event_free, event_total, event_id,event_name,event_date,event_time, event_status
             from Event
             where field(event_status, 'trash','unpub')=0
      	     and event_date >= '$start_date'
      	     and event_date <= '$end_date'
      	     and event_rep LIKE '%sub%'
			       and event_pm_id is not null

             {$_SHOP->admin->getEventRestriction()}
             order by event_date, event_time";
    if (!$res = ShopDB::query($query)){
      user_error(shopDB::error());
      return;
    }
    while ($event = shopDB::fetch_assoc($res)){
      $events[] = $event;
    }

    echo "<table class='admin_list' width='$this->width' cellspacing='0' cellpadding='5'>\n";
    echo "<tr><td class='admin_list_title' colspan='5' align='center'>
          <a class='link' href='{$_SERVER["PHP_SELF"]}?month=" . ($month == 1?12:$month - 1) . "&year=" . ($month == 1?$year - 1:$year) . "'><<<<< </a>
	  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" .
    con('event_stats_title') . " " .
    strftime ("%B %Y", mktime (0, 0, 0, $month, 1, $year)) .
    "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	  <a class='link' href='{$_SERVER["PHP_SELF"]}?month=" . (($month < 12)?($month + 1):1) . "&year=" . ($month < 12?$year:$year + 1) . "'>>>>>></a></td></tr>\n";
	  echo "</table><br>\n";
    for($i = 0;$i < sizeof($events);$i++){
      $evtot = $events[$i]["event_total"];
      $evfree = $events[$i]["event_free"];
      $evsaled = ($evtot - $evfree);
      If ($events[$i]["event_status"] == 'pub' or $evsaled) {
        $evpercent = ($evtot)?(100 * $evsaled / $evtot):0;
        $evpercent = round($evpercent, 2);

        echo "<table class='admin_list' width='$this->width' cellspacing='0' cellpadding='5'>\n";
        echo "<tr class='stats_event_item'><td colspan='5'><img src='{$this->img_pub[$events[$i]['event_status']]}'>&nbsp;" . $events[$i]["event_name"] . " " .
        formatAdminDate($events[$i]["event_date"]) . " " . formatTime($events[$i]["event_time"]) . "</td></tr>";

        $query = "select *
                  from Category
                  where category_event_id=" . _esc($events[$i]["event_id"]);
        if (!$res = ShopDB::query($query)){
          user_error(shopDB::error());
          return;
        }
        $alt = 0;
        $sum_gain = 0;
        while ($cat = shopDB::fetch_assoc($res)){
          $tot = $cat["category_size"];
          $free = $cat["category_free"];
          $saled = ($tot - $free);
          $percent = ($tot)?(100 * $saled / $tot):0;
          $percent = round($percent, 2);
          // $gain=$cat["category_price"]*$saled;
          if ($sum[$cat["category_id"]]){
            $gain = $sum[$cat["category_id"]];
          }else{
            $gain = 0;
          }
          $sum_gain += $gain;
          echo "
                <tr  class='admin_list_row_$alt'>
                    <td class='stats_event_item' width='20'>&nbsp;</td>
                    <td width='180'>" .$cat["category_name"] . "</td>
                    <td width='125' align='right' >$percent%</td>
  	                <td width='125' align='right' >$saled/$tot</td>
                    <td align='right'> " . valuta(sprintf("%1.2f", $gain)) . "</td>
                </tr>";
          $alt = ($alt + 1) % 2;
        }
        echo "
              <tr class='stats_event_item'>
                <td width='200' colspan='2'>&nbsp;&nbsp;</td>
                <td width='125' align='right' >$evpercent%</td>
                <td width='125' align='right' >$evsaled/$evtot</td>
                <td align='right'>  " . valuta(sprintf("%1.2f", $sum_gain)) . "</td>
              </tr>";
   //     echo "<tr><td colapsn='5'></td></tr>";
    	  echo "</table><br>\n";

        $sum_gain = 0;
      }
    }
    //echo "</table>";
  }

  function draw ()
  {
    global $_SHOP;

    $tab = $this->drawtabs();
    if (! $tab) { return; }
    switch ((int)$tab-1){
      case 0:
        $this->getSearchDate($start_date, $end_date, $month, $year);
        $this->eventStats($start_date, $end_date, $month, $year);
        break;
      case 1:
        $this->getSearchDate($start_date, $end_date, $month, $year);
        $this->plotEventStats($start_date, $end_date, $month, $year);
        break;
      case 2:
        if (is_object($this->expviewer)) {
          $this->expviewer->setwidth($this->width);
          $this->expviewer->draw(true);
        }
        break;
      default:
        plugin::call('is'.get_class($this).'_Draw', $tab-1, $this); // Lxsparks, wasn't working - this is from view.utilities.php

    }
  }


  function execute (){
    if (!($tab=$this->drawtabs(null, false))) { return; }
   // var_dump($tab);
    switch ((int)$tab-1){
      case 2 :
        require('view.transports.php');
        $this->expviewer = new TransportsView($this->width);
        return $this->expviewer->execute();
      break;

    default:
      if ($tab <256) {
        return false;
      } else {
        return plugin::call('%'.get_class($this).'_Execute', $tab-1, $this);
      }
    }
  }
}

?>