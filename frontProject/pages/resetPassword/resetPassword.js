var ResetPasswordPage = Backbone.View.extend({
    el			    : $(appData.renderPageTo),
    template	    : 'resetPassword',

    events: {
        'submit .resetPass form'        : 'onForgotSubmit'
    },

    onSubmit: function(e){
        e.preventDefault();
    },

    applyMask: function(){
        $('[type="tel"]').mask("+7 (999) 999-99-99",{autoclear: false});
    },

    render: function () {
        var self = this;

        $.get("/pages/"+this.template+"/" + this.template + ".html", function(template){
			self.$el.html(template);

            getPlugin(appData.plugins.maskedInput, self.applyMask);
        });

        return this;
    }
});

appData.BackboneViews.ResetPassword = new ResetPasswordPage();
appData.pageTransition = 'slideBothRight';