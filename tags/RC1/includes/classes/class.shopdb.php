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
define("DB_DEADLOCK", 1213);

class ShopDB {
    static $prefix = '';
    static $link = null;
    static $db_trx_started = 0;
    // /
    // new SQLi extenstions
    static function getport(& $DB_Hostname) {
      $pos = strpos($DB_Hostname,':');
      if ($pos!= false) {
        $port = substr($DB_Hostname,$pos+1);
        $DB_Hostname = substr($DB_Hostname,0, $pos);
      } else {
        $port = 3306;
      }
    }

    static function init ($canDie=true) {
        global $_SHOP;
        unset($_SHOP->db_errno);
        unset($_SHOP->db_error);

      //  trace("Database Init\n=============================================", true);

        if (!isset(ShopDB::$link)) {
          if (isset($_SHOP->db_name)) {
            $DB_Hostname = $_SHOP->db_host;
            $port = self::getport($DB_Hostname);
            $link = new mysqli($DB_Hostname, $_SHOP->db_uname, $_SHOP->db_pass, '', $port);

            $link->select_db($_SHOP->db_name);
            /*
             * This is the "official" OO way to do it,
             * BUT $connect_error was broken until PHP 5.2.9 and 5.3.0.
             */
            if ($link->connect_error) {
              $_SHOP->db_errno = $link->connect_errno;
              $_SHOP->db_error = 'Connect Error (' . $link->connect_errno . ') ' . $link->connect_error;
            } elseif (mysqli_connect_error()) {
              // Use this instead of $connect_error if you need to ensure
              // compatibility with PHP versions prior to 5.2.9 and 5.3.0.
              $_SHOP->db_errno =  mysqli_connect_errno();
              $_SHOP->db_error = 'Connect Error (' . mysqli_connect_errno() . ') ' . mysqli_connect_error();
            }
            if (isset($_SHOP->db_error))
              if ($canDie){
                die($_SHOP->db_error);
              } else {
                return false;
              }

            ShopDB::$link = $link;

            if (strpos(constant('CURRENT_VERSION'),'svn') !== false) {
              ShopDB::checkdatabase(true, false);
            }

            //Set Session Time Zone.
            //This does not work:
           @ ShopDB::query("SET time_zone = '".date('P')."'");
            if (!empty($_SHOP->useUTF8)){
                ShopDB::query("SET NAMES utf8");
                ShopDB::query('set character set utf8 ');
            }

            return true;
          } elseif ($canDie) {
             echo 'db init - ';
             Print_r($_SHOP);
             die ("No connection settings");
          } else {
            Print_r(debug_backtrace());die ("No connection settings");
            return false;
          }
        }
        return true;
    }

    static function close(){
      if (isset(ShopDB::$link)) {
        if ( ShopDB::$link->close()){
          ShopDB::$link = null;
          return true;
        }
      }
      return false;
    }

    static function GetServerInfo () {
      if (!ShopDB::$link) {
         self::init();
      }
      return mysqli_get_server_info(ShopDB::$link);
    }

    static function begin ($name='') {
        global $_SHOP;
        unset($_SHOP->db_errno);
        unset($_SHOP->db_error);
        if (self::$db_trx_started===0) {
            if (!ShopDB::$link) {
                self::init();
            }
            if (ShopDB::query('START TRANSACTION')) { //$link->autocommit(false)
                self::$db_trx_started = 1;
                trace("[Begin {$name}]");
                return true;
            } else {
                $_SHOP->db_error= mysqli_error(ShopDB::$link);
                self::dblogging("[Begin {$name}]Error: $_SHOP->db_error");
                user_error($_SHOP->db_error);
                return false;
            }
        } else {
            self::$db_trx_started++;
            trace("[Begin {$name}] ".self::$db_trx_started);
            return true;
        }
    }

