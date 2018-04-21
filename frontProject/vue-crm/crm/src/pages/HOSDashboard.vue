<template>
<!-- Page header -->
<div>
  <page-header name="Панель управления" class="test">
    <date-range-picker icon="icon-calendar22" name="sales-range" :date="range"></date-range-picker>
    <span class="input-group-addon" style="width: 50px;">
      <a href @click.prevent="showSettings">
        <Icon type="gear-a" size="20" ></Icon>
      </a>
    </span>
    <modal 
      v-model="showModal" 
      title="Настройки" 
      width="700">

      <Tabs size="small">
        <TabPane label="Причина отказа">
          <i-form @submit.prevent="search" ref="searchField">
            <form-item>
              <i-input v-model="searchQuery" placeholder="Введите что нибуть">
                <span slot="prepend">
                  <Icon type="search" size="22"></Icon>
                </span>
              </i-input>
            </form-item>
          </i-form>

          <Table border :columns="columns7" :data="data6"></Table>

        </TabPane>
        <TabPane label="Для вида">Content</TabPane>
      </Tabs>
      <div slot="footer">
        <Button 
            type="success" 
            size="default"
            disabled> 
              Закрыть
        </Button>
      </div>
    </modal>
  </page-header>

  <div class="row">
    <stats-label name="СУММА ЗАДОЛЖЕННОСТИ" :val="numberAddSpaces(debt.current) + ' ₽' " icon="icon-coins">
      <a class="link" v-if="false">Список должников</a><br/>
    </stats-label>
    <stats-label name="ОЖИДАЕТСЯ ОПЛАТ" :val="numberAddSpaces(debt.current)" icon="icon-spinner2">
      <a class="link">&nbsp;</a>
    </stats-label>
    <stats-label name="Активированно сертификатов на сумму" :val="numberAddSpaces(certificates) + ' ₽'" icon="icon-certificate">
    </stats-label>
  </div>
  <div class="row" v-if="loaded">
    <div class="col-lg-6">
      <stats-revenue :items="days" :total="numberAddSpaces(total)" text="Оборот"></stats-revenue>
    </div>
    <div class="col-sm-6">
      <stats-donut :items="turnover.current"></stats-donut>
    </div>
  </div>
  <div class="row" v-if="loaded">
    <div class="col-lg-6">
      <stats-revenue :items="days" :total="numberAddSpaces(total)" text="Заявки" bgColor="bg-purple-400"></stats-revenue>
    </div>
    <div class="col-sm-6">
      <stats-donut :items="turnover.current"></stats-donut>
    </div>
  </div>
</div>
</template>

<script>
export default {
  name: 'HOSDashboard',

  beforeMount: function(){

    var _self = this;

    this.subscribe('sale', function(){
      // console.log(arguments);
      // _self.$Notice.open({
      //   title: 'Новая заявка',
      //   desc: 'Небольшое описание '
      // });
      _self.getServer();
    });

    _self.getServer();
  }, 

  mounted: function(){
    var _self = this;


    this.$bus.$on('on-date-range-picker-change', function(data){
      console.log(arguments)
      _self.range.from = data.value.start;
      _self.range.to = data.value.end;
    })
  },

  data() {
    return {
      searchQuery: '',
      showModal: false,
      range: {
        from: new Date(new Date().getFullYear(), new Date().getMonth(), 1),
        to: new Date()
      },

      current: {},
      previous: {},

      debt: {
        current: 0,
        previous: 0.
      },

      certificates: 0,

      total: 0,
      loaded: false,

      turnover: {
        current: {
          cash: {
            name: 'Наличными:',
            value: 0,
            difference: 0,
            icon: 'icon-coin-dollar',
            color : "#66BB6A"
          }, card: {
            name: 'Картой:',
            value: 0,
            difference: 0,
            icon: 'icon-credit-card',
            color : "#9575CD"
          }, online: {
            name: 'Онлайн:',
            value: 0,
            difference: 0,
            icon: 'fa fa-globe',
            color : "#FF7043"
          }
        },
      },

      requests: {
        current: {
          accepted: 0,
          rejected: 0
        }
      },

      days: [],
      columns7: [
        {
          title: 'Причина отказа',
          key: 'name',
          render: (h, params) => {
              return h('div', [
                h('strong', params.row.name)
              ]);
          }
        },{
          title: 'Удалить',
          key: 'action',
          width: 150,
          align: 'center',
          render: (h, params) => {
              return h('div', [
                  h('Button', {
                      props: {
                          type: 'error',
                          size: 'small',
                          icon: 'close-circled'
                      },
                      on: {
                          click: () => {
                              this.remove(params.index);
                              this.API('clients/removeReason', {
                                  reasonId: params.row.id
                              }, function (resp) {
                                  console.log(resp);

                              });
                          }
                      }
                  })
              ]);
          }
        }],
        data6: []
    };
  },
  created () {
    var _self = this;

    function setValues(event){
      _self.searchQuery = event.value;
    }
  },

  methods: {
    getServer: function(){
      var _self = this;

      var params =  {
        id: _self.$route.params.id,
        dateFrom: dateFormat.formatServer(_self.range.from),
        dateTo: dateFormat.formatServer(_self.range.to),
      }

      this.API('sales/getDashboard', params, function(resp){
        _self.debt.current = resp.current.debt;
        _self.debt.previous = resp.previous.debt;
        _self.days = resp.current.payments.dates;
        _self.certificates = resp.current.certificates;

        _self.requests.current.accepted = resp.current.requests.accepted;
        _self.requests.current.rejected = resp.current.requests.rejected;

        mapPayments('current', 'previous');

        function mapPayments(prop_name, second_name){
          var turnover = resp[prop_name].payments.turnover,
              prevTurnover = resp[prop_name].payments.turnover;

          _self.total = 0;



          for (var i = 0, len = Object.keys(turnover).length; i < len; i++) {
            var name = Object.keys(turnover)[i],
                prevName = Object.keys(prevTurnover)[i],
                value = turnover[name],
                prevValue = prevTurnover[prevName];

            _self.total += parseInt(value);
            _self.turnover[prop_name][name].value = value;
            _self.turnover[prop_name][name].difference = value/100*prevValue;

          }
        }
;
        _self.loaded = true;
      });
    },
    search: function(params){
      
    },
    remove (index) {
      var _self = this;
        _self.data6.splice(index, 1);
    },

    showSettings: function(){

      var _self = this;

      this.showModal = true;

      this.API('clients/showReasons', {
          reasonId: 4
      }, function (resp) {
          console.log(resp)
          _self.data6 = resp;

      });
    }
  }, 

  watch: {
    range: {
      handler: function(){
        this.getServer();
      },
      deep: true
    }
  },
};
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>

</style>
