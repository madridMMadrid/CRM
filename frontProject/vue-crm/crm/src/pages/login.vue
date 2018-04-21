<template>
  <div class="login">
    <div class="table center">
      <div class="middle">
        <div class="logo_img"></div>
        <form id="loginForm" @submit.prevent="submit" @keyup="keyup">
          <div class="roundedChilds">
            <div class="form_row placeholderInside">
              <input type="email" name="login" required/>
              <span class="placeholder">Логин</span>
              </div>
              <div class="form_row placeholderInside">
                <input type="password" name="password" required/>
                <span class="placeholder">Пароль</span>
              </div>
              <button class="light" id="triggerLogin" :disabled="valid != true">Войти</button>
            </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'Login',
  data() {
    return {
      login: null,
      password: null,

      valid: false
    };
  },

  created: function(){
  },

  methods: {
    submit: function(){
      var _self = this;

      this.API('login/login', {
          username: _self.login,
          password: _self.password
      }, function(resp, success){
        if(success){
          _self.User.jwt = resp.jwt;
          
          switch(resp.user.type){
              case 1:
                  _self.User.dashboard = 'dashboard';
                  break;
              case 2:
                  _self.User.dashboard = 'dashboardHOS';
                  break;
              case 3:
                  _self.User.dashboard = 'dashboardIncome';
                  break;
              case 5:
                  _self.User.dashboard = 'dashboard';
                  break;
          }

          localStorage.setItem('somethingWierd', resp.jwt);
          localStorage.setItem("PCRMUserData", JSON.stringify(resp.user));

          _self.setUser();
          _self.$router.replace({ name: 'Main'});
        }
      });
    }, 
    keyup: function(evt){
      this[evt.target.name] = evt.target.value;
      this.validate()
    },
    validate: function(){
      this.valid = this.login && this.password ? true : false;
    }
  },
};
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>
  .login
  {
    position: fixed;
    left: 0;
    right: 0;
    top: 0;
    bottom: 0;
    text-align: center;
  }

  .table 
  {
    width: auto;
    margin: 0 auto;
    height: 100%;
    display: table;
  }

  #loginForm button:disabled 
  {
    background: transparent;
    border: 2px solid #1E88E5;
    color: #1E88E5;
    opacity: .7;
  }
</style>
