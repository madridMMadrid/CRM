<template>
<!-- Page header -->
<div>
  <i-form @submit.prevent="search" ref="addClient">
    <form-item>
      <i-input v-model="searchQuery" placeholder="Пакет">
        <span slot="prepend">
          <Icon type="search" size="22"></Icon>
        </span>
      </i-input>
    </form-item>
  </i-form>

  <Collapse v-model="opened" accordion>
    <Panel v-for="(pack, index) in packages" :name="index.toString()" v-show="pack.name.search(searchQuery) != -1" v-if="pack.id != ignoreId">
      <package-label :packageName="pack.name"></package-label>
      <div slot="content" v-show="!busy">
        <span class="small-right-padding">Оплатить на</span>
        <br v-if="isShort" class="br" />
        <RadioGroup type="button" size="large" v-model="priceId" class="small-right-padding wrapper_label">
          <Radio v-for="option in thisPackage.options" :label="option.id">
            <Tooltip :content="option.price + ' руб.'">
              {{option.packageLength}}
            </Tooltip>
          </Radio>
        </RadioGroup>
        <br v-if="isShort" class="br" />

        <span class="small-right-padding">дней</span>

        <br v-if="isShort" class="br" />
        <div class="wrapper_label">
          <payment-type 
            v-model="paymentType" 
            :bonuses="bonuses">
          </payment-type>
        </div>
        <payment-bonuses
          :bonuses="bonuses">
        </payment-bonuses>
        <p class="sum">Итого: {{ sum }}</p>
        <div v-if="actualAmount">
          <p>Бонусами: {{ actualAmount}}</p>
          <p>Сумма к оплате: <code>{{ sum - actualAmount }}</code></p>
        </div>
      </div>
    </Panel>
  </Collapse>
</div>
</template>

<script>
export default {
  name: 'PackageList',

  props: {
    'isShort': { 
      default: false,
    },
    'ignoreId': {},
    bonuses: {},
    clientId: {},
  },

  beforeMount: function(){
    var _self = this;

    this.API('packages/packagesList', {}, function(resp){
      _self.packages = resp;
    });
  }, 

  mounted: function(){

  },

  data() {
    return {
      packages: [],
      opened: '0',
      searchQuery: '',

      thisPackage:{},
      busy: false,

      priceId: -1,
      paymentType: 1,
      sum: 0,
      value_list: 0,
      actualAmount: 0,
    };
  },

  methods: {
    search: function(params){

    },

    passValues: function(){
      // var _self = this;

      this.$emit('input', {
        sum         : this.sum,
        priceId     : this.priceId,
        paymentType : this.paymentType,
        packageId   : parseInt(this.opened[0])+1
      })
    },
    send: function(){
      var _self = this;

      if (_self.sum > _self.bonuses && _self.actualAmount != 0) {
        _self.actualAmount = Math.round(_self.bonuses / 2);
        this.$bus.$emit('sending_to_list', {
          amount: Math.round(_self.sum),
        });

      } else {
        
        this.$bus.$emit('sending_to_list', {
          amount: Math.round(_self.sum),
        });
      }
      
    },
    amountMore: function(){
      var _self = this;

      if (_self.actualAmount > _self.bonuses){
        _self.actualAmount = Math.round(_self.bonuses / 2)
      } 
    },

  }, 

  watch: {

    opened: function(newVal){
      var _self = this;

      _self.busy = true;

      this.API('clients/showAddPackage', {
        packageId: this.packages[newVal].id
      }, function(resp){
        _self.thisPackage   = resp;
        _self.busy          = false;
        _self.sum           = resp.options[0].price
        _self.priceId       = resp.options[0].id
      });
    },
    actualAmount: function(){
      this.amountMore()
    },

    priceId: function(newVal){
      var _self = this;
      this.sum = this.thisPackage.options.filter(elem => elem.id == newVal)[0].price;
      this.passValues();
    },

    paymentType: function(){
      this.passValues();
    },

    pacakgeId: function(){
      this.passValues();
    },

    searchQuery: function(){
      
    },
    sum: function () {
      this.send()
    },
  },
  created: function(){
    var _self = this;

    this.$bus.$on('actualAmount', function(val){
      _self.actualAmount   = val.actualAmount;
    });
  },

};
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>
  .sum
  {
    margin: 15px 0 0;
  }

  .ivu-collapse>.ivu-collapse-item>.ivu-collapse-header
  {
    height: 52px;
    padding-top: 5px;
  }
  .wrapper_label,
  .wrapper_label > div {
    display: flex;
    padding: 0;
    width: 100%;
  }
  .wrapper_label label {
    display: flex;
    width: 100%;
    justify-content: center;
  }
  .wrapp_payment_bonuses {
    margin: 30px 0 5px;
  }
</style>
