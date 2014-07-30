<?php

/**
 * Class Demac_MultiLocationInventory_Block_Adminhtml_Location
 */
class Demac_MultiLocationInventory_Block_Adminhtml_Location extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    /**
     * Init location widget grid
     */
    public function __construct()
    {
        $this->_blockGroup = 'demac_multilocationinventory';
        $this->_controller = 'adminhtml_location';
        $this->_headerText = $this->__('Location');

        parent::__construct();
    }
}