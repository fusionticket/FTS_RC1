var reOrder  = function(){
  $("#current-order").dialog({
    bgiframe: false,autoOpen: false,
    height: 'auto',width: 'auto',
    modal: true,
    close: function(event, ui) {
      //updateEvents();
      //refreshOrder();
    }
  });
  
  $("#reorder-button").click(function(){
    //console.log("Order Tickets Button");
    var orderId = $("#order-id").val();
    //console.log(orderId);
    $.ajax({
      type:"POST",
      url: "index.php",
      dataType: "HTML",
      data:{ajax:"yes",action:"ordertocart",order_id:orderId},
      success: function(html){
        //console.log(html);
      }
    });  
  });
  
}