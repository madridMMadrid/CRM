<template>
  <div class="panel panel-flat clearfix">
    <div class="panel-heading">
      <h6 class="panel-title">
        Пакеты
        <a class="heading-elements-toggle">
          <i class="icon-more"></i>
        </a>
      </h6>
      <div class="heading-elements">
        <ul class="icons-list">
          <li>
            <Tooltip content="Добавить пакет" placement="top">
              <a @click="addPackage.modal = true">
                <i class="icon-plus-circle2"></i>
              </a>
            </Tooltip>
          </li>
        </ul>
      </div>
    </div>

    <!-- выводим пакеты клиента -->
    <div class="clearfix">
      <table class="table" id="clientPackagesList">
        <tbody>
            <package 
              v-for="(pack, index) in packages" 
              :package="pack, index" 
              :clientid="clientId"
              :bonuses="bonuses">
            </package>  
        </tbody>
      </table>
    </div>

    <modal v-model="addPackage.modal" title="Добавить пакет" width="800" :footerHide="true">
      <div class="customAccordion">
        <i-form :model="addPackage" ref="addClient">
          <vue-good-wizard 
            :steps="addPackage.steps" 
            :onNext="nextClicked" 
            :onBack="backClicked"
            :nextAvailiable="addPackage.showNextBtn"
            finalStepLabel="Добавить"
            nextLabel="Далее"
            backLabel="Назад" class="nomargin">
            <div slot="page1">
              <package-list 
                v-model="addPackage.data"
                :bonuses="bonuses"
                :clientId="clientId">
              </package-list>
            </div>
            <div slot="page2">
              <DatePicker type="date" placeholder="Дата первой доставки" :options="datePickerOptions" v-model="addPackage.deliverFrom"></DatePicker>
              <address-picker v-model="addPackage.addresses" ref="addressComponent"></address-picker>
            </div>
            <div slot="page3">
              <times-picker v-model="addPackage.times"></times-picker>
            </div>
          </vue-good-wizard>
        </i-form>
      </div>
      <div slot="footer"></div>
    </modal>
  </div>
</template>

<script>
export default {
  name: 'Packages',
  props: {
    packages: {},
    clientId: {},
    bonuses: {},
  },

  created: function(){

  },

  mounted: function(){
    this.showNextBtn = false
  },

  data() {
    return {
      addPackage: {
        modal: false,

        steps: [{
          label: 'Выбор пакета',
          slot: 'page1',
        },{
          label: 'Адрес доставки',
          slot: 'page2',
        },{
          label: 'Время доставки',
          slot: 'page3',
        }],

        deliverFrom: true,

        data: {},

        addresses: {
          valid: true,
          addresses: {}
        },

        times: {
          valid: true,
          times: {}
        },

        showNextBtn: false
      },

      datePickerOptions:{
        disabledDate (date) {
          return date && date.valueOf() < Date.now() - 86400000;
        }
      }
    };
  },

  methods: {
    packageEventName: function(name){
      var result = '';
      switch(name){
        case 0:
            result = 'Ожидается смена пакета';
            break;
        case 1:
            result = 'Ожидается продление пакета';
            break;
        case 2:
            result = 'Ожидается добавление пакета';
            break;
        case 3:
            result = 'Ожидается отмена'; 
            break;
        case 4:
            result = 'Ожидается удаление';
            break;
        case 5:
            result = 'Ожидается погашение долга';
            break;

      }
      return result;
    },

    formValid: function(){
      return true;
    },

    nextClicked(currentPage) {
      console.log('next clicked', currentPage)
      if(currentPage == 0){
        this.addPackage.deliverFrom = "";
        this.addPackage.addresses.valid = false;
      } else if(currentPage == 1){
        // collect address data
        this.addPackage.addresses.addresses = this.$refs.addressComponent.setAddresses();
        this.addPackage.times.valid = false;
      } else if(currentPage == 2){
        this.addPackageServer();
      }

      return true; //return false if you want to prevent moving to next page
    },

    backClicked(currentPage) {
      console.log('back clicked', currentPage);
      this.updateNextBtn();
      return true; //return false if you want to prevent moving to previous page
    },

    updateNextBtn: function(){
      this.addPackage.showNextBtn = parseInt(this.addPackage.data.packageId) > -1 && 
        (this.addPackage.addresses.valid === true && this.addPackage.deliverFrom) && this.addPackage.times.valid === true;
    },

    addPackageServer: function(){
      var _self = this,
          send = {
            order: {
              clientId: this.clientId,
              packageId: this.addPackage.data.packageId,
              priceId: this.addPackage.data.priceId,
              amount: this.addPackage.data.sum,
              paymentType: this.addPackage.data.paymentType,
              dateFrom: dateFormat.formatServer(this.addPackage.deliverFrom),
            }, 
            times: this.addPackage.times.times,
            addresses: this.addPackage.addresses.addresses
          };

      this.API('clients/addWrapper', send, function(resp){
        _self.$bus.$emit('on-client-update-packages');
        _self.addPackage.modal = false;
        _self.$Message.success('Пакет добавлен');
      });
    }
  },

  watch: {
    'addPackage.data.packageId': function(newVal){
      this.updateNextBtn();
    }, 
    'addPackage.addresses.valid': function(newVal){
      this.updateNextBtn();
    },
    'addPackage.times.valid': function(newVal){
      this.updateNextBtn();
    },
    'addPackage.deliverFrom':function(newVal){
      this.updateNextBtn();
    },
  }, 
};
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>

</style>