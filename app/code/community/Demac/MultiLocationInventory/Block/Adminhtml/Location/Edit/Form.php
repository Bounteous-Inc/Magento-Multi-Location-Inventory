<?php

/**
 * Class Demac_MultiLocationInventory_Block_Adminhtml_Location_Edit_Form
 */
class Demac_MultiLocationInventory_Block_Adminhtml_Location_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Init class
     */
    public function __construct()
    {
        parent::__construct();

        $this->setId('location_form');
        $this->setTitle($this->__('Location Information'));
    }

    /**
     * Setup form fields for inserts/updates
     *
     * return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm()
    {
        $model = Mage::registry('multilocationinventory_data');

        $form = new Varien_Data_Form(array(
                'id'      => 'edit_form',
                'action'  => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))),
                'method'  => 'post',
                'enctype' => 'multipart/form-data')
        );

        $form->setValues($model->getData());
        $form->setUseContainer(TRUE);
        $this->setForm($form);

        return parent::_prepareForm();
    }

}