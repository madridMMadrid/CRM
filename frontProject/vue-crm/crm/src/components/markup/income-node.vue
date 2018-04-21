<template>
  <tr>
    <td>
      <Poptip trigger="hover" :title="item.clientName">
        {{ item.clientName }}
      </Poptip>
<!--       <div class="dropdown dropup">
        <a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="true"><span class="caret"></span></a>
        <ul class="dropdown-menu dropdown-menu-right">
          <li><a href="#">{{ item.email }}</a></li>
          <li><a href="#">{{ item.phone }}</a></li>
        </ul>
      </div>   -->              
    </td>
    <td class="dashboardIncome_amount_true">{{ item.amount }}</td>
    <td>
      <!-- <input-bootstrap type="number" name="acceptValue" :val="item.amount" min="0" :max="item.amount"></input-bootstrap> -->

      <!-- <input-test :val="item.amount" :infoDebt="item" v-model="testVls"></input-test> -->
      <i-form>
        <form-item>
          <input type="number" :placeholder="''+item.amount" @keyup="key" class="ivu-input"/>
        </form-item>
      </i-form>  
    </td>
    <td>
      <package-label :packageName="item.packageName"></package-label>{{item.type}}
    </td>
    <td>{{ item.createdAt }}</td>
    <td>
      <ul class="clients_radio dashboardIncome no-padding no-margin ">
        <li>
          <button 
            type="button" 
            class="btn btn-default" 
            @click="accept"
            :disabled="!summa">

            <span class="ladda-label">
              <i class="icon-checkmark4"></i>
            </span>
          </button>
        </li>
        <li>
          <button type="button" class="btn btn-default" data-spinner-size="20" @click="clickBlock = true">
            <span class="ladda-label">
              <i class="icon-user-block"></i>
            </span>
          </button>
          <modal 
            v-model="clickBlock" 
            title="Причина блокировки" 
            width="700">

            <i-form>
              <form-item>
                <input type="text" v-model="comment" placeholder="Введите кометарий" @keyup="key1" class="ivu-input"/>
              </form-item>
            </i-form> 

            <div slot="footer">
              <Button 
                  type="success" 
                  size="default"
                  :disabled="!comment"
                  @click="dashboardIncomeUserBlock"> 
                    Блокировать
              </Button>
            </div>
          </modal>
         
        </li>
      </ul>
    </td>
  </tr>
</template>

<script>
export default {
  name: 'IncomeNode',
  props: {
    item: {},
  },

  beforeMount: function(){
    var _self = this;
    console.log(_self.item);
  },

  created: function(){
    var _self = this;

    this.$bus.$on('on-input', setValues);

    function setValues(event){
      _self[event.name] = event.value;
    }
  },

  mounted: function(){

  },

  data() {
    return {
      acceptValue: this.item.amount,
      paymentType: this.item.paymentType,
      testVls: this.item.amount,
      comment: '',

      rules: {
          income: [
              { type: 'integer', message: 'Пиши число епта', trigger: 'blur', required: true,  }
          ]
      },

      keyUp: 0,
      clickBlock: false,
      summa: 0,
    };
  },

  methods: {
    accept: function(){
      var _self = this;
    
        // Подтвердить оплату
        this.API('clients/confirmPayment', {
            paymentId     : this.item.paymentId,
            amount        : _self.summa,
            paymentType   : this.paymentType
        }, function (resp) {
          
            if(resp == true){
              _self.$bus.$emit('on-income-delete', _self.item.paymentId);
            } 
        });
    },


    dashboardIncomeUserBlock: function(){
      var _self = this;

       this.API('clients/blockClient', {
            clientId    : _self.item.clientId,
            type        : 1,
            comment     : _self.comment
        }, function (resp) {
            _self.clickBlock = false;
            _self.$Message.info('Клиент заблокирован');
        });

    },

    key(evt){

      var _self = this;
      var value = parseInt(evt.target.value);


      if (evt.code == 'Backspace'){
        _self.summa = value;
        return false;
      } 

      if (value > this.testVls || isNaN(value) || value < 0){
        evt.target.value = this.testVls;
        _self.summa = this.testVls;
      } else {
        _self.summa = value;
      }
    },
    key1(evt){
      var _self = this,
          value = evt.target.value;
      if (value == ''){
        console.log(value)
      } 
    },

    block: function(){

    },
    ok(){

    },

  },

  watch: { 
    amount: function(){
      this.testVls;
    }
  }
};
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>
  .ivu-form-item {
    margin-bottom: 0;
  }
</style>