    static function commit ($name='', $retaining = false)
    {
      global $_SHOP;
        if (($retaining && self::isTxn()) || self::$db_trx_started==1) {
          unset($_SHOP->db_errno);
          unset($_SHOP->db_error);
          if (ShopDB::$link->commit()) {
            if (!$retaining){
              ShopDB::$link->autocommit(true);
              self::$db_trx_started = 0;
              trace("[Commit {$name}]");
            } else {
              trace("[Commitremaining {$name}]");
            }
            return true;
          } else {
            user_error($_SHOP->db_error= ShopDB::$link->error);
            self::dblogging("[Commit {$name}]Error: $_SHOP->db_error");
            self::Rollback($name);
            return false;
          }
        } elseif (self::$db_trx_started > 1) {
          trace("[Commit {$name}] ".self::$db_trx_started);
          self::$db_trx_started--;
          return true;
        } else {
          trace("[Commit {$name}] - no transaction");
          return true;
        }
    }
    static function rollback ($name='')
    {
        global $_SHOP;
        if (self::$db_trx_started) {
            unset($_SHOP->db_errno);
            unset($_SHOP->db_error);
            if (ShopDB::$link->rollback()) {
                ShopDB::$link->autocommit(true);
                self::dblogging("[Rollback {$name}] ".self::$db_trx_started);
//                trace("[Rollback {$name}] ".self::$db_trx_started);
                self::$db_trx_started= 0;
                return true;
            } else {
                user_error($_SHOP->db_error= ShopDB::$link->error);
                self::dblogging("[rollback {$name}]Error: $_SHOP->db_error");
//                trace("[rollback {$name}]Error: $_SHOP->db_error");
            }
        }  else {
//            self::dblogging("[Rollback {$name}] no transaction");
//            trace("[Rollback {$name}] no transaction");
        }
    }

    /**
     * ShopDB::isTxn()
     *
     * Shorthand of ShopDB::isTransaction();
     *
     * @return bool, true if transaction has begun.
     */
    static function isTxn(){
      return self::isTransaction();
    }

    /**
     * ShopDB::isTransaction()
     *
     * @return bool, Is true if a transaction has started.
     */
    static function isTransaction(){
     // print_r(self::$db_trx_started);
      return self::$db_trx_started >0;
    }


    static function query($query)
    {
        global $_SHOP;
        // echo  "QUERY: $query <br>";
        if (!isset(ShopDB::$link)) {
            self::init();
        }
		// Optionally allow extra args which are escaped and inserted in place of ?
  			if(func_num_args() > 1) {
  				$args = func_get_args();
  				foreach($args as &$item)
  					$item = ShopDB::quote($item);
  				$query = vsprintf(str_replace('?', '%s', $query), array_slice($args, 1));
  			}
        unset($_SHOP->db_errno);
        unset($_SHOP->db_error);
      //  $query = ShopDB::replacePrefix($query);
        $res = ShopDB::$link->query($query);

        if (!is($_SHOP->skiptrace, false)) {
          trace($query, false, true);
        }
        if (!$res) {
            $_SHOP->db_errno  = ShopDB::$link->errno ;
            $_SHOP->db_error  = mysqli_error(ShopDB::$link);
            $traceArr = debug_backtrace();
            $err = "[Error: {$_SHOP->db_errno}] ";
            if(isset($traceArr) && count($traceArr) > 2) {
              //print_r($traceArr);
              $errString = "$err".basename($traceArr[1]['file']).' '.
                           $traceArr[1]['class'].$traceArr[1]['type'].$traceArr[1]['function'].' ('.$traceArr[1]['line'].')';
              self::dblogging($errString);

              $err = "";
            }
            self::dblogging("$err".$_SHOP->db_error);
            self::dblogging($query);
            if ($_SHOP->db_errno == DB_DEADLOCK) {
                self::$db_trx_started = 0;
            }
        } elseif (is_object($res)) {

          //TODO: Check this line - Warning: Attempt to assign property of non-object classes\ShopDB.php
          $res->query = $query;
        }
        $_SHOP->skiptrace = false;
        return $res;
    }

