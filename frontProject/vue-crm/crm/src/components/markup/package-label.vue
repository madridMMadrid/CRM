<template>
  <div>
    <div class="media-left" :class="{'no-padding': noPadding}">
      <span class="btn btn-rounded btn-icon btn-xs" :class="itemType">
        <span class="letter-icon">
          {{ type ? 'S' : 'G' }}
        </span>
      </span>
      <span v-if="badgeCount > -1" class="badge media-badge" :class="badgeColor">{{ badgeCount }}</span>
    </div>
    <div class="media-right" style="vertical-align: middle;">
      <div class="media-heading">
        <span class="letter-icon-title">{{ packageName }}</span>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'PackageLabel',
  props: {
    packageName: {},
    noPadding:{
      default: false
    },
    badgeCount: '',
  },

  mounted: function(){
    this.itemType = this.packageName.search('Silver') > -1 ? 'bg-grey-300' : 'bg-orange-400';
    this.type = this.packageName.search('Silver') > -1 ? true : false;
  },

  data() {
    return {
      itemType: null,
      type: 0
    };
  },

  methods: {
    
  },

  computed:{
    badgeColor: function(){
      var result = '';

      if(this.badgeCount < 3){
        result = 'badge-danger';
      } else if(this.badgeCount < 5){
        result = 'badge-warning';
      } else {
        result = 'badge-success';
      }

      return result;
    }
  },

  watch: { 

  }
};
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>

</style>