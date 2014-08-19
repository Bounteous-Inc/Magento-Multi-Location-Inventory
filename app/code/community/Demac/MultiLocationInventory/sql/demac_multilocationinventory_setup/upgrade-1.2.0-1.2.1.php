<?php
$installer = $this;
$installer->startSetup();


$table = $installer->getTable('demac_multilocationinventory/stock_status_index');

$installer->getConnection()

        ->addColumn(
            $table, 
            'manage_stock',  
            array(
                'type'      => Varien_Db_Ddl_Table::TYPE_BOOLEAN,
                'length'    => null,
                'nullable'  => FALSE,
                'default'   => 1,
                'comment'   => 'Manage Stock?'
            )
        );

$installer->endSetup();
