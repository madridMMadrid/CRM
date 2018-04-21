var navbar = Backbone.View.extend({
    el: $(document.getElementById('navbar')),
    template: 'navbar',

    events: {
        'click #sidebarMenu li'       : 'changeActiveTab'
    },

    // appData.router.getLocation()

    changeActiveTab: function(evt){
    	var element = $(evt.target).closest('li');
    	this.$el.find('li.active').removeClass('active');
    	element.addClass('active');

    },

    checkConnection: function(){
        console.log(navigator.online)
    },

    render: function () {
        var _self = this;

        $.get("/modules/"+this.template+"/" + this.template + ".html", function(template){
            // set active tab
            var navbar      = $(template).filter('.navbar'),
                className   = $(template).filter('.sidebar').attr('class'),
                temp        = $(template).find('a[href="#'+appData.router.getLocation()+'"]');

            temp = temp.length ? temp.parent().addClass('active').closest('.'+className.split(' ')[0]) : template;

            var compiled = _.template(template);

            _self.$el.html(compiled({
                user: appData.user.data,
                pages: appData.pages
            }));

			// _self.$el.html(temp);
            $('.navbar-container').html(navbar)

		});

		return this;
    }
});

appData.BackboneViews.Navbar = new navbar();