<?php

/**
 * Class Demac_MultiLocationInventory_Model_Stock_Status_Index_Simple
 */
class Demac_MultiLocationInventory_Model_Stock_Status_Index_Simple
    implements Demac_MultiLocationInventory_Model_Stock_Status_Index_Interface
{
    protected $productType = 'simple';

    protected function buildIndexSelectQuery($fields, $from, $fromAs, $joins, $where, $group) {
        $querySelect = $this->buildIndexSelectQuery_Select($fields);
        $queryFrom = $this->buildIndexSelectQuery_From($from, $fromAs);
        $queryJoins = $this->buildIndexSelectQuery_Joins($joins);
        $queryWhere = $this->buildIndexSelectQuery_Where($where);
        $queryGroup = $this->buildIndexSelectQuery_Group($group);
        return $querySelect . ' ' . $queryFrom . ' ' . $queryJoins . ' ' . $queryWhere . ' ' . $queryGroup;
    }
    
    private function buildIndexSelectQuery_Select($fields) {
        $query = 'SELECT ';
        foreach($fields as $fieldAs => $fieldSelect) {
            $query .= $fieldSelect . ' as ' . $fieldAs . ',';
        }
        return rtrim($query, ',');
    }
    
    private function buildIndexSelectQuery_From($from, $fromAs) {
        return 'FROM ' . $from . ' as ' . $fromAs;
    }

    private function buildIndexSelectQuery_Joins($joins) {
        $query = '';
        foreach($joins as $joinArr) {
            $query .= $joinArr['type'] . ' ' . $joinArr['tableName'] . ' as ' . $joinArr['tableAs'];
            if(isset($joinArr['on'])) {
                $query .= ' ON ' . $joinArr['on'];
            }
            $query .= ' ';
        }
        return trim($query);
    }

    private function buildIndexSelectQuery_Where($where) {
        return 'WHERE ' . $where;
    }

    private function buildIndexSelectQuery_Group($group) {
        return 'GROUP BY ' . $group;
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
        $stockTable                    = Mage::getModel('core/resource')->getTableName('demac_multilocationinventory/stock');
        $storesTable                   = Mage::getModel('core/resource')->getTableName('demac_multilocationinventory/stores');
        $locationsTable                = Mage::getModel('core/resource')->getTableName('demac_multilocationinventory/location');
        $coreCatalogProductEntityTable = Mage::getModel('core/resource')->getTableName('catalog/product');
        
        $selectFields = array(
            'store_id' => 'stores.store_id',
            'product_id' => 'stock.product_id',
            'qty' => 'IF(GROUP_CONCAT(stock.manage_stock) LIKE "%0%", 1, SUM(IF(stock.is_in_stock = 1, stock.qty, 0)))',
            'is_in_stock' => 'IF(GROUP_CONCAT(stock.manage_stock) LIKE "%0%", 1, IF(SUM(stock.is_in_stock) > 0, 1, 0))',
            'backorders' => 'IF(SUM(stock.backorders) > 0, 1, 0)',
            'manage_stock' => 'IF(GROUP_CONCAT(stock.manage_stock) LIKE "%0%", 0, 1)'
        );

        $selectJoins = array(
            array(
                'type' => 'JOIN',
                'tableName' => $storesTable,
                'tableAs' => 'stores',
                'on' => 'stock.location_id = stores.location_id'
            ),
            array(
                'type' => 'JOIN',
                'tableName' => $locationsTable,
                'tableAs' => 'location',
                'on' => 'stock.location_id = location.id'
            ),
            array(
                'type' => 'JOIN',
                'tableName' => $coreCatalogProductEntityTable,
                'tableAs' => 'product_entity',
                'on' => 'stock.product_id = product_entity.entity_id'
            )
        );

        $selectWhere = 'location.status = 1 AND product_entity.type_id = "' . $this->productType . '"';
        if(is_array($productIds)) {
            $selectWhere .= ' AND stock.product_id IN (' . implode(',', $productIds) . ')';
        }

        $selectGroup = 'CONCAT(stores.store_id, "_", stock.product_id)';

        $query = $this->buildIndexSelectQuery($selectFields, $stockTable, 'stock', $selectJoins, $selectWhere, $selectGroup);
        
        die($query);

        return $query;
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
        $stockTable                    = Mage::getModel('core/resource')->getTableName('demac_multilocationinventory/stock');
        $locationsTable                = Mage::getModel('core/resource')->getTableName('demac_multilocationinventory/location');
        $coreCatalogProductEntityTable = Mage::getModel('core/resource')->getTableName('catalog/product');

        $query =
            '    SELECT'
            . '      0 as store_id,'
            . '      stock.product_id as product_id,'
            . '      IF(GROUP_CONCAT(stock.manage_stock) LIKE "%0%", 1, SUM(IF(stock.is_in_stock = 1, stock.qty, 0))) as qty,'
            . '      IF(GROUP_CONCAT(stock.manage_stock) LIKE "%0%", 1, IF(SUM(stock.is_in_stock) > 0, 1, 0)) as is_in_stock,'
            . '      IF(SUM(stock.backorders) > 0, 1, 0) as backorders,'
            . '      IF(GROUP_CONCAT(stock.manage_stock) LIKE "%0%", 0, 1) as manage_stock'
            . '    FROM ' . $stockTable . ' as stock'
            . '    JOIN ' . $locationsTable . ' as location'
            . '      ON stock.location_id = location.id'
            . '    JOIN ' . $coreCatalogProductEntityTable . ' as product_entity'
            . '      ON stock.product_id = product_entity.entity_id'
            . '    WHERE'
            . '      location.status = 1'
            . '      AND product_entity.type_id = "' . $this->productType . '"';

        if(is_array($productIds)) {
            $query .= '      AND stock.product_id IN (' . implode(',', $productIds) . ')';
        }

        $query .= '    GROUP BY stock.product_id';


        return $query;
    }
}
