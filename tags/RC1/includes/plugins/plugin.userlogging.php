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
 define ('TABLE_STATS','userstats');

class plugin_userlogging extends baseplugin {

	public $plugin_info		  = 'User Logging';
	/**
	 * description - A full description of your plugin.
	 */
	public $plugin_description	= 'This plugin Will log user access';
	/**
	 * version - Your plugin's version string. Required value.
	 */
	public $plugin_myversion		= '0.0.2';
	/**
	 * requires - An array of key/value pairs of basename/version plugin dependencies.
	 * Prefixing a version with '<' will allow your plugin to specify a maximum version (non-inclusive) for a dependency.
	 */
	public $plugin_requires	= null;
	/**
	 * author - Your name, or an array of names.
	 */
	public $plugin_author		= 'The FusionTicket team';
	/**
	 * contact - An email address where you can be contacted.
	 */
	public $plugin_email		= 'info@fusionticket.com';
	/**
	 * url - A web address for your plugin.
	 */
	public $plugin_url			= 'http://www.fusionticket.org';

  public $plugin_actions  = array ('install','uninstall');

	public $totaalVisits;

  function getTables(& $tbls){
    $tbls['userstats']['fields'] =array(
        "userstats_id"=>" int(11) NOT NULL AUTO_INCREMENT",
        "userstatse_timestamp"=>" datetime DEFAULT NULL",
        "userstats_ip"=>" varchar(100) DEFAULT NULL",
        "userstats_browser"=>" varchar(100) DEFAULT NULL",
        "userstats_server"=>" text",
        "userstats_referrer"=>" varchar(256) DEFAULT NULL",
        "userstats_request_uri"=>" varchar(256) DEFAULT NULL");
    $tbls['userstats']['key'] = array(
      "PRIMARY KEY (`userstats_id`)");
    $tbls['userstats']['remove'] = array ();
    $tbls['userstats']['engine'] = 'InnoDB';
  }

  function doUtilitiesView_Items($items){
    $this->loadlanguage();
    $items[$this->plugin_acl] ='useraccess_list|admin';
    return $items;
  }

  function doUtilitiesView_Draw($id, $view){
    global $_SHOP;
    //   var_dump($id);
    //  var_dump($view);
    if ($id==$this->plugin_acl) {
      $this->access_list($view);
    }
  }

		function GetTotalVisits (){
			$result=ShopDB::Query_one_row("select count(userstats_id) count from ".TABLE_STATS);
			return $result['count'];
		}

		function GetTotalUniqueVisits (){
			$result=ShopDB::Query_one_row("select count(distinct(userstats_ip)) count from ".TABLE_STATS);
			return $result['count'];
		}

		function GetTotalUniqueBrowsers (){
			$result=ShopDB::Query_one_row("select count(distinct(userstats_browser)) count from ".TABLE_STATS);
			return $result['count'];
		}

		function GetTopVisitors (& $total){
			$Visitors=Array();
      $total = 0;
			$result=ShopDB::Query("select userstats_ip, count(*) as count from ".TABLE_STATS." group by userstats_ip order by userstats_ip");
      while ($rec = shopDB::fetch_assoc($result)) {
				$Visitors[$rec["userstats_ip"]] = $rec["count"];
        $total += $rec["count"];
			}
			array_multisort($Visitors,SORT_NUMERIC,SORT_DESC);
			$VisitorCounts=Array();
			$top= 0;
			foreach ($Visitors as $k => $v) {
				$VisitorCounts[$k]=$v."/".$total;
				$top++;
				if ($top==10) {break;}
			}
			return $VisitorCounts;
		}

		function GetTopBrowsers (& $total){
			$BrowserTypes=Array();
      $total = 0;

			$result=ShopDB::Query("select userstats_browser, count(*) as count from ".TABLE_STATS." group by userstats_browser order by userstats_browser");
      while ($rec = shopDB::fetch_assoc($result)) {
				$Referrers[$rec["userstats_browser"]] = $rec["count"];
        $total += $rec["count"];
			}
			array_multisort($Referrers,SORT_NUMERIC,SORT_DESC);
			$top=0;
			$BrowserCounts=Array();
			foreach ($Referrers as $k => $v) {
				$BrowserCounts[$k]=$v."/".$total;
				$top++ ;
				if ($top==10) {break;}
			}
			return $BrowserCounts;
		}

