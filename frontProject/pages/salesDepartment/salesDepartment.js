var salesDepartment = Backbone.View.extend({
    el: $(appData.router.renderPageTo),
    template 		: 'salesDepartment',
    searchTimeout 	: 1,

    events: {
        'click #goBackToGoodOldPage': function(){window.history.back();}
    },


    initialize: function(){
        var _self = this;
        var test = [];

        appData.api.request('clients/myStats', {}, function (resp) {
            resp.sort(function(a,b){
                return b.sum - a.sum;
            });

            var salesResp = Object.assign([], resp);

            salesResp.sort(function(a,b){
                return (b.success + b.canceled) - (a.success + a.canceled) ;
            });

            var percent = (salesResp[0].success + salesResp[0].canceled)/100
            for (var i = 0; i < resp.length; i++) {
                resp[i].percentage = parseInt((salesResp[i].success + salesResp[i].canceled) / percent);

                singlePercent = (salesResp[i].success + salesResp[i].canceled) / 100;

                console.log(singlePercent)

                resp[i].successPercentage = salesResp[i].success / singlePercent
                resp[i].canceledPercentage = salesResp[i].canceled / singlePercent
            }

            for (var i = 0; i < resp.length; i++) {
                var fullPercent     = resp[0].sum/100;
                
                resp[i].fullPercent = fullPercent;                
            };

            _self.trueRender(resp);
        });
    },

    render: function () {}, 

    trueRender: function(data){
        var that = this;

        $.get("/pages/"+this.template+"/" + this.template + ".html", function(template){
            var compiled = _.template(template);


                that.$el.html(compiled({users: data}));
                console.log(data);
                that.initBars();
        
            $(document).trigger("pageRendered");
        });


        return this;
    }, 

    initBars: function(){

        setTimeout(function(){
            $('.wrapper_chart .column, .wrap_top_bottom.one').addClass('animation');
            $('[data-popup="tooltip"]').tooltip();
        }, 700);
    }
});

appData.BackboneViews.SalesDepartment = new salesDepartment();