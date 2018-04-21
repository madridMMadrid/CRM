<template>
  <div class="wrapp_payment_bonuses">
     <div v-if="bonuses > 0" class="bonus">
       <Collapse v-model="collapse" accordion>
        <Panel name="1">
            Бонусы
            <p slot="content">
              <ul>
                <li>Доступно: {{ bonuses }}</li>
                <li>Оплатить: <InputNumber v-model="actualAmount" size="small" :max="Math.round(Number(summa) / 2)" :min="0"></InputNumber></li>
              </ul>
            </p>
        </Panel>
    </Collapse>
    </div>
  </div>
</template>

<script>
export default {
  name: 'PaymentBonuses',
  props: {
    bonuses: {},
  },

  beforeMount(){},

  mounted: function(){},

  data() {
    return {
      collapse: [],
      actualAmount: 0,
      summa: 0,
    };
  },

  methods: {
    pushBonuses: function(){
      if(typeof this.collapse[0] != 'undefined'){
        this.$bus.$emit('actualAmount', {
          actualAmount    : Math.round(this.actualAmount),
        });
      }
    },

    toggleBonuses(){
      this.$bus.$emit('actualAmount', {
        actualAmount: this.collapse[0] ? this.actualAmount : 0
      });
    },
    amountMore: function(){
      var _self = this;

      if (_self.actualAmount > _self.bonuses){
        _self.actualAmount = Math.round(_self.bonuses / 2)
      }
    },
    summaMore: function(){
      var _self = this;
      if (_self.actualAmount > _self.bonuses) {
      }
    },
    writtenSumma: function(){
      var _self = this;

      if (_self.summa > _self.bonuses){
        _self.summa = _self.bonuses
      } else {
         _self.actualAmount = Math.round(_self.summa / 2);
      }
    }

  },

  watch: { 
    summa: function () {
      // this.actualAmount = Math.round(this.summa / 2);
      this.writtenSumma()

    },
    collapse: function(newVal){
      this.toggleBonuses();
    },
    actualAmount: function(){
      this.amountMore();
      this.pushBonuses();
    },

  },

  created: function(){
    var _self = this;

    this.$bus.$on('package-prolongation', function(val){
      _self.summa  = val.amount;
    });
    this.$bus.$on('sending_to_list', function(val){
      _self.summa = val.amount;
    });
    // this.$bus.$on('callapse', function(val){
    //   _self.bonusCallapse = val.bonusCallapse;
    // });
  },
};
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>
  .wrapp_payment_bonuses {
    display: flex;
    flex-wrap: wrap;
  }
  .wrapp_payment_bonuses > div {
    width: 100%;
  }
  .wrapp_payment_bonuses .bonus {
    margin: 0px 0 5px;
  }
</style>