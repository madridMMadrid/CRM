<template>
  <div class="form-group has-feedback has-feedback-left">
    <select class="bootstrap-select" data-style="btn-default btn-lg" data-width="100%" @change="send" :name="name" ref="bootstrapselect">
      <option v-for="option in options" :value="option.value">{{ option.name }}</option>
    </select>
  </div>
</template>

<script>
export default {
  name: 'Select-bootstrap',
  props: {
    options: {},
    name: {}
  },

  updated: function(){
    $(this.$refs.bootstrapselect).selectpicker();
  },

  data() {
    return {
      
    };
  },

  methods: {
    send: function(v){
      this.$bus.$emit('on-select', {
        name: v.target.name,
        value: v.target.value
      });
    }
  },

};
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>

</style>
