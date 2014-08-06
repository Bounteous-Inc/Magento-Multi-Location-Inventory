<?php
/**
 * Created by PhpStorm.
 * User: MichaelK
 * Date: 1/15/14
 * Time: 11:25 AM
 */

/**
 * Class Demac_MultiLocationInventory_Block_Adminhtml_Location_Edit_Tab_Location
 */
class Demac_MultiLocationInventory_Block_Adminhtml_Location_Edit_Tab_Location extends Mage_Adminhtml_Block_Widget_Form
{

    /**
     * Prepare form fields and data for Adminhtml Widget Form rendering.
     *
     * @return Demac_MultiLocationInventory_Block_Adminhtml_Location_Edit_Tab_Location
     */
    protected function _prepareForm()
    {
        $model  = Mage::registry('multilocationinventory_data');
        $isEdit = (bool) $model->getId();

        $form     = new Varien_Data_Form();
        $fieldset = $form->addFieldset('demac_multilocationinventory_form', array(
            'legend' => Mage::helper('demac_multilocationinventory')->__('Location Information')
        ));

        $this->_prepareFormHiddenFields($fieldset, $isEdit);
        $this->_prepareFormStatusField($fieldset);
        if(!Mage::app()->isSingleStoreMode()) {
            $this->_prepareFormStoreSelectorField($fieldset);
        } else {
            $this->_prepareFormStoreSelectorHiddenField($fieldset);
            $model->setStoreId(Mage::app()->getStore(true)->getId());
        }
        $this->_prepareFormGeneralFields($fieldset);
        $this->_prepareFormAddressFields($fieldset);
        $this->_prepareFormLocationFields($fieldset);

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Add hidden fields for id and create/update time to the form.
     *
     * @param $fieldset
     * @param $isEdit
     */
    protected function _prepareFormHiddenFields($fieldset, $isEdit)
    {
        if($isEdit) {
            $fieldset->addField('id', 'hidden', array(
                'name' => 'id',
            ));
        }

        $fieldset->addField('created_time', 'hidden', array(
            'name' => 'created_time',
        ));

        $fieldset->addField('update_time', 'hidden', array(
            'name' => 'update_time',
        ));
    }



    /**
     * Add hidden field to specify the current store to the form.
     *
     * @param $fieldset
     */
    protected function _prepareFormStoreSelectorHiddenField($fieldset)
    {
        $fieldset->addField('store_id', 'hidden', array(
            'name'  => 'stores[]',
            'value' => Mage::app()->getStore(true)->getId()
        ));
    }

    /**
     * Add status field to the form.
     *
     * @param $fieldset
     */
    protected function _prepareFormStatusField($fieldset)
    {
        $fieldset->addField('status', 'select', array(
            'label'  => Mage::helper('demac_multilocationinventory')->__('Status'),
            'name'   => 'status',
            'values' => array(
                array(
                    'value' => 1,
                    'label' => Mage::helper('demac_multilocationinventory')->__('Enabled'),
                ),

                array(
                    'value' => 0,
                    'label' => Mage::helper('demac_multilocationinventory')->__('Disabled'),
                ),
            ),
        ));
    }

    /**
     * Add general fields to the form.
     *
     * @param $fieldset
     */
    protected function _prepareFormGeneralFields($fieldset)
    {
        $fieldset->addField('name', 'text', array(
            'label'    => Mage::helper('demac_multilocationinventory')->__('Name'),
            'class'    => 'required-entry',
            'required' => true,
            'name'     => 'name',
        ));

        $fieldset->addField('external_id', 'text', array(
            'label'    => Mage::helper('demac_multilocationinventory')->__('External ID'),
            'required' => false,
            'name'     => 'external_id',
        ));

        $fieldset->addField('store_url', 'text', array(
            'label'    => Mage::helper('demac_multilocationinventory')->__('Store Link'),
            'required' => false,
            'name'     => 'store_url',
        ));
    }

    /**
     * Add address fields to the form.
     *
     * @param $fieldset
     */
    protected function _prepareFormAddressFields($fieldset)
    {
        $fieldset->addField('address', 'text', array(
            'label'    => Mage::helper('demac_multilocationinventory')->__('Address'),
            'class'    => 'required-entry',
            'required' => true,
            'name'     => 'address',
            'onchange' => 'getLatLng()',
        ));

        $fieldset->addField('zipcode', 'text', array(
            'label'    => Mage::helper('demac_multilocationinventory')->__('Postal Code'),
            'class'    => 'required-entry',
            'required' => true,
            'name'     => 'zipcode',
        ));

        $fieldset->addField('city', 'text', array(
            'label'    => Mage::helper('demac_multilocationinventory')->__('City'),
            'class'    => 'required-entry',
            'required' => true,
            'name'     => 'city',
            'onchange' => 'getLatLng()',
        ));

        $values    = array();
        $countryId = Mage::registry('multilocationinventory_data')->getCountryId();
        if($countryId) {
            $values = Mage::helper('demac_multilocationinventory')->getRegions($countryId);
        }
        $fieldset->addField('region_id', 'select', array(
            'name'   => 'region_id',
            'label'  => 'State/Province',
            'values' => $values
        ));

        $countryList = Mage::getModel('directory/country')->getCollection()->toOptionArray();
        $country     = $fieldset->addField('country_id', 'select', array(
            'label'    => Mage::helper('demac_multilocationinventory')->__('Country'),
            'name'     => 'country_id',
            'title'    => 'country',
            'values'   => $countryList,
            'onchange' => 'getLatLng(); getstate(this)',
        ));
        $country->setAfterElementHtml("<script type=\"text/javascript\">
            function getstate(selectElement){
                var reloadurl = '" . $this->getUrl('adminhtml/multiLocationInventory/region') . "country/' + selectElement.value;
                new Ajax.Request(reloadurl, {
                    method: 'get',
                    onLoading: function (stateform) {
                        $('region_id').update('Searching...');
                    },
                    onComplete: function(stateform) {
                        $('region_id').update(stateform.responseText);
                    }
                });
            }
        </script>");
    }

    /**
     * Add location fields to the form.
     *
     * @param $fieldset
     */
    protected function _prepareFormLocationFields($fieldset)
    {
        $fieldset->addField('lat', 'text', array(
            'label'    => Mage::helper('demac_multilocationinventory')->__('Latitude'),
            'required' => true,
            'name'     => 'lat',
        ));

        $fieldset->addField('long', 'text', array(
            'label'    => Mage::helper('demac_multilocationinventory')->__('Longitude'),
            'required' => true,
            'name'     => 'long',
        ));
    }
}