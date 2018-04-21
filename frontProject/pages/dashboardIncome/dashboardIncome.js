var DashboardIncome = Backbone.View.extend({
    el              : $(appData.router.renderPageTo),
    template 		: 'dashboardIncome',
    searchTimeout   : 1,

    events: {
        'click .confirmPayment'                  : 'confirmPayment',
        'keyup #dashboardIncome-searchInput'     : 'onSearchInput',
        'blur .dashboardIncome_amount'           : 'onAmmountBlur',
        'click .dashboardIncomeUserBlock'        : 'dashboardIncomeUserBlock',
    },

    pageData: {
        payments: []
    },

    templates: {
        basic: '',
        payments: ''
    },

    initialize: function(){},

    render: function () {


        var _self = this;

        $.get("/pages/"+this.template+"/" + this.template + ".html", function(template){
            _self.templates.basic    = $(template).filter('#main-template').html(),
            _self.templates.payments = $(template).filter('#income-payments').html();


            var compiled = _.template(_self.templates.basic);

            _self.$el.html(compiled({}));
            _self.getPaymentsServer({}, _self.renderPayments);


            // ставим скролл в приеме бабла на автоподгрузку
            var cashScroll = new InfiniteScroll($('.infiniteScroll'), {
                request         : 'clients/getPayments'
            }, function(data){
                    var compiled        = _.template(_self.templates.payments),
                        baseElem        = $('.js-wrapp_dashboardIncome'),
                        baseScroll      = baseElem[0].scrollHeight;

                    baseElem.append(compiled({payments: data}));
                    // set scroll to the place it belongs
                    baseElem[0].scrollTop = baseElem[0].scrollHeight - baseScroll;
                
            });

            Ladda.bind('.btn-ladda-spinner', {
                dataSpinnerSize: 16,
                timeout: 2000
            });


            $(document).trigger("pageRendered");
            $('[data-popup="tooltip"]').tooltip();
        });
    }, 

    dashboardIncomeUserBlock: function(evt) {

        var _self            = this,
            clientId         = $(evt.currentTarget).closest('.inside_id').attr('data-clientId');

        _self.test(clientId);

        appData.api.request('clients/blockClient', {
            clientId   : clientId,
            type        : 1,
            comment     : 'Чё, пацаны, аниме?'
        }, function (resp) {

            _self.getPaymentsServer({}, _self.renderPayments);

                new PNotify({
                    title: 'Внимание!',
                    text: 'Клиент заблокирован!',
                    addclass: 'bg-warning'
                });
        });
    },

    getPaymentsServer: function(options, callback){
        // Список неподтвержденных оплат
        var _self = this;

        appData.api.request('clients/getPayments', options, function (resp) {

            _self.pageData.payments = resp;

            // форматируем дату в вид читабельный - "йода"
            for(var i=0, len=_self.pageData.payments.length; i<len; i++){
                var dateArr = appData.dateFormat.formatClientString(_self.pageData.payments[i].createdAt);
                _self.pageData.payments[i].createdAt = dateArr;
            };

            console.log(_self.pageData.payments);


                $.each(_self.pageData.payments, function(index, value){
                    var color = value.packageName;
                    if (color.indexOf('Silver') != -1){
                        value.type = 'Silver';
                    } else if (color.indexOf('Gold') != -1){
                        value.type = 'Gold';
                    }
                });

            callback(_self);
        });
    },

    renderPayments: function(_self){
        var compiled = _.template(_self.templates.payments);

        $('.js-wrapp_dashboardIncome').html(compiled({
            payments: _self.pageData.payments
        }));
    },

    onAmmountBlur: function(evt){

        var _self       = this,
            input       = $(evt.currentTarget),
            paymentId   = input.closest('[data-paymentid]').attr('data-paymentid');

        var thisPayment = _self.pageData.payments.filter(function(obj){
            return obj.paymentId == paymentId;
        })[0];


        if(parseInt(input.val()) > parseInt(thisPayment.amount)){
            input.val(thisPayment.amount)
        }
    },

    confirmPayment: function(evt) {

        var _self                       = this,
            target                      = $(evt.currentTarget).closest('[data-paymentId]'),
            dashboardIncome_paymentId   = target.attr('data-paymentId'),
            dashboardIncome_amount      = target.find('.dashboardIncome_amount').val(),
            dashboardIncome_choice      = target.find('.dashboardIncome input:radio:checked').val(),
            dashboardIncome_amount_true = target.find('.dashboardIncome_amount_true').text();


        // Подтвердить оплату
        appData.api.request('clients/confirmPayment', {
            'paymentId'     : dashboardIncome_paymentId,
            'amount'        : dashboardIncome_amount,
            'paymentType'   : dashboardIncome_choice

        }, function (resp) {
            if(resp == true){
                console.log(resp);
                
                    _self.getPaymentsServer({}, _self.renderPayments);

                    new PNotify({
                        title: 'Оплата произведена',
                        addclass: 'bg-success'
                    });
                
                
            } 
        });

    },

    onSearchInput: function(evt){
        var _self = this;
        // console.log(params.el);

        clearTimeout(_self.searchTimeout);
        _self.searchTimeout = setTimeout(function(){
            var input = $(evt.target).val();
            // request server for string
            // console.log(params.el.attr('id'), params.el);

            if(input.length > 2){
                _self.getPaymentsServer({
                    'query': input
                }, _self.renderPayments);

            } else if(input.length == 0){
                _self.getPaymentsServer({}, _self.renderPayments);
            }
        }, 300);
    },

});

appData.BackboneViews.DashboardIncome = new DashboardIncome();