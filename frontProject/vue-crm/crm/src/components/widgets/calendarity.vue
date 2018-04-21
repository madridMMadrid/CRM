<template>
  <!-- Page header -->
  <div>
    <div class="calendarity" ref="main">
      <div class="calendarity-head">
        <div class="panel-heading text-left no-padding-left">
          <div class="btn btn-default disabled heading-btn">
            <i class="icon-checkmark-circle2 text-teal"></i>
            <span class="js-calendaity_deliveryDaysLeft heading-btn" style="display:inline-block;margin-left:12px;">{{ cellsAvailable }}</span>
          </div>
          <div class="heading-elements" style="right:0;">
            <button class="btn btn-primary js-calendarity-buildPrev pull-left heading-btn" @click="prev">
              <i class="glyphicon glyphicon-chevron-left"></i>
            </button>
            <div class="btn btn-default pull-left heading-btn">
              {{ monthNames[thisDate.date.getMonth()] }} {{ thisDate.date.getFullYear() }}
            </div>
            <button class="btn btn-primary js-calendarity-buildNext pull-left heading-btn" @click="next">
              <i class="glyphicon glyphicon-chevron-right"></i>
            </button>
          </div>
        </div>
      </div>
      <div class="calendarity-table">
        <div class="calendarity-head">
          <div>Пн</div>
          <div>Вт</div>
          <div>Ср</div>
          <div>Чт</div>
          <div>Пт</div>
          <div>Сб</div>
          <div>Вс</div>
        </div>
        <div class="calendarity-body" ref="cells">
          <div v-for="(day, key) in thisDate.days" @mousedown="rangeMouseDown" @mouseup="rangeMouseUp" @mousemove="rangeMouseMove" :class="{'selected': day.selected, 'past': day.past}">
              
            <span v-if="!day.unavailable">
              {{ day.date.getDate() }}
              <span v-if="day.state > -1">
                  <Icon color="green" type="checkmark-circled" v-if="day.state == 0" 
                    :title="day.addressCity+', '+day.addressStreet + ' '+day.addressBuilding + ' кв. '+ day.addressFlat 
                    + ' в ' + day.timeStart + '-' + day.timeEnd"></Icon>
               <Icon color="blue" type="ios-snowy" v-if="day.state == 1"></Icon>
              </span>
            </span>
            <span v-else class="unavailable"></span>
          </div>
        </div>
      </div>

      <div class="js-calendarTooltip" v-show="controllers.showTooltip" ref="tooltip">
        <!-- <button type="button" class="btn bg-success" @click="setDeliveries">
          <i class="icon-checkmark-circle2"></i>
        </button> -->
        <button type="button" class="btn btn-primary" @click="setFreeze" v-if="buttons.freeze" title="Заморозка">
          <i class="icon-snowflake"></i>
        </button>
        <button type="button" class="btn btn-warning" @click="changeAddressTime" v-if="buttons.edit" title="Изменить адрес и время доставки">
          <Icon type="edit"></Icon>
        </button>
        <button type="button" class="btn btn-warning" @click="setFire" v-if="buttons.burn" title="День сгорел">
          <i class="icon-fire"></i>
        </button>
      </div>
    </div>
    <modal v-model="changeAddressTimeModal" title="Изменить дату и время доставки" width="700">
      <div class="panel-body no-border largeInput">
        <Tabs value="address">
            <TabPane label="Адрес" name="address">
              <address-picker v-once :noWeekdays="true" v-model="changeAddress" ref="addressComponent"></address-picker>
            </TabPane>
            <TabPane label="Время" name="time">
              <Row>
                <times-picker v-once :noWeekdays="true" v-model="changeTime" ref="timesComponent"></times-picker>
              </Row>
            </TabPane>
        </Tabs>
        <!-- <times-picker v-once :noWeekdays="true" ref="timesComponent"></times-picker> -->
      </div>
      <div slot="footer">
        <Row type="flex" justify="end" class="code-row-bg">
          <Button type="success" @click="processAddressChange">Изменить</Button>
        </Row>
      </div>
    </modal>
  </div>
