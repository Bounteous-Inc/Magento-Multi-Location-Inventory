<?php

/**
 * Class Demac_MultiLocationInventory_Model_Resource_Quote
 */
class Demac_MultiLocationInventory_Model_Resource_Quote extends Mage_Core_Model_Resource_Db_Abstract
{
    protected $_isPkAutoIncrement = FALSE;

    /**
     * Init Resource
     */
    protected function _construct()
    {
        $this->_init('demac_multilocationinventory/quote', 'item_id');
    }

}