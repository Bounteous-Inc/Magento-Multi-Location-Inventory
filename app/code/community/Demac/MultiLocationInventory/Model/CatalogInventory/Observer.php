<?php

class Demac_MultiLocationInventory_Model_CatalogInventory_Observer extends Mage_CatalogInventory_Model_Observer
{
    /**
     * Check product inventory data when quote item quantity declaring
     *
     * Contains only minor changes so that instanceof allows our overridden class as well.
     *
     * @param  Varien_Event_Observer $observer
     *
     * @return Mage_CatalogInventory_Model_Observer
     */
    public function checkQuoteItemQty($observer)
    {
        $quoteItem = $observer->getEvent()->getItem();
        /* @var $quoteItem Mage_Sales_Model_Quote_Item */
        if(!$quoteItem || !$quoteItem->getProductId() || !$quoteItem->getQuote()
            || $quoteItem->getQuote()->getIsSuperMode()
        ) {
            return $this;
        }

        /**
         * Get Qty
         */
        $qty = $quoteItem->getQty();

        /**
         * Check if product in stock. For composite products check base (parent) item stosk status
         */
        $stockItem = $quoteItem->getProduct()->getStockItem();

        $parentStockItem = false;
        if($quoteItem->getParentItem()) {
            $parentStockItem = $quoteItem->getParentItem()->getProduct()->getStockItem();
        }
        if($stockItem) {
            if(!$stockItem->getIsInStock() || ($parentStockItem && !$parentStockItem->getIsInStock())) {
                $quoteItem->addErrorInfo(
                    'cataloginventory',
                    Mage_CatalogInventory_Helper_Data::ERROR_QTY,
                    Mage::helper('cataloginventory')->__('This product is currently out of stock.')
                );
                $quoteItem->getQuote()->addErrorInfo(
                    'stock',
                    'cataloginventory',
                    Mage_CatalogInventory_Helper_Data::ERROR_QTY,
                    Mage::helper('cataloginventory')->__('Some of the products are currently out of stock.')
                );

                return $this;
            } else {
                // Delete error from item and its quote, if it was set due to item out of stock
                $this->_removeErrorsFromQuoteAndItem($quoteItem, Mage_CatalogInventory_Helper_Data::ERROR_QTY);
            }
        }

        /**
         * Check item for options
         */
        $options = $quoteItem->getQtyOptions();
        if($options && $qty > 0) {
            $qty = $quoteItem->getProduct()->getTypeInstance(true)->prepareQuoteItemQty($qty, $quoteItem->getProduct());
            $quoteItem->setData('qty', $qty);

            if($stockItem) {
                $result = $stockItem->checkQtyIncrements($qty);
                if($result->getHasError()) {
                    $quoteItem->addErrorInfo(
                        'cataloginventory',
                        Mage_CatalogInventory_Helper_Data::ERROR_QTY_INCREMENTS,
                        $result->getMessage()
                    );

                    $quoteItem->getQuote()->addErrorInfo(
                        $result->getQuoteMessageIndex(),
                        'cataloginventory',
                        Mage_CatalogInventory_Helper_Data::ERROR_QTY_INCREMENTS,
                        $result->getQuoteMessage()
                    );
                } else {
                    // Delete error from item and its quote, if it was set due to qty problems
                    $this->_removeErrorsFromQuoteAndItem(
                        $quoteItem,
                        Mage_CatalogInventory_Helper_Data::ERROR_QTY_INCREMENTS
                    );
                }
            }

            $quoteItemHasErrors = false;
            foreach ($options as $option) {
                $optionValue = $option->getValue();
                /* @var $option Mage_Sales_Model_Quote_Item_Option */
                $optionQty         = $qty * $optionValue;
                $increaseOptionQty = ($quoteItem->getQtyToAdd() ? $quoteItem->getQtyToAdd() : $qty) * $optionValue;

                $stockItem = $option->getProduct()->getStockItem();

                if($quoteItem->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
                    $stockItem->setProductName($quoteItem->getName());
                }

                /* @var $stockItem Mage_CatalogInventory_Model_Stock_Item */
                if(!$stockItem instanceof Mage_CatalogInventory_Model_Stock_Item && !$stockItem instanceof Demac_MultiLocationInventory_Model_CatalogInventory_Stock_Item) {
                    Mage::throwException(
                        Mage::helper('cataloginventory')->__('The stock item for Product in option is not valid.')
                    );
                }

                /**
                 * define that stock item is child for composite product
                 */
                $stockItem->setIsChildItem(true);
                /**
                 * don't check qty increments value for option product
                 */
                $stockItem->setSuppressCheckQtyIncrements(true);

                $qtyForCheck = $this->_getQuoteItemQtyForCheck(
                    $option->getProduct()->getId(),
                    $quoteItem->getId(),
                    $increaseOptionQty
                );

                $result = $stockItem->checkQuoteItemQty($optionQty, $qtyForCheck, $optionValue);

                if(!is_null($result->getItemIsQtyDecimal())) {
                    $option->setIsQtyDecimal($result->getItemIsQtyDecimal());
                }

                if($result->getHasQtyOptionUpdate()) {
                    $option->setHasQtyOptionUpdate(true);
                    $quoteItem->updateQtyOption($option, $result->getOrigQty());
                    $option->setValue($result->getOrigQty());
                    /**
                     * if option's qty was updates we also need to update quote item qty
                     */
                    $quoteItem->setData('qty', intval($qty));
                }
                if(!is_null($result->getMessage())) {
                    $option->setMessage($result->getMessage());
                    $quoteItem->setMessage($result->getMessage());
                }
                if(!is_null($result->getItemBackorders())) {
                    $option->setBackorders($result->getItemBackorders());
                }

                if($result->getHasError()) {
                    $option->setHasError(true);
                    $quoteItemHasErrors = true;

                    $quoteItem->addErrorInfo(
                        'cataloginventory',
                        Mage_CatalogInventory_Helper_Data::ERROR_QTY,
                        $result->getMessage()
                    );

                    $quoteItem->getQuote()->addErrorInfo(
                        $result->getQuoteMessageIndex(),
                        'cataloginventory',
                        Mage_CatalogInventory_Helper_Data::ERROR_QTY,
                        $result->getQuoteMessage()
                    );
                } elseif(!$quoteItemHasErrors) {
                    // Delete error from item and its quote, if it was set due to qty lack
                    $this->_removeErrorsFromQuoteAndItem($quoteItem, Mage_CatalogInventory_Helper_Data::ERROR_QTY);
                }

                $stockItem->unsIsChildItem();
            }
        } else {
            /* @var $stockItem Mage_CatalogInventory_Model_Stock_Item */
            if(!$stockItem instanceof Mage_CatalogInventory_Model_Stock_Item && !$stockItem instanceof Demac_MultiLocationInventory_Model_CatalogInventory_Stock_Item) {
                Mage::throwException(Mage::helper('cataloginventory')->__('The stock item for Product is not valid.'));
            }

            /**
             * When we work with subitem (as subproduct of bundle or configurable product)
             */
            if($quoteItem->getParentItem()) {
                $rowQty = $quoteItem->getParentItem()->getQty() * $qty;
                /**
                 * we are using 0 because original qty was processed
                 */
                $qtyForCheck = $this->_getQuoteItemQtyForCheck(
                    $quoteItem->getProduct()->getId(),
                    $quoteItem->getId(),
                    0
                );
            } else {
                $increaseQty = $quoteItem->getQtyToAdd() ? $quoteItem->getQtyToAdd() : $qty;
                $rowQty      = $qty;
                $qtyForCheck = $this->_getQuoteItemQtyForCheck(
                    $quoteItem->getProduct()->getId(),
                    $quoteItem->getId(),
                    $increaseQty
                );
            }

            $productTypeCustomOption = $quoteItem->getProduct()->getCustomOption('product_type');
            if(!is_null($productTypeCustomOption)) {
                // Check if product related to current item is a part of grouped product
                if($productTypeCustomOption->getValue() == Mage_Catalog_Model_Product_Type_Grouped::TYPE_CODE) {
                    $stockItem->setProductName($quoteItem->getProduct()->getName());
                    $stockItem->setIsChildItem(true);
                }
            }

            $result = $stockItem->checkQuoteItemQty($rowQty, $qtyForCheck, $qty);

            if($stockItem->hasIsChildItem()) {
                $stockItem->unsIsChildItem();
            }

            if(!is_null($result->getItemIsQtyDecimal())) {
                $quoteItem->setIsQtyDecimal($result->getItemIsQtyDecimal());
                if($quoteItem->getParentItem()) {
                    $quoteItem->getParentItem()->setIsQtyDecimal($result->getItemIsQtyDecimal());
                }
            }

            /**
             * Just base (parent) item qty can be changed
             * qty of child products are declared just during add process
             * exception for updating also managed by product type
             */
            if($result->getHasQtyOptionUpdate()
                && (!$quoteItem->getParentItem()
                    || $quoteItem->getParentItem()->getProduct()->getTypeInstance(true)
                        ->getForceChildItemQtyChanges($quoteItem->getParentItem()->getProduct())
                )
            ) {
                $quoteItem->setData('qty', $result->getOrigQty());
            }

            if(!is_null($result->getItemUseOldQty())) {
                $quoteItem->setUseOldQty($result->getItemUseOldQty());
            }
            if(!is_null($result->getMessage())) {
                $quoteItem->setMessage($result->getMessage());
            }

            if(!is_null($result->getItemBackorders())) {
                $quoteItem->setBackorders($result->getItemBackorders());
            }

            if($result->getHasError()) {
                $quoteItem->addErrorInfo(
                    'cataloginventory',
                    Mage_CatalogInventory_Helper_Data::ERROR_QTY,
                    $result->getMessage()
                );

                $quoteItem->getQuote()->addErrorInfo(
                    $result->getQuoteMessageIndex(),
                    'cataloginventory',
                    Mage_CatalogInventory_Helper_Data::ERROR_QTY,
                    $result->getQuoteMessage()
                );
            } else {
                // Delete error from item and its quote, if it was set due to qty lack
                $this->_removeErrorsFromQuoteAndItem($quoteItem, Mage_CatalogInventory_Helper_Data::ERROR_QTY);
            }
        }

        return $this;
    }


