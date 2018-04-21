var appData = {
	plugins: {
		'owlCarousel': {
			loaded	: false,
			path	: '/js/plugins/owl.carousel.min.js'
		},
		'maskedInput': {
			loaded	: false,
			path	: '/js/plugins/jquery.maskedinput.min.js'
		}
	},

	pages: [
        {
            name: 'Мои задачи',
            href: '#dashboard',
            icon: 'icon-home4',
            permission: [1,2,5]
        },{
            name: 'Панель управления',
            href: '#dashboardHOS',
            icon: 'icon-chart',
            permission: [2,5]
        },{
            name: 'База клиентов',
            href: '#clientDatabase',
            icon: 'icon-vcard',
            permission: [1,2,5]
        },{
            name: 'Моя статистика',
            href: '#salesDepartment',
            icon: 'icon-graph',
            permission: [1,2,5]
        },{
            name: 'Прием баблишка',
            href: '#dashboardIncome',
            icon: 'icon-piggy-bank',
            permission: [3,5]
        }
    ],
	
	user: {
		JWT : null,
		permissions : null
	},

	// system preferences
	BackboneViews		: {},
	templates 			: {},

	cookie 				: new Cookie(),
	beautify			: new Beautify(),
	dateFormat 			: new DateParser(),

	api					: {
		request: function(method, params, callback){
						
			if(appData.user.JWT || method == 'login/login'){
				$.ajax({
					url: 'http://toster.tech/server/'+method,
					// url: '/server/'+method,
					data: params,
					type: 'POST',
					beforeSend: function(request){
					    request.setRequestHeader('X-Authorization', 'Bearer ' + appData.user.JWT);
					},
					success: function(resp) {

						callback(resp, true);
					},
					error: function(errText) {
						if(errText.responseText){
							var data = JSON.parse(errText.responseText);

							// detect login expired
							if(data.action == 'relogin'){
								appData.user.JWT = null;
								localStorage.removeItem('somethingWierd');
								window.location.hash = '#login';
							}
						}
						callback(errText, false);
					}
	        	});
			} else {
				window.location.hash = '#login';
			}
		},

		simulateRequest: function(method, params, callback){
			callback(true, true);
		}
	},

	// workers
};

$(document).ready(function(){
	liveEvents();

	appData.user.JWT = localStorage.getItem("somethingWierd");
	appData.user.data = JSON.parse(localStorage.getItem("PCRMUserData"));

	// set DOM-dependend workers 
	appData.router 		= new Router();


	// init navbar
	if(appData.user.data){
		renderNavbar();
	}
});

function renderNavbar(){
	$.getScript('/modules/navbar/navbar.js', function(){
		appData.BackboneViews['Navbar'].render();
	});
}

