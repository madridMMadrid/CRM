var clientListView = Backbone.View.extend({
    initialize: function(data){
        _self = this;

        // let's close funciton closure, otherwise it will rewrite _self
        (function(_self){ 
            _self.options = data.options
            if(!_self.options.resp){
                appData.api.request(_self.options.request, _self.options.requestOptions, function(response, success){
                    // data loaded, load template for parsing the data
                    resp = mapit(response);
                    _self.render(resp);
                });
            } else {
                resp = mapit(_self.options.resp);
                _self.render(_self.options.resp);
            }

            function mapit(data){
                return _self.options.mapping ? $.map(data, _self.options.mapping) : data;
             }
         })(_self);
    },

    render: function (data) {
        var _self = this;

        getTemplate(_self.options.template, function(template){
            var compiled = _.template(template);

            var innerNodes = '';
            for(var i=0, len=data.length; i<len; i++){
                innerNodes += compiled(data[i]);
            }

            $(_self.options.el).html(innerNodes);
            if(_self.options.indicator){
                _self.options.indicator.text(len);
            }

            if(typeof _self.options.callback === 'function'){
                _self.options.callback();
            }
        });

        return this;
    }, 
});