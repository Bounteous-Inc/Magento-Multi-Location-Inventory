<?php
/**
 * Created by PhpStorm.
 * User: MichaelK
 * Date: 4/9/14
 * Time: 7:05 AM
 */

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();
/**
 * Create table 'demac_multilocationinventory/quote'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('demac_multilocationinventory/quote'))
    ->addColumn('item_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned' => true,
        'nullable' => false,
        'primary'  => true,
    ), 'Quote Item ID')
    ->addColumn('location_stock_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned' => true,
        'nullable' => false,
        'primary'  => true
    ), 'Location Stock ID')
    ->setComment('Location Stock To Quote Item Relation Table');

$installer->getConnection()->createTable($table);
$installer->endSetup();