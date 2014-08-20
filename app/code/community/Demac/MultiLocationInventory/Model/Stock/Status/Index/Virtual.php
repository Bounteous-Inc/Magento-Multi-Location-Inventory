<?php

/**
 * Class Demac_MultiLocationInventory_Model_Stock_Status_Index_Virtual
 */
class Demac_MultiLocationInventory_Model_Stock_Status_Index_Virtual
    extends Demac_MultiLocationInventory_Model_Stock_Status_Index_Simple
    implements Demac_MultiLocationInventory_Model_Stock_Status_Index_Interface
{
    protected $productType = 'virtual';
}
