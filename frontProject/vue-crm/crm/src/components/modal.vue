<template>
  <div class="modal fade in" :id="id" ref="modal">
    <div class="modal-dialog" :class="size">
      <div class="modal-content">
        <div class="modal-header" :class="color">
          <button type="button" class="close" data-dismiss="modal">×</button>
          <h6 class="modal-title">
            <slot name="header"></slot>
          </h6>
        </div>
        <slot></slot>
      </div>
      <div class="modal-footer">
        <slot name="footer"></slot>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'Modal',
  props: {
    id:{},
    color: {
      default: 'bg-primary-400'
    },
    size: {
      default: 'modal-sm'
    }
  },

  data() {
    return {
      
    };
  },

  methods: {
    close: function(){
      $(this.$refs.modal).modal('hide');
    }
  },

  beforeDestroy: function(){
    $('.modal-backdrop').removeClass('in').one('transitionend', function(){
      $(this).remove();
    })
  }
};
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>

</style>
