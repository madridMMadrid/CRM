<template>
  <div class="panel panel-flat">
      <div class="panel-heading">
        <h6 class="panel-title">Комментарии</h6>
      </div>

      <div class="panel-body">
        <ul class="media-list chat-list content-group" id="tasksList">
          <li v-for="comment in comments.slice().reverse()" class="media" :class="{ 'reversed' : comment.isMyComment }">
            <div class="media-left" v-if="!comment.isMyComment">
              <a>
                <img :src="comment.picture" class="img-circle img-md">
              </a>
            </div>
            <div class="media-body">
              <div class="media-content">
                  {{ comment.comment }}
              </div>
              <span class="media-annotation display-block mt-10">{{ comment.name }}</span>
              <span class="media-annotation display-block">{{ comment.date }}</span>
            </div>
            <div class="media-right" v-if="comment.isMyComment">
              <a>
                <img :src="comment.picture" class="img-circle img-md">
              </a>
            </div>
          </li>
        </ul>

          <textarea id="taskInput" name="enter-message" type="text" class="form-control content-group" placeholder="Введите коментарий" v-model="newComment"></textarea> 

          <div class="row">
            <div class="col-xs-12 text-right">
              <button type="button" class="btn btn-primary btn-ladda btn-ladda-spinner" @click="pushComment">
                <span class="ladda-label">Добавить коментарий</span>
              </button>
            </div>
          </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'Comments',
  props: {
    url: {},
    passdata: {},
    clientid: {},
  },

  mounted: function(){

  },

  data() {
    return {
      comments: [],
      newComment: ''
    };
  },

  methods: {
    pushComment: function(){
      var _self = this;

      this.API('clients/pushComment', {
        clientId: _self.clientid,
        comment : _self.newComment
      }, function(resp, success){
        _self.update();
      })
    }, 

    update: function(){
      var _self = this;

      this.API(_self.url, {
        clientId: _self.clientid,
        limitFrom: 0,
        limitTo: 20
      }, function(resp){
        _self.comments = resp;
      });
    },
  },

  watch: { 
    passdata: function(newVal, oldVal) { // watch it
      this.comments = newVal;
    },

    comments: function(newVal, oldVal){
      var comments = newVal;

      for (var i = 0; i < comments.length; i++) {
        var date = comments[i].date;
        comments[i].date = dateFormat.formatClientString(date);
      }

      this.comments = comments;
    }
  }
};
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>

</style>
