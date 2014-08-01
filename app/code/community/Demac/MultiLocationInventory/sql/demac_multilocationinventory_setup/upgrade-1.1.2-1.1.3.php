<?php
$installer = $this;

$installer->startSetup();

/**
 * Add ClientId to table 'demac_multilocationinventory/location'
 */
$table = $installer->getTable('demac_multilocationinventory/location');

$installer->getConnection()
    ->addColumn($table, 'external_id',
                array(
                    'type'    => Varien_Db_Ddl_Table::TYPE_TEXT,
                    'length'  => 255,
                    'unique'  => true,
                    'comment' => 'External ID for client usage',
                    'after'   => 'name'
                )
    );

$installer->getConnection()
    ->addIndex($table, 'IDX_EXTERNAL_ID', 'external_id', Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE);

$installer->endSetup();
