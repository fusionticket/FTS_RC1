var
  myid = 0,
  type_id = 0,
  pmp_id = 3,
  zoneid = 0, catid= 0,
  imagesize = 20,
  griddata = {maxrows:10, maxcols:10, shift:0, mincols:1, minrows:1, data:[],zones:[],categories:[]},
  needrefrash = false,
  history = [],
  selection = [];

$(document).ready(function() {
  $("#loading").ajaxStart(function() {
    $(this).show();
  }).ajaxComplete(function() {
    $(this).hide();
  });
  $( "#zones" ).selectmenu({
    format: addressFormatting
  });
  $( "#cats" ).selectmenu({
    format: addressFormatting
  });

  $( "#repeatM" ).button({ text: false,icons: { primary: "ui-icon-arrowreturnthick-1-w"}, disabled: true });
  $( "#repeatT" ).button({ text: false,icons: { primary: "ui-icon-pencil"}});
  $( "#repeatRE" ).button({ text: false,icons: { primary: "ui-icon-circle-arrow-e"}});
  $( "#repeatRW" ).button({ text: false,icons: { primary: "ui-icon-circle-arrow-w"}});
  $( "#repeatSS" ).button({ text: false,icons: { primary: "ui-icon-circle-arrow-s"}});
  $( "#repeatSN" ).button({ text: false,icons: { primary: "ui-icon-circle-arrow-n"}});
  $( "#repeatE" ).button({ text: false,icons: { primary: "ui-icon-extlink"}});
  $( "#repeatNUM" ).button({ text: false,icons: { primary: "ui-icon-tag"}});
  $( "#repeatCLR" ).button({ text: false,icons: { primary: "ui-icon-trash"}});
  $( "#repeatHOLD").button({ text: false,icons: { primary: "ui-icon-locked"}});
  $( "#repeatSEAT").button({ text: false,icons: { primary: "ui-icon-circle-plus"}});

  $( "#repeatZP" ).button({ text: false,icons: { primary: "ui-icon-zoomout"}});
  $( "#repeatZM").button({ text: false,icons: { primary: "ui-icon-zoomin"}});
  $( "#repeatZR").button({ text: false,icons: { primary: "ui-icon-search"}});
  $( ".repeat" ).buttonset();

  $( "#repeatM" ).click(restoreHistory);
  $( "#tabs" ).tabs();

  $( "#repeatT" ).click(function (event){
    event.preventDefault();
    var type_id = $(this).val();
    if (selection.length==0) {
      alert('you need to make a selection first');
      return false;
    }
    var myid = window.prompt("Enter the new value:","");
    if (myid=='') {
      return false;
    }
    addHistory();
    addGridtextLabel(type_id, myid);
  });

  $( ".radioz" ).click(function (event){
    event.preventDefault();
    var zoom = parseInt($(this).val());
    if (zoom == 0) {
      imagesize = 20;

    } else {
      imagesize = Math.max(8,imagesize + zoom);

    }
    displaymaingrid();
  });


  $( ".radio" ).click(function (event){
    event.preventDefault();
    var type_id = $(this).val();
    if (selection.length==0) {
      alert('you need to make a selection first');
      return false;
    }
    addHistory();
    addGridLabel(type_id);
  });

  $( "#repeatNUM" ).click(function (event){
    event.preventDefault();
    if (selection.length==0) {
      alert('you need to make a selection first');
      return false;
    }
    fillManualSeats();
    $('#seatnumbering').dialog({
      title: "Setup the seat numbering",
      width: '700' ,
      modal: true,
      open : function(){
      }
    });
  });

  $( "#repeatSEAT" ).click(function (event){
    event.preventDefault();
    if (selection.length==0) {
      alert('you need to make a selection first');
      return false;
    }
    if (($('#zones').val()==0) && ($('#cats').val()==0)) {
      alert('you need to select atliest a category first. Zones are optional.');
      return false;
    }
    addHistory();
    addGridSeat($('#zones').val(), $('#cats').val());
  });

  $( "#repeatHOLD").click(function (event){
    event.preventDefault();
    var state;
    if (selection.length==0) {
      alert('you need to make a selection first');
      return false;
    }
    addHistory();
    selection.forEach( function(index){
      if (griddata.data[index][1]=='S') {
        if (typeof state == 'undefined') {
          state = griddata.data[index][6];
        }
        if ((griddata.data[index][6] ==0) || (griddata.data[index][6] == 3)) {
          if ((state == griddata.data[index][6])) {
            griddata.data[index][6] = (state==3)?0:3;
            drawGridElement(griddata.data[index]);
          }
        }
      }
    });
  });

  $('#manualnumbers').submit(function(event){
    event.preventDefault();
    addHistory();
    selection.forEach( function(index){
      var
        seat = $('#inp_'+index).val(),
        pos = seat.split("/", 2),
        x = is_empty(pos[0])?0:(is_numeric(pos[0])?parseInt(pos[0]):pos[0]),
        y = is_empty(pos[1])?0:(is_numeric(pos[1])?parseInt(pos[1]):pos[1]);

      griddata.data[index][2] = x;
      griddata.data[index][3] = y;
    });
    drawGridData();
    $('#seatnumbering').dialog('close');
    return false;
  });

  $('#autonumbers').submit(function(event){
    event.preventDefault();
    addHistory();
    auto_numbers($('[name="first_row"]').val(), parseInt($('[name="step_row"]').val()),  parseInt($('[name="inv_row"]:checked').val()),
                 $('[name="first_seat"]').val(),parseInt($('[name="step_seat"]').val()), parseInt($('[name="inv_seat"]:checked').val()),
                 parseInt($('[name="flip"]:checked').val()), parseInt($('[name="keep"]:checked').val()))
    $('#seatnumbering').dialog('close');
    return false;
  });

  $("#pmp_id").change(loadgrid);
  loadgrid();
/*  */
});