</template>

<script>
export default {
  name: 'Calendarity',

  props: ['package', 'active'],

  created () {
    this.buildGrid();
  },

  beforeMount: function(){
    
  }, 

  mounted: function(){

  },

  data() {
    return {
      cellsAvailable: 0,
      thisDate: {
        date: new Date(),
        days: [{

        }]
      },

      controllers: {
        dragStart: 0,
        dragEnd: 0,
        isDragging: false,
        showTooltip: false,
      },

      changeAddress: null,
      changeTime: null,

      changeAddressTimeModal: false,

      buttons: {
        burn: true,
        edit: false,
        freeze: false
      },

      serverDeliveries: this.package.deliveries,

      monthNames: ["Январь", "Февраль", "Март","Апрель", "Май", "Июнь", "Июль", "Август", "Сентябрь", "Октябрь", "Ноябрь", "Декабрь"],
    }
  },

  methods: {
    buildGrid: function(){
      var serverDeliveries = this.serverDeliveries,
          today       = new Date(new Date().getFullYear(), new Date().getMonth(), new Date().getDate()+1),
          date        = this.thisDate.date,
          firstDay    = new Date(date.getFullYear(), date.getMonth(), 1),
          lastDay     = new Date(date.getFullYear(), date.getMonth() + 1, 0),
          days        = [];

      var len = firstDay.getDay() == 0 ? 6 : firstDay.getDay()-1
      for(var i=0; i<len; i++){
        days.push({
          unavailable: true
        });
      }

      // add empty tds on missing dates
      for(var i=firstDay.getDate(), len=lastDay.getDate()+1; i<len; i++){
        var thisDate = new Date(date.setDate(i)),
            thisDay = {
              date: thisDate,
              selected: false
            };

        if(thisDate < today){
          thisDay.past = true
        }

        days.push(thisDay);
      }

      if(lastDay.getDay() != 0){
        for(var i=0, len=7-lastDay.getDay(); i<len; i++){
          days.push({
            unavailable: true
          });
        }
      }

      days.forEach(function(day){
        serverDeliveries.forEach(function(servitor){
          var dayDate = new Date(day.date),
              servitorDate = new Date(servitor.deliveryDate);

          if(dayDate.getFullYear() == servitorDate.getFullYear() &&
            dayDate.getMonth() == servitorDate.getMonth() &&
            dayDate.getDate() == servitorDate.getDate()){
              day.state = servitor.state;
              day.employeeId = servitor.employeeId;
              day.id = servitor.id;
              day.addressId = servitor.addressId;
              day.timeId = servitor.timeId;

              day.timeId = servitor.timeId;

              day.addressBuilding   = servitor.addressBuilding;
              day.addressCity       = servitor.addressCity;
              day.addressComment    = servitor.addressComment;
              day.addressEntrance   = servitor.addressEntrance;
              day.addressFlat       = servitor.addressFlat;
              day.addressId         = servitor.addressId;
              day.addressStreet     = servitor.addressStreet;

              day.timeStart         = servitor.timeStart ? servitor.timeStart.slice(0, -2) : null;
              day.timeEnd           = servitor.timeEnd ? servitor.timeEnd.slice(0, -2) : null;
          }
        })
      })

      this.thisDate.days = days;
    },

    next: function(){
      this.thisDate.date = new Date(this.thisDate.date.getFullYear(), this.thisDate.date.getMonth()+1, 1);
    }, 

    prev: function(){
      this.thisDate.date = new Date(this.thisDate.date.getFullYear(), this.thisDate.date.getMonth()-1, 1);
    }, 

    isRightClick: function(e) {
        if (e.which) {
            return (e.which == 3);
        } else if (e.button) {
            return (e.button == 2);
        }
        return false;
    },

    selectRange: function() {

      if(!this.active){
        return false;
      }

      var _self = this,
          result = false;

      var from = _self.controllers.dragStart+1,
          to = _self.controllers.dragEnd + 1;

      if (_self.controllers.dragEnd + 1 < _self.controllers.dragStart) {
        from = _self.controllers.dragEnd + 1;
        to = _self.controllers.dragStart+1;
      }

      if(from != 0){
        for(var i=0, len = _self.thisDate.days.length; i<len; i++){
          var day = _self.thisDate.days[i];
          if(day.date && day.date.getDate() >= from && day.date.getDate() <= to && !day.past){
            _self.thisDate.days[i].selected = true;
            result = true;
          } else {
            _self.thisDate.days[i].selected = false;
          }
        }
      }

      return result;
    },

    getFilteredCells: function(){
      return $(this.$refs.cells).children().filter(function(){
        return $(this).children('.unavailable').length == 0
      });
    },

    rangeMouseDown: function(e) {
      if(!this.active){
        return false;
      }

      var _self = this;

      if (this.isRightClick(e)) {
          return false;
      } else {
        var filtered = _self.getFilteredCells();

        if(filtered.length){
            _self.controllers.dragStart = filtered.index($(e.currentTarget));
            _self.controllers.isDragging = true;

            if (typeof e.preventDefault != 'undefined') { e.preventDefault(); } 
            document.documentElement.onselectstart = function () { return false; };
        }
      } 
    }, 
    rangeMouseUp: function(e) {
      if(!this.active){
        return false;
      }

      var _self = this;

        if (this.isRightClick(e)) {
            return false;
        } else {
          var filtered = _self.getFilteredCells();

          if(filtered.length){
              _self.controllers.dragEnd = filtered.index($(e.currentTarget));

              _self.controllers.isDragging = false;
              if (_self.controllers.dragEnd != -1) {
                  var show = _self.selectRange();

                  if(show){
                    _self.showTooltip($(e.currentTarget));
                  }
              }

              document.documentElement.onselectstart = function () { return true; }; 
          }
        }
    },

    rangeMouseMove: function(e) {
      if(!this.active){
        return false;
      }

      var _self = this;

        if (_self.controllers.isDragging) {
          var filtered = _self.getFilteredCells();
          if(filtered.length){
              _self.controllers.dragEnd = filtered.index(e.currentTarget);
              _self.selectRange();
          }
        }            
    },

    showTooltip: function(cell){
      if(cell.length){
        var top = cell.position().top - cell.height()/1.2,
            left = cell.position().left - $(this.$refs.tooltip).width()/10;

        $(this.$refs.tooltip).css({
          top: top,
          left: left
        });

        var allGreens = true;
        var selected = this.thisDate.days.filter(function(el){
          return el.selected == true;
        });
        selected.forEach(function(item){
          if(!item.addressId){
            allGreens = false;
          }
        });

        this.buttons.edit = this.buttons.freeze = allGreens;

        this.buttons.burn = 
        !Math.abs(this.controllers.dragEnd - this.controllers.dragStart) > 0 && 
          this.thisDate.days[this.controllers.dragEnd-1] && this.thisDate.days[this.controllers.dragEnd-1].past;

        if(this.buttons.burn || this.buttons.edit || this.buttons.freeze){
          this.controllers.showTooltip = true;
        }

        $(document).bind('click', this.outerClick);
      }
    },

    outerClick: function(evt){
      var target = $(evt.target),
          _self = this;

      if(!target.hasClass('calendarity') && target.closest('.calendarity').length < 1 && !target.hasClass('js-calendarTooltip') && target.closest('.js-calendarTooltip').length < 1 ){
        _self.controllers.showTooltip = false;
        $(this).unbind(evt);
      }
    },

    setDeliveries: function(){

    }, 

    changeAddressTime: function(){
      // var selected = this.thisDate.days.filter(function(el){
      //   return el.selected = true;
      // });

      this.changeAddressTimeModal = true;
      console.log(this.selectedRange());
    },

    setFreeze: function(){
      var diapasone = this.selectedRange();

      diapasone.forEach(function(item){
        item.state = 1;
      });

      this.passServer(1, diapasone);
    }, 

    passServer(state, diapasone){
      var _self = this;

      this.API('clients/setDeliveries', {
        clientPackageId: this.package.id,
        state: state,
        from: dateFormat.formatServer(diapasone[0].date),
        to: dateFormat.formatServer(diapasone[diapasone.length-1].date)
      }, function(resp){
        if(state == 1){
          _self.$Message.success('Дни заморожены');
        }
      })
    },

    selectedRange(){
      var dragStart = this.controllers.dragStart,
        dragEnd = this.controllers.dragEnd,
        days = this.thisDate.days.filter(function(el){
          return !el.unavailable;
        });

      if(dragEnd > dragStart){
        var diapasone = days.slice(dragStart, dragEnd+1);
      } else {
        var diapasone = days.slice(dragEnd, dragStart+1);
      }

      return diapasone;
    },

    setFire: function(){

    },

    processAddressChange: function(){
      var _self = this,
        address = this.$refs.addressComponent.setAddresses()[0],
        time = this.$refs.timesComponent.setTimes()[0],
        selected = this.selectedRange();

      var ids = [];

      selected.forEach(function(it){
        ids.push(it.id);
      });

      if(this.$refs.addressComponent.validate()){
        address.clientPackageId = this.package.id;
        this.API('clients/addAddressExclusion', {address: address, deliveryIds: ids}, function(resp){
          _self.updateDeliveries();
          _self.$Message.success('Добавлено исключение адреса доставки');
          _self.changeAddressTimeModal = false;
        })
      }

      if(this.$refs.timesComponent.validate()){
        time.clientPackageId = this.package.id;
        this.API('clients/addTimeExclusion', {time: time, deliveryIds: ids}, function(resp){
          _self.updateDeliveries();
          _self.$Message.success('Добавлено исключение времени доставки');
          _self.changeAddressTimeModal = false;
        })
      }
    },

    updateDeliveries(){
      var _self = this,
          newVal = this.thisDate.date,
          firstDay    = new Date(newVal.getFullYear(), newVal.getMonth(), 1),
          lastDay     = new Date(newVal.getFullYear(), newVal.getMonth() + 1, 0);

      this.API('clients/getDeliveries', {
        clientPackageId: _self.package.id,
        dateFrom: firstDay,
        dateTo: lastDay,
      }, function(resp){
        _self.serverDeliveries = resp;
        _self.buildGrid();
      });
    },
  }, 

  watch: {
    'thisDate.date': function(newVal){
      this.updateDeliveries()
    },

    'package.deliveries': function(newVal){
      this.serverDeliveries = newVal;
    }
  },
};
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>
  .calendarity-table
  {
    font-size: 0;
  }

  .calendarity-table .calendarity-head > div,
  .calendarity-table .calendarity-body > div 
  {
    width: 13.9%;
    border: 1px solid #ddd;
    display: inline-block;
    box-sizing: border-box;
    font-size: 12px;
  }

  .calendarity-table .calendarity-head > div
  {
    font-weight: bold;
  }

  .calendarity-table .calendarity-body > div  
  {
    border-top: 0;
    border-right: 0;
    height: 80px;
    vertical-align: middle;
    position: relative;
  }

  .calendarity-table .calendarity-body span 
  {
    font-size: 14px;
    line-height: 5.5em;
  }

  .calendarity-table .calendarity-body > div:last-of-type
  {
    border-right: 1px solid #ddd;
  }

  .calendarity-table .unavailable
  {
    background: #dddddd4a;
    width: 100%;
    height: 100%;
    display: block;
    opacity: .3;
  }

  .selected
  {
    background: #f5f5f5;
  }

  .past
  {
    border-color: #ddd;
    background: #f1f0f0;
    opacity: .3;
  }

  .calendarity-table .calendarity-body span  .ivu-icon
  {
    color: green;
    position: absolute;
    bottom: 7px;
    right: 7px;
    font-size: 20px;
  }

  .js-calendarTooltip:after 
  {
    left: 9px;
    bottom: -17px;
  }
</style>
