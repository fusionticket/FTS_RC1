<?php
/**
%%%copyright%%%
 *
 * FusionTicket - ticket reservation system
 *  Copyright (C) 2007-2013 FusionTicket Solution Limited . All rights reserved.
 *
 * Original Design:
 *  phpMyTicket - ticket reservation system
 *   Copyright (C) 2004-2005 Anna Putrino, Stanislav Chachkov. All rights reserved.
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
function get_content($url)
{
  if (function_exists('curl_init')) {
    $ch = curl_init();
    if ($ch) {
     	curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
      curl_setopt($ch,CURLOPT_RETURNTRANSFER, TRUE)  ;
    //  curl_setopt($ch,CURLOPT_PORT, 443)  ;
      curl_setopt ($ch, CURLOPT_URL, $url);
      curl_setopt ($ch, CURLOPT_HEADER, 0);
      curl_setopt ($ch, CURLOPT_TIMEOUT ,8);

      $string = curl_exec ($ch);
      curl_close ($ch);
    } else $string = false;
  } else $string = false;
  return $string;
}

class install_settings {
  static function precheck($Install) {
    global $_SHOP;
    $test = md5(date('R'));
    @file_put_contents($_SHOP->tmp_dir.'ssl_instal.txt',$test);
  	$file = constructBase(true).'index.php?do=testhttps';
	//$file = str_replace('https','ssl',$file );

 	  $testx = get_content($file);



    $_SESSION['CONFIG']['secure_site'] = is($_SESSION['CONFIG']['secure_site'], ($test===$testx) );
    if ($test !== $testx) {
      array_push($Install->Warnings,'When you want to use fusionticket we highly recommend that you install an SSL certificate to secure your users address information etc. <br>For now we will show a warning that the site is not secure, when tickets are ordered.');
    }
    return true;
  }

  static function postcheck($Install) {
    Install_Request(array('secure_site','timezone','trace_on'),'CONFIG');
    Install_Request(array('useUTF8'),'SHOP');
    Install_Request(array('fixed_url'));
    return true;
  }

  static function display($Install) {
    Install_Form_Open ($Install->return_pg,'','Configuration settings');

    $_SESSION['CONFIG']['secure_site'] = is($_SESSION['CONFIG']['secure_site'], true);
    $secure    = ($_SESSION['CONFIG']['secure_site'])?"checked='checked'":'';
    $fixed_url = isset($_SESSION['SHOP']['root'])?"checked='checked'":'';
    $selectedzone = is($_SESSION['CONFIG']['timezone'], date_default_timezone_get());
    $useUTF8 = !empty($_SESSION['SHOP']['useUTF8'])?"checked='checked'":'';

    echo "<table cellpadding=\"1\" cellspacing=\"2\" width=\"100%\" >
            <tr>
               <td colspan=\"2\" >
                 This page helps you setup some default settings. Move your mouse over the label for more info on the setting.
              </td>
            </tr>
            <tr> <td height='6px'></td> </tr>
            <tr>
              <td colspan=\"2\">
                <b>Site URL's setup:</b>
              </td>
            </tr>
            <tr>
              <td width='30%' class='has-tooltip'>
                  &nbsp;&nbsp;Force SSL on checkout:
                  <div id='secureCheckout' style='display:none;'>
                     Fusion ticket is designed to be used with SSL certified (HTTPS) checkout pages.<br>
                     We strongly recommend enabling this on your site.<br>
                     If you do not have SSL on your site you can turn this off by unchecking the option.
                   </div>
              </td>
              <td><input type='checkbox' {$secure} name='secure_site' value='1'></td>
            </tr>
             <tr>
              <td width='30%' class='has-tooltip'>
                  &nbsp;&nbsp;Save root url in init_config:
                  <div id='secureCheckout' style='display:none;'>
                      Use of fixed root url (<i>\$_SHOP->root</i> or <i>\$_SHOP->root_secure</i>) is strongly recommended.<br>
                      If you do not require to use fixed root then the option should be left unchecked and<br>
                      Fusion Ticket will automatically calculate the root values for you.
                   </div>
              </td>
              <td><input type='checkbox' {$fixed_url} name='fixed_url' value='1'></td>
            </tr>
            <tr> <td height='6px'></td> </tr>
            <tr>
              <td colspan=2><b>Database settings:</b></td>
            </tr>
            <tr>
              <td width='30%' class='has-tooltip'>
                &nbsp;&nbsp;Use UTF8 to store information:
                <div id='useUTF8' style='display:none;'>
                  With this option you can store the information using UTF8.<br/>
                  Use this option carefully it change the way the information is shown in the shop etc.
                </div>
              </td>
              <td><input type='checkbox' {$useUTF8} name='useUTF8' value='1'></td>
            </tr>
            <tr> <td height='6px'></td> </tr>
            <tr>
              <td colspan=\"2\">
              <b>Timezone setup:</b>
              </td>
            </tr>
            <tr>
              <td class='has-tooltip'>
                  &nbsp;&nbsp;Timezone:
                  <div id='secureCheckout' style='display:none;'>
                    Future versions will require the time zone where your hostserver is located. Please enter the time zone.
                  </div>
              </td>
              <td><select name=\"timezone\">".self::timezonechoice($selectedzone)."'</select></td>
            </tr>
            <tr> <td height='6px'></td> </tr>
            <tr>
              <td colspan=\"2\"  >
                <b>Help Use finding problems:</b>
              </td>
            </tr>
            <tr>
              <td class='has-tooltip'>
                  &nbsp;&nbsp;Create a trace log:
                  <div id='secureCheckout' style='display:none;'>
                    You can help us by automatically sending trace logs.<br>
                    Trace logs help us diagnose possible problems in the program.<br>
                    We would like to receive these logs if or when the system detects a problem.
                  </div>
               </td>
              <td><select name=\"trace_on\"><option value='ALLSEND'>Email orphan errors with history</option><option value='SEND'>eMail orphan errors</option><option value='ALL'>Alway log traces</option><option value='0'>Disable (faster response)</option> </select></td>
            </tr>
          </table>";
    Install_Form_Buttons ();
    Install_Form_Close ();
  }

  function timezonechoice($selectedzone) {
    $all = timezone_identifiers_list();

    $i = 0;
    foreach($all AS $zone) {
      $zone = explode('/',$zone);
      $zonen[$i]['continent'] = isset($zone[0]) ? $zone[0] : '';
      $zonen[$i]['city'] = isset($zone[1]) ? $zone[1] : '';
      $zonen[$i]['subcity'] = isset($zone[2]) ? $zone[2] : '';
      $i++;
    }

    asort($zonen);
    $structure = '';
    foreach($zonen AS $zone) {
      extract($zone);
      if($continent == 'Africa' || $continent == 'America' || $continent == 'Antarctica' || $continent == 'Arctic' || $continent == 'Asia' || $continent == 'Atlantic' || $continent == 'Australia' || $continent == 'Europe' || $continent == 'Indian' || $continent == 'Pacific') {
        if(!isset($selectcontinent)) {
          $structure .= '<optgroup label="'.$continent.'">'; // continent
        } elseif($selectcontinent != $continent) {
          $structure .= '</optgroup><optgroup label="'.$continent.'">'; // continent
        }

        if(isset($city) != ''){
          if (!empty($subcity) != ''){
            $city = $city . '/'. $subcity;
          }
          $structure .= "<option ".((($continent.'/'.$city)==$selectedzone)?'selected="selected "':'')." value=\"".($continent.'/'.$city)."\">".str_replace('_',' ',$city)."</option>"; //Timezone
        } else {
          if (!empty($subcity) != ''){
            $city = $city . '/'. $subcity;
          }
          $structure .= "<option ".(($continent==$selectedzone)?'selected="selected "':'')." value=\"".$continent."\">".$continent."</option>"; //Timezone
        }

        $selectcontinent = $continent;
      }
    }
    $structure .= '</optgroup>';
    return $structure;
  }
}
?>