    private $inventoryToRestock = 0;

    /**
     * Return creditmemo items qty to stock..
     *
     * @TODO support a dropdown to pick where inventory is being returned to.
     *
     * @param Varien_Event_Observer $observer
     */
    public function refundOrderInventory($observer)
    {
        /* @var $creditmemo Mage_Sales_Model_Order_Creditmemo */
        $creditmemo = $observer->getEvent()->getCreditmemo();
        $items      = array();
        foreach ($creditmemo->getAllItems() as $item) {
            /* @var $item Mage_Sales_Model_Order_Creditmemo_Item */
            $return = false;
            if($item->hasBackToStock()) {
                if($item->getBackToStock() && $item->getQty()) {
                    $return = true;
                }
            } elseif(Mage::helper('cataloginventory')->isAutoReturnEnabled()) {
                $return = true;
            }
            if($return) {
                $parentOrderId = $item->getOrderItem()->getParentItemId();
                /* @var $parentItem Mage_Sales_Model_Order_Creditmemo_Item */
                $parentItem = $parentOrderId ? $creditmemo->getItemByOrderId($parentOrderId) : false;
                $qty        = $parentItem ? ($parentItem->getQty() * $item->getQty()) : $item->getQty();

                $this->inventoryToRestock = $qty;

                $quoteItemId = $item->getOrderItem()->getQuoteItemId();
                $productId   = $item->getProductId();

                $locationIds = Mage::helper('demac_multilocationinventory/location')->getPrioritizedLocationsForOrderQuoteItem($orderId, $quoteItemId);


                $sourceCollection = Mage::getModel('demac_multilocationinventory/order_stock_source')
                    ->getCollection()
                    ->addFieldToSelect(array('id', 'location_id', 'qty'))
                    ->addFieldToFilter('sales_quote_item_id', $quoteItemId);
                $sourceCollection
                    ->getSelect()
                    ->order('id ASC');
                $this->inventoryToRestock = $qty;
                Mage::getModel('demac_multilocationinventory/resource_iterator')->walk(
                    $sourceCollection->getSelect(),
                    array(
                        array($this, '_loopCancelItem')
                    ),
                    array(
                        'invoker'       => $this,
                        'product_id'    => $productId,
                        'quote_item_id' => $quoteItemId
                    )
                );

                Mage::getModel('demac_multilocationinventory/indexer')->reindex($productId);
            }
        }
    }


