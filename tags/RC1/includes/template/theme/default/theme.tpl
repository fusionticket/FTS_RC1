{*                  %%%copyright%%%
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
 *}{function name='menu' class='' level=0 data=[]}
  <ul class="{$class} level{$level}">
  {foreach $data as $entry}
    <li><a href={$entry.href}>{$entry.title}</a></li>
    {if !empty($entry.menu) && is_array($entry.menu)}
       {call name='menu' data=$entry.menu level=$level+1}
    {/if}
  {/foreach}
  </ul>
{/function}<!DOCTYPE html>
<html dir="ltr" lang="en-US"><head><!-- Created by Artisteer v4.0.0.57793 -->
    <meta charset="utf-8">
    <meta name="viewport" content="initial-scale = 1.0, maximum-scale = 1.0, user-scalable = no, width = device-width">

    <!--[if lt IE 9]><script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script><![endif]-->
    <link rel="stylesheet" href="{$_SHOP_root}style.php" media="screen">
    <!--[if lte IE 7]><link rel="stylesheet" href="{$_SHOP_root_secured}style.php?T=style.ie7.css" media="screen" /><![endif]-->
    <link rel="stylesheet" href="{$_SHOP_root}style.php?T=style.responsive.css" media="all">

		{include file="required_header.tpl"}
		<link rel='stylesheet' href='{$_SHOP_root}style.php?T=style.ext.css' type='text/css' />
    <script src="{$_SHOP_root}style.php?T=script.js"></script>
    <script src="{$_SHOP_root}style.php?T=script.responsive.js"></script>

  <script type="text/javascript">
  	jQuery(document).ready(function(){
      //var msg = ' errors';
      showErrorMsg(emsg);
      showNoticeMsg(nmsg);
      if (navigator.cookieEnabled == false) { $.modal("<div>{!cookie_disabled!}</div>"); }
    });
var ajaxQManager = $.manageAjax.create('ajaxQMan',{
	queue:true,
	abortOld:true,
	maxRequests: 1,
	cacheResponse: false
});
  </script>
</head>
<body>
   <div id='messagebar' style='text-align:left; display: block; position: fixed; left:0px; top: 0px; z-index: 1031; width:100%;'> </div>
  <div id="art-main">
    <div class="art-sheet clearfix">
      <header class="art-header clearfix">
        {if $organizer_logo}
          {gui->image href="{$organizer_logo}" class='art-logo' height=130 align='left' border="0" style="left:10px;top:4px"}
        {else}
          <img class='art-logo' style='left:10px;top:4px' src="{$_SHOP_themeimages}object0.png"/>
        {/if}
        <div class="art-shapes">
          <h1 class="art-headline" data-left="1.39%">
            <a href="#">
             {if !empty($shop_sitename)}
               {$shop_sitename}
             {else}
                {$organizer_name}
             {/if}
            </a>
          </h1>
          {if !empty($shop_slogan)}
            <h2 class="art-slogan" data-left="1.39%">Voer site-slogan in</h2>
          {/if}
        </div>
      </header>
      <nav class="art-nav clearfix">
        {if !empty($topmenu)}
          {call name='menu' data=$topmenu class="art-hmenu"}
        {else}
          <ul class="art-hmenu">
            <li>
               welkom to the world
            </li>
          </ul>
        {/if}
        {$plugin->showLanguageSelector()}
      </nav>
      {* include file="Progressbar.tpl" name=$name *}

      <div class="art-layout-wrapper clearfix">
        <div class="art-content-layout">
          <div class="art-content-layout-row">
            <div class="art-layout-cell art-sidebar1 clearfix">
              {include file='user_login_block.tpl'}
              {if !empty($vermenu)}
                <div class="art-vmenublock clearfix">
                  <div class="art-vmenublockheader">
                    <h3 class="t">{!vertical_menu!}</h3>
                  </div>
                  <div class="art-vmenublockcontent">
                    {call name='menu' data=$vermenu class="art-vmenu"}
                  </div>
                </div>
              {/if}
              {include file='cart_view_block.tpl'}
              {eval var=$plugin->leftsidebar()}
              {if $google_ad_client && $google_ad_slot_left && !$hideGoogleAds}
              <div class="art-block clearfix">

                        <script type="text/javascript">
                          <!--
                          google_ad_client = "{$google_ad_client}";
                          /* 200x200, gemaakt 13-9-10 */
                          google_ad_slot = "{$google_ad_slot_left}";
                          google_ad_width = 200;
                          google_ad_height = 200;
                          //-->
                        </script>
                        <script type="text/javascript"
                          src="https://pagead2.googlesyndication.com/pagead/show_ads.js">
                        </script>
              </div>
              {/if}
            </div>
            <div class="art-layout-cell art-content clearfix">
              <article class="art-post art-article">
                <h2 class="art-postheader">{$pagetitle}</h2>

                <div class="art-postcontent art-postcontent-0 clearfix">
                  {if !empty($headerNote)}
                    <div class="art-content-layout">
                      <div class="art-content-layout-row">
                        <div class="art-layout-cell layout-item-0" style="width: 100%;" >
                            <p>{$headerNote}</p>
                        </div>
                      </div>
                    </div>
                  {/if}
                  <div class="art-content-layout">
                    <div class="art-content-layout-row">
                        {$WebContent}
                    </div>
                  </div>
                  <div class="art-content-layout">
                    <div class="art-content-layout-row">
                      <div class="art-layout-cell layout-item-0" style="width: 100%;" >
                        {if !empty($footNote)}
                          <p>{$footNote}</p>
                        {/if}
                      </div>
                    </div>
                  </div>
                </div>
              </article>
            </div>
            {if $google_ad_client && $google_ad_slot_right && !$hideGoogleAds}
            <div class="art-layout-cell art-sidebar2 clearfix">
              <div class="art-block clearfix">

                <script type="text/javascript">
                  <!--
                  google_ad_client = "{$google_ad_client}";
                  /* 120x600, gemaakt 13-9-10 */
                  google_ad_slot = "{$google_ad_slot_right}";
                  google_ad_width = 120;
                  google_ad_height = 600;
                  //-->
                </script>
                <script type="text/javascript" src="https://pagead2.googlesyndication.com/pagead/show_ads.js"></script>
              </div>
            </div>
            {/if}
          </div>
        </div>
      </div>
      <footer class="art-footer clearfix">
        <p>
          <!-- To comply with our GPL please keep the following link in the footer of your site -->
          <!-- Failure to abide by these rules may result in the loss of all support and/or site status. -->
          Copyright &copy; 2013. All Rights Reserved.<br>
          Powered By <a href="https://www.fusionticket.org">Fusion Ticket</a> - Free Open Source Online Box Office
        </p>
      </footer>

    </div><br>
  </div>
  <div style="display:none" id='showdialog'></div>
  <script>
    var emsg = '{printMsg|escape:'quotes' key='__Warning__' addspan=false}';
    var nmsg = '{printMsg|escape:'quotes' key='__Notice__' addspan=false}';
  	jQuery(document).ready(function(){
      {gui->getJQuery}
    });
  </script>


</body>
</html>