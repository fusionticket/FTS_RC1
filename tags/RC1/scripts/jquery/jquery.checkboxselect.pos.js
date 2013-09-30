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
$.fn.checkboxAreaSelect = function(){
	
	var cbAS = new Object();

    /*when a mouse clicks down, prepair to start a drag*/
    $(document).mousedown(function(e){
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
		cbAS.mouseIsDown = true; /*flag that the mouse is down */
    });/*close mousedown*/
    
    /*if the mouse is moving run this*/
    $(document).mousemove(function(e){
        if(cbAS.mouseIsDown){ /*check if they are currently dragging the mouse*/
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
    $(document).mouseup(function(e){
        cbAS.mouseIsDown = false; /*clear currently dragging flag */
        $(".dragbox").remove(); /*get rid of select box */
        cbAS.endX = e.pageX;
        cbAS.endY = e.pageY; /*discover where mouse was released x&y */
        
        // if the mouse hasnt moved dont bother checking the check boxes as the mouse hasnt been dragged.
        if((cbAS.endX != cbAS.startX) || (cbAS.endY != cbAS.startY)){
        	if(cbAS.endY > cbAS.startY){cbAS.dragY = "normal"; }else{ cbAS.dragY = "invert"; }
        	if(cbAS.endX > cbAS.startX){cbAS.dragX = "normal"; }else{ cbAS.dragX = "invert"; }
			/*for each checkbox on the page check if its within the drag-area*/
			var ckBox;
			var gap = 0;
			
	        $("form :checkbox").each(function(){
	        	ckBox = $(this);
	        	box_top = ckBox.position().top + (ckBox.height()/2);   /*checkboxes have an area */
	            box_left = ckBox.position().left + (ckBox.width()/2);  /*so find their centerpoint */
	            
	            if( (box_top > cbAS.startY && box_top < cbAS.endY ) || (box_top < cbAS.startY && box_top > cbAS.endY )){
					if( (box_left > cbAS.startX && box_left < cbAS.endX ) || (box_left < cbAS.startX && box_left > cbAS.endX )){
					    /*if checkbox was in the drag area */
					    if(e.shiftKey){
							ckBox.attr("checked",false); /*uncheck due to shift key */	  
						} else {
							ckBox.attr("checked",true);  /*check the box */						  
	                    }
					}
		    	}     
			});/*close each*/
		}
    });/*close mouseup*/
    
};/*close checkboxAreaSelect*/
