{*                  %%%copyright%%%
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
 *}<!-- $Id$ -->
{if $user->mode() eq '-1' and !$user->logged && $shop_event.event_date lt $smarty.now|date_format:"%Y-%m-%d"}
  {redirect url="?event_id={$smarty.request.event_id}"}
{/if}
{event event_id=$smarty.request.event_id ort='on' place_map='on' event_status='pub' limit=1}
  {include file="header.tpl" name=!shop! header=!shop_info! footer=!shop_condition!}
  <style type="text/css">
    .seatmapimage {
       width: 16px;
       height:16px;
    }
  </style>

  {include file="event_header.tpl"}
  <div class="art-content-layout-br layout-item-0"></div>
    {cart->maxSeatsAlowed event=$shop_event}
    <form name='f' id='catselect' action='index.php' method='post'>
      {ShowFormToken name='orderevent'}
      <input type='hidden' name='event_id' value='{$shop_event.event_id}'>
      <input type='hidden' name='action' value='addtocart'>
    <table width="100%" cellpadding='2' cellspacing='2' >
      <tbody>
        <tr>
          <td class='user_item' width="110">{!select_category!}:</td>
          <td class='user_value'>{$count=0}
            {category event_id=$shop_event.event_id stats="on"}
              {if !$category_id} {$category_id=$shop_category.category_id} {/if}
              <label for="category_id_{$shop_category.category_id}">
                <span id="catcolor" style="background-color:{$shop_category.category_color};padding:0px; margin-right:5px;">
                 <input type="radio" style='margin:0px 0px 0px  2px;' id="category_id_{$shop_category.category_id}" name="category_id" value="{$shop_category.category_id}" {if $category_id eq $shop_category.category_id}checked{/if} onClick="setNum({$shop_category.category_id},true)" {if $shop_category.category_free == 0}disabled="true"{/if}>
               </span>
                {$shop_category.category_name}</label> &nbsp;
            {/category}
            ({!free_seat!}: <span id="ft-cat-free-seats" >{$shop_category.category_free}</span> ({!approx!}))
          </td>
        </tr>
        <tr id='discount-name'>
          <td class='user_item' >{!select_discounts!}:</td>
          <td class='user_value'>
            {discount all='on' event_id=$shop_event.event_id cat_price=$shop_category.category_price}{/discount}
            {$haspromos=false}
            <label><input class='checkbox_dark mydiscount discount_none' type='radio' name='discount' data-name='{!no_discount!}' data-price='{$shop_category.category_price}' data-pricetxt='{valuta value=$shop_category.category_price}' value='0' checked>{!no_discount!}</label>
            {section name='d' loop=$shop_discounts}
              <label id='discount_{$shop_discounts[d].discount_id}' class='discount has-tooltip' title='{$shop_discounts[d].discount_cond}'>
                <input class='checkbox_dark mydiscount discount_{$shop_discounts[d].discount_id}' type='radio' name='discount' value='{$shop_discounts[d].discount_id}'>
                {$shop_discounts[d].discount_name}&nbsp;
              </label>
              {if $shop_discounts[d].discount_promo}{$haspromos=true}{/if}
            {sectionelse}
              <script>
                $('#discount-name').hide();
              </script>
            {/section}
          </td>
        </tr>
      </tbody>
    </table>


<div id="top5tabs" style='margin:0px; padding:0px;'>
  {if $shop_event.pm_image}
  	<ul>
  		<li><a href="#tabs-1">{!event_select_cat!}</a></li>
		<li><a href="#tabs-2">{!event_select_seats!}</a></li>
	</ul>
  	<div id="tabs-1">
      <img class="chartmap" src="files/{$shop_event.pm_image}"  border='1' width='581' usemap="#ort_map">
      <map name="ort_map">
        {category event_id=$shop_event.event_id stats="on"}
          {if $shop_category.category_free gt 0 && $shop_category.category_data}
            <area href="#" cat="{$shop_category.category_id}" {$shop_category.category_data} />
          {/if}
        {/category}
      </map>
  	</div>
  	{else}
  	<br/>
  {/if}
	<div id="tabs-2">
         <span id='order_amound' style='display:none;'>
         <center>
            <table border="0" cellspacing="0" cellpadding="5">
              <tr>
                <td class='event_data'>
                  {!number_seats!}
                </td>
                <td class='title'>
                  <select style="float:none;"  id='seat_places' name='places' >
                    {section name="myLoop" start=1 loop=$seatlimit+1}
                      <option value='{$smarty.section.myLoop.index}' > {$smarty.section.myLoop.index} </option>
                    {/section}
                  </select>
                  <input type='hidden' name='numbering' value='none' />
                </td>
              </tr>
            </table>
          </center>
          </span>
          <span id='order_placemap' style='display:none;'>
          <div style='overflow: auto; height: 350px;  border: 1px solid #DDDDDD;background-color: #fcfcfc'
               id='placemap' align='center' valign='middle'>
          </div> <!-- width:595px; --->
          <center><div valign='top'> {!placemap_image_explanation!}</div></center>
          </span>

	</div>
