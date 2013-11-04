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

function smarty_function_placemap($params, $smarty){

    $pz = preg_match(strtolower('/no|0|false/'), $params['print_zone']);
    $imagesize = is ($params['imagesize'], 16);
    return placeMapDraw($params['category'], $params['restrict'], !$pz, $params['area'], $imagesize, is($params['seatlimit'],15));

}

function placeMapStyle($style){
  return vsprintf('top: %dpx;left: %dpx; height: %dpx; width: %dpx; ',$style);
}

function placeMapDraw($category, $restrict = false, $print_zone = true, $area = 'www', $imagesize = 16, $seatlimit = 15) {
  global $_SHOP;

  $l_row = ' '.con('place_row').' ';
  $l_seat = ' '.con('place_seat').' ';

If (is_array($category)) {
  $cat_ident = $category['category_ident'];
  $res = '';
  $pmp = PlaceMapPart::loadFull($category['category_pmp_id']);
  if (!$pmp) {
    return '';
  }

  } else {
    $cat_ident = -1;
    $cat_num = 0;
    $pmp = $category;
  }

//  print_r($category);
  $cats = $pmp->categories;
  $zones = $pmp->zones;

  $pmp->check_cache();

  if (false && $restrict) {
      $bounds = $pmp->category_bounds($cat_ident);
      $left   = $bounds['left'];
      $right  = $bounds['right'];
      $top    = $bounds['top'];
      $bottom = $bounds['bottom'];

  } else {
      $left   = 0;
      $right  = $pmp->pmp_width - 1;
      $top    = 0;
      $bottom = $pmp->pmp_height - 1;
  }
//    $pmp->pmp_shift = true;
  $shift = ($pmp->pmp_shift) ?((int)($imagesize/2)):0;

  for ($j = $top; $j <= $bottom; $j++) {
      for ($k = $left; $k <= $right; $k++) {
          $seat = $pmp->data[$j][$k];
          if (!is_array($seat)) continue;
          $style= array($j*$imagesize,$k*$imagesize+($shift*($j % 2)),$imagesize-1,$imagesize-1);
          $reszz= "";
          if ($seat[PM_ZONE] === 'L') {
              if ($seat[PM_LABEL_TYPE] == 'RE' and $irow = $pmp->data[$j][$k + 1][PM_ROW]) {
                  $reszz= "<div class='pm_seatmap' style='".placeMapStyle($style)."'>$irow</div>";
              } elseif ($seat[PM_LABEL_TYPE] == 'RW' and $irow = $pmp->data[$j][$k - 1][PM_ROW]) {
                  $reszz= "<div class='pm_seatmap' style='".placeMapStyle($style)."'>$irow</div>";
              } elseif ($seat[PM_LABEL_TYPE] == 'SS' and $iseat = $pmp->data[$j + 1][$k][PM_SEAT]) {
                  $reszz= "<div class='pm_seatmap' style='".placeMapStyle($style)."'>$iseat</div>";
              } elseif ($seat[PM_LABEL_TYPE] == 'SN' and $iseat = $pmp->data[$j - 1][$k][PM_SEAT]) {
                  $reszz= "<div class='pm_seatmap' style='".placeMapStyle($style)."'>$iseat</div>";
              } elseif ($seat[PM_LABEL_TYPE] == 'T') {
                if ($seat[PM_LABEL_SIZE] == 0) {
                   continue;
                } elseif (strlen($seat[PM_LABEL_TEXT])>3 * $seat[PM_LABEL_SIZE]){
                   $reszz= "<img class='pm_seatmap' style='".placeMapStyle($style)."' src='".$_SHOP->images_url."info.gif' alt='{$seat[PM_LABEL_TEXT]}' title='{$seat[PM_LABEL_TEXT]}'>";
                } else {
                   $style[3] = $style[3] * $seat[PM_LABEL_SIZE];
                   $reszz= "<div class='pm_seatmap' style='".placeMapStyle($style)."'>{$seat[PM_LABEL_TEXT]}</div>";
                }
              } else
              if ($seat[PM_LABEL_TYPE] == 'E') {
                $reszz = "<img class='pm_seatmap' style='".placeMapStyle($style)."' src='".$_SHOP->images_url."exit.gif' alt='".con('placemapexit')."' title='".con('placemapexit')."'>";
              }
          } elseif (isset($seat[PM_CATEGORY])) {
              $zone = !empty($seat[PM_ZONE])? $zones[$seat[PM_ZONE]]:null;
              $cat  = $cats[$seat[PM_CATEGORY]];
              $cat_id = $seat[PM_CATEGORY];
              $sty = "";//"background-color:{$zone->pmz_color};";

              $cat_num = 0;
              switch ($cat->category_numbering) {
                case 'both':
                  $cat_num = 3;
                  break;
                case 'rows':
                  $cat_num = 2;
                  break;
                case 'seat':
                  $cat_num = 1;
                  break;
              }


              if (is($pmp->data[$j - 1][$k][PM_CATEGORY],0) != $cat_id) {
                  $sty .= "border-top-color: {$cat->category_color};";
              }

              if (is($pmp->data[$j + 1][$k][PM_CATEGORY],0) != $cat_id) {
                  $sty .= "border-bottom-color: {$cat->category_color};";
              }

              if (is($pmp->data[$j][$k - 1][PM_CATEGORY],0) != $cat_id) {
                  $sty .= "border-left-color: {$cat->category_color};";
              }

              if (is($pmp->data[$j][$k + 1][PM_CATEGORY],0) != $cat_id) {
                  $sty .= "border-right-color: {$cat->category_color};";
              }
              //Empty seats
              if ($seat[PM_STATUS] == PM_STATUS_FREE) {
                  if ($seat[PM_CATEGORY] == $cat_ident) {
                       $reszz .= "<img class='pm_seatmap pm_check' style='{$sty}".placeMapStyle($style)."' border=1 id='seat{$seat[PM_ID]}' onclick='javascript:gridClick({$seat[PM_ID]});' src='".$_SHOP->images_url."seatfree.png' title='";
                      if ($print_zone && $zone) {
                          $reszz .= $zone->pmz_name . ' ';
                      }
                      if (($cat_num & 2) and $seat[PM_ROW] != '0') {
                          $reszz .= $l_row . $seat[PM_ROW];
                      }
                      if (($cat_num & 1) and $seat[PM_SEAT] != '0') {
                          $reszz .= $l_seat . $seat[PM_SEAT];
                      }
                      $reszz .= "'>";
                  } else {
                    $reszz = "<img class='pm_seatmap' style='{$sty}background-color:Gainsboro;".placeMapStyle($style)."' border=1 src='".$_SHOP->images_url."seatdisable.png'>";
                  }
                  ////////////Reserved seats, they will only be selectable if you have area='pos' set in cat...tpl
              } elseif ($seat[PM_STATUS] == PM_STATUS_RESP ) {//&& $seat[PM_CATEGORY] == $cat_ident
                  $reszz = "<img class='pm_seatmap' style='{$sty}".placeMapStyle($style)."' border=1 src='".$_SHOP->images_url."seatselect.png' title='";
                  if ($print_zone && $zone) {
                      $reszz .= $zone->pmz_name . ': ';
                  }
                  if (($cat_num & 2) and $seat[PM_ROW] != '0') {
                      $reszz .= $l_row . $seat[PM_ROW];
                  }
                  if (($cat_num & 1) and $seat[PM_SEAT] != '0') {
                      $reszz .= $l_seat . $seat[PM_SEAT];
                  }
                  $reszz .= "'>";
              } else {
                if ($seat[PM_CATEGORY] != $cat_ident) {
                  $sty .= 'background-color:Gainsboro;';
                }
                $reszz = "<img class='pm_seatmap' style='{$sty}".placeMapStyle($style)."' border=1 src='".$_SHOP->images_url."seatused.png'>";
              }
          }
          $res .= $reszz;
      }
  }

  $l = $_SHOP->lang;

  switch ($pmp->pmp_scene) {
      case 'south':
          $res = "<table border=0 cellspacing=0 cellpadding=0>
                    <tr>
                      <td>
                     <div style='position: relative; overflow-x: hidden; overflow-y: hidden; ".
            sprintf('height: %dpx; width: %dpx; ',(($bottom-$top)+1)*$imagesize,(($right-$left)+2)*$imagesize)."'>$res
            </div>
                      </td>
                    </tr>
                    <tr>
                      <td align='center' valign='middle' style='vertical-align:middle; text-align:center'>
                        <img src='".$_SHOP->images_url."scene_h_$l.png'>
                      </td>
                    </tr>
                  </table>";
          break;
      case 'west':
         $res = "<table border=0 cellspacing=0 cellpadding=0>
                   <tr>
                     <td align='center' valign='middle' style='vertical-align:middle; text-align:center'>
                       <img src='".$_SHOP->images_url."scene_v_$l.png'>
                     </td>
                     <td>
   <div style='position: relative; overflow-x: hidden; overflow-y: hidden; ".
   sprintf('height: %dpx; width: %dpx; ',(($bottom-$top)+1)*$imagesize,(($right-$left)+2)*$imagesize)."'>$res
   </div>
                       </td>
                     </tr>
                   </table>";
            break;
        case 'east':
            $res = "<table border=0  cellspacing=0 cellpadding=0>
                      <tr>
                        <td>
                       <div style='position: relative; overflow-x: hidden; overflow-y: hidden; ".
                       sprintf('height: %dpx; width: %dpx; ',(($bottom-$top)+1)*$imagesize,(($right-$left)+2)*$imagesize)."'>$res
                       </div>
                        </td>
                        <td align='center' valign='middle' style='vertical-align:middle; text-align:center'>
                          <img src='".$_SHOP->images_url."scene_v_$l.png'>
                        </td>
                      </tr>
                    </table>";
            break;
        case 'north':
            $res = "<table border=0 cellspacing=0 cellpadding=0>
               <tr>
                 <td align='center' valign='middle' style='vertical-align:middle; text-align:center'>
                   <img src='".$_SHOP->images_url."scene_h_$l.png'>
                 </td>
               </tr>
               <tr>
                 <td>
   <div style='position: relative; overflow-x: hidden; overflow-y: hidden; ".
   sprintf('height: %dpx; width: %dpx; ',(($bottom-$top)+1)*$imagesize,(($right-$left)+2)*$imagesize)."'>$res
   </div>
                 </td>
               </tr>
             </table>";
          break;
        default:
            $res = "<table border=0 cellspacing=0 cellpadding=0>
               <tr>
                 <td align='center' valign='middle' style='vertical-align:middle; text-align:center'>
                   <img src='".$_SHOP->images_url."scene_h_$l.png'>
                 </td>
               </tr>
               <tr>
                 <td>
                   <div style='position: relative; overflow-x: hidden; overflow-y: hidden; ".
                   sprintf('height: %dpx; width: %dpx; ',(($bottom-$top)+1)*$imagesize,(($right-$left)+2)*$imagesize)."'>$res
                   </div>
                 </td>
               </tr>
             </table>";
    }
    $resx ='
      	<div id="seatslegend">
     	'.con('placemap_seats_selected').' <span id="seatscounter" class="seatscounter">0</span> '.con('placemap_seats_of').' <span id="maxseatscounter">'.$seatlimit.'</span>
   		</div>
       <input type=\'hidden\' id=\'place\' name=\'place\' value=\'\'>
         <input id="maxseats" value="'.$seatlimit.'" type="hidden" size="3" maxlength="5">
         <script>
          var places = [];
          function gridClick(id) {
            x = jQuery.inArray( id, places );';
    if ($seatlimit >= 0) {
      $resx .='
            c = jQuery("#maxseats").val();
         // alert(c+"  "+x);
            if ((x == -1) && (c >0)) {
              c--;
            } else if (( x > -1) && (c <= '.$seatlimit.' )) {
              c++;
            } else if (c == 0) {
                alert("You cannot choose more than '.$seatlimit.' seats at a time");
              return;
            }
       //   alert(c);
            jQuery("#maxseats").val(c);';
    }
      $resx .='
			  var counter = jQuery("#seatscounter").text();
            if (x == -1) {
              jQuery("#seat"+id).attr("src","'.$_SHOP->images_url.'seatselect.png");
              places.push(id);
                counter++;
            } else {
              jQuery("#seat"+id).attr("src","'.$_SHOP->images_url.'seatfree.png");
             places.splice(x,1);
                counter--;
            }
            jQuery("#place").val(places.join(\'~\'));
            jQuery(".seatscounter").text(counter);
          }
     </script>
';
   $res = $resx .'
<style type="text/css">
  .pm_seatmap {
     padding: 0px  !important;
     margin:  0px  !important;
      font-size: '.((int)($imagesize/1.75)).'px;
      overflow-x: hidden;
      overflow-y: hidden;
      position: absolute;
      vertical-align:middle;
      text-align: center;
      cursor:pointer;
      border:1px solid transparent;

  }
  #seatslegend {
    position: relative;
    top : 0px;
    left: auto;
    right: 0px;
    text-align: right;
    padding: 2px;
  }

</style>'."\n".$res;
    return $res;

}

?>