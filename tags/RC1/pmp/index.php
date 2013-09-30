<?PHP
  define('ft_check','shop');
  require_once('../includes/config/init_common.php');
//  require_once('../includes/config/init.php');
  require_once(INC.'admin/class.adminview.php');

  $thisx = new adminview ('100%');
  ?><!DOCTYPE html>
<html dir="ltr" lang="en-US"><head><!-- Created by Artisteer v4.0.0.57793 -->
    <meta charset="utf-8">
		<meta http-equiv="Content-Language" content="English" />
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <title>Administration</title>
  <link rel='stylesheet' href='admin.css'>
  <link rel="stylesheet" type="text/css" href="../css/flick/jquery-ui.css" media="screen" />
   <link rel="stylesheet" type="text/css" media="screen" href="../css/jquery.ui.selectmenu.css" />

	<script type="text/javascript" src="../scripts/jquery/jquery.min.js"></script>
  <script type="text/javascript" src="../scripts/jquery/jquery.ui.js"></script>
  <script type="text/javascript" src="../scripts/jquery/jquery.ui.selectmenu.js"></script>
  <script type="text/javascript" src="../scripts/jquery/jquery.selectable.se.js"></script>
	<script type="text/javascript" src='jquery.admin.pmp.js'></script>

  <style type="text/css">
  .flexigrid { display: table-cell;}
.pm_seatmap {
  padding: 0px  !important;
  margin:0px  !important;
  overflow-x: hidden;
  overflow-y: hidden;
  position: absolute;
  white-space: nowrap;
  vertical-align: middle;
  text-align: center;
  border:1px solid transparent;
  cursor:pointer;
  }
.pm_table    {margin:5px;}
.pm_info     {width:100%;}
.pm_box      {width:600px; background-color:#FFFFFF; padding:10px;}
.pm_nosale   {background-color:#d2d2d2}

.pm_ruler    {background-repeat:no-repeat; text-indent: 0px; margin-left:2px; margin-top:1px;}

.pm_free     {background-image: url("../images/2.png");background-repeat:no-repeat;background-position:center center; background-size: 90%;}
.pm_occupied {background-image: url("../images/3.png");background-repeat:no-repeat;background-position:center center; background-size: 90%;}
.pm_none     {background-image: url("../images/6.png");background-repeat:no-repeat; background-position:center center; background-size: 90%;}
.pm_all      {background-image: url("../images/2.png");background-repeat:no-repeat; background-position:center center; background-size: 90%;}
.pm_number   {background-image: url("../images/8.png");background-repeat:no-repeat; background-position:center center; background-size: 90%;}
.pm_error    {background-image: url("../images/1.png");background-repeat:no-repeat; background-position:center center; background-size: 90%;}
.pm_hold    {background-image: url("../images/4.png");background-repeat:no-repeat; background-position:center center; background-size: 90%;}
.pm_text    {}

.pm_check {
  cursor:pointer;
  }
#selectable .ui-selecting { background-color: #FECA40; }
#selectable .ui-selected { background-color: #F39814; }
#loading {
  display:none;
  position:absolute;
  background-color:#d2d2d2;
  opacity: 0.4;
  zIndex: 1001;
  left:0;
  top:0;
  width:100%;
  height:100%;
  }

#loading p {
  text-align: right;
  vertical-align: middle !important;
  font-family: Arial, Helvetica, sans-serif;
  font-size: 20px;
  Margin: 5px;
  color: black;
  opacity: 1;
  }
.ui-selectmenu {
  font-family: Arial, Helvetica, sans-serif;
  font-size: 12px;
  }

