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

$table = $installer->getTable('demac_multilocationinventory/stock');

$connection = $installer->getConnection();
$connection->addColumn(
    $table,
    'min_qty',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'scale'     => 4,
        'precision' => 12,
        'nullable'  => false,
        'default'   => '0.0000',
        'comment'   => 'Min Qty'
    )
);

$installer->endSetup();