</div>

  <div class="art-content-layout-br layout-item-0"></div>
  <div class="art-content-layout layout-item-1">
    <div class="art-content-layout-row" style='padding:10px;'>
      <div class="art-layout-cell layout-item-3"  style='width: 100%;padding:10px;'>
      {!event_ordering_tickets_info!}
      <div style='float:right;padding:0px;margin:0px;'>
      {if $haspromos}
        {gui->button url="button" onclick="enterPromo();" name="has_eventpromo" type=1}
      {/if}
      {gui->button url="button" onclick="validateSelection();" name="add_to_cart" type=1}
      </div>
   	  </div>
    </div>
  </div><br/>
</form>
<script type="text/javascript">
	var mode = 'both';
	var category = -1;
	function setNum(cat_id, doSwitch) {
      //$('#tabs-2').
	  $('.pm_check').unbind('click');
	  $('#seat_places').attr('disabled', true);
 //   $('#add_to_cart').button( "option", "disabled", true );
      ajaxQManager.add({
         type:       "POST",
         url:        "json.php?x=placemap",
         dataType:   "json",
         data:      { "action":"PlaceMap", "category_id":cat_id, "seatlimit":{$seatlimit} },
         success:function(data, status){
           category = cat_id;
            if(data.status){
              $("#ft-cat-free-seats").text(data.cat.category_free);
              $("#ordering_category").text(data.cat.category_name);
              $(".discount_none").data('price',data.cat.category_price);
              $("#ordering_seats").text('0');
              $("#placemap").html(data.placemap);
          		if (data.placemap != '') {
          			$("#order_amound").hide();
          			$("#order_placemap").show();
          			mode = 'both';
                setTimeout( function(){ var c = $('#tabs-2').width(); $('#placemap').width(c); }, 300 );
          	    $("#places").val(0);
          		} else {
          			$("#order_amound").show();
          			$("#order_placemap").hide();
	              $('#seat_places').html('');
	              var max = Math.min({$seatlimit},data.cat.category_free);
	              for (var z=1;z<max+1;z++) {
	                 $("#seat_places").append('<option value='+z+'>'+z+'</option>');
	              }
	              mode = 'none';
	          		$('#seat_places').removeAttr('disabled');
	          	  $("#ordering_seats").text('1');
	           	}
              updateDiscounts(data.discounts);
       // 		  $('#add_to_cart').button( "option", "disabled", false );
              {if $shop_event.pm_image}
             		if (doSwitch) {
                    $("#top5tabs").tabs("option",  "active" , 1);
                }
      				{/if}

            }
         }
      });

	}
  /**
   *
   * @access public
   * @return void
   **/
  Number.prototype.formatMoney = function(c, d, t){
var n = this,
    c = isNaN(c = Math.abs(c)) ? 2 : c,
    d = d == undefined ? "." : d,
    t = t == undefined ? "," : t,
    s = n < 0 ? "-" : "",
    i = parseInt(n = Math.abs(+n || 0).toFixed(c)) + "",
    j = (j = i.length) > 3 ? j % 3 : 0;
   return s + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : "");
 };


  function updateDiscounts(discounts){
  	$('.discount').hide();
  	$.each(discounts,function(index, value){
  	  var disc = $('.discount_'+index);
  	  disc.data('price',value.discount_price);
  	  disc.data('pricetxt',value.discount_pricetxt);
  	  disc.data('name',value.discount_name);
    	$('#discount_'+index).show();
  	});
  	if ( $('input[name=discount]:checked').is(':hidden')) {
    	$(".discount_none").prop('checked', true);
  	}
  	setsetprice();
  }

  /**
   *
   * @access public
   * @return void
   **/
  setsetprice = function (){
  	var disc = $('input[name=discount]:checked');
  	var price = 0.0 + parseFloat(disc.data('price'));
  	$("#ordering_discount").text(disc.data('name'));
  	$("#ordering_price").html(price.toFixed(2));

    var amount = $("#ordering_seats").text();
  	$("#ordering_total").text((amount * price).formatMoney(2));
  }

   $('.mydiscount').live('click', setsetprice);
   $('.pm_check').live('click',setsetprice);
   $('#seat_places').live('change',function() {
     $("#ordering_seats").text($(this).val());
     setsetprice();
   });

  /**
   *
   * @access public
   * @return void
   **/
  function enterPromo(){
  	var promo;
  	promo = prompt('{!enter_eventpromocode!}','');
  	ajaxQManager.add({
         type:       "POST",
         url:        "json.php?x=checkEventPromo",
         dataType:   "json",
         data:      { "action":"checkEventPromo", "event_id":{$shop_event.event_id},'category_id': category ,'promo': promo},
         success:function(data, status){
            printMessages(data.messages);
            if(data.status){
               updateDiscounts(data.discounts);
               $(".discount_"+data.discount_id).prop('checked', true);
            } else {
              showErrorMsg(data.reason);
            }
         }
    });


  }
	function validateSelection() {
	  if ($('#add_to_cart').attr('disabled')) {
	  	return false;
	  }
	  if (mode == 'none') {
			bool= ($('#places').val() != 0 && $('input[name=category_id]:checked').val() != "");
			if (!bool) {
				showErrorMsg('Please select your category and the number of tickets');
				return false;
			}
		} else {
			bool= $('input[name=category_id]:checked').val();
			if (!bool) {
				showErrorMsg('Please select your category');
			}
		}
		$('#catselect').submit();
		return true;
	}

  function state_change(data) {
		 $('.chartmap').mapster('rebind',{
		    noHrefIsMask: false,
	    	onClick: state_change,
     		fillColor: 'afafaf',
    		fillOpacity: 0.3,
    		strokeWidth: 2,
    		stroke:true,
    		strokeColor: 'F88017',
         singleSelect:true,

      });
      var url = $(this).attr('cat');
      $("#category_id_"+url).attr('checked', true);
      setNum(url, true);
	}

    $('.chartmap').mapster({
		    noHrefIsMask: false,
	    	onClick: state_change,
      		fillColor: 'afafaf',
    		fillOpacity: 0.3,
    		strokeWidth: 2,
    		stroke:true,
    		strokeColor: 'F88017',
      singleSelect:true,

    });
    {if $shop_event.pm_image}
     	$("#top5tabs").tabs( { activate: function( event, ui ) { var c = $('#tabs-2').width(); $('#placemap').width(c); } });
	{/if}
  	$('.catselect').on('submit', function() {
  	  $('.pm_check').unbind('click');
  	  $('#seat_places').attr('disabled', true);
      $('#add_to_cart').attr('disabled', true);
        ajaxQManager.add({
           type:       "POST",
           url:        "jsonrpc.php?x=add",
           dataType:   "json",
           data:      { "action":"AddToCart", "event_id": {$smarty.request.event_id}, "category_id":cat_id,  "category_id":cat_id, "seatlimit":{$seatlimit} },
           success:function(data, status){
            //  printMessages(data.messages);
              if(data.status){
                  $("#ft-cat-free-seats").text(data.cat.category_free);
                  $("#placemap").html(data.placemap);
            		if (data.placemap != '') {
            			$("#order_amound").hide();
            			$("#order_placemap").show();
            			mode = 'both';
           //       setTimeout( function(){ var c = $('#tabs-2').width(); $('#placemap').width(c); }, 200 );
            		} else {
            			$("#order_amound").show();
            			$("#order_placemap").hide();
  	                $('#seat_places').html('');
  	                var max = Math.min({$seatlimit},data.cat.category_free);
  	                for (var z=1;z<max+1;z++) {
  	                   $("#seat_places").append('<option value='+z+'>'+z+'</option>');
  	                }
  	                mode = 'none';
  	      		    $('#seat_places').removeAttr('disabled');
            		}
                  $('#add_to_cart').removeAttr('disabled');
            		$("#places").val(0);
                  {if $shop_event.pm_image}
                    if (doSwitch) {
                      $("#top5tabs").tabs("option",  "active" , 1);
                    }
        	        {/if}
              }
           }
        });
  	  });
     setNum({$category_id}, false);

</script>

{/event}