<template>
  <div class="wrap_group">
    <RadioGroup type="button" size="large" v-model="value">
       <Radio label="1">
         <Tooltip content="Наличными">
           <Icon type="cash" size="18"></Icon>
         </Tooltip>
       </Radio>
       <Radio label="2">
         <Tooltip content="Картой">
           <Icon type="card" size="18"></Icon>
         </Tooltip>
       </Radio>
       <Radio label="3">
         <Tooltip content="Онлайн">
           <Icon type="android-globe" size="18"></Icon>
         </Tooltip>
       </Radio>
    </RadioGroup>
  </div>
</template>

<script>
export default {
  name: 'PaymentType',
  props: {
    clientid: {},
    bonuses: {},
  },

  beforeMount(){},

  mounted: function(){

  },

  data() {
    return {
      value: 1,
      value1: '0',
      value3: 0,
      summa: 0,
    };
  },

  methods: {

  },

  watch: { 
    value: function(){
      this.$emit('input', this.value);
    },
    summa: function () {
      var _self = this;
      _self.value3 = _self.summa / 2;
    }
  },

  created: function(){
    var _self = this;

    this.$bus.$on('package-prolongation', function(val){
      _self.summa  = val.amount;
    });
  },
};
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>
  .ivu-radio-group.ivu-radio-group-large.ivu-radio-large.ivu-radio-group-button {
    display: flex;
    width: 100%;
  }
  .ivu-radio-group.ivu-radio-group-large.ivu-radio-large.ivu-radio-group-button label,
  .js-prolongPackageDaysChange .prolong_label_type label {
    width: 100%;
    display: flex;
    justify-content: center;
  }
  .wrap_group {
    display: flex;
    flex-wrap: wrap;
  }
  .wrap_group > div {
    width: 100%;
  }
  .wrap_group .bonus {
    margin: 20px 0 5px;
  }
</style>