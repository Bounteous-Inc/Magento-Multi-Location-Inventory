<?php
/**
 * Created by PhpStorm.
 * User: MichaelK
 * Date: 4/1/14
 * Time: 1:52 PM
 */

/**
 * Class Demac_MultiLocationInventory_Block_Adminhtml_Location_Edit_Tab_Inventory
 */
class Demac_MultiLocationInventory_Block_Adminhtml_Location_Edit_Tab_Inventory extends Mage_Adminhtml_Block_Widget_Grid
{

    /**
     * Init widget grid
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('productGrid');
        // This is the primary key of the database
        $this->setDefaultSort('id');
        $this->setDefaultDir('desc');

        $this->setSaveParametersInSession(true);
        //$this->setUseAjax(true);

    }

    /**
     * Build a collection of stock data, set it as the collection for this widget, then let the parent apply any filters.
     *
     * @return Demac_MultiLocationInventory_Block_Adminhtml_Location_Edit_Tab_Inventory
     */
    protected function _prepareCollection()
    {
        $location   = Mage::registry('multilocationinventory_data');
        $collection = Mage::getModel('demac_multilocationinventory/stock')->getCollection();
        $collection->addFieldToFilter('location_id', $location->getId());
        $collection->addProductData();
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * Add columns to the grid view.
     *
     * @return Demac_MultiLocationInventory_Block_Adminhtml_Location_Edit_Tab_Inventory
     */
    protected function _prepareColumns()
    {
        $this->addColumn('product_id', array(
            'header' => Mage::helper('demac_multilocationinventory')->__('Product ID'),
            'align'  => 'right',
            'type'   => 'number',
            'index'  => 'product_id',
            'width'  => '40px'
        ));

        $this->addColumn('product_name', array(
            'header' => Mage::helper('demac_multilocationinventory')->__('Name'),
            'align'  => 'left',
            'index'  => 'product_name',
        ));

        $this->addColumn('product_sku', array(
            'header' => Mage::helper('demac_multilocationinventory')->__('SKU'),
            'align'  => 'left',
            'index'  => 'product_sku',
        ));

        $this->addColumn('qty', array(
            'header' => Mage::helper('demac_multilocationinventory')->__('QTY'),
            'align'  => 'left',
            'index'  => 'qty',
        ));

        $this->addColumn('is_in_stock', array(
            'header'  => Mage::helper('demac_multilocationinventory')->__('In Stock'),
            'align'   => 'left',
            'type'    => 'options',
            'options' => array(
                0 => Mage::helper('demac_multilocationinventory')->__('Out of Stock'),
                1 => Mage::helper('demac_multilocationinventory')->__('In Stock'),
            ),
            'index'   => 'is_in_stock',
        ));

        $this->addColumn('product_status', array(
            'header'  => Mage::helper('demac_multilocationinventory')->__('Status'),
            'align'   => 'left',
            'width'   => '80px',
            'index'   => 'product_status',
            'type'    => 'options',
            'options' => array(
                1 => Mage::helper('demac_multilocationinventory')->__('Enabled'),
                0 => Mage::helper('demac_multilocationinventory')->__('Disabled'),
            ),
        ));

        $this->addColumn('action',
                         array(
                             'header'   => Mage::helper('demac_multilocationinventory')->__('Action'),
                             'width'    => '100px',
                             'align'    => 'center',
                             'type'     => 'action',
                             'getter'   => 'getProductId',
                             'actions'  => array(array(
                                 'caption' => Mage::helper('demac_multilocationinventory')->__('Edit'),
                                 'url'     => array(
                                     'base' => '*/catalog_product/edit'
                                 ),
                                 'field'   => 'id'
                             )),
                             'filter'   => false,
                             'sortable' => false,
                             'index'    => 'product_id',
                         ));

        return parent::_prepareColumns();
    }

    /**
     * Define identifier field for mass actions.
     *
     * @return Demac_MultiLocationInventory_Block_Adminhtml_Location_Edit_Tab_Inventory
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('product_id');
        $this->getMassactionBlock()->setFormFieldName('ids');
        $this->getMassactionBlock()->setUseSelectAll(true);

        return $this;
    }
}