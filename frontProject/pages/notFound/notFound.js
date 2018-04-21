var NotFound = Backbone.View.extend({
    el              : $(appData.router.renderPageTo),
    template 		: 'notFound',

    initialize: function(){},

    render: function () {
        var that = this;

        $.get("/pages/"+this.template+"/" + this.template + ".html", function(template){
            that.$el.html(template);

        });

        return this;
    }, 


});

appData.BackboneViews.NotFound = new NotFound();