
//Actual Order Functions

var clearOrder = function(){
  $("#cat-select").html(lang.select_event_first);
  $("#discount-select").html("<option value='0'>"+lang.discount_none+"</option>");//.hide();
//  $("#discount-name").hide();
  $("#seat-qty-div").hide();
  $("#show-seats-div").hide();
  $("#seat-chart").html("");
  $("#date-from").val('');
  $("#date-to").val('');
  $("#ft-event-free-seats").html('');
  $("#ft-cat-free-seats").html('');
	$("#user_data :input").each(function() {
  	$(this).val('');
	});
	setUser_id();

  unBindSeatChart();
  updateEvents();
  return false;
}

var setUser_id = function() {
  var user = $('input[name=user_info]:checked').val();


	$("#user_data :input").each(function() {
  	$(this).val('');
	});

  if (user== 0)      { $('#user_id').val(-1);}
  else if (user== 1) { $('#user_id').val(-2);}
  else               { $('#user_id').val( 0);}

}

var eventIdChange = function(){
  var event =  $(getSelected(tblEvent));
     $(".category_id_radio").unbind( "change" );
  $("#seat-qty-div").hide();
  $("#show-seats-div").hide();
  $("#placemap-button").hide();

  if ( event.length !== 0 ) {
    ajaxQManager.add({
      type:      "POST",
      url:      "ajax.php?x=cat",
      dataType:   "json",
      data:      {"pos":true,"action":"categories","event_id": event.attr('id'), "categories_only":true},
      success:function(data, status){
        printMessages(data.messages);
        catData = data; //set cat var
        //Fill Categories
        $("#cat-select").html('');
        $.each(data.categories,function(){
          $("#cat-select").append(this.html);
        });

        if (data.placemap) {
          $("#placemap-button").show();
           $("#placemap-button").data('image',data.placemap);
        }
        //$("#cat-select").show().change();
      }
    });
    $("#ft-event-free-seats").html(event.data('seats'));
  }else{
    $("#cat-select").html(lang.select_event_first);
    $("#discount-select").html("<option value='0'>"+lang.discount_none+"</option>");//hide().
  }
}

//The refresh orderpage, the ajax manager SHOULD ALLWAYS be used where possible.
var refreshOrder = function(){
  tblCart.fnDraw(true);
}

var updateSeatChart = function(){
   var catId = $('input[name=category_id]:checked').val();//$("#cat-select").val();
   var event = $("#event-id option:selected").text();
   unBindSeatChart();
   if(catData.categories[catId].numbering){
      $("#seat-qty-div").hide();
      $("#seat-qty").val('');
      $('#seat-chart').dialog('option', 'title', lang.chart_title +event+" - " + catData.categories[catId].title );
      bindSeatChart();
   }else{
      unBindSeatChart();
      $("#seat-chart").html("");
     $("#seat-qty").val('1');
      $("#seat-qty-div").show();
   }
}
// Update events function will take the dates and compile onto the event var
var updateEvents = function(){ //alert('jjaa');
  tblEvent.$('tr.row_selected').removeClass('row_selected');
  tblEvent.fnDraw(true);
  $("#event-id").val(0);
  eventIdChange();
}

var bindCheckoutSubmitForm = function(){
  $("#payment-confirm-form").ajaxForm({
    data:{ajax:true, pos:"yes", action:"_PosSubmit"},
    url:      "ajax.php?x=_possubmit",
    dataType: "json",
    success:  function(data, status){
      if(jQuery.isPlainObject(data)){
        printMessages(data.messages);
        $("#order_action").dialog('close');
        $("#order_action").html(data.html);
        bindCheckoutSubmitForm();
        $("#order_action").dialog('open');
      }
    }
  });
}
var seatCount = 0;
var bindSeatChart = function(){
   $("#show-seats-div").show();
   $("#show_seats").click(function(){
      var catId = $('input[name=category_id]:checked').val();// $("#cat-select").val();
      var event_id =  $(getSelected(tblEvent)).attr('id');
      ajaxQManager.add({
         type:      "POST",
         url:      "ajax.php?x=placemap",
         dataType:   "json",
         data:      {"pos":true,"action":"PlaceMap","category_id":catId,"event_id":event_id},
         success:function(data, status){
            printMessages(data.messages);
            if(data.status){
               $("#seat-chart").html(data.placemap);
               $("#seat-chart").dialog('open');
            }
         }
      });
   });
}
var unBindSeatChart = function(){
   //$("#seat-chart").dialog('destroy');
   $("#show-seats-div").hide();
   $("#show_seats").unbind( "click" );
}

var bindCartRemove = function(){
  $(".remove-cart-row").unbind('submit');
  $(".remove-cart-row").submit(function(){
    $(this).ajaxSubmit({
      url: 'ajax.php?x=removeitemcart',
      dataType: 'json',
      data:{pos:"yes",action:"_removeitemcart"},
      success: function(data){
        printMessages(data.messages);
        if(data.status){
          refreshOrder(); //Refresh Cart
          eventIdChange(); //Update ticket info (Free tickets etc)
        }
      }
    });
    return false;
  });
}

var printMessages = function(messages){
  if(messages === undefined){
    return;
  }
  if (messages.warning) {
    showErrorMsg(messages.warning);
  }
  if (messages.notice) {
    showNoticeMsg(messages.notice);
  }
}

/* Get the rows which are currently selected */
function getSelected( oTableLocal )
{
  return oTableLocal.$('tr.row_selected');
}