		function GetTopRequests (& $total){
			$Referrers=Array();
      $total = 0;

			$result=ShopDB::Query("select userstats_request_uri, count(*) as count from ".TABLE_STATS." group by userstats_REQUEST_URI order by userstats_REQUEST_URI");
      while ($rec = shopDB::fetch_assoc($result)) {
				$Referrers[$rec["userstats_request_uri"]] = $rec["count"];
        $total += $rec["count"];
			}
			array_multisort($Referrers,SORT_NUMERIC,SORT_DESC);
			$ReferrerCounts=Array();
			$top=0;
			foreach ($Referrers as $k => $v){
				$ReferrerCounts[$k]=$v."/".$total;
				$top++ ;
				if ($top==10) {break;}
			}
			return $ReferrerCounts;
		}

		function GetTopReferrers () {
			$Referrers=Array();$total = 0;

			$result=ShopDB::Query("select userstats_referrer, count(*) as count from ".TABLE_STATS." group by userstats_referrer order by userstats_referrer");
      while ($rec = shopDB::fetch_assoc($result)) {
				$Referrers[$rec["userstats_referrer"]] = $rec["count"];
        $total += $rec["count"];
			}
			$ReferrerCounts=Array();
			$top=0;
			array_multisort($Referrers,SORT_NUMERIC,SORT_DESC);
			foreach ($Referrers as $k => $v){
				$ReferrerCounts[$k]=$v."/".$total;
				$top++ ;
				if ($top==10) {break;}
			}
  		return $ReferrerCounts;
		}

		function access_list ($page){
		  $this->loadlanguage();
			/* top visitors display*/
			$this->totaalVisits = $this->GetTotalVisits ();

			$TopVisitors =  $this->GetTopVisitors ($total); $row=true;

      $page->form_head(con('moda_1')." ({$this->GetTotalUniqueVisits()} ".con('moda_2').")",'100%');
			foreach ($TopVisitors as $k => $v){
				$class=($row= !$row)?"admin_name":"admin_value";
        echo "
              <tr>
                <td class='{$class}'>".(($k=="")?'{empty}':$k)."</td>
                <td class='{$class}' valign='right' width=50>".$v."</td>
              </tr>\n";
				}
			echo '</table><br>';

			/* top browsers display*/
			$TopBrowsers =  $this->GetTopBrowsers ($total); $row=true;
      $page->form_head(con('moda_3')." ({$this->GetTotalUniqueBrowsers()} ".con('moda_4').")",'100%');

			foreach ($TopBrowsers as $k => $v){
				$class=($row= !$row)?"admin_name":"admin_value";
        echo "
              <tr>
                <td class='{$class}'>".(($k=="")?'{empty}':$k)."</td>
                <td class='{$class}' valign='right' width=50>".$v."</td>
              </tr>\n";
				}
			echo '</table><br>';

			/* top referrers display */
			$TopReferrers =  $this->GetTopReferrers (); $row=true;
      $page->form_head(con('moda_5'),'100%');
			foreach ($TopReferrers as $k => $v)	{
				$class=($row= !$row)?"admin_name":"admin_value";
        echo "
              <tr>
                <td class='{$class}'>".(($k=="")?'{empty}':$k)."</td>
                <td class='{$class}' valign='right' width=50>".$v."</td>
              </tr>\n";
			}
			echo '</table><br>';

			/* top REQUEST_URI display */
			$TopRequests =  $this->GetTopRequests ($total); $row=true;
      $page->form_head(con('moda_12'),'100%');
			foreach ($TopRequests as $k => $v) {
				$class=($row= !$row)?"admin_name":"admin_value";
        echo "
              <tr>
                <td class='{$class}'>".(($k=="")?'{empty}':$k)."</td>
                <td class='{$class}' valign='right' width=50>".$v."</td>
              </tr>\n";
			}
			echo '</table><br>';

			/* raw logfile display */
		  $_REQUEST['page'] = is($_REQUEST['page'],1);
		  //  $this->page_length = 2;
		  $recstart = ($_REQUEST['page']-1)* $page->page_length;

      $sql = "select  SQL_CALC_FOUND_ROWS * from ".TABLE_STATS." order by userstatse_timestamp desc";
			$result=shopDB::Query($sql);
			if(!isset($_REQUEST['prevoffset'])){$_REQUEST['prevoffset']=0;}
			if(!isset($_REQUEST['offset'])){$_REQUEST['offset']=0;}

			$sql .=" limit ".$recstart.",". $page->page_length;
			$result=shopDB::Query($sql);
		  if(!$rowcount=ShopDB::query_one_row('SELECT FOUND_ROWS()', false)){return;}

 		  echo "<table class='admin_list' width='100%' style='table-layout:fixed;' cellspacing='1' cellpadding='4'>\n";
		  echo "<tr><td class='admin_list_title' colspan='3' align='left'>".con('moda_6')."</td></tr>\n";
		  $row=true;
      while ($rec = shopDB::fetch_assoc($result)) {
				$class=($row= !$row)?"admin_name":"admin_value";
				echo "<tr>";
				echo "<td width='12%' class='{$class}'>".$rec["userstats_ip"]."</td>\n";
				echo "<td width='15%' class='{$class}'>".formatAdminDate($rec["userstatse_timestamp"])."<br />".
                                                 formatAdminTime($rec["userstatse_timestamp"])."</td>\n";
        $this->checkurl($rec["userstats_referrer"]);
        $this->checkurl($rec["userstats_request_uri"]);
        echo"<td class='{$class}' style='overflow: hidden;' nowrap>".
             "<b>From:</b>&nbsp;<a class=\"Table\"  href=\"".$rec["userstats_referrer"]."\"  title=\"".$rec["userstats_referrer"]."\" target=\"_blank\">".$rec["userstats_referrer"]."</a><br />".
             "<b>To:</b>&nbsp;<a class=\"Table\"   href=\"".$rec["userstats_request_uri"]."\"  title=\"".$rec["userstats_request_uri"]."\" target=\"_blank\">".$rec["userstats_request_uri"]."</a>";
       // var_dump($rec);
        echo"</td></tr>\n";
      }
			echo'</table>';
		  $page->get_nav( $_REQUEST['page'], $rowcount[0]);
//			$PrevPageAction=$_SERVER['PHP_SELF']."?cmd=".MODS_USE."&file=".MODULE_NAME;
//			$PrevPageAction.="&offset=" ;
//                        $output .= $this->uw->UI_PageBar($_REQUEST['offset'], $matches, $PrevPageAction);
      Return '';
		}
    function checkurl(& $name){
      if ((strpos($name,'https://')=== false) and (strpos($name,'http://')=== false)) {
         $name = 'http://'.$name;
      }
    }

