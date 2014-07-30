<?php

/**
 * Class Demac_MultiLocationInventory_Block_Adminhtml_Location_Edit
 */
class Demac_MultiLocationInventory_Block_Adminhtml_Location_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    /**
     * Init class
     */
    public function __construct()
    {
        parent::__construct();
        $this->_blockGroup = 'demac_multilocationinventory';
        $this->_objectId   = 'id';
        $this->_controller = 'adminhtml_location';


        $this->_updateButton('save', 'label', Mage::helper('demac_multilocationinventory')->__('Save Location'));
        $this->_updateButton('delete', 'label', Mage::helper('demac_multilocationinventory')->__('Delete Location'));

        $this->_addButton('saveandcontinue', array(
            'label'   => Mage::helper('demac_multilocationinventory')->__('Save and Continue Edit'),
            'onclick' => 'saveAndContinueEdit()',
            'class'   => 'save',
        ), -100);

        $apiUrl    = Mage::getStoreConfig('demac_multilocationinventory/general/apiurl');
        $apiKey    = Mage::getStoreConfig('demac_multilocationinventory/general/apikey');
        $apiSensor = Mage::getStoreConfig('demac_multilocationinventory/general/apisensor');
        $sensor    = ($apiSensor == 0) ? 'false' : 'true';
        $img       = "";
        $marker    = "var marker = new google.maps.Marker({position: latLng, map: map });";
        if (!is_null(Mage::getStoreConfig('demac_multilocationinventory/general/mapicon')) && Mage::getStoreConfig('demac_multilocationinventory/general/mapicon') != '') {
            $img    = "var imgMarker =  new google.maps.MarkerImage('" . Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'multilocationinventory/markers/' . Mage::getStoreConfig('demac_multilocationinventory/general/mapicon') . "');";
            $marker = "var marker = new google.maps.Marker({position: latLng, icon: imgMarker,map: map });";
        }

        $this->_formScripts[] = "
            function loadScript(){
                var script = document.createElement('script');
                script.type = 'text/javascript';
                script.src = '" . $apiUrl . "&callback=getLatLng&sensor=" . $sensor . "&key=" . $apiKey . "';
                document.head.appendChild(script);
                var div = document.createElement('div');
                div.id='map_canvas';
                document.getElementsByClassName('hor-scroll')[0].appendChild(div);
                var img = document.createElement('img');
                document.getElementsByClassName('form-list')[0].style.float='left';
                document.getElementById('map_canvas').style.height='500px';
                document.getElementById('map_canvas').style.width='500px';
                document.getElementById('map_canvas').style.float='left';
                document.getElementById('map_canvas').style.marginLeft='30px';
                document.getElementById('map_canvas').style.marginTop='6px';
            }

            window.onload = loadScript;

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }

            function capitalize(str) {
                var pieces = str.split(' ');
                for ( var i = 0; i < pieces.length; i++ )
                {
                    var j = pieces[i].charAt(0).toUpperCase();
                    pieces[i] = j + pieces[i].substr(1);
                }
                return pieces.join(' ');
            }

            function getLatLng(){
                " . $img . "
                var geocoder = new google.maps.Geocoder();
                var address = document.getElementById('address').value;
                var city = document.getElementById('city').value;
                var country = document.getElementById('country_id').options[document.getElementById('country_id').selectedIndex].text;
                var imgStore = storeImage = '';
                if(document.getElementById('image_image')){
                    storeImage = document.getElementById('image_image').src;
                    imgStore = '<div><img src='+storeImage+' alt='+document.getElementById('name').value+' style=\'float:left;width:150px;\'/></div>';
                }
                if(document.getElementById('marker_image')){
                    storeMarker = document.getElementById('marker_image').src;
                }
                if(address != '' && city != ''){
                    var addressComplete = address + ', ' + city;
                    if(country != '') addressComplete = addressComplete + ' ' + country;
                    geocoder.geocode( { 'address': addressComplete}, function(results, status) {
                      if (status == google.maps.GeocoderStatus.OK) {
                        document.getElementById('lat').value = results[0].geometry.location.lat();
                        document.getElementById('long').value = results[0].geometry.location.lng();
                        document.getElementById('address').value = capitalize(document.getElementById('address').value);
                        document.getElementById('city').value = capitalize(document.getElementById('city').value);
                        var latLng =  new google.maps.LatLng(document.getElementById('lat').value , document.getElementById('long').value);
                        var mapOption = {zoom: 17, center: latLng, mapTypeId: google.maps.MapTypeId.ROADMAP, disableDefaultUI : true };
                        map = new google.maps.Map(document.getElementById('map_canvas'), mapOption);
                        if(document.getElementById('marker_image')){
                            storeMarker = document.getElementById('marker_image').src;
                            var marker = new google.maps.Marker({position: latLng, icon: storeMarker,map: map });
                        }else{
                        " . $marker . "
                        }
                        var infoWindow = new google.maps.InfoWindow();
                        google.maps.event.addListener(marker, 'click', (function(marker) {
                            return function() {
                                var content = 	imgStore+'<div style=\'float:left;margin-top: 10px;\' ><h3>' + document.getElementById('name').value + '</h3>'
                                + document.getElementById('address').value + '<br>'
                                + document.getElementById('zipcode').value+' '+document.getElementById('city').value +' <br>'
                                + document.getElementById('country_id').options[document.getElementById('country_id').selectedIndex].text+'<br>'
                                + document.getElementById('phone').value + '<br>'
                                + document.getElementById('fax').value + '<br>'
                                +'</div>';
                                infoWindow.setContent(content);
                                infoWindow.open(map,marker);
                            }
                        })(marker));
                      }
                    });
                }
            }

        ";
    }

    /**
     * Get Header text
     *
     * @return string
     */
    public function getHeaderText()
    {
        if (Mage::registry('multilocationinventory_data')->getId()) {
            return $this->__('Edit Location');
        } else {
            return $this->__('New Location');
        }
    }
}