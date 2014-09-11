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
     * @param $postalCode
     * @param $country
     *
     * @return array
     *
     * @TODO Move to the location helper.
     * @TODO Isolate curl functionality so it is less exposed. (maybe use Zend_Http as part of this)
     * @TODO Come up with a better solution to sleep(1), at the very least sleep in microseconds.
     */
    public function getLatLong($postalCode, $country)
    {
        //This is used to avoid being rate limited by the google maps api.
        sleep(1);
        $postalCode = urlencode($postalCode);
        $country    = urlencode($country);
        $url        = "http://maps.google.com/maps/api/geocode/json?address=$postalCode&sensor=false&region=$country";
        try {
            $ch         = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            $response = curl_exec($ch);
            curl_close($ch);
            $response_a = json_decode($response);
            $lat  = $response_a->results[0]->geometry->location->lat;
            $long = $response_a->results[0]->geometry->location->lng;
            return array($lat, $long);
        } catch (Exception $e) {
            //@TODO return false and methods calling this to accept false as a response
            return array(0, 0);
        }
    }


    /**
     * @param      $field
     * @param null $storeId
     *
     * @return mixed
     */
    public function getConfigData($field, $storeId = null)
    {
        $path = self::CONFIG_PATH . $field;
        return Mage::getStoreConfig($path, $storeId);
    }
}
