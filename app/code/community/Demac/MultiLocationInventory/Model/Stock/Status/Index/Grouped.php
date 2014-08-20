<?php

/**
 * Class Demac_MultiLocationInventory_Model_Stock_Status_Index_Grouped
 */
class Demac_MultiLocationInventory_Model_Stock_Status_Index_Grouped
    extends Demac_MultiLocationInventory_Model_Stock_Status_Index_Simple
    implements Demac_MultiLocationInventory_Model_Stock_Status_Index_Interface
{
    protected $productType = 'grouped';

    /**
     * Get base simple product select query.
     *
     * @param bool $productIds
     *
     * @return mixed
     */
    protected function getBaseQuery($productIds = false) {
        $stockTable                    = Mage::getModel('core/resource')->getTableName('demac_multilocationinventory/stock');
        $locationsTable                = Mage::getModel('core/resource')->getTableName('demac_multilocationinventory/location');
        $coreCatalogProductEntityTable = Mage::getModel('core/resource')->getTableName('catalog/product');
        $coreCatalogProductlink        = Mage::getModel('core/resource')->getTableName('catalog/product_link');

        $query = parent::getBaseQuery($productIds);
        $query->addField('product_id', 'product_entity.entity_id');
        $query->addField('qty', 'IF(GROUP_CONCAT(stock.manage_stock) LIKE "%0%", 1, IF(SUM(IF(stock.is_in_stock = 1, stock.qty, 0)) AND SUM(stock.is_in_stock) > 0, 1, 0))');
        $query->addField('is_in_stock', 'IF(GROUP_CONCAT(stock.manage_stock) LIKE "%0%", 1, IF(SUM(IF(stock.is_in_stock = 1, stock.qty, 0)) AND SUM(stock.is_in_stock) > 0, 1, 0))');
        $query->setFrom($coreCatalogProductEntityTable, 'product_entity');
        $query->addJoin('JOIN', $coreCatalogProductlink, 'link', 'product_entity.entity_id = link.product_id');
        $query->addJoin('JOIN', $stockTable, 'stock', 'link.linked_product_id = stock.product_id');
        $query->addJoin('JOIN', $locationsTable, 'location', 'stock.location_id = location.id');

        $query->removeJoin('product_entity');

        return $query;
    }


    /**
     * A select query to retrieve the stock status index data of grouped products.
     *
     * @param bool|array $productIds Product IDs to reindex, if a non-array is provided we reindex all products.
     *
     * @return string
     */
    public function getStockStatusIndexSelectQuery($productIds = false)
    {
        $storesTable                   = Mage::getModel('core/resource')->getTableName('demac_multilocationinventory/stores');

        $query = $this->getBaseQuery($productIds);
        $query->addField('store_id', 'stores.store_id');
        $query->addJoin('JOIN', $storesTable, 'stores', 'stock.location_id = stores.location_id');
        $query->setGroup('CONCAT(stores.store_id, "_", product_entity.entity_id)');

        return (string) $query;
    }


    /**
     * A select query to retrieve the global stock status index data of grouped products.
     *
     * @param bool|array $productIds Product IDs to reindex, if a non-array is provided we reindex all products.
     *
     * @return string
     */
    public function getGlobalStockStatusIndexSelectQuery($productIds = false) {
        $query = $this->getBaseQuery($productIds);
        $query->addField('store_id', '0');
        $query->setGroup('product_entity.entity_id');

        return (string) $query;
    }
}
