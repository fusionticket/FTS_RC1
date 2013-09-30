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
// Encryption connect to MySQL
// auto - automatic selection (set encoding table), cp1251 - windows-1251, etc.
define('CHARSET', 'auto');
// Limiting the size of the data for one get access to the database (in megabytes)
// Need to limit the amount of memory dump when the server ate very voluminous tables
define('LIMIT', 1);
define('C_DEFAULT', 1);
define('C_RESULT', 2);
define('C_ERROR', 3);
define('C_WARNING', 4);

if (!defined('ft_check')) {die('System intrusion ');}
require_once("admin/class.adminview.php");
require_once("classes/redundantdatachecker.php");

class UtilitiesView extends AdminView{
  var $tabitems = array( 0=> "orphan_tab|admin",
                         1=> "garbage_tab|admin",
                         2=> "emaillog_tab|admin",
                         3=> "backup_tab|admin");

	function garbage_list (){
    global $_SHOP;
		$this->list_head(con('garbage'),2);
		$stats= $this->stats();

		echo "<tr class='admin_list_row_0'>
		<td class='admin_list_item'>".con('event')."</td>
		<td class='admin_list_item' align='right'>".$stats['event']."</td></tr>";

		echo "<tr class='admin_list_row_1'>
		<td class='admin_list_item'>".con('seat')."</td>
		<td class='admin_list_item' align='right'>".$stats['seat']."</td></tr>";

		echo "<tr class='admin_list_row_0'>
		<td class='admin_list_item'>".con('order')."</td>
		<td class='admin_list_item' align='right'>".$stats['order']."</td></tr>";

		echo "<tr class='admin_list_row_1'>
		<td class='admin_list_item'>".con('unused_guests') ."</td>
		<td class='admin_list_item' align='right'>".$stats['guests']."</td></tr>";
	  echo "<tr  class='admin_value' ><td align='right' class='admin_value' colspan='2'>";
	    echo  $this->show_button("{$_SERVER['PHP_SELF']}?empty=true",'empty_trash',3);
	  echo "</td></tr></table></form><br>\n";

    $content = array();
	  if ($handle = opendir($_SHOP->tmp_dir))
	  {
	    while (false !== ($file = readdir($handle)))
	    {
	      if ($file != "." && $file != ".." && !is_dir($_SHOP->tmp_dir.$file) && preg_match("/^(.*?\w+).(.*?\w+).log\z/", $file, $matches))
	      { @ $content[$matches[1]] += 1;}
	    }
	    closedir($handle);
	  }

	  $this->list_head(con('garbage_logfiles'),2);
	  $zebra = '0'; $shown= false;
	  foreach($content as $file => $count){
	    if ($count>1) {
	      echo "<tr class='admin_list_row_{$zebra}'>
	      <td class='admin_list_item'>".$file.".*.log</td>
	      <td class='admin_list_item' align='right'>".$count."</td></tr>";
	      $zebra=($zebra=='0')?'1':'0';
	      $shown= true;
	    }
	  }
	  if (!$shown) {
	    echo "<tr class='admin_list_row_{$zebra}'>
	    <td colspan=2 class='admin_list_item'>".con('no_logfiles_find')."</td></tr>";

	  }
	  echo "<tr  class='admin_value' ><td align='right' class='admin_value' colspan='2'>";
	  echo  $this->show_button("{$_SERVER['PHP_SELF']}?empty=true",'empty_trash',3);
	  echo  $this->show_button("{$_SERVER['PHP_SELF']}?action=clearcache",'clear_cache',3);
	  echo "</td></tr></table></form><br>\n";

	  /*Lxsparks - check for orphan files */
		$files= array();
		foreach(new DirectoryIterator($_SHOP->files_dir) as $file) {
			$ext=end(explode('.',$file));
			if (strlen($ext) >1) {
				if (in_array($ext, $_SHOP->allowed_uploads)) {
				$files[]=basename($file);
				}
			}
		}

		$query ="select event_image AS file FROM Event WHERE event_image IS NOT NULL
			UNION
			select event_mp3 FROM Event WHERE event_mp3 IS NOT NULL
			UNION
			select ort_image FROM Ort WHERE ort_image IS NOT NULL
			UNION
			select pm_image FROM PlaceMap2 WHERE pm_image IS NOT NULL
			UNION
			select event_group_image FROM Event_group WHERE event_group_image IS NOT NULL";

		$db_files=array();
		if (!$result = ShopDB::query($query)) {
			addWarning(shopDB::error());
		} else {
	   while ($row = ShopDB::fetch_row($result)) {
				$db_files[]=$row[0];
			}
	   }

		$query ='select config_value FROM configuration WHERE config_field="organizer_logo"';
		$res=ShopDB::query($query);
		$row =ShopDB::fetch_assoc($res);
		$db_files[]=unserialize($row['config_value']);
		$diff=array_diff($files, $db_files);
		$_SESSION['oldfiles']=$diff;

	  $this->list_head(con('garbage_files'),1);
	  $zebra = '0'; $shown= false;
	  foreach($diff as $name){
	    if ($name) {
	      echo "<tr class='admin_list_row_{$zebra}'>
	      <td class='admin_list_item'>".$name."</td></tr>";
	      $zebra=($zebra=='0')?'1':'0';
	      $shown= true;
	    }
	  }
	  if (!$shown) {
	    echo "<tr class='admin_list_row_{$zebra}'>
	    <td colspan=2 class='admin_list_item'>".con('no_files_found')."</td></tr>";

	  }
	  echo "<tr  class='admin_value' ><td align='right' class='admin_value' colspan='2'>";
	  echo  $this->show_button("{$_SERVER['PHP_SELF']}?action=deletefiles",'delete_files',3);
	  echo "</td></tr></table></form><br>\n";

  }

