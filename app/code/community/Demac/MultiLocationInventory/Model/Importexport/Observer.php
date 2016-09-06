<?php

/**
 * Class Demac_MultiLocationInventory_Model_Importexport_Observer
 */
class Demac_MultiLocationInventory_Model_Importexport_Observer
{
    const COL_SKU            = 'sku';
    const COL_STOCK_LOCATION = 'stock_location';

    /** @var array */
    protected $locationIds = array();

    /** @var array */
    protected $defaultStockData = array(
        'manage_stock'              => 1,
        'use_config_manage_stock'   => 1,
        'qty'                       => 0,
        'backorders'                => 0,
        'use_config_backorders'     => 1,
        'is_in_stock'               => 0
    );

    /** @var string */
    protected $stockTable;

    /** @var array */
    protected $stockData = array();

    /**
     * Build up the locations for use later on
     */
    public function __construct()
    {
        $this->_initLocations();

        $this->stockTable = Mage::getResourceModel('demac_multilocationinventory/stock')->getMainTable();
    }

    /**
     * Load all location IDs
     *
     * @return $this
     */
    private function _initLocations()
    {
        /** @var Demac_MultiLocationInventory_Model_Resource_Location_Collection $collection */
        $collection = Mage::getModel('demac_multilocationinventory/location')->getCollection();
        $collection->addFieldToSelect('id');
        $collection->addFieldToSelect('code');

        /** @var Demac_MultiLocationInventory_Model_Location $location */
        foreach($collection as $location) {
            $this->locationIds[$location->getCode()] = $location->getId();
        }

        return $this;
    }

    /**
     * catalog_product_import_finish_before
     *
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function catalogProductImportFinishBefore(Varien_Event_Observer $observer)
    {
        /** @var Mage_ImportExport_Model_Import_Entity_Product $adapter */
        $adapter = $observer->getData('adapter');
        if(!$adapter) {
            return $this;
        }

        // Only process replace or append imports
        if (Mage_ImportExport_Model_Import::BEHAVIOR_DELETE == $adapter->getBehavior()) {
            return $this;
        }

        $newSku = $adapter->getNewSku();
        $sku    = false;

        while ($bunch = $adapter->getNextBunch()) {
            $this->resetStockData();

            // Format bunch to stock data rows
            foreach ($bunch as $rowNum => $rowData) {
                $this->filterRowData($rowData);
                if (!$adapter->isRowAllowedToImport($rowData, $rowNum)) {
                    continue;
                }

                $rowScope = $adapter->getRowScope($rowData);
                // Let's reset the sku value on every default scope
                if (Mage_ImportExport_Model_Import_Entity_Product::SCOPE_DEFAULT == $rowScope) {
                    $sku = $rowData[self::COL_SKU];
                } elseif (null === $sku) {
                    continue;
                }

                // If we have no sku we have nothing to do
                if(!$sku) {
                    continue;
                }

                if (!isset($rowData[self::COL_STOCK_LOCATION])) {
                    continue;
                }

                $locationCode = $rowData[self::COL_STOCK_LOCATION];
                // Check to see if we have this location code in the DB
                if(!$this->isStoredLocation($locationCode)) {
                    continue;
                }

                $productId = $newSku[$sku]['entity_id'];
                $this->buildStockData($locationCode, $productId, $rowData);
            }

            // Insert rows
            if ($this->stockData) {
                $adapter->getConnection()->insertOnDuplicate($this->stockTable, $this->stockData);
            }
        }

        return $this;
    }

    /**
     * Removes empty keys in case value is null or empty string
     *
     * @param array $rowData
     */
    protected function filterRowData(&$rowData)
    {
        $rowData = array_filter($rowData, 'strlen');
        // Exceptions - for sku - put them back in
        if (!isset($rowData[self::COL_SKU])) {
            $rowData[self::COL_SKU] = null;
        }
    }

    /**
     * Check to see if we have a given location code in the database
     *
     * @param string $locationCode
     * @return bool
     */
    private function isStoredLocation($locationCode)
    {
        return array_key_exists($locationCode, $this->locationIds);
    }

    /**
     * Build the stock data array for a given location/product combination
     *
     * @param string $locationCode
     * @param int $productId
     * @param array $rowData
     */
    private function buildStockData($locationCode, $productId, $rowData)
    {
        $locationId = $this->locationIds[$locationCode];

        $row = array();
        $row['location_id'] = $locationId;
        $row['product_id']  = $productId;
        if (isset($rowData['qty'])) {
            $row['qty'] = $rowData['qty'];
        }
        if (isset($rowData['backorders'])) {
            $row['backorders'] = $rowData['backorders'];
        }

        /** @var $stockItem Demac_MultiLocationInventory_Model_Stock */
        $stockItem = Mage::getModel('demac_multilocationinventory/stock');
        $stockItem->loadByProduct($locationId, $productId);
        $existStockData = $stockItem->getData();

        $row = array_merge(
            $this->defaultStockData,
            array_intersect_key($existStockData, $this->defaultStockData),
            array_intersect_key($rowData, $this->defaultStockData),
            $row
        );

        $stockItem->setData($row);

        $this->stockData[] = $stockItem->unsetOldData()->getData();
    }

    /**
     * Reset the internal stock data array
     */
    private function resetStockData()
    {
        $this->stockData = array();
    }
}