//a custom format option callback
var addressFormatting = function(text, opt){
  var
    newText = text,
    findreps = [{find:/^([^\-]+)\- ([^\-]+)/g, rep: '<span style=\'background-color:$1; width:10px;display:inline-block;\'>&emsp;</span> $2'}];

  for(var i in findreps){
    newText = newText.replace(findreps[i].find, findreps[i].rep);
  }
  return newText;
}

function test(com, grid) {
  if (com == 'Delete') {
    confirm('Delete ' + $('.trSelected', grid).length + ' items?')
  } else if (com == 'Add') {
    alert('Add New Item');
  }
}

function loadgrid() {
  pmp_id = $('#pmp_id').val();
  $.getJSON('remote.php',
    { load: "grid", pmp_id:pmp_id },
    function(result) {
      selection = [];
      griddata = result;
      if (!is_object (griddata)) {
        return;
      }
      var myzone = $('#zones');
      myzone.html('');
      myzone.append('<option value="0">Not selected</option>');
      griddata.zones.forEach(function(element, row, array){
        myzone.append('<option value="'+element.pmz_ident+'">'+element.pmz_color+'- '+element.pmz_name+'</option>');
      });
      myzone.selectmenu({format: addressFormatting});

      var myzone = $('#cats');
      myzone.html('');
      myzone.append('<option value="0">Not selected</option>');
      griddata.categories.forEach(function(element, row, array){
        myzone.append('<option value="'+element.category_ident+'">'+element.category_color+'- '+element.category_name+'</option>');
      });
      myzone.selectmenu({format: addressFormatting});
      displaymaingrid();
      recalcMinSize();
    });
}

function displaymaingrid() {
  var
    seatmap = $('#selectable'),
    seat, shift = (griddata.shift==1) ?((imagesize/2)):0;

  seatmap.resizable().resizable( "destroy" );
  seatmap.selectabled().selectabled( "destroy" );
  seatmap.css({'font-size': ((imagesize/1.75)).toFixed(0)+'px', height: griddata.maxrows*(imagesize+2)+4, width: (griddata.maxcols*(imagesize+2))+shift+4});
  $('.pm_seatmap').remove();

 // return;

  for (var row=0; row < griddata.maxrows; row++) {
    for (var col=0; col < griddata.maxcols; col++) {
      seat = $('<div />');
      seat.attr("id","s_"+col+"_"+row);
      seat.addClass('pm_seatmap');
      seat.css({'font-size': ((imagesize/1.5)).toFixed(0)+'px', left: col*(imagesize+2), top: row*(imagesize+2)+(shift*(row % 2)), height: imagesize, width: imagesize });
      seat.data('rows',0);
      seatmap.append(seat);
    }
  }
  seatmap.resizable({
    grid: imagesize+2,
    helper: "ui-resizable-helper",
    minWidth: griddata.mincols*(imagesize+2),
    minHeight: griddata.minrows*(imagesize+2),
    stop: function( event, ui ) {
      griddata.maxcols = (ui.size.width/(imagesize+2)).toFixed(0);
      griddata.maxrows = (ui.size.height/(imagesize+2)).toFixed(0);
      displaymaingrid();
    }
  });
  needrefrash = false;
  seatmap.selectabled({
    autoRefresh: true,
    filter:".pm_seatmap" ,
    stop: function (event, ui ) {
      selection = [];
      $( ".ui-selected", this ).each(function() {
        selection.push( this.id );
      });
    }
  });
  drawGridData();
  seatmap.show();
}

