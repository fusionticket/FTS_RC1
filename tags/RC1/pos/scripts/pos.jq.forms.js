$(window).load(function(){

jQuery.validator.addMethod("phoneCheck", function(phone_number, element) {
    phone_number = phone_number.replace(/\s+/g, "");
	return this.optional(element) || phone_number.length >= 9 &&
	phone_number.match(/^((\(?\d{3,5}\)?\d{4}[\.\-\ ]?\d{2,3})|((1-?)?(\([2-9]\d{2}\)|[2-9]\d{2})\-?[2-9]\d{2}\-?\d{4}))$/);

}, "Please specify a valid phone number");

$("#pos-user-form").validate({
  errorClass: "form-error",
    validClass: "form-valid",
  success: "form-valid",
  errorPlacement: function(error, element) {
    if (element.attr("name") == "check_condition")
    error.insertAfter(element);
    else
    error.insertAfter(element);
  }
  });
  
  jQuery.extend(jQuery.validator.messages, {
	    required: " "
	});

});