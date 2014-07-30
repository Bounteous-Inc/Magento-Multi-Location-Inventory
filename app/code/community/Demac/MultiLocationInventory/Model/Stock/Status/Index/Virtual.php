<?php

/**
 * Class Demac_MultiLocationInventory_Model_Stock_Status_Index_Virtual
 */
class Demac_MultiLocationInventory_Model_Stock_Status_Index_Virtual
    implements Demac_MultiLocationInventory_Model_Stock_Status_Index_Interface
{
    /**
     * A select query to retrieve the stock status index data of simple products.
     *
     * @param bool|array $productIds Product IDs to reindex, if a non-array is provided we reindex all products.
     *
     * @return string
     */
    public function getStockStatusIndexSelectQuery($productIds = FALSE)
    {
        $stockTable                    = Mage::getModel('core/resource')->getTableName('demac_multilocationinventory/stock');
        $storesTable                   = Mage::getModel('core/resource')->getTableName('demac_multilocationinventory/stores');
        $locationsTable                = Mage::getModel('core/resource')->getTableName('demac_multilocationinventory/location');
        $coreCatalogProductEntityTable = Mage::getModel('core/resource')->getTableName('catalog/product');

        $query =
            '    SELECT'
            . '      stores.store_id as store_id,'
            . '      stock.product_id as product_id,'
            . '      IF(SUM(stock.is_in_stock) > 0, 9999, 0) as qty,'
            . '      IF(SUM(stock.is_in_stock) > 0, 1, 0) as is_in_stock,'
            . '      IF(SUM(stock.backorders) > 0, 1, 0) as backorders'
            . '    FROM ' . $stockTable . ' as stock'
            . '    JOIN ' . $storesTable . ' as stores'
            . '      ON stock.location_id = stores.location_id'
            . '    JOIN ' . $locationsTable . ' as location'
            . '      ON stock.location_id = location.id'
            . '    JOIN ' . $coreCatalogProductEntityTable . ' as product_entity'
            . '      ON stock.product_id = product_entity.entity_id'
            . '    WHERE'
            . '      location.status = 1'
            . '      AND product_entity.type_id = "virtual"';

        if (is_array($productIds)) {
            $query .= '      AND stock.product_id IN (' . implode(',', $productIds) . ')';
        }

        $query .= '    GROUP BY CONCAT(stores.store_id, "_", stock.product_id)';


        return $query;
    }

    /**
     * A select query to retrieve the global stock status index data of simple products.
     *
     * @param bool|array $productIds Product IDs to reindex, if a non-array is provided we reindex all products.
     *
     * @return string
     */
    public function getGlobalStockStatusIndexSelectQuery($productIds = FALSE)
    {
        $stockTable                    = Mage::getModel('core/resource')->getTableName('demac_multilocationinventory/stock');
        $locationsTable                = Mage::getModel('core/resource')->getTableName('demac_multilocationinventory/location');
        $coreCatalogProductEntityTable = Mage::getModel('core/resource')->getTableName('catalog/product');

        $query =
            '    SELECT'
            . '      0 as store_id,'
            . '      stock.product_id as product_id,'
            . '      IF(SUM(stock.is_in_stock) > 0, 9999, 0) as qty,'
            . '      IF(SUM(stock.is_in_stock) > 0, 1, 0) as is_in_stock,'
            . '      IF(SUM(stock.backorders) > 0, 1, 0) as backorders'
            . '    FROM ' . $stockTable . ' as stock'
            . '    JOIN ' . $locationsTable . ' as location'
            . '      ON stock.location_id = location.id'
            . '    JOIN ' . $coreCatalogProductEntityTable . ' as product_entity'
            . '      ON stock.product_id = product_entity.entity_id'
            . '    WHERE'
            . '      location.status = 1'
            . '      AND product_entity.type_id = "virtual"';

        if (is_array($productIds)) {
            $query .= '      AND stock.product_id IN (' . implode(',', $productIds) . ')';
        }

        $query .= '    GROUP BY stock.product_id';


        return $query;
    }
}

?>