  static function query_one_row ($query, $assoc = true) {
    $assoc = ($assoc)? MYSQLI_ASSOC:MYSQLI_NUM;
    if ($result = self::query($query) and $row = $result->fetch_array($assoc)) {
      return $row;
    }
  }

  static function query_one_object ($query) {
//    $assoc = ($assoc)? MYSQLI_ASSOC:MYSQLI_NUM;
    if ($result = self::query($query) and $row = $result->fetch_object()) {
      return $row;
    }
  }

  static function insert_id() {
        global $_SHOP;
        if (!ShopDB::$link) {
          self::init();
        }
        return ShopDB::$link->insert_id;
    }

    static function lock ($name, $time = 30) {
        $query_lock = "SELECT GET_LOCK('SHOP_$name','$time')";
        if ($res = self::query($query_lock) and $row = $res->fetch_array()) {
            return $row[0];
        }
    }

    static function unlock ($name) {
        $query_lock = "SELECT RELEASE_LOCK('SHOP_$name')";
        self::query($query_lock);
    }

    static function affected_rows() {

      global $_SHOP;
        if (!isset(ShopDB::$link)) {
            self::init();
        }
        return ShopDB::$link->affected_rows;
    }

    static function fetch_array($result) {
      if ($result)
        return $result->fetch_array();
    }

    static function fetch_assoc($result) {
      if ($result)
        return $result->fetch_assoc();
    }

    static function fetch_object($result) {
      if ($result)
        return $result->fetch_object();
    }

    static function fetch_row($result) {
      if ($result)
        return $result->fetch_row()  ;
    }

    static function num_rows($result) {
      if ($result)
        return $result->num_rows ;
    }

    static function foundRows(){
      $query='SELECT FOUND_ROWS();';
      if($row = ShopDB::query_one_row($query, false)){
        return (INT) $row[0];
      }
      return 0;
    }

    static function error() {
      global $_SHOP;
      return $_SHOP->db_error;
    }

    static function errno() {
      global $_SHOP;
      return $_SHOP->db_errno;
    }

    static function quote ($s, $quote=true) {
      $str = self::escape_string($s);
      return (!isset($s) or is_null($s)) ? 'NULL' : (($quote && !is_numeric($s))?"'".$str."'":$str);
    }


		static function quoteParam($var) { return self::quote($_REQUEST[$var]); }

    static function escape_string($escapestr) {
      // magic_quotes will be checked in the init.php procedure.
//      if (!get_magic_quotes_gpc ()) {
        if (!isset(ShopDB::$link)) {
          self::init();
        }
        if (is_array($escapestr) || is_object($escapestr)) {
          print_r($escapestr);
        }
        return ShopDB::$link->real_escape_string($escapestr);
//      } else { //echo "get_magic_quotes_gpc<br>\n";
//        die ("<b><font color='red'>fusion ticket can only work with magic_quotes_gpc turned off</font></b>");
//      }
    }

    static function free_Result($result) {
      if (isset(ShopDB::$link) and isset($result)) {
        $result->free;
      }
    }

    static function tblclose($result) {
      if (isset(ShopDB::$link) and isset($result)) {
        $result->close();
      }
    }

