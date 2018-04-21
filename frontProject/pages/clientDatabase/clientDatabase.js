var clientDatabase = Backbone.View.extend({
    el: $(appData.router.renderPageTo),
    template 		: 'clientDatabase',
    searchTimeout 	: 1,

    events: {
        'keyup #clientDatabase-searchInput'       : 'onSearchInput',
    },

    pageData: {
        payments: [],
        listTemplate: null
    },

    // initialize: function(){
    //     this.render();
    // },

    render: function () {
        var that = this;
        // console.log(pageData.payments);
        $.get("/pages/"+this.template+"/" + this.template + ".html", function(template){
			that.$el.html($(template).filter('#baseShit').html());
            that.listTemplate = $(template).filter('#otherShit').html();

			that.renderClientlist();
            $(document).trigger("pageRendered");


            var cashScroll = new InfiniteScroll($('.infiniteScroll'), {
                request         : 'clients/displayClients'
            }, function(data){
                console.log(data);
                    var compiled        = _.template(that.listTemplate),
                        baseElem        = $('#clientDatabase-clientList'),
                        baseScroll      = baseElem[0].scrollHeight;

                    baseElem.append(compiled({users: data}));
                    
                    baseElem[0].scrollTop = baseElem[0].scrollHeight - baseScroll;
                
            });

		});

		return this;
    }, 

    onSearchInput: function(evt){

        var that = this;

        clearTimeout(this.searchTimeout);
        this.searchTimeout = setTimeout(function(){
            var input = $(evt.target).val();


            // request server for string
            if(input.length > 2){
                that.renderClientlist({
                    request         : 'clients/displayClients',
                    requestOptions  : {
                        query: input
                    }
                });
            } else if(input.length == 0){
                that.renderClientlist();                
            }
        }, 300);
    },

    renderClientlist: function(options){

        console.log(options);

    	options = $.extend(true, {
    		el          	: '#clientDatabase-clientList',
            request     	: 'clients/displayClients',
            template        : 'clientCard',
            requestOptions 	: {
				limitFrom: 0,
				limitTo: 20
			}
    	}, options);

    	getModule('clientList', function(){
			var clientList = new clientListView({
                options : options
            });
		});

        
        
    }
});


appData.BackboneViews.ClientDatabase = new clientDatabase();