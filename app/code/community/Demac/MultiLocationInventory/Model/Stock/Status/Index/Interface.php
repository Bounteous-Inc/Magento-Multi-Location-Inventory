<?php

/**
 * Interface Demac_MultiLocationInventory_Model_Stock_Status_Index_Interface
 */
interface Demac_MultiLocationInventory_Model_Stock_Status_Index_Interface
{
    /**
     * A select query to retrieve the stock status index data.
     *
     * @param bool|array $productIds Product IDs to reindex, if a non-array is provided we reindex all products.
     *
     * @return string
     */
    public function getStockStatusIndexSelectQuery($productIds = false);

    /**
     * A select query to retrieve the global stock status index data.
     *
     * @param bool|array $productIds Product IDs to reindex, if a non-array is provided we reindex all products.
     *
     * @return string
     */
    public function getGlobalStockStatusIndexSelectQuery($productIds = false);
}

?>