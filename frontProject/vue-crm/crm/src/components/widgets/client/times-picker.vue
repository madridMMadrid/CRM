<template>
  <div>
    <div class="form-horizontal js-add-time-form"></div>
    <script type="text/template" ref="template">
      <div class="selectNode row">
        <div class="form-group col-lg-6">
          <div class="multi-select-full multi-select-inline">
            <select class="multiselect-full" multiple="multiple" required>
              <option value="1">Пн</option>
              <option value="2">Вт</option>
              <option value="3">Ср</option>
              <option value="4">Чт</option>
              <option value="5">Пт</option>
              <option value="6">Сб</option>
              <option value="7">Вс</option>
            </select>
          </div>
        </div>
        <div class="col-lg-6">
          <div class="form-group col-lg-3">
            <input class="form-control" type="time" name="timeFrom" required/>
          </div>
          <div class="form-group col-lg-1 text-center">
            -
          </div>
          <div class="form-group col-lg-3">
            <input class="form-control" type="time" name="timeTo" required disabled=""/>
          </div>
          <div class="form-group col-lg-6 text-center">
            <label>
              <input class="" type="checkbox" name="shortTimeInterval">
              Экспресс доставка
            </label>
          </div>
        </div>
      </div>
    </script>
  </div>
</template>

<script>
export default {
  name: 'TimesPicker',
  props: {
    timesProp: {},
    noWeekdays: {
      default: false
    },
  },

  created: function(){
  },

  mounted: function(){
    var _self = this,
        deliveryTimes = groupWeekdays(this.timesProp);

    if(deliveryTimes.length){
        for (var i = 0; i < deliveryTimes.length; i++) {
            this.initMultiselect(deliveryTimes[i]);
        }
    } else {
        this.initMultiselect();
    }

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

            _self.selectedDays[arr[i].weekDay] = arr[i].weekDay;
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

  data() {
    return {
      selectedDays: {},
      parsedData: {},
      valid: false
    };
  },

  methods: {
    initMultiselect: function(data){
      var _self           = this,
          el              = $(this.$el),
          compiledNode    = el.find('script').html(),
          lastNode        = el.find('.js-add-time-form');

      el.find('.js-add-time-form').append(compiledNode);

      el.find('.selectNode:last input').on('keyup, change', this.onTimeChange);

      if(!this.noWeekdays){
        el.find('.multiselect-full:last').multiselect({
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
                    _self.selectedDays[element.val()] = element.val();
                } else {
                    delete _self.selectedDays[element.val()];
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
      } else {
        el.find('.multiselect-full:last').closest('.form-group').remove();
        el.find('.selectNode > div').removeClass('col-lg-6').addClass('col-lg-12');
      }

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
        var multiselects = el.find('.multiselect-full');

        // disable this option in all other selects
        multiselects.find('option').each(function(index, elem){
            $(this).prop("disabled", (!this.selected && typeof _self.selectedDays[this.value] != 'undefined') == true);
        });

        multiselects.multiselect('refresh');
        _self.validate();
      }
    },

    additionalSelect: function(){
        var _self           = this,
            el              = $(this.$el),
            form            = el.find('.js-add-time-form'),
            selects         = form.find('.selectNode select'),
            last            = form.find('.selectNode:last select'),
            daysLength      = Object.keys(this.selectedDays),
            initOnce        = true;

        selects.each(function(){
            if(daysLength.length < 7 && $(this).val() && initOnce && last.val() && !loopSelects()){
                // в селекте выбраны не все значения
                _self.initMultiselect();
                initOnce = false;
            } else if(!$(this).val().length && daysLength.length == 7) {
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

    validate: function(){
      var modal   = $(this.$el),
          form    = modal.find('.js-add-time-form');

      var disable = false;

      if(Object.keys(this.selectedDays).length < 7 && !this.noWeekdays){
        disable = true;
      }

      form.find('input:not([name="shrimpComment"])').each(function(){
          if(!this.value){
              disable = true;
          }
      });

      this.valid = !disable;

      if(this.valid){
        this.setTimes();
      }

      return !disable;
    },

    onTimeChange: function(evt){
        var form    =  $(evt.currentTarget).closest('.selectNode'),
            time    = form.find('[name="timeFrom"]')[0].value.split(':'),
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
    },

    setTimes: function(){
      var _self = this;

        var form    = $(this.$el).find('.js-add-time-form'),
            times   = [];

        form.find('.selectNode').each(function(){
            var timeFrom    = $(this).find('[name="timeFrom"]').val(),
                timeTo      = $(this).find('[name="timeTo"]').val(),
                interval    = $(this).find('[name="shortTimeInterval"]')[0].checked ? 1 : 0,
                selectVals  = $(this).find('select').val();

            if(selectVals){
              for (var i = 0; i < selectVals.length; i++) {
                  var item = selectVals[i];

                  times.push({
                      start: timeFrom,
                      finish: timeTo,
                      interval: parseInt(interval),
                      weekDay: item,
                  })
              }
            } else if(_self.noWeekdays){
              times.push({
                      start: timeFrom,
                      finish: timeTo,
                      interval: parseInt(interval),
                  });
            }
        });

      this.parsedData = times;
      return times;
    },
  },

  watch: {
    parsedData: function(){
      this.$emit('input', {
        valid: this.valid,
        times: this.parsedData
      });
    }
  }, 
};
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>

</style>