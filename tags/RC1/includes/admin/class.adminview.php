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
require_once("classes/class.component.php");

class AdminView extends Component {
  static $labelwidth = '40%';
  var $tabitems = array(0=> 'admin_default|control');
  var $page_width = 918;
  var $title = "Administration";
  var $ShowMenu = true;
  var $page_length = 15;

  function __construct ($width=0, $dummy=null){
    global $_SHOP;
    parent::__construct();
    if ($width) {
      $this->width = $width;
    }
    if (is_array($this->tabitems) && isset($_SHOP->controller)) {
      $this->tabitems = plugin::call('update'.get_class($this).'_Items', $this->tabitems);
      $this->tabitems = $_SHOP->controller->addACLs($this->tabitems, true);
    }
  }

  function ActionACLs($ACLs){
    global $_SHOP;
    if (is_array($ACLs)) {
      $ACLs = plugin::call('update'.get_class($this).'_Actions', $ACLs);
      $_SHOP->controller->addACLs($ACLs);
    }
  }

  function isAllowed($task, $isAction=false){
    global $_SHOP;
    return $_SHOP->controller->isAllowed($task);
  }


  function extramenus(&$menu){}
  function execute(){
     $tab = $this->drawtabs(null,false);
     return plugin::call('is'.get_class($this).'_Execute', $tab-1, $this);
  }

  function draw(){
    $tab = $this->drawtabs(null,false);
    return plugin::call('is'.get_class($this).'_Draw', $tab-1, $this);
  }

  function drawtabs($session=null, $show=true, $get='tab'){
    GLOBAL $_SHOP;
    if (is_null($session)) {
      $session = get_class($this);
    }
    if(isset($_REQUEST[$get])) {
      $_SESSION['tabs'][$session] = (int)$_REQUEST[$get];
    } elseif(!isset($_SESSION['tabs'][$session])) {
      $_SESSION['tabs'][$session] = (int)reset($this->tabitems);
    }
    if (!in_array((int)$_SESSION['tabs'][$session], array_values($this->tabitems))) {
      unset($_SESSION['tabs'][$session]);
      $_SHOP->controller->showForbidden(get_class($this));
      return false;
    }
    $this->tabindex = (int)$_SESSION['tabs'][$session];
    if ($show) {
      $_SHOP->trace_subject .= "[tab:{$_SESSION['tabs'][$session]}]";
      if (count($this->tabitems)>1 ) {
        echo $this->PrintTabMenu($this->tabitems, $_SESSION['tabs'][$session], "left");
      }
    }
    return ((int)$_SESSION['tabs'][$session])+1;
  }

  function list_head ($name, $colspan=2, $width = 0) {
      echo "<table class='admin_list' width='" . ($width?$width:$this->width) . "' cellspacing='1' cellpadding='4'>\n";
    if ($name) {
      echo "<tr><td class='admin_list_title' colspan='$colspan' >$name</td></tr>";
  }
  }

  function form_head ($name, $width = 0, $colspan = 2) {
      echo "<table class='admin_form' width='" . ($width?$width:$this->width) . "' cellspacing='1' cellpadding='4'>\n";
      if ($name) {
        echo "<tr><td class='admin_list_title' colspan='$colspan' >$name</td></tr>";
      }
  }

  function form_foot($colspan = 2, $backlink='', $submit='save', $showreset=true ) {
      echo "<tr  class='admin_value' ><td>";
      if (!is_array($backlink) and $backlink) {
        echo  $this->show_button($backlink,'admin_list',3);
      } elseif (is_array($backlink) and isset($backlink['backlink'])) {
        echo  $this->show_button($backlink['backlink'],'admin_list',3);
      }
      $colspan = $colspan-1;
      echo "&nbsp;</td><td align='right' class='admin_value' colspan='{$colspan}'>";
      echo $this->Show_button('submit',$submit,3);
      if (is_array($backlink)) {
        echo  $this->show_button($backlink['href'],$backlink['name'],3,$backlink);
      }
      if ($showreset) {
        echo $this->Show_button('reset','res',3);
      }
      echo "</td></tr></table></form>\n";
  }

  function form_footer($formid, $colspan = 2, $backlink='', $submit='save', $showreset=true) {
    $this->form_foot($colspan,$backlink,$submit,$showreset);
  }

  function grid_head($name='', $headers=array(), $options = array()) { // Lxparks - The two arays were not merging, so suggest this?
		$defaults=array('addaction'=>'','width'=>0, 'height'=>'100px', 'addfilter'=>true, 'footerdiv' =>'', 'sortable'=> true, 'showtitle'=>true, 'multi'=>'');
		$options=array_merge($defaults, $options);
		extract($options);

    $columnsfields = array('width','sort');
    echo "\n<div style=' width:" . ($width?$width:$this->width) . ";'>\n";
    echo "<table id='{$name}' cellspacing='1'>\n";
    $aFilters ='';
    $aColumns = '';

    if (is_array($headers)) {
      echo "  <thead>\n";
      $theads= $tsearch = "    <tr class='admin_list_header'>\n";
      $aColumns = array();
      $aFilters = array();
      foreach ($headers as $columnname => & $header) {
        if (is_numeric($columnname)) {
          $columnname   = $header;
          $header =  array();
        }

        $header['class'] = is($header['class'],'');
        $header['align'] = is($header['align'],'left');
        $header['width'] = (is($header['width'],'')?"'sWidth':'{$header['width']}px'":'');
        $theads .= "      <th class='{$header['class']}' id='{$columnname}' style='text-align:{$header['align']}'> ".con($columnname).'</th>'."\n"; //
        $tsearch .= "      <th class='{$header['class']}' id='{$columnname}' style='text-align:{$header['align']}'></th>\n"; //
        $col = array();
        if ($header['width']) {$col[] = $header['width']; }
        if ($columnname =='actions_header' || !is($header['sort'],true)) {$col[] = '\'bSortable\': false'; }
        if (is( $header['visible'],true)===false) {$col[] = '\'bVisible\': false'; }
        if (!empty($header['columntype']))  {$col[] = '\'sType\': \''.$header['columntype'].'\'';}

        $aColumns[] = ((!empty($col))?('{'.implode(',',$col).'}'):'null');

        if ($addfilter) {

          $values = is($header['values'],null);
          $res = array();
          if (is_array($values)) {
            foreach($values as $key => $value) {
              $res[] = (!is_numeric($key))?"{'value': '{$key}', 'label':'{$value}'}":'{$value}';
            }
            $values = '['.implode(',', $res).']';
          } else {
            $values='null';

          }
          $aFilters[] = '{"type": "'. is($header['type'],($columnname =='actions_header')?'null':'text').'", "values":'.$values.'}';
        }
      }
      if ($addfilter) {
        echo $tsearch."    </tr>\n";
        echo $theads."    </tr>\n";
        $aFilters = '.columnFilter({
          sPlaceHolder: "head:after",
          aoColumns: ['.implode(',
                     ', $aFilters).'] })';
      } else {
        $aFilters = '';
        echo $theads."    </tr>\n";

      }

