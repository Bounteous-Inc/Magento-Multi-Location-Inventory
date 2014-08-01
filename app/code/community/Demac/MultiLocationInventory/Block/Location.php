<?php

/**
 * Class Demac_MultiLocationInventory_Block_Location
 */
class Demac_MultiLocationInventory_Block_Location extends Mage_Core_Block_Template
{
    /**
     * Get default marker image.
     *
     * @return string
     */
    public function getDefaultMarker()
    {
        $defaultMarker = '';
        if(!is_null(Mage::getStoreConfig('demac_multilocationinventory/general/mapicon')) && Mage::getStoreConfig('demac_multilocationinventory/general/mapicon') != '') {
            $defaultMarker = 'multilocationinventory/markers/' . Mage::getStoreConfig('demac_multilocationinventory/general/mapicon');
        }

        return $defaultMarker;
    }

    /**
     * Get all locations
     *
     * @return mixed
     */
    public function getStores()
    {
        $locations = Mage::getModel('demac_multilocationinventory/location')->getCollection()
            ->addFieldToFilter('status', 1)
            ->addLocatorStoreFilter($this->getCurrentStore())
            ->addFieldToSelect('*');

        foreach ($locations as $location) {
            if(!is_null($location->getCountryId())) {
                $location->setCountryId($this->getCountryByCode($location->getCountryId()));
            } else {
                $location->setCountryId($this->__('NC'));
            }

            if(!is_null($location->getImage()) || $location->getImage() != '') {
                $imgUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . $location->getImage();
            } elseif(!is_null(Mage::getStoreConfig('demac_multilocationinventory/general/defaultimage')) && Mage::getStoreConfig('demac_multilocationinventory/general/defaultimage') != '') {
                $imgUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'multilocationinventory/images/' . Mage::getStoreConfig('demac_multilocationinventory/general/defaultimage');
            } else {
                $imgUrl = $this->getLogoSrc();
            }
            $location->setImage($imgUrl);
        }

        return $locations;
    }

    /**
     * Get URL for Google Geocoding API
     *
     * @return string
     */
    public function getGoogleApiUrl()
    {
        $apiUrl = Mage::getStoreConfig('demac_multilocationinventory/general/apiurl');
        if(is_null($apiUrl))
            $apiUrl = "http://maps.googleapis.com/maps/api/js?v=3";
        $apiKey       = "&key=" . Mage::getStoreConfig('demac_multilocationinventory/general/apikey');
        $apiSensor    = Mage::getStoreConfig('demac_multilocationinventory/general/apisensor');
        $sensor       = ($apiSensor == 0) ? 'false' : 'true';
        $urlGoogleApi = $apiUrl . "&sensor=" . $sensor . $apiKey . "&callback=initialize&libraries=places";

        return $urlGoogleApi;
    }

    /**
     * retrieve current store
     *
     * @return Mage_Core_Model_Store
     */
    public function getCurrentStore()
    {
        return Mage::app()->getStore()->getId();
    }

    /**
     * Load country by code
     *
     * @param $code
     *
     * @return Mage_Directory_Model_Country
     */
    public function getCountryByCode($code)
    {
        return Mage::getModel('directory/country')->loadByCode($code)->getName();
    }

    /**
     * Get logo.
     *
     * @return string
     */
    public function getLogoSrc()
    {
        if(empty($this->_data['logo_src'])) {
            $this->_data['logo_src'] = Mage::getStoreConfig('design/header/logo_src');
        }

        return $this->getSkinUrl($this->_data['logo_src']);
    }
}

