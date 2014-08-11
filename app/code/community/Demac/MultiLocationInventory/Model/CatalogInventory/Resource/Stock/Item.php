<?php
/**
 * Class Demac_MultiLocationInventory_Model_CatalogInventory_Resource_Stock_Item
 */
class Demac_MultiLocationInventory_Model_CatalogInventory_Resource_Stock_Item extends Mage_CatalogInventory_Model_Resource_Stock_Item
{
    /**
     * Loading stock item data by product
     *
     * @param Mage_CatalogInventory_Model_Stock_Item $item
     * @param int                                    $productId
     *
     * @return Mage_CatalogInventory_Model_Resource_Stock_Item
     */
    public function loadByProductId(Mage_CatalogInventory_Model_Stock_Item $item, $productId)
    {
        $storeId = Mage::app()->getStore()->getId();

        $select = $this->_getLoadSelect('product_id', $productId, $item)
            ->where('stock_id = :stock_id');
        $data   = $this->_getReadAdapter()->fetchRow($select, array(':stock_id' => $item->getStockId()));


        $stockStatusCollection = Mage::getModel('demac_multilocationinventory/stock_status_index')
            ->getCollection()
            ->addFieldToFilter('product_id', $productId)
            ->addFieldToFilter('store_id', $storeId);

        $stockStatus = $stockStatusCollection->getFirstItem();
        if($data && $stockStatus->getId()) {
            $data['qty']         = $stockStatus->getQty();
            $data['backorders']  = $stockStatus->getBackorders();
            $data['is_in_stock'] = $stockStatus->getIsInStock();
            //@TODO support use_config_backorders
            //$data['use_config_backorders'] = 1;//override...
            //@TODO support manage_stock
            //$data['manage_stock'] = 1;
            //@TODO support use_config_manage_stock
            //$data['use_config_manage_stock'] = 1;
            $item->setData($data);
        }
        $this->_afterLoad($item);

        return $this;
    }


    /**
     * Add join for catalog in stock field to product collection
     *
     * @param Mage_Catalog_Model_Resource_Product_Collection $productCollection
     *
     * @return Mage_CatalogInventory_Model_Resource_Stock_Item
     */
    public function addCatalogInventoryToProductCollection($productCollection)
    {
        $productCollection->getSelect()->joinLeft(
            array('cisi' => Mage::getSingleton('core/resource')->getTableName('demac_multilocationinventory/stock_status_index')),
            'e.entity_id = cisi.product_id' .
            ' AND cisi.store_id = ' . Mage::app()->getStore()->getId(),
            array(
                'is_saleable'        => 'is_in_stock',
                'inventory_in_stock' => 'is_in_stock'
            )
        );

        return $this;
    }

}