      echo "  </thead>\n";

    }

    $namex = str_replace('_', '', $name );
    $footerdiv = ($footerdiv)?'<"#'.$footerdiv.'">':'';
    echo "  <tbody>\n";

    $this->addJQuery("$('#{$name}').dataTable( {
          'bJQueryUI': true,".
          ($showtitle?
          "'sDom': '<\"H\"<\"{$namex}toolbar\"><\"{$namex}addbutton\">r>t',":
          "'sDom': '<r>t',")."
          'sScrollY': '{$height}',
          'bSort': ".($sortable?'true':'false').",
          'bAutoWidth': false,
          'bSortClasses': false,
          'oLanguage': {
            'sEmptyTable': '".con('datatable_no_data')."',
            'sLoadingRecords' : '".con('datatable_loading')."',
            'sZeroRecords':  '".con('datatable_zerorecords')."',
          },
          'bPaginate': false,
          'bScrollCollapse': false".
				  ($aColumns?(",
          \"aoColumns\" : [".implode(",
                       ", $aColumns)."]"):'')."
       }){$aFilters};");
    if ($name && $showtitle) {
      $this->addJQuery("$('div.{$namex}toolbar').addClass('admin_list_title').css('float','left').html("._esc(con($name)).");");
      if (is_string($addaction)) {
         $this->addJQuery("$('div.{$namex}addbutton').css('float','right').html('"._esc($this->show_button($addaction,is($actionname,"add"),3),false)."');");
      }
    }
		if ($multi) {
				$this->addJQuery("
					$('#{$name}').dataTable().rowGrouping({

						bHideGroupingColumn: true});
				");
		}

  }

  function grid_footer() {
    echo "</tbody></table></div>\n";
  }
  /**
   * AdminView::print_multiRowField()
   *
   * This function will create a multirow of fields.
   * Prime example is Multiple Email Address and Names
   *
   * @param mixed $name array field name
   * @param mixed $data location of array will use $data[$name][$i]
   * @param mixed $err
   * @param integer $size field size
   * @param integer $max max field size
   * @param bool $multiArr to fields Key / Value or just Text/Field
   * @return void
   */
   function print_multiRowField($name, &$data , $err, $size = 30, $max = 100, $multiArr=false, $arrayPrefix=''){
    if($arrayPrefix <> ''){
      $prefix = $arrayPrefix."[$name]";
    }else{
      $prefix = "{$name}";
    }

    echo "<tr id='{$name}-tr' ><td class='admin_name' width='".self::$labelwidth."'>" , con($name) , "</td>
              <td class='admin_value' ><button id='{$name}-add' type='button'>".con('add_row')."</button> </td></tr>\n";

    $data[$name] = is($data[$name],array()); $i=0;
    foreach($data[$name] as $key=>$val){
      if(!$multiArr){
        echo "<tr id='{$name}-row-$i' class='{$name}-row'><td class='admin_name' width='".self::$labelwidth."'>".con($name)."</td>
                <td class='admin_value'>
                  <input type='text' name='{$prefix}[$i][value]' value='" . htmlspecialchars($val, ENT_QUOTES) . "'>
                  <a class='{$name}-row-delete link' href='#'><img src=\"../images/trash.png\" border='0' alt='".con('remove')."' title='".con('remove')."'></a>
                  ".printMsg($name)."
                </td></tr>\n";
      }else{
        echo "<tr id='{$name}-row-$i' class='{$name}-row'><td class='admin_value' style='width:100%;' colspan='2'>
                <input type='text' name='{$prefix}[$i][key]' value='" . htmlspecialchars($key, ENT_QUOTES) . "'>
                <input type='text' name='{$prefix}[$i][value]' value='" . htmlspecialchars($val, ENT_QUOTES) . "'>
                <a class='{$name}-row-delete link' href='#'><img src=\"../images/trash.png\" border='0' alt='".con('remove')."' title='".con('remove')."'></a>
                ".printMsg($name)."
              </td></tr>\n";
      }
      $i++;
    }
    if($multiArr){
      $script = "
          var {$name}Count = {$i};
          $('#{$name}-add').click(function(){
            $('#{$name}-tr').after(\"<tr id='{$name}-row-\"+{$name}Count+\"' class='{$name}-row' >\"+
                \"<td class='admin_value' style='width:100%;' colspan='2'>\"+
                  \"<input type='text' name='{$prefix}[\"+{$name}Count+\"][key]' value='' />&nbsp; \"+
                  \"<input type='text' name='{$prefix}[\"+{$name}Count+\"][value]' value='' />\"+
                  \"<a class='{$name}-row-delete link' href=''><img src='../images/trash.png' border='0' alt='".con('remove')."' title='".con('remove')."'></a>\"+
                \"</td>\"+
              \"</tr>\");

            {$name}Count++;
          });";
    }else{
      $script = "
          var {$name}Count = {$i};
          $('#{$name}-add').click(function(){
            $('#{$name}-tr').after(\"<tr id='{$name}-row-\"+{$name}Count+\"' class='{$name}-row' ><td class='admin_name' width='".self::$labelwidth."'>".con($name)."</td>\"+
                \"<td class='admin_value'>\"+
                  \"<input type='text' name='{$prefix}[\"+{$name}Count+\"][value]' value='' />\"+
                  \"<a class='{$name}-row-delete link' href=''><img src='../images/trash.png' border='0' alt='".con('remove')."' title='".con('remove')."'></a>\"+
                \"</td>\"+
              \"</tr>\");

            {$name}Count++;
          });";
    }
    $this->addJQuery($script);

    $script = "$('.{$name}-row-delete').live(\"click\",function(){
          $(this).parent().parent().remove();
          return false;
        });";
    $this->addJQuery($script);

  }

  /**
   * @param array other : additional optional options
   *  add_arr => array('value'=>'name') this is the value for the add row button which wen set can be a drop down box.
   */
   function print_multiRowGroup($name, &$data , $err, $fields=array(),$arrayPrefix='',$other=array()){
    if(!is_array($fields)){
      return false;
    }elseif(empty($fields)){
      return false;
    }

    if($arrayPrefix <> ''){
      $prefix = $arrayPrefix."[$name]";
    }else{
      $prefix = "{$name}";
    }

    if(is($other['add_arr']) && is_array($other['add_arr']) ){
      $select = "<select name='{$name}_group_add' id='{$name}-group-add-field' >";
      foreach($other['add_arr'] as $oVal=>$oName){
        $select .= "<option value='{$oVal}' >{$oName}</option>";
      }
      $select .= "</select>";
    }else{
      $select = "<input type='text' name='{$name}_group_add' id='{$name}-group-add-field' size='15' maxlength='100'>";
    }

    echo "
      <tr id='{$name}-group-add-tr' >
        <td class='admin_name' width='".self::$labelwidth."'>" , con($name) , "</td>
        <td class='admin_value' >
          <button id='{$name}-group-add-button' type='button'>".con($name)." ".con('add_row')."</button>
          {$select}
          <span id='{$name}-error' style='display:none;'>".con('err_blank_or_allready')."</span>
        </td>
      </tr>\n";

    echo "
      <tr id='{$name}-group-select-tr'>
        <td class='admin_name'  width='".self::$labelwidth."'>".con($name)." ".con('select')."</td>
        <td class='admin_value'>
          <select id='{$name}-group-select' name='{$name}_group_select'>\n</select> ".
          $this->show_button('button','remove_group',2,
                             array('id'=>"{$name}-group-delete",
                                   'image'=>"trash.png")).
        "</td>
      </tr>\n";

    $data[$name] = is($data[$name],array()); $opts = "";
    foreach($data[$name] as $group=>$values){
      //for each group add the option list.
      $opts .= "<option value='{$group}'>".con($group)."</option>";

      //Fill Field type and values else add blanks.
      foreach($fields as $field=>$arr){
        $type = is($arr['type'],'text');
        $value = is($values[$field],'');
        if($type=='text'){
          $size=is($arr['size'],40);
          $max=is($arr['max'],100);
          $input = "<td colspan='1' width='60%' class='admin_value'><input type='text' name='{$prefix}[$group][$field]' value='".htmlspecialchars($value, ENT_QUOTES)."' size='{$size}' maxlength='{$max}'></td>";
          $colspan = 1;
        }elseif($type=='textarea'){
          $rows=is($arr['rows'],10);
          $cols=is($arr['cols'],92);
          $input = "</tr>
                    <tr id='{$name}-{$group}-{$field}-row' class='{$name}-row {$name}-group-row {$name}-{$group}-row' style='display:none;'>
                      <td colspan='2' class='admin_value'><textarea rows='{$rows}' cols='{$cols}' name='{$prefix}[$group][$field]'>".$value."</textarea></td>";
          $colspan = 2;
        }

        echo "<tr id='{$name}-{$group}-{$field}-row' class='{$name}-row {$name}-group-row {$name}-{$group}-row' style='display:none;'>
                <td colspan='{$colspan}' class='admin_name'>".con($field)."</td>"
                .$input."
              </tr>\n";
      }
    }
    //This add the exsisting options to the select
    $addOptsScript = "$('#{$name}-group-select').html(\"{$opts}\");";
    $this->addJQuery($addOptsScript);
    //This will let you delete a group
    $deleteScript = "$('#{$name}-group-delete').click(function(){
      var group = $('#{$name}-group-select').val();
      $('.{$name}-'+group+'-row').each(function(){
        $(this).remove();
      });
      $('#{$name}-group-select option[value=\"'+group+'\"]').remove();
      $('#{$name}-group-select').change();
    });";
    $this->addJQuery($deleteScript);

    //This is the add section, We build the fields first then chuck them into jquery;
    unset($input); $inputs = "";
    foreach($fields as $field=>$arr){
      $type = is($arr['type'],'text');
      $value = is($values[$field],'');
      if($type=='text'){
        $size=is($arr['size'],40);
        $max=is($arr['max'],100);
        $input = "<td colspan='1' class='admin_value' width='60%'><input type='text' name='{$prefix}[\"+newGroup+\"][{$field}]' value='' size='{$size}' maxlength='{$max}' /></td>";
        $colspan = 1;
      }elseif($type=='textarea'){
        $rows=is($arr['rows'],10);
        $cols=is($arr['cols'],92);
        $input = "</tr><tr id='{$name}-\"+newGroup+\"-{$field}-row' class='{$name}-row {$name}-group-row {$name}-\"+newGroup+\"-row' style='display:none;'><td colspan='2' class='admin_value'><textarea rows='{$rows}' cols='{$cols}' name='{$prefix}[\"+newGroup+\"][{$field}]'> </textarea></td>";
        $colspan = 2;
      }
      $inputs .= "\"<tr id='{$name}-\"+newGroup+\"-{$field}-row' class='{$name}-row {$name}-group-row {$name}-\"+newGroup+\"-row' style='display:none;'><td colspan='{$colspan}' class='admin_name' width='".self::$labelwidth."'>".con($field)."</td>{$input}</tr>\"+";
    }

    $addScript = "$('#{$name}-group-add-button').click(function(){
      var newGroup = $('#{$name}-group-add-field').val();
      newGroup = jQuery.trim(newGroup);

      if($('#{$name}-group-select option[value=\"'+newGroup+'\"]').val()!=newGroup && newGroup!=''){
        $('#{$name}-group-select-tr').after({$inputs}\"\");
        $('#{$name}-group-select').append(\"<option value='\"+newGroup+\"'>\"+newGroup+\"</option>\").val(newGroup);
        $('#{$name}-group-select').change();
      }else{
        $('#{$name}-error').show();
      }
    });";
    $this->addJQuery($addScript);

    //Add the change script which will run when the select box value changes.
    $changeScript = "$('#{$name}-group-select').change(function(){
      var group = $(this).val();
      $('.{$name}-group-row').each(function(){
        //$(this).hide();
        $(this).attr('style','display:none;');
      });
      $(\".{$name}-\"+group+\"-row\").each(function(){
        $(this).show();
      });
      $('#{$name}-error').hide();
    }).change();";
    $this->addJQuery($changeScript);
  }

  /**
   * AdminView::hasToolTip()
   *
   * @param string constantName : constant name
   *
   * @return mixed : false or the tooltip constant name.
   */
  static protected function hasToolTip($constantName){
    if(!defined($constantName."_tooltip")){
      return false;
    }else{
      return $constantName."_tooltip";
    }
  }

  static function print_label($name, $prefix='-input', $required=false, $classprefix='',$suffix=''){
    global $_SHOP;
     $rtn ='';
    if ($toolTipName = self::hasToolTip(strtolower($name))) {
      $toolTipText = con($toolTipName);//con(strtolower($name)).'~'.
      $rtn = "&nbsp;<img src=\"{$_SHOP->images_url}help.png\" width=16 alt=\"".con('help')."\" id='{$toolTipName}' class='has-tooltip' title='{$toolTipText}'>";
    }

    $style = 'class="input '.$classprefix.((!empty($required))?' required"':'"');
    return '<label id="'.$name.'-label" for="'.$name. $prefix.'" '.$style.'>'.$suffix.con(strtolower($name)).'</label>'.$rtn;
  }

  function print_field ($name, $data, $prefix='',$fieldtype='') {
      $prefix= (!is_string($prefix))?'':$prefix;
      echo "<tr id='{$name}-tr'><td class='admin_name' width='".self::$labelwidth."'>" , self::print_label($name,'',false,'',$prefix) , "</td>
          <td class='admin_value'>";
    $data = (is_array($data))?$data[$name]:$data;
    if ($fieldtype == 'valuta') {
      echo valuta($data);
    } elseif ($fieldtype == 'date') {
       echo formatAdminDate($data);
    } elseif ($fieldtype == 'time') {
      echo formatAdminTime($data);
    } elseif ($fieldtype == 'datetime') {
      echo formatAdminDatetime($data);
    } else {
       echo $data;
    }
    echo "</td></tr>\n";
  }

  function print_field_o ($name, $data, $prefix='') {
    if (!empty($data[$name])) {
        $this->print_field($name, $data, $prefix);
    }
  }

  function _check ($name,$main,$data){
    if (is_object($main)) {
      $chk=($main->$name==$data[$name])?'checked':'';
      return "<input title=".con('reset_to_main')." type='checkbox' id='$name"."_reset_chk' name='$name"."_chk' class='reset_to_main' value=1 $chk align='middle' style='border:0px;'> ";
    }
    return $main;
  }

  function check_required($name, $model) {

    If (is_bool($model)) {
      return $model;
    } elseif(is_object($model)) {
      return $model->isMandatory($name);
    }
    return false;
  }

  function print_input ($name, $data, $required= false, $size = 30, $max = 100, $suffix = '', $arrPrefix='', $type = 'text') {
    $suffix = self::_check($name, $suffix, $data);
    if($arrPrefix <> ''){
      $prefix = $arrPrefix."[$name]";
    }else{
      $prefix = "{$name}";
    }

    if ($type == 'number') $step = 1;
    else if ($type == 'decimal')  {
    	$step = '0.1';
    	$type = 'number';
    }
    else $step = '';
   if (is_array($data)) {
     $data = $data[$name];
   }
    echo "<tr id='{$name}-tr' ><td class='admin_name'  width='".self::$labelwidth."'>" . self::print_label($name,'-input',self::check_required($name, $required),'',$suffix) . "</td>
          <td class='admin_value'><input id='{$name}-input' type='{$type}' step='{$step}' name='{$prefix}' value='" . htmlspecialchars(is($data,''), ENT_QUOTES) . "' size='{$size}' maxlength='{$max}'>
          ".printMsg($name)."
          </td></tr>\n";
  }

  function print_password ($name, &$data, $err, $size = 30, $max = 100, $suffix = '', $arrPrefix='') {
    $suffix = self::_check($name, $suffix,$data);
    if($arrPrefix <> ''){
      $prefix = $arrPrefix."[$name]";
    }else{
      $prefix = "{$name}";
    }
    echo "<tr id='{$name}-tr' ><td class='admin_name'  width='".self::$labelwidth."'>" . self::print_label($name,'-input',self::check_required($name, $err),'',$suffix) . "</td>
          <td class='admin_value'><input id='{$name}-input' type='password' name='$prefix'  size='$size' maxlength='$max'>
          ".printMsg($name)."
          </td></tr>\n";// Removed the value='" . htmlspecialchars(is($data[$name],''), ENT_QUOTES) . "' here for savety resonse.
  }

    function print_checkbox ($name, &$data, $err, $size = '', $max = '')
    {
      $suffix = '';//self::_check($name, $suffix,$data);
      $sel0 = $sel1 = '';
      if (empty($data[$name]) || $data[$name] == false) {
        $sel0='checked';
      	$data[$name] = false;
      } else {
        $sel1='checked';
      }
      echo "<tr id='{$name}-tr'><td class='admin_name'>" . self::print_label($name,($data[$name]?'-yes':'-no'),self::check_required($name, $err),'',$suffix) . "</td>

          <td class='admin_value'>
            <input type='radio'  id='{$name}-no' name='$name' value=0 $sel0><label for='{$name}-no'>".con('no')."</label>&nbsp;
            <input type='radio'  id='{$name}-yes' name='$name' value=1 $sel1><label for='{$name}-yes' >".con('yes')."</label>

          ".printMsg($name)."
          </td></tr>\n";
    }

    function print_area ($name, &$data, $err, $rows = 6, $cols = 50, $suffix = '') {
      $suffix = self::_check($name, $suffix,$data);
      echo "<tr id='{$name}-tr'><td class='admin_name'>" . self::print_label($name, '-textarea',self::check_required($name, $err),'',$suffix) . "</td>
          <td class='admin_value'><textarea id='{$name}-textarea' style='width:100%;' rows='$rows' cols='$cols' name='$name'>" . htmlspecialchars($data[$name], ENT_QUOTES) . "</textarea>
          ".printMsg($name)."
          </td></tr>\n";
    }

    /**
     * AdminView::print_large_area()
     *
     * @param mixed $name
     * @param mixed $data
     * @param mixed $err
     * @param integer $rows
     * @param integer $cols
     * @param string $suffix
     * @param string|array $options ($class depricated) array of options additional options
     * 'escape'=>false, wont escape the html
     * 'class'=>'class-name' CSS Class name
     * @return void
     */
    function print_large_area ($name, &$data, $err, $rows = 20, $cols = 80, $suffix = '', $options='') {
      if(is_array($options)){
        $class = is($options['class'],'');
      	if (is($options['wysiwyg'],false)) {
      		addJquery('$("#'.$name.'-textarea").cleditor({width:"100%", height:"100%"});');
      	}
      }else{$class = $options;}
      $escape = is($options['escape'],true);
      $suffix = self::_check($name, $suffix,$data);
      echo "<tr id='{$name}-tr'><td colspan='2' class='admin_name'>" . self::print_label($name, '-textarea',self::check_required($name, $err),'large',$suffix) . "&nbsp;&nbsp; ".printMsg($name)."</td></tr>
              <tr><td colspan='2' class='admin_value'><textarea id='{$name}-textarea' style='width:100%;' rows='$rows' cols='$cols' id='$name' name='$name' $class>" . ($escape ? htmlspecialchars($data[$name], ENT_QUOTES):$data[$name]) . "</textarea>
              </td></tr>\n";
    }


    /**
     * AdminView::show_button()
     *
     * NEEDS TO BE ECHO'D ON RETURN!
     *
     * returns <a href=$url> ... </a> depending
     * on the settings.
     *
     * @param string url : the url, can also set button type (submit,reset)
     * @param string name : the method of the button, if not recognised will print text instead.
     * @param int type : 1 = text only, 2 = icon (will try to match against $name) 3 = both
     * @param array options : array of additional options.
     *  'disable' => false,
     *  'image'=>"image url",
     *  'style'=>'extra style info here'
     *  'classes'=>'css-class-1 css-class-2'
     *  'tooltiptext'=>''
     *  'showtooltip'=>false
     * @return string
     */
     function show_button($url, $name, $type=1, $options=array() ){

      if(!empt($name,false)){
          return;
      }
      $button = false;
      $text = false;
      $icon = false;
      $iconArr = array(
        'add'=>array('image'=>'add.png'),
        'edit'=>array('image'=>'edit.png'),
        'view'=>array('image'=>'view.png'),
        'list'=>array('image'=>'arrow_left.png'),
        'delete'=>array('image'=>'trash.png'),
        'remove'=>array('image'=>'trash.png'));

      //Find what to show
      if($type===1 || $type >= 3){
        $icon = false;
        $text = $name;
      }
      if(is($options['image'],false)){
        $icon = true;
        $image = $options['image'];
      }elseif($type===2 || $type >= 3){
        foreach($iconArr as $icoNm=>$iconDtl){
          $name2 = strtolower($name);
          if(preg_match('/'.$icoNm.'/',$name2)){
            $icon = $icoNm;
            $image = $iconDtl['image'];
            break;
          };
        }
        if(!$icon){
          $text = $name;
        }
      }
      //Is it a button?
      if($url=='submit' || $url=='reset'|| $url=='button'){
        $button = true;
      }
      //Extra options
      $classes = is($options['classes'],'');
      $style   = is($options['style'],'');
      $idname  = is($options['id'], $name);
      $disabled= is($options['disable'],false);
      $target= is($options['target'],'');
     	$onclick= is($options['onclick'],'');

      if ($target) {
        $target = "target='{$target}'";
      }

      if(!$icon){
        $classes .= " admin-button-text";
      }
      $disClass = ''; $disAtr = '';
      if($disabled){
        $disAtr = " disabled='disabled' ";
        $disClass = " ui-state-disabled ";
        $url = '';
      }
      //Tooltip stuff
      $toolTipName = self::hasToolTip($name);
      $hasTTClass = 'has-tooltip';
      $toolTipText = con($toolTipName);
      if(is($options['showtooltip'])===false){
        $hasTTClass = '';
        $title = con($name);

      }elseif(empt($options['tooltiptext'],false)){
        $toolTipText = $options['tooltiptext'];
        $toolTipName = empt($toolTipName,$name."_tooltip");

      }elseif(!empt($toolTipName,false)){
        $hasTTClass = '';
        $title = con($name);
      }

      $alt     = is($options['alt'],is($title,con($name)));
      if ($alt) {
        $alt = "alt='{$alt}'";
      }
      $rtn = "";
      if ($onclick) {
      	$alt .= ' onclick= "'.($onclick).'"';
      }
      //If image bolt on image css for button
      if($icon && $image && $text){ $css = 'admin-button-icon-left'; }else{ $css = ''; }

      if(!$button){
        $rtn .= "<a id='{$idname}' {$target} class='{$hasTTClass} admin-button ui-state-default {$css} ui-corner-all link {$classes} {$disClass}' style='{$style}' href='".empt($url,'#')."' title='{$title}' {$alt}>";
      }else{
        $rtn .= "<button $disAtr type='{$url}' name='{$name}' id='{$idname}' class='{$hasTTClass} admin-button ui-state-default {$css} ui-corner-all link {$classes} {$disClass}' style='{$style}' {$alt}>";
      }
      if($icon && $image && $text){
        $rtn .= " <span class='ui-icon' style='background-image:url(\"../images/{$image}\"); background-position:center center; margin:-8px 5px 0 0; top:50%; left:0.6em; width:16px; height:16px;  position:absolute;' title='{$title}' ></span>";
      }elseif($icon && $image){
        $rtn .= " <span class='ui-icon' style='background-image:url(\"../images/{$image}\"); background-position:center center; width:16px; height:16px;' title='{$title}' ></span>";
      }
      if($text){
        $rtn .= con($name);
      }
       //Add on the Tooltip div for the text
      if(!empty($hasTTClass)){
        $rtn .= "<div id='{$toolTipName}' style='display:none;'>{$toolTipText}</div>";
      }
      if(!$button){
        $rtn .= "</a>";
      }else{
        $rtn .= "</button>";
      }


      return $rtn;
    }

    function print_set ($name, &$data, $table_name, $column_name, $key_name, $file_name) {
        $ids = explode(",", $data);
        $set = array();
        if (!empty($ids) and $ids[0] != "") {
            foreach($ids as $id) {
                $query = "select $column_name as id from $table_name where $key_name="._esc($id);
                if (!$row = ShopDB::query_one_row($query)) {
                    // user_error(shopDB::error());
                    return 0;
                }
                $row["id"] = $id;
                array_push($set, $row);
            }
        }
        echo "<tr id='{$name}-tr'><td class='admin_name'>" . self::print_label($name, '-a',false,'','') . "</td>
    <td class='admin_value'>";
        if (!empty($set)) {
            foreach ($set as $value) {
                echo "<a id='{$name}-a' class='link' href='$file_name?action=view&$key_name=" . $value["id"] . "'>" . $value[$column_name] . "</a><br>";
            }
        }
        echo "</td></tr>\n";
    }

  function print_url ($name, &$data, $prefix = ''){
    	$suffix = self::_check($name, $prefix, $data);

    echo "<tr id='{$name}-tr'><td class='admin_name' width='".self::$labelwidth."'>" . self::print_label($name,'-a',false,'',$suffix) . "</td>
    <td class='admin_value'>
    <a id='{$name}-a' href='{$data[$name]}' target='blank'>{$data[$name]}</a>
    </td></tr>\n";
  }

  function print_datetime ($name, &$data, $err, $suffix = '', $arrPrefix='') {
      global $_SHOP;
    $size = 20;
    $max  = 20;
      $suffix = self::_check($name, $suffix,$data);
    if($arrPrefix <> ''){
      $prefix = $arrPrefix."[$name]";
    }else{
      $prefix = "{$name}";
    }
    $value = is($data[$name],'');
    if ($value && $value != '0000-00-00 00:00:00') {
      $value = date('o-m-d H:i:00', strtotime($value));
    } else $value = '';
    // 'y' for short year, 'Y' for full year,
    // 'o' for month, 'O' for two-digit month,
    // 'n' for abbreviated month name, 'N' for full month name,
    // 'd' for day, 'D' for two-digit day,
    // 'w' for abbreviated day name and number, 'W' for full day name and number),
    // 'h' for hour, 'H' for two-digit hour,
    // 'm' for minute, 'M' for two-digit minutes,
    // 's' for seconds, 'S' for two-digit seconds,
    // 'a' for AM/PM indicator (omit for 24-hour)

      echo "
          <tr id='{$name}-tr'><td class='admin_name'>" . self::print_label($name,'-datetime-edit',self::check_required($name, $err),'',$suffix) . "</td>
            <td class='admin_value'>
              <input id='{$name}-datetime-input' type='hidden' title='for debug only'  class='hiddendate' readonly=readonly name='{$prefix}' value='" . htmlspecialchars($value, ENT_QUOTES) . "'>
              <input id='{$name}-datetime-edit' type='text' readonly=readonly name='{$prefix}_dummy' value='' size='{$size}' maxlength='{$max}'>
              ".printMsg($name)."
            </td>
          </tr>\n";
    $this->addJQuery("
      $(\"#{$name}-datetime-input\").datetimepicker({dateFormat:  'yy-mm-dd', timeFormat : 'HH:mm:00',spinnerImage: ''});
      inputField =$(\"#{$name}-datetime-edit\").datetimepicker({
      showOn: 'button',
      buttonText: '',
      buttonImageOnly: true,
      buttonImage: '',
      	hourGrid: 3,
      	stepMinute: 5,
      	minuteGrid: 10,
       dateFormat:  'dd-mm-yy',
       timeFormat : 'HH:mm',
        appendText: '(dd-mm-yyyy hh:mm)',
   		  altField : '#{$name}-datetime-input',
			  altFormat: 'yy-mm-dd',
			  altTimeFormat: 'HH:mm:00',
	      altFieldTimeOnly: false
      });
      altfield = $(\"#{$name}-datetime-input\");
       d = (altfield || altfield.val())? altfield.val():null;
 //     alert(d);
      if (d) {
        $(\"#{$name}-datetime-edit\").datetimepicker('setDate',  $('#{$name}-datetime-input').datetimepicker('getDate'));
      }
         inputField.change(function(){
            if (!$(this).val()) $(\"#{$name}-datetime-input\").val('');
         });
      ");
			}

    function print_time ($name, &$data, $err, $suffix = '', $arrPrefix='') { // Lxsparks edited to allow for midday/midnight times
      global $_SHOP;
      $size = 5;
      $max  = 5;
      $suffix = self::_check($name, $suffix, $data);
      if($arrPrefix <> ''){
        $prefix = $arrPrefix."[$name]";
		} else {
        $prefix = "{$name}";
		 }
      $value = is($data[$name],'');
      if ($value && $value != '00:00:00') {
        $value = date('H:i:00', strtotime($value));
      } else $value = '';
  //    var_dump($value);
      // 'y' for short year, 'Y' for full year,
      // 'o' for month, 'O' for two-digit month,
      // 'n' for abbreviated month name, 'N' for full month name,
      // 'd' for day, 'D' for two-digit day,
      // 'w' for abbreviated day name and number, 'W' for full day name and number),
      // 'h' for hour, 'H' for two-digit hour,
      // 'm' for minute, 'M' for two-digit minutes,
      // 's' for seconds, 'S' for two-digit seconds,
      // 'a' for AM/PM indicator (omit for 24-hour)

      echo "<tr id='{$name}-tr'><td class='admin_name'>" . self::print_label($name,'-time-edit',self::check_required($name, $err),'',$suffix) . "</td>
           <td class='admin_value'>
                 <input id='{$name}-time-input' type='hidden' title='for debug only' class='hiddendate' readonly=readonly name='{$prefix}' value='" . htmlspecialchars($value, ENT_QUOTES) . "'>
                 <input id='{$name}-time-edit' type='text' readonly=readonly name='{$prefix}_dummy'  size='{$size}' maxlength='{$max}'>
         ".printMsg($name)."
         </td></tr>\n";
      $this->addJQuery("
         inputField = $(\"#{$name}-time-edit\").timepicker({
        showOn: 'button',
        buttonText: '',
        buttonImageOnly: true,
        buttonImage: '',
      	hourGrid: 3,
      	stepMinute: 5,
      	minuteGrid: 10,
      	dateFormat:'',
            timeFormat:  'HH:mm',
           appendText: '(hh:mm)',
      		  altField : '#{$name}-time-input',
			  altTimeFormat: 'HH:mm:00',
			  timeOnly: true,
        	altFieldTimeOnly: false
         });
         altfield = $(\"#{$name}-time-input\");
         if (altfield && altfield.val()!='') {
               $(\"#{$name}-time-edit\").timepicker('setDate', '01/01/2012 '+ altfield.val());
             }
         inputField.change(function(){
            if (!$(this).val()) $(\"#{$name}-time-input\").val('');
         });
         ");
    }

    function print_date ($name, &$data, $err, $suffix = '', $arrPrefix='') {
      global $_SHOP;
      $size = 11;
      $max  = 11;
      $suffix = self::_check($name, $suffix,$data);
      if($arrPrefix <> ''){
        $prefix = $arrPrefix."[$name]";
      }else{
        $prefix = "{$name}";
    }
      $value = is($data[$name],'');
      if ($value && $value != '0000-00-00') {
        $value = date('o-m-d', strtotime($value));
      } else $value = '';
      // 'y' for short year, 'Y' for full year,
      // 'o' for month, 'O' for two-digit month,
      // 'n' for abbreviated month name, 'N' for full month name,
      // 'd' for day, 'D' for two-digit day,
      // 'w' for abbreviated day name and number, 'W' for full day name and number),
      // 'h' for hour, 'H' for two-digit hour,
      // 'm' for minute, 'M' for two-digit minutes,
      // 's' for seconds, 'S' for two-digit seconds,
      // 'a' for AM/PM indicator (omit for 24-hour)

      echo "<tr id='{$name}-tr'><td class='admin_name'>" . self::print_label($name,'-date-edit',self::check_required($name, $err),'',$suffix) . "</td>
    <td class='admin_value'>
                 <input id='{$name}-date-input' type='hidden' title='for debug only' class='hiddendate' readonly=readonly name='{$prefix}' value='" . htmlspecialchars($value, ENT_QUOTES) . "'>
                 <input id='{$name}-date-edit' type='text' readonly=readonly name='{$prefix}_dummy' value='' size='{$size}' maxlength='{$max}'>
         ".printMsg($name)."
    </td></tr>\n";
      $this->addJQuery("
        inputField = $(\"#{$name}-date-edit\").datepicker({
        showOn: 'button',
        buttonText: '',
        buttonImage: '',
        buttonImageOnly: true,
        showButtonPanel: false,
           dateFormat:  'dd-mm-yy',
           //appendText: '(dd-mm-yyyy)',
      		  altField : '#{$name}-date-input',
			  altFormat: 'yy-mm-dd'
         });
         altfield = $(\"#{$name}-date-input\");
         d = $.datepicker.parseDate(\"yy-mm-dd\",  altfield || altfield.val()?altfield.val():null);
         $(\"#{$name}-date-edit\").datepicker('setDate',  d);
         inputField.change(function(){
            if (!$(this).val()) $(\"#{$name}-date-input\").val('');
        }); ");
    }

  function Set_time($name, & $data, & $err) {
    global $_SHOP;
    if ( (isset($data[$name.'-h']) and strlen($data[$name.'-h']) > 0) or
    (isset($data[$name.'-m']) and strlen($data[$name.'-m']) > 0) ) {
      $h = $data[$name.'-h'];
      $m = $data[$name.'-m'];
      if ( !is_numeric($h) or $h < 0 or $h >= $_SHOP->input_time_type ) {
        $err[$name] = invalid;
      } elseif ( !is_numeric($m) or $h < 0 or $m > 59 ) {
        $err[$name] = invalid;
      } else {
        if (isset($data[$name.'-f']) and $data[$name.'-f']==='PM') {
          $h = $h + 12;
        }
        $data[$name] = "$h:$m";
      }
    }
  }

  function set_date($name,&$data, $err) {
    if ( (isset($data["$name-y"]) and strlen($data["$name-y"]) > 0) or
      (isset($data["$name-m"]) and strlen($data["$name-m"]) > 0) or
    (isset($data["$name-d"]) and strlen($data["$name-d"]) > 0) ) {
      $y = $data["$name-y"];
      $m = $data["$name-m"];
      $d = $data["$name-d"];

      if ( !checkdate($m, $d, $y) ) {
        adderror($name,'invalid');
      } else {
        $data[$name] = "$y-$m-$d";
      }
    }

  }

    function print_select ($name, &$data, $err, $opt, $actions=''){
        // $val=array('both','rows','none');
        if (!empty($data[$name])) $sel[$data[$name]] = " selected ";

        echo "<tr id='{$name}-tr'><td class='admin_name'  width='".self::$labelwidth."'>" . self::print_label($name,'-select',self::check_required($name, $err)) . "</td>
              <td class='admin_value'>
               <select id='{$name}-select' name='$name' $actions>\n";

        foreach($opt as $v) {
            echo "<option value='$v'".is($sel[$v],'').">" . con($name . "_" . $v) . "</option>\n";
        }

        echo "</select>".printMsg($name)."
              </td></tr>\n";
    }

    function print_select_assoc ($name, &$data, $err, $opt, $actions='', $mult = false) {
        // $val=array('both','rows','none');
        @ $sel[$data[$name]] = " selected ";
        $mu = '';
        if ($mult) {
            $mu = 'multiple';
        }
        echo "<tr id='{$name}-tr'><td class='admin_name'  width='".self::$labelwidth."'>" . self::print_label($name,'-select',self::check_required($name, $err)) . "</td>
                <td class='admin_value'> <select id='{$name}-select' name='{$name}'  {$actions} {$mu}>\n";

        foreach($opt as $k => $v) {
          if (is_array($v)) {
            echo "<optgroup label='{$k}'>\n";
            foreach($v as $v2 => $n2) {
//              if($nokey) {
//                $v2 = $n2;
 //             }
              $cap =con($n2) ;//$prefix.($prefix or $con)?con($prefix.$n2):$n2;
              echo "<option value='". htmlspecialchars($v2)."' ".is($sel[$v2],'').">" .  htmlspecialchars($cap) . "</option>\n";
            }
            echo "</optgroup>\n";
            continue;
          }
          echo "<option value='$k'".is($sel[$k],"").">".con($v)."</option>\n";
        }

        echo "</select>".printMsg($name)."</td></tr>\n";
    }

    function print_color ($name, &$data, $err) {
        echo "<tr id='{$name}-tr'><td class='admin_name'  width='".self::$labelwidth."'>" . self::print_label($name,'-color',self::check_required($name, $err)) . "</td>
        <td class='admin_value'>";
          $act = is($data[$name],'#FFFFFF');
        echo "<input type='hidden' id='{$name}_text' name='$name' value='$act'>\n
      		<div id='{$name}_color' class='colorSelector'><div style='background-color: $act'></div></div>\n";
       echo "<script>
              $('#{$name}_color').ColorPicker({
								color: '{$act}',
								onShow: function (colpkr) {
									$(colpkr).fadeIn(500);
									return false;
								},
								onHide: function (colpkr) {
									$(colpkr).fadeOut(500);
									return false;
								},
								onChange: function (hsb, hex, rgb) {
									$('#{$name}_color div').css('backgroundColor', '#' + hex);
									$('#{$name}_text').val('#' + hex);
                }
							});</script>";
       echo "<div style=''>".printMsg($name)."</div></td></tr>\n";
    }

    function view_file ($name, &$data, $err, $type = 'img', $prefix = '') {
        global $_SHOP;
        $suffix = self::_check($name, $prefix, $data);

        if ($data[$name]) {
            $src = self::user_url($data[$name]);
            echo "<tr id='{$name}-tr'><td class='admin_name'  width='".self::$labelwidth."'>" . self::print_label($name,'',self::check_required($name, $err),'',$suffix) . "</td>";
            if ($type == 'img') {
              echo "<td class='admin_value'>";
           		if(file_exists(ROOT.'files'.DS.$data[$name])){
           			list($width, $height, $type, $attr) = getimagesize(ROOT.'files'.DS.$data[$name]);
      					if (($width>$height) and ($width > 300)) {
      						$attr = "width='300'";
      					} elseif ($height > 250) {
      						$attr = "height='250'";
      					}
      					echo "<img $attr src='$src'>";
           		}else{
           			echo "<strong>".con("file_not_exsist")."</strong>";
           		}
            } else {
                echo "<td class='admin_value'><a class='link' href='$src'>{$data[$name]}</a>";
            }
            echo "</td></tr>\n";
        }
    }

    function print_file ($name, &$data, $err, $type = 'img', $suffix = ''){
      global $_SHOP;
      $suffix = self::_check($name, $suffix,$data);
    	$require = self::check_required($name, $err);
        if (!isset($data[$name]) || empty($data[$name])) {
            echo "\n<tr id='{$name}-tr'><td class='admin_name'  width='".self::$labelwidth."'>" . self::print_label($name,'-file',$require,'',$suffix) . "</td>
            <td class='admin_value'><input type='file' id='{$name}-file' name='{$name}'>".printMsg($name)."</td></tr>\n";
        } else {
            $src = self::user_url($data[$name]);

            echo "<tr id='{$name}-tr'><td class='admin_name' rowspan=".($require?2:3)." valign='top' width='".self::$labelwidth."'>" . self::print_label($name,'',$require,'',$suffix) . "</td>
            <td class='admin_value'>";

            if ($type == 'img') {
           		if(file_exists($_SHOP->files_dir.$data[$name])){
           			list($width, $height, $type, $attr) = getimagesize($_SHOP->files_dir.$data[$name]);
      					if (($width>$height) and ($width > 300)) {
      						$attr = "width='300'";
      					} elseif ($height > 250) {
      						$attr = "height='250'";
      					}
      					echo "<img $attr src='$src'>";
           		}else{
           			echo "<strong title='$src'>".con("file_not_exsist")."</strong>";//$_SHOP->files_dir.$data[$name]
           		}
            } else {
                echo "<a href='$src'>{$data[$name]}</a>";
            }
            $this->print_hidden ($name, $data);
            $checked = (isset($_REQUEST["remove_{$name}"]))?' checked':'';
            echo "</td></tr>
              <tr>
                <td class='admin_value'><input id='{$name}-file' type='file' name='$name'>".printMsg($name)."</td>
              </tr>";
        	  if (!$require) {
        	  	echo "  <tr>
                <td class='admin_value'><input id='{$name}-checkbox' type='checkbox' name='remove_{$name}' value='1'{$checked}>  " . con('remove_image') . "</td>
              </tr>\n";
        	  }
        }
    }

  function print_countrylist($name, $selected, $err){
  global $_SHOP,  $_COUNTRY_LIST;
    $_SHOP->lang = is($_SHOP->lang,'en');
    if (!isset($_COUNTRY_LIST)) {
      If (file_exists($_SHOP->includes_dir."/lang/countries_". $_SHOP->lang.".inc")){
        include_once("lang/countries_". $_SHOP->lang.".inc");
      }else {
        include_once("lang/countries_en.inc");
      }
    }
    if (is_array($selected)) $selected = $selected[$name];

    if (empty($selected) && $name != 'organizer_country') {
      $selected = $_SHOP->organizer_country;
    }

    echo "<tr id='{$name}-tr'><td class='admin_name'  width='".self::$labelwidth."'>" . self::print_label($name,'_select',self::check_required($name, $err)) . "</td>
            <td class='admin_value'><select id='{$name}-select' name='$name'>";
    $si[$selected]=' selected';
    echo "<option value=''>".con("please_select")."</option>";
    foreach ($_COUNTRY_LIST as $key=>$value){
      $si[$key] = (isset($si[$key]))?$si[$key]:'';
      echo "<option value='$key' {$si[$key]}>$value</option>";
    }
    echo "</select>". printMsg($name). "</td></tr>\n";;
  }

  function getCountry($val){
    global $_SHOP, $_COUNTRY_LIST;
    $val=strtoupper($val);

    if (!isset($_COUNTRY_LIST)) {
      If (file_exists($_SHOP->includes_dir."/lang/countries_". $_SHOP->lang.".inc")){
        include_once("lang/countries_". $_SHOP->lang.".inc");
      }else {
        include_once("lang/countries_en.inc");
      }
    }

    return $_COUNTRY_LIST[$val];
  }

  function getCountries(){
    global $_SHOP, $_COUNTRY_LIST;
    if (!isset($_COUNTRY_LIST)) {
      If (file_exists($_SHOP->includes_dir."/lang/countries_". $_SHOP->lang.".inc")){
        include_once("lang/countries_". $_SHOP->lang.".inc");
      }else {
        include_once("lang/countries_en.inc");
      }
    }

    return $_COUNTRY_LIST;
  }

  function user_url($data) {
      global $_SHOP;
      return $_SHOP->files_url . $data;
  }

  function user_file ($path){
      global $_SHOP;
      return $_SHOP->files_dir .  $path;
  }

  function delayedLocation($url){
      echo "<SCRIPT LANGUAGE='JavaScript'>
            <!-- Begin
                 function runLocation() {
                   location.href='{$url}';
                 }
                 window.setTimeout('runLocation()', 1500);
            // End -->\n";
      echo "</SCRIPT>\n";
  }

  protected function addJQuery($script){
    global $_SHOP;
    $_SHOP->jScript .= "\n".$script;
  }

  public function getJQuery(){
    return '';
  }

  protected function hasNewVersion(){
    $result = $this->getLatestVersion();
    if(is_array($result)) {
      if ($result['current'][0]) addNotice('new_version_available');
      return $result['current'][1];
    }else{
      return " - Could not check for new version.";
    }
  }

  protected function getLatestVersion($dlLink=false,$ftu=false,$ftp=false){
    require_once("classes/class.restservice.client.php");
    $rsc = new RestServiceClient('http://cpanel.fusionticket.org/versions/latest.xml');
    $rev = explode(" ",INSTALL_REVISION);
    try{
      if(!empty($ftu) && !empty($ftp)){
        $rsc->josUsername = $ftu;
        $rsc->josPassword = $ftp;
        //print_r(base64_decode($_SHOP->shopconfig_ftpassword));
      }
      $rsc->ftrevision = (int)$rev[1];
      $rsc->ftDownloadNow = ($dlLink)?'true':'false';

      $rsc->excuteRequest();
      if (!$dlLink) {
        $array = $rsc->getArray();//var_dump($array, true);
        if(!isset($array['versions']['version']['0_attr'])){
          return addwarning('Couldnt get version');
        }

        $donor_rev = $array['versions']['version']['1_attr']['order'];
        $donor_ver = $array['versions']['version']['1_attr']['version'];
        $result['donor'] = array($donor_rev, $donor_ver);

        $main_rev  = $array['versions']['version']['0_attr']['order'];
        $main_ver  = $array['versions']['version']['0_attr']['version'];
        $result['main'] = array($main_rev, $main_ver);

        if ( $main_rev > (int)$rev[1]){
          $result['current'] = array(true, "<br> - <span style='color:red;'> There is a new version Available: ".$main_ver."! </span>");
        } elseif ( $donor_rev > (int)$rev[1]){
          $result['current'] = array(false, "<br> - <span style='color:blue;'> There is a new <b>donor</b> verion Available: ".$donor_ver."! </span>");
        } elseif ( $main_rev == (int)$rev[1]){
          $result['current'] = array(false, "");
        } elseif ( $donor_rev == (int)$rev[1]){
          $result['current'] = array(false, " <span style='color:blue;'><b>donor</b> version. </span>");
        } else {
          $result['current'] = array(false, " <span style='color:blue; '> SVN Build </span>");
        }
        return $result;
      } else {
        return $rsc->getResponse();
      }
    }catch(Exception $e){
     // print_r($e->getMessage());
      return false;// " - Could not check for new version.";
    }
  }

  // make tab menus using html tables
  // vedanta_dot_barooah_at_gmail_dot_com

  function PrintTabMenu($linkArray, &$activeTab, $menuAlign="center", $param='tab') {
    Global $_SHOP;

  	$str= "<table width=\"100%\" cellpadding=0 cellspacing=0 border=0  class=\"UITabMenuNav\">\n";
  	$str.= "<tr>\n";
  	if($menuAlign=="right"){
      $str.= "<td width=\"100%\" align=\"left\">&nbsp;</td>\n";
    }
  	foreach ($linkArray as $k => $v){
      if (!is_integer($activeTab)) $activeTab = $v;
      $menuStyle=($v==$activeTab)?"UITabMenuNavOn":"UITabMenuNavOff";
      $str.= "<td valign=\"top\" class=\"$menuStyle\"><img src=\"".$_SHOP->images_url."left_arc.gif\"></td>\n";
      $str.= "<td nowrap=\"nowrap\" height=\"16\" align=\"center\" valign=\"middle\" class=\"$menuStyle\">\n";
      $str.= "      <a class='$menuStyle' href='?{$param}={$v}'>".con($k)."</a>";
      $str.= "</td>\n";
      $str.= "<td valign=\"top\" class=\"$menuStyle\"><img src=\"".$_SHOP->images_url."right_arc.gif\"></td>\n";
      $str.= "<td width=\"1pt\">&nbsp;</td>\n";
    }
  	if($menuAlign=="left"){
      $str.= "<td width=\"100%\" align=\"right\">&nbsp;</td>";
    }
  	$str.= "</tr>\n";
  	$str.= "</table>\n";
	  return $str;
  }

  function get_nav ($page,$count,$condition=''){
    if(!isset($page)){ $page=1; }
    if (!empty( $condition)) {
      $condition .= '&';
    }
    echo "<table border='0' width='$this->width'><tr><td align='center'>";

    if($page>1){
      echo "<a class='link' href='".$_SERVER["PHP_SELF"]."?{$condition}page=1'>".con('nav_first')."</a>";
      $prev=$page-1;
      echo "&nbsp;<a class='link' href='".$_SERVER["PHP_SELF"]."?{$condition}page={$prev}'>".con('nav_prev')."</a>";
    } else {
      echo con('nav_first');
      echo con('nav_prev');
    }

    $num_pages=ceil($count/$this->page_length);
    for ($i=floor(($page-1)/10)*10+1;$i<=min(ceil($page/10)*10,$num_pages);$i++){
      if($i==$page){
        echo "&nbsp;<b>[$i]</b>";
      }else{
        echo "&nbsp;<a class='link' href='".$_SERVER["PHP_SELF"]."?{$condition}page=$i'>$i</a>";
      }
    }
    echo "&nbsp;";
    if($page<$num_pages){
      $next=$page+1;
      echo "<a class='link' href='".$_SERVER["PHP_SELF"]."?{$condition}page=$next'>".con('nav_next')."</a>";
      echo "<a class='link' href='".$_SERVER["PHP_SELF"]."?{$condition}page=$num_pages'>". con('nav_last')."</a>";
    }  else {
      echo con('nav_next')."\n";
      echo con('nav_last')."\n";
    }
    echo "</td></tr></table>";
  }


  function print_hidden ($name, $data=''){
    echo "<input type='hidden' id='$name' name='$name' value='" . ((is_array($data))?$data[$name]:$data) . "'>\n";
  }

   function fill_images() { //Moved from view.events.php as its now used on more than one page
		$img_pub['pub'] = array(
            "src" => '../images/grun.png',
            'title' => con('icon_unpublish'),
            'alt' => con('icon_unpublish_alt'),
            'link' => "view_event.php?action=unpublish&cbxEvents[]=" );

		$img_pub['unpub'] = array(
            "src" => '../images/rot.png',
            'title' => con('icon_publish'),
            'alt' => con('icon_publish_alt'),
            'link' => "view_event.php?action=publish&cbxEvents[]=" );

		$img_pub['nosal'] = array(
            "src" => '../images/grey.png',
            "title" => con('icon_nosal'),
            "alt" => con('icon_nosal_alt'),
            "link" => "view_event.php?action=publish&cbxEvents[]=" );

		$img_pub['webpub'] = array(
            "src" => 'shop_web_pub.png',
            "title" => con('icon_webunpub'),
            "alt" => con('icon_webunpub_alt'),
            "link" => "view_event.php?action=unpub_web&cbxEvents[]=" );

		$img_pub['webunpub'] = array(
            "src" => 'shop_web_unpub.png',
            "title" => con('icon_webpub'),
            "alt" => con('icon_webpub_alt'),
            "link" => "view_event.php?action=pub_web&cbxEvents[]=" );

		$img_pub['pospub'] = array(
            "src" => 'shop_pos_pub.png',
            "title" => con('icon_posunpub'),
            "alt" => con('icon_pospub_alt'),
            "link" => "view_event.php?action=unpub_pos&cbxEvents[]=" );

		$img_pub['posunpub'] = array(
            "src" => 'shop_pos_unpub.png',
            "title" => con('icon_pospub'),
            "alt" => con('icon_pospub_alt'),
            "link" => "view_event.php?action=pub_pos&cbxEvents[]=" );

		return $img_pub;
	}
}


?>