    /**
     * Cancel order item
     *
     * @param   Varien_Event_Observer $observer
     *
     * @return  Mage_CatalogInventory_Model_Observer
     */
    public function cancelOrderItem($observer)
    {
        $item = $observer->getEvent()->getItem();

        $children = $item->getChildrenItems();
        $qty      = $item->getQtyOrdered() - max($item->getQtyShipped(), $item->getQtyInvoiced()) - $item->getQtyCanceled();

        $quoteItemId = $item->getQuoteItemId();

        $productId = $item->getProductId();

        if($item->getQuoteItemId() && $qty && empty($children) && $qty) {
            $sourceCollection = Mage::getModel('demac_multilocationinventory/order_stock_source')
                ->getCollection()
                ->addFieldToSelect(array('id', 'location_id', 'qty'))
                ->addFieldToFilter('sales_quote_item_id', $quoteItemId);
            $sourceCollection
                ->getSelect()
                ->order('id ASC');
            $this->inventoryToRestock = $qty;
            Mage::getModel('demac_multilocationinventory/resource_iterator')->walk(
                $sourceCollection->getSelect(),
                array(
                    array($this, '_loopCancelItem')
                ),
                array(
                    'invoker'       => $this,
                    'product_id'    => $productId,
                    'quote_item_id' => $quoteItemId
                )
            );
        }

        Mage::getModel('demac_multilocationinventory/indexer')->reindex($productId);

        return $this;
    }

