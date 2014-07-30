<?php

/**
 * Class Demac_MultiLocationInventory_Model_Indexer
 */
class Demac_MultiLocationInventory_Model_Indexer
    extends Mage_Index_Model_Indexer_Abstract
{

    /**
     * Register events that require a re-index. We never do since we update the indexer via observers.
     *
     * @param Mage_Index_Model_Event $event
     */
    protected function _registerEvent(Mage_Index_Model_Event $event)
    {

    }

    /**
     * Process events.. we never need to.
     *
     * @param Mage_Index_Model_Event $event
     */
    protected function _processEvent(Mage_Index_Model_Event $event)
    {

    }

    /**
     * Get indexer name for displaying in the indexer list.
     *
     * @return string
     */
    public function getName()
    {
        return 'Multi Location Inventory Stock';
    }

    /**
     * Get indexer description for displaying in the indexer list.
     *
     * @return string
     */
    public function getDescription()
    {
        return 'Index stock status on a per store view level rather than per inventory location.';
    }


    /**
     * Reindex all
     */
    public function reindexAll()
    {
        Mage::getModel('demac_multilocationinventory/stock_status_index')->reindex();
    }

    /**
     * Run reindexers
     *
     * @param int|array $productIds Product id (int) or array of product ids to reindex
     */
    public function reindex($productIds)
    {
        Mage::getModel('demac_multilocationinventory/stock_status_index')->reindex($productIds);
    }

}