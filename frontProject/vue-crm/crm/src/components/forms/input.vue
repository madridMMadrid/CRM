<template>
  <div class="form-group" :class="{'has-feedback has-feedback-left' : icon, 'has-error': errors.has(name)}">
    <i-input :type="type" class="form-control input-xlg" :placeholder="placeholder" :name="name" v-model="value" @input="send" ref="input" v-validate="validate"/>
    <div class="form-control-feedback" v-if="icon">
      <i :class="icon"></i>
    </div>
    <span v-show="errors.has(name)" class="help-block">{{ errors.first(name) }}</span>
  </div>
</template>

<script>
export default {
  name: 'Input',
  props: {
    type: {
      default: 'text',
      type: String
    }, 
    placeholder: {},
    name: {},
    required: {
      default: false,
    },
    icon: {},
    val: {},
    validate: {}
  },

  mounted: function(){
    if(this.type === 'tel'){
      $(this.$refs.input).mask('+7 (999) 999-99-99').keyup(this.send);
    }
  },

  data() {
    return {
      value: this.val
    };
  },

  methods: {
    send: function(v){
      var val = this.value

      if(this.type === 'tel'){
        val = $(this.$refs.input).val();
      }

      this.$bus.$emit('on-input', {
        name: v.target.name,
        value: val
      });
    }
  },
};
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>

</style>
