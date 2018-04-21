<template>
  <button type="button" class="btn bg-danger btn-ladda btn-ladda-spinner ladda-button" data-style="expand-left" data-spinner-color="#fff" data-spinner-size="20" @click="action" :name="name" ref="buttonSpinner">
     <span class="ladda-label">{{ text }}</span>
     <span class="ladda-spinner"></span>
   </button>
</template>

<script>
export default {
  name: 'spinnerBtn',
  props: {
    text: {},
    name: {}
  },


  data() {
    return {
      l: null
    };
  },

  mounted: function(){
    this.l = Ladda.create(this.$refs.buttonSpinner);
  },

  methods: {
    action: function(v){
      this.l.start();
      this.$bus.$emit('on-button-spinner', {
        name: $(v.target).closest('button')[0].name,
      });
    },

    reset: function(){
      this.l.remove();
    }
  },

};
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>

</style>