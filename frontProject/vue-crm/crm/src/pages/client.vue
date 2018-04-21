<template>
<!-- Page header -->
<div>
  <div class="test_position" v-if="client.blocked">
    <div class="modal_block_user">
      <div class="middle">
        <div class="inner">
            <div class="wrapp_ul">
            <ul class="media-list chat-list content-group">
              <li class="">
                <div class="media-left">
                  <div class="" style="width:200px;">
                    <div class="thumbnail">
                      <div class="thumb thumb-rounded">
                        <img src="/../static/limitless/assets/images/cover.jpg" alt="">
                      </div>
                    
                        <div class="caption text-center">
                          <h6 class="text-semibold no-margin">{{ client.blocked.employeeName }} <small class="display-block">Sales manager</small></h6>
                        </div>
                      </div>
                  </div>
                </div>

                <div class="media-body">
                  <div class="media-header">
                    <h2>Клиент заблокирован</h2>
                  </div>
                  <div class="media-content">
                    {{ client.blocked.comment }}
                  </div>
                  <span class="media-annotation display-block mt-10">
                    <Button 
                      type="success" 
                      size="default"
                      @click="unblockClient"
                      :on-ok="ok">
                        Разблокировать
                  </Button>
                  </span>
                </div>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="page-header page-header-default">
      <div class="page-header-content">
          <div class="page-title">
              <h4>
                  <i class="icon-arrow-left52 position-left" id="goBackToGoodOldPage"></i>
                  <span class="text-semibold">{{ client.name }}</span>
                  <!-- <span class="text-semibold">{{ client.name }} - {{}}</span> -->
              </h4>
          </div>
          <div class="heading-elements">
        <div class="heading-btn-group">
          <certificate :clientId="client.id"></certificate>
          <a href class="btn btn-link btn-float" @click.prevent="openModalCall">
            <i class="icon-phone-slash text-primary"></i> 
            <span>Завершить</span>
          </a>
        </div>
      </div>
      </div>
  </div>
  <div class="col-lg-4">
      <div class="panel panel-flat">
        <div class="panel-heading">
          <h6 class="panel-title">
            Контакты
            <a class="heading-elements-toggle">
              <i class="icon-more"></i>
            </a>
          </h6>
        </div>

        <div class="list-group no-border no-padding-top">
          <li href="#" class="list-group-item"><i class="icon-phone2"></i> {{ client.phone }}</li>
          <li href="#" class="list-group-item"><i class="icon-mail5"></i> {{ client.email }}</li>
          <li href="#" class="list-group-item"><i class="icon-city"></i> {{ client.city }} - {{ time }}</li>
          <li href="#" class="list-group-item"><i class="icon-gift"></i> {{ client.bonuses }}</li>
        </div>
      </div>

      <div id="client-packageListContainer"></div>

      <!-- проверяем задолжность клиента -->
      <div class="panel panel-flat" v-if="client.debt > 0">
        <div class="panel-heading">
          <div class="media">
          <div class="media-left media-middle">
            <h6 class="panel-title">Задолженность: <a class="heading-elements-toggle"><i class="icon-more"></i></a></h6>
          </div>
          <div class="media-body media-middle">
            {{ client.debt }}
          </div>
          <div class="media-body right text-right">
            <button 
              type="button" 
              class="btn btn-danger" 
              @click="debt.modal = true" 
              :disabled="client.isDebtHold">

              {{ client.isDebtHold ? 'Ожидается оплата' : 'Погасить' }}

            </button>
          </div>
        </div>
        </div>
      </div>
      <!-- end проверяем задолжность клиента -->

      <packages 
        :packages="client.packages" 
        :clientId="client.id"
        :bonuses="client.bonuses">
      </packages>
      <comments 
        url="clients/getClientComments" 
        :passdata="client.comments" 
        :clientid="client.id">
      </comments>

    </div>

    <div class="col-lg-8">
      <purchase-history :clientId="client.id"></purchase-history>
      <div class="panel panel-flat">
        <div class="panel-body text-right">
          <Tabs value="name1" id="packageTabber" ref="calendarTabs" @on-click="calendarTabChange">
            <TabPane label="Календарь" v-for="(pack, index) in client.packages" :name="'name'+index">
              <package-tab :package="pack" :active="calendarActiveTab == 'name'+index"></package-tab>
            </TabPane>
          </Tabs>
        </div>
      </div>
    </div>


    <modal v-model="cancelCall" title="Давайка уточним..." >
      <div class="panel-body no-border text-center">

        <div>
          <h3 class="">Причина отказа</h3>
          <br/>
          <div class="row">
            <Col span="24">
                <Select v-model="whyReason" filterable multiple placeholder="Причина">
                    <Option v-for="item1 in why" :value="item1.value" :key="item1.value">{{ item1.label }}</Option>
                </Select>
            </Col>
          </div> 
          <br>
          <div class="row">
            <Col span="24">
              <Button 
                type="success" 
                long 
                @click="canceledCall"
                @on-ok="ok">

                  Обосновать
              </Button>
            </Col>
          </div>  
        </div>
      </div>
      <div slot="footer"></div>
    </modal>

    <modal v-model="leave.modal" title="Минуточку..." :closable="false" :mask-closable="false">
      <div class="panel-body no-border text-center">
        <div v-if="!leave.showReason">
          <h3 class="">Позвонили этому клиенту?</h3>
          <br/>
          <div class="row">
            <Button type="success" size="large" @click="showReason">Да</Button>
            <Button size="large" @click="leavePage">Нет</Button>
          </div>
        </div>
        <div v-else>
          <h3 class="">И... он отказался, почему?</h3>
          <br/>
          <div class="row">
            <Col span="24">
                <Select v-model="whyReason" filterable multiple placeholder="Причина">
                    <Option v-for="item in why" :value="item.value" :key="item.value">{{ item.label }}</Option>
                </Select>
            </Col>
          </div> 
          <br>
          <div class="row">
            <Col span="24">
              <Button 
                type="success" 
                long 
                @click="refuseReason"
                @on-ok="ok">

                  Далее
              </Button>
            </Col>
          </div>  
        </div>
      </div>
      <div slot="footer"></div>
    </modal>

    <modal v-model="debt.modal">
      <div class="modal-header bg-danger">
        <h6 class="modal-title">Погасить задолженность</h6>
      </div>


      <div class="panel-body no-border largeInput">
        <Row>
          <Col span="12" v-model="value1">
            <div class="ivu-input-group ">
              <div class="ivu-input-group-prepend">
                <Icon type="social-usd" size="22"></Icon>
              </div>
              <InputNumber v-model="desabledButton" :max="Number(debt.amount)" :min="0"  style="width:100%;" size="large"></InputNumber>
            </div>
          </Col>
          <Col span="12">
            <div class="prolong_label_type" v-model="priceId">
              <payment-type 
                v-model="debt.paymentType" 
                :clientid="client.id"
                :bonuses="client.bonuses">
              </payment-type>
              <!-- <payment-bonuses
                
                :bonuses="client.bonuses">
              </payment-bonuses> -->
            </div> 
          </Col>
        </Row>
      </div>

      <div slot="footer">
        <Row type="flex" justify="end" class="code-row-bg">
          <Button type="success" :disabled="!desabledButton" @click="debtSubmit">Выставить счет за задолжность</Button>
        </Row>
      </div>

    </modal>
