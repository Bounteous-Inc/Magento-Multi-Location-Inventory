<?php
/**
 * Created by PhpStorm.
 * User: Allan MacGregor - Magento Practice Lead <allan@demacmedia.com>
 * Company: Demac Media Inc.
 * Date: 5/7/14
 * Time: 1:17 PM
 */

/**
 * Class Demac_MultiLocationInventory_Model_CatalogInventory_Stock_Item
 */
class Demac_MultiLocationInventory_Model_CatalogInventory_Stock_Item extends Mage_CatalogInventory_Model_Stock_Item
{
    /**
     * Before save prepare process
     *
     * @return Demac_MultiLocationInventory_Model_CatalogInventory_Stock_Item
     */
    protected function _beforeSave()
    {
        parent::_beforeSave();

        Mage::dispatchEvent('model_save_before', array('object' => $this));
        Mage::dispatchEvent($this->_eventPrefix . '_save_before', $this->_getEventData());

        return $this;
    }

    /**
     * Check quantity
     *  - Loop through all locations/product links for given store, when you find one that has the right amount
     *      return true otherwise return false with the error,
     *
     * @param   decimal $qty
     * @exception Mage_Core_Exception
     * @return  bool
     */
    public function checkQty($qty)
    {
        if (!$this->getManageStock() || Mage::app()->getStore()->isAdmin()) {
            return true;
        }

        $availableQty = 0;

        /** @var Demac_MultiLocationInventory_Model_Resource_Location_Collection $locations */
        $locations = Mage::getModel('demac_multilocationinventory/location')->getCollection();
        $locations->joinStockDataOnProductAndStoreView($this->getProductId(), Mage::app()->getStore()->getId());

        foreach ($locations as $location) {
            $locationAvailableQty = $location->getQty() - $location->getMinQty();

            // We have more requested from this location than we have
            if ($locationAvailableQty - ($qty - $availableQty) >= 0) {
                return true;
            } else {
                $availableQty += $locationAvailableQty;

                switch ($this->getBackorders()) {
                    case Mage_CatalogInventory_Model_Stock::BACKORDERS_YES_NONOTIFY:
                    case Mage_CatalogInventory_Model_Stock::BACKORDERS_YES_NOTIFY:
                        return true;
                        break;
                    default:
                        break;
                }
            }
        }

        return false;
    }
}
