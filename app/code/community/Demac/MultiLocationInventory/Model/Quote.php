<?php

/**
 * Class Demac_MultiLocationInventory_Model_Quote
 */
class Demac_MultiLocationInventory_Model_Quote extends Mage_Core_Model_Abstract
{
    /**
     * Init
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('demac_multilocationinventory/quote');
    }
}