// функциональность попапа добавить адрес
var client_addAddressPopupComponent = Backbone.View.extend({
    el: '#client-modalAddAddress',

    events: {
        'click .js-addAddressPopup' : 'displayMapSearch',
        'click #addAddress'         : 'addAddress'
    },

    initialize: function(options) {
        this.data.clientId = options.clientId;
        this.parent        = options.parent;
    },

    data: {
        latitude    : null,
        longitude   : null,
        city        : null,
        street      : null,
        building    : null
    },

    render: function(){
        var _self = this;

        $.getScript('https://maps.google.com/maps/api/js?sensor=false&key=AIzaSyATb847_GlZ9_iTf-bp8BeojGx5t-ugHWI&libraries=places', mappy);

        function mappy(){
            if($('#client-addAddressMap').length > 0 ) {
                // параметры карты
                var myOptions = { 
                    zoom: 11,
                    center: new google.maps.LatLng(55.75393030000001,37.620795000000044),
                    mapTypeId: google.maps.MapTypeId.ROADMAP,
                    disableDefaultUI:true,
                    panControl: false,
                    mapTypeControl: false,
                    streetViewControl: false,
                    zoomControl: true,
                    zoomControlOptions: {
                        style: google.maps.ZoomControlStyle.SMALL
                    },
                };
                
                // инициализируем карты и автокомплит
                var map             = new google.maps.Map(document.getElementById("client-addAddressMap"), myOptions),
                    input           = document.getElementById('client-addAddressMapSearch'),
                    autocomplete    = new google.maps.places.Autocomplete((input), {
                        types: ['address'],
                        componentRestrictions: { 
                            country: "ru"            
                        }
                    });

                map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);
                autocomplete.addListener('place_changed', fillInAddress);

                var marker = null;

                function fillInAddress() {
                    // Get the place details from the autocomplete object.
                    var place       = autocomplete.getPlace();
                    var geometry    = place.geometry.location,
                        latLng      = {
                            lat: geometry.lat(), 
                            lng: geometry.lng()
                        };

                    // ставим маркер
                    if(marker){
                        marker.setMap(null);
                    }

                    marker = new google.maps.Marker({
                        map: map,
                        position: latLng
                    });

                    map.setCenter(latLng)

                    // запомним параметры широты и долготы
                    _self.data.latitude = latLng.lat;
                    _self.data.longitude = latLng.lng;

                    var elem = $('.js-addAddressPopupInfoBlock');

                    for(var i=0,len=place.address_components.length; i<len; i++){
                        var item = place.address_components[i],
                            placeToPaste = null;

                        if(findValue(item.types, "locality")){
                            // это город
                            _self.data.city = item;
                            placeToPaste = '.js-address_city';
                        } else if(findValue(item.types, "route")){
                            // это улица
                            _self.data.street = item;
                            placeToPaste = '.js-address_street';
                        } else if(findValue(item.types, "street_number")){
                            // это дом
                            _self.data.building = item;
                            placeToPaste = '.js-address_house';
                        }


                        if(typeof placeToPaste === 'string'){
                            $(placeToPaste).text(item.long_name);
                        }
                    }

                    function findValue(arr, val){
                        return _.findKey(arr, function(value, key) {
                            return value == val;
                        });
                    } 
                }
            }
        }
    },

    addAddress: function(evt){
        var _self   = this,
            form    = serializeForm('#addAddressForm');

        data = {
            clientId    : _self.data.clientId,
            city        : _self.data.city.long_name,
            street      : _self.data.street.long_name,
            building    : _self.data.building.long_name,
            latitude    : _self.data.latitude,
            longitude   : _self.data.longitude,
            entrance    : form.entrance,
            flat        : form.flat,
            comment     : form.comment
        }

        appData.api.request('clients/addAddress', data, function (resp) {
            new PNotify({
                title: 'Адрес добавлен',
                text: 'Его можно использовать',
                addclass: 'bg-success'
            });

            // обновить список адресов
            _self.parent.components.addressList.update();
            $(".modal.in").modal("hide");
        });
    }
});