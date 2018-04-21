<template>
  <tr class="test_ep" @click="triggerPackage" ref="packNode">
    <td class="js-client-getPackageId_first_td">
      <package-label :packageName="package.packageName" :noPadding="true" :badgeCount="package.daysRemain"></package-label>
      <div class="text-muted text-size-small leftOffset">
          <i class="icon-comment position-left"></i>
          <span class="js-client_package-comment" contenteditable="true" @blur="onPackageCommentEdit">{{ package.comment }}</span>
        </div>
    </td>
    <td>
      <ul class="icons-list">
        <li class="dropdown">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false"><i class="icon-menu7"></i></a>
              <ul class="dropdown-menu dropdown-menu-right" v-if="!package.action">
                  <li>
                    <a class="js-popupProlongPackage" @click="prolongPacakge = true">
                      <i class="icon-forward3"></i>Продлить
                    </a>
                  </li>
                  <li>
                    <a class="popupChangePackage" @click="clientModalChangePackage = true">
                      <i class="icon-loop3"></i>Сменить
                    </a>
                  </li>
              </ul>
              <ul class="dropdown-menu dropdown-menu-right" v-else>
                <li>
                  <a class="noevents">{{ packageEventName(package.action.actionType) }}</a>
                </li>
                <li>
                  <a class="noevents">{{ packageEventData(package.action.updatedAt) }}</a>
                </li>
                <li class="divider"></li>
                <li>
                  <a class="deletePackage" @click="cancelPackage">
                    <i class="icon-subtract text-danger-600"></i>Отмена
                  </a>
                </li>
              </ul>
        </li>
      </ul>

      <modal
        size="modal-lg" 
        width="1000"
        color="bg-success" 
        ref="packageProlongation" 
        v-model="prolongPacakge"
        @on-ok="ok">

        <div slot="header">Продлить пакет </div>
        <div class="wrapper_translit_form">

          <div class="modal-body">
            <div class="modal-body">
              <div class="wrap_change">
                <div class="inner_change_wrappwer_left col-md-6">
                  <div class="panel-heading">
                    <h6 class="panel-title">Сейчас<a class="heading-elements-toggle"><i class="icon-more"></i></a></h6>
                    <div class="heading-elements"></div>
                  </div>
                  <div class="table-responsive">
                    <table class="table">
                      <tbody>
                        <tr>
                          <td>
                            <package-label :packageName="package.packageName"></package-label>
                          </td>
                          <td align="center">
                            <h6 class="text-semibold no-margin">
                              <p>{{ package.daysRemain }} Дней</p>
                            </h6>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>  
                </div>
                <div class="innere_change_wrapper_right col-md-6">
                  <extensionPackage 
                    v-model="prolongation" 
                    :package="package" 
                    :clientid="clientid"
                    :bonuses="bonuses">
                  </extensionPackage>
                </div>
                </div>
              </div>
        </div>
      </div>
      <div slot="footer">
            <Button 
              type="default" 
              size="default" 
              @click="cancel">
                Отмена
            </Button>
            <Button 
              type="success" 
              size="default" 
              @click="popupProlongPackageSend"
              @on-ok="ok">
                Продлить
            </Button>
      </div>
    </modal>

    <modal
      size="modal-lg"
      width="1000" 
      color="bg-success" 
      v-model="clientModalChangePackage">

      <div slot="header" class="test-test">Смена пакета</div>
      <div class="wrapper_translit_form">
        <div class="modal-body">
          <div class="modal-body">
          <div class="wrap_change">
            <div class="inner_change_wrappwer_left col-md-6">
              <div class="panel-heading">
                <h6 class="panel-title">Сейчас<a class="heading-elements-toggle"><i class="icon-more"></i></a></h6>
                <div class="heading-elements"></div>
              </div>
              <div class="table-responsive">
                <table class="table">
                  <tbody>
                    <tr>
                      <td>
                        <package-label :packageName="package.packageName"></package-label>
                      </td>
                      <td align="center">
                        <h6 class="text-semibold no-margin">
                          <p>{{ package.daysRemain }} Дней</p>
                        </h6>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>  
            </div>
            <div class="innere_change_wrapper_right col-md-6">
              <div class="panel-heading">
                <h6 class="panel-title">Станет<a class="heading-elements-toggle"><i class="icon-more"></i></a></h6>
                <div class="heading-elements"></div>
              </div>
              <div class="table-responsive">
                <div class="panel-group panel-group-control content-group-lg js-client-packageList" id="accordion-control">
                  <div class="changePackageList">
                    <package-list 
                      v-model="changePackage.pack" 
                      :isShort="true" 
                      :ignoreId="package.packageId"
                      :bonuses="bonuses"
                      :clientId="clientid">
                    </package-list>
                  </div>
                </div>
              </div>
            </div>
          </div>
          </div>
        </div>
      </div>
      <div slot="footer">
            <Button 
              type="default" 
              size="default" 
              @click="cancel">
                Отмена
            </Button>
            <Button 
              type="success" 
              size="default"
              @click="changePackageClick">
                Сменить
            </Button>
          </div>
    </modal>
    </td>
  </tr>
