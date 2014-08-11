<?php
/**
 * Class Demac_MultiLocationInventory_Model_Resource_Stock_Status_Index_Collection
 */
class Demac_MultiLocationInventory_Model_Resource_Stock_Status_Index_Collection
    extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * Init collection
     */
    protected function _construct()
    {
        $this->_init('demac_multilocationinventory/stock_status_index');
    }

    /**
     * Add product filter to collection
     *
     * @param array $products
     *
     * @return Demac_MultiLocationInventory_Model_Resource_Stock_Status_Index_Collection
     */
    public function addProductsFilter($products)
    {
        $productIds = array();
        foreach ($products as $product) {
            if($product instanceof Mage_Catalog_Model_Product) {
                $productIds[] = $product->getId();
            } else {
                $productIds[] = $product;
            }
        }
        if(empty($productIds)) {
            $productIds[] = false;
            $this->_setIsLoaded(true);
        }
        $this->addFieldToFilter('main_table.product_id', array('in' => $productIds));

        return $this;
    }

    /**
     * Join Stock Status to collection
     *
     * @param int $storeId
     *
     * @return Demac_MultiLocationInventory_Model_Resource_Stock_Status_Index_Collection
     */
    public function joinStockStatus($storeId = null)
    {
        $websiteId = Mage::app()->getStore($storeId)->getWebsiteId();
        $this->getSelect()->joinLeft(
            array(
                'status_table_mli' => $this->getTable('demac_multilocationinventory/stock_status_index')
            ),
            'main_table.product_id=status_table_mli.product_id'
            . $this->getConnection()->quoteInto(' AND status_table_mli.store_id=?', Mage::app()->getStore()->getId()),
            array('is_in_stock as stock_status')
        );

        return $this;
    }

}