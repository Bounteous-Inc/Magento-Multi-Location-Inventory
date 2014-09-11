<?php

/**
 * Class Demac_MultiLocationInventory_Helper_Location
 */
class Demac_MultiLocationInventory_Helper_Location extends Mage_Core_Helper_Abstract
{
    /**
     * Finds the closest location with a product in stock.
     *
     * @param array $customerCoordinates The customer's location (array with 2 keys: latitude/longitude).
     * @param int   $productId           Product ID that is being searched for.
     * @param int   $quantity            Minimum quantity that is being searched for.
     *
     * @return bool|Demac_MultiLocationInventory_Model_Location Location if the location is found, otherwise false.
     * @throws Mage_Core_Exception Throws exception when Demac_Geocoding isn't loaded.
     */
    public function getClosestLocationWithProduct($customerCoordinates, $productId, $quantity)
    {
        $closestLocation = $this->getClosestLocation($customerCoordinates);
        $excludes        = array();
        while ($closestLocation !== false) {
            //check if inventory of the correct product is available at this location...
            $quantityAvailable = Mage::getModel('demac_multilocationinventory/stock')->loadByProduct($closestLocation->getId(), $productId)->getQty();
            if($quantityAvailable > $quantity) {
                return $closestLocation;
            }

            $excludes[]      = $closestLocation->getId();
            $closestLocation = $this->getClosestLocation($customerCoordinates, $excludes);
        }

        return false;
    }

    /**
     *
     *
     * @param $orderId
     * @param $quoteItemId
     *
     * @return array
     */
    public function getPrioritizedLocationsForOrderQuoteItem($orderId, $quoteItemId)
    {
        //get storeview id from order id.
        $storeId = Mage::getModel('sales/order')->load($orderId)->getStoreId();

        $locationsCollection = Mage::getModel('demac_multilocationinventory/location')
            ->getCollection()
            ->joinStockDataOnProductAndStoreView();
        $locationsCollection
            ->getSelect()
            ->group('main_table.id');


        $locationIds = $locationsCollection->getAllIds();

        //Init a blank array of prioritized locations for now.
        $prioritizedLocationsProcessing = array();
        //for each location available to this store view...
        foreach ($locationIds as $locationId) {
            //each priority score will have an array of locations with that score
            $priority = $this->getPriorityForOrderLocationQuoteItem($orderId, $locationId, $quoteItemId);
            if(isset($prioritizedLocationsProcessing[$priority])) {
                $prioritizedLocationsProcessing[$priority][] = $locationId;
            } else {
                $prioritizedLocationsProcessing[$priority] = array($locationId);
            }
        }

        //Sort array by keys (which holds priority)
        ksort($prioritizedLocationsProcessing);

        //reverse the order of the array so that it now goes from highest to lowest and gets rekeyed
        $prioritizedLocationsProcessing = array_reverse($prioritizedLocationsProcessing);

        $prioritizedLocations = array();

        //Get the prioritized list into a single dimensional array.
        //If multiple locations exist with the same priority they are ordered based on the order they were pulled from
        //the database in.
        foreach ($prioritizedLocationsProcessing as $prioritizedLocationsSet) {
            foreach ($prioritizedLocationsSet as $location) {
                $prioritizedLocations[] = $location;
            }
        }

        return $prioritizedLocations;
    }

    /**
     * Get a priority score based on Order ID, Location ID and Quote Item ID.
     *
     * @param int $orderId
     * @param int $locationId
     * @param int $quoteItemId
     *
     * @return int
     */
    public function getPriorityForOrderLocationQuoteItem($orderId, $locationId, $quoteItemId)
    {
        //We can look into other items in the order in the future, but for now we're just going to return a random
        //priority for testing.
        return rand(1, 100);
    }
}