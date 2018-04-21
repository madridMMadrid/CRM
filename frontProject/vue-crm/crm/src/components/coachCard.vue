<template>
  <li class="media panel panel-body stack-media-on-mobile">
    <div class="media-body">
      <h5 class="text-semibold bp-10">
        <router-link :to="'/client/'+client.id">
          coach.name 
        </router-link>
      </h5>
      <ul class="panel-heading media-list content-group no-padding-left no-margin-bottom">
        <li class="media">
          <div class="media-left">
            <i class="icon-phone2"></i>
          </div>

          <div class="media-body">
            <h6 class="media-heading">
              client.phone 
            </h6>
          </div>
        </li>
        <li class="media">
          <div class="media-left">
            <i class="icon-mail5"></i>
          </div>

          <div class="media-body">
            <h6 class="media-heading">
              client.email
            </h6>
          </div>
        </li>
        <li class="media" >
          <div class="media-left">
            <i class="icon-megaphone"></i>
          </div>

          <div class="media-body">
            <h6 class="media-heading">
              client.question
            </h6>
          </div>
      </li>
      </ul>
    </div>
    <div class="media-right text-nowrap">
    </div>
  </li>
</template>

<script>
export default {
  name: 'CoachCard',
  props: [],

  data() {
    return {
      
    };
  },

  methods: {
    
  },
};
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>

</style>
