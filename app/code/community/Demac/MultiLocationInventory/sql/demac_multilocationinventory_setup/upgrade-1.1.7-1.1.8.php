<?php
$installer = $this;
$installer->startSetup();


$table = $installer->getConnection()
    ->newTable($installer->getTable('demac_multilocationinventory/locator_stores'))
    ->addColumn('location_id', Varien_Db_Ddl_Table::TYPE_INTEGER, NULL, array(
        'unsigned' => TRUE,
        'nullable' => FALSE,
        'primary'  => TRUE,
    ), 'Location ID')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, NULL, array(
        'unsigned' => TRUE,
        'nullable' => FALSE,
        'primary'  => TRUE
    ), 'Store ID')
    ->addForeignKey(
        $installer->getFkName('demac_multilocationinventory/locator_stores', 'location_id',
            'demac_multilocationinventory/location', 'id'), 'location_id',
        $installer->getTable('demac_multilocationinventory/location'), 'id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey(
        $installer->getFkName('demac_multilocationinventory/locator_stores', 'store_id',
            'core/store', 'store_id'), 'store_id',
        $installer->getTable('core/store'), 'store_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Location To Magento Store Linkage Table');

$installer->getConnection()->createTable($table);


$installer->endSetup();