  function orphan_list() {

    $data = Orphans::getlist($keys);

    $space = (count($keys)*60 < $this->width -200)?1:0;


		$this->list_head(con('record_orphan_test'),count($keys)+2+$space);
    print " <tr class='admin_list_header'>
              <th width=130 align='left'>
                Tablename
              </th>
              <th width=50 align='right'>
                ID
              </th>";
    foreach ($keys as $key) {
      print "<th width=60 align='center'> {$key}&nbsp;</th>";
    }
    if ($space) {
      print "<th align='center'>&nbsp;</th>";
    }

    print "</tr>";
    $alt =0;
    foreach ($data as $row) {

      print "<tr class='admin_list_row_$alt'>
        <td class='admin_list_item'>{$row['_table']}</td>
        <td class='admin_list_item' align='right'>{$row['_id']}</td>\n";
      foreach ($keys as $key) {
        print "<td align='center'>{$row[$key]}&nbsp;</td>\n";
      }
      if ($space) {
        print "<th align='center'>&nbsp;</th>";
      }
      print "</tr>";
      $alt = ($alt + 1) % 2;
    }
    print "</table>";
	}

	function emaillogTable() {
		global $_SHOP;

    $_REQUEST['page'] = is($_REQUEST['page'],1);
  //  $this->page_length = 2;
    $recstart = ($_REQUEST['page']-1)* $this->page_length;
		//echo $history,' => ',
    $query = "select SQL_CALC_FOUND_ROWS * from email_log
              order by el_timestamp desc
              limit {$recstart},{$this->page_length} ";

		if ( !$res = ShopDB::query($query) ) {
			return;
		}
    if(!$rowcount=ShopDB::query_one_row('SELECT FOUND_ROWS()', false)){return;}
		$alt = 0;
    echo "<table class='admin_list' border='0' width='".($this->width)."' cellspacing='1' cellpadding='2'>\n";
    echo "<tr><td class='admin_list_title' colspan='4' align='left'>" . con('email_log_list_title') . "</td>\n";
    echo "</tr>\n";
    print " <tr class='admin_list_header'>
              <th width=140 align='left'>".con('el_timestamp')." </th>
              <th align='left'>".con('el_action')."</th>
              <th align='left'>".con('el_email_to')." </th>
              <th align='left'>".con('el_failed')."</th>
              <th align='left' colspan='2' >".con('el_received')."</th>
              ";

		$alt = 0;

		while ( $row = shopDB::fetch_assoc($res) ) {
			$edate = formatAdminDate( $row["el_timestamp"], false );

      echo "<tr id='nameROW_{$row['el_id']}' class='admin_list_row_$alt' >";
			echo "<td class='admin_list_item' >{$row["el_timestamp"]}</td>\n";
      echo "<td  class='admin_list_item' >{$row["el_action"]}</td>\n";
      $email = $this->emailList( unserialize($row["el_email_to"]), true);
//      $email = $email[1].'&lt;'.$email[0].'&gt;';
      echo "<td  class='admin_list_item' >{$email}</td>\n";
      echo "<td  class='admin_list_item' >{$row["el_failed"]}</td>\n";
      echo "<td  class='admin_list_item' >{$row["el_received"]}</td>\n";
      echo "<td class='admin_list_item' width='18' align='right' nowarp=nowarp'><nowrap>".
        $this->show_button("{$_SERVER['PHP_SELF']}?action=el_view&el_id={$row['el_id']}","view",2);
      echo "</nowrap></td>\n";

			echo "</tr>\n\n";
			$alt = ( $alt + 1 ) % 2;
		}
		echo "</table>\n";
    $this->get_nav( $_REQUEST['page'], $rowcount[0]);
	}

