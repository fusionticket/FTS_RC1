/*****************************************
 * Checkbox Area Select:  version 1.0 Created by Harry Pottash
 * Contact hpottash@gmail.com for further information
 * http://www.7goldfish.com
 *
 *  To apply to your page include this file and 
 *  add the js call: $(document).checkboxAreaSelect() 
 *
 *  Example:
 *    <script type='text/javascript' src='http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js'></script>
 *    <script type='text/javascript' src='<path to your js>/checkboxAreaSelect.js'></script>          
 *    <script type='text/javascript'>
 *      $(document).ready(function(){				
 *        $(document).checkboxAreaSelect();
 *      });
 *    </script>          
 *
 *	This works on the fly so the need to rebind is unnessary,
 *  once called will remained binded after ajaxcalls and page changes.
 *
 *	Works Tested in  IE8, FF 2.0+, Opera 8+ (IE has transparency issues.) 
 * 
 *  To use drag the mouse over checkboxes to click them
 *  Shift+Drag the mouse over checkboxes to unclick them
 *
 * Released under GPL version 3
 *
 ****************************************/
$.fn.checkboxAreaSelect = function(tbl){
	
	var cbAS = new Object();

    /*when a mouse clicks down, prepair to start a drag*/
    $('#seats').mousedown(function(e){
      if (!cbAS.mouseIsDown) {
        cbAS.startX = e.pageX;
        cbAS.startY = e.pageY; /*record where the mouse started */
        $("body").append("<div id='cbAS_dragbox' class='dragbox'></div>"); /*create a graphic indicator of select area */
        	$("#cbAS_dragbox").css({ 
        		"background-color":"#f00",
        		filter:"alpha(opacity=20)",
                opacity:".20", 
                position:"absolute", 
                left: cbAS.startX + "px", 
    			top: cbAS.startY + "px", 
                width: "0px", 
                height: "0px"});
		    cbAS.mouseIsDown = 1; /*flag that the mouse is down */
		  }
    });/*close mousedown*/
    
    /*if the mouse is moving run this*/
    $('#seats').mousemove(function(e){
        if(cbAS.mouseIsDown == 1){ /*check if they are currently dragging the mouse*/
          dragHeight = e.pageY - cbAS.startY;
          dragWidth = e.pageX - cbAS.startX; /*find the x & y diff of where they are and where they started */

  	    /*make the colored box fit the mouse movements */
          if (dragHeight < 0 && dragWidth < 0){ /* up and to the left */
  				$("#cbAS_dragbox").css({ height: -dragHeight ,  width: -dragWidth, left: e.pageX, top: e.pageY});
  	    	} else if (dragHeight < 0 && dragWidth > 0){ /*up and to the right */
  				$("#cbAS_dragbox").css({ height: -dragHeight ,  width: dragWidth, left: cbAS.startX, top: e.pageY});
  	    	} else if (dragHeight > 0 && dragWidth < 0){ /* down and to the left */
  				$("#cbAS_dragbox").css({ height: dragHeight ,  width: -dragWidth, left: e.pageX, top: cbAS.startY});
  	    	} else { /* down and to the right */
  				$("#cbAS_dragbox").css({ height: dragHeight , width: dragWidth, left: cbAS.startX, top: cbAS.startY});
  	    	}
        }
    });

    /* when they release the mouse button, check if they have dragged over any checkboxes,
     If they have, do work on them. Also reset things that started on mouse-down */
    $('body').mouseup(function(e){
      if (!cbAS.mouseIsDown) return;
      cbAS.mouseIsDown = false; /*clear currently dragging flag */
      $(".dragbox").remove(); /*get rid of select box */
      endX = e.pageX;
      endY = e.pageY; /*discover where mouse was released x&y */
      
      // if the mouse hasnt moved dont bother checking the check boxes as the mouse hasnt been dragged.
      if((endX != cbAS.startX) || (endY != cbAS.startY)){
  			/*for each checkbox on the page check if its within the drag-area*/
	    	var ckBox;
        var seats = $('#seats');
        var pos = seats.position();
	    	rows = seats.find('tr');
	    	cols = rows.eq(0).find('td');
	    	h = Math.round(seats.height() / rows.size());
	    	w = Math.round(seats.width()  / cols.size());
        if (cbAS.startX > endX) {
          z = endX;
          endX = cbAS.startX;
          cbAS.startX = z;
         // $('body').prepend('Xswap ');
        }
        if (cbAS.startY > endY) {
          z = endY;
          endY = cbAS.startY;
          cbAS.startY = z;
      //    $('body').prepend('Yswap ');
        }

	    	begX = Math.round(((cbAS.startX - pos.left) / w)-0.5);
	    	begY = Math.round(((cbAS.startY - pos.top)  / h)+0.5);
	    	endX = Math.round(((endX - pos.left) / w)-0.5);
	    	endY = Math.round(((endY - pos.top)  / h)+0.5);

	    	if (begX< 0) begX =0;
	    	if (begY< 0) begY =0;
	    	if (endX>= w) endX =w-1;
	    	if (endY>= h) endY =h-1;
	    	rows.slice(begY,endY).each(function(i, row){
          $(this).find('td').slice(begX,endX).each(function(j,col){
         //   this.style.background = "blue";
      	    if(e.shiftKey){
      			$(this).find(":checkbox").attr("checked",false); /*uncheck due to shift key */
                          } else {
      			$(this).find(":checkbox").attr("checked",true);  /*check the box */
                    }

          });
        });
	   // 	document.body.style.cursor= mouse;
  		}
    });/*close mouseup*/
  
  };/*close checkboxAreaSelect*/
