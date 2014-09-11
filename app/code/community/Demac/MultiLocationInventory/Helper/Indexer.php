<?php

/**
 * Class Demac_MultiLocationInventory_Helper_Indexer
 */
class Demac_MultiLocationInventory_Helper_Indexer extends Mage_Core_Helper_Abstract
{


    /**
     * Get Global Stock Status Select (for use in updates)
     *
     * @param bool  $productIds
     * @param array $additionalFields
     *
     * @return string
     */
    protected function getGlobalStockStatusSelectQuery($productIds = false, $additionalFields = array())
    {
        $fields = array('product_id', 'qty', 'is_in_stock', 'manage_stock');
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
     *
     * @TODO Update to use varien query builder.
     */
    public function getUpdateCoreStockStatusQuery($productIds = false)
    {

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
     *
     * @TODO Update to use varien query builder.
     */
    public function getUpdateCoreStockItemQuery($productIds = false)
    {
        $coreStockItemTable    = Mage::getModel('core/resource')->getTableName('cataloginventory/stock_item');
        $query                 =
            'UPDATE'
            . '  ' . $coreStockItemTable . ' dest,'
            . '  (' . $this->getGlobalStockStatusSelectQuery($productIds, array('backorders')) . ') src '
            . '  SET dest.qty = src.qty,'
            . '    dest.is_in_stock = src.is_in_stock,'
            . '    dest.backorders = src.backorders,'
            . '    dest.manage_stock = src.manage_stock'
            . '  WHERE dest.product_id = src.product_id';

        return $query;
    }

}
