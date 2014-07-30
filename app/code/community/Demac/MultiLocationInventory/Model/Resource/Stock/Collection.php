<?php

/**
 * Created by PhpStorm.
 * User: Michael
 * Date: 4/2/14
 * Time: 11:52 AM
 */
class Demac_MultiLocationInventory_Model_Resource_Stock_Collection
    extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * Join product data to this collection.
     *
     * @return Demac_MultiLocationInventory_Model_Resource_Stock_Collection
     */
    public function addProductData()
    {
        /** add particular attribute code to this array */
        $productAttributes = array('name', 'status');
        foreach ($productAttributes as $attributeCode) {
            $alias     = $attributeCode . '_table';
            $attribute = Mage::getSingleton('eav/config')
                ->getAttribute(Mage_Catalog_Model_Product::ENTITY, $attributeCode);

            /** Adding eav attribute value */
            $this->getSelect()->join(
                array($alias => $attribute->getBackendTable()),
                "main_table.product_id = $alias.entity_id AND $alias.attribute_id={$attribute->getId()}",
                array('product_' . $attributeCode => 'value')
            );
            $this->_map['fields']['product_' . $attributeCode] = $alias . '.value';
        }
        /** adding catalog_product_entity table fields */
        $this->join(
            'catalog/product',
            'product_id=`catalog/product`.entity_id',
            array('product_sku' => 'sku', 'product_type_id' => 'type_id',)
        );

        //Group by product_id just in case we have multiple views of a product being selected.
        $this->getSelect()->group('main_table.product_id');

        $this->_map['fields']['product_sku']     = '`catalog/product`.sku';
        $this->_map['fields']['product_type_id'] = '`catalog/product`.type_id';

        return $this;
    }

    /**
     * Init collection
     */
    protected function _construct()
    {
        $this->_init('demac_multilocationinventory/stock');
    }
}