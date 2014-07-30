<?php
$installer = $this;

$installer->startSetup();

/**
 * Create table 'demac_multilocationinventory/location'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('demac_multilocationinventory/location'))
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, NULL, array(
        'identity' => TRUE,
        'unsigned' => TRUE,
        'nullable' => FALSE,
        'primary'  => TRUE,
    ), 'Location ID')
    ->addColumn('name', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255,
        array(
            'nullable' => FALSE
        ), 'Name')
    ->addColumn('address', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255,
        array(
            'nullable' => FALSE
        ), 'Address')
    ->addColumn('zipcode', Varien_Db_Ddl_Table::TYPE_VARCHAR, 10,
        array(
            'nullable' => FALSE
        ), 'ZipCode')
    ->addColumn('city', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255,
        array(
            'nullable' => FALSE
        ), 'City')
    ->addColumn('region_id', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255,
        array(
            'nullable' => FALSE
        ), 'Region/Province')
    ->addColumn('country_id', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255,
        array(
            'nullable' => TRUE
        ), 'Country')
    ->addColumn('phone', Varien_Db_Ddl_Table::TYPE_VARCHAR, 30,
        array(
            'nullable' => TRUE
        ), 'Phone')
    ->addColumn('fax', Varien_Db_Ddl_Table::TYPE_VARCHAR, 30,
        array(
            'nullable' => TRUE
        ), 'Fax')
    ->addColumn('description', Varien_Db_Ddl_Table::TYPE_TEXT, 255,
        array(
            'nullable' => TRUE
        ), 'Description')
    ->addColumn('store_url', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255,
        array(
            'nullable' => TRUE
        ), 'Store Website')
    ->addColumn('status', Varien_Db_Ddl_Table::TYPE_SMALLINT, 6,
        array(
            'nullable' => FALSE
        ), 'Status')
    ->addColumn('image', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255,
        array(
            'nullable' => TRUE
        ), 'Image Link')
    ->addColumn('marker', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255,
        array(
            'nullable' => TRUE
        ), 'Marker Link')
    ->addColumn('lat', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255,
        array(
            'nullable' => TRUE
        ), 'Latitude Value')
    ->addColumn('long', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255,
        array(
            'nullable' => TRUE
        ), 'Longitude Value')
    ->addColumn('created_time', Varien_Db_Ddl_Table::TYPE_DATETIME, NULL,
        array(), 'Creation Time')
    ->addColumn('update_time', Varien_Db_Ddl_Table::TYPE_DATETIME, NULL,
        array(), 'Modification Time')
    ->setComment('Location Table');
$installer->getConnection()->createTable($table);

/**
 * Create table 'demac_multilocationinventory/stores'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('demac_multilocationinventory/stores'))
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
