<?php

/**
 * Class Demac_MultiLocationInventory_Model_Stock_Status_Index_Simple
 */
class Demac_MultiLocationInventory_Model_Stock_Status_Index_Simple
    implements Demac_MultiLocationInventory_Model_Stock_Status_Index_Interface
{
    protected $productType = 'simple';

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

        $query = Mage::getModel('demac_multilocationinventory/stock_status_index_query');
        $query->addField('product_id', 'stock.product_id');
        $query->addField('qty', 'IF(GROUP_CONCAT(stock.manage_stock) LIKE "%0%", 1, SUM(IF(stock.is_in_stock = 1, stock.qty, 0)))');
        $query->addField('is_in_stock', 'IF(GROUP_CONCAT(stock.manage_stock) LIKE "%0%", 1, IF(SUM(stock.is_in_stock) > 0, 1, 0))');
        $query->addField('backorders', 'IF(SUM(stock.backorders) > 0, 1, 0)');
        $query->addField('manage_stock', 'IF(GROUP_CONCAT(stock.manage_stock) LIKE "%0%", 0, 1)');
        $query->setFrom($stockTable, 'stock');
        $query->addJoin('JOIN', $locationsTable, 'location', 'stock.location_id = location.id');
        $query->addJoin('JOIN', $coreCatalogProductEntityTable, 'product_entity', 'stock.product_id = product_entity.entity_id');
        $selectWhere = 'location.status = 1 AND product_entity.type_id = "' . $this->productType . '"';
        if(is_array($productIds)) {
            $selectWhere .= ' AND stock.product_id IN (' . implode(',', $productIds) . ')';
        }
        $query->setWhere($selectWhere);

        return $query;
    }

    /**
     * A select query to retrieve the stock status index data of simple products.
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
        $query->setGroup('CONCAT(stores.store_id, "_", stock.product_id)');


        return (string) $query;
    }

    /**
     * A select query to retrieve the global stock status index data of simple products.
     *
     * @param bool|array $productIds Product IDs to reindex, if a non-array is provided we reindex all products.
     *
     * @return string
     */
    public function getGlobalStockStatusIndexSelectQuery($productIds = false)
    {
        $query = $this->getBaseQuery($productIds);
        $query->addField('store_id', '0');
        $query->setGroup('stock.product_id');

        return (string) $query;
    }
}
