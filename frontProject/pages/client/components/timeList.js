var client_timeListComponent = Backbone.View.extend({
    template    : '',

    events: {
        'click .js-addTimePopup'               : 'addTime',
        'keyup  [name="timeFrom"]'             : 'onTimeChange',
        'change [name="shortTimeInterval"]'    : 'onTimeChange',
        'keyup input'                          : 'validate',
    },

    data: {
        clientId: null
    },

    initialize: function(options) {
        this.template                   = $(options.template).filter('#client-addTimeForm-template').html();
        this.templateSelectNode         = $(options.template).filter('#client-addTimeNode-template').html();
        this.templateAdd                = options.templateAdd;
        this.parent                     = options.parent;
        this.package                    = options.package;
        this.data.clientId              = options.clientId;
        this.btn                        = options.btn;

        this.storage = {
            selectedDays: {}
        };

        this.setElement(options.element);
    },

    update: function(){
        var _self = this;

        appData.api.request('clients/getTimes', {
            'clientPackageId': _self.package.id
        }, function (resp) {
            _self.package.deliveryTime = resp;
            _self.render();
        });
    },

    render: function(){

        var _self = this,
            compiled = _.template(this.template);

        this.$el.html(compiled(this.package));

        var deliveryTimes = groupWeekdays(this.package.deliveryTime);

        if(deliveryTimes.length){
            for (var i = 0; i < deliveryTimes.length; i++) {
                this.initMultiselect(deliveryTimes[i]);
            }
        } else {
            this.initMultiselect();
        }

        this.validate();

        function groupWeekdays(arr){
            var result = [],
                arr = arr ? arr : [];

            for (var i = 0; i < arr.length; i++) {
                if(result.length < 1){
                    pushItUp(0, arr[i].start, arr[i].finish, arr[i].interval, arr[i].weekDay)
                } else {
                    var last = result[result.length - 1];
                    if(last.start == arr[i].start && last.finish == arr[i].finish && last.interval == arr[i].interval){
                        last.weekDay.push(arr[i].weekDay)
                    } else {
                        pushItUp(result.length, arr[i].start, arr[i].finish, arr[i].interval, arr[i].weekDay)
                    }
                }

                _self.storage.selectedDays[arr[i].weekDay] = arr[i].weekDay;
            }

            function pushItUp(index, start, finish, interval, weekDay){
                result[index] = {
                    start: start,
                    finish: finish,
                    interval: interval,
                    weekDay: [weekDay]
                };
            }

            return result;
        }
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
            compiledNode    = _.template(this.templateSelectNode),
            lastNode        = this.$el.find('.js-add-time-form');

        lastNode.append(compiledNode());

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
                if(checked){
                    _self.storage.selectedDays[element.val()] = element.val();
                } else {
                    delete _self.storage.selectedDays[element.val()];
                }

                _self.additionalSelect();

                runThroughSelects();
            }
        });

        if(data){
            var node = lastNode.find('.selectNode:last');
            node.find('[name="timeFrom"]').val(data.start),
            node.find('[name="timeTo"]').val(data.finish),
            node.find('[name="shortTimeInterval"]')[0].checked = data.interval == true;

            var select = node.find('select');

            select.val(data.weekDay);
            select.multiselect('refresh');
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
    },

    validate: function(){
        var modal   = this.$el,
            form    = modal.find('.js-add-time-form'),
            btn     = this.btn ? this.btn : modal.find('.js-addTimePopup');

        var disable = false;
        form.find('select,input').each(function(){
            if(!this.value){
                disable = true;
            }
        })

        btn.attr('disabled', disable);

        console.log(btn);

        if(this.btn && disable){
            btn.addClass("disabled noevents").attr("aria-disabled", 'true');
        } else if(this.btn){
            btn.removeClass("disabled noevents").attr("aria-disabled", 'false');
        }
    },

    addTime: function(){
        var _self = this;

        var form    = this.$('.js-add-time-form'),
            times   = [];

        form.find('.selectNode').each(function(){
            var timeFrom    = $(this).find('[name="timeFrom"]').val(),
                timeTo      = $(this).find('[name="timeTo"]').val(),
                interval    = $(this).find('[name="shortTimeInterval"]')[0].checked ? 1 : 0,
                selectVals  = $(this).find('select').val();

            for (var i = 0; i < selectVals.length; i++) {
                var item = selectVals[i];

                times.push({
                    clientPackageId: _self.package.id,
                    start: timeFrom,
                    finish: timeTo,
                    interval: parseInt(interval),
                    weekDay: item,
                })
            }
        });

        appData.api.request('clients/setTime', {times: times}, function (resp) {
            (new PNotify({
                 title: 'Время доставки установлено.',
                 addclass: 'bg-success'
            }));
            
            _self.update();
            _self.$(".modal.in").modal("hide");
        });
    },

    onTimeChange: function(evt){
        var form    =  $(evt.currentTarget).closest('.selectNode'),
            time    = form.find('[name="timeFrom"]')[0].value.split(':')
            hours   = parseInt(time[0]);

        hours = hours + (form.find('[name="shortTimeInterval"]')[0].checked ? 1 : 2);

        if(hours > 23){
            hours = (24 - hours) *-1;
        }

        if(hours < 10){
            hours = "0"+hours;
        }

       form.find('[name="timeTo"]').val(hours+ ':' + time[1]);
       this.validate();
    }
});