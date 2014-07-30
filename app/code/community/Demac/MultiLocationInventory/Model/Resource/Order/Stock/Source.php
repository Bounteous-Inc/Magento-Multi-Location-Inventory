<?php

/**
 * Class Demac_MultiLocationInventory_Model_Resource_Order_Stock_Source
 */
class Demac_MultiLocationInventory_Model_Resource_Order_Stock_Source extends Mage_Core_Model_Resource_Db_Abstract
{

    /**
     * Init Resource
     */
    protected function _construct()
    {
        $this->_init('demac_multilocationinventory/order_stock_source', 'id');
    }
}