  function emaillogView ($data) {
    global $_SHOP,  $_COUNTRY_LIST;

    $query = "select * from email_log
              where el_id = "._esc((int)$data['el_id']);

		if ( !$row = ShopDB::query_one_row($query) ) {
			return ;
		}
    echo "<table class='admin_form' width='".($this->width)."' cellspacing='1' cellpadding='4'>\n";
    $this->print_field('el_timestamp',$row);
    $this->print_field('el_action',$row);
    $this->print_field('el_email_to',$this->emailList( unserialize($row["el_email_to"])));
    $this->print_field("el_failed",  $row);
    $this->print_field("el_received",  $row);
    echo "<tr><td colspan='2' class='admin_name'>" .con('el_log'). "</td></tr>";
    echo "<tr><td colspan='2' class='admin_value' style='border:#cccccc 2px dashed;'>" .
         " <div style='overflow: auto; height: 150px; width:97%;padding:10px;'>".

          nl2br(htmlspecialchars($row['el_log'])) . "</div></td></tr>";
    echo "<tr><td colspan='2' class='admin_name'>" .con('el_bad_emails'). "</td></tr>";
    echo "<tr><td colspan='2' class='admin_value' style='border:#cccccc 2px dashed;'>" .
         " <div style='overflow: auto; height: 50px; width:97%;padding:10px;'>".
          nl2br(htmlspecialchars($row['el_bad_emails'])) . "&nbsp;</div></td></tr>";

    echo "<tr><td colspan='2' class='admin_name'>" .con('el_email_message'). "</td></tr>";
    echo "<tr><td colspan='2' class='admin_value' style='border:#cccccc 2px dashed;'>" .
         " <div style='overflow: auto; height: 250px; width:97%;padding:10px;'>".
          nl2br(htmlspecialchars($row['el_email_message'])) . "</div></td></tr>";
   	echo "</table>\n";
		echo "<br>".$this->show_button("{$_SERVER['PHP_SELF']}",'admin_list',3);
    return true;
	}

