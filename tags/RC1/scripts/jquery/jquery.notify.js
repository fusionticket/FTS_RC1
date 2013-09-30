     function getNotifyBar(msg) {
       if (typeof notifytime == 'undefined') notifytime = 20;
       var bar = $('<div class=\"notify_message\"><span class=\"notify_text\"></span></div>');
       bar.attr('id','notify_' + (new Date().getTime() * Math.floor(Math.random() * 1000000)));
       bar.find('.notify_text').html(msg);
       $('#messagebar').append(bar);
       bar.css({
         overflow: 'hidden',
         //background: "url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABsAAAAoCAYAAAAPOoFWAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAPZJREFUeNq81tsOgjAMANB2ov7/7ypaN7IlIwi9rGuT8QSc9EIDAsAznxvY4pXPKr05RUE5MEVB+TyWfCEl9LZApYopCmo9C4FKSMtYoI8Bwv79aQJU4l6hXXCZrQbokJEksxHo9KMOgc6w1atHXM8K9DVC7FQnJ0i8iK3QooGgbnyKgMDygBWyYFZoqx4qS27KqLZJjA1D0jK6QJcYEQEiWv9PGkTsbqxQ8oT+ZtZB6AkdsJnQDnMoHXHLGKOgDYuCWmYhEERCI5gaamW0bnHdA3k2ltlIN+2qKRyCND0bhqSYCyTB3CAOc4WusBEIpkeBuPgJMAAX8Hs1NfqHRgAAAABJRU5ErkJggg==') repeat-x scroll left top lightgreen",
         //						borderRadius: '0px 0px 5px 5px',
         borderBottom: '2px solid #eee',
         borderLeft: '2px solid #eee',
         borderRight: '2px solid #eee',
         boxShadow: '0 2px 4px rgba(0, 0, 0, 0.1)',
         fontSize: '13px',
         lineHeight: '16px',
         //padding: '8px 10px 9px',
         width: 'auto',
         position: 'relative',
         cursor: 'pointer'
       });
       bar.data('tmr',0);
       if (notifytime>0) bar.data('tmr',setTimeout(function(){ bar.slideUp({'always': function () {bar.remove(); updatebar(); } }); }, notifytime * 1000));
       bar.click(function(){

         if (bar.data('tmr') != 0) {
          window.clearTimeout(bar.data('tmr'));
         }  bar.remove(); updatebar(); });
       updatebar();
       return bar;
     }

function updatebar(){
  $('.notify_message').css({borderRadius: '0px 0px 0px 0px'});
  $('.notify_message').last().css({borderRadius: '0px 0px 5px 5px'});
}

var showErrorMsg = function(msg){
  if(msg) {
    var bar = getNotifyBar(msg);//  $('#error-message');
    bar.css({
      backgroundColor: '#DA1F3E', borderColor: 'darkred', color: '#FFF'
    }).show();
  }
}

var showNoticeMsg = function(msg){
  if(msg) {
    var bar = getNotifyBar(msg);
    bar.css({
      backgroundColor: 'lightgreen', borderColor: '#50C24E', color: 'darkgreen'
    }).show();
  }
}

var showDebugMsg = function(msg){
  if(msg) {
    var bar = getNotifyBar(msg);
    bar.css({
      backgroundColor: '#FFEAA8', borderColor: '#FFC237', color: '#826200'
    }).show();
  }
}