function Router(){
	// router constructor
	var self				= this;
		self.currPage		= location.hash,
		this.subpageParams 	= null,
		self.pageHistory	= [],
		self.loading		= true,
		self.renderPageTo	= document.getElementById('wrapper'),
		self.pageTransition = 'fade';
		self.blockUIWrapper = new BlockUI('#wrapper');

	// custom event for page loaded behavior
	$(document).bind("pageRendered", function(e){
		function destroyMainPreloader(){
			if($('.mainLoader .loadCircle').css('animation-play-state') == 'running'){
				setTimeout(mainFunc, 300);
			} else {
				mainFunc();
			}

			function mainFunc(){
				$('.mainLoader').addClass('hidden').one('transitionend', function(){
					$(this).addClass('nodisplay');
				});
			}
		};

		destroyMainPreloader();
		
		$(".page .styled, .page .multiselect-container input").each(function(){
			$(this).uniform({
		        radioClass: 'choice',
		        checkboxClass: 'checker',
		        wrapperClass: $(this).attr('data-uniform-wrapper')
		    });
		});
		$('[data-popup="tooltip"]').tooltip();
		$("[data-mask]").each(function(){
			$(this).mask($(this).attr('data-mask'));
		});
	});

	// functions:
	self.loadPage = function(name){
		$('#wrapper').append('<div class="page" id="'+name+'"></div>');
		self.renderPageTo = document.getElementById(name);
		self.blockUIWrapper.block();

		
		$.getScript('/pages/'+name+'/'+name+'.js').done(function(){
			appData.BackboneViews[name.charAt(0).toUpperCase() + name.slice(1)].render();
			self.afterLoad(name);
		}).fail(function(){
			window.location.hash = '#notFound';
		});
	}

	self.afterLoad = function(name){
		// set page transition type

		if(self.pageTransition){
			var pages 		= $('.page'),
				showPage	= $(pages.filter('#'+name).get(0)),
				hidePage 	= showPage.siblings('.page');

			if(pages.length < 2){
				animationOver();
			}

			if(self.pageTransition == 'slideTop'){
				hidePage.addClass('topOverScreen').one('transitionend', function(){
					setTimeout(function(){
						hidePage.remove();
						animationOver();
					}, 1000)
				});
			} else if(self.pageTransition == 'fade'){
				hidePage.one('transitionend', function(){
					hidePage.remove();
					animationOver();
				}).addClass('fade')
			}

			self.pageTransition = 'fade';
		}

		function animationOver(){
			self.loading = false;
			self.blockUIWrapper.unblock();
		}

		// weak point, remake to update only if nessesary
		if(name == 'login' || name == 'validateSMS'){
			$.get('/templates/login_header.html', function(html){
				$('header').empty().append(html).show();
			})
		} else if(name == 'tutorial') {
			$.get('/templates/header.html', function(html){
				$('header').empty().append(html).show();
			})
		}
	};

	self.getLocation = function(){
		locationName		= location.hash.replace('#', '');
		testSubpage 		= locationName.split('/');

		if(testSubpage.length > 1){
			locationName = testSubpage[0];
			self.subpageParams = testSubpage;
		} else {
			self.subpageParams = null;
		}

		return locationName;
	};

	// onInit
	window.onhashchange = function(){
		// page changes here
		var locationName 		= self.getLocation(),
			noLoginRequired 	= ['login', 'validateSMS', 'resetPassword']; // pages, that don't require login

		self.loading = true;

		// check rights to view over here
		if(!locationName){ //empty location tail
			if(appData.user.JWT){ //if user is authed
				locationName = appData.user.data.dashboard;
			} else {
				location.hash = '#login';
				return false;
			}
		} else if(locationName == 'login' && appData.user.JWT){ //redirect from login to dashboard
			location.hash = '';
			return false;
		} else if(!locationName in noLoginRequired && !appData.user.JWT){ //if page view requires login
			location.hash = '#login';
			return false;
		}

		// check for permission to display this page
		var pageObj = appData.pages.searchByKeyValue('href', '#'+locationName)[0];
		if(appData.user.data && pageObj && pageObj.permission.indexOf(appData.user.data.type) == -1){
			window.location.hash = '#notFound';
			return false;
		}

		if(self.pageHistory[self.pageHistory.length-1] != locationName){
			// adds previous page to history
			self.pageHistory.push(locationName);
		}

		self.currPage = locationName;
		self.loadPage(locationName);
	};

	$(window).trigger('hashchange');
}

function Validator(form){
	this.form = $(form);
};

Validator.prototype.validate = function(){
	var form		= this.form,
		required	= form.find('[required]');

	form.find('.incorrect').removeClass('incorrect');
	required.each(function(){
		if(!$(this).val()){
			$(this).addClass('incorrect');
		}
	});

	return form.find('.incorrect').length < 1;
};

function Beautify(){}

Beautify.prototype.phone = function(val){
	return val.replace(/(?:([\d]{1,}?))??(?:([\d]{1,3}?))??(?:([\d]{1,3}?))??(?:([\d]{2}))??([\d]{2})$/, function( all, a, b, c, d, e, f, stringy){
			var string = '';
			if(stringy.charAt(0) == '7'){
				string += '+'+a
			}
			string += '('+b+')'+c+'-'+d+'-'+e;
			return string;
        });
}

