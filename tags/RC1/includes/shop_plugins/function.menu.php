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

function smarty_function_menu ($params, $smarty) {
  return __menu($params['data'],is($params['level'],0),is($params['class'],''));
}

function __menu($data, $level=0, $class=''){
  $return = "<ul class='{$class} level{$level}'>";
  foreach ($data as $entry){
  	$classx = ($entry['active'])?'class="active" ':'';
    $return .= "<li><a href='{$entry['href']}' {$classx} >{$entry['title']}</a></li>";
    if (!empty($entry['menu']) && is_array($entry['menu'])){
      $return .= __menu($entry['menu'], $level+1);
    }
  }
  $return .= "</ul>";
  return $return;
}
?>