</template>

<script>
export default {
  name: 'Package',
  props: {
    package: {},
    clientid: {},
    bonuses: {},
  },

  data() {
    return {
      index: '',
      paymentTypeChecked: '',

      prolongation: {
        amount: null,
        paymentType: null
      },

      prolongPacakge: false,
      clientModalChangePackage: false,

      changePackage: {
        pack: {}
      },
      actualAmount: 0,
    };
  },

  created: function(){
    var _self = this;

    this.$bus.$on('package-prolongation', function(val){
      _self.prolongation.amount       = val.amount;
      _self.prolongation.paymentType  = val.paymentType;
      // _self.prolongation.bonus        = val.bonus;
    });

    this.$bus.$on('actualAmount', function(val){
      _self.actualAmount   = val.actualAmount;
    });

  },

  mounted: function(){
    // костыльно писец
    // будет вермя - надо переписать по нормальному
    setTimeout(function(){
        var tabs = $('#packageTabber .ivu-tabs-tab'),
          packageList = $('#clientPackagesList');

      if(tabs.length && packageList.length){
        $(packageList.find('tr').get(0)).trigger('click');
      }
    }, 700);
  },
  beforeMount: function () {},

  methods: {
    onPackageCommentEdit: function (event) {
      var _self = this;

      this.API('clients/changePackageComment', {
          clientPackageId          : _self.package.id,
          comment                  : $(event.currentTarget).text(),
          bonus                    : _self.bonuses,
      }, function(resp){
          console.log(resp)
      });
    },

    packageEventName: function(name){
      var result = '';
      
      switch(name){
        case 0:
          result = 'Ожидается смена пакета'
          break;
        case 1:
          result = 'Ожидается добавление пакета'
          break;
        case 2:
          result = 'Ожидается продление пакета'
          break;
      }
      return result;
    },

    packageEventData: function (data) {
      return dateFormat.formatClientString(data);
    },

    cancel: function () {
      this.prolongPacakge           = false;
      this.clientModalChangePackage = false;
    },

    cancelPackage: function () {
      var _self = this;
      
       this.API('clients/cancelPackageChange', {
            clientPackageId: this.package.id
        }, function (resp) {
          console.log('yup, it fired')
          _self.$bus.$emit('on-client-update-packages');
          _self.$Message.success('Действие отменено');
        });
    },

    changePackageClick: function () {

      var _self = this;

        var sendData = {
            clientPackageId   : this.package.id,
            packageId         : this.changePackage.pack.packageId,
            amount            : this.changePackage.pack.sum,
            priceId           : this.changePackage.pack.priceId,
            paymentType       : this.changePackage.pack.paymentType,
            bonus             : this.actualAmount,
        }

        this.API('clients/changePackage', sendData, function(resp){
          _self.clientModalChangePackage = false;
          _self.$bus.$emit('on-client-update-packages');
          _self.$Message.success('Пакет сменен');
        });

    },

    popupProlongPackageSend: function(){

        var _self = this;

        this.API('clients/packageProlongation', {
            clientPackageId   : this.package.id,
            amount            : this.prolongation.amount,
            paymentType       : this.prolongation.paymentType,
            bonus             : this.actualAmount,
        }, function(resp){
            _self.prolongPacakge = false;
            _self.$bus.$emit('on-client-update-packages');
            _self.$Message.success('Пакет продлен');
        });
    },

    triggerPackage: function(evt){
      var tabs = $('#packageTabber .ivu-tabs-tab');

      if(tabs.length){
        $(tabs.get($(evt.currentTarget).index())).trigger('click');
        $(evt.currentTarget).addClass('activeTab');
        $(evt.currentTarget).siblings('.activeTab').removeClass('activeTab');
      }
    },

    ok() {
      
    }, 
  },

  watch: {
    prolongation: function(newval){}
  }, 
};
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>
  .activeTab:after
  {
    content: "";
    width: 45px;
    height: 45px;
    position: absolute;
    right: -12px;
    background: #fff;
    border: 2px solid #ddd;
    transform: rotate(45deg);
    margin: 10px 0 0 -28px;
    border-bottom-left-radius: 1004px;
    border-bottom-color: transparent;
    border-left-color: transparent;
    z-index: 9;
  }

  .leftOffset
  {
    padding-left: 51px;
  }
</style>