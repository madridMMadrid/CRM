<template>
  <div class="page-header page-header-default">
    <div class="page-header-content">
     <div class="page-title">
         <h4 class="display_flex"> 
             <i class="icon-arrow-left52 position-left" id="goBackToGoodOldPage"></i>
             <span class="text-semibold col-md-12">
              {{ name }}
            </span>
            <slot name="basis"></slot>
         </h4>
     </div>
    <div class="heading-elements">
      <div class="heading-btn-group">
        <slot></slot>
      </div>
    </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'PageHeader',
  props: {
    name: {}
  },

  mounted: function(){},

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
  .display_flex {
    display: flex;
    justify-content: center;
    align-items: center;
  }

  .heading-btn-group
  {
    display: flex;
  }
</style>
