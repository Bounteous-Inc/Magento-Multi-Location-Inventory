<?php
$installer = $this;

$installer->startSetup();

$table = $installer->getConnection()
    ->newTable($installer->getTable('demac_multilocationinventory/stock_status_index'))
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned' => true,
        'nullable' => false
    ), 'Location ID')
    ->addColumn('product_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned' => true,
        'nullable' => false
    ), 'Product ID')
    ->addColumn('qty', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable' => false,
        'default'  => '0.0000'
    ), 'Qty')
    ->addColumn('backorders', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned' => true,
        'nullable' => false,
        'default'  => '0'
    ), 'Backorders')
    ->addColumn('is_in_stock', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned' => true,
        'nullable' => false,
        'default'  => '0',
    ), 'Is In Stock')
    ->addIndex($installer->getIdxName(
                   'demac_multilocationinventory/stock_status_index',
                   array('product_id', 'store_id'),
                   Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
               ),
               array('product_id', 'store_id'),
               array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE)
    )
    ->addForeignKey($installer->getFkName(
                        'demac_multilocationinventory/stock_status_index',
                        'store_id',
                        'core/store',
                        'store_id'
                    ),
                    'store_id',
                    $installer->getTable('core/store'),
                    'store_id',
                    Varien_Db_Ddl_Table::ACTION_CASCADE,
                    Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->addForeignKey($installer->getFkName(
                        'demac_multilocationinventory/stock_status_index',
                        'product_id',
                        'catalog/product',
                        'entity_id'
                    ),
                    'product_id',
                    $installer->getTable('catalog/product'),
                    'entity_id',
                    Varien_Db_Ddl_Table::ACTION_CASCADE,
                    Varien_Db_Ddl_Table::ACTION_CASCADE
    );

$installer->getConnection()->createTable($table);


$installer->endSetup();
