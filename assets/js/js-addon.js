$(()=>{
    jsAddon = {
        display:{
            swalMessage:(indicator,message)=>{

                icon = !indicator ? 'success' : 'error';
                
                let Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                    onOpen: (toast) => {
                      toast.addEventListener('mouseenter', Swal.stopTimer)
                      toast.addEventListener('mouseleave', Swal.resumeTimer)
                    }
                  })
                  
                  Toast.fire({
                    icon: icon,
                    title: message
                  })
            },
            addFormLoading:(elem)=>{
                $(elem).find("input").prop("disabled",'disabled');
                $(elem).find("select").prop("disabled",'disabled');
                $(elem).find("textarea").prop("disabled",'disabled');
                $(elem).find("button").prop("disabled",'disabled');
                $(elem).find("button[type=submit]").prepend(
                    $("<i>")
                        .addClass('fas fa-spinner fa-spin')
                )
            },
            removeFormLoading:(elem)=>{
                $(elem).find("input").prop("disabled",'');
                $(elem).find("select").prop("disabled",'');
                $(elem).find("textarea").prop("disabled",'');
                $(elem).find("button").prop("disabled",'');
                $(elem).find("i").remove();
            },
            addfullPageLoader:()=>{
                $(".loading").show();
            },
            removefullPageLoader:()=>{
                $(".loading").hide();
            },
            setSessionData:(name,data)=>{
                localStorage.setItem(name,btoa(JSON.stringify(data)));
            },
            getSessionData:(name)=>{
                $data = localStorage.getItem(atob(JSON.parse(name)));
                return atob($data);
            },
            deleteSesionDAta:(name)=>{
                sessionStorage.deleteSesionDAta(name);
            },
        }
    }
})