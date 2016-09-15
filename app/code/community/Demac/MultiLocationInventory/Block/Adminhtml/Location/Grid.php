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
        $this->setSaveParametersInSession(true);

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
                             'header' => $this->__('ID'),
                             'align'  => 'right',
                             'width'  => '50px',
                             'index'  => 'id'
                         )
        );

        $this->addColumn('external_id',
                         array(
                             'header' => $this->__('External ID'),
                             'index'  => 'external_id',
                         )
        );

        $this->addColumn('code',
            array(
                'header' => $this->__('Code'),
                'index'  => 'code'
            )
        );

        $this->addColumn('name',
                         array(
                             'header' => $this->__('Name'),
                             'index'  => 'name',
                         )
        );

        $this->addColumn('address',
                         array(
                             'header' => $this->__('Address'),
                             'index'  => 'address',
                         )
        );

        $this->addColumn('zipcode',
                         array(
                             'header' => $this->__('Postal Code'),
                             'index'  => 'zipcode',
                         )
        );

        $this->addColumn('city',
                         array(
                             'header' => $this->__('City'),
                             'index'  => 'city',
                         )
        );

        $this->addColumn('region_id',
                         array(
                             'header' => $this->__('Region'),
                             'index'  => 'region_id',
                         )
        );

        $this->addColumn('country_id', array(
            'header' => $this->__('Country'),
            'width'  => '100',
            'type'   => 'country',
            'index'  => 'country_id',
        ));

        $this->addColumn('store_id', array(
            'header'     => $this->__('Inventory For'),
            'index'      => 'store_id',
            'type'       => 'store',
            'store_all'  => false,
            'store_view' => false,
            'sortable'   => false,
            'filter'     => false
        ));

        $this->addColumn('status',
                         array(
                             'header'  => $this->__('Status'),
                             'index'   => $this->__('status'),
                             'type'    => 'options',
                             'options' => array(
                                 0 => $this->__('Disabled'),
                                 1 => $this->__('Enabled'),
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
            'label'   => $this->__('Delete'),
            'url'     => $this->getUrl('*/*/massDelete'),
            'confirm' => $this->__('Are you sure?')
        ));

        $statuses = array(
            1 => $this->__('Enabled'),
            0 => $this->__('Disabled')
        );
        $this->getMassactionBlock()->addItem('status', array(
            'label'      => $this->__('Change status'),
            'url'        => $this->getUrl('*/*/massStatus', array('_current' => true)),
            'additional' => array(
                'visibility' => array(
                    'name'   => 'status',
                    'type'   => 'select',
                    'class'  => 'required-entry',
                    'label'  => $this->__('Status'),
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
}