  function backupview($data) {
    global $_SHOP;
		$comp_levels = array('9' => '9 (maximum)', '8' => '8', '7' => '7', '6' => '6', '5' => '5 (average)', '4' => '4', '3' => '3', '2' => '2', '1' => '1 (minimum)','0' => 'Without compression');

		if (function_exists("bzopen")) {
		    $comp_methods[2] = 'BZip2';
		}
		if (function_exists("gzopen")) {
		    $comp_methods[1] = 'GZip';
		}
		$comp_methods[0] = 'Without compression';
		if (count($comp_methods) == 1) {
		    $comp_levels = array('0' =>'Without compression');
		}

    echo "<form method='POST' action='{$_SERVER['PHP_SELF']}'  autocomplete='off'>";
    $this->print_hidden('action','backup');
    $this->form_head(con('backup_title'));
    $this->print_select_assoc('comp_method',$data ,$err , $comp_methods );
    $this->print_select_assoc('comp_level',$data ,$err , $comp_levels );
    $this->form_foot();
  }

  function backupExec($data) {
    global $_SHOP;
		$this->comp_method  = isset($_POST['comp_method']) ? intval($_POST['comp_method']) : 0;
		$this->comp_level   = isset($_POST['comp_level']) ? intval($_POST['comp_level']) : 0;

    preg_match("/^(\d+)\.(\d+)\.(\d+)/", mysqli_get_server_info(ShopDB::$link), $m);
    $this->mysql_version = sprintf("%d%02d%02d", $m[1], $m[2], $m[3]);
    $db = $_SHOP->db_name;
    echo <<<HTML
        <SCRIPT>
        var WidthLocked = false;
        function s(st, so){
        	document.getElementById('st_tab').width = st ? st + '%' : '1';
        	document.getElementById('so_tab').width = so ? so + '%' : '1';
        }
        function l(str, color){
        	switch(color){
        		case 2: color = 'navy'; break;
        		case 3: color = 'red'; break;
        		case 4: color = 'maroon'; break;
        		default: color = 'black';
        	}
        	with(document.getElementById('logarea')){
        		if (!WidthLocked){
        			style.width = clientWidth;
        			WidthLocked = true;
        		}
        		str = '<FONT COLOR=' + color + '>' + str + '</FONT>';
        		innerHTML += innerHTML ? "<BR>\\n" + str : str;
        		scrollTop += 14;
        	}
        }
        </SCRIPT>
HTML;
    $this->form_head(con('backup_title'));
    echo <<<HTML
          <TR>
            <TD class='admin_value' COLSPAN=2>
              <DIV ID=logarea STYLE="width: 97%; height: 140px; border: 1px solid #7F9DB9; padding: 3px; overflow: auto;"></DIV>
            </TD>
          </TR>
          <TR>
            <TD class='admin_name' WIDTH=30%>Table status:</TD>
            <TD class='admin_value' >
              <TABLE WIDTH=100% BORDER=1 CELLPADDING=0 CELLSPACING=0>
                <TR><TD BGCOLOR=#FFFFFF>
                    <TABLE WIDTH=1 BORDER=0 CELLPADDING=0 CELLSPACING=0 BGCOLOR=#5555CC ID=st_tab STYLE="border-right: 1px solid #AAAAAA">
                      <TR><TD HEIGHT=12></TD></TR>
                    </TABLE>
                </TD></TR>
              </TABLE>
            </TD>
          </TR>
          <TR>
            <TD class='admin_name'>General status:</TD>
            <TD class='admin_value'>
              <TABLE WIDTH=100% BORDER=1 CELLSPACING=0 CELLPADDING=0>
                <TR><TD BGCOLOR=#FFFFFF>
                  <TABLE WIDTH=1 BORDER=0 CELLPADDING=0 CELLSPACING=0 BGCOLOR=#00AA00 ID=so_tab STYLE="border-right: 1px solid #AAAAAA">
                    <TR><TD HEIGHT=12></TD></TR>
                  </TABLE>
                </TD></TR>
              </TABLE>
            </TD>
          </TR>
          <tr  class='admin_value' align=right><td COLSPAN=2>
           	<A ID=save HREF='' STYLE='display: none;' target='_blank'>Download file</A> &nbsp; <INPUT ID=back TYPE=button VALUE='Back' DISABLED onClick="history.back();">
          </td></tr>
          </table>
HTML;

    $tables = ShopDB::TableList();
		$tabs = count($tables);
		// Determining the size of tables
		$result = ShopDB::query("SHOW TABLE STATUS");
		$tabinfo = array();
		$tab_charset = array();
		$tab_type = array();
		$tabinfo[0] = 0;
		$info = '';
		while($item = ShopDB::fetch_assoc($result)){
  		$item['Rows'] = empty($item['Rows']) ? 0 : $item['Rows'];
  		$tabinfo[0] += $item['Rows'];
  		$tabinfo[$item['Name']] = $item['Rows'];
  		$this->size += $item['Data_length'];
  		$tabsize[$item['Name']] = 1 + round(LIMIT * 1048576 / ($item['Avg_row_length'] + 1));
  		if($item['Rows']) $info .= "|" . $item['Rows'];
  		if (!empty($item['Collation']) && preg_match("/^([a-z0-9]+)_/i", $item['Collation'], $m)) {
  			$tab_charset[$item['Name']] = $m[1];
  		}
  		$tab_type[$item['Name']] = isset($item['Engine']) ? $item['Engine'] : $item['Type'];
		}
		$show = 10 + $tabinfo[0] / 50;
		$info = $tabinfo[0] . $info;
		$name = $db . '_' . date("Y-m-d_H-i");
    $fp = $this->fn_open($name, "w");
		$this->tpl_l("Creating file with the backup database:<BR>\\n  -  {$this->filename}");
//		$this->fn_write($fp, "#SKD101|{$db}|{$tabs}|" . date("Y.m.d H:i:s") ."|{$info}\n\n");
		$t=0;
		$this->tpl_l(str_repeat("-", 60));
		$result = ShopDb::query("SET SQL_QUOTE_SHOW_CREATE = 1");
		// Encryption connections by default
		if ($this->mysql_version > 40101 && CHARSET != 'auto') {
			ShopDB::query("SET NAMES '" . CHARSET . "'") or trigger_error ("Failed to change the connection charset.<BR>" . mysqli_error(), E_USER_ERROR);
			$last_charset = CHARSET;
		}	else{
			$last_charset = '';
		}
    foreach ($tables AS $table){
			// Puting an encoding compounds encoded table
			if ($this->mysql_version > 40101 && $tab_charset[$table] != $last_charset) {
				if (CHARSET == 'auto') {
					shopDB::query("SET NAMES '" . $tab_charset[$table] . "'") or trigger_error ("Failed to change the connection charset.<BR>" . mysqli_error(), E_USER_ERROR);
					$this->tpl_l("Established the connection charset `" . $tab_charset[$table] . "`.", C_WARNING);
					$last_charset = $tab_charset[$table];
				}
				else{
					$this->tpl_l('Charset of connection and table charset does not match:', C_ERROR);
					$this->tpl_l('Table `'. $table .'` -> ' . $tab_charset[$table] . ' (connection '  . CHARSET . ')', C_ERROR);
				}
			}
			$this->tpl_l("Proccessing Table `{$table}` [" . fn_int($tabinfo[$table]) . "].");
        	// Table creation
			$result = ShopDB::query("SHOW CREATE TABLE `{$table}`");
      $tab = ShopDB::fetch_array($result);
			$tab = preg_replace('/(default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP|DEFAULT CHARSET=\w+|COLLATE=\w+|character set \w+|collate \w+)/i', '/*!40101 \\1 */', $tab);
      $this->fn_write($fp, "DROP TABLE IF EXISTS `{$table}`;\n{$tab[1]};\n\n");
    	// To check whether it is necessary to dump data
    	// Determine the types of columns
      $NumericColumn = array();
      $result = ShopDB::query("SHOW COLUMNS FROM `{$table}`");
      $field = 0;
      while($col = ShopDB::fetch_row($result)) {
      	$NumericColumn[$field++] = preg_match("/^(\w*int|year)/", $col[1]) ? 1 : 0;
      }
			$fields = $field;
      $from = 0;
			$limit = $tabsize[$table];
			$limit2 = round($limit / 3);
			if ($tabinfo[$table] > 0) {
			if ($tabinfo[$table] > $limit2) {
			  $this->tpl_s(0, $t / $tabinfo[0]);
			}
			$i = 0;
			$this->fn_write($fp, "INSERT INTO `{$table}` VALUES");
      while(($result = ShopDB::query("SELECT * FROM `{$table}` LIMIT {$from}, {$limit}")) && ($total = ShopDB::num_rows($result))){
        while($row = ShopDB::fetch_row($result)) {
         	$i++;
    			$t++;
					for($k = 0; $k < $fields; $k++){
        		if ($NumericColumn[$k])
        		    $row[$k] = isset($row[$k]) ? $row[$k] : "NULL";
        		else
        			$row[$k] = isset($row[$k]) ? "'" . ShopDB::escape_string($row[$k]) . "'" : "NULL";
         	}

					$this->fn_write($fp, ($i == 1 ? "" : ",") . "\n(" . implode(", ", $row) . ")");
					if ($i % $limit2 == 0)
						$this->tpl_s($i / $tabinfo[$table], $t / $tabinfo[0]);
        }
				ShopDB::free_result($result);
				if ($total < $limit) {
				   break;
				}
    		$from += $limit;
      }

			$this->fn_write($fp, ";\n\n");
    	$this->tpl_s(1, $t / $tabinfo[0]);}
		}
		$this->tabs = $tabs;
		$this->records = $tabinfo[0];
		$this->comp = $this->SET['comp_method'] * 10 + $this->SET['comp_level'];
    $this->tpl_s(1, 1);
    $this->tpl_l(str_repeat("-", 60));
    $this->fn_close($fp);
		$this->tpl_l("Backup of DB `{$db}` is created.", C_RESULT);
		$this->tpl_l("DB size:       " . round($this->size / 1048576, 2) . " Mb", C_RESULT);
		$filesize = round(filesize($_SHOP->files_dir. $this->filename) / 1048576, 2) . " Mb";
		$this->tpl_l("File size: {$filesize}", C_RESULT);
		$this->tpl_l("Tables processed: {$tabs}", C_RESULT);
		$this->tpl_l("Lines processed:   " . fn_int($tabinfo[0]), C_RESULT);
  //  echo $_SHOP->files_dir;
		echo "<SCRIPT>with (document.getElementById('save')) {style.display = ''; innerHTML = 'Download file ({$filesize})'; href = '".$_SHOP->root_base .'files/' . $this->filename . "'; }document.getElementById('back').disabled = 0;</SCRIPT>";
    return true;
  }

