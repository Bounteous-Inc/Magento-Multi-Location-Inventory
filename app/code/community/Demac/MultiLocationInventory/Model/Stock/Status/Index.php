<?php

/**
 * Class Demac_MultiLocationInventory_Model_Stock_Status_Index
 */
class Demac_MultiLocationInventory_Model_Stock_Status_Index
    extends Mage_Core_Model_Abstract
{
    /**
     * Init
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('demac_multilocationinventory/stock_status_index');
    }


    /**
     * Processes the actual reindex.
     *
     * @param bool $productIds If FALSE all products will be indexed
     */
    public function reindex($productIds = false)
    {
        if($productIds !== false && !is_array($productIds) && is_numeric($productIds)) {
            $productIds = array($productIds);
        }

        //Create Missing Rows
        $this->getResource()->createMissingStockRows($productIds);
        $this->getResource()->createMissingStockIndexRows($productIds);

        //Add associated products (parent products, child products, etc)
        if($productIds !== false) {
            $associatedProductIds = $this->getAssociatedProducts($productIds);
            $productIds           = array_merge($productIds, $associatedProductIds);
        }

        //Update multi location inventory stock status index table
        $this->getResource()->updateStockStatusIndex($productIds);

        //Update core stock status table.
        $this->getResource()->updateCoreStockStatus($productIds);

        //Update core stock item table
        $this->getResource()->updateCoreStockItem($productIds);
    }


    /**
     * Gets parent products.
     *
     * @param Array $productIds
     *
     * @return Array
     */
    public function getAssociatedProducts($productIds)
    {
        $parentProductIds =
            Mage::getModel('catalog/product_link')
                ->getCollection()
                ->addFieldToFilter(
                    'product_id',
                    array(
                        'in' => $productIds
                    )
                )
                ->getColumnValues('linked_product_id');

        return $parentProductIds;
    }
}
