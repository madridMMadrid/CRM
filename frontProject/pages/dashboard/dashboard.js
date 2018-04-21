// Dashboard page
var dashboardView = Backbone.View.extend({
    el: $(appData.router.renderPageTo),
    template: 'dashboard',

    events: {
        'submit #dashboard-addClientForm': 'addClient'
    },

    data: {
        cities: []
    },

    initialize: function(){
        var _self = this;
        // appData.api.request('clients/initWebsocket', {}, function(resp, success){
        //     console.log(resp);
        //     // data loaded, load template for parsing the data
        //     _self.render(resp);
        // });
        appData.api.request('clients/getCities', {}, function (resp) {
            _self.data.cities = resp;
            _self.renderCitiesSelect(resp);
        });
    },

    render: function () {
        var self = this;

        $.get("/pages/"+this.template+"/" + this.template + ".html", function(template){
            var template    = $(template),
                page        = template.filter('#dashboard').html(),
                compiled    = _.template(page);

            self.$el.html(compiled());

            var dashboardBlocker = new BlockUI('#dashboard-newClients', {
                light: true
            });
            dashboardBlocker.block();

            var requiresAttentionClientsBlocker = new BlockUI('#dashboard-requiresAttentionClients', {
                light: true
            });
            requiresAttentionClientsBlocker.block();

            getModule('clientList', function(){
                // init widgets
                // модуль новые клиенты

                var newClients = new clientListView({
                    options : {
                        el          : '#dashboard-newClients',
                        template    : 'clientCard',
                        indicator   : $('#dashboard-newClientsIndicator'),
                        request     : 'clients/getRequests',
                        callback    : function(){
                            dashboardBlocker.unblock();
                        }
                    }
                });

                // модуль действующие клиенты
                var requiresAttentionClients = new clientListView({
                    options : {
                        el          : '#dashboard-requiresAttentionClients',
                        template    : 'clientCard',
                        indicator   : $('#dashboard-requiresAttentionClientsIndicator'),
                        request     : 'clients/requireAttention',
                        mapping     : function(val, ind){
                            val.id      = val.clientId;
                            val.phone   = val.clientPhone;
                            val.name    = val.clientName;
                            
                            return val;
                        },
                        callback    : function(){
                            requiresAttentionClientsBlocker.unblock();
                        }
                    }
                });

                // progressCounter('#hours-available-progress', 38, 2, "#F06292", 0.68, "icon-watch text-pink-400", 'Выполнение плана продаж', '')

                $(document).trigger("pageRendered");
            });
        });

		return this;
    }, 

    renderCitiesSelect: function(resp){
        var options = '',
            select = this.$('.js-citySelector');

        for (var i = 0; i < resp.length; i++) {
            options+='<option value="'+resp[i].id+'">'+resp[i].name+'</option>';
        }

        select.append(options)
        select.selectpicker();
    },

    addClient: function(evt){
        evt.preventDefault();
        var addClientBtnLadda = Ladda.create(document.getElementById('dashboard-addClientBtn'));
        addClientBtnLadda.start();

        var form = serializeForm($('#dashboard-addClientForm'));

        form.city = this.$('.js-citySelector').val();

        // navigate employee to client's page
        appData.api.request('clients/store', form, function (resp) {
            $('.modal').modal('hide');
            setTimeout(function(){
                window.location.href = "#client/"+parseInt(resp);
            }, 500);
        });
    }
});

appData.BackboneViews.Dashboard = new dashboardView();