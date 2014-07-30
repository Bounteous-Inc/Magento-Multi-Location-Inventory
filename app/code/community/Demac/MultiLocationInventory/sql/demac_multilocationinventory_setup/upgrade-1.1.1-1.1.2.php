<?php
/**
 * Created by PhpStorm.
 * User: MichaelK
 * Date: 4/9/14
 * Time: 7:05 AM
 */

/* @var $installer Mage_Customer_Model_Entity_Setup */
$installer = new Mage_Customer_Model_Entity_Setup('core_setup');

$installer->startSetup();
/**
 * Create table 'demac_multilocationinventory/quote'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('demac_multilocationinventory/quote'))
    ->addColumn('item_id', Varien_Db_Ddl_Table::TYPE_INTEGER, NULL, array(
        'unsigned' => TRUE,
        'nullable' => FALSE,
        'primary'  => TRUE,
    ), 'Quote Item ID')
    ->addColumn('location_stock_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, NULL, array(
        'unsigned' => TRUE,
        'nullable' => FALSE,
        'primary'  => TRUE
    ), 'Location Stock ID')
    ->setComment('Location Stock To Quote Item Relation Table');

$installer->getConnection()->createTable($table);
$installer->endSetup();