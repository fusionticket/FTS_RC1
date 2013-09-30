<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Language" content="English" />
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<title>FusionTicket: Language editor </title>

		<link rel="stylesheet" type="text/css" href="../css/langedit.css" />
    <link rel='stylesheet' type="text/css" href='../css/flick/jquery-ui.css' />
		<link rel="stylesheet" type="text/css" href="../css/ui.jqgrid.css" />
		<link rel="stylesheet" type="text/css" href="../css/ui.multiselect.css" />

		<script type="text/javascript" src="../scripts/jquery/jquery.min.js"></script>
		<script type="text/javascript" src="../scripts/jquery/jquery.ui.js"></script>
		<script type="text/javascript" src="../scripts/jquery/i18n/grid.locale-en.js"></script>
		<script type="text/javascript" src="../scripts/jquery/jquery.jqGrid.min.js"></script>

		<script type="text/javascript">
       $(document).ready(function() {
          var mycombo = $("#combo");
          var lang = mycombo.val();
          var lastsel;
          var mygrid1 = $("#table1").jqGrid({
            url:'langedit.php',
            datatype: 'JSON',
            mtype: 'POST',
            postData: {"load":"grid","lang":lang},
            colNames: ['Define','Default language','Editable language'],
            colModel :[
                {name:'key',   index:'key',   width:135, sortable:false, resizable: false,
                 editable:true, editoptions: {readonly:"true"}  },
                {name:'lang1', index:'lang1', width:425, sortable:false, resizable: false,
                 editable:true, edittype: "textarea", editoptions: {rows:"4",cols:"70",readonly:"true"}  },
                {name:'lang2', index:'lang2', width:425
                , sortable:false, resizable: false,
                 editable:true, edittype: "textarea", editoptions: {rows:"4",cols:"70"} }],
            altRows: true,
            height: 400,
        		hiddengrid : true,
            forceFit   : true,
            rownumbers : false,
        //    pager: '#prowed2',
            rowNum:   -1,
        		footerrow : false,
        		viewrecords: true,
            editurl: "langedit.php?load=save",
            loadError: function(xhr,status,error) {
              alert(status+'-'+error);
            },
            onSelectRow: function(id){
               mygrid1.jqGrid('editGridRow',id,{top: 150, left: "200", width: 600, resize: false, closeAfterEdit:true ,reloadAfterSubmit:false});
            }

          });
   //       mygrid1.navGrid('#prowed2',{del:false,add:false,search:false},{reloadAfterSubmit:false, top: 150, left: "200", width: 600 },{reloadAfterSubmit:false});

         $( "#update_2" ).button({
      			text: true,
      			icons: {
      				primary: "ui-icon-transferthick-e-w"
      			}
      		});

      		$('#update_2').click(function(){
             $.post("langedit.php", { load: "update_2", lang: lang }, function(data){
                if (data== 'done') {
                  mygrid1.trigger('reloadGrid');
                } else alert(data);}, "text");
      		});

        	$( "#new_language" ).button({
      			text: true,
      			icons: {
      				primary: "ui-icon-plusthick"
      			}
      		});
          $('#new_language').click(function(){
            var reply = prompt("Please enter the 2 token code of the new language file?", "xx");
            if (name!=null && name!="") {
              $.post("langedit.php", { load: "new_language", lang: reply }, function(data){
                if (data== 'done') {
                  location.reload();
                } else alert(data);}, "text");
              }
          });

      		$('#combo').change(function(){
      			lang = mycombo.val();
      			mygrid1.jqGrid('setGridParam',{ postData: {"load":"grid","lang":lang} }).trigger('reloadGrid');
      		});

     	  	$( "#sved4" ).button({
      			text: true,
      			icons: {
      				primary: "ui-icon-disk"
      			}
      		});
          jQuery("#sved4").click( function() {
          	var gr = jQuery("#table1").jqGrid('getGridParam','selrow');
          	if( gr != null ) jQuery("#table1").jqGrid('editGridRow',gr,{top: 150, left: "200", width: 600, resize: false, closeAfterEdit:true ,reloadAfterSubmit:false});
          	else alert("Please Select Row");
          });
       });
function checksave(result) {
	if (result.responseText=="") {alert("Update is missing!"); return false;}
	return true;
}

		</script>
	<style>
	#toolbar {
		padding: 10px 4px;
	}
	</style>
 	</head>
	<body>
  <center>
  	<div id="header"  style="width:1002px">
     		<img src='../images/logo.png'  border='0'/>
  			<h2>Language translater</h2>
  	</div>
	<div id="toolbarz" class="ui-widget-header ui-corner-all"  style="width:1000px">
  Select the languagefile: <select id='combo'>
<?Php
    $content = array();
    $dir = dirname(__FILE__)."/../includes/lang";
    echo "<option>Select</option>\n";

    if ($handle = opendir($dir)) {
      while (false !== ($file = readdir($handle))) {
        if ($file != "." && $file != ".." && !is_dir($dir.$file) && preg_match("/^site_(.*?\w+).inc\z/", $file, $matches)&& $matches[1]!='en')
        {
          echo "<option value='{$matches[1]}'>{$file}</option>\n";
        }
      }
      closedir($handle);
    }
?>
</select>
    <button id='new_language'>new</button>
    <button id='update_2'>Update missing</button>
  </div><br>

  <table id="table1"></table>
  <div id="prowed2"></div> <br>
  <div align='right' id="toolbarz" class="ui-widget-header ui-corner-all" style="width:1000px">
  <input type="BUTTON" id="sved4" value="Edit row" />
  </div>

  </body>
</html>
<?php
/*
  onSelectRow: function(rowid,status) {
  //   alert('click');
  if(rowid && rowid!==lastsel){
  jQuery("#sved4").attr("disabled",false);
  mygrid1.jqGrid('restoreRow',lastsel);
  mygrid1.jqGrid('editRow',rowid, true, null, null, null, null, null, null, function(){lastsel=-1;});
  lastsel=rowid;
  }
  }

  ,
  {name:'save', index:'', width:20, sortable:false, resizable: false,
  editable:true, edittype: "custom", editoptions: {custom_element: myelem}
*/
?>