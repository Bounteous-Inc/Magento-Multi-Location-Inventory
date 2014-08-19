<?php

/**
 * Class Demac_MultiLocationInventory_Helper_Indexer
 */
class Demac_MultiLocationInventory_Helper_Indexer extends Mage_Core_Helper_Abstract
{
    /**
     * Get stock status select queries.
     *
     * @param bool|array $productIds
     * @param bool       $union Determines if we should return one query string using UNION or multiple queries in an array.
     *
     * @return array|string
     */
    public function getAllStockStatusIndexSelects($productIds = false, $union = true)
    {
        $stockStatusIndexSelect = array();
        foreach ($this->getAllStockStatusIndexes() as $indexerModel) {
            $stockStatusIndexSelect[] = $indexerModel->getStockStatusIndexSelectQuery($productIds);
            $stockStatusIndexSelect[] = $indexerModel->getGlobalStockStatusIndexSelectQuery($productIds);
        }

        if($union) {
            return implode(' UNION ', $stockStatusIndexSelect);
        } else {
            return $stockStatusIndexSelect;
        }
    }

    /**
     * Get all stock status indexes from config
     *
     * @return array|string
     */
    public function getAllStockStatusIndexes()
    {
        $indexesConfig = Mage::getConfig()->getNode('global/mli_index_types')->asArray();
        foreach ($indexesConfig as &$indexerModel) {
            $indexerModel = Mage::getModel($indexerModel);
            if(!$indexerModel instanceof Demac_MultiLocationInventory_Model_Stock_Status_Index_Interface) {
                Mage::throwException('Invalid Indexer Model ' . get_class($indexes) . ', does not implement Demac_MultiLocationInventory_Model_Stock_Status_Index_Interface. ');
            }
        }

        return $indexesConfig;
    }

    /**
     * A generic update query for inserting into the stock status index based on a provided select query.
     *
     * The select query should return data in the same format as the
     *
     * @param string $selectQuery
     *
     * @return string
     */
    public function getUpdateStockStatusIndexQuery($selectQuery)
    {
        $stockStatusIndexTable = Mage::getModel('core/resource')->getTableName('demac_multilocationinventory/stock_status_index');

        return
            'UPDATE ' . $stockStatusIndexTable . ' dest,'
            . '  ('
            . $selectQuery
            . '  ) src'
            . '  SET'
            . '    dest.qty = src.qty,'
            . '    dest.is_in_stock = src.is_in_stock,'
            . '    dest.backorders = src.backorders,'
            . '    dest.manage_stock = src.manage_stock'
            . '  WHERE'
            . '    dest.store_id = src.store_id'
            . '    AND dest.product_id = src.product_id;';
    }


    /**
     * Get Global Stock Status Select (for use in updates)
     *
     * @return string
     */
    protected function getGlobalStockStatusSelectQuery($productIds = false, $additionalFields = array())
    {
        $fields = array('product_id', 'qty', 'is_in_stock');
        $fields = array_merge($fields, $additionalFields);

        $stockStatusCollection = Mage::getModel('demac_multilocationinventory/stock_status_index')
            ->getCollection();
        $stockStatusCollection
            ->addFieldToSelect($fields)
            ->addFieldToFilter('store_id', 0);
        if($productIds !== false) {
            $stockStatusCollection
                ->addFieldToFilter(
                    'product_id',
                    array(
                        'in' => $productIds
                    )
                );
        }

        return $stockStatusCollection->getSelectSql(true);
    }

    /**
     * Get core stock status update query.
     *
     * @param bool|array $productIds
     *
     * @return array|string
     */
    public function getUpdateCoreStockStatusQuery($productIds = false)
    {

        $stockStatusIndexTable = Mage::getModel('core/resource')->getTableName('demac_multilocationinventory/stock_status_index');
        $coreStockStatusTable  = Mage::getModel('core/resource')->getTableName('cataloginventory/stock_status');
        $query                 =
            'UPDATE'
            . '  ' . $coreStockStatusTable . ' dest,'
            . '  (' . $this->getGlobalStockStatusSelectQuery($productIds) . ') src '
            . '  SET dest.qty = src.qty,'
            . '    dest.stock_status = src.is_in_stock'
            . '  WHERE dest.product_id = src.product_id';

        return $query;
    }

    /**
     * Get core stock item update query.
     *
     * @param bool|array $productIds
     *
     * @return array|string
     */
    public function getUpdateCoreStockItemQuery($productIds = false)
    {
        $stockStatusIndexTable = Mage::getModel('core/resource')->getTableName('demac_multilocationinventory/stock_status_index');
        $coreStockItemTable    = Mage::getModel('core/resource')->getTableName('cataloginventory/stock_item');
        $query                 =
            'UPDATE'
            . '  ' . $coreStockItemTable . ' dest,'
            . '  (' . $this->getGlobalStockStatusSelectQuery($productIds, array('backorders')) . ') src '
            . '  SET dest.qty = src.qty,'
            . '    dest.is_in_stock = src.is_in_stock,'
            . '    dest.backorders = src.backorders'
            . '  WHERE dest.product_id = src.product_id';

        return $query;
    }

}
