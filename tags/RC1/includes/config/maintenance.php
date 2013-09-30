<?php
    define ('BASE_URL',$_SHOP->root_base);
/**
 *               %%%copyright%%%
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

    define ('BASE_URL',$_SHOP->root_base);

  ?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
  <title>Fusion Ticket Installation</title>
    <link rel='stylesheet' href='<?php echo $_SHOP->root_base; ?>admin/admin.css' />


   <style>
    .err {color:#dd0000;}
    .warn {color:#cc9900;}

    .ok {color:#00dd00;}
    #mydiv {
    position:relative;
    top: 50%;
    left: 50%;
    width:30em;
    margin-top: -9em; /*set to a negative number 1/2 of your height*/
    margin-left: -15em; /*set to a negative number 1/2 of your width*/
 }
   #wrap {
    position:absolute;
    bottom : 00px;
    top : 00px;
       left: 50%;
       margin-left: -512px; /*set to a negative number 1/2 of your width*/
       min-height:300px;
     }
   #footer {
    position:absolute;
     bottom: 00px;
     width: 1024px;

   }
   </style>

</head>
<body>
    <div id="wrap">
      <div id="header">
        <img src="<?php echo BASE_URL;?>/images/logo.png" border="0" />
        <h2>Maintenance <span style=\"color:red; font-size:14px;\"><i>[<?php echo INSTALL_VERSION; ?>]</i></span></h2>
      </div>
      <div id="navbar">
            <table width='100%'>
              <tr><td>&nbsp;<b> </b></td></tr>
            </table>
      </div>
      <div id="mydiv"><center>
         <h1> <?php echo $_SHOP->offline_message; ?></h1>
	          <h3>Please return later</h3></center>
      </div>

      <div id="footer">
        Powered by <a href="http://fusionticket.org">Fusion Ticket</a> - The Free Open Source Box Office
      </div>
    </div>
  </body>
</html>