		function PurgeStats (){
			$result=$this->db->Query("delete from ".TABLE_STATS);
			$Content = $this->uw->UI_Message('moda_10');
			$Content .= $this->uw->UI_Navigate($_SERVER['PHP_SELF']."?cmd=".MODS_USE."&file=".MODULE_NAME);
			return $Content;
    }

  function doPageload() {
		$date_logged=date('c');
		$ip=$_SERVER['REMOTE_ADDR'];

		$browser  = $_SERVER['HTTP_USER_AGENT'];
    $REQUEST_URI = $_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];

		$referrer = getenv("HTTP_REFERER");
		if (isset( $_SERVER["HTTP_COOKIE"])){
		    $referrer = str_replace("&".$_SERVER["HTTP_COOKIE"],'',$referrer);
		    $referrer = str_replace("?".$_SERVER["HTTP_COOKIE"],'',$referrer);

		    $REQUEST_URI = str_replace("&".$_SERVER["HTTP_COOKIE"],'',$REQUEST_URI);
		    $REQUEST_URI = str_replace("?".$_SERVER["HTTP_COOKIE"],'',$REQUEST_URI);
		    }

		$sql="insert into userstats (userstatse_timestamp, userstats_ip, userstats_browser, userstats_referrer, userstats_server, userstats_request_uri) values (";
		$sql.=_esc($date_logged).", ";
		$sql.=_esc($ip).",";
		$sql.=_esc($browser).",";
		$sql.=_esc($referrer).",";
		$sql.=_esc(print_r($_SERVER,true)).","; //print_r($_SERVER,true)
		$sql.=_esc($REQUEST_URI);
		$sql.=")";
		ShopDB::Query($sql);
  }


}

?>