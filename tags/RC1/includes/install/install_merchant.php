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
require_once(dirname(dirname(__FILE__)).DS."admin".DS."class.adminview.php");
require_once(dirname(dirname(__FILE__)).DS."classes".DS."class.model.php");
//require_once(dirname(dirname(__FILE__)).DS."classes".DS."model.organizer.php");

class install_merchant  {
  static function precheck($Install) {
    return  true;
  }

  static function postcheck($Install) {
    if (empty($_POST['organizer_name'])) { addError('organizer_name','Mandatory'); }
    if (empty($_POST['organizer_email'])) { addError('organizer_email','Mandatory'); }
    Install_Request(array('organizer_name','organizer_address', 'organizer_plz', 'organizer_ort', 'organizer_state', 'organizer_country',
                          'organizer_phone','organizer_fax', 'organizer_currency', 'organizer_email'),'CONFIG');
    return !hasErrors();
  }

  static function display($Install) {
    define("organizer_name","Name");
    define("organizer_address","Address");
    define("organizer_plz","Zip");
    define("organizer_ort","City");
    define("organizer_state","State");
    define("organizer_country","Country");
    define("organizer_phone","Phone");
    define("organizer_fax","Fax");
    define("organizer_email","E-Mail");
    define("organizer_currency","Currency");

    Install_Form_Open ($Install->return_pg,'','Merchant Detail Settings');
    echo "<table cellpadding=\"1\" cellspacing=\"2\" width=\"100%\">
            <tr>
                Enter the required merchant details. This information can later be changed within the admin section.<br>
              </td>
            </tr>
            <tr> <td width='30%' height='6px'></td><td></td> </tr>
";
    AdminView::$labelwidth = '30%';
    AdminView::print_input('organizer_name'   ,$_SESSION['CONFIG'], true,25,100);
    AdminView::print_input('organizer_address',$_SESSION['CONFIG'], true,25,100);
    AdminView::print_input('organizer_plz'    ,$_SESSION['CONFIG'], true,25,100);
    AdminView::print_input('organizer_ort'    ,$_SESSION['CONFIG'], true,25,100);
    AdminView::print_input('organizer_state'  ,$_SESSION['CONFIG'], true,25,100);
    AdminView::print_countrylist('organizer_country', $_SESSION['CONFIG'], true);
    AdminView::print_input('organizer_phone'  ,$_SESSION['CONFIG'], false,25,100 );
    AdminView::print_input('organizer_fax'    ,$_SESSION['CONFIG'], false,25,100 );
    AdminView::print_input('organizer_email'  ,$_SESSION['CONFIG'], true,25,100 );
    AdminView::print_input('organizer_currency',$_SESSION['CONFIG'], true,4,3 );
    echo " </table>";
    Install_Form_Buttons ();
    Install_Form_Close ();
  }
}
?>