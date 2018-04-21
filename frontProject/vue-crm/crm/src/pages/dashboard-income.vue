<template>
<!-- Page header -->
<div>
  <page-header name="Прием финансов"></page-header>
  <div class="content">
    <i-form @submit.prevent="search" ref="addClient">
      <form-item>
        <i-input v-model="searchQuery" placeholder="Имя клиента, номер телефона, название пакета или email">
          <span slot="prepend">
            <Icon type="search" size="22"></Icon>
          </span>
        </i-input>
      </form-item>
    </i-form>
      <div class="table-responsive">
        <div class="infiniteScroll">
          <table class="table table-hover">
            <thead>
              <tr height="70px">
                <th>Имя</th>
                <th>Сумма</th>
                <th>Принято от суммы</th> 
                <th>Пакет</th>
                <th>Дата</th>
                <th>Тип оплаты</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <income-node v-for="payment in payments" :item="payment"></income-node>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    </div>  
  </div>
</template>

<script>
export default {
  name: 'DashboardIncome',

   data() {
    return {
      payments: [],
      searchQuery: ''
    };
  },

  created () {
    var _self = this;

    this.$bus.$on('on-input', setValues);
    this.$bus.$on('on-income-delete', this.removeIncomeNode);

    function setValues(event){
      _self.searchQuery = event.value;
    }
  },

  beforeMount: function(){
    var _self = this;

    //   var params =  {
    //     id: _self.$route.params.id,
    //     dateFrom: dateFormat.formatServer(_self.range.from),
    //     dateTo: dateFormat.formatServer(_self.range.to),
    //   }

    this.API('clients/getPayments', {}, function(resp){
      for(var i=0, len=resp.length; i<len; i++){
        resp[i].createdAt = dateFormat.formatClientString(resp[i].createdAt);
      }

      _self.payments = resp;
    });
  }, 

  mounted: function(){

  },

  methods: {
    search: function(params){

    },

    removeIncomeNode: function(paymentId){
      var victim = this.payments.findIndex(function(payment){
        return payment.paymentId == paymentId;
      });
      this.payments.splice(victim, 1);
    }
  }, 

  watch: {

  },
};
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style>

</style>
