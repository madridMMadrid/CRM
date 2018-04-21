<template>
  <!-- Page header -->
  <div>
    <page-header name="Моя статистика"></page-header>
    <!-- /page header -->
        <div class="content no-padding over">
         <div class="outside_wrap">
            <div class="wrapper_chart">
                <div class="chart one_chart" v-for="(salesman, index) in sellers">
                    <div class="wrap_top_bottom one" :class="{'animation' : animation }">
                        <i v-if="index == 0" class="icon-crown"></i>
                        <div class="wrap_img">
                            <img :src="salesman.picture" alt="">
                        </div>
                        <div class="name">
                            <p>{{ salesman.name }}</p>
                        </div>
                    </div>
                    <div class="wrap_top_bottom two" :class="{'animation' : animation }" :style="{'height': (salesman.sum / salesman.fullPercent)+'%' }">

                        <div class="column left_column" :class="{'animation' : animation }">
                          <div class="inner_column bg-success" style="height: 100%">
                            <Tooltip placement="top" style="width: 100%">
                                <i class="icon-coin-dollar"></i>   
                                <div slot="content">
                                  <p>Продано на {{ salesman.sum }}</p>
                                </div>
                            </Tooltip>  
                          </div>
                        </div>
                        <div class="wrap_bottom no-margin" :style="{'height': salesman.percentage+'%'}">
                          <div class="column right_column" :class="{'animation' : animation }">
                            <div class="inner_column inner_column_top bg-danger" :style="{'height': salesman.successPercentage+'%'}">
                              <Tooltip placement="top" style="width: 100%">
                                  <i class="icon-heart5"></i>
                                  <div slot="content">
                                    <p>Продаж {{ parseInt(salesman.success) }}</p>
                                  </div>
                              </Tooltip> 
                            </div>
                            <div class="inner_column inner_column_bottom bg-primary" :style="{'height': salesman.canceledPercentage+'%'}">
                              <Tooltip placement="top" style="width: 100%">
                                <i class="icon-heart-broken2"></i>
                                <div slot="content">
                                  <p>Отказов {{ parseInt(salesman.canceled) }}</p>
                                </div>
                              </Tooltip> 
                            </div>
                          </div>
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'Sales-stats',
  props: {
    // sellers: [],
  },

  beforeMount: function(){
    var _self = this;

    this.getData();

    this.subscribe('sale', function(){
      // console.log(arguments);
      // _self.$Notice.open({
      //   title: 'Новая заявка',
      //   desc: 'Небольшое описание '
      // });
      _self.getData();
    });
  },

  mounted: function(){
    var _self = this;
    setTimeout(function(){
      _self.animation = true
    }, 700);
  },

  data() {
    return {
      sellers: [],
      animation: false
    };
  },

  methods: {
    getData (){
      var _self = this;

      this.API('clients/myStats', {}, function(resp){
        if(resp){
          resp.sort(function(a,b){
            return b.sum - a.sum;
          });


          var salesResp = Object.assign([], resp);
          

          salesResp.sort(function(a,b){
              return (b.success + b.canceled) - (a.success + a.canceled) ;
          });


          var percent = (salesResp[0].success + salesResp[0].canceled)/100

          for (var i = 0; i < resp.length; i++) {
              resp[i].percentage = parseInt((salesResp[i].success + salesResp[i].canceled) / percent);

              var singlePercent = (salesResp[i].success + salesResp[i].canceled) / 100;

              resp[i].successPercentage = salesResp[i].success / singlePercent
              resp[i].canceledPercentage = salesResp[i].canceled / singlePercent
          }

          for (var i = 0; i < resp.length; i++) {
              var fullPercent     = resp[0].sum/100;
              
              resp[i].fullPercent = fullPercent;                
          };

          _self.sellers = resp;
        }
      });
    }
  },
};
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style>
  .ivu-tooltip-rel {
    width: 100%;
    height: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
    overflow: hidden;
  }
</style>
