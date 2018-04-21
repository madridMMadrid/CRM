var client_addressListComponent = Backbone.View.extend({
    events: {
        'click .js-addTimePopup': 'setAddresses',
        'keyup input'           : 'validate'
    },

    data: {},

    initialize: function(options) {
        this.package        = options.package;

        this.parent         = options.parent;
        
        this.template               = $(options.template).filter('#client-addressListBase').html();
        this.templateSelectNode     = $(options.template).filter('#client-addAddressNode-template').html();

        this.storage = {
            selectedDays: {},
            selectedAddresses: {},
        };

        this.btn = options.btn;

        this.setElement(options.element);
    },

    update: function(){
        var _self = this;
        appData.api.request('clients/getAddresses', {
            'clientPackageId': _self.package.id
        }, function (resp) {
            _self.package.addresses = resp;
            _self.render();
        });
    },

    render: function(){
        var _self = this;
            compiled = _.template(this.template);
        
        this.$el.html(compiled(this.package));

        $.getScript('https://cdn.jsdelivr.net/npm/suggestions-jquery@17.10.1/dist/js/jquery.suggestions.min.js', function(){

            var addresses = groupWeekdays(_self.package.addresses);

            if(addresses.length){
                for (var i = 0; i < addresses.length; i++) {
                    _self.initMultiselect(addresses[i]);
                }
            } else {
                _self.initMultiselect();
            }

            _self.validate();

            function groupWeekdays(arr){
                var result = [],
                    arr = arr ? arr : [];

                for (var i = 0; i < arr.length; i++) {
                    if(result.length < 1){
                        pushItUp(0, arr[i].building,
                                    arr[i].city,
                                    arr[i].comment,
                                    arr[i].entrance,
                                    arr[i].flat,
                                    arr[i].latitude,
                                    arr[i].longitude,
                                    arr[i].street,
                                    arr[i].weekDay);
                    } else {
                        var last = result[result.length - 1];

                        // magic goes here
                        /*
                            building
                            city
                            comment
                            entrance
                            flat
                            id
                            latitude
                            longitude
                            street
                         */

                        if(last.building == arr[i].building 
                            && last.city == arr[i].city 
                            && last.comment == arr[i].comment
                            && last.entrance == arr[i].entrance
                            && last.flat == arr[i].flat
                            && last.latitude == arr[i].latitude
                            && last.longitude == arr[i].longitude
                            && last.street == arr[i].street
                        ){
                            last.weekDay.push(arr[i].weekDay)
                        } else {
                            pushItUp(result.length, arr[i].building,
                                    arr[i].city,
                                    arr[i].comment,
                                    arr[i].entrance,
                                    arr[i].flat,
                                    arr[i].latitude,
                                    arr[i].longitude,
                                    arr[i].street,
                                    arr[i].weekDay);
                        }

                    }

                    _self.storage.selectedDays[arr[i].weekDay] = arr[i].weekDay;
                }

                function pushItUp(index, building, city, comment, entrance, flat, latitude, longitude, street, weekDay){
                    result[index] = {
                        building    : building,
                        city        : city,
                        entrance    : entrance,
                        flat        : flat,
                        latitude    : latitude,
                        longitude   : longitude,
                        street      : street,
                        comment     : comment,
                        weekDay     : [weekDay]
                    };
                }

                return result;
            }
        });
    },

    additionalSelect: function(){
        var _self           = this,
            modal           = this.$el,
            form            = modal.find('.js-add-time-form'),
            selects         = form.find('.selectNode select'),
            last            = form.find('.selectNode:last select')
            daysLength      = Object.keys(_self.storage.selectedDays),
            initOnce        = true;

        selects.each(function(){
            if(daysLength.length < 7 && $(this).val() && initOnce && last.val() && !loopSelects()){
                // в селекте выбраны не все значения
                _self.initMultiselect();
                initOnce = false;
            } else if(!$(this).val() && daysLength.length == 7) {
                // выбраны все значения
                $(this).closest('.selectNode').remove();
            }
        });

        function loopSelects(){
            return form.find('select').filter(function(){
                return !this.value;
            }).length > 0;
        }
    },

    initMultiselect: function(data){
        var _self           = this,
            compiledNode    = _.template(this.templateSelectNode);
            lastNode        = this.$el.find('.js-add-time-form');

        this.$el.find('.js-add-time-form').append(compiledNode({}));

        this.$('.multiselect-full:last').multiselect({
            buttonText: function(options, select) {
                if (options.length === 0) {
                    return 'Выберите дни недели';
                }
                 else {
                     var labels = [];
                     options.each(function() {
                         if ($(this).attr('label') !== undefined) {
                             labels.push($(this).attr('label'));
                         }
                         else {
                             labels.push($(this).html());
                         }
                     });
                     return labels.join(', ') + '';
                 }
            },

            onChange: function(element, checked) {
                console.log('onchange')
                if(checked){
                    _self.storage.selectedDays[element.val()] = element.val();
                } else {
                    delete _self.storage.selectedDays[element.val()];
                }

                _self.additionalSelect();
                runThroughSelects();
            },

            // onSelectAll: function (all) {
            //     // stack overflow ahead!
            //     // if(all){
            //     //     for (var i = 1; i < 8; i++) {
            //     //         _self.storage.selectedDays[i] = i;
            //     //     }
            //     //     setTimeout(function(){
            //     //         runThroughSelects();
            //     //     }, 150)
            //     // }
            //     // console.log('all?', arguments, all, _self.storage.selectedDays)
            // },
            // onDeselectAll: function () {
            //     // console.log('deselect all')
            //     // for (var i = 0; i < 7; i++) {
            //     //     delete _self.storage.selectedDays[i];
            //     //     console.log('deleted', _self.storage.selectedDays[i])
            //     // }
            // },

            // selectAllText: 'Все',
            // includeSelectAllOption: true,
        });

        if(data){
            var node = lastNode.find('.selectNode:last');

            node.find('[name="address"]').val(data.city + ' ' + data.street + ' ' + data.building),
            node.find('[name="entrance"]').val(data.entrance),
            node.find('[name="flat"]').val(data.flat);
            node.find('[name="shrimpComment"]').val(data.comment);

            var select = node.find('select');

            select.val(data.weekDay);
            select.multiselect('refresh');
            runThroughSelects();
        }

        function runThroughSelects(){
            var multiselects = _self.$el.find('.multiselect-full');

            // disable this option in all other selects
            multiselects.find('option').each(function(index, elem){
                $(this).prop("disabled", (!this.selected && typeof _self.storage.selectedDays[this.value] != 'undefined') == true);
            });

            multiselects.multiselect('refresh');
            _self.validate();
        }

        this.addressSuggestions(this.$('.selectNode:last [name="address"]'));
    },

    addressSuggestions: function(elem){
        /* 
            Jquery suggestions plugin
            documentation over here: https://confluence.hflabs.ru/pages/viewpage.action?pageId=207454318
        */

        var _self = this,
            index = $(elem).closest('.selectNode').index();

        $(elem).suggestions({
            token: "470a9200602e61b47511cf398bb8d2c5ecad3c2b",
            type: "ADDRESS",
            count: 5,
            autoSelectFirst: true,
            deferRequestBy: 750,
            minChars: 5,

            /* Вызывается, когда пользователь выбирает одну из подсказок */
            onSelect: function(suggestion) {
                if(suggestion && suggestion.data){
                    _self.storage.selectedAddresses[index] = suggestion.data;
                }
            }
        });
    },

    validate: function(){
        // validate data over here
        var modal   = this.$el,
            form    = modal.find('.js-add-time-form'),
            btn     = this.btn ? this.btn : modal.find('.js-addTimePopup');

        var disable = false;

        form.find('select,input').each(function(){
            if(!this.value){
                disable = true;
            }
        });

        btn.attr('disabled', disable);
        
        if(this.btn && disable){
            btn.addClass("disabled noevents").attr("aria-disabled", 'true');
        } else if(this.btn){
            btn.removeClass("disabled noevents").attr("aria-disabled", 'false');
        }
    },

    setAddresses: function(){
        var _self                   = this,
            modal                   = this.$el,
            form                    = modal.find('.js-add-time-form'),
            addresses               = [],
            selectedAdressesData    = this.storage.selectedAddresses;

        form.find('.selectNode').each(function(){
            var index       = $(this).index(),
                selectVals  = $(this).find('select').val(),
                entrance    = $(this).find('[name="entrance"]').val(),
                flat        = $(this).find('[name="flat"]').val(),
                comment     = $(this).find('[name="shrimpComment"]').val(),
                data        = selectedAdressesData[index] ? selectedAdressesData[index] : {
                    street: $(this).find('[name="address"]').val()
                };

            var building = data.house_type_full + ' ' + data.house;

            if(data.block_type_full){
                building += ' ' + data.block_type_full + ' ' + data.block;
            }

            for (var i = 0; i < selectVals.length; i++) {
                var item = selectVals[i];

                addresses.push({
                    clientPackageId     : _self.package.id,
                    city                : data.region_with_type,
                    street              : data.street_with_type,
                    building            : building,
                    weekDay             : item,
                    latitude            : data.geo_lat,
                    longitude           : data.geo_lon,
                    flat                : flat,
                    entrance            : entrance,
                    comment             : comment,
                })
            }
        });

        appData.api.request('clients/addAddress', {
            addresses: addresses
        }, function (resp) {
            (new PNotify({
                 title: 'Адрес доставки установлен.',
                 addclass: 'bg-success'
            }));
            
            _self.update();
            _self.$(".modal.in").modal("hide");
        });
    }
});