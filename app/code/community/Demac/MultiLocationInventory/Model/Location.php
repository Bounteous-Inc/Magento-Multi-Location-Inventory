<?php

/**
 * Class Demac_MultiLocationInventory_Model_Location
 */
class Demac_MultiLocationInventory_Model_Location extends Mage_Core_Model_Abstract
{
    /**
     * Init model
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('demac_multilocationinventory/location');
    }

    /**
     * @param string $code
     * @return int|false
     */
    public function getIdByCode($code)
    {
        return $this->_getResource()->getIdByCode($code);
    }
}