    /**
     * This function replaces a string identifier <var>$prefix</var> with the
     * string held is the <var>_table_prefix</var> class variable.
     *
     * @access public
     * @param string The SQL query
     * @param string The common table prefix
     */
    static function replacePrefix( $sql, $prefix='#__' ) {
      $sql = trim( $sql );

      $escaped = false;
      $quoteChar = '';

      $n = strlen( $sql );

      $startPos = 0;
      $literal = '';
      while ($startPos < $n) {
        $ip = strpos($sql, $prefix, $startPos);
        if ($ip === false) {
          break;
        }

        $j = strpos( $sql, "'", $startPos );
        $k = strpos( $sql, '"', $startPos );
        if (($k !== FALSE) && (($k < $j) || ($j === FALSE))) {
          $quoteChar	= '"';
          $j			= $k;
        } else {
          $quoteChar	= "'";
        }

        if ($j === false) {
          $j = $n;
        }

        $literal .= str_replace( $prefix, shopDB::$prefix,substr( $sql, $startPos, $j - $startPos ) );
        $startPos = $j;

        $j = $startPos + 1;

        if ($j >= $n) {
          break;
        }

        // quote comes first, find end of quote
        while (TRUE) {
          $k = strpos( $sql, $quoteChar, $j );
          $escaped = false;
          if ($k === false) {
            break;
          }
          $l = $k - 1;
          while ($l >= 0 && $sql{$l} == '\\') {
            $l--;
            $escaped = !$escaped;
          }
          if ($escaped) {
            $j	= $k+1;
            continue;
          }
          break;
        }
        if ($k === FALSE) {
          // error in the query - no end quote; ignore it
          break;
        }
        $literal .= substr( $sql, $startPos, $k - $startPos + 1 );
        $startPos = $k+1;
      }
      if ($startPos < $n) {
        $literal .= substr( $sql, $startPos, $n - $startPos );
      }
      return $literal;
    }

  	//function to find the number of fields in a recordSet
  	static function fieldCount($result) {
  		return $result->field_count;
  	}

  	//function to find the field flags in a recordSet
  	static function fieldFlags($result,$i) {
  		$fld_array = $result->fetch_field_direct($i);
  		if($fld_array->flags & 2)
  			return "primary_key";
  		else
  			return "";
  	}

  	//function to find the field name from recordSet
  	static function fieldName($result,$i) {
  		$fld_array = $result->fetch_field_direct($i);
  		return $fld_array->orgname;
  	}

  	//function to find the alias field name from recordSet
  	static function aliasFieldname($result,$i) {
  		$fld_array = $result->fetch_field_direct($i);
  		return $fld_array->name;
  	}

  	//function to find the table of a field name from recordSet
  	static function fieldTable($result,$i) {
  		$fld_array = $result->fetch_field_direct($i);
  		return $fld_array->orgtable;
  	}

    /*
    *
    * Checks if a given table exists in the active database . Returns true if the table exists, false otherwise .
    *
    * @access private
    * @return boolean
    * @param string $tablename The table name to check for
    **/

    static function FieldList ($TableName, $prefix = '') {
        $Fields = array ();

        $result = self::Query("SHOW COLUMNS FROM `$TableName`" . ((!empty($prefix))?" LIKE '$prefix%'":""));

        if (!$result) {
            return false;
        } while ($row = self::fetch_row($result)) {
            $Fields[] = $row[0];
        }

        $result->Free();

        Return $Fields;
    }

    static function FieldListExt ($TableName, $prefix = '') {
        $Fields = array ();

        $result = self::Query("SHOW COLUMNS FROM `$TableName`" . ((!empty($prefix))?" LIKE '$prefix%'":""));

        if (!$result) {
            return $Fields;
        }
        while ($row = self::fetch_object($result)) {
          $field =$row->Field;
          unset($row->Field);
          $Fields[$field] = $row;
        }

     //   $result->Free;

        Return $Fields;
    }

    static function FieldExists ($tablename, $Fieldname) {
        $Fields = self::FieldList ($tablename);

        if (($tables) && in_array($Fieldname, $Fields)) {
            return true;
        }else {
            return false;
        }
    }

    static function TableList ($prefix = ''){
        $tables = array ();
        $result = self::Query("SHOW TABLE" . ((!empty($prefix))?" status where lower(name)=lower('$prefix')":"s"));
        if (!$result) {
            return false;
        }
        while ($row = self::fetch_row($result)) {
            $tables[] = $row[0];
        }
     //   $result->Free;
     //  print_r($tables);
        Return $tables;
    }

    static function TableExists ($tablename) {
        $tables = self::TableList ();
        if (($tables) && (in_array($tablename, $tables)) || in_array( strtolower($tablename), $tables)) {
            return true;
        }else {
            return false;
        }
    }

