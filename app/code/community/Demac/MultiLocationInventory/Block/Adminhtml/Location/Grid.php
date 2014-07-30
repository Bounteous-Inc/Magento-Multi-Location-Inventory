<?php

/**
 * Class Demac_MultiLocationInventory_Block_Adminhtml_Location_Grid
 */
class Demac_MultiLocationInventory_Block_Adminhtml_Location_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Init - prepare widget grid.
     */
    public function __construct()
    {
        parent::__construct();

        // Set some defaults for our grid

        $this->setDefaultSort('id');
        $this->setId('LocationGrid');
        $this->setDefaultDir('asc');
        $this->setSaveParametersInSession(TRUE);

    }

    /**
     * Set this widget grid's collection to be a collection of locations then let parent prepare grid object collection.
     *
     * @return Demac_MultiLocationInventory_Block_Adminhtml_Location_Grid
     */
    protected function _prepareCollection()
    {
        // Get and set our collection for the grid
        $collection = Mage::getResourceModel('demac_multilocationinventory/location_collection');
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * Add all necessary columns then prepare for rendering.
     *
     * @return Demac_MultiLocationInventory_Block_Adminhtml_Location_Grid
     */
    protected function _prepareColumns()
    {
        // Add the columns that should appear in the grid
        $this->addColumn('id',
            array(
                'header' => Mage::helper('demac_multilocationinventory')->__('ID'),
                'align'  => 'right',
                'width'  => '50px',
                'index'  => 'id'
            )
        );

        $this->addColumn('image', array(
            'header'   => Mage::helper('demac_multilocationinventory')->__('Image'),
            'width'    => '100',
            'renderer' => 'demac_multilocationinventory/adminhtml_location_renderer_image',
            'index'    => 'image',
            'sortable' => FALSE,
            'filter'   => FALSE
        ));

        $this->addColumn('external_id',
            array(
                'header' => Mage::helper('demac_multilocationinventory')->__('External ID'),
                'index'  => 'external_id',
            )
        );

        $this->addColumn('name',
            array(
                'header' => Mage::helper('demac_multilocationinventory')->__('Name'),
                'index'  => 'name',
            )
        );

        $this->addColumn('address',
            array(
                'header' => Mage::helper('demac_multilocationinventory')->__('Address'),
                'index'  => 'address',
            )
        );

        $this->addColumn('zipcode',
            array(
                'header' => Mage::helper('demac_multilocationinventory')->__('Postal Code'),
                'index'  => 'zipcode',
            )
        );

        $this->addColumn('city',
            array(
                'header' => Mage::helper('demac_multilocationinventory')->__('City'),
                'index'  => 'city',
            )
        );

        $this->addColumn('region_id',
            array(
                'header' => Mage::helper('demac_multilocationinventory')->__('Region'),
                'index'  => 'region_id',
            )
        );

        $this->addColumn('country_id', array(
            'header' => Mage::helper('demac_multilocationinventory')->__('Country'),
            'width'  => '100',
            'type'   => 'country',
            'index'  => 'country_id',
        ));

        $this->addColumn('phone',
            array(
                'header' => Mage::helper('demac_multilocationinventory')->__('Phone'),
                'index'  => 'phone',
            )
        );

        $this->addColumn('fax',
            array(
                'header' => Mage::helper('demac_multilocationinventory')->__('Fax'),
                'index'  => 'fax',
            )
        );

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('store_id', array(
                'header'     => Mage::helper('demac_multilocationinventory')->__('Inventory For'),
                'index'      => 'store_id',
                'type'       => 'store',
                'store_all'  => FALSE,
                'store_view' => FALSE,
                'sortable'   => FALSE,
                'filter'     => FALSE
            ));
        }

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('locator_store_id', array(
                'header'     => Mage::helper('demac_multilocationinventory')->__('Locator For'),
                'index'      => 'locator_store_id',
                'type'       => 'store',
                'store_all'  => FALSE,
                'store_view' => FALSE,
                'sortable'   => FALSE,
                'filter'     => FALSE,
            ));
        }

        $this->addColumn('status',
            array(
                'header'  => Mage::helper('demac_multilocationinventory')->__('Status'),
                'index'   => Mage::helper('demac_multilocationinventory')->__('status'),
                'type'    => 'options',
                'options' => array(
                    0 => Mage::helper('demac_multilocationinventory')->__('Disabled'),
                    1 => Mage::helper('demac_multilocationinventory')->__('Enabled'),
                ),
            )
        );


        return parent::_prepareColumns();
    }

    /**
     * Define identifier field and options for mass actions.
     *
     * @return $this|Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('demac_multilocationinventory');

        $this->getMassactionBlock()->addItem('delete', array(
            'label'   => Mage::helper('demac_multilocationinventory')->__('Delete'),
            'url'     => $this->getUrl('*/*/massDelete'),
            'confirm' => Mage::helper('demac_multilocationinventory')->__('Are you sure?')
        ));

        $statuses = array(
            1 => Mage::helper('demac_multilocationinventory')->__('Enabled'),
            0 => Mage::helper('demac_multilocationinventory')->__('Disabled')
        );
        $this->getMassactionBlock()->addItem('status', array(
            'label'      => Mage::helper('demac_multilocationinventory')->__('Change status'),
            'url'        => $this->getUrl('*/*/massStatus', array('_current' => TRUE)),
            'additional' => array(
                'visibility' => array(
                    'name'   => 'status',
                    'type'   => 'select',
                    'class'  => 'required-entry',
                    'label'  => Mage::helper('demac_multilocationinventory')->__('Status'),
                    'values' => $statuses
                )
            )
        ));

        return $this;
    }

    /**
     * Get edit URL for clicking on a row.
     *
     * @param $row
     *
     * @return string
     *
     * @TODO is this filter necessary?
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

    /**
     * After the collection is loaded call it's afterLoad method on each item.
     *
     * @return Demac_MultiLocationInventory_Block_Adminhtml_Location_Grid
     */
    protected function _afterLoadCollection()
    {
        $this->getCollection()->walk('afterLoad');
        parent::_afterLoadCollection();
    }

    /**
     * Filter
     *
     * @param $collection
     * @param $column
     *
     * @TODO is this filter necessary?
     */
    protected function _filterStoreCondition(&$collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return;
        }
        $collection->addStoreFilter($value);

        return $collection;
    }

}