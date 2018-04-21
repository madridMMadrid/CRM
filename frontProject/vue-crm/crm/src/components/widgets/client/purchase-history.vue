<template>
  <div class="panel panel-flat">
    <Collapse v-model="show">
      <Panel 
          name="1">

        История покупок
        <p slot="content" class="content">
          <i-form ref="addClient">
            <form-item>
              <i-input v-model="search.query" placeholder="Пакет">
                <span slot="prepend">
                  <Icon type="search" size="22"></Icon>
                </span>
              </i-input>
            </form-item>
          </i-form>
            <Col class="demo-spin-col" span="24" v-if="!history.length && displayPreloader">
                <Spin fix >
                    <Icon type="load-c" size=18 class="demo-spin-icon-load"></Icon>
                    <div>Грузимся...</div>
                </Spin>
            </Col>
          <scroll :on-reach-edge="loadMore" loading-text="Загружаем..." :height="search.height" v-else>
            <div class="col-lg-12 no-padding"> 
              <Table :columns="columns" :data="history" no-data-text="отсутствует"></Table>
            </div>
          </scroll>    
        </p>
      </Panel>
    </Collapse>
  </div>
</template>

<script>
export default {
  name: 'PurchaseHistory',
  props: {
    clientId: {}
  },

  mounted: function(){
    this.search.height = $(window).height() - $('.content').position().top - 30 
  },

  beforeMount: function(){
    this.searchIt();
  }, 

  data() {
    return {
      show  : null,
      search: {
        query: '',
        timer: null,
        from: 0,
        to: 20,
        height: 500
      },

      columns: [{
        title: 'Действие',
        key: 'actionType'
      },{
        title: 'Пакет',
        key: 'packageId'
      },{
        title: 'Сумма',
        key: 'amount'
      },{
        title: 'Оплата',
        key: 'paymentType'
      },{
        title: 'Дата',
        key: 'updatedAt'
      }],

      history: [],
      displayPreloader: true,
    };
  },

  methods: {
   searchIt: function(params){
      var _self = this;

        var params = {
          clientId: this.clientId,
          limitFrom: this.search.from,
          limitTo: this.search.to,
          query: this.search.query
        };

        if(_self.search.query){
          params.query = _self.search.query;
        }

        this.API('clients/getHistory', params, function(resp){
          resp.forEach(function(el){
                el.actionType   = _self.numberAction(el.actionType);
                el.packageId    = _self.numberPackage(el.packageId);
                el.paymentType  = _self.numberCard(el.paymentType);
                el.updatedAt    = _self.dateFormat(el.updatedAt);
              })
            if(params.limitFrom == 0 && params.query != ''){
              _self.history = resp;
              _self.emptySearch();
            } else {
              _self.history = _self.history.concat(resp);
              _self.emptySearch();
          }
        });
    },

    emptySearch: function() {

      var _self = this;

      if(!_self.history.length){
        _self.displayPreloader = false;
      } else {
        _self.displayPreloader = true; 
      }
    },
   
    numberAction: function(name){
      var result = '';
      switch(name){
        case 0:
            result = 'Смена пакета';
            break;
        case 1:
            result = 'Продление пакета';
            break;
        case 2:
            result = 'Добавление пакета';
            break;
        case 3:
            result = 'Отмена';
            break;
      }
      return result;
    },
    numberPackage: function(name){
      var result = '';
      switch(name){
        case 0:
            result = 'Спортивное питание Silver — 1500 ккал';
            break;
        case 1:
            result = 'Спортивное питание Silver — 2000 ккал';
            break;
        case 2:
            result = 'Спортивное питание Gold — 1500 ккал';
            break;
      }
      return result;
    },
    numberCard: function(name){
      var result = '';
      switch(name){
        case 1:
            result = 'Наличными';
            break;
        case 2:
            result = 'Картой';
            break;
        case 3:
            result = 'Онлайн';
            break;
      }
      return result;
    },
    loadMore: function(){
      this.search.from = this.search.from + 20
      this.search.to = this.search.to + 20
      this.searchIt();

    },
    dateFormat: function (data) {
      return dateFormat.formatClientString(data);
    }
  },

  watch: { 
    show: function(){
      if(this.history.length < 1){
        var _self = this;

        this.API('clients/getHistory', {
          clientId: this.clientId
        }, function(resp){
          resp.forEach(function(el){
            el.actionType   = _self.numberAction(el.actionType);
            el.packageId    = _self.numberPackage(el.packageId);
            el.paymentType  = _self.numberCard(el.paymentType);
            el.updatedAt    = _self.dateFormat(el.updatedAt);
          })
          _self.history = resp;
        });
      }
    },
    'search.query': function () {
      var _self = this;

      clearTimeout(_self.search.timer);
        _self.search.timer = setTimeout(function(someval){
        _self.search.from = 0;
        _self.search.to = 20;
        _self.query = someval;
        _self.searchIt();
        _self.emptySearch();
      }, 500);

    }

  }
};
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>
    .demo-spin-icon-load{
        animation: ani-demo-spin 1s linear infinite;
    }

    @keyframes ani-demo-spin {
        from { transform: rotate(0deg);}
        50%  { transform: rotate(180deg);}
        to   { transform: rotate(360deg);}
    }
    .demo-spin-col{
        height: 100px;
        position: relative;
        border: 1px solid #eee;
    }
</style>