    static function dblogging($debug) {
        global $_SHOP;
        trace($debug);
        $handle=@fopen($_SHOP->tmp_dir."shopdb.".date('Y-m-d') .".log","a");
        @fwrite($handle, date('c',time()).' '. $debug."\n");
        @fclose($handle);
        /*
        require_once("classes/class.restservice.client.php");
        try{
          $rsc = new RestServiceClient('http://cpanel.fusionticket.org/reports/querys.xml'); //cpanel.fusionticket.org
          $rsc->subject  = "{$_SHOP->db_errno}: {$_SHOP->db_error}";
          $rsc->data     =  gzcompress($debug);
          $rsc->excuteRequest();
        }catch(Exception $e){
          print_r($e->getMessage());
        }
        */
    }

    static function checkdatabase($update=false, $viewonly=false){
      global $_SHOP;
      $trace = $_SHOP->trace_on;
      $_SHOP->trace_on=false;
      $logfile = $_SHOP->tmp_dir.'databasehist.log';
      $dbstructfile = INC.'install'.DS.'data_db.php';
      if (!$update and file_exists($_SHOP->tmp_dir.'databasehist.log')) {
        $update = filemtime($logfile) < filemtime($dbstructfile);
      } else {
        $update = true;
      }

      if ($update) {
        require($dbstructfile);
        $tbls = plugin::getTables($tbls);
        if ($errors = ShopDB::DatabaseUpgrade($tbls, true, $viewonly)) {
          $handle=fopen($logfile,"a");
          fwrite($handle, date('c',time()).": \n". print_r($errors,true). "\n");
          fclose($handle);
        }
        $result ='';
        if (is_array($errors)) {
          foreach ($errors as $data) {
            $result .= $data['changes'];
            if ($data['error']) $result .= $data['error'];
          }
        }
        If ($result) {
          require_once('admin'.DS.'class.adminview.php');
          echo "
<style type='text/Css'>
.admin_name{
    background-color:#ededed;
    color:#999999;
    font-size: 12px;
    font-weight:bold;}
.admin_value{background-color:#fafafa}
.admin_form{border: #cccccc 1px solid;}
admin_list_title{font-size:16px; font-weight:bold;color:#555555;}

</style>";

          echo "<center>";
          AdminView::form_head(con('Update database structure'),'800',1);
          echo "<tr><td class='admin_value'>".nl2br(str_replace(' ','&nbsp;',$result));
          echo "</td></tr><tr><td align='center' class='admin_value'><form action='index.php'>
            <input type='submit' name='submit' value='Reload Page'></form></td></tr>";
          echo "</table>\n";
          die();
        }
      }
      $_SHOP->trace_on = $trace;
    }



    /*

    * $DB_Struction needs to be a array with the tablename as key and a second array with fields/index's
    * like: array(name, $definition)
    *   => array('ID', 'int(11) NOT NULL auto_increment');
    *
    */
    private static function TableCreateData( $tablename ) {
      $result = self::query_one_row('SHOW CREATE TABLE ' ."`$tablename`");
      $tables = ($result)? $result['Create Table']:'';
      unset($result);
      $keys = null;
      if ($tables) {
        $keys = array ( 'keys'=>array(),'fields'=>array(), 'engine'=>'');
        // echo "<pre>$tables</pre><br>\n ";
        // Convert end of line chars to one that we want (note that MySQL doesn't return query it will accept in all cases)
        if (strpos($tables, "(\r\n ")) {
            $tables = str_replace("\r\n", "\n", $tables);
        } elseif (strpos($tables, "(\r ")) {
            $tables = str_replace("\r", "\n", $tables);
        }
        $tables = str_replace(" default ", " DEFAULT ", $tables);
        $tables = str_replace(" auto_increment", " AUTO_INCREMENT", $tables);
        $tables = str_replace(" on update ", " ON UPDATE ", $tables);

        // Split the query into lines, so we can easily handle it. We know lines are separated by $crlf (done few lines above).
        $sql_lines = explode("\n", $tables);
        $sql_count = count($sql_lines);
        // lets find first line with constraints
        for ($i = 1; $i < $sql_count; $i++) {
           $sql_line = trim($sql_lines[$i]);
           if (substr($sql_line,-1) ==',') $sql_line = substr($sql_line,0,-1);
           if (preg_match('/^[\s]*(CONSTRAINT|FOREIGN|PRIMARY|UNIQUE)*[\s]*(KEY)+/', ' '.$sql_line)) {
             $keys['keys'][] = str_replace('  ',' ',$sql_line);
           } else if (preg_match('/(ENGINE=)(?P<name>\w+) /i', $sql_line, $matches)) {
             $keys['engine'] = $matches[2];
           } else {
             $x = strpos( $sql_line,' ');
             $key = substr($sql_line,0,$x);
             if (strpos("`'\"", substr($key,0,1)) !== false) {
               $key = substr($key,1,-1);
             }
             $keys['fields'][$key] = substr($sql_line,$x);
           }
        }
      }// print_r($keys);
      Return $keys;
    }

    static function DatabaseUpgrade($Struction, $logall =false, $viewonly=false, $collation='utf8_general_ci') {
      $error = '';  $returning = array();
      foreach ($Struction as $tablename => $fields) {
        $update = false; $datainfo = ''; $error='';
        if (is_string($fields)) {
          if (count(ShopDB::Tablelist ($tablename)) > 0 && !ShopDB::TableExists ($fields)){
            $datainfo .= "Rename table $tablename to $fields.\n";
            $update = true;
            // We have to change to a tmp table name as when changing case on the same table
            //we cant actualy change from Admin to admin as the table name check performed
            //by MySQL thinks there the same thing and ERRORS.
            //So change the name to temp and back to the correct one.
            $sql = "RENAME TABLE `$tablename` TO  `__tmp_$fields`,
                                 `__tmp_$fields` TO `$fields`"; // The MySQL way.
            //$sql = "ALTER TABLE `$tablename` rename to `$fields`;";
          }

        } elseif ($tblFields = self::TableCreateData($tablename)) {
          if (!isset($fields['engine'])) $fields['engine'] = $tblFields['engine'];
          $sql = "";
          $oldkey = '';
          $primary ='';
          $txt = '';
          if (isset($fields['renamefield'])) {
            foreach ($fields['renamefield'] as $key => $info) {
              if (stripos($info,'AUTO_INCREMENT') !== false) $primary = $key;
              if (array_key_exists($key, $tblFields['fields'])) {
                  $datainfo .= "Change $tablename.$key into $info\n";
                  $update = true;
                  $sql .= ", CHANGE `{$key}` `{$info}` {$tblFields['fields'][$key]}";
              }
            }
          }
          foreach ($fields['fields'] as $key => $info) {
            if (stripos($info,'AUTO_INCREMENT') !== false) $primary = $key;
            if (!array_key_exists($key, $tblFields['fields'])) {
                $datainfo .= "Add $tablename.$key $info\n";
                $update = true;
                $sql .= ', ADD `' . $key . "` " . $info;
                $sql .= (($oldkey == '')?' FIRST':' AFTER ' . $oldkey)."\n";
            } elseif ((trim($info)) != (trim($tblFields['fields'][$key]))) {
                $datainfo .= "mod $tablename.$key:\n     {".$tblFields['fields'][$key]."}\n     {".$info."}\n";
                $update = true;
                $sql .= ', MODIFY `' . $key . "` " . $info."\n";
            }
            $oldkey = $key;
          }
          if (isset( $fields['remove'])) {
            foreach ($fields['remove'] as $key) {
              if (array_key_exists($key, $tblFields['fields'])) {
                  $datainfo .= "del $tablename.$key:\n";
                  $sql .= ', DROP COLUMN `' . $key . "`\n";
                  $update = true;
                  unset($tblFields['fields'][$key]);
              }
            }
          }

          foreach ($tblFields['fields'] as $key => $info) {
            if (!array_key_exists($key, $fields['fields'])) {
                $datainfo .= "Missing in $tablename: ".$key. $tblFields['fields'][$key].".\n";
            }
          }

          If ((isset($fields['key']) && count($fields['key']) > 0)) {
            foreach ($fields['key'] as $info){
              if (substr($info,0,1)!=='P'){
                  $sql .= ', ADD ' . $info."\n";
                  if (!in_array($info, $tblFields['keys'])) $update = true;
              } elseif (!in_array($info, $tblFields['keys'])) {
                  $sql .= ', ADD ' . $info."\n";
                  $update = true;
              } elseif ( stripos($info,"`$primary`")===false ) {
                  $sql .= ', ADD ' . $info."\n";
              }
            }
          }
          if ($fields['engine'] <> $tblFields['engine'] ) {
            $datainfo .= "mod $tablename enigne from {$tblFields['engine']} to ".$fields['engine']."\n";
            $sql .= ', ENGINE = '.$fields['engine'] ."\n";
            $update = true;
          }
          If ((isset($fields['key']) and isset($tblFields['key']) and
              count($fields['key']) <> count($tblFields['keys'])) or
              ($fields['engine'] <> $tblFields['engine'] )) $update = true;

          $sql = "ALTER TABLE `$tablename` " . substr($sql, 2);
          If ($update) {
            $sql1 ='';
            $datainfo .=  $tablename.': db-'. print_r($tblFields['keys'], true).' inst-'.print_r($fields['key'], true);
            If ((isset($tblFields['keys'])) and (count($tblFields['keys']) > 0)) {
              foreach ($tblFields['keys'] as  $info) {
                if (substr($info,0,1)!=='P') {
                  $sql1 .= ', DROP '.str_replace('UNIQUE','', substr(trim($info),0,strpos($info,'(')-1))."\n";
                } elseif ( stripos($info,"`$primary`")===false ) {
                    $sql1 .= ', DROP PRIMARY KEY'."\n";
                }
              }
            }
            If (!empty($sql1)){
              $result = self::query($sql1 = "ALTER TABLE `$tablename` " . substr($sql1, 2));
              if (!$result) {
                $error .= '$sql1\n<B>' .self::error ().".</b>\n\n";
              }
            }
          }

        } else {
          $update = true;
          $sql = '';
          $datainfo .= "Create table $tablename.";
          foreach ($fields['fields'] as $key => $info) {
             $sql .= ", `" . $key . "` " . $info."\n";
          }
          If ((isset($fields['key'])) and (count($fields['key']) > 0))
              foreach ($fields['key'] as $info) $sql .= ', ' . $info."\n";
          $sql = "CREATE TABLE `$tablename` (" . substr($sql, 2) . ")";
          if ($fields['engine']) $sql .= ' ENGINE='.$fields['engine']."\n";
          $sql .= "COLLATE = {$collation}\n";
        }
        If ($update) {

          If (!$viewonly) {
            $result = self::query($sql);
            if (!$result) {
              $error .= $sql."\n".self::error ()."\n\n";
            }
          }
          $returning[] = array( 'changes' => $datainfo, 'error' => $error);

        }
      }
    //      self::Upgrade_Autoincrements();
    //      self::dblogging("[SQLupdate:] Finnish \n");
      return $returning;
    }

    static function Upgrade_Autoincrements(){
        $error = '';
        $Struction = self::TableList();

        foreach ($Struction as $tablename ) {
            $keys = self::query_one_row("Show index from ".$tablename);
            if (isset($keys) and ($keys['Key_name'] === "PRIMARY")) {
              $Value = self::query_one_row("select max(".$keys['Column_name'].") from ".$tablename);
              self::query("alter table ".$tablename." auto_increment=".$value[$keys['Column_name']]);
            }
        }
        return $error;
    }
}
?>