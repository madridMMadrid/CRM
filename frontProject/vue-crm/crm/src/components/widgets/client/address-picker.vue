<template>
  <div>
    <div class="form-horizontal js-add-time-form"></div>
    <script type="text/template" ref="template">
      <div class="selectNode baseMarginTop">
        <div class="row">
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
        </div>
        <div class="row">
          <div class="col-lg-6">
            <input type="text" name="address" class="form-control" placeholder="Адрес"/>
          </div>
          <div class="col-lg-3">
            <input type="number" name="entrance" class="form-control" placeholder="Подъезд" />
          </div>
          <div class="col-lg-3">
            <input type="number" name="flat" class="form-control" placeholder="Квартира" />
          </div>
          <div class="col-lg-12">
            <input type="text" name="shrimpComment" class="form-control" placeholder="Комментарий курьеру" />
          </div>
        </div>
      </div>
    </script>
  </div>
</template>

<script>
export default {
  name: 'AddressPicker',
  props: {
    addressesProp: {},
    needData: {},
    noWeekdays: {
      default: false
    },
  },

  created: function(){
      
  },

  mounted: function(){
    this.initSuggestions();
  },

  data() {
    return {
      selectedDays: {},
      selectedAddresses: {},
      addressMemory: {},
      parsedData: {},
      suggestionsLoaded: false,
      valid: false
    };
  },

  methods: {
    initSuggestions: function(){
      var _self = this;

      $.getScript('https://cdn.jsdelivr.net/npm/suggestions-jquery@17.10.1/dist/js/jquery.suggestions.min.js', function(){
        _self.suggestionsLoaded = true


        var addresses = groupWeekdays(_self.addressesProp);
        
          if(addresses.length){
            for (var i = 0; i < addresses.length; i++) {
               _self.initMultiselect(addresses[i]);
            }
          } else {
            _self.initMultiselect();
          }

        _self.selectedAddresses = addresses;

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

                _self.selectedDays[arr[i].weekDay] = arr[i].weekDay;
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
      })
    },

    initMultiselect: function(data){
      var _self           = this,
          el              = $(this.$el),
          compiledNode    = el.find('script').html(),
          lastNode        = el.find('.js-add-time-form');

      el.find('.js-add-time-form').append(compiledNode);

      el.find('.selectNode:last input').on('keyup', function(){
        _self.validate();
      })

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
        el.find('.multiselect-full:last').closest('.row').remove();
      }



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
        var multiselects = el.find('.multiselect-full');

        // disable this option in all other selects
        multiselects.find('option').each(function(index, elem){
            $(this).prop("disabled", (!this.selected && typeof _self.selectedDays[this.value] != 'undefined') == true);
        });

        multiselects.multiselect('refresh');
        _self.validate();
      }

      _self.addressSuggestions(el.find('.selectNode:last [name="address"]'));
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
                  _self.selectedAddresses[index] = suggestion.data;
                  console.log('address update', _self.selectedAddresses[index], suggestion)
                }
            }
        });
    },

    setAddresses: function(){
        var _self                   = this,
            modal                   = $(this.$el),
            form                    = modal.find('.js-add-time-form'),
            addresses               = [],
            selectedAdressesData    = this.selectedAddresses,
            addressMemory           = this.addressMemory;

        form.find('.selectNode').each(function(){
            var index       = $(this).index(),
                selectVals  = $(this).find('select').val(),
                entrance    = $(this).find('[name="entrance"]').val(),
                flat        = $(this).find('[name="flat"]').val(),
                comment     = $(this).find('[name="shrimpComment"]').val();

              var data = selectedAdressesData[index];

              if(data){
                if(data.house_type_full && data.house){
                  var building = data.house_type_full + ' ' + data.house;

                  if(data.block_type_full){
                      building += ' ' + data.block_type_full + ' ' + data.block;
                  }
                } else {
                  var building = data.building;
                }

                if(selectVals){
                  for (var i = 0; i < selectVals.length; i++) {
                      var item = selectVals[i];

                      addresses.push({
                          city                : data.region_with_type ? data.region_with_type : data.city,
                          street              : data.street_with_type ? data.street_with_type : data.street,
                          building            : building,
                          weekDay             : item,
                          latitude            : data.geo_lat ? data.geo_lat : data.latitude,
                          longitude           : data.geo_lon ? data.geo_lon : data.longitude,
                          flat                : flat,
                          entrance            : entrance,
                          comment             : comment,
                      })
                  }
                } else if(_self.noWeekdays){
                  addresses.push({
                          city                : data.region_with_type ? data.region_with_type : data.city,
                          street              : data.street_with_type ? data.street_with_type : data.street,
                          building            : building,
                          latitude            : data.geo_lat ? data.geo_lat : data.latitude,
                          longitude           : data.geo_lon ? data.geo_lon : data.longitude,
                          flat                : flat,
                          entrance            : entrance,
                          comment             : comment,
                      })
                }
              }
        });

        this.parsedData = addresses;
        return addresses;
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
      return !disable;
    }
  },

  watch: {
    selectedAddresses: function(){
      this.validate();
    },

    valid: function(newVal){
      this.$emit('input', {
        valid: this.valid,
      });
    }, 

    parsedData: function(){
      console.log('data passed');
      this.$emit('input', {
        valid: this.valid,
        addresses: this.parsedData
      });

      this.$bus.$emit('on-address-picker', {
        valid: this.valid,
        addresses: this.parsedData
      });
    },
  }, 
};
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>

</style>