<?php
$installer = $this;

$installer->startSetup();

//You probably shouldn't enable / deploy this during the day if you have a big catalog and/or a lot of locations...
//Mage::getModel('demac_multilocationinventory/indexer')->reindexAll();

$installer->endSetup();
