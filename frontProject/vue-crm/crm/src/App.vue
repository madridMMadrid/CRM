<template>
  <div id="app">
    <div class="navbar-container" v-if="this.$route.name != 'Login'">
      <div class="sidebar sidebar-main">
        <div class="sidebar-content">
            <!-- User menu -->
            <div class="sidebar-user">
                <div class="category-content">
                    <div class="media">
                        <a href="#" class="media-left">
                            <img :src="User.picture" class="img-circle img-sm" alt="">
                        </a>
                        <div class="media-body">
                            <span class="media-heading text-semibold">{{ User.employeeName }}</span>
                            <div class="text-size-mini text-muted">
                                {{ User.typeName }}
                            </div>
                        </div>

                        <div class="media-right media-middle">
                            <ul class="icons-list">
                                <li>
                                    
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /user menu -->
            <!-- Main navigation -->
            <div class="sidebar-category sidebar-category-visible" id="sidebarMenu">
                <div class="category-content no-padding">
                    <ul class="navigation navigation-main navigation-accordion">
                      <!-- Main -->
                      <li v-for="page in pages" v-if="page.permissions.indexOf(User.type) !== -1 && page.icon" :class="{'active' : page.current}">
                        <router-link :to="page.path">
                          <i :class="page.icon"></i>
                          <span>{{ page.name }}</span>
                        </router-link>
                      </li>
                    </ul>
                </div>
            </div>
            <!-- /main navigation -->
        </div>
    </div>
    </div>
    <div class="page-container">
        <div class="page-content">
            <div class="content-wrapper" id="wrapper">
              <div class="page">
                <transition name="fade" mode="out-in" appear>
                  <router-view/>
                </transition>
              </div>
            </div>
        </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'app',
  data() {
    return {
      pages: [],
      thisPage: this.$route.name
    };
  },

  created: function(){
    this.setUser();

    if(!this.User.jwt){
      this.$router.replace({ name: 'Login'});
    }

    this.$router.options.routes.forEach(route => {
      if(!route.noLoginRequired){
        this.pages.push({
            name: route.naming, 
            path: route.path,
            permissions: route.permissions,
            current: this.$route.name == route.path,
            icon: route.icon
        })
      }
    })
  }, 
};
</script>

<style>

</style>