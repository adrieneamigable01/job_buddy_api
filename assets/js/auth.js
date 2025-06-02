$(()=>{
    var authenticate = {
        init:()=>{
            if(localStorage.getItem('session') != null){
                session = JSON.parse(atob(localStorage.getItem('session')));
                if(session.key != null){
                    window.location.href = 'pos';
                }else{
                    localStorage.clear();
                }
            }
            jsAddon.display.removefullPageLoader();
        },
        ajax:{
            login:(payload)=>{
                $.ajax({
                    type:'post',
                    url:loginApi,
                    dataType:'json',
                    data:payload,
                    beforeSend:function(){
                        jsAddon.display.addFormLoading("#frm_login");
                    },
                    success:function(res){
                        jsAddon.display.swalMessage(res._isError,res.reason)

                        if(!res._isError){
                            jsAddon.display.setSessionData('session',res.data);
                            window.location.href = 'pos';
                        }

                        jsAddon.display.removeFormLoading("#frm_login");
                        
                    }
                })
            }
        }
    }
    authenticate.init()
    $("#frm_login").validate({
        errorElement: 'span',
		errorClass: 'text-danger',
	    highlight: function (element, errorClass, validClass) {
	      $(element).closest('.form-group').addClass("has-warning");
	      $(element).closest('.form-group').find("input").addClass('is-invalid');
	      $(element).closest('.form-group').find("select").addClass('is-invalid');
	    },
	    unhighlight: function (element, errorClass, validClass) {
	      $(element).closest('.form-group').removeClass("has-warning");
	      $(element).closest('.form-group').find("input").removeClass('is-invalid');
	      $(element).closest('.form-group').find("select").removeClass('is-invalid');
	    },
        rules:{
            username:{
                required:true,
            },
            password:{
                required:true,
            },
        },
        submitHandler: function(form) {
            var payload = {
                'userName':$(form).find('input[name=username]').val(),
                'password':$(form).find('input[name=password]').val(),
            };
    
            authenticate.ajax.login(payload);
        }
    })

    window.addEventListener('beforeunload',(event) =>{
        if(localStorage.getItem('session') != null){
            jsAddon.display.setSessionData('session',session);
        }
    });
   
})

