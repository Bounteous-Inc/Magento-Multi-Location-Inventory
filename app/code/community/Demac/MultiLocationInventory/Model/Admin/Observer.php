<?php

/**
 * Class Demac_MultiLocationInventory_Model_Admin_Observer
 */
class Demac_MultiLocationInventory_Model_Admin_Observer
{

    /**
     * Used to store input data from the 'multilocationinventory' field so that we can manipulate it without
     * manipulating $_POST.
     * @var array
     */
    private $inputData = array();

    /**
     * Fires when a product is saved, used to the multi location inventory tab data.
     */
    public function multiLocationInventoryProductSave($observer)
    {
        $productId       = $observer->getEvent()->getProduct()->getId();
        $this->inputData = Mage::app()->getRequest()->getPost('multilocationinventory');
        if ($productId) {
            if ($this->inputData) {
                $input_multiLocationInventoryDataIds = array_keys($this->inputData);

                //Get select collection to find all existing inventory data to update...
                $multilocationinventoryCollection = Mage::getModel('demac_multilocationinventory/stock')
                    ->getCollection()
                    ->addFieldToSelect(array('stock_id', 'location_id'))
                    ->addFieldToFilter(
                        'location_id',
                        array(
                            'in' => $input_multiLocationInventoryDataIds
                        )
                    )
                    ->addFieldToFilter(
                        'product_id',
                        array(
                            'eq' => $productId
                        )
                    );

                //Iterate through the collection of inventory data to update...
                if ($multilocationinventoryCollection->getSize() > 0) {
                    Mage::getSingleton('core/resource_iterator')->walk(
                        $multilocationinventoryCollection->getSelect(),
                        array(
                            array($this, '_updateInventoryDataIterate')
                        ),
                        array(
                            'invoker' => $this
                        )
                    );
                }

                //Create remaining stock data
                foreach ($this->inputData as $locationId => $locationData) {
                    $_stock = array(
                        'location_id' => $locationId,
                        'product_id'  => $productId,
                    );
                    $this->_updateInventoryData($_stock);
                }
            }
            Mage::getModel('demac_multilocationinventory/indexer')->reindex($productId);
        }
    }

    /**
     * Wrapper for the update stock iterator to push data into other functions in a generic format.
     *
     * @param $args
     */
    public function _updateInventoryDataIterate($args)
    {
        $this->_updateInventoryData($args['row']);
    }

    /**
     * Load a stock object based on the passed in data, update it based on input data then save.
     *
     * @param $_stockData
     */
    public function _updateInventoryData($_stockData)
    {
        $_stock     = Mage::getModel('demac_multilocationinventory/stock')->setData($_stockData);
        $locationId = $_stock->getLocationId();
        if (isset($this->inputData[$locationId])) {
            $inputStock = $this->inputData[$locationId];
            $_stock->setQty($inputStock['quantity']);
            $_stock->setBackorders($inputStock['backorders']);
            $_stock->setIsInStock($inputStock['is_in_stock']);
            $_stock->save();
            unset($this->inputData[$locationId]);
        }
    }
}