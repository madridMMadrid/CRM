<template>
  <div class="dashboard">
    <page-header name="Мои задачи">
      <Button type="primary" @click="addClientModal = true">Принять звонок от клиента</Button>
    </page-header>

    <div class="content">
      <div class="col-lg-6">
        <h2 class="panel-heading no-margin-top no-padding-left">
          Новые клиенты
          <span class="badge bg-primary-400 heading-text">{{ requests.length }}</span>
        </h2>
        <div>
          <client-card v-for="client in requests" :client="client"></client-card>
        </div>
      </div>

      <div class="col-lg-6">
        <h2 class="panel-heading no-margin-top no-padding-left">
          Действующие клиенты
          <span class="bg-success-400 heading-text badge">{{ attention.length }}</span>
        </h2>
        <div>
          <client-card v-for="client in attention" :client="client"></client-card>
        </div>
      </div>
    </div>
    <modal v-model="addClientModal" title="Добавить клиента">
      <div class="modal-body">
        <i-form :model="newClient" :rules="ruleInline" @submit.prevent="addClient" ref="addClient">
  
          <form-item prop="name">
            <i-input v-model="newClient.name" placeholder="Имя">
              <span slot="prepend">
                <Icon type="person" size="22"></Icon>
              </span>
            </i-input>
          </form-item>

          <form-item prop="phone">
              <div class="ivu-input-group ">
                <div class="ivu-input-group-prepend">
                  <Icon type="ios-telephone" size="22"></Icon>
                </div>
                <masked-input v-model="newClient.phone" mask="\+\7 (111) 111-1111" placeholder-char="_" placeholder="Телефон" type="tel"  class="ivu-input"/>
              </div>
          </form-item>

          <form-item prop="email">
            <i-input v-model="newClient.email" placeholder="E-mail">
              <span slot="prepend">
                <Icon type="email" size="22"></Icon>
              </span>
            </i-input>
          </form-item>

            <Select v-model="newClient.city.value">
              <Option v-for="(city, index) in cities" :value="city.value" :key="city.value" :selected="index == 0">{{ city.name }}</Option>
            </Select>
        </i-form>
      </div>
      <div slot="footer">
            <Row type="flex" justify="end" class="code-row-bg">
              <Button type="success" :disabled="!formValid()" @click="addClient">Добавить</Button>
            </Row>
          </div>
    </modal>
  </div>
</template>

<script>
export default {
  name: 'Dashboard',

  created () {

  },

  beforeMount: function(){
    var _self = this;

    this.API('clients/getCities', {}, function(resp){
      var arr = [];
      resp.forEach(function(el){
        arr.push({
          name: el.name,
          value: el.id
        })
      });

      _self.cities = arr
      _self.newClient.city = arr[0]
    });

    _self.updateRequests();

    this.API('clients/requireAttention', {}, function(resp){
      _self.attention = resp;
    });

    this.subscribe('newRequests', function(){
      _self.$Notice.open({
        title: 'Новая заявка',
        desc: 'Небольшое описание '
      });
      _self.updateRequests();
    });
  }, 

  data() {
    return {
      cities: [],

      newClient: {
        name: null,
        phone: null,
        email: null,
        city: {},
      },

      ruleInline: {
          email: [
              { type: 'email', message: 'Неверный e-mail', trigger: 'blur' }
          ]
      },

      attention: [],
      requests: [],

      addClientModal: false
    };
  },

  methods: {
    addClient: function(){
      var _self = this;

      this.API('clients/store', {
        name: this.newClient.name,
        phone: this.newClient.phone,
        email: this.newClient.email,
        city: this.newClient.city.value,
      }, function (resp) {
        _self.$router.push({ path: `/client/${resp}` })
      });
    },

    formValid: function(){
      var keys = Object.keys(this.newClient);

      for(var i=0, len=keys.length; i<len; i++){
        if(this.newClient[keys[i]] == null){
          // console.log('fail on', this.newClient[keys[i]], keys[i])
          return false
        };
      }

      var result = false;

      this.$refs['addClient'].validate(function(valid){
        result = valid;
      });

      return result;
    }, 

    updateRequests: function(){
      var _self = this;
      this.API('clients/getRequests', {}, function(resp){
        _self.requests = resp;
      });
    }
  }
};
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style>

</style>
