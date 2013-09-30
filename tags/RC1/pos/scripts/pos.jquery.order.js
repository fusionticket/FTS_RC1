var catData = new Object();
var refreshTimer = null;
var eventData = new Object();
var cartData = new Object();
var timerid = 0;
var loadOrder = function(){

  orderDialogs();

  tblCart = $('#cart_table').dataTable({
 //   bProcessing: true,
    bServerSide: true,
    sAjaxSource: "ajax.php?x=cart",
    sServerMethod: "POST",
    fnServerParams: function ( aoData ) {
      aoData.push( { "name":  'action', "value": "CartInfo" } );
      aoData.push( { "name":  'handling_id', "value":  $("input[type=radio][name=handling_id]:checked").val()});
      if($("input[type=checkbox][name='no_fee']").is(":checked")){
        aoData.push( { "name":   'no_fee', "value":  1});
      }else{
        aoData.push( { "name": 'no_fee',"value":     0});
      }
      if($("input[type=checkbox][name='no_cost']").is(":checked")){
        aoData.push( { "name":   'no_cost', "value":  1});
      }else{
        aoData.push( { "name": 'no_cost',"value":     0});
      }

    },
    sScrollY: '116px',
    bJQueryUI: true,
    sDom: '<l<t>p>',
    bSort: false,
    bAutoWidth: true,
    oLanguage: {
      sEmptyTable: 'No data available in table',
      sLoadingRecords : 'Loading...',
      sZeroRecords:  'No matching records found.'
    },
    bPaginate: false,
    bScrollCollapse: false,
    aoColumns : [ {'sWidth':'260px', "mData": "Event" }, {'sWidth':'260px', "mData": "Tickets"  }, {'sWidth':'80px', "mData": "Total"  },{ "mData": "Expire_in" , 'sWidth':'17px'}],
    fnGridComplete:  function(aoData){
//      $('#cart_table td').addClass('payment_form');
      cartData = aoData.userdata;

      $.each(cartData.handlings,function(index, domElement){
        $(this.index).html(this.value);
      });
      $('#total_price').html(cartData.total);
      $('#cancel').attr("disabled", !cartData.can_cancel);
      $('#checkout').attr("disabled", !cartData.can_order);
      setRequirements(cartData.requered_fields);
      bindCartRemove(); // This listens for cart remove button;
    }
  });

  tblEvent = $('#event_table').dataTable( {
    sAjaxSource: "ajax.php?x=event",
    sServerMethod: "POST",
    bScrollInfinite: false,
    bAutoWidth:false,
    fnServerParams: function ( aoData ) {
      aoData.push( { "name": 'action', "value": "events" } );
    },
    'bJQueryUI': true,
    'sDom': 'rt',
    'sScrollY': '290px',
    'sScrollX': '100%',
    'bSort': false,
    'oLanguage': {
      'sEmptyTable': 'No data available in table',
      'sLoadingRecords' : 'Loading...',
      'sZeroRecords':  'No matching records found.'
    },
    'bPaginate': false,
    'bScrollCollapse': false,
    "aoColumns" : [ {'sWidth': null,  "mData": "grid"}]
  });

  var keys = new KeyTable( {
		"table": document.getElementById("event_table"),
		"datatable": tblEvent,
		"form": true
  });

  keys.event.focus( null, null, function( nNode ) {
		nNode.click();
  });


  $("#event_table tbody tr").live('click', function( e ) {
      tblEvent.$('tr.row_selected').removeClass('row_selected');
      $(this).addClass('row_selected');
      $("#event-id").val(this.id);
      eventIdChange();
  });
  $("#event-search").keyup( function () {
    tblEvent.fnFilter($(this).val());
  } );

  $("#event-search").focus();

  //Start the event listeners
  $(".category_id_radio").live('change',function(){
    var event =  $(getSelected(tblEvent));
    var catId = $('input[name=category_id]:checked').val();
    if ( event.length !== 0  && catId > 0 ){
      var eventId = event.attr('id');
      $("#ft-cat-free-seats").html(catData.categories[catId].free_seats);
      updateSeatChart();
      ajaxQManager.add({
        type:      "POST",
        url:      "ajax.php?x=Discount",
        dataType:   "json",
        data:      {"pos":true,"action":"discounts","event_id": eventId, "cat_id":catId },
        success:function(data, status){
          if(data.enable_discounts){
            $("#discount-select").html("");
            $.each(data.discounts,function(){
              $("#discount-select").append(this.html);
            });
//            $("#discount-name").show();
//            $("#discount-select").show();
          }else{
//            $("#discount-name").hide();
            $("#discount-select").html("<option value='0'>"+lang.discount_none+"</option>"); //hide().
          }
        }
      });
    }
  });
  $('#no_fee').click(function(){ refreshOrder(); });
  $('#no_cost').click(function(){ refreshOrder(); });

  //Make sure all add ticket fields are added to this so when clearing selection
  //All fields are reset.
  $('#clear-button').click(function(){ clearOrder(); });

  //Change to live
  // Refresh (Update Price) on transaction change.
  jQuery("input").live("click change",function(){
    if(jQuery(this).attr('name') == 'handling_id' && jQuery(this).is(':radio')){
      refreshOrder();
    }
  });

  //Creates a auto refreshing function.
  refreshTimer = setInterval(function(){refreshOrder();}, 120000);

  $("#order-form").submit(function(){
    $("#error-message-main").hide();
    $(this).ajaxSubmit({
      data:{pos:"yes",action:"_addToCart"},
      dataType: "json",
      success: function(data, status){
        printMessages(data.messages);
        if(data.status){
          refreshOrder(); //Refresh Cart
          eventIdChange(); //Update ticket info (Free tickets etc)
        }
      }
    });
    return false;
  });

  ////
  // Sends the order information the POS Confirm action in controller/checkout.php
  ////
  $("#checkout").click(function(){
    var userdata = {ajax:"yes",pos:"yes",action:"_PosConfirm"};

    userdata['handling_id'] = $("input[type=radio][name='handling_id']:checked").val();
    if(userdata['handling_id'] === undefined){
      message = new Object();
      message.warning = "<ul><li>Select a payment option.</li></ul>";
      printMessages(message);
      return;
    }

    //If user is being passed check its valid
    if(!$('#user_info_none').is(':checked')){
      if(!$('#pos-user-form').valid()){
        message = new Object();
        message.warning = "<ul><li>Please fill missing fields!</li></ul>";
        printMessages(message);
        return;
      }
    }
    if($("input[type=checkbox][name='no_fee']").is(":checked")){
      userdata['no_fee'] = 1;
    }else{
      userdata['no_fee'] = 0;
    }
    if($("input[type=checkbox][name='no_cost']").is(":checked")){
      userdata['no_cost'] = 1;
    }else{
      userdata['no_cost'] = 0;
    }
    $("#user_data input").each(function() {
      userdata[$(this).attr("name")] = $(this).val();
    });
    $("#user_data select").each(function() {
      userdata[$(this).attr("name")] = $(this).val();
    });
    $("#error-message-main").hide();
    ajaxQManager.add({
      type:      "POST",
      url:      "ajax.php?x=posconfirm",
      dataType:   "json",
      data:      userdata,
      success:function(data, status){
        printMessages(data.messages);
        if(data.status){
        	$("#user_data :input").each(function() {
          	$(this).val('');
        	});
          setUser_id();
          $("#order_action").html(data.html);
          $("#order_action").dialog('open');
          bindCheckoutSubmitForm();
        }
      }
    });
    return false;
  });

  $("#cancel").click(function(){
   $("#error-message-main").hide();
   ajaxQManager.add({
      type:      "POST",
      url:      "ajax.php?x=poscancel",
      dataType:   "HTML",
      data:      {pos:"yes", action:"_PosCancel"},
      success:function(html, status){
        refreshOrder();
      	$("#user_data :input").each(function() {
        	$(this).val('');
      	});
      	setUser_id();
      }
    });

    return false;
  });

  $('#placemap-button').click(function(){
    $('<div>'+ $('#placemap-button').data('image')+'</div>').dialog({    height: 'auto',
    width: 'auto',
    modal: true,
    position:['middle',50],
});
  });
  //Load the events
}

//Load Dialog Functions

var orderDialogs = function(){

  //Seat Chart Popup Box, Gets pushed back into Form on open.
  $("#seat-chart").dialog({
    bgiframe: false,
    autoOpen: false,
  //  maxHeight: 400,
    height: 'auto',
    width: 'auto',
    modal: true,
    position:['middle',50],
    appendTo: "#order-form" ,
    buttons: {
      'Add tickets': function() {
        if( $('#place').val().length) {
          $("#order-form").submit();
        }
        $(this).dialog('close');
      }
    },
  });

  // Opens a dialog to confirm payment
  $("#order_action").dialog({
    bgiframe: false,
    autoOpen: false,
    height: 'auto',
    width: 'auto',
    modal: true,
    position:['middle',50],
    close: function(event, ui) {
      updateEvents();
      if (timerid) {
        clearTimeout(timerid);
        timerid = -1;
      }
      refreshOrder();
    }
  });

}

//End of order startup