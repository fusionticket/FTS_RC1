<!DOCTYPE html>
<html>
<head>
    <!-- Created by Artisteer v4.1.0.59861 -->
    <meta name="viewport" content="initial-scale = 1.0, maximum-scale = 1.0, user-scalable = no, width = device-width">
		<title>FusionTicket: Box Office / Sale Point </title>
    <!--[if lt IE 9]><script src="https://html5shiv.googlecode.com/svn/trunk/html5.js"></script><![endif]-->
    <link rel="stylesheet" href="style.php?T=style.css" media="screen">
    <!--[if lte IE 7]><link rel="stylesheet" href="style.php?T=style.ie7.css" media="screen" /><![endif]-->

    <link rel="stylesheet" href="style.php?T=style.responsive.css" media="all">

    {minify type='css'  base=''}
    {minify type='css'  base='css' files='jquery.datatables.css'}

    {minify type='js' base='scripts/jquery'}
    {minify type='js' base='scripts/jquery' files='jquery-ui-timepicker-addon.js,jquery.dataTables.js,jquery.dataTables.columnFilter.js,jquery.dataTables.KeyTable.min.js,jquery.notify.js'}

    <script src="style.php?T=script.js"></script>
    <script src="style.php?T=script.responsive.js"></script>

    <link rel="stylesheet" href="style.php?T=style.ext.css" type="text/css">

		<script type="text/javascript">
      var address = '{$_SHOP_root}';
			var lang = new Object();
			lang.required = '{!mandatory!}';        lang.phone_long = '{!phone_long!}'; lang.phone_short = '{!phone_short!}';
			lang.fax_long = '{!fax_long!}';         lang.fax_short = '{!fax_short!}';
			lang.email_valid = '{!email_valid!}';   lang.email_match = '{!email_match!}';
			lang.not_number = '{!not_number!}';     lang.add_tickets = '{!add_tickets!}';
			lang.chart_title = '{!select_seat_pos!}';
      lang.discount_none ='{!discount_none!}';
      lang.select_event_first ='{!select_event_first!}';
		</script>
    {minify type='js' base='pos/scripts' files='pos.jquery.style.js,pos.jquery.ajax.js,pos.jquery.order.functions.js,pos.jquery.order.js,pos.jquery.order.user.js,pos.jq.forms.js,pos.jq.current.js,pos.jq.current.functions.js'}
    <style>
      .ui-state-highlight {
        	background: #cdefeb url(../css/flick/images/ui-bg_flat_55_d2e2fe_40x100.png) 50% 50% repeat-x !important ;
			}
		</style>

    <script type="text/javascript">
        jQuery(document).ready(function(){
            {gui->getJQuery}
        });
    </script>

</head>
<body>
<div id="art-main">

    <div id='messagebar' style='text-align:left; display: block; position: fixed; left:0px; top: 0px; z-index: 1031; width:100%;'> </div>

    <div class="art-sheet clearfix">
<header class="art-header">
				<div class="loading">
					<img src="{$_SHOP_themeimages}LoadingImageSmall.gif" width="16" height="16" alt="Loading data, please wait" />
				</div>
    <div class="art-shapes"></div>
<h1 class="art-headline" data-left="1.22%">
   {!box_office!}
</h1>
 <h2 class="art-slogan" data-left="50%"> {$pos->user_firstname} {$pos->user_lastname} - {$pos->user_city}</h2>
<div class="art-positioncontrol art-positioncontrol-1616327719" id="logo" data-left="1.25%"><img src='{$_SHOP_images}logo.png'  border='0' style='float:left;'/></div>
<nav class="art-nav">
{if !empty($topmenu)}
  {menu data=$topmenu class='art-hmenu'}
{else}
  <ul class='art-hmenu'>
   <li>
      Welkom to Fusion Ticket.
   </li>
  </ul>
{/if}
</nav>
</header>
<div class="art-layout-wrapper">
  <div class="art-content-layout">
    <div class="art-content-layout-row">

			<div class="art-layout-cell art-content">
         <article class="art-post art-article">
                                {if $headerNote}
                                  <div class="art-postcontent  art-postcontent-0 clearfix">
                                    <div class="art-content-layout">
                                      <div class="art-content-layout-row">
                                        <div class="art-layout-cell layout-item-0" style="width: 100%;">
                                          <p>{$headerNote}</p>
                                        </div>
                                      </div>
                                    </div>
                                  </div>
                                {/if}
                                <div class="art-postcontent  art-postcontent-0 clearfix">
                                   {$WebContent}
                                </div>
                                {if !empty($footNote)}
                                  <div class="art-postcontent art-postcontent-0 clearfix">
                                    <div class="art-content-layout">
                                      <div class="art-content-layout-row">
                                        <div class="art-layout-cell layout-item-0" style="width: 100%;">
                                          <p>@@:{$footNote}</p>
                                        </div>
                                      </div>
                                    </div>
                                  </div>
                                {/if}
</article>

                        </div>
                    </div>
                </div>
            </div>
<footer class="art-footer">
<!-- To comply with our GPL please keep the following link in the footer of your site -->
              <!-- Failure to abide by these rules may result in the loss of all support and/or site status. -->
              <p>Copyright © 2013. All Rights Reserved.	Powered by <a href="http://fusionticket.org">Fusion Ticket</a> - The Free Open Source Box Office</p>
</footer>

    </div>
    <p class="art-page-footer">

    </p>
</div>


<div style="display:none" id="showdialog"></div>
<script>
var emsg = '{printMsg|escape:'quotes' key='__Warning__' addspan=false}';
var nmsg = '{printMsg|escape:'quotes' key='__Notice__' addspan=false}';
</script>

</body>
</html>