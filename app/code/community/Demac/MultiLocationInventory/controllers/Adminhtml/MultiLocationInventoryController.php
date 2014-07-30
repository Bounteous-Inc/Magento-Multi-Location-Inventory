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
        $id    = $this->getRequest()->getParam('id');
        $model = Mage::getModel('demac_multilocationinventory/location');

        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                Mage::getSingleton('adminhtml/session')->addError($this->__('This location no longer exists.'));
                $this->_redirect('*/*/');

                return;
            }
        }

        $this->_title($model->getId() ? $model->getName() : $this->__('New Location'));
        $data = Mage::getSingleton('adminhtml/session')->getStoreData(TRUE);
        if (!empty($data)) {
            $model->setData($data);
        }
        Mage::register('multilocationinventory_data', $model);

        $this->loadLayout();
        $this->_setActiveMenu('catalog/demac_multilocationinventory');

        $this->_addBreadcrumb($id ? Mage::helper('demac_multilocationinventory')->__('Edit Location') : Mage::helper('demac_multilocationinventory')->__('New Location'), $id ? Mage::helper('demac_multilocationinventory')->__('Edit Location') : Mage::helper('demac_multilocationinventory')->__('New Location'));

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
     * @TODO Refactor: Avoid using super globals like the $_FILES variable
     */
    public function saveAction()
    {
        if ($postData = $this->getRequest()->getPost()) {
            if (isset($_FILES['image']['name']) and (file_exists($_FILES['image']['tmp_name']))) {
                try {
                    $uploader = new Varien_File_Uploader('image');
                    $uploader->setAllowedExtensions(array('jpg', 'jpeg', 'gif', 'png'));
                    $uploader->setAllowRenameFiles(FALSE);
                    $uploader->setFilesDispersion(FALSE);
                    $locImg = 'multilocationinventory/images/';
                    $path   = Mage::getBaseDir('media') . DS . $locImg;
                    $uploader->save($path, $_FILES['image']['name']);
                    $postData['image'] = $_FILES['image']['name'];
                    // Start Refactor : Empty catch statement
                } catch (Exception $e) {

                }
                // End Refactor
            } else {
                if (isset($postData['image']['delete']) && $postData['image']['delete'] == 1) {
                    $postData['image'] = '';
                } else {
                    unset($postData['image']);
                }
            }

            if (isset($_FILES['marker']['name']) and (file_exists($_FILES['marker']['tmp_name']))) {
                try {
                    $uploader = new Varien_File_Uploader('marker');
                    $uploader->setAllowedExtensions(array('jpg', 'jpeg', 'gif', 'png'));
                    $uploader->setAllowRenameFiles(FALSE);
                    $uploader->setFilesDispersion(FALSE);
                    $locMarker = 'multilocationinventory/markers/';
                    $path      = Mage::getBaseDir('media') . DS . $locMarker;
                    $uploader->save($path, $_FILES['marker']['name']);
                    $postData['marker'] = $_FILES['marker']['name'];
                } catch (Exception $e) {

                }
            } else {
                if (isset($postData['marker']['delete']) && $postData['marker']['delete'] == 1) {
                    $postData['marker'] = '';
                } else {
                    unset($postData['marker']);
                }
            }

            $model = Mage::getSingleton('demac_multilocationinventory/location');


            if ($id = $this->getRequest()->getParam('id')) {
                $model->load($id);
            }

            unset($postData['entity_id']);
            $model->setData($postData);

            try {
                if (is_null($model->getCreatedTime()) || $model->getCreatedTime() == '') {
                    $model->setCreatedTime(time());
                }
                $model->setUpdateTime(time());

                if (!is_null($model->getImage()) && $model->getImage() != '') {
                    $filename = str_replace(" ", "_", $model->getImage());
                    $filename = str_replace(":", "_", $filename);
                    $model->setImage($locImg . $filename);
                }

                if (!is_null($model->getMarker()) && $model->getMarker() != '') {
                    $filename = str_replace(" ", "_", $model->getMarker());
                    $filename = str_replace(":", "_", $filename);
                    $model->setMarker($locMarker . $filename);
                }

                $model->save();

                Mage::getSingleton('adminhtml/session')->addSuccess($this->__('The Location has been saved.'));
                if ($this->getRequest()->getParam('back')) {
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
        if ($this->getRequest()->getParam('id') > 0) {
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
        if (!is_array($locationIds)) {
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
        if (!is_array($locationIds)) {
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
     * Export order grid to CSV format
     *
     * @return void
     */
    public function exportCsvAction()
    {
        $fileName = 'locations.csv';
        $grid     = $this->getLayout()->createBlock('demac_multilocationinventory/adminhtml_location_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
    }

    /**
     * Read CSV file from disk.
     *
     * @param $csvFile
     *
     * @return array
     */
    public function readCSV($csvFile)
    {
        $csvFile      = Mage::getConfig()->getVarDir() . DS . 'import' . DS . 'multilocationinventory' . DS . $csvFile;
        $file_handle  = fopen($csvFile, 'r');
        $line_of_text = array();
        while (!feof($file_handle)) {
            $line_of_text[] = fgetcsv($file_handle, 1024);
        }
        fclose($file_handle);

        return $line_of_text;
    }

    /**
     * Import CSV Action
     *
     * @return void
     */
    public function importAction()
    {

        // Set path to CSV file
        $csvFile = Mage::getStoreConfig('demac_multilocationinventory/general/file');

        $csv = $this->readCSV($csvFile);
        foreach ($csv as $key => $line) {
            if ($key == 0) continue;
            $country = $this->getCountry($line[6]);

            if (!$country) {
                Mage::log('Error with country line:' . $key . ' value:' . $line[6], NULL, 'locationimport.log', TRUE);
                continue;
            }

            $region = Mage::getModel('directory/region')->loadByCode($line[4], $country);
            if (!$region->getName() && in_array($country, array('US', 'CA', 'DE', 'AT', 'CH', 'ES', 'FR', 'RO', 'FI', 'EE', 'LV', 'LT'))) {
                Mage::log('Error with region line:' . $key . ' value:' . $line[4], NULL, 'locationimport.log', TRUE);
            }

            if (is_null($line[9]) || $line[9] == '' || is_null($line[10]) || $line[10] == '') {
                $address  = implode(' ', array($line[2], $line[3], $line[4], $line[5],));
                $latLong  = Mage::helper('demac_multilocationinventory')->getLatLong($address, $line[6]);
                $line[9]  = $latLong[0];
                $line[10] = $latLong[1];

                if (is_null($latLong[0])) {
                    Mage::log('Error getting lat/long line:' . $key . ' value:' . $latLong[2], NULL, 'locationimport.log', TRUE);
                }
            }

            $model = Mage::getModel('demac_multilocationinventory/location');

            $data = array(
                'store_id'    => explode(',', $line[0]),
                'name'        => $line[1],
                'address'     => $line[2],
                'city'        => $region->getRegionId() ? $line[3] : $line[3] . ', ' . $line[4],
                'region_id'   => $region->getRegionId() ? $region->getRegionId() : '',
                'zipcode'     => $line[5],
                'country_id'  => $country,
                'phone'       => $line[7],
                'description' => $line[8],
                'lat'         => $line[9],
                'long'        => $line[10],
                'store_url'   => $line[11],
                'status'      => empty($line[12]) ? 0 : $line[12]
            );

            $model->setData($data);
            $model->save();

            $this->_redirect('*/*/');
        }

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
        if (is_null($this->_countries)) {
            $countriesList    = Mage::getResourceModel('directory/country_collection')
                ->loadData()
                ->toOptionArray(FALSE);
            $newCountriesList = array();
            foreach ($countriesList as $key => $val) {
                $newCountriesList[strtolower($val['label'])] = $val['value'];;
            }
            $this->_countries = $newCountriesList;
        }
        $countryCode = str_replace('USA', 'US', strtolower($needle));
        if (isset($this->_countries[$countryCode])) {
            return $this->_countries[$countryCode];
        }

        return FALSE;
    }

    /**
     * Get a list of regions for the selected country (AJAX).
     */
    public function regionAction()
    {
        $countryCode = $this->getRequest()->getParam('country');
        $options     = Mage::helper('demac_multilocationinventory')->getRegions($countryCode);

        // @TODO Start Refactor : Loop through this array in a view instead where we don't have to echo.
        foreach ($options as $option) {
            echo '<option value="' . $option['value'] . '">' . $option['label'] . '</option>';
        }
    }
}