#selectable {
  position: relative;
  overflow: hidden;
  border:1px solid red;
  margin: 0.5em;
  vertical-align:top;
  text-align: left;
  width: 100px;
  height:100px;
  background-color:white;
  -moz-box-shadow: 3px 3px 4px #000;
  -webkit-box-shadow: 3px 3px 4px #000;
  box-shadow: 3px 3px 4px #000;
  /* For IE 8 */
  -ms-filter: "progid:DXImageTransform.Microsoft.Shadow(Strength=4, Direction=135, Color='#000000')";
  /* For IE 5.5 - 7 */
  filter: progid:DXImageTransform.Microsoft.Shadow(Strength=4, Direction=135, Color='#000000');

}
.ui-resizable-helper { border: 2px dotted #00F; }
button.ui-button-icon-only {
  height: 22px;
  width: 22px;
  }

  </style>
</head>
<body  leftmargin=0 topmargin=0 marginwidth=0 marginheight=0 >
  <table border='0' width='900'  cellspacing='0' cellpadding='0' bgcolor='#ffffff' style='text-align:center;'>
     <tr>
       <td  colspan='2' style='padding-left:20px;padding-bottom:5px;'>
       <img src='../images/logo.png'>
       </td>
     </tr>
    <tr><td style='padding-left:20px;border-top:#cccccc 1px solid;border-bottom:#cccccc 1px solid; padding-bottom:5px; padding-top:5px;' valign='bottom'><font color='#555555'><b>Welcome Lumensoft int</b></font></td>
        <td  align='right' style='padding-right:20px;border-top:#cccccc 1px solid;border-bottom:#cccccc 1px solid; '>
        <select name='setlang' onChange='set_lang(this)'><option value='en' selected>English</option><option value='de' >Deutsch</option><option value='nl' >Nederlands</option></select></td></tr></table>
        <br>
    </td></tr>
    <tr><td>
    <table border=0 width='900' class='aui_bico'><tr> <td>
      <table class='admin_form' width='900' border=0 cellspacing='1' cellpadding='5'>
        <tr>
          <td class='admin_list_title' colspan='1'>
             Placemap Part:
             <select id ='pmp_id'>
             <?PHP
    $query = "select *
              from PlaceMapPart
              order by pmp_id";

    if ($res = ShopDB::query($query)) {
      while ($data = shopDB::fetch_assoc($res)) {
         echo "<option value={$data['pmp_id']}>{$data['pmp_id']} {$data['pmp_name']}</option>";
      }
    }
             ?>
     </select>
         	</td>
         	<td align='right'>
             <!-- a class='link' href='/beta5/admin/view_event.php?action=edit_pmp&pmp_id=36'><img src='../images/edit.gif' border='0' alt='Edit' title='Edit'></a -->
         	</td>
        </tr>
        <tr>
          <td class='admin_value'>
            	test me now
         	</td>
          <td class='admin_value''>
            	2009-10-10 10:00:00
         	</td>
        </tr>
        <tr>
          <td class='admin_value' colspan='2'>
            Seat chart part: test2
            <a class='link' href='../admin/view_event.php?action=view_only_pmp&pmp_id=36'>
              <img src='../images/view.png' border='0' alt='View' title='View'>
            </a>
          </td>
        </tr>
      </table>
      </td></tr>
<tr><td>
      <form name='thisform' method='post' action='index.php'>
      <input type='hidden' name='action' value='coucou'>
      <input type='hidden' name='pmp_id' value='36'>
      <div style="position: relative;">

      <div id="toolbar" class="ui-widget-header ui-corner-all" style='width:100%;'>
        <button type='button'  id="repeatM"   name="type" value='MOD'>Refresh the grid</button>
    <span class="repeat">
        <button type='button' class="radiox" id="repeatT"  name="type" value='T'>Place custom text</button>
        <button type='button' class="radio" id="repeatRE" name="type" value='RE'>Show right side Row nr.</button>
        <button type='button' class="radio" id="repeatRW" name="type" value='RW'>Show left side Row nr.</button>
        <button type='button' class="radio" id="repeatSS" name="type" value='SS'>Show Seat nr. from below</button>
        <button type='button' class="radio" id="repeatSN" name="type" value='SN'>Show Seat nr. from above</button>
        <button type='button' class="radio" id="repeatE"  name="type" value='E'>Place "Exit" images</button>
    </span>
    <select class='searchselect' id='zones' name='zones' style='width:200px;font-size: 10px;' title='Select one of the zones'></select>
    <select class='searchselect' id='cats' name='cats'  style='width:200px;font-size: 10px;' title='Select one of the categories'></select>
    <span class="repeat">
    <button type='button' class="radiox" id="repeatSEAT" name="type" value='SEAT'>Place seat</button>
    <button type='button' class="radiox" id="repeatHOLD" name="type" value='HOLD'>Place seat on Hold</button>
    <button type='button' class="radiox" id="repeatNUM" name="type" value='NUM'>Numbering seats</button>
    </span>
    <button type='button' class="radio" id="repeatCLR" name="type" value='CLR'>Clear section</button>
    <span class="repeat">
    <button type='button' class="radioz" id="repeatZP" name="zoom" value='-2'>Zoom In</button>
    <button type='button' class="radioz" id="repeatZM" name="zoom" value='+2'>Zoom Out</button>
    <button type='button' class="radioz" id="repeatZR" name="zoom" value='0'>Restore size</button>
    </span>
</div>
      <div  id='seats' style="overflow: auto;  position: relative; height: 350px; width:900px; border: 1px solid #DDDDDD; background-color: #fcfcfc" align='center' valign='center'>
        <div id="selectable" style='display:none'>
        </div>
      </div>
      <div id="loading" align='center' valign='center'> <p>
        <img src="../images/theme/default/grid-loading.gif"/></p>
      </div>
</div>

       <tr>
          <td align='center' height=3 class='admin_value' colspan='2'> </td>
        </tr>
        <tr>
          <td colspan=2 align='left' valign='top' >
            <table class='admin_form' width='100%' border=0 cellspacing='1' cellpadding='4'>
              <tr>
                <td align='right' class='admin_name'>
                  <input type='button' name='def_label_pmp' value='Update'/>
   	            </td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
    </form>

</td>
</tr>
</table>
      <div id="dialog-confirm"><div>
<div id=seatnumbering style='display:none;'>
<div id="tabs">
    <ul>
        <li><a href="#tabs-2">Automaticly</a></li>
        <li><a href="#tabs-1">Manual config</a></li>
    </ul>

    <div id="tabs-2"><form id=autonumbers>
  <?php
  $thisx->form_head(con('autonumber_pmz'));

  if (!isset($data['first_row'])) {
    $data['first_row'] = 1;
  }
  if (!isset($data['step_row'])) {
    $data['step_row'] = 1;
  }
  if (!isset($data['first_seat'])) {
    $data['first_seat'] = 1;
  }
  if (!isset($data['step_seat'])) {
    $data['step_seat'] = 1;
  }

  $thisx->print_input('first_row', $data, $err, 3, 4);
  $thisx->print_input('step_row', $data, $err, 3, 4);
  $thisx->print_checkbox('inv_row', $data, $err);
  $thisx->print_input('first_seat', $data, $err, 3, 4);
  $thisx->print_input('step_seat', $data, $err, 3, 4);
  $thisx->print_checkbox('inv_seat', $data, $err);
  $thisx->print_checkbox('flip', $data, $err);
  $thisx->print_checkbox('keep', $data, $err);

  $thisx->form_foot();
  ?>
  </form>
  </div>
      <div id="tabs-1">
    <form id=manualnumbers>
    <?php
  $thisx->list_head(con('seat_numbering'), 1);
  ?>
<tr><td align=center>
       <table id=manualsetings></table>
</td></tr>
    <?php
  $thisx->form_foot(1);

  ?>
    </form>
  </div>
</div>
</div>