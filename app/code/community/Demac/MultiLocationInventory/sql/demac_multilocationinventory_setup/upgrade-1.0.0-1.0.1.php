<?php
$installer = $this;

$installer->startSetup();

/**
 * Create table 'demac_multilocationinventory/stores'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('demac_multilocationinventory/stock'))
    ->addColumn('stock_id', Varien_Db_Ddl_Table::TYPE_INTEGER, NULL, array(
        'identity' => TRUE,
        'unsigned' => TRUE,
        'nullable' => FALSE,
        'primary'  => TRUE,
    ), 'Stock Id')
    ->addColumn('location_id', Varien_Db_Ddl_Table::TYPE_INTEGER, NULL, array(
        'unsigned' => TRUE,
        'nullable' => FALSE,
    ), 'Location ID')
    ->addColumn('product_id', Varien_Db_Ddl_Table::TYPE_INTEGER, NULL, array(
        'nullable' => FALSE,
        'unsigned' => TRUE,
    ), 'Product ID')
    ->addColumn('qty', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable' => FALSE,
        'default'  => '0.0000',
    ), 'Qty')
    ->addColumn('backorders', Varien_Db_Ddl_Table::TYPE_SMALLINT, NULL, array(
        'unsigned' => TRUE,
        'nullable' => FALSE,
        'default'  => '0',
    ), 'Backorders')
    ->addColumn('use_config_backorders', Varien_Db_Ddl_Table::TYPE_SMALLINT, NULL, array(
        'unsigned' => TRUE,
        'nullable' => FALSE,
        'default'  => '1',
    ), 'Use Config Backorders')
    ->addColumn('is_in_stock', Varien_Db_Ddl_Table::TYPE_SMALLINT, NULL, array(
        'unsigned' => TRUE,
        'nullable' => FALSE,
        'default'  => '0',
    ), 'Is In Stock')
    ->addColumn('manage_stock', Varien_Db_Ddl_Table::TYPE_SMALLINT, NULL, array(
        'unsigned' => TRUE,
        'nullable' => FALSE,
        'default'  => '0',
    ), 'Manage Stock')
    ->addColumn('use_config_manage_stock', Varien_Db_Ddl_Table::TYPE_SMALLINT, NULL, array(
        'unsigned' => TRUE,
        'nullable' => FALSE,
        'default'  => '1',
    ), 'Use Config Manage Stock')
    ->addIndex($installer->getIdxName('demac_multilocationinventory/stock', array('product_id', 'location_id'), Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE),
        array('product_id', 'location_id'), array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE)
    )
    ->addForeignKey(
        $installer->getFkName('demac_multilocationinventory/stock', 'location_id',
            'demac_multilocationinventory/location', 'id'), 'location_id',
        $installer->getTable('demac_multilocationinventory/location'), 'id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey($installer->getFkName('demac_multilocationinventory/stock', 'product_id',
            'catalog/product', 'entity_id'), 'product_id',
        $installer->getTable('catalog/product'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->setComment('Location To Magento Store Linkage Table');

$installer->getConnection()->createTable($table);

$installer->endSetup();
