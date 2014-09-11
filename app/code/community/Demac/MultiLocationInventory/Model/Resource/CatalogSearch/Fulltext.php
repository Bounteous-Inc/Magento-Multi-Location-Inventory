<?php

/**
 * @author    Allan MacGregor - Magento Practice Lead <amacgregor@demacmedia.com>
 * @company   Demac Media Inc.
 * @copyright 2010-2014 Demac Media Inc.
 */
class Demac_MultiLocationInventory_Model_Resource_CatalogSearch_Fulltext extends Mage_CatalogSearch_Model_Resource_Fulltext
{
    /**
     * Retrieve searchable products per store using the multilocation inventory index
     *
     * @param int       $storeId
     * @param array     $staticFields
     * @param array|int $productIds
     * @param int       $lastProductId
     * @param int       $limit
     *
     * @return array
     */
    protected function _getSearchableProducts($storeId, array $staticFields, $productIds = null, $lastProductId = 0,
                                              $limit = 100)
    {
        $websiteId    = Mage::app()->getStore($storeId)->getWebsiteId();
        $writeAdapter = $this->_getWriteAdapter();
        $select = $writeAdapter->select();
        $select
            ->useStraightJoin(true)
            ->from(
                array('e' => $this->getTable('catalog/product')),
                array_merge(array('entity_id', 'type_id'), $staticFields)
            )
            ->join(
                array('website' => $this->getTable('catalog/product_website')),
                $writeAdapter->quoteInto(
                    'website.product_id=e.entity_id AND website.website_id=?',
                    $websiteId
                ),
                array()
            )
            ->join(
                array('stock_status' => $this->getTable('demac_multilocationinventory/stock_status_index')),
                $writeAdapter->quoteInto(
                    'stock_status.product_id=e.entity_id AND stock_status.store_id=?',
                    $storeId
                ),
                array('in_stock' => 'is_in_stock')
            );
        if(!is_null($productIds)) {
            $select->where('e.entity_id IN(?)', $productIds);
        }
        $select->where('e.entity_id>?', $lastProductId)
            ->limit($limit)
            ->order('e.entity_id');
        $result = $writeAdapter->fetchAll($select);
        return $result;
    }
}