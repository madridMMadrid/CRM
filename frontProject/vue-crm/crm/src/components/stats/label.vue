<template>
  <div class="col-sm-6 col-md-3">
    <div class="panel panel-body panel-body-accent">
      <div class="media no-margin">
        <div class="media-left media-middle">
          <i class="icon-3x text-danger-400" :class="icon"></i>
        </div>

        <div class="media-body text-right">
          <h3 class="no-margin text-semibold" id="debtLabel">{{ val }}</h3>
          <span class="text-uppercase text-size-mini text-muted">{{ name }}<br><slot></slot></span>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'StatsLabel',
  props: {
    name: {},
    val: {},
    icon: {}
  },

  data() {
    return {
      value: null
    };
  },

  methods: {

  },
};
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>

</style>
