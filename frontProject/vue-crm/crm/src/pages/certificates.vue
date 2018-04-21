<template>
<!-- Page header -->
<div>
  <page-header name="Сертификаты">
    <a href class="btn btn-link btn-float has-text" @click.prevent="show = true">
      <i class="icon-certificate text-primary"></i>
      <span>Добавить</span>

      <modal 
        v-model="show" 
        title="Добавить сертификат" 
        width="700">

        <Form :model="form">
          <FormItem>
            <Row>
              <Col span="11">
                <div class="ivu-input-group ">
                  <div class="ivu-input-group-prepend">
                    <Icon type="ios-pricetag" size="22"></Icon>
                  </div>
                  <input type="number" v-model="addCerteficationInput.amountInput"  @keyup="key" placeholder="Сумма" class="ivu-input"/>
                </div> 
              </Col>
              <Col span="11" offset="2">
                  <DatePicker 
                    type="date" 
                    placeholder="Действителен до" 
                    icon="icon-calendar22" 
                    style="width: 100%;"
                    v-model="addCerteficationInput.dataInput"
                    :options="options">    
                  </DatePicker>
                </Col>
              </Row>
            </FormItem>
            <FormItem>
              <i-input placeholder="Комментарий" v-model="addCerteficationInput.commentInput">
                <span slot="prepend">
                  <Icon type="chatbox-working" size="22"></Icon>
                </span>
              </i-input>
            </FormItem>
        </Form>
        <div slot="footer">
            <!-- <Button type="success" :disabled="!parseInt(certificate.certificateId)" @click="activate">Активировать</Button> -->
          <Button 
              type="success" 
              size="default"
              @click="addCertification"
              @on-ok="ok"
              :disabled="!addCerteficationInput.amountInput"> 
                Активировать
          </Button>
        </div>
      </modal>
    </a>

  </page-header>
  <div class="content">
    <i-form @submit.prevent="search" ref="searchField">
      <form-item>
        <i-input v-model="scroll.query" placeholder="Номер сертификата, сумма или комментарий">
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
                <th>Для клиента</th>
                <th>Номер сертификата</th>
                <th>Сумма бонусных баллов</th>
                <th>Действителен до</th> 
                <th>Комментарий</th>
              </tr>
            </thead>
            <tbody>
              <certificate-node v-for="cert in certs" :item="cert"></certificate-node>
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
  name: 'Certificates',


   data() {
    return {

      certs: [],
      searchQuery: null,
      show: false,

      form: {},

      addCerteficationInput: {
        amountInput: '',
        commentInput: '',
        dataInput: [],
      },

      scroll: {
        from: 0,
        to: 20,
        timer: null,
        query: '',
      },

      options: {
        disabledDate (date) {
            return date && date.valueOf() < Date.now() - 86400000;
        }
      }

    };
  },

  created () {
    var _self = this;
  },

  beforeMount: function(){
    var _self = this;

    this.search(true);
  }, 

  mounted: function(){

  },

  methods: {

    search: function(noParams){
      var _self = this;

      var params = noParams ? {} : {
        limitFrom: _self.scroll.from,
        limitTo: _self.scroll.to,
        query: _self.scroll.query,
      };

      this.API('clients/getAllCertificates', params, function(resp){
        resp.forEach(function(item){
          item.expiration = dateFormat.formatClientString(item.expiration);
        });

        if(params.limitFrom == 0 && params.query != ''){
          _self.certs = resp;
        } else {
          _self.certs = _self.certs.concat(resp);
        }
      });
    }, 

    addCertification(){

      var _self = this;

      this.API('clients/addCertificate', {
          expiration  : dateFormat.formatServer(_self.addCerteficationInput.dataInput),
          comment     : _self.addCerteficationInput.commentInput,
          discount    : _self.addCerteficationInput.amountInput,
      }, function (resp) {
          _self.show = false;
          _self.$Message.success('Сертификат добавлен');
      });
    },
    key(evt){

      var value = parseInt(evt.target.value);

      if (isNaN(value) || value < 0){
        evt.target.value = '';
      }
    },
    ok() {
      
    }, 
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