function getPlugin(plugin, callback){
	if(!plugin.loaded){
        $.getScript(plugin.path, callback);
    } else {
        callback();
    }
}

function getStyle(path){
	$("<link/>", {
		rel: "stylesheet",
		type: "text/css",
		href: path
	}).appendTo("head");
}

function getTemplate(name, callback){
	if(!appData.templates[name]){
		$.get("/templates/"+name+".html", function(template){
			appData.templates[name] = template;
			callback(appData.templates[name]);
        });
	} else{
		callback(appData.templates[name]);
	}
}

function getModule(name, callback){
	$.get("/modules/"+name+"/"+name+".js", function(module){
		callback();
    });
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
		data = this.basic(date);
		return {	
	  		day: data.day,
	  		month: data.monthName,
	  		year: data.year,
	  		hours: data.hours,
	  		minutes: data.minutes
	  	}
	}

	this.formatClientString = function(date){
		data = this.basic(date);
		return data.day + ' ' + data.monthName + ' ' + data.year + ' ' + data.hours+':'+data.minutes;
	}

	this.formatServer = function(date){
		data = this.basic(date);

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

function Cookie(){
	this.get = function(name){
		var matches = document.cookie.match(new RegExp(
			"(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
		));
		return matches ? decodeURIComponent(matches[1]) : undefined;
	}, 

	this.set = function(name, value, options){
		if(name && value){
			options = options || {};

			var expires = options.expires;

			if (typeof expires == "number" && expires) {
				var d = new Date();
				d.setTime(d.getTime() + expires * 1000);
				expires = options.expires = d;
			}
			if (expires && expires.toUTCString) {
				options.expires = expires.toUTCString();
			}

			value = encodeURIComponent(value);

			var updatedCookie = name + "=" + value;

			for (var propName in options) {
				updatedCookie += "; " + propName;
				var propValue = options[propName];
				if (propValue !== true) {
					updatedCookie += "=" + propValue;
				}
			}

			document.cookie = updatedCookie;
		} else {
			throw 'Cookie set function: name and value params are required'
		}

		return updatedCookie;
	},

	this.delete = function(name){
		cookie.setCookie(name, "", {
			expires: -1
		});
	}
};

function liveEvents(){
	var numArr = [46,8,9,27,13,190],
		baseNumCondition = function(event){
			return event.ctrlKey === true || $.inArray(event.keyCode, numArr) !== -1 || (event.keyCode == 65 && event.ctrlKey === true) || (event.keyCode >= 35 && event.keyCode <= 40);
		},
		otherNumCondition = function(event){
			return event.shiftKey || (event.keyCode < 48 || event.keyCode > 57) && (event.keyCode < 96 || event.keyCode > 105 );
		};

	$(document).on('keyup', '.form_row.placeholderInside input', function(event) {
		if(this.value){
			$(this).addClass('noShowdown');
		} else {
			$(this).removeClass('noShowdown');
		}
	});

	$(document).on('keydown', 'input[type=number]', function(event) {
        if(baseNumCondition(event)){
			return;
        } else if(otherNumCondition(event)){
            event.preventDefault();
        }
    });

    $(document).on('keydown', 'input[type=tel]', function(event) {
		if(baseNumCondition(event) || (event.shiftKey && event.keyCode == 187)) {
			return;
        } else if(otherNumCondition(event)) {
            event.preventDefault();
        }
    });

    window.addEventListener('offline', changeState);
    window.addEventListener('online', changeState);

    function changeState(e){
    	if(e.type == 'offline'){
    		$('#network-online').addClass('hidden');
    		$('#network-offline').removeClass('hidden');
    	} else {
    		$('#network-online').removeClass('hidden');
    		$('#network-offline').addClass('hidden');
    	}
    }
}

function listenToServer(){
	var eventSource = new EventSource('/server/sse/listen');

	eventSource.onopen = function(e) {
	  console.log("Открыто соединение");
	};

	eventSource.addEventListener('event_name',function(e){
		var data = e.data;
		console.log('custom reciever', data);
		//handle your data here
	},false);

	eventSource.onerror = function(e) {
	  if (this.readyState == EventSource.CONNECTING) {
	    console.log("Ошибка соединения, переподключение");
	  } else {
	    console.log("Состояние ошибки: " + this.readyState);
	  }
	};

	eventSource.onmessage = function(e) {
	  console.log("Сообщение: " + e.data);
	};
}

function progressCounter(element, radius, border, color, end, iconClass, textTitle, textAverage) {


        // Basic setup
        // ------------------------------

        // Main variables
        var d3Container = d3.select(element),
            startPercent = 0,
            iconSize = 32,
            endPercent = end,
            twoPi = Math.PI * 2,
            formatPercent = d3.format('.0%'),
            boxSize = radius * 2;

        // Values count
        var count = Math.abs((endPercent - startPercent) / 0.01);

        // Values step
        var step = endPercent < startPercent ? -0.01 : 0.01;



        // Create chart
        // ------------------------------

        // Add SVG element
        var container = d3Container.append('svg');

        // Add SVG group
        var svg = container
            .attr('width', boxSize)
            .attr('height', boxSize)
            .append('g')
                .attr('transform', 'translate(' + (boxSize / 2) + ',' + (boxSize / 2) + ')');



        // Construct chart layout
        // ------------------------------

        // Arc
        var arc = d3.svg.arc()
            .startAngle(0)
            .innerRadius(radius)
            .outerRadius(radius - border);



        //
        // Append chart elements
        //

        // Paths
        // ------------------------------

        // Background path
        svg.append('path')
            .attr('class', 'd3-progress-background')
            .attr('d', arc.endAngle(twoPi))
            .style('fill', '#eee');

        // Foreground path
        var foreground = svg.append('path')
            .attr('class', 'd3-progress-foreground')
            .attr('filter', 'url(#blur)')
            .style('fill', color)
            .style('stroke', color);

        // Front path
        var front = svg.append('path')
            .attr('class', 'd3-progress-front')
            .style('fill', color)
            .style('fill-opacity', 1);



        // Text
        // ------------------------------

        // Percentage text value
        var numberText = d3.select(element)
            .append('h2')
                .attr('class', 'mt-15 mb-5')

        // Icon
        d3.select(element)
            .append("i")
                .attr("class", iconClass + " counter-icon")
                .attr('style', 'top: ' + ((boxSize - iconSize) / 2) + 'px');

        // Title
        d3.select(element)
            .append('div')
                .text(textTitle);

        // Subtitle
        d3.select(element)
            .append('div')
                .attr('class', 'text-size-small text-muted')
                .text(textAverage);



        // Animation
        // ------------------------------

        // Animate path
        function updateProgress(progress) {
            foreground.attr('d', arc.endAngle(twoPi * progress));
            front.attr('d', arc.endAngle(twoPi * progress));
            numberText.text(formatPercent(progress));
        }

        // Animate text
        var progress = startPercent;
        (function loops() {
            updateProgress(progress);
            if (count > 0) {
                count--;
                progress += step;
                setTimeout(loops, 10);
            }
        })();
    }

function InfiniteScroll(elem, options, callback) {
	this.elem 		= $(elem);
	this.elemNode	= this.elem[0];
	this.busy 		= false;
	this.from 		= options.from ? options.from : 0;
	this.to 		= options.to ? options.to : 20;
	this.blockUI 	= new BlockUI(this.elem, {
		light: true
	});

	var _self = this;

	this.requestData = function(){
		if(!this.busy){
			this.setNextStep();

			var requestOptions = $.extend(true, {
		            limitFrom: this.from,
		            limitTo: this.to
		        }, options.requestOptions);

			this.busy = true;
			this.blockUI.block();
			appData.api.request(options.request, requestOptions, function(resp, success){
				 // data loaded, load template for parsing the data
				_self.busy = false;
				callback(resp);
				_self.blockUI.unblock();
				if(resp.length == 0){
					_self.destroy();
				}
	        });
		}
	};

	this.setNextStep = function(){
		this.from += this.to;
	}

	this.getElemScrollTop = function(){
		return this.elemNode.scrollTop;
	}

	this.destroy = function(){
		elem.off('scroll', scrollSpy);
		elem.off('mouseover', offDocumentScroll);
		elem.off('mouseout', onDocumentScroll);

		this.blockUI.unblock();

		onDocumentScroll();
	}

	elem.on('scroll', scrollSpy);
	elem.on('mouseover', offDocumentScroll);
	elem.on('mouseout', onDocumentScroll);

	function offDocumentScroll(){document.body.style.overflow='hidden'};
	function onDocumentScroll(){document.body.style.overflow=''};

	function scrollSpy(){
		var rule = options.reverse === true ? this.scrollTop == 0 : this.scrollTop + this.clientHeight >= this.scrollHeight;
		if (rule) {
			_self.requestData();
		}
	}
}

function BlockUI(elem, options){
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

function Calendarity(elem, options){
	// settings down here
	// params from external
	this.calendarNode 		= $(elem);
	this.period 			= options.period,
	this.clientPackageId	= options.clientPackageId,
	this.resp 				= options.resp,
	this.limitDelivery 		= options.greens ? options.greens : 0;
	this.deliveryMax 		= options.deliveryMax ? options.deliveryMax : 0;

	// dates
	this.date 					= new Date();
	this.today 					= new Date(),
	this.todayMonth				= new Date(this.today.getFullYear(), this.today.getMonth(), 0);

	// dictionaries
	this.dayTypes 		= ['<i class="icon-checkmark-circle2 text-teal js-deliveryDay"></i>', '<i class="icon-snowflake text-blue js-freezeDay"></i>', '<i class="icon-fire text-warning-400 js-burnDay"></i>']
	this.monthNames  	= ["Январь", "Февраль", "Март",
		  "Апрель", "Май", "Июнь", "Июль",
		  "Август", "Сентябрь", "Октябрь",
		  "Ноябрь", "Декабрь"
	];

	// inner variables
	this.dragStart		= 0;
	this.dragEnd		= 0;
	this.isDragging		= false;
	this.allCells		= null;
	this.greensCounter 	= null;
	this.tooltip 		= null;
	this.monthObj 		= null;

	var _self = this;


	this.init = function(){
		var html = $('<div class="calendarity"></div>')
			html.append('<div class="calendarity-head"></div><table class="calendarity-table"><thead>'
				+'<th>Пн</th>'
				+'<th>Вт</th>'
				+'<th>Ср</th>'
				+'<th>Чт</th>'
				+'<th>Пт</th>'
				+'<th>Сб</th>'
				+'<th>Вс</th>'
			+'</thead><tbody></tbody></table>');

		_self.calendarNode.append(html);
		_self.calendarNode.append('<div class="js-calendarTooltip"><button type="button" class="btn bg-success"><i class="icon-checkmark-circle2"></i></button><button type="button" class="btn btn-primary"><i class="icon-snowflake"></i></button><button type="button" class="btn btn-warning"><i class="icon-fire"></i></button></div>')
		_self.tooltip 	= _self.calendarNode.find('.js-calendarTooltip');

		//build grid
		_self.buildGrid();

		// init Events
		_self.calendarNode.find('.js-calendarTooltip button').bind('click',_self.setSelectedRange);
	};

	this.buildMonthObject = function(){
		var date 			= _self.date,

			firstDay 		= new Date(date.getFullYear(), date.getMonth(), 1),
			lastDay 		= new Date(date.getFullYear(), date.getMonth() + 1, 0),

			thisMonthResp	= _self.resp ? _self.resp : [],

			respIndexArr 	= [],
			result 			= [];

			// если данные о месяце пришли с сервака, то вхерачить их 
			if(thisMonthResp){
				for(var i=0, len=thisMonthResp.length; i<len; i++){
					respIndexArr[new Date(thisMonthResp[i].deliveryDate).getDate()] = thisMonthResp[i]
				}
			}

			// loop through month days
			for(var i=1, len=lastDay.getDate()+1; i<len; i++){
				var dayInResponce 			= respIndexArr[i],
					day 					= new Date(date.getFullYear(), date.getMonth(), i);

					result.push({
						date 	: day,
						isPast 	: day < _self.today,
						state 	: dayInResponce ? dayInResponce.state : null
					})
			}

			return result;
	};

	this.buildGrid = function(){
		_self.monthObj = _self.buildMonthObject();
		_self.buildHead(_self.date);
		_self.buildMonth(_self.date);
	}

	this.buildHead = function(date){
		var greenCellsAvailable = _self.limitDelivery - _self.monthObj.searchByKeyValue('state', 1).length;
		console.log(greenCellsAvailable);

		var date 	= new Date(date),
			node 	= '<div class="panel-heading text-left no-padding-left"><div class="btn btn-default disabled heading-btn"><i class="icon-checkmark-circle2 text-teal"></i><span class="js-calendaity_deliveryDaysLeft heading-btn" style="display:inline-block;margin-left:12px;">'+greenCellsAvailable+'</span></div><div class="heading-elements" style="right:0;">'
						+'<button class="btn btn-primary js-calendarity-buildPrev pull-left heading-btn"><i class="glyphicon glyphicon-chevron-left"></i></button><div class="btn btn-default pull-left heading-btn">'+_self.monthNames[date.getMonth()]+' '+date.getFullYear()
						+'</div><button class="btn btn-primary js-calendarity-buildNext pull-left heading-btn"><i class="glyphicon glyphicon-chevron-right"></i></button></div></div>';

		_self.calendarNode.find('.calendarity-head').html(node);
		_self.greensCounter = _self.calendarNode.find('.js-calendaity_deliveryDaysLeft');

		// set header events
		_self.calendarNode.find('.js-calendarity-buildNext').bind('click', buildNextPrev);
		_self.calendarNode.find('.js-calendarity-buildPrev').bind('click', buildNextPrev);

		function buildNextPrev(evt){
			var isNext = $(evt.currentTarget).hasClass('js-calendarity-buildNext');

			_self.date = new Date(_self.date.getFullYear(), isNext ? _self.date.getMonth()+1 : _self.date.getMonth()-1, 1);
			_self.update(function(){
				_self.buildGrid()
			});
		}
	};

	this.buildMonth = function(date){
		var firstDay 		= new Date(date.getFullYear(), date.getMonth(), 1),
			lastDay 		= new Date(date.getFullYear(), date.getMonth() + 1, 0),
			node 			= '<tr>';

		// add empty tds on missing dates
		var len = firstDay.getDay() == 0 ? 6 : firstDay.getDay()-1
		for(var i=0; i<len; i++){
			node += '<td class="unselectable"></td>';
		}

		// loop through month days
		_self.monthObj.forEach(function(item, index){
			index++;

			var classes = '',
				dayType = '';

			if(_self.dayTypes[item.state]){
				dayType = _self.dayTypes[item.state];
			}

			if(item.isPast){
				classes+='past';
			}

			node += '<td class="'+classes+'"><div class="date">'+index+'</date><div class="underground">'+dayType+'</div></td>';

			if(item.date.getDay() == 0 && index != _self.monthObj.length){
				node += '</tr></tr>'
			} else if(i == _self.monthObj.length){
				node += '</tr>'
			}
		});

		if(lastDay.getDay() != 0){
			for(var i=0, len=7-lastDay.getDay(); i<len; i++){
				node += '<td class="unselectable"></td>';
			}
		}

		_self.calendarNode.find('tbody').html(node);

		// init events
		_self.allCells 	= _self.calendarNode.find('td:not(.unselectable)');

		_self.allCells.mousedown(_self.rangeMouseDown)
        _self.allCells.mouseup(_self.rangeMouseUp)
        _self.allCells.mousemove(_self.rangeMouseMove);
	};

	this.selectRange = function() {
	     if(_self.todayMonth <= _self.date){ // allow modifications in future only

	     	var filtered = _self.allCells.filter(':not(.past)');
	     	filtered.removeClass('selected');

	        if (_self.dragEnd + 1 < _self.dragStart) {
				filtered.slice(_self.dragEnd, _self.dragStart + 1).addClass('selected');
	        } else {
				filtered.slice(_self.dragStart, _self.dragEnd + 1).addClass('selected');
	        }
	    }
    };

    this.isRightClick = function(e) {
        if (e.which) {
            return (e.which == 3);
        } else if (e.button) {
            return (e.button == 2);
        }
        return false;
    };

    this.rangeMouseDown = function(e) {
        if (_self.isRightClick(e)) {
            return false;
        } else {
        	var filtered = _self.allCells.filter(':not(.past)');

        	if(filtered.length){
	            _self.dragStart = filtered.index($(this));
	            _self.isDragging = true;

	            if (typeof e.preventDefault != 'undefined') { e.preventDefault(); } 
	            document.documentElement.onselectstart = function () { return false; };
	        }
        } 
    };

	this.rangeMouseUp = function(e) {
        if (_self.isRightClick(e)) {
            return false;
        } else {
        	var filtered = _self.allCells.filter(':not(.past)');

        	if(filtered.length){
	            _self.dragEnd = filtered.index($(this));

	            _self.isDragging = false;
	            if (_self.dragEnd != -1) {
	                _self.selectRange();
	                _self.showTooltip(_self.allCells.filter('.selected:last'));
	            }

	            document.documentElement.onselectstart = function () { return true; }; 
	        }
        }
    };

    this.rangeMouseMove = function(e) {
        if (_self.isDragging) {
        	var filtered = _self.allCells.filter(':not(.past)');
        	if(filtered.length){
	            _self.dragEnd = filtered.index($(this));
	            _self.selectRange();
	        }
        }            
    };

    this.showTooltip = function(cell){
    	if(cell.length){
	    	_self.tooltip.addClass('show').css({
	    		top: cell.position().top,
	    		left: cell.position().left + cell.width()
	    	});

	    	$(document).bind('click', _self.outerClick);
    	}
    };

    this.outerClick = function(evt){
    	var target = $(evt.target);

    	if(!target.hasClass('calendarity') && target.closest('.calendarity').length < 1 
    		&& !target.hasClass('js-calendarTooltip') && target.closest('.js-calendarTooltip').length < 1 ){
    		_self.tooltip.removeClass('show');
    		_self.allCells.filter('.selected').removeClass('selected');
    		$(this).unbind(evt);
    	}
    };

    this.setSelectedRange = function(evt){
    	var btnIndex = $(this).index();

    	var selectedCells  = _self.allCells.filter('.selected');
    	var override = NaN;
    	var greenCells = _self.monthObj.searchByKeyValue('state', 0).length;

    	var alreadySetCellsInSelection = selectedCells.filter(function(){
    		if(($(this).find('.js-deliveryDay').length > 0 && btnIndex == 0) ||
    			($(this).find('.js-freezeDay').length > 0 && btnIndex == 1) ||
    			($(this).find('.js-burnDay').length > 0 && btnIndex == 2)) {
    			return true;
    		}
    	});

    	if(alreadySetCellsInSelection.length == selectedCells.length){
    		// all selected cells are same type and similar type button has been pressed
    		// let's unselect them

    		selectedCells.each(function(index, item){
    			var thisDay = parseInt($(this).find('.date').text()) - 1;

	    		$(this).find('.underground').html('');
	    		_self.monthObj[thisDay].state = null;
	    	});
    	} else {
    		var sublimation = _self.period == 2 ? Math.round(selectedCells.length / 2) : selectedCells.length;

	    	if(btnIndex == 0 && (sublimation > _self.limitDelivery || greenCells <= _self.deliveryMax)){
	    		// пытаемся проставить доставку
	    		// количество дней превышает лимит
	    		selectedCells = selectedCells.slice(0, _self.period == 2 ? _self.limitDelivery / 2 - greenCells : _self.limitDelivery - greenCells);
	    	}

	    	// check for value override
	    	$.each(selectedCells, function(){
	    		if($(this).find('.underground').children().length > 0){
	    			if(isNaN(override)){
	    				override = confirm('Опаньки, на этот день уже проставлен статус, перезаписать?');
	    				return override;
	    			}
	    		}
	    	});

	    	selectedCells.each(function(index, item){
	    		var thisDay = parseInt($(this).find('.date').text())-1;
	    		if(override || isNaN(override)){
	    			if(_self.period == 2){
	    				if(thisDay % 2 == 0){
	    					pushDay(thisDay, this);
	    				}
	    			} else if(_self.period == 1) {
	    				pushDay(thisDay, this);
	    			}
	    		}
	    	});

	    	function pushDay(thisDay, elem){
				_self.monthObj[thisDay].state = btnIndex;
	    		$(elem).find('.underground').html(_self.dayTypes[btnIndex]);
	    	}
	    }

	    _self.setDeliveries(selectedCells, btnIndex);
	    _self.greensCounter.text(_self.deliveryMax - _self.monthObj.searchByKeyValue('state', 0).length);
    };

    this.setDeliveries = function(cells, btn){
    	var first = appData.dateFormat.formatServer(new Date(_self.date).setDate(parseInt(cells.filter(':first').text()))),
    		last = appData.dateFormat.formatServer(new Date(_self.date).setDate(parseInt(cells.filter(':last').text())));

    	if(first.search('NaN') == -1 && last.search('NaN') == -1){
	    	appData.api.request('clients/setDeliveries', {
			    'clientPackageId' 	: _self.clientPackageId,
			    'from' 				: first,
			    'to' 				: last,
			    'state' 			: btn,
			    // 'addressId' 		: 1,
			    // 'timeId' 			: 1
			}, function (resp) {
			    console.log(resp)
			});
	    }
    };

    this.update = function(callback){
    	var _self = this;

    	var date 		= new Date(_self.date), y = date.getFullYear(), m = date.getMonth(),
    		firstDay 	= new Date(y, m, 1),
    		lastDay 	= new Date(y, m + 1, 0);

    	var to 		= appData.dateFormat.formatServer(lastDay),
    		from 	= appData.dateFormat.formatServer(firstDay);

    	appData.api.request('clients/getDeliveries', {
    		'clientPackageId' 	: _self.clientPackageId,
    		'dateFrom' 			: from,
    		'dateTo' 			: to
    	}, function(resp){
    		_self.resp = resp;
    		callback();
    	});
    };

    this.rerender = function(){
    	console.log('rerender me pls');
    	_self.calendarNode.empty();
    	_self.init();
    };

	this.init();
}

Array.prototype.searchByKeyValue = function(key, value){
	var result = [];

	for (var i=0; i < this.length; i++) {
        if (this[i][key] == value) {
            result.push(this[i]);
        }
    }

    return result;
}

Image.prototype.blobToImage = function(blob, callback){
	var reader = new window.FileReader();
		
	reader.readAsDataURL(blob);
	reader.onloadend = function() {
		base64data = reader.result;                
		console.log(base64data);
	}
};

function serializeForm(form) {
	var formArray 	= $(form).serializeArray(),
		returnArray = {};

	for (var i = 0; i < formArray.length; i++){
		returnArray[formArray[i]['name']] = formArray[i]['value'];
	}
	return returnArray;
}