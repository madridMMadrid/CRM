<template>
	<div>
  	<form>
        <div class="panel-body no-padding js-prolongPackageDaysChange" >
          <div class="panel-heading">  
            <div class="panel-title">
              <h6>Продлить, на дней: </h6>
            </div>
          </div>
          <RadioGroup type="button" size="large" v-model="priceId" class="small-center-padding prolong_label">
            <Radio v-for="option in prolong.options" :label="option.id">
              <Tooltip :content="option.priceToPay + ' руб.'">
                {{ option.packageLength }}
              </Tooltip>
            </Radio>
          </RadioGroup>
          <div class="prolong_label_type" v-model="priceId">
            <payment-type 
              v-model="paymentType" 
              :clientid="clientid"
              :bonuses="bonuses">
            </payment-type>
            <payment-bonuses
              :bonuses="bonuses">
            </payment-bonuses>
          </div> 
          <br>
            <p class="sum">Итого: {{ sum }}</p>
            <div v-if="actualAmount">
              <p>Бонусами: {{ actualAmount}}</p>
              <p>Сумма к оплате: <code>{{ sum - actualAmount }}</code></p>
            </div>
        </div>
    </form>
	</div>
</template>

<script>
export default {
  name: 'ExtensionPackage',
  props: {
    package: {},
    clientid: {},
    bonuses: {},
  },

  beforeMount: function(){
  	var _self = this;

  	this.API('clients/showProlongPackage', {
        'clientId'          : this.package.clientId,
        'clientPackageId'   : this.package.id,
        bonus               : this.bonuses,
    }, function (resp) {
    	_self.prolong 				= resp;
      _self.sum             = _self.prolong.options[0].priceToPay;

    });


  },

  mounted: function(){
    
  },

  data() {
    return {
    	prolong: {},
    	paymentType: '1',
      priceId: 1,
      sum: 0,
      actualAmount: 0,
      collapse: [],
    };
  },

  methods: {
    send: function(){
    	this.$bus.$emit('package-prolongation', {
	      amount 		    : this.sum,
	      paymentType 	: this.paymentType,
	    });
    },

    pushBonuses: function(){
      if(typeof this.collapse[0] != 'undefined'){
        this.$bus.$emit('actualAmount', {
          actualAmount    : this.actualAmount,
        });
      }
    },

    toggleBonuses(){
      this.$bus.$emit('actualAmount', {
        actualAmount: this.collapse[0] ? this.actualAmount : 0
      });
    },

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
    collapse: function(newVal){
      this.toggleBonuses();
    },
    actualAmount: function(){
      this.pushBonuses();
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
  .js-prolongPackageDaysChange .prolong_label,
  .js-prolongPackageDaysChange .prolong_label_type > div {
    display: flex;
    justify-content: center;
    padding: 10px 0;
  }
  .js-prolongPackageDaysChange .prolong_label label {
    width: 100%;
    display: flex;
    justify-content: center;
  }
</style>