function drawGridData(){
  var propNames = Object.getOwnPropertyNames(griddata.data);
  propNames.forEach( function(index){
    drawGridElement(griddata.data[index]);
  });
}

function drawGridElement(seat) {
  var
    element = $("#s_"+seat[0][0]+"_"+seat[0][1]),
    test = '', seatid, catid, zoneid, title,
    rows = element.data('rows');

  if (rows>1) {
    for (var z=1; z<rows; z++){
      $("#s_"+(seat[0][0]+z)+"_"+(seat[0][1])).show();
    }
  }
  element.css({width: imagesize});
  element.data('rows',0);
  element.prop('title','');
  element.html('');
  element.css({color:'black', 'border-color': 'transparent' });
  element.removeClass('pm_number pm_none pm_error pm_free pm_occupied pm_hold pm_text');
 // alert(seat.toString());
  switch(seat[1]){
      case 'C':
         break;
      case 'T':
        element.html(seat[2]);
        element.css({width: seat[0][2]*(imagesize+2)-2});
        element.data('rows', seat[0][2]);
        element.addClass('pm_text');
        if (seat[0][2]>1) {
          for (var z=1; z<seat[0][2];z++){
            $("#s_"+(seat[0][0]+z)+"_"+(seat[0][1])).hide();
          }
        }

        break;
      case 'RE':
        test = "s_"+(seat[0][0]+1)+"_"+(seat[0][1]);
        if (test && test in griddata.data && griddata.data[test][1]=='S' && !is_empty(griddata.data[test][2])) {
          element.html(griddata.data[test][2]);
        } else
          element.html("<span class='ui-icon pm_ruler ui-icon-triangle-1-e'></span>");
        element.addClass('pm_number');
        break;
      case 'RW':
        test = "s_"+(seat[0][0]-1)+"_"+(seat[0][1]);
        if (test && test in griddata.data && griddata.data[test][1]=='S'&& !is_empty(griddata.data[test][2]) ) {
          element.html(griddata.data[test][2]);
        } else
          element.html("<span class='ui-icon pm_ruler ui-icon-triangle-1-w'></span>");
        element.addClass('pm_number');
        break;
      case 'SS':
        test = "s_"+(seat[0][0])+"_"+(seat[0][1]+1);
        if (test && test in griddata.data && griddata.data[test][1]=='S' && !is_empty(griddata.data[test][3])) {
          element.html(griddata.data[test][3]);
        } else
          element.html("<span class='ui-icon pm_ruler ui-icon-triangle-1-s'></span>");
        element.addClass('pm_number');
        break;
      case 'SN':
        test = "s_"+(seat[0][0])+"_"+(seat[0][1]-1);
        if (test && test in griddata.data && griddata.data[test][1]=='S' && !is_empty(griddata.data[test][3]) ) {
          element.html(griddata.data[test][3]);
        } else
          element.html("<span class='ui-icon pm_ruler ui-icon-triangle-1-n'></span>");
        element.addClass('pm_number');
        break;
      case 'E':
        element.attr({title: 'Exit'} );
        element.html("<span class='ui-icon pm_ruler ui-icon-extlink' alt='exit'></span>");
        element.addClass('pm_none');
        break;
      case 'S':
         seatid = seat[2]+'/'+seat[3];
         catid  = seat[4];
         zoneid = seat[5];
         title = '';
         if (zoneid) {
           zone = griddata.zones[zoneid-1];
           element.css({'border-color': zone.pmz_color});
           title += 'Zone: '+zone.pmz_name+"\n";
         }
         if (catid) {
           cat = griddata.categories[catid-1];
           element.css({'color': cat.category_color});
           title += 'Cat: '+cat.category_name+"\n";
           element.html("<b>&diams;</b>");
         } else {
           title += 'Cat: NOT SET'+"\n";
         }
     //    alert([!catid && (!is_empty(seat[2]) || !is_empty(seat[3])))
        if (catid) {
          if ((is_empty(seat[2]) && is_empty(seat[3]))) {
            element.addClass('pm_error');
            title += 'Seat: NOT SET';
            if (seat[6]==3) {
              title = 'SEAT is ON HOLD'+"\n";
            }
          } else if (seat[6]==3) {
             title = 'SEAT is ON HOLD'+"\n";
             element.addClass('pm_hold');
          } else {
            element.addClass('pm_free');
            title += 'Seat: '+seatid;
          }
        }
        element.attr({title:title});
  } // switch
}

