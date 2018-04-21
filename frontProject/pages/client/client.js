var ClientPageView = Backbone.View.extend({
    el: $(appData.router.renderPageTo),
    template 		: 'client',
    pageData        : null,
    calendars       : [],
    searchTimeout   : 1,

    sessionVars : {
        packageId: null,
        newPackage: null,
        mapLoaded: false
    },

    selected: {
        addressId: null,
        timeId: null
    },

    components: {
        addressPopup    : null,
        packages        : null,
        timeList        : [],
        addressList     : [],
    },

    timeouts: {
        cert: null
    },

    templates: {
        fetchCertificate: null
    },

    events: {
        'click #goBackToGoodOldPage'              : function(){window.history.back();},
        'submit .main-search'                     : function(e){e.preventDefault();},
        'click #client_deptBillSubmit'            : 'deptBillSubmit',

        // комментарии
        'click .js-clients_comment-add'           : 'commentAdd', // добавляет комментарий
        'keyup #taskInput'                        : 'onCommentInput', //сработает только если  focus находиться на input

        'click .modal-backdrop'                   : 'removepopupChangePackage',
        'click #end_call_buying'                  : 'end_call_buying',
        'click .currentPurchaseHistory'           : 'getPurchaseHistory',

        'keyup #client-certificateNumberInput'    : 'fetchCertificate',
        'click .js-activateCertificate'           : 'activateCertificate',
        'keyup #getHistory-searchInput'           : 'onSearchInputHistory',
    },

    initialize: function(){
        var _self = this;
        if(appData.router.subpageParams){
            appData.api.request('clients/getClientById', {
                id: appData.router.subpageParams[1]
            }, function(resp, success){
                // map data

                // форматируем дату в человеческий вид
                if(resp.comments){
                    for(var i=0, len=resp.comments.length; i<len; i++){
                        var dateArr = appData.dateFormat.formatClientString(resp.comments[i].date);
                        resp.comments[i].date = dateArr;
                    }
                }

                if(resp.comments){
                    $.each(resp.packages, function(index, value){
                        var color = value.packageName;
                        if (color.indexOf('Silver') != -1){
                            value.type = 'Silver';
                        } else if (color.indexOf('Gold') != -1){
                            value.type = 'Gold';
                        }

                        var daysRemain = value.daysRemain;
                        
                        if(daysRemain < 2){
                            value.badgeColor = "bg-danger-400";
                        } else if(daysRemain < 5){
                            value.badgeColor = "bg-warning-400";
                        }
                    })
                }
                console.log(resp);


                // data loaded, load template for parsing the data
                _self.pageData = resp;
                _self.trueRender(resp);

            });
  
        } else {
            // navigate to client database
        }
    },

    render: function () {}, 

    trueRender: function(resp){

        var that = this;

        $.get("/pages/"+this.template+"/" + this.template + ".html", function(template){
            var template    = $(template),
                page        = template.filter('#client-main-page').html(),
                compiled    = _.template(page);

            that.$el.html(compiled(resp));


            that.templates.fetchCertificate = template.filter('#foreshowAddCertificateTemplate').html();


            var componentsPath = "/pages/"+that.template+"/components/";

            // включаем компонент пакеты
            $.getScript(componentsPath+'packages.js', function(data){
                that.components.packages = new client_packagesComponent({
                    // templates
                    entireTemplate          : template,
                    template                : template.filter('#client-packageTemplate').html(),
                    changePackageTemplate   : template.filter('#client-changePackagePopupTemplate').html(),
                    addPackageTemplate      : template.filter('#client-addPackageTemplate').html(),
                    prolongPackageTemplate  : template.filter('#client-prolongPackageTemplate').html(),
                    paramsTemplate          : template.filter('#client-packageParams').html(),
                    addressTemplate         : template.filter('#client-addressListTemplate').html(),

                    // parent
                    parent                  : appData.BackboneViews.Client,

                    // data
                    packages                : that.pageData.packages,
                    clientId                : that.pageData.id,
                });

                that.components.packages.render();
                 // табы пакетов отнасительно месяцев
                $('.tab_content:not(:first) > li').css('display', 'none');
                $('.table .js-client-getPackageId_first_td:first').trigger('click');
                // end табы пакетов отнасительно месяцев
            });

            // включаем компонент попап добавление адреса
            // $.getScript(componentsPath+'addressPopup.js', function(data){
            //     that.components.addressPopup = new client_addAddressPopupComponent({
            //         clientId    : that.pageData.id,
            //         parent      : appData.BackboneViews.Client
            //     });
            //     that.components.addressPopup.render();
            // });

            Ladda.bind('.btn-ladda-spinner', {
                dataSpinnerSize: 16
            });

            $(document).trigger("pageRendered");
            $('#tasksList').scrollTop($('#tasksList')[0].scrollHeight);

            // включаем календарь доставки
            $.getScript(componentsPath+'timeList.js', function(data){
                $.getScript(componentsPath+'addressList.js', function(data){
                    $('.js-clientCalendar').each(function(index, elem){
                        var thisPackageName = Object.keys(that.pageData.packages),
                            thisPackage     = $(that.pageData.packages).get(0)[thisPackageName[index]];

                        that.calendars.push(new Calendarity(this, {
                            greens              : thisPackage.daysRemain - thisPackage.deliveries.searchByKeyValue('state', 0).length,
                            deliveryMax         : thisPackage.daysRemain,
                            resp                : thisPackage.deliveries,
                            clientPackageId     : thisPackage.id,
                            period              : thisPackage.period
                        }));

                        // включаем компонент время доставки для каждого пакета
                        var component = that.components.timeList.push(new client_timeListComponent({
                            element     : '#client-timeList-'+thisPackage.id,
                            clientId    : that.pageData.id,
                            package     : thisPackage,

                            template    : template,

                            parent      : appData.BackboneViews.Client
                        }));

                        that.components.timeList[component-1].render();

                         // включаем компонент попап список адресов адреса
                        var componentAddress = that.components.addressList.push(new client_addressListComponent({
                            element     : '#client-addressList-'+thisPackage.id,
                            clientId    : that.pageData.id,
                            package     : thisPackage,

                            template    : template,

                            parent      : appData.BackboneViews.Client
                        }));
                        that.components.addressList[componentAddress-1].render();
                    });
                });
            });


            // ставим скролл в комментариях на автоподгрузку
            var commentsScroll = new InfiniteScroll($('#tasksList'), {
                reverse         : true,
                request         : 'clients/getClientComments',
                requestOptions  : {
                    clientId: resp.id
                }
            }, function(data){
                getTemplate('client_comments', function(template){
                    // format date in a readable way
                    for(var i=0, len=data.length; i<len; i++){
                        var dateArr = appData.dateFormat.formatClientString(data[i].date);
                        data[i].date = dateArr;
                    }

                    var compiled        = _.template(template),
                        baseElem        = $('#tasksList'),
                        baseScroll      = baseElem[0].scrollHeight;

                    baseElem.prepend(compiled({comments: data}));
                    // set scroll to the place it belongs
                    baseElem[0].scrollTop = baseElem[0].scrollHeight - baseScroll;
                });
            });

        });



        return this;
    },

    onSearchInputHistory: function(evt){

        var _self = this;

        clearTimeout(_self.searchTimeout);
        _self.searchTimeout = setTimeout(function(){
            var input = $(evt.target).val();


            if(input.length > 2){
                _self.renderHistory({
                    'string': input
                });
            } else if(input.length == 0){
                _self.renderHistory();
            }
        }, 300);
    },

    // получаем история покупок
    getPurchaseHistory: function(e) {
        this.renderHistory();
    },

    renderHistory: function(opts){
        var _self = this;

        opts = $.extend(opts, {
            clientId: _self.pageData.id,
        });

        appData.api.request('clients/getHistory', opts, function(resp){
            $.each(resp, function(index, value){
                var color = value.name;
                if (color.indexOf('Silver') != -1){
                    value.type = 'Silver';
                } else if (color.indexOf('Gold') != -1){
                    value.type = 'Gold';
                }
            });

            for(var i=0, len=resp.length; i<len; i++){
                var item = resp[i].updatedAt;
                resp[i].updatedAt = appData.dateFormat.formatClientString(item);
            }


            getTemplate('getHistory', function(template){

                var renderTo = $('#currentPurchaseHistory');

                var compiled = _.template(template);
                renderTo.html(compiled({
                    data: resp
                }));
                // console.log({data: resp});
                renderTo.find('[data-popup="tooltip"]').tooltip();


                if(resp.length){
                    var histotyScroll = new InfiniteScroll(renderTo, {
                        request         : 'clients/getHistory',
                        requestOptions  : {
                            clientId: _self.pageData.id
                        }
                    }, function(data){
                        getTemplate('getHistory', function(template){

                            var compiled        = _.template(template),
                                baseElem        = renderTo,
                                baseScroll      = baseElem[0].scrollHeight;

                            baseElem.prepend(compiled({data}));
                            // set scroll to the place it belongs
                            baseElem[0].scrollTop = baseElem[0].scrollHeight - baseScroll;

                            baseElem.find('[data-popup="tooltip"]').tooltip();
                        });
                    });
                }
            });
        });
    },

    fetchCertificate: function(){
        var _self           = this,
            popup           = $('#client-activateCertificate'),
            progressBar     = popup.find('.form-control-feedback'),
            btn             = popup.find('.js-activateCertificate'),
            input           = popup.find('[name="certificateNumber"]');

        btn.removeAttr('data-cert-id');

        clearTimeout(this.timeouts.cert);
        this.timeouts.cert = setTimeout(function(){

            blockBtn(true);
            appData.api.request('clients/getCertificate', {
                'number': input.val()
            }, function (resp) {
                console.log(resp)
                if(resp){
                    var blockIndex = resp.isActive;

                    resp.type                   = resp.type == 0 ? ' рублей' : '%';
                    resp.certificateExpiration  = appData.dateFormat.formatClientString(resp.expiration);
                    resp.isActive               = resp.isActive ? 'да' : 'нет';

                    compiled = _.template(_self.templates.fetchCertificate);
                    $('#fetchCertificateContainer').html(compiled(resp));

                    btn.attr('data-cert-id', resp.id);
                    blockBtn(blockIndex);
                } else {
                    blockBtn();
                }
            });
        }, 550)

        function blockBtn(block){
            if(block){
                console.log('blockin')
                progressBar.css('opacity', 1);
                btn.attr('disabled', 'disabled');
            } else {
                console.log('blockout')
                progressBar.css('opacity', 0);
                btn.removeAttr('disabled');
            }
        }
    },

    activateCertificate: function(evt){
        var _self = this;

        appData.api.request('clients/activateCertificate', {
            'certificateId' : $(evt.currentTarget).attr('data-cert-id'),
            'clientId'      : _self.pageData.id
        }, function (resp) {
            console.log(resp)
            $('modal').modal('hide');
        });
    },

    onCommentInput: function(e){
        //отправляеться сообщение тоько при focus`е 
        if (e.keyCode == 13 && e.ctrlKey) {
            this.commentAdd();
        }
    },

    removepopupChangePackage: function() {
        $('#client-modalChangePackage').children().remove();
    },

    commentAdd: function(e) {

        var _self = this;

        var $tasksList = $('#tasksList');
        var $taskInput = $('#taskInput');

        var comment = $taskInput.val();

        var commentsBtnLadda = Ladda.create(document.querySelector('.js-clients_comment-add'));
        commentsBtnLadda.start();


        if ($taskInput.val().length > 2) {
                appData.api.request('clients/pushComment', {
                clientId: _self.pageData.id,
                comment : comment

            }, function(resp, success){
                // console.log(resp);
                if(success){
                    var date = appData.dateFormat.formatClientString(new Date());

                    $tasksList.append("<li class='media reversed'><div class='media-body'><div class='media-content'>"
                        + comment 
                        + "</div><span class='media-annotation display-block mt-10'>"
                        + date +
                        "</span></div><div class='media-right'><a href='assets/images/placeholder.jpg'><img src='assets/images/placeholder.jpg' class='img-circle img-md' alt=''></a></div></li>");
                        $taskInput.val('');
                    $('#tasksList').scrollTop($('#tasksList')[0].scrollHeight);
                    commentsBtnLadda.remove();
                }
            });
        }
            
    },

    // погашение задолженности
    deptBillSubmit: function(evt){
        evt.preventDefault();
        // set spinner
        var _self               = this,
            btn                 = $(evt.target),
            openedUnit          = $(evt.currentTarget).closest('#client-modalChangeDebt'),
            paymentOpenedUnit   = openedUnit.find('.js-client_debt input:radio:checked').val(),
            input_debt          = openedUnit.find('.input_debt input').val();

            console.log(paymentOpenedUnit, input_debt);

        btn.button('Высчитать счет за задолжность');

        appData.api.request('clients/payDebt', {
            'clientId'      : _self.pageData.id,
            'amount'        : input_debt,
            'paymentType'   : paymentOpenedUnit
        }, function (resp) {

            console.log(resp);
            btn.button('reset');
            var btnActivator = $('#client_openDebtpaymentPopup');
            btnActivator.attr('disabled', 'disabled');
            btnActivator.text('Ожидание оплаты');
            $('#client-modalChangeDebt').modal('hide');

        });

           
    },

    // завершить звонок с покупкой
    end_call_buying: function() {
        console.log('test');
    }
});

appData.BackboneViews.Client = new ClientPageView();