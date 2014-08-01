<?php
$installer = $this;
$installer->startSetup();


$table = $installer->getTable('demac_multilocationinventory/location');


$table = $installer->getTable('demac_multilocationinventory/stock');

$installer->getConnection()
    ->modifyColumn(
        $table,
        'manage_stock',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_SMALLINT,
            'length'   => null,
            'unsigned' => true,
            'nullable' => false,
            'default'  => '1'
        )
    );

$installer->endSetup();