<?php

/**
 * Class Demac_MultiLocationInventory_Helper_Data
 */
class Demac_MultiLocationInventory_Helper_Data extends Mage_Core_Helper_Abstract
{
    const CONFIG_PATH = 'demac_multilocationinventory/general/';

    /**
     * Get regions in a country.
     *
     * @param $countryCode
     *
     * @return array
     */
    public function getRegions($countryCode)
    {
        $options            = array();
        $notAvailableOption = array(
            'value' => 'N/A',
            'label' => 'N/A'
        );

        if($countryCode != '') {
            $regionArray = Mage::getModel('directory/region')
                ->getResourceCollection()
                ->addCountryFilter($countryCode);
            foreach ($regionArray as $region) {
                if($region->getCode() == '') {
                    $region[] = $notAvailableOption;
                } else {
                    $options[] = array(
                        'value' => $region->getCode(),
                        'label' => $region->getDefaultName()
                    );
                }
            }
        } else {
            $options[] = $notAvailableOption;
        }

        return $options;

    }

    /**
     * Get latitude and longitude of an address and country.
     *
     * Uses Demac_Geocoding library if it is available.
     *
     * @param $postalCode
     * @param $country
     *
     * @return array
     *
     * @TODO Move to the location helper.
     * @TODO Isolate curl functionality so it is less exposed. (maybe use Zend_Http as part of this)
     * @TODO Come up with a better solution to sleep(1), at the very least sleep in microseconds.
     * @TODO if we keep sleep related functionality add comments explaining that it is for the google api timeouts.
     */
    public function getLatLong($postalCode, $country)
    {
        sleep(1);
        if($this->geocodingAvailable()) {
            $coordinates = Mage::getModel('geocoding/geocode')->loadByPostalCode($postalCode, $country);

            return array($coordinates['latitude'], $coordinates['longitude']);
        } else {
            $postalCode = urlencode($postalCode);
            $country    = urlencode($country);
            $url        = "http://maps.google.com/maps/api/geocode/json?address=$postalCode&sensor=false&region=$country";
            $ch         = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            $response = curl_exec($ch);
            curl_close($ch);
            $response_a = json_decode($response);

            $lat  = $response_a->results[0]->geometry->location->lat;
            $long = $response_a->results[0]->geometry->location->lng;

            return array($lat, $long);
        }
    }

    /**
     * Checks if the Demac_Geocoding extension is available (recommended).
     *
     * @returns Boolean
     * @TODO Move this to the Location helper.
     */
    public function geocodingAvailable()
    {
        return (bool) Mage::getModel('geocoding/geocode');
    }


    /**
     * @param      $field
     * @param null $storeId
     *
     * @return mixed
     */
    public function getConfigData($field, $storeId = null)
    {
        // Start Refactor : Add a class constant for the path
        $path = self::CONFIG_PATH . $field;

        // End Refactor
        return Mage::getStoreConfig($path, $storeId);
    }
}
