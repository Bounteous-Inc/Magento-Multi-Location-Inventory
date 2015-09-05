<?php

/**
 * Class Demac_MultiLocationInventory_Block_Adminhtml_Location_Edit
 */
class Demac_MultiLocationInventory_Block_Adminhtml_Location_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    /**
     * Init class
     */
    public function __construct()
    {
        parent::__construct();
        $this->_blockGroup = 'demac_multilocationinventory';
        $this->_objectId   = 'id';
        $this->_controller = 'adminhtml_location';


        $this->_updateButton('save', 'label', $this->__('Save Location'));
        $this->_updateButton('delete', 'label', $this->__('Delete Location'));

        $this->_addButton('saveandcontinue', array(
            'label'   => $this->__('Save and Continue Edit'),
            'onclick' => 'saveAndContinueEdit()',
            'class'   => 'save',
        ), -100);
    }

    /**
     * Get Header text
     *
     * @return string
     */
    public function getHeaderText()
    {
        if(Mage::registry('multilocationinventory_data')->getId()) {
            return $this->__('Edit Location');
        } else {
            return $this->__('New Location');
        }
    }
}