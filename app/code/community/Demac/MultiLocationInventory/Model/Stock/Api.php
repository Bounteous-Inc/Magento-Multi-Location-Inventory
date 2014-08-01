<?php

/**
 * Class Demac_MultiLocationInventory_Model_Stock_Api
 */
class Demac_MultiLocationInventory_Model_Stock_Api extends Mage_Catalog_Model_Api_Resource
{

    /**
     * Create stock item.
     *
     * @param $data
     *
     * @return mixed
     */
    public function create($data)
    {
        try {
            $data = (array) $data;

            $product = $this->_getProduct($data['product_id']);

            if(!$product->getId()) {
                $this->_fault('not_exists');
            }

            if($product->getTypeId() != Mage_Catalog_Model_Product_Type::TYPE_SIMPLE) {
                $this->_fault('data_invalid', 'Invalid Product Type: Only Simple Allowed');
            }

            $stockData = array();

            $stockData['location_id'] = $data['location_id'];
            $stockData['product_id']  = $data['product_id'];

            $fieldArray = array(
                'qty', 'is_in_stock', 'manage_stock', 'use_config_manage_stock', 'use_config_backorders', 'backorders'
            );

            foreach ($fieldArray as $field) {
                if(isset($data[$field])) {
                    $stockItem[$field] = $data[$field];
                }
            }

            $stockItem = Mage::getModel('demac_multilocationinventory/stock')
                ->addData($stockData)
                ->save();
            $id        = $stockItem->getId();
            $stockItem->clearInstance();


        } catch (Exception $e) {
            $this->_fault('data_invalid', $e->getMessage());
        }

        return $id;
    }

    /**
     * Bulk create stock items.
     *
     * @param $productData
     *
     * @return bool
     */
    public function multiCreate($productData)
    {

        $productData = (array) $productData;

        foreach ($productData as $data) {
            $this->create($data);
        }

        return true;
    }

    /**
     * Get info on stock item(s)
     *
     * @param $ids
     *
     * @return array
     */
    public function info($ids)
    {
        $ids       = (array) $ids;
        $stockItem = Mage::getModel('demac_multilocationinventory/stock')->loadByProduct($ids['location_id'], $ids['product_id']);

        if(!$stockItem->getId()) {
            $this->_fault('not_exists');
            // No item found
        }

        return $stockItem->toArray();
        // We can use only simple PHP data types in webservices.
    }

    /**
     * Get items within a certain criteria
     *
     * @param $locationId
     * @param $filters
     *
     * @return array
     */
    public function items($locationId, $filters)
    {
        $collection = Mage::getResourceModel('demac_multilocationinventory/stock_collection');
        if(isset($locationId) && !is_null($locationId)) {
            $collection->addFieldToFilter('location_id', $locationId);
        }
        $collection->addProductData();

        if(is_array($filters)) {
            try {
                foreach ($filters as $field => $value) {
                    $collection->addFieldToFilter($field, $value);
                }
            } catch (Mage_Core_Exception $e) {
                $this->_fault('filters_invalid', $e->getMessage());
                // If we are adding filter on non-existent attribute
            }
        }

        $result = $this->getResults($collection);

        return $result;
    }

    /**
     * Update stock item
     *
     * @param $data
     *
     * @return mixed
     */
    public function update($data)
    {
        try {
            $data = (array) $data;

            $stockItem = Mage::getModel('demac_multilocationinventory/stock')->loadByProduct($data['location_id'], $data['product_id']);

            if(!$stockItem->getId()) {
                return $this->create($data);
                // No item found
            }

            $fieldArray = array(
                'qty', 'is_in_stock', 'manage_stock', 'use_config_manage_stock', 'use_config_backorders', 'backorders'
            );

            $stockData = array();

            foreach ($fieldArray as $field) {
                if(isset($data[$field])) {
                    $stockItem[$field] = $data[$field];
                }
            }

            $stockItem->addData($stockData)
                ->save();
            $id = $stockItem->getId();
            $stockItem->clearInstance();

        } catch (Exception $e) {
            $this->_fault('data_invalid', $e->getMessage());
        }

        return $id;
    }

    /**
     * Update multiple stock items.
     *
     * @param $stockDataArray
     *
     * @return bool
     */
    public function multiUpdate($stockDataArray)
    {

        $stockDataArray = (array) $stockDataArray;

        foreach ($stockDataArray as $stockData) {
            $this->update($stockData);
        }

        return true;
    }

    /**
     * Remove stock item
     *
     * @param $ids
     *
     * @return bool
     */
    public function remove($ids)
    {
        $ids       = (array) $ids;
        $stockItem = Mage::getModel('demac_multilocationinventory/stock')->loadByProduct($ids['location_id'], $ids['product_id']);

        if(!$stockItem->getId()) {
            $this->_fault('not_exists');
            // No item found
        }

        try {
            $stockItem->delete();
        } catch (Mage_Core_Exception $e) {
            $this->_fault('not_deleted', $e->getMessage());
            // Some errors while deleting.
        }

        return true;
    }

    /**
     * Remove multiple stock items
     *
     * @param $idArray
     *
     * @return bool
     */
    public function multiRemove($idArray)
    {

        $idArray = (array) $idArray;

        foreach ($idArray as $ids) {
            $this->remove($ids);
        }

        return true;
    }

    /**
     * Get stock item collection as array.
     *
     * @param Demac_MultiLocationInventory_Model_Resource_Stock_Collection $collection
     *
     * @return array
     *
     * @TODO Is there a more efficient way to do this / is it necessary? Does $collection->toArray() work for this?
     */
    public function getResults($collection)
    {
        $result = array();
        foreach ($collection as $stockItem) {
            $result[] = $stockItem->toArray();
        }

        return $result;
    }

}