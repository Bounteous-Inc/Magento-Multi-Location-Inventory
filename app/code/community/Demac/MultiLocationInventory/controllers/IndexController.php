<?php

/**
 * Class Demac_MultiLocationInventory_IndexController
 */
class Demac_MultiLocationInventory_IndexController extends Mage_Core_Controller_Front_Action
{
    /**
     * Index Action
     */
    public function indexAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function saveLocationAction()
    {
        $savedLocation = $this->getRequest()->getParam('saved_location', FALSE);
        $clear         = $this->getRequest()->getParam('clear_location', FALSE);

        if ($clear) {
            $savedLocation = NULL;
        }

        if ($savedLocation || $clear) {
            if (Mage::getSingleton('customer/session')->isLoggedIn()) {
                $customer = Mage::getSingleton('customer/session')->getCustomer();
                $customer->setSavedLocation($savedLocation);
                $customer->save();
            }
            Mage::getSingleton('customer/session')->setSavedLocation($savedLocation);
        }

        $url = Mage::helper('core/url')->removeRequestParam($this->_getRefererUrl(), 'address');
        $this->getResponse()->setRedirect($url);

    }

    public function returnClosestStoresAction()
    {
        // Gets the current store's id
        $storeId = Mage::app()->getStore()->getStoreId();

        $address = $this->getRequest()->getParam('address');

        //$topthree = true;
        $three_closest = array();
        $model         = Mage::getModel('demac_multilocationinventory/location');
        $model->setAddressDisplay($address);
        $this->fetchCoordinates2($model);

        $num = (int)Mage::getStoreConfig('demac_multilocationinventory/general/num_results', $storeId);

        $units = Mage::getStoreConfig('demac_multilocationinventory/general/distance_units', $storeId);
        if (!(isset($units)) || empty($units)) {
            $units = $this->getRequest()->getParam('units', $units);
        }
        $radius = $this->getRequest()->getParam('radius');

        $lat  = $model->getLatitude();
        $long = $model->getLongitude();

        $dist = sprintf("(%s*acos(cos(radians(%s))*cos(radians(`lat`))*cos(radians(`long`)-radians(%s))+sin(radians(%s))*sin(radians(`lat`))))", $units == 'mi' ? 3959 : 6371, $lat, $long, $lat);

        $collection = $model->getCollection();
        $collection->join("locator_stores", "id = locator_stores.location_id");
        $collection->getSelect()->group('locator_stores.location_id');
        $savedLocation = Mage::getSingleton('customer/session')->getCustomer()->getSavedLocation();

        if (isset($savedLocation) && !empty($savedLocation)) {
            $collection->addExpressionFieldToSelect('distance', $dist, NULL)
                ->addFieldToFilter("store_id", array('eq' => Mage::app()->getStore()->getStoreId()))
                ->addFieldToFilter("id", array('neq' => Mage::getSingleton('customer/session')->getCustomer()->getSavedLocation()));
        } else {
            $collection->addExpressionFieldToSelect('distance', $dist, NULL)
                ->addFieldToFilter("store_id", array('eq' => Mage::app()->getStore()->getStoreId()));
        }

        $collection->getSelect()->having("distance <= $radius")->order('distance ASC');

        $privateFields = Mage::getConfig()->getNode('global/demac_multilocationinventory/private_fields');
        $i             = 0;
        $c             = 0;
        $limit         = (int)Mage::getStoreConfig('demac_multilocationinventory/general/closest_amount', $storeId);
        foreach ($collection as $loc) {
            if ($c == $limit) {
                break;
            };

            array_push($three_closest, $loc->getData());
            $c++;
        }

        $this->getResponse()->setBody(json_encode($three_closest));
    }

    public function fetchCoordinates2($model)
    {
        $url = "http://maps.googleapis.com/maps/api/geocode";

        if (substr($url, -1) != '/') {
            $url .= '/';
        }

        $url .= 'json?sensor=false&address=' . urlencode(preg_replace('#\r|\n#', ' ', $model->getAddressDisplay()));
        $cinit = curl_init();
        curl_setopt($cinit, CURLOPT_URL, $url);
        curl_setopt($cinit, CURLOPT_HEADER, 0);
        curl_setopt($cinit, CURLOPT_USERAGENT, $_SERVER["HTTP_USER_AGENT"]);
        curl_setopt($cinit, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($cinit);

        if (is_string($response) && !empty($response)) {
            $result = json_decode($response, FALSE);
            try {
                $data = $result->results[0]->geometry->location;
                $model->setLatitude($data->lat)->setLongitude($data->lng);
            } catch (Exception $e) {
            }
        }

        return $model;
    }

    public function updateClosestStoreAction()
    {
        $store_id = $this->getRequest()->getParam('storeId');
        $store    = Mage::getModel('demac_multilocationinventory/location')->load($store_id);

        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            $customer = Mage::getSingleton('customer/session')->getCustomer();
            $customer->setSavedLocation($store_id);
            $customer->save();
        }
        Mage::getSingleton('customer/session')->setSavedLocation($store_id);

        $this->getResponse()->setBody(json_encode($store->getData()));
    }
}