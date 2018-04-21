var employee = Backbone.View.extend({
    el: $(appData.router.renderPageTo),
    template 		: 'employee',
    searchTimeout 	: 1,

    events: {
        'click #goBackToGoodOldPage': function(){window.history.back();}
    },

    initialize: function(){
        var _self = this;

        if(appData.router.subpageParams){
            appData.api.request('clients/getClientById', {
                id: appData.router.subpageParams[1]
            }, function(resp, success){
                // data loaded, load template for parsing the data
                _self.trueRender(resp);
            });
        } else {
            // navigate to client database
            
        }
    },

    render: function () {
        
    }, 

    trueRender: function(resp){
        console.log('rendered')
        var that = this;

        $.get("/pages/"+this.template+"/" + this.template + ".html", function(template){
            var compiled = _.template(template);

            that.$el.html(compiled(resp));
        });

        return this;
    }
});

appData.BackboneViews.Employee = new employee();