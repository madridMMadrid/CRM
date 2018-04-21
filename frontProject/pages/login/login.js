var LoginPage = Backbone.View.extend({
    el			    : $(appData.router.renderPageTo),
    template	    : 'login',

    events: {
        'click .slider .paginator li'       : 'paginator',
        'click #triggerLogin'               : 'onLogin',
        'click #triggerRegister'            : 'onRegister',
        'submit #loginForm'                 : 'onLoginSubmit',
        'click #loginForm .forgotPassword'  : 'onForgot',
        'keyup #loginForm input'            : 'onInput',

        'submit .resetPass form'            : 'onForgotSubmit'
    },

    onLoginSubmit: function(e){
        e.preventDefault();
        console.log('it works!');

        var form        = $('#loginForm'),
            validator   = new Validator(form);

        if(validator.validate()){
            appData.api.request('login/login', {
                username: form.find('[type="email"]').val(),
                password: form.find('[type="password"]').val()
            }, function(resp, success){
                if(success){
                    appData.user.JWT = resp.jwt;
                    appData.user.data = resp.user;

                    switch(resp.user.type){
                        case 1:
                            resp.user.dashboard = 'dashboard';
                            break;
                        case 2:
                            resp.user.dashboard = 'dashboardHOS';
                            break;
                        case 3:
                            resp.user.dashboard = 'dashboardIncome';
                            break;
                        case 5:
                            resp.user.dashboard = 'dashboard';
                            break;
                    }

                    localStorage.setItem('somethingWierd', resp.jwt);
                    localStorage.setItem("PCRMUserData", JSON.stringify(resp.user));

                    appData.router.pageTransition  = 'slideTop';
                    renderNavbar();
                    location.hash = "";
                }
            })
        } else {
            console.log('form error')
        }
    },

    onInput: function(){
        var form        = $('#loginForm'),
            validator   = new Validator(form);

        if(form.find('input[type="password"]').is(':-webkit-autofill')){
            $('#triggerLogin').removeAttr('disabled');
        } else {
            if(validator.validate()){
                $('#triggerLogin').removeAttr('disabled');
            } else {
                $('#triggerLogin').attr('disabled', true);
            }
        };
    },

    onLogin: function(e){
        e.preventDefault();
        $('#loginForm').submit();
    },

    onForgot: function(e){
        $('.resetPass').removeClass('rightOverScreen');
        $('.loginScreen').addClass('leftOverScreen');
    },

    applyMask: function(){
        $('[type="tel"]').mask("+7 (999) 999-99-99",{autoclear: false});
    },

    render: function () {
        var self = this;

        $.get("/pages/"+this.template+"/" + this.template + ".html", function(template){
			self.$el.html(template);

            setTimeout(function(){
                // give the browser some time to autofill the form
                self.onInput();
            }, 900);
        });

        return this;
    }
});

appData.BackboneViews.Login = new LoginPage();