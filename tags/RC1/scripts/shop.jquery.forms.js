var ajaxQManager = $.manageAjax.create('ajaxQMan',{
	queue:true,
	abortOld:true,
	maxRequests: 1,
	cacheResponse: false
});

$(window).load(function(){

	jQuery.validator.addMethod("phoneCheck", function(phone_number, element) {
		phone_number = phone_number.replace(/\s+/g, "");
		return this.optional(element) || phone_number.length >= 9 &&
		phone_number.match(/^((\(?\d{3,5}\)?\d{4}[\.\-\ ]?\d{2,3})|((1-?)?(\([2-9]\d{2}\)|[2-9]\d{2})\-?[2-9]\d{2}\-?\d{4}))$/);

	}, "Please specify a valid phone number");
/*
  updateUserRules = {
		rules: {
			user_firstname	: 	{ required :true },
			user_lastname	:	{ required : true },
			user_address	:	{ required : true },
			user_zip 	:	{ required : true },
			user_city 	: 	{ required : true },
			user_phone 	: 	{ phoneCheck: true, minlength : 9, maxlength : 16 },
			user_fax 	: 	{ phoneCheck: true, minlength : 9,	maxlength : 16 },
			user_email 	: 	{ required : true, email :true },
			old_password 	: 	{ required : true, minlength : 6 }
		},
		messages : {
			user_firstname : { required : lang.required },
			user_lastname : { required : lang.required	},
			user_address : { required : lang.required },
			user_zip : { required : lang.required },
			user_city : { required : lang.required	},
			user_phone : { minlength : lang.phone_short, maxlength : lang.phone_long },
			user_fax : { minlength : lang.fax_short, maxlength : lang.fax_long },
			user_email : { required : lang.required, email : lang.email_valid },
			old_password : { required : lang.required,	minlength : lang.pass_short }
		},
		errorClass: "form-error",
		success: "form-valid"
	}

  updateUserRulesAndPass = updateUserRules;
  updateUserRulesAndPass.rules.password1 = { minlength : 6 };
  updateUserRulesAndPass.rules.password2 = { minlength : 6, equalTo: "#password" }

	$("#update_user").validate(updateUserRulesAndPass);

	$("#user-register").validate({
 	rules: {
			user_firstname	: 	{ required :true },
			user_lastname	:	{ required : true },
			user_address	:	{ required : true },
			user_zip 	:	{ required : true },
			user_city 	: 	{ required : true },
			user_phone 	: 	{ phoneEU : true }, //We support addtional validation methods ie. phoneUS:true , check jquery.validation.add-methods.js
			user_fax 	: 	{ phoneEU : true },
			user_email 	: 	{ required : true, email :true },
			user_email2 	: 	{ required : true, email :true, equalTo : "#email" },
			password1 	: 	{ required : true, minlength : 6 },
			password2 	: 	{ required : true, minlength : 6, equalTo: "#password" },
			check_condition	:	{ required : true},
			user_nospam: {
				required: true,
				remote: {
          url: "nospam.php",
          type: "post",
          data: {
            name: "user_nospam",
            check: function() {
              return $('input[name$="user_nospam"]').val();
            }
          }
  			}
			}
 		},
		messages : {
			user_firstname : { required : lang.required },
			user_lastname : { required : lang.required	},
			user_address : { required : lang.required },
			user_zip : { required : lang.required },
			user_city : { required : lang.required	},
			user_phone : { minlength : lang.phone_short, maxlength : lang.phone_long, digits : lang.not_number },
			user_fax : { minlength : lang.fax_short, maxlength : lang.fax_long, digits : lang.not_number },
			user_email : { required : lang.required, email : lang.email_valid },
			user_email2 : { required : lang.required, email : lang.email_valid, equalTo: lang.email_match },
			password1 : { required : lang.required,	minlength : lang.pass_short },
			password2 : { required : lang.required,	minlength : lang.pass_short, equalTo: lang.pass_match },
			user_nospam :	{ required : lang.required },
			check_condition	:	{ required : lang.condition}
		},
		errorClass: "form-error",
		success: "form-valid",
		errorPlacement: function(error, element) {
	 		if (element.attr("name") == "check_condition")
		   		error.insertAfter(element);
		 	else
		   		error.insertAfter(element);
		}
	});
	$("#user-login").validate({
		rules: {
			password : { required : true},
			username : { required : true, email :true }
		},
		messages : {
			password : { required : " " },
			username : { required : " ", email : " " }
		},
		errorClass: "form-error"

	});

*/

  $('#ismember-checkbox').click(function(){
    if($(this).is(':checked')){
      showPasswords(true);
    }else{
      showPasswords(false);
    }
  });

  $('#forgot_password').click(function(){
    ajaxQManager.add({
      type:      "POST",

      url:      "forgot_password.php",
      dataType:   "HTML",
      data:      {},
      success:function(data, status){
          $("#showdialog").html(data);
          $("#showdialog").modal({
            autoResize: true
          });
      }
    });
  });
});
var updateUserRulesAndPass = {};
var updateUserRules = {};
var showPasswords = function(show){ alert('hello all');
  if(show == true){
    $('#passwords_tr1').show();
    $('#passwords_tr2').show();
      $("input[name='password1']").rules("add",{ required : true, minlength : 6 });
      $("input[name='password2']").rules("add",{ required : true , minlength : 6, equalTo: "#password" });
  }else{
    $('#passwords_tr1').hide();
    $('#passwords_tr2').hide();
      $("input[name='password1']").rules("remove");
      $("input[name='password2']").rules("remove");  }
}
