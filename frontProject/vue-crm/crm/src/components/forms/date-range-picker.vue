<template>
  <div class="input-group">
    <input 
      type="text" 
      class="form-control daterange-locale" 
      :placeholder="placeholder" 
      :name="name" 
      @change="send" 
      ref="daterangelocale"/>

    <span class="input-group-addon">
      <i :class="icon"></i>
    </span>
  </div>
</template>

<script>
export default {
  name: 'DateRangePicker',
  props: {
    placeholder: {},
    name: {},
    icon: {},
    date: {
      from: null,
      to: null
    },
    query:'',
  },
  beforeMount: function(){
    var _self = this;


  }, 

  mounted: function () {
    var _self = this;

    $(this.$refs.daterangelocale).daterangepicker({
        applyClass: 'bg-slate-600',
        cancelClass: 'btn-default',
        opens: "left",
        ranges: {
          'Сегодня': [moment(), moment()],
          'Вчера': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
          'Последние 7 дней': [moment().subtract(6, 'days'), moment()],
          'Последние 30 дней': [moment().subtract(29, 'days'), moment()],
          'Этот месяц': [moment().startOf('month'), moment().endOf('month')],
          'Прошедший месяц': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        },
        locale: {
          applyLabel: 'Вперед',
          cancelLabel: 'Отмена',
          startLabel: 'Начальная дата',
          endLabel: 'Конечная дата',
          customRangeLabel: 'Выбрать дату',
          daysOfWeek: ['Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт','Сб'],
          monthNames: ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'],
          firstDay: 1
        }
    }).on('apply.daterangepicker', function(ev, picker) {
      _self.send(ev, picker);
    });

    var datapicker = $(this.$refs.daterangelocale).data('daterangepicker');
    datapicker.setStartDate(_self.date.from);
    datapicker.setEndDate(_self.date.to);
  },

  data() {
    return {
      
    };
  },

  methods: {
    send: function(v, picker){
      this.$bus.$emit('on-date-range-picker-change', {
        name: v.target.name,
        value: {
          start: new Date(picker.startDate._d),
          end: new Date(picker.endDate._d)
        }
      });
    }, 
    
  }
};
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>

</style>