</div>
</template>

<script>
export default {
  name: 'Client',


   created () {
    var _self = this;

    this.$bus.$on('on-button-spinner', function(event){
      _self[event.name]();
    });

    this.$bus.$on('on-client-update-packages', function(event){
      console.log('ok, update this')
      _self.updatePackages();
    });
  },

  beforeMount: function(){
    var _self = this;

    this.API('clients/getClientById', {
      id: _self.$route.params.id
    }, function(resp){

      var namespace = Object.keys(resp);
      for (var i = 0; i < namespace.length; i++) {
        _self.client[namespace[i]] = resp[namespace[i]];
      }

      _self.debt.amount = resp.debt
    });

  }, 

  mounted: function(){
    
    // this.calendarActiveTab = this.$refs.calendarTabs.activeKey;
  },

  data() {
    return {
      value1: 1,
      priceId: 1,
      cancelCall: false,
      why: [],
      whyReason: [],
      // client: {
      //   blocked   : false,
      //   bonuses   : 0,
      //   city      : null,
      //   comments  : [],
      //   debt      : 0,
      //   email     :"",
      //   gender    :0,
      //   id        :0,
      //   isDebtHold:false,
      //   isNew     :true,
      //   name      :"",
      //   packages  : {},
      //   phone     :"",
      //   timeZone  :0
      // },

      client: {
        blocked   : false,
        bonuses   : 150,
        city      : 'moscou',
        comments  : [],
        debt      : 0,
        email     :"test@test.ru",
        gender    :0,
        id        :1,
        isDebtHold:false,
        isNew     :true,
        name      :"test",
        packages  : {
          name: 'test'
        },
        phone     :"222",
        timeZone  :0
      },

      debt: {
        modal: false,
        amount: 0,
        paymentType: "1"
      },

      rules: {
          debt: [
              { type: 'number', message: 'Введите число', trigger: 'blur' },
              { type: 'number', min:0, message: 'Сумма погашения должна быть больше 0', trigger: 'blur' }
          ]
      },

      stopOnLeave: false,

      leave: {
        modal: false,
        path: '',
        showReason: false
      },

      packageTab: 'name',

      time: 0,

      calendarActiveTab: null,

      desabledButton:0,
    };
  },

  methods: {

    debtSubmit: function (evt) {

      var _self = this;

      this.API('clients/payDebt', {
        'clientId'      : _self.client.id,
        'amount'        : _self.desabledButton,
        'paymentType'   : _self.debt.paymentType,
      }, function(resp){
          // _self.$refs.debtBtn.reset();


          _self.debt.modal = false;
          _self.client.isDebtHold = true;
      });

    },

    // formValid: function(){
    //   var result = false;

    //   if(this.$refs['debtForm']){
        
    //     this.$refs['debtForm'].validate(function(valid){
    //       result = valid;
    //     });

    //   }

    //   return result;
    // }, 

    updatePackages: function(){
      var _self = this;

      this.API('clients/getPackages', {
        'clientId': this.client.id
      }, function (resp) {
        console.log(resp)
        _self.client.packages = resp;
      });
    },

    leavePage: function(){
      if(this.leave.path){
        this.stopOnLeave = false;
        this.$router.push({ path: this.leave.path })
      }
    },

    showReason: function(){
      var _self = this;

      this.API('clients/showReasons', {}, function (resp) {
        var totality = []

        resp.forEach(function(val){
          totality.push({
            label: val.name,
            value: val.id
          })
        });

        _self.why = totality;
      });

      this.leave.showReason = true
    },

    openModalCall(){
      var _self = this;
      _self.cancelCall = true;
      _self.showReason();

    },

    canceledCall(){
      var _self = this;
      _self.cancelCall = false;
      _self.leave.modal = false;
      _self.stopOnLeave = false;
      _self.$Message.info('Все ясненько....');
    },

    refuseReason: function(){
      var _self = this;
      
      _self.leave.modal = false;
      _self.stopOnLeave = false;
      _self.$Message.info('Ваше мнение учтино');
    },

    unblockClient(){
      var _self = this;

      this.API('clients/unblockClient', {
          clientId: _self.client.id
      }, function (resp) {
          console.log(resp);
          _self.client.blocked = false;
          _self.$Message.info('Клиент разблокирован');
      });
    },

    ok(){

    },

    calendarTabChange: function(curName){
      this.calendarActiveTab = curName;
    },
    // send: function(){
    //   this.$bus.$emit('package-prolongation', {
    //     amount        : this.sum,
    //     paymentType   : this.paymentType
    //   });
    // }
  }, 

  watch: {
    priceId: function(newVal){
      this.sum = this.prolong.options.filter(elem => elem.id == newVal)[0].priceToPay;
    },
    sum: function(){
      this.send()
    }, 
    paymentType: function(){
      this.send()
    },
    'client.timeZone': function(){
      var _self = this;

      setInterval(function(){
        var date = new Date(),
        offsetInMinutes = _self.client.timeZone,
        utcTime = date.getUTCHours(),
        thisHours = utcTime + offsetInMinutes;

        var utcTime = utcTime < 10 ? '0'+utcTime : utcTime,
            minutes = date.getMinutes() < 10 ? '0'+date.getMinutes() : date.getMinutes();

        _self.time = thisHours + ':' + minutes;
      }, 500);
    },
    amount: function(){
      var _self = this;
      _self.desabledButton;
    },

    desabledButton(newVal){
      console.log(newVal);
    }
  },

  beforeRouteEnter (to, from, next) {
    next(vm => {
      if(from.name == 'Dashboard'){
        vm.stopOnLeave = true;
      }
    })
  },

  beforeRouteLeave: function(to, from, next){
    var _self = this;

    if (_self.client.blocked){
      next();
    } else {
      if(this.stopOnLeave){
        this.leave.modal = true;
        this.leave.path = to.path;
        console.log('waaait, dude, you forgot somethin\'', to);
      } else {
        next();
      }
    }
    
  }
};
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style>
  #packageTabber .ivu-tabs-bar
  {
    display: none;
  }
  .test_position {
    width: calc(100% - 260px);
    height: 100%;
    /* Permalink - use to edit and share this gradient: http://colorzilla.com/gradient-editor/#ffffff+0,ffffff+100&0+0,0.6+1,0.7+100 */
    background: -moz-radial-gradient(center, ellipse cover, rgba(255,255,255,0) 0%, rgba(255,255,255,0.75) 1%, rgba(255,255,255,0.95) 100%); /* FF3.6-15 */
    background: -webkit-radial-gradient(center, ellipse cover, rgba(255,255,255,0) 0%,rgba(255,255,255,0.75) 1%,rgba(255,255,255,0.95) 100%); /* Chrome10-25,Safari5.1-6 */
    background: radial-gradient(ellipse at center, rgba(255,255,255,0) 0%,rgba(255,255,255,0.75) 1%,rgba(255,255,255,0.95) 100%); /* W3C, IE10+, FF16+, Chrome26+, Opera12+, Safari7+ */
    filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#00ffffff', endColorstr='#b3ffffff',GradientType=1 ); /* IE6-9 fallback on horizontal gradient */
    position: fixed;
    z-index: 9999999;

    animation: opacity .5s linear;
  }
  .modal_block_user {
    position: absolute;
    width: 100%;
    height: 100%;
    display: table;
    text-align: center;
  }
  .who_blocked {
    display: flex;
    justify-content: center;
    align-items: center;
  }
  .who_blocked > div {
    padding: 10px;
  }
  .who_blocked img {
    width: 80px;
    height: 80px;
    border-radius: 100%;
    border: 2px solid #737373;
  }
  .footer_button {
    padding: 10px 0;
  }
  .largeInput .prolong_label_type > div {
    display: flex;
    padding: 0px 0 0 5px;
  }
  .largeInput .prolong_label_type label {
    width: 100%;
    display: flex;
    justify-content: center;
  }
  .modal_block_user .wrapp_ul {
    display: flex;
    justify-content: center;
    align-items: center;
  }
  .wrapp_ul .media-list.chat-list.content-group {
    width: 500px;
  }
  .thumb img:not(.media-preview) {
    height: 115px;
  }

  @keyframes opacity
  {
    from {opacity: 0}
    to {opacity: 1}
  }
</style>
