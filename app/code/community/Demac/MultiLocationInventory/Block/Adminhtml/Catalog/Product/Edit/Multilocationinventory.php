<?php

/**
 * Class Demac_MultiLocationInventory_Block_Adminhtml_Catalog_Product_Edit_Multilocationinventory
 */
class Demac_MultiLocationInventory_Block_Adminhtml_Catalog_Product_Edit_Multilocationinventory
    extends Mage_Adminhtml_Block_Widget
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{

    /**
     * @var array Inventory for each location.
     */
    private $locations = array();

    /**
     * @var float Total quantity within the current scope.
     */
    private $scopeInventory = 0.0;

    /**
     * @var float Total quantity available for this product globally.
     */
    private $globalInventory = 0.0;

    /**
     * Init the tab and set it's template
     */
    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('demac/catalog_multilocationinventory.phtml');
        $this->loadLocationsInventoriesData();
        $this->loadGlobalInventory($this->getProductId());
    }

    /**
     * Returns the product id.
     *
     * @return int
     */
    protected function getProductId()
    {
        return Mage::app()->getRequest()->getParam('id');
    }

    /**
     * Returns the current store view id or NULL.
     *
     * @return int
     */
    protected function getStoreViewId()
    {
        return Mage::app()->getRequest()->getParam('store');
    }

    /**
     * Returns the tab's label.
     *
     * @return string
     */
    public function getTabLabel()
    {
        return $this->__('Multi Location Inventory');
    }

    /**
     * Returns the tab's title.
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->__('Multi Location Inventory');
    }

    /**
     * Returns true/false if the tab can or can't be displayed.
     *
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Returns true/false if that tab should be hidden.
     *
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }


    /**
     * Get stock details for each location.
     *
     * @return array
     */
    public function getLocationsInventories()
    {
        return $this->locations;
    }

    /**
     * Get inventory within the current store view scope.
     *
     * @return array
     */
    public function getScopeInventory()
    {
        return $this->scopeInventory;
    }

    /**
     * Get global inventory.
     *
     * @return array
     */
    public function getGlobalInventory()
    {
        return $this->globalInventory;
    }

    /**
     * Load stock details for each location.
     */
    private function loadLocationsInventoriesData()
    {
        $productId   = $this->getProductId();
        $storeViewId = $this->getStoreViewId();

        $locationStockCollection = Mage::getModel('demac_multilocationinventory/location')
            ->getCollection()
            ->joinStockDataOnProductAndStoreView($productId, $storeViewId);

        $locations = array();
        foreach ($locationStockCollection as $locationStock) {
            $locationStock->setQty(floatval($locationStock->getQty()));
            $locationStock->setMinQty(floatval($locationStock->getMinQty()));
            $this->scopeInventory += $locationStock->getQty();
            array_push($locations, $locationStock->toArray());
        }

        $this->locations = $locations;
    }

    /**
     * Load global inventory.
     */
    private function loadGlobalInventory($productId)
    {
        $this->globalInventory = Mage::getModel('demac_multilocationinventory/stock')->getGlobalInventory($productId);
    }
}