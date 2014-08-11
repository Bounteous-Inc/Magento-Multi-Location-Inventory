<?php

/**
 * Class Demac_MultiLocationInventory_Adminhtml_MultiLocationInventoryController
 */
class Demac_MultiLocationInventory_Adminhtml_MultiLocationInventoryController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Index page
     *
     * @return void
     */
    public function indexAction()
    {
        $this->_initAction()->renderLayout();
    }

    /**
     * New Page (redirects to edit)
     */
    public function newAction()
    {
        $this->_forward('edit');
    }

    /**
     * Edit Page
     *
     * @return void
     */
    public function editAction()
    {
        $this->_initAction();
        $locationId    = $this->getRequest()->getParam('id');
        $locationModel = Mage::getModel('demac_multilocationinventory/location');

        if($locationId) {
            $locationModel->load($locationId);
            if(!$locationModel->getId()) {
                Mage::getSingleton('adminhtml/session')->addError($this->__('This location no longer exists.'));
                $this->_redirect('*/*/');

                return;
            }
        }

        $this->_title($locationModel->getId() ? $locationModel->getName() : $this->__('New Location'));
        $data = Mage::getSingleton('adminhtml/session')->getStoreData(true);
        if(!empty($data)) {
            $locationModel->setData($data);
        }
        Mage::register('multilocationinventory_data', $locationModel);

        $this->loadLayout();
        $this->_setActiveMenu('catalog/demac_multilocationinventory');

        $this->_addBreadcrumb($locationId ? Mage::helper('demac_multilocationinventory')->__('Edit Location') : Mage::helper('demac_multilocationinventory')->__('New Location'), $locationId ? Mage::helper('demac_multilocationinventory')->__('Edit Location') : Mage::helper('demac_multilocationinventory')->__('New Location'));

        $this->_addContent($this->getLayout()->createBlock('demac_multilocationinventory/adminhtml_location_edit'));
        $this->_addLeft($this->getLayout()->createBlock('demac_multilocationinventory/adminhtml_location_edit_tabs'));

        $this->renderLayout();

    }


    /**
     * Save...
     *
     * @return void
     *
     * @TODO Refactor: Current function is too complex needs to be broken down into simpler logic
     */
    public function saveAction()
    {
        if($postData = $this->getRequest()->getPost()) {
            $model = Mage::getSingleton('demac_multilocationinventory/location');
            if($id = $this->getRequest()->getParam('id')) {
                $model->load($id);
            }

            unset($postData['entity_id']);
            $model->setData($postData);

            try {
                if(is_null($model->getCreatedTime()) || $model->getCreatedTime() == '') {
                    $model->setCreatedTime(time());
                }
                $model->setUpdateTime(time());

                $model->save();

                Mage::getSingleton('adminhtml/session')->addSuccess($this->__('The Location has been saved.'));
                if($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));

                    return;
                }
                $this->_redirect('*/*/');

                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/');

                return;
            }

            Mage::getSingleton('adminhtml/session')->setStoreData($postData);
            $this->_redirectReferer();
        }
    }

    /**
     * Delete action
     *
     * @return void
     */
    public function deleteAction()
    {
        if($this->getRequest()->getParam('id') > 0) {
            try {
                $model = Mage::getModel('demac_multilocationinventory/location');

                $model->setId($this->getRequest()->getParam('id'))
                    ->delete();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('demac_multilocationinventory')->__('Location was successfully deleted'));
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/');
    }

    /**
     * Mass Delete Action
     *
     * @return void
     */
    public function massDeleteAction()
    {
        $locationIds = $this->getRequest()->getParam('demac_multilocationinventory');
        if(!is_array($locationIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('demac_multilocationinventory')->__('Please select a location(s)'));
        } else {
            try {
                foreach ($locationIds as $locationId) {
                    $location = Mage::getModel('demac_multilocationinventory/location')
                        ->setId($locationId)
                        ->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('demac_multilocationinventory')->__(
                        'Total of %d record(s) were successfully deleted', count($locationIds)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    /**
     * Mass Update Status Action
     *
     * @return void
     */
    public function massStatusAction()
    {
        $locationIds = $this->getRequest()->getParam('demac_multilocationinventory');
        if(!is_array($locationIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select a location(s)'));
        } else {
            try {
                foreach ($locationIds as $locationId) {
                    // Start Refactor Eventually switch this to an adapter mass update
                    Mage::getSingleton('demac_multilocationinventory/location')
                        ->load($locationId)
                        ->setStatus($this->getRequest()->getParam('status'))
                        ->save();
                    // End Refactor
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were successfully updated', count($locationIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    /**
     * Initialize action
     *
     * Here, we set the breadcrumbs and the active menu
     *
     * @return Demac_MultiLocationInventory_Adminhtml_MultiLocationInventoryController
     */
    protected function _initAction()
    {

        $this->loadLayout()
            // Make the active menu match the menu config nodes (without 'children' inbetween)
            ->_setActiveMenu('demac/demac_multilocationinventory')
            ->_title($this->__('Demac'))->_title($this->__('Location'))
            ->_addBreadcrumb($this->__('Demac'), $this->__('Demac'))
            ->_addBreadcrumb($this->__('Location'), $this->__('Location'));

        return $this;
    }

    /**
     * Check currently called action by permissions for current user
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('catalog/demac_multilocationinventory');
    }

    /**
     * Get country by code.
     *
     * @param $needle
     *
     * @return bool|array
     */
    public function getCountry($needle)
    {
        if(is_null($this->_countries)) {
            $countriesList    = Mage::getResourceModel('directory/country_collection')
                ->loadData()
                ->toOptionArray(false);
            $newCountriesList = array();
            foreach ($countriesList as $key => $val) {
                $newCountriesList[strtolower($val['label'])] = $val['value'];;
            }
            $this->_countries = $newCountriesList;
        }
        $countryCode = str_replace('USA', 'US', strtolower($needle));
        if(isset($this->_countries[$countryCode])) {
            return $this->_countries[$countryCode];
        }

        return false;
    }

    /**
     * Get a list of regions for the selected country (AJAX).
     */
    public function regionAction()
    {
        $countryCode = $this->getRequest()->getParam('country');
        $options     = Mage::helper('demac_multilocationinventory')->getRegions($countryCode);
        $optionsHtml = '';

        // @TODO Start Refactor : Loop through this array in a view instead where we don't have to echo.
        foreach ($options as $option) {
            $optionsHtml .= '<option value="' . $option['value'] . '">' . $option['label'] . '</option>';
        }

        $this->getResponse()->setBody($optionsHtml);
    }
}