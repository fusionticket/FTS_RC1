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
require_once("classes/class.component.php");

class MenuAdmin extends Component {
  function __construct(){
    global $_SHOP;
    $menu_items = array (
    	"index.php" 	    	=> "index_admin|control",
    	"view_users.php"	  => "tabs.admins_admin|manager",
    	"view_event.php"	  => "tabs.events_admin|manager",
    	"view_order.php"	  => "orders_admin|manager",
      "view_stats.php"	  => "statistic_admin|organizer",
      "view_impexp.php"	  => "transports_admin|admin",
      "view_search.php"	  => "search_admin|manager",
      "view_template.php"	=> "template_admin|admin",
    	"view_handling.php"	=> "handlings_admin|admin",
    	"view_utils.php"	  => "utilities_admin|admin"
    );

    $menu_items = plugin::call('updateExtendMenuItems', $menu_items, 'admin'); // depricated
    $menu_items = plugin::call('updateMenuItems', $menu_items, 'admin');
    $this->menu_items = $_SHOP->controller->addACLs($menu_items);
  }


  function draw () {
    global $_SHOP;

		// Specify menu items in an array
		//  "file name" 		=> "text_define"    (text define from /includes/lang/site_XX.inc)

		// Begin drawing the menu table
		echo "<center>
        <table width='{$this->width}' class='menu_admin' cellspacing='1' >
        <tr><td  class='menu_admin_title'>" . con('logged_in') . " " . $_SHOP->admin->admin_login . "</td></tr>";

		// Loop through the menu item array and put the'menu_admin_link_selected'-class on the linkt to current file
		foreach($this->menu_items as $link => $text){
		  if ($link !='#') {
		    echo "<tr><td  class='menu_admin_item'><a href={$link} id='{$text}' ";
		    if ($text=="{$this->current_page}_admin"){
		      echo "class='menu_admin_link_selected'>";
		    } else {
		      echo "class='menu_admin_link'>";
		    }
		    $text = str_replace('.','-' ,$text);
		    echo con($text);
		    //  var_dump( preg_match('|^[a-z]+[a-z0-5_-]+$|', $text));
		    echo "</a></td></tr>\n";
		  } else {
		    echo "<tr><td></td></tr>\n";
		  }
		}
    echo "<tr><td></td></tr>\n";
  	$session_name = session_name();
	echo "<tr><td  class='menu_admin_item'>
	<a href='{$_SHOP->root_base}?AdminSession={$session_name}' class='menu_admin_link' target='_blank'>" . con('preview_webshop') . "</a></td></tr>";
	echo "<tr><td  class='menu_admin_item'>
     <a href='{$_SERVER["PHP_SELF"]}?action=logout' class='menu_admin_link'>" . con('logout') . "</a></td></tr>
     </table>
    </center><br>";
  }
}

?>