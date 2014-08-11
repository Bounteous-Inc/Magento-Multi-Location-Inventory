<?php

/**
 * Class Demac_MultiLocationInventory_Block_Adminhtml_Catalog_Product_Edit_Tab_Super_Config_Simple
 */
class Demac_MultiLocationInventory_Block_Adminhtml_Catalog_Product_Edit_Tab_Super_Config_Simple
    extends Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Super_Config_Simple
{
    /**
     * Prepare associated product / simple product quick create form.
     *
     * Moves inventory related fields into hidden fields to avoid errors (these fields are no longer used).
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();

        $form->setFieldNameSuffix('simple_product');
        $form->setDataObject($this->_getProduct());

        $fieldset = $form->addFieldset('simple_product', array(
            'legend' => Mage::helper('catalog')->__('Quick simple product creation')
        ));
        $this->_addElementTypes($fieldset);
        $attributesConfig = array(
            'autogenerate' => array('name', 'sku'),
            'additional'   => array('name', 'sku', 'visibility', 'status')
        );

        $availableTypes = array('text', 'select', 'multiselect', 'textarea', 'price', 'weight');

        $attributes = Mage::getModel('catalog/product')
            ->setTypeId(Mage_Catalog_Model_Product_Type::TYPE_SIMPLE)
            ->setAttributeSetId($this->_getProduct()->getAttributeSetId())
            ->getAttributes();

        /* Standart attributes */
        foreach ($attributes as $attribute) {
            if(($attribute->getIsRequired()
                    && $attribute->getApplyTo()
                    // If not applied to configurable
                    && !in_array(Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE, $attribute->getApplyTo())
                    // If not used in configurable
                    && !in_array($attribute->getId(),
                                 $this->_getProduct()->getTypeInstance(true)->getUsedProductAttributeIds($this->_getProduct()))
                )
                // Or in additional
                || in_array($attribute->getAttributeCode(), $attributesConfig['additional'])
            ) {
                $inputType = $attribute->getFrontend()->getInputType();
                if(!in_array($inputType, $availableTypes)) {
                    continue;
                }
                $attributeCode = $attribute->getAttributeCode();
                $attribute->setAttributeCode('simple_product_' . $attributeCode);
                $element = $fieldset->addField(
                    'simple_product_' . $attributeCode,
                    $inputType,
                    array(
                        'label'    => $attribute->getFrontend()->getLabel(),
                        'name'     => $attributeCode,
                        'required' => $attribute->getIsRequired(),
                    )
                )->setEntityAttribute($attribute);

                if(in_array($attributeCode, $attributesConfig['autogenerate'])) {
                    $element->setDisabled('true');
                    $element->setValue($this->_getProduct()->getData($attributeCode));
                    $element->setAfterElementHtml(
                        '<input type="checkbox" id="simple_product_' . $attributeCode . '_autogenerate" '
                        . 'name="simple_product[' . $attributeCode . '_autogenerate]" value="1" '
                        . 'onclick="toggleValueElements(this, this.parentNode)" checked="checked" /> '
                        . '<label for="simple_product_' . $attributeCode . '_autogenerate" >'
                        . Mage::helper('catalog')->__('Autogenerate')
                        . '</label>'
                    );
                }


                if($inputType == 'select' || $inputType == 'multiselect') {
                    $element->setValues($attribute->getFrontend()->getSelectOptions());
                }
            }

        }

        /* Configurable attributes */
        $usedAttributes = $this->_getProduct()->getTypeInstance(true)->getUsedProductAttributes($this->_getProduct());
        foreach ($usedAttributes as $attribute) {
            $attributeCode = $attribute->getAttributeCode();
            $fieldset->addField('simple_product_' . $attributeCode, 'select', array(
                'label'    => $attribute->getFrontend()->getLabel(),
                'name'     => $attributeCode,
                'values'   => $attribute->getSource()->getAllOptions(true, true),
                'required' => true,
                'class'    => 'validate-configurable',
                'onchange' => 'superProduct.showPricing(this, \'' . $attributeCode . '\')'
            ));

            $fieldset->addField('simple_product_' . $attributeCode . '_pricing_value', 'hidden', array(
                'name' => 'pricing[' . $attributeCode . '][value]'
            ));

            $fieldset->addField('simple_product_' . $attributeCode . '_pricing_type', 'hidden', array(
                'name' => 'pricing[' . $attributeCode . '][is_percent]'
            ));
        }

        $stockHiddenFields = array(
            'qty'                         => 0,
            'is_in_stock'                 => 1,
            'use_config_min_qty'          => 1,
            'use_config_min_sale_qty'     => 1,
            'use_config_max_sale_qty'     => 1,
            'use_config_backorders'       => 1,
            'use_config_notify_stock_qty' => 1,
            'is_qty_decimal'              => 0
        );

        foreach ($stockHiddenFields as $fieldName => $fieldValue) {
            $fieldset->addField('simple_product_inventory_' . $fieldName, 'hidden', array(
                'name'  => 'stock_data[' . $fieldName . ']',
                'value' => $fieldValue
            ));
        }


        $fieldset->addField('create_button', 'note', array(
            'text' => $this->getButtonHtml(
                    Mage::helper('catalog')->__('Quick Create'),
                    'superProduct.quickCreateNewProduct()',
                    'save'
                )
        ));

        $this->setForm($form);
    }


}