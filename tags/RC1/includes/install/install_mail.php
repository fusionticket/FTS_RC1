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

class install_mail {
  static function precheck($Install) {
    return true;
  }

  static function postcheck($Install) {
    GLOBAL $_SHOP;
    Install_Request(array('mail_sendmail','mail_smtp_host', 'mail_smtp_port', 'mail_smtp_security',
                          'mail_smtp_user', 'mail_smtp_pass'),'CONFIG');
    Install_Request(array('usesendmail','usesmtp'));

    if (isset($_POST['usesendmail'])) {
      if (empty($_POST['mail_sendmail'])) {
        array_push($Install->Errors,'You need to fill the sendmail path to use sendmail.');
      }
    } else {
      unset($_SESSION['CONFIG']['mail_sendmail']);//  = null;
    }
    if (isset($_POST['usesmtp'])) {
      if (empty($_POST['mail_smtp_host']) or empty($_POST['mail_smtp_port'])) {
        array_push($Install->Errors,'You need to fill the Hostname and port to use SMTP.');
      }
    } else {
      unset($_SESSION['CONFIG']['mail_smtp_host']);// = null;
      unset($_SESSION['CONFIG']['mail_smtp_port']);// = null;
      unset($_SESSION['CONFIG']['mail_smtp_user']);// = null;
      unset($_SESSION['CONFIG']['mail_smtp_pass']);// = null;
      unset($_SESSION['CONFIG']['mail_smtp_security']);// = null;
    }
    // The next values are not used anymore so the can be removed when exist.
    unset($_SESSION['CONFIG']['mail_smtp_helo']);// = null;
    unset($_SESSION['CONFIG']['mail_smtp_auth']);// = null;
    unset($_SESSION['CONFIG']['mail_mode']);// = null;

    if (!empty($_POST['testemail'])) {
      setmail();
      Opendatabase();
      //Create a message
      $message = Swift_Message::newInstance('Test email from: '.$_SESSION['CONFIG']['organizer_name'] )
        ->setFrom(array($_SESSION['CONFIG']['organizer_email'] => $_SESSION['CONFIG']['organizer_name']))
        ->setTo(array($_POST['testemail']))
        ->setBody('This is a test mail create by the installation programm of Fusion Ticket.')
        ;
      try {
        if(!EmailSwiftSender::send($message,"",$logger, $failedAddr, array('action' => 'test mail'))){
          array_push($Install->Errors,'Sorry the mail was not sent, check your mail settings.<br>'."<pre>".$logger->dump()."</pre>" );
        }
      } catch  (Exception $e) {
        array_push($Install->Errors, 'Caught exception: '.  $e->getMessage() );
      }
    }

    if (!isset($_POST['usesendmail'])) {
      unset($_SESSION['CONFIG']['mail_sendmail']);//  = null;
    }
    if (!isset($_POST['usesmtp'])) {
      unset($_SESSION['CONFIG']['mail_smtp_host']);// = null;
      unset($_SESSION['CONFIG']['mail_smtp_port']);// = null;
      unset($_SESSION['CONFIG']['mail_smtp_user']);// = null;
      unset($_SESSION['CONFIG']['mail_smtp_pass']);// = null;
      unset($_SESSION['CONFIG']['mail_smtp_security']);// = null;
    }

    return true;

  }

  static function display($Install) {
    Install_Form_Open ($Install->return_pg,'','Mail settings.');
    if (!empty($_SESSION['CONFIG']['mail_smtp_security'])) $chk[$_SESSION['CONFIG']['mail_smtp_security']] = 'selected="selected"';

    $transports =stream_get_transports();
    if (empty($_SESSION['CONFIG']['mail_smtp_host'])) $_SESSION['CONFIG']['mail_smtp_host'] = 'localhost';
    if (empty($_SESSION['CONFIG']['mail_smtp_port'])) $_SESSION['CONFIG']['mail_smtp_port'] = '25';
    $_SESSION['testemail'] = is($_SESSION['testemail'],'');
    echo "<table cellpadding=\"1\" cellspacing=\"2\" width=\"100%\" border=0>
            <tr>
              <td colspan=\"4\">
                Please configure your mail server settings. You can now choose between sendmail and SMTP. <br>
                The default linux-mail system will be used as a backup.<br>
              </td>
            </tr>
            <tr> <td colspan=\"2\" height='6px'></td> </tr>
            <tr >
               <td colspan=\"4\" ><input type=checkbox name='usesmtp' value='1'
               ".is($_SESSION['usesmtp'],'')."> <b>Use SMTP transport:</b></td>
            </tr>
            <tr >
              <td width='30%'>&nbsp;&nbsp;Hostname</td>
              <td width='30%'><input type=\"text\"  size=40 name=\"mail_smtp_host\" value=\"".$_SESSION['CONFIG']['mail_smtp_host']."\" /></td>
              <td width='5%'>Port</td>
              <td><input type=\"text\" size=6 name=\"mail_smtp_port\" value=\"".$_SESSION['CONFIG']['mail_smtp_port']."\" /></td>
            </tr>
            <tr >
              <td width='30%'>&nbsp;&nbsp;Username (opt.)</td>
              <td colspan=\"3\" ><input type=\"text\" name=\"mail_smtp_user\" value=\"".is($_SESSION['CONFIG']['mail_smtp_user'],'')."\" /></td>
            </tr>
            <tr >
              <td width='30%'>&nbsp;&nbsp;Password (opt.)</td>
              <td colspan=\"3\" ><input type=\"password\" name=\"mail_smtp_pass\" value=\"".is($_SESSION['CONFIG']['mail_smtp_pass'],'')."\" /></td>
            </tr>";

        echo" <tr >
              <td width='30%'>&nbsp;&nbsp;Security type</td>
              <td colspan=\"3\">
                <select name='mail_smtp_security'>
                  <option value='' >None</option>";
        if (in_array('ssl',$transports )){
          echo" <option value='ssl'>ssl</option>";
        }
        if (in_array('tls',$transports )){
          echo" <option value='tls'>tls</option>";
        }
        echo"</select>
              </td>
            </tr>
            <tr >
               <td colspan=\"2\" height='6px'></td>
            </tr>

            <tr >
               <td colspan=\"4\" ><input type=checkbox name='usesendmail' value='1' ".is($_SESSION['usesendmail'],'')."> <b>Use sendmail transport:</b> </td>
            </tr>
            <tr>
              <td valign='top'>&nbsp;&nbsp;Sendmail path:</td>
              <td colspan=\"3\"><input type=\"text\"  size=60  name=\"mail_sendmail\" value=\"".$_SESSION['CONFIG']['mail_sendmail']."\" /><br></td>
            </tr>
            <tr >
               <td colspan=\"2\" height='18px'></td>
            </tr>
            <tr >
               <td colspan=\"4\" ><b>Send a test email to:</b> </td>
            </tr>
            <tr>
              <td valign='top'>&nbsp;&nbsp;eMail address:</td>
              <td colspan=\"3\"><input type=\"text\"  size=60  name=\"testemail\" value=\"".$_SESSION['testemail']."\" /></td>
            </tr>

          </table>";
    Install_Form_Buttons ();
    Install_Form_Close ();
  }
}
?>