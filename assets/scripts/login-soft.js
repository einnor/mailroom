function submitform(mform){ 
	var fdata=JSON.stringify(mform.serializeObject());
	var m_method=mform.find('#m_method').val();var response='';
	var dataR = testAjax( m_method, fdata, mform.find('#m_url').val()," application/json");
	dataR.success(function (data) {
		if(data.response=="error"){response = data.response;}
		if(data.response=="fail"){response = data.response;}
		if(data.response=="success"){
			$('.alert-error', $('.login-form')).hide();
			mform[0].reset();
			setCookie('username',data.username,1);
			setCookie('userid',data.userid,1);
			setCookie('dept',data.dept,1);
			setCookie('role',data.role,1); 
			response = data.response;
		}
	});
	return response;
}; //Submit Form


var dataR = testAjax("POST","","api/logout","application/json");

$('#signinform').on('submit',function (event) {  event.preventDefault();  event.stopPropagation();
	if (submitform( $(this) )=="success") {
		if(getCookie('role')=="2"){window.location = 'admin/';}
		if(getCookie('role')=="1"){window.location = 'cms/';}
		if(getCookie('role')=="0"){window.location = 'dashboard/';}
	}return false;
})

var Login = function () {

	var handleLogin = function() {
		$('.login-form').validate({
	            errorElement: 'label', //default input error message container
	            errorClass: 'help-inline', // default input error message class
	            focusInvalid: false, // do not focus the last invalid input
	            rules: {
	                username: {
	                    required: true
	                },
	                password: {
	                    required: true
	                },
	                remember: {
	                    required: false
	                }
	            },

	            messages: {
	                username: {
	                    required: "Username is required."
	                },
	                password: {
	                    required: "Password is required."
	                }
	            },

	            invalidHandler: function (event, validator) { //display error alert on form submit   
	                $('.alert-error', $('.login-form')).show();
	            },

	            highlight: function (element) { // hightlight error inputs
	                $(element)
	                    .closest('.control-group').addClass('error'); // set error class to the control group
	            },

	            success: function (label) {
	                label.closest('.control-group').removeClass('error');
	                label.remove();
	            },

	            errorPlacement: function (error, element) {
	                error.addClass('help-small no-left-padding').insertAfter(element.closest('.input-icon'));
	            }
	        });
	}

    return {
        //main function to initiate the module
        init: function () {
        	
            handleLogin();

            $.backstretch([
		        "assets/img/bg/1.jpg",
		        "assets/img/bg/2.jpg",
		        "assets/img/bg/3.jpg",
		        "assets/img/bg/4.jpg"
		        ], {
		          fade: 1000,
		          duration: 5000
		    });
        }
    };
}();