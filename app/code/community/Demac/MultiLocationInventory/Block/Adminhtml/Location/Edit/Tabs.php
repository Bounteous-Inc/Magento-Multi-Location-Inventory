<?php
/**
 * Created by PhpStorm.
 * User: MichaelK
 * Date: 4/1/14
 * Time: 1:53 PM
 */

/**
 * Class Demac_MultiLocationInventory_Block_Adminhtml_Location_Edit_Tabs
 */
class Demac_MultiLocationInventory_Block_Adminhtml_Location_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

    /**
     * @var bool|array An array of tabs to be rendered.
     */
    protected $tabs = false;

    /**
     * Init form tabs.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('multilocationinventory_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('demac_multilocationinventory')->__('Multi-Inventory Location'));
    }

    /**
     * Prepare tabs array.
     */
    protected function prepareTabs()
    {
        $tabs   = array();
        $tabs[] = array('form_section' =>
                            array(
                                'label'   => Mage::helper('demac_multilocationinventory')->__('Location'),
                                'title'   => Mage::helper('demac_multilocationinventory')->__('Location'),
                                'content' => $this->getLayout()->createBlock('demac_multilocationinventory/adminhtml_location_edit_tab_location')->toHtml(),
                                'active'  => true
                            )
        );
        if(Mage::registry('multilocationinventory_data') && Mage::registry('multilocationinventory_data')->getId()) {
            $tabs[] = array('inventory' =>
                                array(
                                    'label'   => Mage::helper('demac_multilocationinventory')->__('Inventory'),
                                    'title'   => Mage::helper('demac_multilocationinventory')->__('Inventory'),
                                    'content' => $this->getLayout()->createBlock('demac_multilocationinventory/adminhtml_location_edit_tab_inventory')->toHtml(),
                                )
            );
        }
        $this->tabs = $tabs;
    }

    /**
     * Called before the block is converted to HTML.
     * Prepare tabs if necessary, add the prepared tabs to the block, then call parent implementations.
     *
     * @return Demac_MultiLocationInventory_Block_Adminhtml_Location_Edit_Tabs
     */
    protected function _beforeToHtml()
    {
        if(!$this->tabs) {
            $this->prepareTabs();
        }

        foreach ($this->tabs as $tab) {
            foreach ($tab as $_tabId => $_tabData) {
                $this->addTab($_tabId, $_tabData);
            }
        }

        return parent::_beforeToHtml();
    }
}