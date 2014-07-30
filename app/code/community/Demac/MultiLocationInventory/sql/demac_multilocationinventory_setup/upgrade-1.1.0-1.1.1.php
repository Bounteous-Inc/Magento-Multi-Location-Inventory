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

$table = $installer->getTable('demac_multilocationinventory/location');

$installer->getConnection()
    ->addColumn(
        $table,
        'priority',
        array(
            'type'    => Varien_Db_Ddl_Table::TYPE_BOOLEAN,
            'default' => 0,
            'comment' => 'Location Priority'
        )
    );

$installer->endSetup();