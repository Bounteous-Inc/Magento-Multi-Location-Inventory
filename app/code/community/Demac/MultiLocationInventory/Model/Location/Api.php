<?php

/**
 * Class Demac_MultiLocationInventory_Model_Location_Api
 */
class Demac_MultiLocationInventory_Model_Location_Api extends Mage_Catalog_Model_Api_Resource
{
    /**
     * Create location
     *
     * @param $data
     *
     * @return bool|mixed
     */
    public function create($data)
    {
        try {
            $data = (array) $data;

            $locationItem = Mage::getModel('demac_multilocationinventory/location');

            $fieldArray = array(
                'name', 'external_id', 'address', 'zipcode', 'city', 'region_id', 'country_id', 'phone', 'fax', 'description',
                'store_Url', 'status', 'images', 'marker', 'lat', 'long'
            );

            $locationData = array();

            foreach ($fieldArray as $field) {
                if(isset($data[$field])) {
                    $locationData[$field] = $data[$field];
                }
            }

            if(isset($data['store_ids'])) {
                $locationData['store_id'] = (array) $data['store_ids'];
            }

            $locationData = $this->getAddressInformation($locationData, $locationItem);


            $locationItem->addData($locationData)
                ->save();

            return $locationItem->getId();

        } catch (Exception $e) {
            $this->_fault('data_invalid', $e->getMessage());
        }

        return false;

    }

    /**
     * Create multiple locations
     *
     * @param $productData
     *
     * @return bool
     */
    public function multiCreate($productData)
    {

        $productData = (array) $productData;

        foreach ($productData as $data) {
            $this->create($data);
        }

        return true;
    }

    /**
     * Get location info
     *
     * @param $locationId
     *
     * @return array
     */
    public function info($locationId)
    {
        $locationItem = Mage::getModel('demac_multilocationinventory/location')->load($locationId);

        if(!$locationItem->getId()) {
            $this->_fault('not_exists');
            // No item found
        }

        $locationItem->setData('store_ids', $locationItem->getStoreId());

        return $locationItem->toArray();
        // We can use only simple PHP data types in webservices.
    }

    /**
     * Get a collection of locations within the provided filter(s)
     *
     * @param $filters
     *
     * @return array
     */
    public function items($filters)
    {
        $collection = Mage::getResourceModel('demac_multilocationinventory/location_collection')->load();

        if(is_array($filters)) {
            try {
                foreach ($filters as $field => $value) {
                    $collection->addFieldToFilter($field, $value);
                }
            } catch (Mage_Core_Exception $e) {
                $this->_fault('filters_invalid', $e->getMessage());
                // If we are adding filter on non-existent attribute
            }
        }

        $result = array();
        foreach ($collection as $locationItem) {
            $locationItem->setData('store_ids', $locationItem->getStoreId());
            $result[] = $locationItem->toArray();
        }

        return $result;
    }

    /**
     * Update a location
     *
     * @param $data
     *
     * @return bool
     */
    public function update($data)
    {
        try {
            $data = (array) $data;

            $locationItem = Mage::getModel('demac_multilocationinventory/location')->load($data['location_id']);

            if(!$locationItem->getId()) {
                $this->_fault('not_exists');
                // No item found
            }

            $fieldArray = array(
                'name', 'external_id', 'address', 'zipcode', 'city', 'region_id', 'country_id', 'phone', 'fax', 'description',
                'store_Url', 'status', 'images', 'marker', 'lat', 'long'
            );

            $locationData = array();

            foreach ($fieldArray as $field) {
                if(isset($data[$field])) {
                    $locationData[$field] = $data[$field];
                }
            }

            if(isset($data['store_ids'])) {
                $locationData['store_id'] = (array) $data['store_ids'];
            }

            $locationData = $this->getAddressInformation($locationData, $locationItem);

            $locationItem->addData($locationData)
                ->save();

        } catch (Exception $e) {
            $this->_fault('data_invalid', $e->getMessage());
        }

        return true;
    }

    /**
     * Update multiple locations
     *
     * @param $locationDataArray
     *
     * @return bool
     */
    public function multiUpdate($locationDataArray)
    {

        $locationDataArray = (array) $locationDataArray;

        foreach ($locationDataArray as $locationData) {
            $this->update($locationData);
        }

        return true;
    }

    /**
     * Remove a location
     *
     * @param $locationId
     *
     * @return bool
     */
    public function remove($locationId)
    {
        $locationItem = Mage::getModel('demac_multilocationinventory/location')->load($locationId);

        if(!$locationItem->getId()) {
            $this->_fault('not_exists');
            // No item found
        }

        try {
            $locationItem->delete();
        } catch (Mage_Core_Exception $e) {
            $this->_fault('not_deleted', $e->getMessage());
            // Some errors while deleting.
        }

        return true;
    }

    /**
     * Remove multiple locations
     *
     * @param $idArray
     *
     * @return bool
     */
    public function multiRemove($idArray)
    {

        $idArray = (array) $idArray;

        foreach ($idArray as $id) {
            $this->remove($id);
        }

        return true;
    }

    /**
     * Get address information of a location.
     *
     * @param $locationData
     * @param $locationItem
     *
     * @return mixed
     */
    public function getAddressInformation($locationData, $locationItem)
    {
        $country = isset($locationData['country']) ? $locationData['country'] : $locationItem->getCountry();
        $address = isset($locationData['address']) ? $locationData['address'] : $locationItem->getAddress();
        $city    = isset($locationData['city']) ? $locationData['city'] : $locationItem->getCity();

        if(isset($locationData['region_id'])) {
            $region = Mage::getModel('directory/region')->loadByCode($locationData['region_id'], $country);
            if(!$region->getName() && in_array($country, array('US', 'CA', 'DE', 'AT', 'CH', 'ES', 'FR', 'RO', 'FI', 'EE', 'LV', 'LT'))) {
                $this->_fault('data_invalid', 'Invalid Region');
            }
            if($region->getId()) {
                $locationData['region_id'] = $region->getCode();
            }
        } else {
            $region = Mage::getModel('directory/region')->load($locationItem->getRegionId());
        }
        if((isset($locationData['address']) || isset($locationData['country'])
                || isset($locationData['city']) || isset($locationData['region_id']))
            && (!isset($locationData['lat']) || !isset($locationData['long'])
                || $locationData['lat'] == '' || $locationData['long'] == '')
        ) {
            $address .= ' ' . $city . ' ' . $region->getName();
            $latLong = Mage::helper('demac_multilocationinventory')->getLatLong($address, $country);
            if(is_null($latLong[0])) {
                $locationData['lat']  = false;
                $locationData['long'] = false;
            } else {
                $locationData['lat']  = $latLong[0];
                $locationData['long'] = $latLong[1];
            }
        }


        return $locationData;
    }

}