$(document).ready(function(){

	// *** Accordian Menu *** //
	//$('.accordion-content').not(':first').hide();
	$('.accordion-content').hide();
	
	//$('.accordion-content:first').show();
	//$('.accordion-header:first').addClass('header-active');	
	//$('.accordion-header:first').find('span').addClass('icon-active');

	$('.accordion-header').click(function () {
		if ($(this).hasClass('header-active')) {
			$('.accordion-content:visible').slideUp('slow').prev().removeClass('header-active');
			$('.icon-active:visible').removeClass('icon-active');
		
		} else {
			$('.accordion-content:visible').slideUp('slow').prev().removeClass('header-active');
			$('.icon-active:visible').removeClass('icon-active');
			$(this).addClass('header-active').next().slideDown('slow');
			$(this).find('span').addClass('icon-active');
			}
	});
 
  // *** Multiple select/deselect functionality *** //
	// *** Also Enables/Disables submit buttons *** //
  $("#selectall").click(function () {
				//if ($('.case').attr('checked')) { alert ('5');
				if ($('.case:checked').length == $('.case').length) { 	
          $('.case').attr('checked', this.checked);
						if ($('.submit').hasClass('btn_disabled') ) { 
							$('.submit').removeAttr('disabled').removeClass('btn_disabled');
						}
						if (!$('.submit').hasClass('btn_disabled') ) { 
							$('.submit').attr('disabled', 'disabled').addClass('btn_disabled');
						}			
				} else { 
					$('.case').attr('checked', this.checked);
						if (!$('.submit').hasClass('btn_disabled') ) {
							$('.submit').attr('disabled', 'disabled').addClass('btn_disabled');
						}
						if ($('.submit').hasClass('btn_disabled') ) { 
							$('.submit').removeAttr('disabled').removeClass('btn_disabled');
						}
				};
  });

  $(".case").click(function(){ // If all checkbox are selected, check the selectall checkbox & viceversa
    if($(".case").length == $(".case:checked").length) {
      $("#selectall").attr("checked", "checked");
    } else {
      $("#selectall").removeAttr("checked");
    }
  });
		
	// *** On FORM submit - check at least one option is checked *** //
	$('.submit').addClass('btn_disabled');
	$('.case').change(function() {
    if ($('.case:checked').length) {
			if ($('.submit').hasClass('btn_disabled') ) {
				$('.submit').removeAttr('disabled').removeClass('btn_disabled');
			} 
    } else {
			if (!$('.submit').hasClass('btn_disabled') ) {
				$('.submit').attr('disabled', 'disabled').addClass('btn_disabled');
			}
		}
	});
	
	$("table#resultsEdit:even").css('background', '#dedede');
	
	
});

			function confirmSubmit($msg)
			{
			var agree=confirm($msg);
			if (agree)
				return true ;
			else
				return false ;
			}


