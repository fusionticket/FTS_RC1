var user_id = 0;
var loadUser = function(){
	$('#search_user').hide();

	$('#user_data').show();

 tblUser = $('#users_table').dataTable({
    //   bProcessing: true,
    bServerSide: true,
    sAjaxSource: "ajax.php?x=users",
    sServerMethod: "POST",
    fnServerParams: function ( aoData ) {
      aoData.push( { "name":  'action', "value": "UserSearch" } );
      $("#user_data :input").each(function() {
        if ($(this).attr("name") != 'user_id') {
          aoData.push( { "name":  this.name, "value": $(this).val() } );
        }
      });

    },
    fnGridComplete:  function(aoData){
         user_id = 0;
    },
    sScrollY: '216px',
    bJQueryUI: true,
    sDom: '<l<rt>p>',
//    bSort: false,
    bAutoWidth: true,
    oLanguage: {
      sEmptyTable: 'No data available in table',
      sLoadingRecords : 'Loading...',
      sZeroRecords:  'No matching records found.'
    },
    bPaginate: false,
    bScrollCollapse: false,
    aoColumns : [ {'sWidth':'152px', "mData": "user_data" }, {'sWidth':'200px', "mData": "user_phone"  }, {'sWidth':'200px', "mData": "user_city"  },{ "mData": "user_email" , 'sWidth':'202px'}]
  });

/*
  var userkeys = new KeyTable( {
    "table": document.getElementById("users_table"),
    "datatable": tblUser,
    "form": true
  } );

  userkeys.event.focus( null, 0, function( nNode ) {
    nNode.click();
  } );

*/

  $("#users_table tbody tr").live('click', function( e ) {
    tblUser.$('tr.row_selected').removeClass('row_selected');
    $(this).addClass('row_selected');
    user_id = this.id;
  });
  $("#users_table tbody tr").live('dblclick', function( e ) {
    tblUser.$('tr.row_selected').removeClass('row_selected');
    $(this).addClass('row_selected');
    user_id = this.id;
    var dialog = $("#search-dialog");
    buttons = dialog.dialog('option', 'buttons');
    buttons['Ok'].apply(dialog);
    });

	$('#user_info_none').click(function(){
    	$("#user_data :input").each(function() {
        	$(this).val('');
      	});
    	$('#search_user').hide();
    	$('#user_data').hide();
      $('#user_id').val(-1);
//	    setRequirements([]);
    });

	$('#user_info_search').click(function(){
    	$('#search_user').show();
    	$('#user_data').show();
    	$('form#pos-user-form').unbind('keypress');
    	$('form#pos-user-form').bind('keypress',function(e){
            if(e.which == 13){
                $('#search_user').click();
             }});

	    if ($('#user_id').val() <=0) {
	    	$('#user_id').val(-2);
     	}
 // 	  setRequirements(["user_firstname", "user_lastname", "user_email", "user_phone"]);
    });

	$('#user_info_new').click(function(){
    	$('#search_user').hide();
    	$('#user_data').show();
    	$('form#pos-user-form').unbind('keypress');
    	$('form#pos-user-form').bind('keypress',function(e){
            if(e.which == 13){
                $('#pos-user-form').submit();
             }});
      	if (($('#user_id').val() <=0) || confirm('Are you sure you want to create a new user?')) {
        	$("#user_data :input").each(function() {
           		$(this).val('');
        	});
        	$('#user_id').val(0);
      	} else {
        	$('#user_info_search').change();
        	$('#user_info_search').click();
      	}
//	  setRequirements(cartData.requered_fields);

    });

	$('form#pos-user-form').unbind('keypress');
	$('form#pos-user-form').bind('keypress',function(e){
        if(e.which == 13){
            $('#pos-user-form').submit();
         }});

	$("#search-dialog").dialog({
		bgiframe: false,
		autoOpen: false,
		position:['middle',50],
		height: 'auto',
		width: '775',
		modal: true,
		resizable  : false,
		buttons: {
    		'Cancel': function() {
				$(this).dialog('close');
			 },
  		  'Ok': function() {
         	if (user_id != 0) {
     			 	ajaxQManager.add({
  					type:		"POST",
  					url:		"ajax.php?x=getuser",
  					dataType:	"json",
  					data:		{"pos":true,"action":"UserData",'user_id':user_id},
  					success:function(data, status){
                			$.each(data.user, function(i,item){
                   			$("#"+i).val(item);
                			});
   						$("#search-dialog").dialog('close');
             			}
  			  	});
          } else {
            alert('You need to select a user first.');
          }
		  	}
		}
	});

  $('#search_user').click(function() {
	  if ($("#user_firstname").val() != "" && $("#user_lastname").val() != "" || $("#user_email").val() != "" || $("#user_phone").val() != "") {
        tblUser.fnDraw(true);
        $("#search-dialog").dialog('open');
     	}
	  else alert('Please provide either of the following to search:\nFull name\nEmail Address\nPhone Number');
	});
}

function array_length(arr) {
	var length = 0;
	for(val in arr) {
	    length++;
	}
	return length;
}

function setRequirements(requered_fields) {
/* //  if (typeof requered_fields == 'boolean') {
    if (false) { // && requered_fields
      $('#user_info_none-td').hide();
      if($('#user_info_none').is(':checked')){
          $('#user_info_new').click();
      }
    } else {
      $('#user_info_none-td').show();
      $('#user_info_none').click();

    }
 }
  if (requered_fields instanceof Array) {
    $("#user_data :input").each(function() {
      var require = jQuery.inArray(this.name,requered_fields)
      label =     $('#'+this.id+'-label');
      $(this).rules('add',{ required: require >= 0 } );

      if (require >= 0) {
        label.addClass('required');
      }else {
        label.removeClass('required');
      }
    });
  }
  */
}