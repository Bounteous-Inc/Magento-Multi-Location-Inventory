var markerVisitor = false;
function initialize() {
    bounds = new google.maps.LatLngBounds();
    var mapOption = {zoom: 17, mapTypeId: google.maps.MapTypeId.ROADMAP, disableDefaultUI: true, zoomControl: true};
    map = new google.maps.Map(document.getElementById('map_canvas'), mapOption);
    direction = new google.maps.DirectionsRenderer({
        map: map,
        panel: document.getElementById('panel'),
        suppressMarkers: true
    });
    autocomplete = new google.maps.places.Autocomplete($("address"));
    google.maps.event.addListener(autocomplete, 'place_changed', autocompleteCallback);

    infoWindow = new google.maps.InfoWindow();
    if (!setMarkerFromAddress()) {
        initGeoloc();
    }
    initStores();

    google.maps.event.addListener(map, "idle", function () {
        for (i = 0; i < stores.items.length; i++) {
            var latLng = new google.maps.LatLng(stores.items[i].lat, stores.items[i].long);
            if (map.getBounds().contains(latLng)) {
                $('store' + stores.items[i].id).up(3).show();
            }
            else {
                $('store' + stores.items[i].id).up(3).hide();
            }
        }
    });
}

function getItineraire(lat, lng) {
    var destination = new google.maps.LatLng(lat, lng);
    if (markerVisitor) {
        var origin = markerVisitor.getPosition();
        var request = {
            origin: origin,
            destination: destination,
            travelMode: google.maps.DirectionsTravelMode.DRIVING
        };

        var directionsService = new google.maps.DirectionsService();
        directionsService.route(request, function (response, status) {
            if (status == google.maps.DirectionsStatus.OK) {
                direction.setDirections(response);
            }
        });
    }
}

function getMyLatLng() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function (position) {
            var latlng = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);
        }, erreurPosition);
        return latlng;
    }
}

function loadScript() {
    var script = document.createElement("script");
    script.type = "text/javascript";
    script.src = gmapUrl;
    document.body.appendChild(script);
}

function initGeoloc() {
    if (apiSensor) {
        if (navigator.geolocation) {
            survId = navigator.geolocation.getCurrentPosition(maPosition, erreurPosition);
        }
    }
}

function maPosition(position) {
    latlng = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);
    markerPosition(latlng);
}

function erreurPosition(error) {
    var info = "Erreur lors de la géolocalisation : ";
    switch (error.code) {
        case error.TIMEOUT:
            info += "Timeout !";
            break;
        case error.PERMISSION_DENIED:
            info += "Vous n’avez pas donné la permission";
            break;
        case error.POSITION_UNAVAILABLE:
            info += "La position n’a pu être déterminée";
            break;
        case error.UNKNOWN_ERROR:
            info += "Erreur inconnue";
            break;
    }
}

function initStores() {
    markers = new Array();
    for (i = 0; i < stores.items.length; i++) {
        var latLng = new google.maps.LatLng(stores.items[i].lat, stores.items[i].long);
        bounds.extend(latLng);
        if (stores.items[i].marker) {
            var imgMarker = new google.maps.MarkerImage(pathMarker + stores.items[i].marker);
        } else {
            if (defaultMarker) {
                var imgMarker = new google.maps.MarkerImage(pathMarker + defaultMarker);
            } else {
                var imgMarker = '';
            }
        }
        markers[i] = new google.maps.Marker({position: latLng, icon: imgMarker, map: map, store: stores.items[i]});
        google.maps.event.addListener(markers[i], 'click', openWindowInfo);
        $('store' + stores.items[i].id).observe('click', openWindowInfo.bind(markers[i]));
    }
    map.fitBounds(bounds);
    map.panToBounds(bounds);
}


function openWindowInfo() {

    if (!this.store.image) {
        this.store.image = defaultImage;
    }

    var content = '<div class="store-info"><div class="store-image-div-infoWindow"><img src="' + this.store.image + '" alt="' + this.store.name + '"class="store-image-infoWindow"/></div><div class="store-name-infoWindow"><h3>' + this.store.name + '</h3>'
        + this.store.address + '<br>'
        + this.store.zipcode + ' ' + this.store.city + ' <br>' + this.store.country_id + '<br>';


    if (this.store.phone) {
        content += 'Phone : ' + this.store.phone + '<br>'
    }

    if (this.store.fax) {
        content += 'Fax : ' + this.store.fax + '<br>'
    }
    content += "</div>";
    if (this.store.description) {
        content += '<div class="store-description">' + this.store.description + '</div>';
    }

    if (markerVisitor && directionEnable) {
        content += '<span onclick="getItineraire(' + this.store.lat + ',' + this.store.long + ')" class="span-geoloc">' + estimateDirectionLabel + '</span></div></div>';
    }

    infoWindow.setContent(content);
    infoWindow.open(map, this);
}

function autocompleteCallback() {
    var place = this.getPlace();
    position = place.geometry.location;
    var latLng = new google.maps.LatLng(position.lat(), position.lng());
    markerPosition(latLng);
}

function markerPosition(latlng) {
    bounds.extend(latlng);
    if (markerVisitor) {
        markerVisitor.setPosition(latlng);
    } else {
        markerVisitor = new google.maps.Marker({
            position: latlng,
            map: map,
            title: "You are here"
        });
    }
    map.panTo(latlng);
    map.setZoom(12);
}

function setMarkerFromAddress() {
    var geocoder = new google.maps.Geocoder();
    var address = document.getElementById('address').value;
    if (address != '') {
        geocoder.geocode({ 'address': address}, function (results, status) {
            if (status == google.maps.GeocoderStatus.OK) {
                var latLng = new google.maps.LatLng(results[0].geometry.location.lat(), results[0].geometry.location.lng());
                markerPosition(latLng);
                return true;
            }
        });
        return false;
    }
    return false;
}

function saveLocation(id) {
    $('saved_location').setValue(id);
    $('closest_store_form').submit();
    return false;
}