    /**
     * Return refunded items to stock (iterator method)
     *
     * @param $args Iterator data: invoker / product_id / quote_item_id / row
     *
     * @return bool
     */
    public function _loopCancelItem($args)
    {
        $productId = $args['product_id'];

        $row = $args['row'];

        $locationId      = $row['location_id'];
        $qtyFromLocation = $row['qty'];

        $stockItem = Mage::getModel('demac_multilocationinventory/stock')->loadByProduct($locationId, $productId);

        if($this->inventoryToRestock < $qtyFromLocation) {
            $orderStockSource = Mage::getModel('demac_multilocationinventory/order_stock_source')->load($row['id']);
            $orderStockSource->setQty($orderStockSource->getQty() - $this->inventoryToRestock);
            $orderStockSource->save();
            $stockItem->setQty($stockItem->getQty() + $this->inventoryToRestock);
            $stockItem->save();
            $this->inventoryToRestock = 0;

            //return false to exit loop
            return false;
        } else {
            $orderStockSource = Mage::getModel('demac_multilocationinventory/order_stock_source')->load($row['id']);
            $orderStockSource->setQty($orderStockSource->getQty() - $qtyFromLocation);
            $orderStockSource->save();
            $stockItem->setQty($stockItem->getQty() + $qtyFromLocation);
            $stockItem->save();
            $this->inventoryToRestock -= $qtyFromLocation;
        }
    }

}