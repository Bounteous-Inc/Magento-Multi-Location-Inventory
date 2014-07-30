<?php

/**
 * Class Demac_MultiLocationInventory_Block_Adminhtml_Location_Renderer_Image
 */
class Demac_MultiLocationInventory_Block_Adminhtml_Location_Renderer_Image extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{

    /**
     * Render image HTML.
     *
     * @param Varien_Object $row
     *
     * @return string
     */
    public function render(Varien_Object $row)
    {
        if (!is_null($row->getData($this->getColumn()->getIndex()))) {
            $html = '<img ';
            $html .= 'id="' . $this->getColumn()->getId() . '" ';
            $html .= 'src="' . Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . $row->getData($this->getColumn()->getIndex()) . '"';
            $html .= 'width="100"';
            $html .= 'class="grid-image ' . $this->getColumn()->getInlineCss() . '"/>';

            return $html;
        }
    }
}