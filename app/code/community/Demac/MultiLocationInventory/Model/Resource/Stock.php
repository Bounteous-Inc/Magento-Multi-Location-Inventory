<?php
/**
 * Created by PhpStorm.
 * User: Michael
 * Date: 4/2/14
 * Time: 11:52 AM
 */

/**
 * Class Demac_MultiLocationInventory_Model_Resource_Stock
 */
class Demac_MultiLocationInventory_Model_Resource_Stock extends Mage_Core_Model_Resource_Db_Abstract
{

    /**
     * Init Resource
     */
    protected function _construct()
    {
        $this->_init('demac_multilocationinventory/stock', 'stock_id');
    }

    /**
     * Load Stock object by product
     *
     * @param Demac_MultiLocationInventory_Model_Stock $stock
     * @param                                          $locationId
     * @param                                          $productId
     *
     * @return Demac_MultiLocationInventory_Model_Resource_Stock
     */
    public function loadByProduct(Demac_MultiLocationInventory_Model_Stock $stock, $locationId, $productId)
    {
        $adapter = $this->_getReadAdapter();
        $bind    = array('location' => $locationId, 'product' => $productId);
        $select  = $adapter->select()
            ->from($this->getMainTable(), array($this->getIdFieldName()))
            ->where('location_id = :location')
            ->where('product_id = :product');


        $stockId = $adapter->fetchOne($select, $bind);
        if ($stockId) {
            $this->load($stock, $stockId);
        } else {
            $stock->setData(array());
        }

        return $this;
    }

    /**
     * Get select query for loading this collection with product data.
     *
     * @param string                   $field
     * @param mixed                    $value
     * @param Mage_Core_Model_Abstract $object
     *
     * @return Zend_Db_Select
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);
        $this->addProductData($select);

        return $select;
    }

    /**
     * Add product data to this collection's select
     *
     * @param $select
     *
     * @return $this
     */
    public function addProductData($select)
    {
        /** add particular attribute code to this array */
        $productAttributes = array('name', 'status');
        foreach ($productAttributes as $attributeCode) {
            $alias     = $attributeCode . '_table';
            $attribute = Mage::getSingleton('eav/config')
                ->getAttribute(Mage_Catalog_Model_Product::ENTITY, $attributeCode);
            $mainTable = $this->getMainTable();

            /** Adding eav attribute value */
            $select->join(
                array($alias => $attribute->getBackendTable()),
                "$mainTable.product_id = $alias.entity_id AND $alias.attribute_id={$attribute->getId()}",
                array('product_' . $attributeCode => 'value')
            );
        }
        /** adding catalog_product_entity table fields */
        $select->join(
            array('product' => $this->getTable('catalog/product')),
            'product_id=product.entity_id',
            array('product_sku' => 'sku', 'product_type_id' => 'type_id',)
        );

        return $this;
    }

}