function addGridLabel(type_id) {
  var
    index, pos;

  selection.forEach( function(index){
    if (type_id == 'CLR'){
      pos = index.split("_", 3);
      if (typeof griddata.data[index] !== 'undefined') {
         delete griddata.data[index];
      }
      drawGridElement([[parseInt(pos[1]),parseInt(pos[2]),1,1],'C']);

    } else {
      pos = index.split("_", 3);
      griddata.data[index] = [[parseInt(pos[1]),parseInt(pos[2]),1,1],type_id];
      drawGridElement(griddata.data[index]);

   }
 });
  recalcMinSize();
}

function addGridtextLabel(type_id, label) {
  var
    list = [], pos, x, y,
    con = 0, element, col, row, array;
  selection.forEach( function(index){
    pos = index.split("_", 3);
    x = parseInt(pos[1]);
    y = parseInt(pos[2]);
    if (typeof list[y] == 'undefined') {list[y] = [];}
    list[y][x] = index;
  });

  list.forEach(function(element, row, array){
     con =0;
     element.forEach(function (element, col) {
       if (!con) {
         con = [col, row, 1, element];
       } else {
         if ((col - 1) != k) {
           griddata.data[con[3]] = [[con[0],con[1],con[2],1],type_id, label];
           drawGridElement(griddata.data[con[3]]);
           con = [col, row, 1, element];
         } else {
            con[2]++;
         }
       }
       k = col;
     } );
   });
 //  if (!con) {
   griddata.data[con[3]] = [[con[0],con[1],con[2],1],type_id, label];
   drawGridElement(griddata.data[con[3]]);
   recalcMinSize();
//   }
}

function addGridSeat(zone, cat){
  var
    index, pos;

  selection.forEach( function(index){
    if (index in griddata.data && griddata.data[index][1]=='S' ) {
        if (zone) { griddata.data[index][5] = parseInt(zone);}
        if (cat)  { griddata.data[index][4] = parseInt(cat);}
    } else {
      pos = index.split("_", 3);
      griddata.data[index] = [[parseInt(pos[1]),parseInt(pos[2]),1,1], 'S', 0, 0, parseInt(cat), parseInt(zone), 0];
    }
    drawGridElement(griddata.data[index]);
  });
  recalcMinSize();
}

function recalcMinSize(){
  var
    SizeX=0, SizeY=0,
    propNames = Object.getOwnPropertyNames(griddata.data);

  propNames.forEach( function(index){
    var x = griddata.data[index][0][0] + griddata.data[index][0][2];
    var y = griddata.data[index][0][1] + griddata.data[index][0][3];
    if (SizeX < x) { SizeX = x;}
    if (SizeY < y) { SizeY = y;}
  });
  griddata.mincols = SizeX;
  griddata.minrows = SizeY;
  $( "#selectable" ).resizable( "option", "minHeight", griddata.minrows*(imagesize+2) );
  $( "#selectable" ).resizable( "option", "minWidth", griddata.mincols*(imagesize+2) );
}

function _getSeatArea() {
  var
    list = [],
    array = [-1,-1,-1,-1];
  selection.forEach( function(index){
    var pos = index.split("_", 3);
    var x = parseInt(pos[1]);
    var y = parseInt(pos[2]);
    if (typeof list[y] == 'undefined') {list[y] = [];}
    list[y][x] = index;
    if ((array[0]==-1) || array[0]>y) { array[0] =y;}
    if ((array[2]==-1) || array[2]<y) { array[2] =y;}
    if ((array[1]==-1) || array[1]>x) { array[1] =x;}
    if ((array[3]==-1) || array[3]<x) { array[3] =x;}
  });
  return [list, array];
}

function fillManualSeats(){
  var
    space = $('#manualsetings'), mytr, mytd,
    arr   = _getSeatArea(),
    list  = arr[0],
    array = arr[1];

  space.html('');
  for (var y= array[0]; y<= array[2];y++) {
    mytr = $('<tr />');
    space.append(mytr);

    for (var x= array[1]; x<= array[3];x++) {
      index = list[y][x];

      if (index in griddata.data && griddata.data[index][1]=='S' ) {
        var seat = griddata.data[index];
        var text = "<input type='text' id='inp_"+index+"' value='"+seat[2]+'/'+seat[3]+"' size='4' style='font-size:9px;'>";
      } else {
        var text = '&nbsp;'
      }
      mytd = $('<td />');
      mytd.html(text);
      mytr.append(mytd);
    }
  }
}

