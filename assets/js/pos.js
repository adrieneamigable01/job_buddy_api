$(()=>{
    var accesskey   = session != null ? session.key : null,
    activeTable = null,
    menuTypeId = 1,
    total = 0,
    discount_amount = 0,
    subtotal = 0,
    discount = 0,
    objectMenu = {},
    discountPercentage = 0,
    discountItems = {},
    cash = 0,
    canSubmit = 0,
    note = '',
    pos_table = $("#selected-menu-list"),
    pos_message = $("#system-pos-message"),
    transaction_id = null,
    isLogout = false;
    pos = {
        init:()=>{

            if(accesskey == null){
                localStorage.clear();
                window.location.href = baseUrl;
            }

            pos.ajax.getMenuType()
            .then(data=>{
                if(data){
                    pos.ajax.getMenu();
                }

            })
        },
        ajax:{
            getMenuType:()=>{
                return new Promise((resolve,reject)=>{
                     $.ajax({
                        type:'get',
                        url:`${getMenuTypeApi}/?accessKey=${accesskey}`,
                        dataType:'json',
                        success:function(response){
                            $.each(response.data,function(k,v){

                                if(k == 0){
                                    menuTypeId = v.menuTypeId;
                                }

                                $('.mebu-tabs')
                                    .append(
                                        $("<li>")
                                            .addClass("nav-item")
                                            .append(
                                                $("<a>")
                                                    .addClass(k == 0 ? "nav-link active" : "nav-link")
                                                    .attr({
                                                        id:`pills-home-tab`,
                                                        'data-toggle':`pill`,
                                                        'data-target':`#`,
                                                        'role':`tab`,
                                                        'aria-selected':`true`,
                                                    })
                                                    .text(v.menuType)
                                                    .click(function(){
                                                        menuTypeId = v.menuTypeId;
                                                        pos.ajax.getMenu();
                                                    })
                                            )
                                    )
                            })
                            resolve(true)
                        }
                    })
                })
            },
            getMenu:()=>{
                let payload = {
                    menuTypeId:menuTypeId,
                    accessKey:accesskey
                }
                $.ajax({
                    type:'post',
                    url:getMenuApi,
                    dataType:'json',
                    data:payload,
                    beforeSend:function(){
                        jsAddon.display.addfullPageLoader()
                        $(`#menu-container`)
                        .empty()
                    },
                    success:function(response){
                        if(!response._isError){ 
                            $.each(response.data,function(key,value){
                                $(`#menu-container`).append(
                                    $("<div>")
                                        .click(function(){

                                            if($("#transaction-header").find("span.fa-trash").length <= 0){
                                                $("#transaction-header")
                                                .append(
                                                    $("<span>")
                                                    .addClass("fa fa-trash text-danger float-right")
                                                    .attr({
                                                        title:'Remove all'
                                                    })
                                                    .css({
                                                        'cursor':'pointer'
                                                    })
                                                    .click(function(){


                                                        Swal.fire({
                                                            title: 'Are you sure?',
                                                            text: `Remove all`,
                                                            icon: 'warning',
                                                            showCancelButton: true,
                                                            confirmButtonColor: '#3085d6',
                                                            cancelButtonColor: '#d33',
                                                            confirmButtonText: 'Yes, remove it!'
                                                        }).then((result) => {
                                                            if (result.value) {
                                                                $('#selected-menu-list').fadeOut(500,function(){
                                                                    $("#selected-menu-list").empty();
                                                                    subtotal            = 0 ;
                                                                    discount            = 0 ;
                                                                    total               = 0 ;
                                                                    discountPercentage  = 0;
                                                                    canSubmit           = false;
                                                                    note                = '';
                                                                    discountItems       = {};
                                                                    objectMenu          = {},   
                                                                    pos.display.calculatePOS();
                                                                    $('#selected-menu-list').show()
                                                                })
                                                                $("#transaction-header").find("span.fa-trash").remove();
                                                            }
                                                        })

                                                    })
                                                )
                                            }
                                            
                                            if(!objectMenu.hasOwnProperty(value.menuId)){
                                                objectMenu[value.menuId] = {
                                                    menuName:value.menuName,
                                                    price:value.price,
                                                    total_price:value.price,
                                                }
                                            }else{
                                                let newPrice = parseFloat(objectMenu[value.menuId].total_price) + parseFloat(value.price);
                                                let newName = `${value.menuName} x(${newPrice / value.price})`
                                                objectMenu[value.menuId] = {
                                                    menuName:newName,
                                                    price:value.price,
                                                    total_price:newPrice.toString(),
                                                }
                                            }
                                           
                                            subtotal += parseFloat(value.price);

                                            $("#selected-menu-list")
                                            .append(
                                                $("<tr>")
                                                    .append(
                                                        $("<td>")
                                                            .text(` ${value.menuName}`),
                                                        $("<td>")
                                                            .append(
                                                                $("<span>")
                                                                .addClass("badge badge-primary badge-pill")
                                                                .text(value.price),
                                                            ),
                                                        $("<td>")
                                                            .append(
                                                                $("<i>")
                                                                .addClass("fa fa-trash text-danger float-right")
                                                                .attr({'title':`Remove ${value.menuName}`})
                                                                .css({'cursor':'pointer',})
                                                                .click(function(){
                                                                    // console.log("objectMenu",objectMenu);return false;
                                                                    Swal.fire({
                                                                        title: 'Are you sure?',
                                                                        text: `Remove item ${value.menuName} item (${$(this).parents("li").index() + 1})`,
                                                                        icon: 'warning',
                                                                        showCancelButton: true,
                                                                        confirmButtonColor: '#3085d6',
                                                                        cancelButtonColor: '#d33',
                                                                        confirmButtonText: 'Yes, remove it!'
                                                                    }).then((result) => {
                                                                        if (result.value) {
                                                                            let _this = $(this).parents("tr");
                                                                            if(objectMenu[value.menuId].total_price > value.price){
                                                                                objectMenu[value.menuId].total_price = parseFloat(objectMenu[value.menuId].total_price) - parseFloat(value.price)
                                                                            }else{
                                                                                delete objectMenu[value.menuId];  
                                                                                $("#transaction-header").find("span.fa-trash").remove();  
                                                                            }

                                                                            _this.fadeOut(500,function(){
                                                                                _this.remove();
                                                                                subtotal -= parseFloat(value.price);
                                                                                pos.display.calculatePOS();
                                                                            })

                                                                        }
                                                                    })
                                                                    
                                                                }),
                                                        )
                                                    )
                                            )

                                            pos.display.calculatePOS();
                                        })
                                        .addClass("col-xl-3 col-md-6 mb-4")
                                        .append(
                                            $("<div>")
                                                .addClass("card border-left-primary shadow h-100 py-2")
                                                .append(
                                                    $("<div>")
                                                        .addClass("card-body")
                                                        .append(
                                                            $("<div>")
                                                                .addClass("row no-gutters align-items-center")
                                                                .append(
                                                                    $("<div>")
                                                                        .addClass("col mr-2")
                                                                        .append(
                                                                            $("<div>")
                                                                                .addClass("text-xs font-weight-bold text-primary text-upper")
                                                                                .text(value.menuName)
                                                                                .append(
                                                                                    $("<div>")
                                                                                        .addClass("dropdown no-arrow float-right")
                                                                                        .append(
                                                                                            $("<div>")
                                                                                                .addClass("dropdown-menu dropdown-menu-right shadow animated--fade-in")
                                                                                                .attr({
                                                                                                    'aria-labelledby':'dropdownMenuLink'
                                                                                                })
                                                                                                .append(
                                                                                                    $("<div>")
                                                                                                        .addClass("dropdown-header")
                                                                                                        .text("Select a menu"),
                                                                                                    $("<a>")
                                                                                                        .addClass("dropdown-item")
                                                                                                        .attr({
                                                                                                            href:'#'
                                                                                                        })
                                                                                                        .text("View"),
                                                                                                    $("<a>")
                                                                                                        .addClass("dropdown-item")
                                                                                                        .attr({
                                                                                                            href:'#',
                                                                                                            'data-toggle':'modal',
                                                                                                            'data-target':'#updateMenuModal',
                                                                                                        })
                                                                                                        .click(function(){
                                                                                                            menuId = value.menuId
                                                                                                            $("#frm-update-menu").find("input[name=menuName]").val(value.menuName);
                                                                                                            $('select[name=menuTypeId] option').filter(function() { 
                                                                                                                return ($(this).val() == value.menuTypeId); //To select
                                                                                                            }).prop('selected', true);
                                                                                                            $("#frm-update-menu").find("input[name=price]").val(value.price);
                                                                                                        })
                                                                                                        .text("Update")
                                                                                                )
                                                                                        )
                                                                                ),
                                                                            
                                                                            $("<div>")
                                                                                .addClass("ribbon blue")
                                                                                .text(`₱ ${value.price}`),
                                                                        ),
                                                                    $("<div>")
                                                                        .addClass("col-auto"),
                                                                )
                                                        )
                                                )
                                        )
                                )
                            })
                        }else{

                        }
                        jsAddon.display.removefullPageLoader()
                    }
                })
            },
            addTransaction:(payload)=>{
                $.ajax({
                    type:'post',
                    url:addTransactionApi,
                    dataType:'json',
                    data:payload,
                    success:function(response){
                        if(!response._isError){
                            total = 0,
                            subtotal = 0,
                            discount = 0,
                            change = 0,
                            discountPercentage = 0;
                            canSubmit = false;
                            objectMenu = {};
                            note = '';
                            discountItems = {};
                            cash = 0;
                            $("#cash").val("");
                            $("#transaction-header").find(".fa-trash").remove();
                            pos.display.calculatePOS()
                            .then(data=>{
                                $("#selected-menu-list").empty();
                            })
                        }
                        jsAddon.display.swalMessage(response._isError,response.reason);
                    }
                })
            },
            getTransaction:(payload)=>{
                new Promise((resolve,reject)=>{
                    let from = $("input[name=from-date-transaction]").val();
                    let to   = $("input[name=to-date-transaction]").val();
                    $.ajax({
                        type:'post',
                        url:`${getTransactionApi}/${from}/${to}`,
                        dataType:'json',
                        data:payload,
                        beforeSend:function(){
                            
                            jsAddon.display.addfullPageLoader()

                            if ( $.fn.DataTable.isDataTable('#transaction-table') ) {
                                activeTable.clear();
                                activeTable.destroy();

                                $("#transaction-table")
                                .find("tbody")
                                .empty()
                            }

                           
                        },
                        success:function(response){
                            if(response._isError){
                                jsAddon.display.swalMessage(response._isError,response.reason);
                            }else{
                                $.each(response.data,function(k,v){
                                    
                                    let  orderItems = $("<ul>").addClass("list-group list-group-flush");
                                    let  discountItems = $("<ul>").addClass("list-group list-group-flush");

                                    $.each(JSON.parse(v.data),function(k,v){
                                        orderItems.append(
                                            $("<li>")
                                            .addClass("list-group-item d-flex justify-content-between align-items-center list-group-item-action").text(`${v.menuName}`)
                                            .append(
                                                $("<span>")
                                                    .addClass('badge badge-primary badge-pill')
                                                    .text(`${v.price}`)
                                            )
                                        )
                                    })
                                    $.each(JSON.parse(v.discount_items),function(k,v){
                                        discountItems.append(
                                            $("<li>").addClass("list-group-item d-flex justify-content-between align-items-center list-group-item-action").text(`${v.discount}`)
                                            .append(
                                                $("<span>")
                                                .addClass('badge badge-primary badge-pill')
                                                .text(`${v.percentage}%`)
                                            )
                                        )
                                    })


                                    $("#transaction-table")
                                    .find("tbody")
                                    .append(
                                        $("<tr>")
                                            .append(
                                                $("<td>").text(v.transactionid),
                                                $("<td>").text(v.transactionDate),
                                                $("<td>").append(
                                                    orderItems
                                                ),
                                                $("<td>").text(`${v.discount}%`),
                                                $("<td>").text(v.discount_amount),
                                                $("<td>").append(
                                                    discountItems
                                                ),
                                                $("<td>").text(v.cash),
                                                $("<td>").text( v.discount_amount > 0 ? v.total_price : 0 ),
                                                $("<td>").text(parseFloat(v.total_price) + parseFloat(v.discount_amount)),
                                                $("<td>").text(v.note),
                                                $("<td>").text('-'),
                                            )
                                    )
                                })
                            }
                            jsAddon.display.removefullPageLoader()
                            resolve(true)
                        }
                    })
                }).then(data=>{
                    if(data){
                        activeTable = $("#transaction-table").DataTable({
                            // "aoColumns": [
                            //     { "sTitle": "Purpose","sWidth": "20%"},
                                // { "bVisible": false, "aTargets": [ 0 ] }
                            // ],
                            "aoColumnDefs": [
                                // { "bVisible": false, "aTargets": [ 6 ] }
                            ] ,
                            "iDisplayLength": 10,
                            "aLengthMenu": [[10,25,50,75,100,-1],[10,25,50,75,100,"All"]],
                            "bAutoWidth": false,
                            "bSort": false,
                            "fnCreatedRow": function( nRow, aData, iDataIndex ) {
                                // let data = aData[0].split(":");
                                // let name = data[1];
                                // let id = data[0];
                                $(nRow).attr("id",`transaction-${aData[1]}`)
                            

                                $("td",nRow)
                                .last()
                                .empty()
                                .addClass("text-center")
                                .append(
                                    // $("<button>")
                                    // .addClass("btn btn-success btn-sm")
                                    // .text("Update")
                                    // .click(function(){
                                    //     transaction_id = aData[0];
                                    // }),
                                )

                            },
                            "footerCallback": function ( row, data, start, end, display ) {
                                var api = this.api(), data;
                     
                                // Remove the formatting to get integer data for summation
                                var intVal = function ( i ) {
                                    return typeof i === 'string' ?
                                        i.replace(/[\$,]/g, '')*1 :
                                        typeof i === 'number' ?
                                            i : 0;
                                };



                                discount_amount = api
                                    .column( 4 )
                                    .data()
                                    .reduce( function (a, b) {
                                        return intVal(a) + intVal(b);
                                    }, 0 );
                     
                                // Update footer
                                $( api.column( 4 ).footer() ).html(
                                    `Total: (₱) ${discount_amount}`
                                    // '(₱)'+pageTotal +' ( $(₱) '+ total +' total)'
                                    // '(₱)'+pageTotal +' ( $(₱) '+ total +' total)'
                                );




                                total_cash = api
                                    .column( 6 )
                                    .data()
                                    .reduce( function (a, b) {
                                        return intVal(a) + intVal(b);
                                    }, 0 );
                     
                                // Update footer
                                $( api.column( 6 ).footer() ).html(
                                    `Total: (₱) ${total_cash}`
                                    // '(₱)'+pageTotal +' ( $(₱) '+ total +' total)'
                                    // '(₱)'+pageTotal +' ( $(₱) '+ total +' total)'
                                );



                                total_discount = api
                                        .column( 7 )
                                        .data()
                                        .reduce( function (a, b) {
                                            return intVal(a) + intVal(b);
                                        }, 0 );
                        
                                    // Update footer
                                    $( api.column( 7 ).footer() ).html(
                                        `Total: (₱) ${total_discount}`
                                        // '(₱)'+pageTotal +' ( $(₱) '+ total +' total)'
                                        // '(₱)'+pageTotal +' ( $(₱) '+ total +' total)'
                                    );
                     
                                // Total over all pages
                                total = api
                                    .column( 8 )
                                    .data()
                                    .reduce( function (a, b) {
                                        return intVal(a) + intVal(b);
                                    }, 0 );
                     
                                // Update footer
                                $( api.column( 8 ).footer() ).html(
                                    `Total: (₱) ${total}`
                                    // '(₱)'+pageTotal +' ( $(₱) '+ total +' total)'
                                    // '(₱)'+pageTotal +' ( $(₱) '+ total +' total)'
                                );

                               
                            },
                            "fnInitComplete": function(oSettings, json) {
                                jsAddon.display.removefullPageLoader()
                                // menu.ajax.getUser();
                            }
                        });
                    }
                })
            },
            getDiscounts:()=>{
                return new Promise((resolve,reject)=>{
                    $.ajax({
                        type:'get',
                        url:`${getDiscountsApi}?accessKey=${accesskey}`,
                        dataType:'json',
                        success:function(response){
                            resolve(response);
                        }
                    })
                })
            },
            deAuth:()=>{
                Swal.fire({
                    title: 'Ready to Leave?',
                    text: "Select (Logout) below if you are ready to end your current session.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Logout'
                }).then((result) => {
                    $.ajax({
                        type:'get',
                        url:deautnApi,
                        dataType:'json',
                        beforeSend:function(){
                            jsAddon.display.addfullPageLoader();
                        },
                        success:function(response){
                            if(!response.isError){
                                localStorage.clear();
                                setTimeout(() => {
                                    window.location.href = baseUrl;
                                }, 2000);
                            }
                        }
                    })
                })
            }
        },
        display:{
            addDiscount:()=>{
                discount = parseFloat($("#discount-amount").val());
                pos.display.calculatePOS()
                .then(data=>{
                    $("#addDiscount").modal("hide")
                })
            },
            calculatePOS:()=>{
                return new Promise((res,rej)=>{
                    subtotal        = parseFloat(subtotal);
                    discount_amount  =  subtotal > 0 ? ((discount/subtotal) * 100).toFixed(2) : 0;
                    total           = (subtotal - discount_amount).toFixed(2) ;
                    change          = (cash - total).toFixed(2);

                    
                    if(isNaN(change)){
                        change = 0.00;
                    }
                    if(isNaN(discount_amount)){
                        discount_amount = 0.00;
                    }
                    if(isNaN(discount_amount)){
                        subtotal = 0.00;
                    }

                    if(isNaN(total)){
                        total = 0.00;
                    }

                    if(change >= 0 && total > 0){
                        canSubmit = true;
                    }else{
                        $("#system-pos-message").hide()
                        $("#system-pos-message").text("")
                        canSubmit = false;
                    }
                    

                    
                    $("#subtotal")
                        .text(subtotal.toFixed(2))
                    $("#discount")
                        .text(`${discount}%`)
                    $("#change")
                        .text(change)
                    $("#discount-amount")
                        .text(discount_amount)
                    $("#total")
                        .text( total )
                    
                    res(true);
                })
            },
            emptyAll:()=>{

                Swal.fire({
                    title: 'Are you sure?',
                    text: `Cancel transaction`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, cancel it!'
                }).then((result) => {
                    if (result.value) {
                        total               = 0;
                        discount_amount      = 0;
                        subtotal            = 0;
                        discount            = 0;
                        objectMenu          = {};
                        discountPercentage  = 0;
                        discountItems       = {};
                        cash                = 0;
                        canSubmit           = 0;
                        note                = '';
                        $("#cash")
                            .val("")
                        $("#subtotal")
                            .text("0.00")
                        $("#subtotal")
                            .text("0.00")
                        $("#discount")
                            .text('0.00')
                        $("#change")
                            .text('0.00')
                        $("#discount-amount")
                            .text('0.00')
                        $("#total")
                            .text('0.00')
                        pos_table.empty();
                        pos_message.empty();
                        $("#transaction-header").find("span.fa-trash").remove();
                    }
                })
            }
        }
    }

    pos.init();
    $("#submit-discount").click(function(){
        pos.display.addDiscount();
    })
    $("#submit-transaction").click(function(){

        let html = "";
        if( canSubmit == 0 ){
            if(change < 0){
                $("#system-pos-message").show()
                $("#system-pos-message").text("Insuficient cash")
                canSubmit = 0;
            }
            return false;
        }
       

        html += '<div class="table-responsive">';
            html += '<table class="table col-8 mx-auto" style="font-size:10pt;">';
                html += '<thead>';
                    html += '<tr>';
                        html += '<th>Item</th>';
                        html += '<th>Price</th>';
                    html += '</tr>';
                html += '</thead>';
            
                
                $.each(objectMenu,function(k,v){
                    html += '<tr>';
                        html += `<td>${v.menuName}</td>`;
                        html += `<td><span class="badge badge-primary badge-pill">${v.total_price}</span></td>`;
                    html += `</tr>`;
                })

                html += '<thead>';
                    html += '<tr>';
                        html += `<th colspan="2" class="text-right">Sub Total: ${subtotal}</th>`;
                    html += '</tr>';
                    if(discount > 0 ){
                        html += '<tr>';
                            html += `<th colspan="2" class="text-right">Disount : ${discount} %</th>`;
                        html += '</tr>';
                    }
                    if(discount_amount > 0 ){
                        html += '<tr>';
                            html += `<th colspan="2" class="text-right">Disount : ${discount_amount}</th>`;
                        html += '</tr>';
                    }
                    html += '<tr>';
                        html += `<th colspan="2" class="text-right">Total Due : ${total}</th>`;
                    html += '</tr>';
                  
                html += '</thead>';
            // html += `<li class="list-group-item text-right">`;
            //         html += `Sub Total - ${subtotal} <br> `;
            //         html += `Total - ${discount} <br>`;
            //         html += `Total Due - ${total}`;
            
            html += '</table>';
            html += `<p class="text-muted" style="font-size:15px">Note: ${note}</p>`;
        html += '</div>';

        Swal.fire({
            title: 'Preview Transaction',
            html:`${html}`,
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Submit'
          }).then((result) => {
            if (result.value) {
                let payload = {
                    accessKey:accesskey,
                    data:JSON.stringify(objectMenu),
                    discount:discountPercentage,
                    discount_amount:discount_amount,
                    discountItems:JSON.stringify(discountItems),
                    total_price:total,
                    note:note,
                    cash:cash,
                }
                pos.ajax.addTransaction(payload);
            }
          })
        
    })

    $("#v-transaction").click(function(){
        let payload = {
            'userid':userid,
            'accesskey':accesskey,
        }
        pos.ajax.getTransaction(payload);
    })

    $("input[type=date]").change(function(){
        let payload = {
            'userid':userid,
            'accesskey':accesskey,
        }
        pos.ajax.getTransaction(payload);
    })

    $('#addDiscount').on('shown.bs.modal', function (e) {
        pos.ajax.getDiscounts()
        .then(data=>{
            if(!data.isError){
                $("#discounts-items").empty();
                $.each(data.data,function(k,v){
                    $("#discounts-items").append(
                        $("<div>")
                            .addClass("custom-control custom-switch col-12")
                            .append(
                                $("<input>")
                                    .addClass("custom-control-input")
                                    .prop({
                                        'checked':discountItems.hasOwnProperty(v.discountId) ? true : false
                                    })
                                    .attr({
                                        id:v.tag,
                                        type:'checkbox',
                                        name:v.tag,
                                        value:v.percentage
                                    }).click(function(){
                                        let checkboxdiscount = $(this);
                                        
                                        if(checkboxdiscount.is(':checked')){
                                            discountPercentage += parseFloat(v.percentage);
                                            discountItems[v.discountId] = {
                                                'discountId':v.discountId,
                                                'discount':v.discount,
                                                'percentage':v.percentage,
                                            }
                                            
                                          //    discountPercentage 
                                        }else{
                                            delete discountItems[v.discountId];
                                            discountPercentage -= parseFloat(v.percentage);
                                        }
                                        discount = discountPercentage;
                                        pos.display.calculatePOS();
                                    }),
                                $("<label>")
                                    .addClass("custom-control-label")
                                    .attr({
                                        for:v.tag
                                    })
                                    .text(`${v.discount} ${v.percentage} %`)
                            )
                    )
                })
            }
        })
    })

    $("#add-note").click(function(){
        (async () => {

            const { value: text } = await Swal.fire({
              title: 'Add note',
              input: 'textarea',
              allowOutsideClick: false,
              inputValue:note,
              inputPlaceholder: 'Type your message here...',
              inputAttributes: {
                'aria-label': 'Type your message here'
              },
              showCancelButton: true
            }).then((result) => {
                if (result.value) {
                    note = result.value;
                }else{
                    note = note;
                }
              })

            
        })()
        
    })

    $("#cash").keyup(function(){
        if(!isNaN(parseFloat($(this).val())) && cash != $(this).val()){
            cash = $(this).val();
            pos.display.calculatePOS();
        }

    })

    $("#cancel").click(function(){
        pos.display.emptyAll();
    })

    window.addEventListener('beforeunload',(event) =>{
        if(!isLogout){
            jsAddon.display.setSessionData('session',session);
        }
    });

    $(".logout").click(function(){
        isLogout = true;
        pos.ajax.deAuth();
    })
    $(document).ready(function(){
        localStorage.removeItem('session');
    })
   
})