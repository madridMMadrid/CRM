<template>
<!-- Page header -->
<div>
         
  <page-header name="Карта тренеран">
    <div slot="basis" class="test_1 position-left">
      <div class="pull-left col-md-2 test_ept">
        <a href="#" class="media-left">
          <img alt="" class="img-circle img-sm">
        </a>
        <div class="media-body">
          <div class="text-size-mini text-muted media-heading text-semibold">Афанасий Крепкий</div>
            <div class="text-size-mini text-muted">
              Мастер спорта
            </div>
        </div>
      </div> 
    </div>

    <Checkbox v-model="checked" style="display:none;">
        <span v-if="checked">Checked</span>
        <span v-else>Unchecked</span>
    </Checkbox>
    <Button type="primary" @click="checked = !checked">
        <span v-if="!checked">Заблокировать</span>
        <span v-else>Разблокировать</span>
    </Button>

  </page-header>
  <!-- /page header -->
    <div class="content">
      <Row :gutter="16">
        <Col span="6">
          <Row> 
            <div class="wrap_row">
               <h5 class="text-semibold bp-10">Контакты</h5>
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
                  <li class="media">
                    <div class="media-left">
                      <i class="icon-piggy-bank"></i>
                    </div>

                    <div class="media-body">
                      <h6 class="media-heading">
                        client.question
                      </h6>
                    </div>
                   </li>
                </ul>
                <Button type="primary" long>Отправить выплату</Button>
            </div>
          </Row>
          <Row>
            <div class="wrap_row">
             <h5 class="text-semibold bp-10">Привлеченные контакты</h5>
             <br />
              <i-form @submit.prevent="search"> 
                <form-item>
                  <i-input v-model="scroll.query" placeholder="Имя клиента, номер телефона или email">
                    <span slot="prepend">
                      <Icon type="search" size="22"></Icon>
                    </span>
                  </i-input>
                </form-item>
              </i-form>
              <scroll :on-reach-edge="loadMore" loading-text="Загружаем..." :height="scroll.height">
                <div class="col-lg-12 no-padding">
                  <client-card v-for="client in clients" :client="client"></client-card>
                </div>
              </scroll>  
            </div>
          </Row>  
        </Col>
        <Col span="8">
            <div class="wrap_row">
              <Row> 
               <h5 class="text-semibold bp-10">Утиные истории</h5> 
               <br />
               <i-form> 
                <form-item>
                  <i-input placeholder="Поиск по истории...">
                    <span slot="prepend">
                      <Icon type="search" size="22"></Icon>
                    </span>
                  </i-input>
                </form-item>
              </i-form>
                <ul class="panel-heading media-list content-group no-margin-bottom">
                  <li class="media">
                    <div class="media-left">
                      <i class="icon-plus-circle2"></i>
                    </div>

                    <div class="media-body">
                      <h6 class="media-heading">
                        Анна купила пакет Silver 1500, Вам начисленно 10%, так держать!
                      </h6>
                    </div>
                    <div class="media-right">
                       <Tag  closable color="green">+10$</Tag>
                    </div>
                  </li>

                  <li class="media">
                    <div class="media-left">
                      <i class="icon-forward3"></i>
                    </div>

                    <div class="media-body">
                      <h6 class="media-heading">
                        Валерий продлил контракт Gold 2500, Вам начисленно 30%
                      </h6>
                    </div>
                    <div class="media-right">
                       <Tag closable color="green">+30$</Tag>
                    </div>
                  </li>
                  <li class="media">
                    <div class="media-left">
                      <i class="icon-bubble-notification"></i>
                    </div>

                    <div class="media-body">
                      <h6 class="media-heading">
                        Александ не подтвердил что вы его тренер
                      </h6>
                    </div>
                    <div class="media-right">
                       <Tag closable color="yellow">warning</Tag>
                    </div>
                   </li>
                   <li class="media">
                    <div class="media-left">
                      <i class="icon-coin-dollar"></i>
                    </div>

                    <div class="media-body">
                      <h6 class="media-heading">
                        Вам выплачено 200$, не потрать все сразу!
                      </h6>
                    </div>
                    <div class="media-right">
                       <Tag closable color="red">-200$</Tag>
                    </div>
                   </li>
                </ul>
              </Row>
            </div>
        </Col>
        <Col span="6">
          <Row>
            <div class="row">
              <stats-label name="ВЫПЛАЧЕНО" :val="debt.current" icon="ivu-icon ivu-icon-social-bitcoin" :class="{ activeWidthFull: isActive}">
              </stats-label>
              <stats-label name="ДОХОД ЗА ВСЕ ВРЕМЯ" :val="debt.current" icon="icon-spinner2" :class="{ activeWidthFull: isActive}">
              </stats-label>
              <stats-label name="АКТИВНЫХ КЛИЕНТОВ" :val="debt.current" icon="icon-person" :class="{ activeWidthFull: isActive}">
              </stats-label>
            </div>
          </Row> 
        </Col>
    </Row>
    </div>
  </div>
