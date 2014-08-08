<?php
$installer = $this;

$installer->startSetup();

/**
 * Create table 'demac_multilocationinventory/location'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('demac_multilocationinventory/location'))
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity' => true,
        'unsigned' => true,
        'nullable' => false,
        'primary'  => true,
    ), 'Location ID')
    ->addColumn('name', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255,
                array(
                    'nullable' => false
                ), 'Name')
    ->addColumn('address', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255,
                array(
                    'nullable' => false
                ), 'Address')
    ->addColumn('zipcode', Varien_Db_Ddl_Table::TYPE_VARCHAR, 10,
                array(
                    'nullable' => false
                ), 'ZipCode')
    ->addColumn('city', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255,
                array(
                    'nullable' => false
                ), 'City')
    ->addColumn('region_id', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255,
                array(
                    'nullable' => false
                ), 'Region/Province')
    ->addColumn('country_id', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255,
                array(
                    'nullable' => true
                ), 'Country')
    ->addColumn('status', Varien_Db_Ddl_Table::TYPE_SMALLINT, 6,
                array(
                    'nullable' => false
                ), 'Status')
    ->addColumn('lat', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255,
                array(
                    'nullable' => true
                ), 'Latitude Value')
    ->addColumn('long', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255,
                array(
                    'nullable' => true
                ), 'Longitude Value')
    ->addColumn('created_time', Varien_Db_Ddl_Table::TYPE_DATETIME, null,
                array(), 'Creation Time')
    ->addColumn('update_time', Varien_Db_Ddl_Table::TYPE_DATETIME, null,
                array(), 'Modification Time')
    ->setComment('Location Table');
$installer->getConnection()->createTable($table);

/**
 * Create table 'demac_multilocationinventory/stores'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('demac_multilocationinventory/stores'))
    ->addColumn('location_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned' => true,
        'nullable' => false,
        'primary'  => true,
    ), 'Location ID')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned' => true,
        'nullable' => false,
        'primary'  => true
    ), 'Store ID')
    ->addForeignKey(
        $installer->getFkName('demac_multilocationinventory/stores', 'location_id',
                              'demac_multilocationinventory/location', 'id'), 'location_id',
        $installer->getTable('demac_multilocationinventory/location'), 'id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey(
        $installer->getFkName('demac_multilocationinventory/stores', 'store_id',
                              'core/store', 'store_id'), 'store_id',
        $installer->getTable('core/store'), 'store_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Location To Magento Store Linkage Table');

$installer->getConnection()->createTable($table);

$installer->endSetup();
