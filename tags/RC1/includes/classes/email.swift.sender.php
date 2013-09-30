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

require_once (LIBS.'swift'.DS.'swift_required.php');

class EmailSwiftSender {

  public function send(&$swiftMessage, $type='',&$logger , &$failed, $data=array(), $manSet=array()){
    global $_SHOP;

    $smtpHost = is($manSet['smtp_host'],$_SHOP->mail_smtp_host);
    $smtpPort = is($manSet['smtp_port'],$_SHOP->mail_smtp_port);
    $smtpSecurity = is($manSet['smtp_security'],$_SHOP->mail_smtp_security);
    $smtpUsername = is($manSet['smtp_username'],$_SHOP->mail_smtp_user);
    $smtpPassword = is($manSet['smtp_password'],$_SHOP->mail_smtp_pass);
    $sendmail = is($manSet['sendmail'],$_SHOP->mail_sendmail);

    //Add SMTP Mailer if defined.
    if(empt($smtpHost,false)){
      //if need to auth use the following method.
      if(empt($smtpUsername,false)){
        $smtp = Swift_SmtpTransport::newInstance($smtpHost,
        empt($smtpPort,'25'),
        empt($smtpSecurity,null))
          ->setUsername($smtpUsername)
          ->setPassword(empt($smtpPassword,''));
      }else{
        $smtp = Swift_SmtpTransport::newInstance($smtpHost,
        empt($smtpPort,'25'),
        empt($smtpSecurity,null));
      }
      $tranports[] = $smtp;
    }
    //Add sendmail
    if(empt($sendmail,false)){
      $sendMailTsprt = Swift_SendmailTransport::newInstance(empt($sendmail,'/usr/sbin/sendmail -bs'));
      $tranports[] = $sendMailTsprt;
    }

    //Add mail as good measure to try and fall back on
    $mail = Swift_MailTransport::newInstance();
    $tranports[] = $mail;

    //Add to fail over transport
    $transport = Swift_FailoverTransport::newInstance($tranports);

    //Create Mailer
    $mailer = Swift_Mailer::newInstance($transport);

    //Or to use the Echo Logger
    //$logger = new Swift_Plugins_Loggers_EchoLogger();
    //$mailer->registerPlugin(new Swift_Plugins_LoggerPlugin($logger));

    //Or to use the Normal Logger
    $logger = new Swift_Plugins_Loggers_ArrayLogger();
    $mailer->registerPlugin(new Swift_Plugins_LoggerPlugin($logger));
    $log = new EmailLog($data, $swiftMessage);

    try{
      $ret = $mailer->send($swiftMessage,$failedAddr);
    }catch(Exception $e){
      $ret = false;
    }

    $log->el_failed = ($ret)?'no': 'yes';
    $log->el_log = empt($logger->dump(),'');
    $log->el_bad_emails = serialize(empt($failedAddr,''));
    $_SHOP->skiptrace = true;
    $log->save();

    if(!$ret || $ret < 1){
      Shopdb::dblogging("email '{$type}' errors:\n".$logger->dump());
      addWarning('failed_send_to',print_r($swiftMessage->getTo(),true));
      return false;
    }else{
      return $ret;
    }

  }

}

?>