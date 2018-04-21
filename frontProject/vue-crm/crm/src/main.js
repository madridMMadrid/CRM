import Vue from 'vue';
import App from './App';
import router from './router';

Vue.config.productionTip = false;

// const APIurl = 'http://toster.tech/server/';
// const APIurl = 'http://192.168.1.181:8888/';
// const socketUrl = 'ws://192.168.1.181:8081';

const APIurl = 'http://localhost:8888/';
const socketUrl = 'ws://localhost:8080';

// Vue.filter();

Vue.mixin({
  data: function() {
    return {
      User: {
      	jwt: localStorage.getItem("somethingWierd"),

      	employeeId: null,
		employeeName: null,
		picture: null,
		type: null,
		typeName: null,
		username: null,
		dashboard: null,
      },

      socket: {}
    }
  },

 //  mounted: function(){
 //  	console.log(this)
 //  	this.block = new BlockUI(this.$el);
 //    this.block.block();
 //  },

 //  activated: function(){
 //  	console.log('mixin', this)
	// this.block.unblock();
 //  },

  methods: {
  	API: function(method, params, callback){
  		var _self = this;

		if(this.User.jwt || method == 'login/login'){
			$.ajax({
				url: APIurl+method,
				data: params,
				type: 'POST',
				beforeSend: function(request){
				    request.setRequestHeader('X-Authorization', 'Bearer ' + _self.User.jwt);
				},
				success: function(resp) {
					if(!resp || !resp.error){
						callback(resp, true);
					} else {
						console.log('resp error', resp);
						if(resp.error == 'Auth error'){
							removeToken();
						}
					}
				},
				error: function(errText) {
					if(errText.responseText){
						var data = JSON.parse(errText.responseText);

						// detect login expired
						if(data.action == 'relogin'){
							removeToken()
						}
					}
					callback(errText, false);
				}
	    	});
		} else {
			router.replace({ name: 'Login'});
		}

		function removeToken(){
			_self.User.JWT = null;
			localStorage.removeItem('somethingWierd');
			router.replace({ name: 'Login'});
		}
	}, 
	subscribe: function(name, callback){
		var bann = new ab.Session(socketUrl, function() {
			bann.subscribe(name, function(topic, data) {
				if(typeof callback === 'function'){
					callback(topic, data)
				}
		        // This is where you would add the new article to the DOM (beyond the scope of this tutorial)
		        console.log('New article published to category "' + topic + '" : ' + data.title);
		    });
		},
		function() { console.warn('WebSocket connection closed');},
			{'skipSubprotocolCheck': true}
		);
	},
	setUser: function(){
		var resp = JSON.parse(localStorage.getItem("PCRMUserData"));

		if(resp){
	      this.User.employeeId   = resp.employeeId;
	      this.User.employeeName = resp.employeeName;
	      this.User.picture      = resp.picture;
	      this.User.type         = resp.type;
	      this.User.typeName     = resp.typeName;
	      this.User.username     = resp.username;
	      console.log('User Settement', this.User, resp)
	    }
	},
	numberAddSpaces: function(val){
		return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, " ");
	}
  }
});

const EventBus = new Vue()

Object.defineProperties(Vue.prototype, {
  $bus: {
    get: function () {
      return EventBus
    }
  }
});

// import infiniteScroll from 'vue-infinite-scroll'
// Vue.use(infiniteScroll);

window.BlockUI = function(elem, options){
	this.elem = $(elem);
	this.options = $.extend(true, {
	    light: false
	}, options);

	this.block = function(){
		var _self = this;

		this.elem.block({
		    message: '<i class="icon-spinner10 spinner"></i>',
		    overlayCSS: {
		        backgroundColor 	: _self.options.light ? '#fff' : '#1B2024', 
		        opacity 			: 0.85,
		        cursor 				: 'wait',
		        position			: 'fixed',
		    },
		    css: {
		        border 				: 0,
		        padding 			: 0,
		        backgroundColor 	: 'none',
		        color 				: _self.options.light ? '' : '#fff'
		    }
		});
	};

	this.unblock = function(){
		this.elem.unblock();
	}
}

function DateParser() {
	this.basic = function(date){

		switch(typeof date) {
			case 'undefined':
				date = new Date();
				break;
			case 'object':
				break;
			case 'string':
				date = new Date(date.replace(/\s/, 'T'));
				break;
			case 'number':
				date = new Date(date)
				break;
			default:
			    throw 'DateParser Factory requires date as Data object, string or timestamp number, ' + (typeof date) + ' given in';
		}

		// welcome to JavaScript;
		if(date == null){
			date = new Date();
		}

		var monthNames = [
			"Января", "Февраля", "Марта",
			"Апреля", "Мая", "Июня", "Июля",
			"Августа", "Сентября", "Октября",
			"Ноября", "Декабря"
		];

		var day 		= date.getDate(),
	  		monthIndex 	= date.getMonth(),
	  		monthName   = monthNames[monthIndex],
	  		year 		= date.getFullYear(),
	  		hours 		= date.getHours() < 10 ? '0'+date.getHours() : date.getHours(),
	  		minutes		= date.getMinutes() < 10 ? '0'+date.getMinutes() : date.getMinutes();

	  	return {
	  		day:day, 
	  		monthIndex:monthIndex,
	  		monthName:monthName, 
	  		year:year, 
	  		hours:hours,
	  		minutes:minutes};
	}

	this.formatClient = function(date) { 
		var data = this.basic(date);
		return {	
	  		day: data.day,
	  		month: data.monthName,
	  		year: data.year,
	  		hours: data.hours,
	  		minutes: data.minutes
	  	}
	}

	this.formatClientString = function(date){
		var data = this.basic(date);
		return data.day + ' ' + data.monthName + ' ' + data.year + ' ' + data.hours+':'+data.minutes;
	}

	this.formatServer = function(date){
		var data = this.basic(date);

		if(data.day < 10){
			data.day = '0' + data.day;
		} 

		data.monthIndex += 1;

		if(data.monthIndex < 10){
			data.monthIndex = '0' + data.monthIndex;
		}


		return data.year + '-' + data.monthIndex + '-' + data.day;
	}
}

window.dateFormat = new DateParser();
/* eslint-disable no-new */
new Vue({
  el: '#app',
  router,
  render: h => h(App),
});