  function clearlogfiles(){
    global $_SHOP;
    $content = array();
    if ($handle = opendir($_SHOP->tmp_dir))
    {
      while (false !== ($file = readdir($handle)))
      {
        if ($file != "." && $file != ".." && !is_dir($dir.$file) && preg_match("/^(.*?\w+).(.*?\w+).log\z/", $file, $matches))
        { $content[$matches[1]][] = $file;}
      }
      closedir($handle);
    }
    foreach($content as $file => $count){
      if (count($count)>1) {
        foreach($count as $file){
          @ unlink($_SHOP->tmp_dir.$file);
        }

      }
    }
    return true;
  }


  function clearcache(){
    global $_SHOP;
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($_SHOP->tmp_dir),
                                              RecursiveIteratorIterator::CHILD_FIRST);
    foreach ($iterator as $path) {
      if (!$path->isDir()) {
        unlink($path->__toString());
      }
    }
    //    rmdir($dir);
    return false;
  }

	function draw () {
		global $_SHOP;

    if(isset($_GET['fix'])){
      Orphans::dofix($_GET['fix']);
    } elseif(isset($_GET['empty'])){
			$this->empty_trash();
		} elseif ($_GET['action']=='el_view') {
      if ($this->emaillogView($_GET )) return;
    }elseif ($_GET['action']=='backup') {
      if ($this->backupExec($_POST )) return;
    } elseif($_GET['action']=='clearcache') {
      if ($this->clearcache()) return;
    } elseif($_GET['action']=='deletefiles' && (isset($_SESSION['oldfiles']))) {
      if ($this->deletefiles()) return;
    }

	  $tab = $this->drawtabs();
	  if (! $tab) { return; }
	  switch ($tab-1){
	    case 0:
         $this->orphan_list($_POST);
         break;

      case 1:
         $this->garbage_list($_POST);
         break;

      case 2:
         $this->emaillogTable($_POST);
         break;

      case 3:
     //    $this->barcodeForm($_POST);
         $this->backupview($_POST);
         break;
      default:
  	    plugin::call('is'.get_class($this).'_Draw', $tab-1, $this);
    }
	}

  function emailList( $emails) {
    $names = array();
    foreach($emails as $key => $name) {
      $name = (empty($name))?$key:$name;
      $names[] = "<span title={$key}>{$name}</span>";
    }
    return implode(', ', $names);
  }
	function stats(){
	  global $_SHOP;

		$res=array('event'=>0,'seat'=>0,'order'=>0);

		$query="select count(event_id) as count
						from Event
						where event_status='trash'";

		if($data=ShopDB::query_one_row($query)){
		  $res['event']=$data['count'];
		}

		$query="select count(seat_id) as count
						from Seat
						where seat_status='trash'";

		if($data=ShopDB::query_one_row($query)){
		  $res['seat']=$data['count'];
		}

				$query="select count(order_id) as count
						from `Order`
						where order_status='trash'";

		if($data=ShopDB::query_one_row($query)){
		  $res['order']=$data['count'];
		}
		$res['guests']= User::cleanup();

		return $res;

	}

	function empty_trash(){
	  Order::toTrash();
		Event::emptyTrash();
		Order::emptyTrash();
		User::cleanup(0,true);
	}

	function deletefiles() {
	global $_SHOP;
		foreach ($_SESSION['oldfiles'] as $file){
			if (file_exists($_SHOP->files_dir.$file)) {
				unlink ($_SHOP->files_dir.$file);
			}
		}
	unset($_SESSION['oldfiles']);
	return;
	}

  function fn_open($name, $mode){
    global $_SHOP;
		if ($this->comp_method == 2) {
			$this->filename = "{$name}.sql.bz2";
		    return bzopen($_SHOP->files_dir. $this->filename, "{$mode}b{$this->comp_level}");
		} elseif ($this->comp_method == 1) {
			$this->filename = "{$name}.sql.gz";
		    return gzopen($_SHOP->files_dir . $this->filename, "{$mode}b{$this->comp_level}");
		} else{
			$this->filename = "{$name}.sql";
			return fopen($_SHOP->files_dir . $this->filename, "{$mode}b");
		}
	}

	function fn_write($fp, $str){
		if ($this->comp_method == 2) {
		    bzwrite($fp, $str);
		} elseif ($this->comp_method == 1) {
		    gzwrite($fp, $str);
		}	else{
			fwrite($fp, $str);
		}
	}

	function fn_close($fp){
		if ($this->comp_method == 2) {
		    bzclose($fp);
		}	elseif ($this->comp_method == 1) {
		    gzclose($fp);
		}	else {
			fclose($fp);
		}
		@chmod($_SHOP->files_dir . $this->filename, 0666);
  }

  function tpl_l($str, $color = C_DEFAULT){
    $str = preg_replace("/\s{2}/", " &nbsp;", $str);
    echo "<SCRIPT>l('{$str}', $color);</SCRIPT>";
  }

  function tpl_enableBack(){
    echo "<SCRIPT>document.getElementById('back').disabled = 0;</SCRIPT>";
  }

  function tpl_s($st, $so){
    $st = round($st * 100);
    $st = $st > 100 ? 100 : $st;
    $so = round($so * 100);
    $so = $so > 100 ? 100 : $so;
    echo "<SCRIPT>s({$st},{$so});</SCRIPT>";
  }
}
function fn_int($num){
	return number_format($num, 0, ',', ' ');
}

?>