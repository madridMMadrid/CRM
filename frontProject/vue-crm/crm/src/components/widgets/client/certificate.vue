<template>
    <a href class="btn btn-link btn-float has-text" @click.prevent="show = true">
      <i class="icon-certificate text-primary"></i>
      <span>Сертификат</span>

      <modal v-model="show" title="Сертификат" width="700">
        <Row>
          <Col span="8">
            <i-form>
              <form-item>
                <div class="ivu-input-group ">
                  <div class="ivu-input-group-prepend">
                    <Icon type="ios-pricetag" size="22"></Icon>
                  </div>
                  <input type="number" v-model="code" placeholder="Код сертификата" class="ivu-input"/>
                </div>
              </form-item>
            </i-form>
          </Col>
          <Col span="14" offset="2">
            <Card>
              <p slot="title">Сертификат</p>
              <div v-show="parseInt(certificate.certificateId)">
                <p>Номер: {{ certificate.certificateNumber }}</p>
                <p>Скидка: {{ certificate.certificateDiscount }}</p>
                <p>Действителен до: {{ certificate.certificateExpiration }}</p>
              </div>
              <div v-show="certificate.code == 0">
                <p>Этот клиент уже активировал этот сертификат!</p>
              </div>
              <div v-show="certificate.code == 1">
                <p>Сертификата с таким номером в природе не водится</p>
              </div>

            </Card>
          </Col>
        </Row>
        <!-- <i-form :rules="rules" ref="debtForm">
          <form-item prop="debt">
            <i-input v-model="debt.amount" placeholder="Сумма погашения" size="large">
              <span slot="prepend">
                <Icon type="social-usd" size="22"></Icon>
              </span>
              <span slot="append">
                <payment-type v-model="debt.paymentType"></payment-type>
              </span>
            </i-input>
          </form-item>
        </i-form> -->
      <div slot="footer">
        <Row type="flex" justify="end" class="code-row-bg">
          <Button type="success" :disabled="!parseInt(certificate.certificateId)" @click="activate">Активировать</Button>
        </Row>
      </div>
    </modal>
    </a>
</template>

<script>
export default {
  name: 'Certificate',
  props: ['clientId'],

  created () {

  },

  beforeMount: function(){
    
  }, 

  mounted: function(){

  },

  data() {
    return {
      show: false,
      code: '',
      timeout: 0,

      certificate: {
        certificateComment: '',
        certificateDiscount: '',
        certificateExpiration: '',
        certificateId: '',
        certificateNumber: '',
      }
    };
  },

  methods: {
    formValid: function(){
      return true;
    },

    activate: function(){
      var _self = this;

      this.API('clients/activateCertificate', {
        'certificateNumber' : this.certificate.certificateNumber,
        'clientId'          : this.clientId
      }, function (resp) {
        _self.show = false;
        if(resp){
          _self.$Message.success('Сертификат активирован');
          _self.show = false;
        }
      });
    }
  }, 

  watch: {
    code: function(){
      var _self = this;

      clearTimeout(this.timeout);
      this.timeout = setTimeout(function(){
        if(_self.code){
          _self.API('clients/getCertificate', {
            certificateNumber: _self.code,
            clientId: _self.clientId,
          }, function(resp){
            console.log(resp)
            if(resp){
              resp.certificateExpiration = dateFormat.formatClientString(resp.certificateExpiration);
              _self.certificate = resp;
            }
          });
        }
      }, 700);
    }
  },
};
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style>

</style>
