<template>
  <tr>
    <td>{{ item.clientId }}</td>
    <td>{{ item.number }}</td>
    <td>{{ item.discount }}</td>
    <td>{{ item.expiration }}</td>
    <td>{{ item.comment }}</td>
  </tr>
</template>

<script>
export default {
  name: 'CertificateNode',
  props: {
    item: {},
  },
  beforeMount: function(){
    
  },

  created: function(){
    var _self = this;


  },

  mounted: function(){

  },

  data() {
    return {

    };
  },

  methods: {

  },

  watch: { 

  }
};
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>

</style>
