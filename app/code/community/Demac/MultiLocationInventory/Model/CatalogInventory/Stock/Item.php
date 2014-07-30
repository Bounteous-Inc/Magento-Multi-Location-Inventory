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

}