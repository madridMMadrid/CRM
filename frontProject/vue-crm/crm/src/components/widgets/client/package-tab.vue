<template>
  <div>
    <Tooltip content="Адрес доставки">
      <Button type="default" size="large" @click="addressModal = true">
        <Icon type="location" size="20"></Icon>
      </Button>
    </Tooltip>
    <Tooltip content="Время доставки">
      <Button type="default" size="large" @click="timeModal = true">
        <Icon type="ios-clock" size="20"></Icon>
      </Button>
    </Tooltip>
    <calendarity :package="package" :active="active"></calendarity>

    <modal v-model="addressModal" title="Адрес доставки" width="800">
      <div class="panel-body no-border largeInput">
        <address-picker v-once v-model="addresses" :addressesProp="addresses.addresses" ref="addressComponent"></address-picker>
      </div>
      <div slot="footer">
        <Row type="flex" justify="end" class="code-row-bg">
          <Button type="success" :disabled="!addresses.valid" @click="submitAddresses">Сохранить</Button>
        </Row>
      </div>
    </modal>
    <modal v-model="timeModal" title="Время доставки" width="800">
      <div class="panel-body no-border largeInput">
        <times-picker v-once :timesProp="times.times" v-model="times" ref="timesComponent"></times-picker>
      </div>
      <div slot="footer">
        <Row type="flex" justify="end" class="code-row-bg">
          <Button type="success" :disabled="!times.valid" @click="submitTimes">Сохранить</Button>
        </Row>
      </div>
    </modal>
  </div>
</template>

<script>
export default {
  name: 'PackageTab',
  props: {
    package: {},
    active: {}
  },

  mounted: function(){

  },

  data() {
    return {
      addressModal: false,
      timeModal: false,

      addresses: {
        valid: false,
        addresses: this.package.addresses
      },

      times: {
        valid: false,
        times: this.package.deliveryTime
      },

      waitForUpdateAddress: false
    };
  },

  methods: {
    update: function(){
        var _self = this;
        this.API('clients/getAddresses', {
            'clientPackageId': _self.package.id
        }, function (resp) {
            _self.addresses.addresses = resp;
        });
    },

    submitAddresses: function(){
      var _self = this;

      this.waitForUpdateAddress = true;
      this.$refs.addressComponent.setAddresses();

    },

    submitTimes: function(){
      var _self = this;

      this.times.times.forEach(function(el){
        el.clientPackageId = _self.package.id;
      });

      this.API('clients/setTime', {times: this.times.times}, function (resp) {
        _self.timeModal = false;
        _self.$Message.success('Время доставки сохранено');
      });
    }
  },

  watch: {
    'addresses.addresses': function(newVal, oldVal){
      var _self = this;

      if(typeof newVal && this.addresses.addresses && this.waitForUpdateAddress){

        this.waitForUpdateAddress = true;

        this.addresses.addresses.forEach(function(el){
          el.clientPackageId = _self.package.id;
        });

        this.API('clients/addAddress', {
            addresses: this.addresses.addresses
        }, function (resp) {
            _self.addressModal = false;
            _self.$Message.success('Адрес доставки сохранен');
        });
      }
    },
  }
};
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>
  
</style>
