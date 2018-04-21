<template>
<!-- Page header -->
<div>
  <page-header name="База клиентов"></page-header>
  <!-- /page header -->
  <div class="content">
  <i-form @submit.prevent="search" ref="addClient">
    <form-item>
      <i-input v-model="scroll.query" placeholder="Имя клиента, номер телефона или email">
        <span slot="prepend">
          <Icon type="search" size="22"></Icon>
        </span>
      </i-input>
    </form-item>
  </i-form>
    <scroll :on-reach-edge="loadMore" loading-text="Загружаем..." :height="scroll.height">
      <div class="col-lg-12 no-padding">
        <client-card v-for="client in clients" :client="client"></client-card>
      </div>
    </scroll>  
  </div>
</div>
</template>

<script>
export default {
  name: 'ClientDatabase',

  created () {},

  beforeMount: function(){
    this.search();
  }, 

  mounted: function(){
    this.scroll.height = $(window).height() - $('.content').position().top - 30
  },

  data() {
    return {
      clients: [],
      block: null,

      scroll: {
        from: 0,
        to: 20,
        query: '',
        timer: null,
        height: 500
      }
    };
  },

  methods: {
    search: function(){
      var _self = this;

      var params = {
        limitFrom: this.scroll.from,
        limitTo: this.scroll.to,
        query: this.scroll.query
      };

      console.log(params);

      this.API('clients/displayClients', params, function(resp){
        if(params.limitFrom == 0 && params.query != ''){
          _self.clients = resp;
        } else {
          _self.clients = _self.clients.concat(resp);
        }
      });
    }, 
    loadMore: function(){
      this.scroll.from = this.scroll.from + 20
      this.scroll.to = this.scroll.to + 20
      this.search();
    }
  }, 

  watch: {
    'scroll.query': function(someval){
      var _self = this;

      clearTimeout(_self.scroll.timer);
      _self.scroll.timer = setTimeout(function(){
        _self.scroll.from = 0;
        _self.scroll.to = 20;
        _self.query = someval;
        _self.search();
      }, 500);
    }
  },
};
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style>

</style>
