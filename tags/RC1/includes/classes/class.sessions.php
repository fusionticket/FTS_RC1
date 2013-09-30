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

$session_class = new Session;
session_set_save_handler(array(&$session_class, '_open'),
                         array(&$session_class, '_close'),
                         array(&$session_class, '_read'),
                         array(&$session_class, '_write'),
                         array(&$session_class, '_destroy'),
                         array(&$session_class, '_clean'));

class Session  {
  protected $isnew= true;
  function _open() {
    return true;
  }

  function _close() {
    return $this->_clean(ini_get('session.gc_maxlifetime'));
  }

  function _read($id) {
    $sql = "SELECT session_data FROM sessions WHERE session_id = ". _esc($id);
    if ($result = ShopDB::query_one_row($sql)){
        $this->isnew= false;
        return $result['session_data'];
    }
    return '';
  }

  function _write($id, $data) {
    $access = time();
 //   if ($this->isnew) {
      $sql = "REPLACE INTO sessions (session_id, session_access, session_data)
              VALUES ("._esc($id).", "._esc($access).", ". _esc($data).")";
//    } else {
 //     $sql = "update sessions set
 //               session_access = "._esc($access).",
 //               session_data   = ". _esc($data)."
 //             where session_id = "._esc($id) ;
 //   }
    $this->isnew = false;
    return ShopDB::query($sql);
  }

  function _destroy($id)  {
    $sql = "DELETE FROM sessions WHERE session_id = "._esc($id);
    return ShopDB::query($sql);
  }

  function _clean($max) {

    $old = time() - $max;

    $sql = "DELETE FROM sessions WHERE session_access < "._esc($old);
    return ShopDB::query($sql);
  }

  // ensure session data is written out before classes are destroyed
  // (see http://bugs.php.net/bug.php?id=33772 for details)
  function __destruct () {
    @session_write_close();
  } // __destruct

}
/*
This requires an existing table named sessions, whose format is as follows:

mysql> DESCRIBE sessions;
+--------+------------------+------+-----+---------+-------+
| Field  | Type             | Null | Key | Default | Extra |
+--------+------------------+------+-----+---------+-------+
| id     | varchar(32)      |      | PRI |         |       |
| access | int(10) unsigned | YES  |     | NULL    |       |
| data   | text             | YES  |     | NULL    |       |
+--------+------------------+------+-----+---------+-------+
This database can be created in MySQL with the following syntax:

CREATE TABLE sessions
(
    id varchar(32) NOT NULL,
    access int(10) unsigned,
    data text,
    PRIMARY KEY (id)
);
*/
?>