</div>
</template>

<script>
export default {

  name: 'CoachCardPage',

  created () {},

  beforeMount: function(){
    this.search();
  }, 

  mounted: function(){
    this.scroll.height = $(window).height() - $('.content').position().top - 30
  },


  data() {
    return {
      isActive: true,
      debt: {
        current: 2000,  
      },
      checked: true,
      theme3: 'light',
      clients: [],

      scroll: {
        from: 0,
        to: 20,
        query: '',
        timer: null,
        height: 500
      },
      columns1: [
          {
              title: 'Name',
              key: 'name'
          },
          {
              title: 'Age',
              key: 'age'
          },
          {
              title: 'Address',
              key: 'address'
          }
      ],
      data1: [
          {
              name: 'John Brown',
              age: 18,
              address: 'New York No. 1 Lake Park',
              date: '2016-10-03'
          },
          {
              name: 'Jim Green',
              age: 24,
              address: 'London No. 1 Lake Park',
              date: '2016-10-01'
          },
          {
              name: 'Joe Black',
              age: 30,
              address: 'Sydney No. 1 Lake Park',
              date: '2016-10-02'
          },
          {
              name: 'Jon Snow',
              age: 26,
              address: 'Ottawa No. 2 Lake Park',
              date: '2016-10-04'
          }
      ]
    };
  },
  methods: {
    search: function(){
      var _self = this;

      var params = {
        limitFrom: this.scroll.from,
        limitTo: this.scroll.to,
        query: this.scroll.query
      };

      console.log(params);

      this.API('clients/displayClients', params, function(resp){
        if(params.limitFrom == 0 && params.query != ''){
          _self.clients = resp;
        } else {
          _self.clients = _self.clients.concat(resp);
        }
      });
    }, 
    loadMore: function(){
      this.scroll.from = this.scroll.from + 20
      this.scroll.to = this.scroll.to + 20
      this.search();
    }
  }, 

  watch: {
    'scroll.query': function(someval){
      var _self = this;

      clearTimeout(_self.scroll.timer);
      _self.scroll.timer = setTimeout(function(){
        _self.scroll.from = 0;
        _self.scroll.to = 20;
        _self.query = someval;
        _self.search();
      }, 500);
    }
  },
};
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>
  .wrap_row {
    border: 1px solid #d7dde4;
    padding: 10px;
    margin-bottom: 10px;
  }
  .layout {
    margin-top:-20px; 
    margin-bottom:30px;
    padding:10px;
    background:#fff;
    border-bottom:1px solid #ccc;
    border-top:1px solid #ccc;
  }
  .ivu-layout {
    background: #fff;
  }
  .page-header-content {
    margin-bottom: 0;
  }
  .activeWidthFull {
    width: 100% !important;
  }
</style>
