<?php
/**
 * Class Demac_MultiLocationInventory_Model_CatalogInventory_Resource_Stock_Status
 */
class Demac_MultiLocationInventory_Model_CatalogInventory_Resource_Stock_Status extends Mage_CatalogInventory_Model_Resource_Stock_Status
{

    private $stockStatuses = array();

    /**
     * Retrieve product status
     * Return array as key product id, value - stock status
     *
     * @param int|array $productIds
     * @param int       $websiteId
     * @param int       $stockId
     *
     * @return array
     */
    public function getProductStatus($productIds, $websiteId, $stockId = 1)
    {
        $storeId = Mage::app()->getStore()->getId();
        if(is_numeric($productIds)) {
            $productIds = array($productIds);
        }
        $stockStatusCollection = Mage::getModel('demac_multilocationinventory/stock_status_index')
            ->getCollection()
            ->addFieldToFilter('store_id', $storeId)
            ->addFieldToFilter('product_id', array('in' => $productIds));

        //reset, just in case there is data left over from a previous run
        $this->stockStatuses = array();

        Mage::getModel('core/resource_iterator')->walk(
            $stockStatusCollection->getSelect(),
            array(
                array($this, '_getProductStatusIterate')
            ),
            array(
                'invoker' => $this
            )
        );

        return $this->stockStatuses;
    }

    /**
     * Iterator for getProductStatus
     *
     * @param $args
     */
    public function _getProductStatusIterate($args)
    {
        $row                             = $args['row'];
        $productId                       = $row['product_id'];
        $this->stockStatuses[$productId] = $row['is_in_stock'];
    }


    /**
     * Add stock status limitation to catalog product price index select object
     *
     * @param Varien_Db_Select    $select
     * @param string|Zend_Db_Expr $entityField
     * @param string|Zend_Db_Expr $websiteField
     *
     * @return Mage_CatalogInventory_Model_Resource_Stock_Status
     */
    public function prepareCatalogProductIndexSelect(Varien_Db_Select $select, $entityField, $websiteField)
    {
        return $this;
    }

    /**
     * Add only is in stock products filter to product collection
     *
     * @param Mage_Catalog_Model_Resource_Product_Collection $collection
     *
     * @return Mage_CatalogInventory_Model_Resource_Stock_Status
     */
    public function addIsInStockFilterToCollection($collection)
    {
        $websiteId     = Mage::app()->getStore($collection->getStoreId())->getWebsiteId();
        $joinCondition = $this->_getReadAdapter()
            ->quoteInto('e.entity_id = status_table_mli.product_id'
                        . ' AND status_table_mli.store_id = ?', Mage::app()->getStore()->getId()
            );

        $collection->getSelect()
            ->join(
                array('status_table_mli' => $this->getTable('demac_multilocationinventory/stock_status_index')),
                $joinCondition,
                array()
            )
            ->where('status_table_mli.is_in_stock=1');

        return $this;
    }

}