function auto_numbers (first_row,  step_row, inv_row,
                       first_seat, step_seat, inv_seat,
                       flip, keep){
  var
    arr   = _getSeatArea(), row, col, j_0, k_0, index,
    list  = arr[0], array = arr[1],
    oldcol, oldrow;

  first_seat = is_numeric(first_seat)?parseInt(first_seat): first_seat;
  first_row = is_numeric(first_row)?parseInt(first_row): first_row;

  row = first_row;
  for(var j = array[0];j <= array[2];j++) {
    var first_seat = is_numeric(first_seat)?parseInt(first_seat): first_seat;
    col = first_seat;
    for(var k = array[1];k <= array[3];k++) {
      j_0 = (inv_row?(array[2]  - j)+array[0] :j);
      k_0 = (inv_seat?(array[3] - k)+array[1] :k);
      index = list[j_0][k_0];
      if (index in griddata.data && griddata.data[index][1]=='S' ) {
        var oldcol = griddata.data[index][2];
        var oldrow = griddata.data[index][2];
        if (!flip) {
          if (keep && !is_empty(oldcol)) {
            col = oldcol;
          } else {
            griddata.data[index][3] = col;
          }
          if (keep && !is_empty(oldrow)) {
            row = oldrow;
          } else {
            griddata.data[index][2] = row;
          }
        } else {
          if (keep && oldcol) {
            row = oldcol;
          } else {
            griddata.data[index][3] = row;
          }
          if (keep && oldrow) {
            col = oldrow;
          } else {
            griddata.data[index][2] = col;
          }
        }
        if (is_numeric(col)) {
          col += step_seat;
        } else {
          col = chr(asc(col) + step_seat);
        }
      }
    }
    if (col != first_seat) {
      if (is_numeric(row)) {
        row += step_row;
      } else {
        row = chr(asc(row) + step_row);
      }
    }
  }
  drawGridData();
}

function addHistory () {
var buff = []
    selection.forEach( function(index){
        if (index in griddata.data) {
          buff.push(griddata.data[index].slice(0));
        } else {
            var pos = index.split("_", 3);
            var x = parseInt(pos[1]);
            var y = parseInt(pos[2]);
            buff.push([[x,y,1,1],'C']);
        }
    });
    if (history.length >10) {
      history.shift();
    }
    history.push(buff);
    $( "#repeatM" ).button( "enable" );
}

function restoreHistory() {
  var buff, index;
  if (history.length > 0) {
    buff = history.pop();
    buff.forEach( function(item){
        index = 's_'+item[0][0]+'_'+item[0][1];
        if (index in griddata.data && item[1]==='C') {
            delete griddata.data[index];
        } else {
            griddata.data[index] = item;
        }
        drawGridElement(item);
    });
  }
  if (history.length == 0) {
    $( "#repeatM" ).button( "disable" );
  }

}


/* ========================================================================== */
function is_empty(str) {
  return (!str || 0 === str.length);
}

function is_object (mixed_var) {
  // http://kevin.vanzonneveld.net
  // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // +   improved by: Legaev Andrey
  // +   improved by: Michael White (http://getsprink.com)
  // *     example 1: is_object('23');
  // *     returns 1: false
  // *     example 2: is_object({foo: 'bar'});
  // *     returns 2: true
  // *     example 3: is_object(null);
  // *     returns 3: false
  if (Object.prototype.toString.call(mixed_var) === '[object Array]') {
    return false;
  }
  return mixed_var !== null && typeof mixed_var == 'object';
}

function asc(String){
	return String.charCodeAt(0);
}

function chr(AsciiNum){
	return String.fromCharCode(AsciiNum)
}
function is_numeric (mixed_var) {
  // http://kevin.vanzonneveld.net
  // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // +   improved by: David
  // +   improved by: taith
  // +   bugfixed by: Tim de Koning
  // +   bugfixed by: WebDevHobo (http://webdevhobo.blogspot.com/)
  // +   bugfixed by: Brett Zamir (http://brett-zamir.me)
  // *     example 1: is_numeric(186.31);
  // *     returns 1: true
  // *     example 2: is_numeric('Kevin van Zonneveld');
  // *     returns 2: false
  // *     example 3: is_numeric('+186.31e2');
  // *     returns 3: true
  // *     example 4: is_numeric('');
  // *     returns 4: false
  // *     example 4: is_numeric([]);
  // *     returns 4: false
  return (typeof(mixed_var) === 'number' || typeof(mixed_var) === 'string') && mixed_var !== '' && !isNaN(mixed_var);
}