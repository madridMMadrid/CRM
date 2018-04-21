var client_packagesComponent = Backbone.View.extend({
    el              : '#client-packageListContainer',
    template        : '',
    searchTimeout   : 1,

    events: {
        // смена пакета
        'click .popupChangePackage'                                        : 'popupChangePackage',
        'click #client-modalChangePackage .js-clients-package-popup'       : 'changePackageTransition',
        'change #client-modalChangePackage .clients_radio_icon_change'     : 'onClientChangePackageChooseChangement',
        'click .js-sendChangePackage'                                      : 'sending_the_package_option',

        // добавление пакета
        'click .js-popupAddPackage'                                        : 'popupAddPackage',
        'click #client-modalAddPackage .js-clients-package-popup'          : 'addPackageTransition',
        'click #client-addPackageBtn'                                      : 'addPackageServer',

        // продление пакета
        'click .js-popupProlongPackage'                                    : 'popupProlongPackage',
        'change .clients_radio input[type=radio]'                          : 'onProlongDaysChange',
        'click .js-sendProlongPackage'                                     : 'popupProlongPackageSend',             

        // 
        'click .deletePackage'                                             : 'cancelPackage',
        'blur .js-client_package-comment'                                  : 'onPackageCommentEdit',
        'click .js-client-getPackageId_first_td'                           : 'client_getPackageId_tab',
                       
        'keyup #addPackage-searchInput'                                    : 'onSearchInput',
        'keyup #changePackage-searchInput'                                 : 'onSearchInput',
        
    },

    data: {
        packages            : {},
        clientId            : {},
        newPackage          : {},
        currentPackageId    : null,
        addPackage          : {},
    },

    initialize: function(options) {
        this.parent             = options.parent;

        this.entireTemplate     = options.entireTemplate;
        this.template           = options.template;
        this.changeTemplate     = options.changePackageTemplate;
        this.addTemplate        = options.addPackageTemplate;
        this.prolongTemplate    = options.prolongPackageTemplate;
        this.paramsTemplate     = options.paramsTemplate;

        this.data.packages  = options.packages;
        this.data.clientId  = options.clientId;

        this.mapPackages();
    },

    update: function(addCalendar){
        var _self = this;

        appData.api.request('clients/getPackages', {
            clientId: _self.data.clientId,
        }, function(resp){
            _self.data.packages = resp;
            _self.mapPackages();

            var compiled = _.template(_self.template);
            _self.$el.html(compiled(_self.data));

            // console.log('renderCalendar', _self.parent.calendars.length)
            // for (var i = 0; i < _self.parent.calendars.length; i++) {
            //     _self.parent.calendars[i].rerender();
            // }

            if(addCalendar){
                window.location.reload();
            }

            _self.parent.getPurchaseHistory();
        });
    },

    mapPackages: function(){
        var _self = this;

        $.each(_self.data.packages, function(index, value){
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

            var action = value.action;

            if(action){
                switch(parseInt(action.actionType)){
                    case 0:
                        value.action.name = 'Ожидается смена пакета';
                        break;
                    case 1:
                        value.action.name = 'Ожидается продление пакета';
                        break;
                    case 2:
                        value.action.name = 'Ожидается добавление пакета';
                        break;
                    case 3:
                        value.action.name = 'Ожидается отмена';
                        break;
                    case 4:
                        value.action.name = 'Ожидается удаление';
                        break;
                    case 5:
                        value.action.name = 'Ожидается погашение долга';
                        break;
                }

                value.action.date = appData.dateFormat.formatClientString(value.action.date);
            }
        });

        console.log(_self.data.packages)
    },

    render: function(){
        var _self = this;
        var compiled = _.template(this.template);
        // console.log(compiled(this.data))
            this.$el.html(compiled(this.data));
    },

    popupAddPackage: function(){
        // render add package popup
        var _self       = this,
            compiled    = _.template(this.addTemplate);

        _self.data.ignoreId = null;

        _self.$('#client-modalAddPackage').html(compiled(_self.data.packages));

        _self.$(".steps-basic").steps({
            headerTag: "h6",
            bodyTag: "fieldset",
            transitionEffect: "fade",
            titleTemplate: '<span class="number">#index#</span> #title#',
            labels: {
                current     : "текущий шаг",
                pagination  : "страницы",
                finish      : "Добавить пакет",
                next        : "Далее",
                previous    : "Назад",
                loading     : "Грузимся ..."
            },
            onStepChanging: function(evt, currIndex, nextIndex){
                if(nextIndex == 1){
                    _self.renderAddressComponent();
                } else if(nextIndex == 2){
                    _self.renderTimeComponent();
                }

                if(currIndex > nextIndex){
                    _self.triggerSteps();
                }

                return true
            },
            onFinished: function (event, currentIndex) {
                alert("Form submitted.");
            }
        });


        _self.renderPackageList({
            el                  : '#client-modalAddPackage .js-client-addPackageList',
            template            : 'popupChangePackage-list',
            parentAccordion     : '#accordion-control_addPackage'
        });

        _self.triggerSteps(true);
    },

    triggerSteps: function(disable){
        var el = this.$('[role="menu"] [href="#next"]').closest('li');
        if(disable){
            el.addClass("disabled noevents").attr("aria-disabled", 'true');
        } else {
            el.removeClass("disabled noevents").attr("aria-disabled", 'false');
        }
    },

    addPackageTransition: function(evt){
        var _self = this;

        var elem = $(evt.currentTarget),
            id = elem.attr('data-id');

        _self.data.currentPackageId = id;

        appData.api.request('clients/showAddPackage', {
            'packageId': id
        }, function (resp) {
            _self.addPackage = resp;
            _self.pasteForeshowParams(resp, elem.closest('.panel').find('.currentPackageAndNew'));

            setTimeout(function(){
                if($('#client-modalAddPackage').find('.panel-collapse.collapse.in').length){
                    _self.triggerSteps();
                }
            }, 1000);
        });
    },

    renderAddressComponent: function(){
        var _self = this,
            componentAddress = new client_addressListComponent({
                element     : _self.$('.js-addressList-component')[0],
                btn         : this.$('[role="menu"] [href="#next"]').closest('li'),

                clientId    : _self.data.clientId,
                package     : {
                    id: 'newPackage',
                    renderRaw: true
                },

                template    : _self.entireTemplate,

                parent      : _self
            });

        componentAddress.render();
    },

    renderTimeComponent: function(){
        var _self = this,
            componentTime = new client_timeListComponent({
                element     : _self.$('.js-timeList-component')[0],
                btn         : this.$('[role="menu"] [href="#finish"]').closest('li'),
                clientId    : _self.data.clientId,

                package     : {
                    id: 'newPackage',
                    renderRaw: true
                },

                template    : _self.entireTemplate,

                parent      : _self
            });

        componentTime.render();
    },

    // рендрим параметры продления
    pasteForeshowParams: function(resp, mainElem, package){
        var _self       = this,
            compiled    = _.template(_self.paramsTemplate);

        mainElem.html(compiled({
            actions: resp
        }));

        var tooltips = mainElem.find('[data-popup="tooltip"]');
            tooltips.tooltip();
    },


    addPackageServer: function(evt){
        // send data to server
        var _self       = this,
            modal       = $(evt.currentTarget).closest('.modal'),
            accordion   = modal.find('.panel-collapse.collapse.in'),
            formData    = accordion.find('form').serializeArray(),

            price       = formData[0].value;

        var priceId = _self.addPackage.options.filter(function( obj ) {
          return obj.price == price;
        })[0].id;


        var data = {
            'clientId'      : _self.data.clientId,
            'packageId'     : accordion.parent().find('.js-clients-package-popup').attr('data-id'),
            'priceId'       : priceId,
            'amount'        : price,
            'paymentType'   : formData[1].value
        };

        appData.api.request('clients/addPackage', data, function (resp) {

            // (new PNotify({
            //      title: 'Запрос отправлен.',
            //      text: 'Пакет ожидает оплаты.',
            //      addclass: 'bg-success'
            // }));

            // _self.update(true);
            // $(".modal.in").modal("hide");
        });
    },

    popupProlongPackage: function(evt){
        var _self       = this,
            compiled    = _.template(this.prolongTemplate);

        _self.data.currentPackageId = $($(evt.currentTarget).closest('.js-client-getPackageId')).attr('data-id'),
        
        mainElem        = $('#client-modalProlongationPackage');

        appData.api.request('clients/showProlongPackage', {
            'clientId'          : _self.data.clientId,
            'clientPackageId'   : _self.data.currentPackageId,
        }, function (resp) {
            mainElem.html(compiled({
                prolong: resp,
                package: _self.data.packages[_self.data.currentPackageId]
            }));

            var tooltips = mainElem.find('[data-popup="tooltip"]');
                tooltips.tooltip();
        });
    },

    // срабатывает, когда меняем активное количество дней продления
    onProlongDaysChange: function(evt){
        var elem = evt.currentTarget;

        $(elem).closest('.modal').find('.js-prolongPackagePaymentAmmount').text(elem.value);
    },

    popupProlongPackageSend: function(evt){
        var _self               = this,
            openedUnit          = $(evt.currentTarget).closest('.modal'),
            form                = openedUnit.find('form').serializeArray();

        appData.api.request('clients/packageProlongation', {
            'clientPackageId'   : _self.data.currentPackageId,
            'amount'            : parseInt(openedUnit.find('.js-prolongPackagePaymentAmmount').text()),
            'paymentType'       : form[1].value
        }, function(resp){
            // console.log(resp, success)
            // if(success){
                // PNotify.desktop.permission();
                (new PNotify({
                     title: 'Запрос отправлен.',
                     text: 'Пакет ожидает оплаты.',
                     addclass: 'bg-success'
                }));
                _self.update();
                $(".modal.in").modal("hide");
            // }
            
        });
    },

    // рендрим пакеты оформеленные на клиената
    renderPackageList: function(options){
        var _self = this;

        options = $.extend(true, {
            el              : '#accordion-control',
            request         : 'package/packagesList',
            template        : 'popupAddPackage',
            requestOptions  : {
                from: 0,
                to: 20
            },
            mapping : function(val, ind){
                var color = val.name;

                if (color.indexOf('Silver') != -1){
                    val.type = 'Silver';
                } else if (color.indexOf('Gold') != -1){
                    val.type = 'Gold';
                }

                if(options.parentAccordion){
                    val.parentAccordion = options.parentAccordion;
                }

                if(_self.data.ignoreId && val.id == _self.data.ignoreId){
                    return null;
                } else {
                    return val;
                }

            }
        }, options);

        $('.js-client-getPackageId.no-select .js-client_package-comment').attr('contenteditable', false);
        

        getModule('clientList', function(){
            var clientList = new clientListView({
                options : options
            });
        });
    },



    //при введении данных в поле поиска перерендрим список, чтобы он соответствовал фильтру
    onSearchInput: function(evt){
        var that = this,
            params = {
                el: $(evt.target).closest('.modal-content').find('.js-client-packageList')
            };

        console.log($(evt.target).closest('.modal-content').find('.js-client-packageList'))

        clearTimeout(this.searchTimeout);
        this.searchTimeout = setTimeout(function(){
            var input = $(evt.target).val();

            console.log(input)

            // request server for string
            // console.log(params.el.attr('id'), params.el);
            if(input.length > 2){
                params = $.extend(true, {
                    request: input ? 'package/packagesSearch' : 'package/packagesList',
                    requestOptions  : {
                        string: input
                    },
                    template: 'popupChangePackage-list'
                }, params);
            }

            that.renderPackageList(params);   
        }, 300);
    },

    //отображение текущего пакета в popup смена пакета
    popupChangePackage: function(e) {
        var _self       = this;
        
        _self.data.ignoreId = $($(e.currentTarget).closest('.js-client-getPackageId')).attr('data-packageid');
        _self.data.currentPackageId = $($(e.currentTarget).closest('.js-client-getPackageId')).attr('data-id');

        var template    = _self.changeTemplate,
            data        = _self.data.packages[_self.data.currentPackageId],
            compiled    = _.template(template);

        $('#client-modalChangePackage').html(compiled(data));

        _self.renderPackageList({
            el          : '#client-modalChangePackage #accordion-control',
            template    : 'popupChangePackage-list',
            ignoreId    : _self.data.ignoreId
        });
    },

    // рендрим внутренности пакета при клике на него, в popup смена пакета
    changePackageTransition: function(e) {
        _self = this;
        
        var selfCurrentTarget = $(e.currentTarget),
            packageId = selfCurrentTarget.attr('data-id'),
            tooltips = $('.js-client-packageList').find('[data-popup="tooltip"]');
            tooltips.tooltip("destroy");


        appData.api.request('clients/foreshowChangePackage', {
            clientId: _self.data.clientId,
            clientPackageId: _self.data.currentPackageId,
            newPackageId: packageId,
        }, function(resp){
            getTemplate('currentPackageAndNew', function(template){
                _self.data.newPackage = resp;

                var renderTo = $(e.currentTarget).closest('.panel-white').find('.currentPackageAndNew');

                var compiled = _.template(template);
                renderTo.html(compiled(resp));

                Ladda.bind('.btn-ladda-spinner', {
                    dataSpinnerSize: 16,
                    timeout: 2000
                });
                
                var tooltips = $('.js-client-packageList').find('[data-popup="tooltip"]');
                tooltips.tooltip();

                $('.js-client-changPackageeOnSum').text('0');

                setTimeout(function(){
                    $('.js-sendChangePackage').prop('disabled', !$('.panel-collapse.collapse').hasClass('in'));
                }, 300);

            });
        });
    },

    // срабатывает при смене доплачивать/не доплачивать в попапе смена пакета
    onClientChangePackageChooseChangement: function(evt) {
        var clickedNode     = $(evt.currentTarget),
            allNode         = clickedNode.closest('.js-mainChangePackageNode'),
            currentLiIndex  = clickedNode.closest('li').index(),
            manipulateView  = $('.js-client-hideOnNoSum,.js-client-hideOnNoCard');

        if(currentLiIndex > 0){
            // мы кликнули на доплатить
            sumToDisplay = _self.data.newPackage.newPackage.paymentActions[currentLiIndex-1].priceToPay;
            manipulateView.removeClass('hidden_number');
        } else {
            // мы кликнули на не доплачивать
            manipulateView.addClass('hidden_number');
            sumToDisplay = '0';
        }

        $('.js-client-changPackageeOnSum').text(sumToDisplay);
    },

    //Сбор и отправка информации о новом пакете
    sending_the_package_option: function(evt) {

        var _self               = this,
            openedUnit          = $(evt.currentTarget).closest('#client-modalChangePackage').find('.panel-collapse.collapse.in'),
            form                = openedUnit.find('form').serializeArray();
            paymentOpenedUnit   = openedUnit.find('.js-client-hideOnNoCard input:radio:checked').val();

        var sendData = {
            'clientPackageId'   : _self.data.currentPackageId,
            'packageId'         : _self.data.newPackage.newPackage.id,
            'amount'            : parseInt(openedUnit.find('.js-client-changPackageeOnSum').text()),
            'priceId'           : form[0].value
        }

        if(form[1]){
            sendData.paymentType = form[1].value;
        }

        appData.api.request('clients/changePackage', sendData, function(resp){
            // console.log(resp, success)
            // if(success){
                // PNotify.desktop.permission();
                (new PNotify({
                     title: 'Запрос отправлен.',
                     text: 'Пакет ожидает оплаты.',
                     addclass: 'bg-success'
                }));
                _self.update();
                $(".modal.in").modal("hide");
                // console.log(evt.currentTarget)
            // }
            
        });
    },

    cancelPackage: function(evt) {
        var _self       = this,
            target      = $(evt.currentTarget),
            packageId   = parseInt(target.closest('[data-packageid]').attr('data-id'));


        appData.api.request('clients/cancelPackageChange', {
            clientPackageId: packageId
        }, function (resp) {
            _self.update();
        });
    },

    // при изменении комментария к пакету отправляем данные на сервачок
    onPackageCommentEdit: function(event){
        var elem    = $(event.currentTarget),
            comment = elem.text();

        if(comment.length > 0){
            var id = elem.closest('.js-client-getPackageId').attr('data-id');

            appData.api.request('clients/changePackageComment', {
                clientPackageId          : id,
                comment                  : comment
            }, function(resp){
                console.log(resp)
            });
        }
    },

    // при клике меняем календарь относительно вбаранного пакета
    client_getPackageId_tab: function(e) {
        var _self = this;

        var content = $(e.currentTarget);
        var index = $(e.currentTarget).parent().index();

        if (content.parent().not('active')){
            content.parent().addClass('active').siblings().removeClass('active');
            
        } else {
            content.parent().removeClass('active');
        }

        $('.tab_content > li').one('transitionend', function(){
            $('.tab_content > li').css('display', 'none').eq(index).css('display', 'block').removeClass('fade');
        }).addClass('fade');
    
    },
    // end при клике меняем календарь отнасительно вбаранного пакета
});