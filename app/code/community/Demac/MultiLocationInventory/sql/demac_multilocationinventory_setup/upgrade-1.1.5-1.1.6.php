<?php
$installer = $this;

$installer->startSetup();

$table = $installer->getConnection()
    ->newTable($installer->getTable('demac_multilocationinventory/order_stock_source'))
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity' => true,
        'primary'  => true,
        'auto_increment', true,
        'unsigned' => true,
        'nullable' => false
    ), 'Order Stock Source ID')
    ->addColumn('sales_quote_item_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned' => true,
        'nullable' => false
    ), 'Sales Quote Item ID')
    ->addColumn('location_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned' => true,
        'nullable' => false,
    ), 'Location ID')
    ->addColumn('qty', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable' => false,
    ), 'Qty')
    ->addColumn('is_backorder', Varien_Db_Ddl_Table::TYPE_BOOLEAN, null, array(
        'nullable' => false,
    ), 'Is Backorder?')

    ->addForeignKey($installer->getFkName(
                        'demac_multilocationinventory/order_stock_source',
                        'sales_quote_item_id',
                        'sales/quote_item',
                        'item_id'
                    ),
                    'sales_quote_item_id',
                    $installer->getTable('sales/quote_item'),
                    'item_id',
                    Varien_Db_Ddl_Table::ACTION_CASCADE,
                    Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->addForeignKey($installer->getFkName(
                        'demac_multilocationinventory/order_stock_source',
                        'location_id',
                        'demac_multilocationinventory/location',
                        'id'
                    ),
                    'location_id',
                    $installer->getTable('demac_multilocationinventory/location'),
                    'id',
                    Varien_Db_Ddl_Table::ACTION_CASCADE,
                    Varien_Db_Ddl_Table::ACTION_CASCADE
    );

$installer->getConnection()->createTable($table);


$installer->endSetup();
