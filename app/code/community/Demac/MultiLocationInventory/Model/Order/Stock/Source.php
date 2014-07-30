<?php

/**
 * Class Demac_MultiLocationInventory_Model_Order_Stock_Source
 */
class Demac_MultiLocationInventory_Model_Order_Stock_Source extends Mage_Core_Model_Abstract
{
    /**
     * Init model
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('demac_multilocationinventory/order_stock_source');
    }
}