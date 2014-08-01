<?php
/**
 * Created by PhpStorm.
 * User: MichaelK
 * Date: 4/9/14
 * Time: 7:05 AM
 */

/* @var $installer Mage_Customer_Model_Entity_Setup */
$installer = new Mage_Customer_Model_Entity_Setup('core_setup');

$installer->startSetup();

$attributeCode = 'saved_location';

$installer->addAttribute('customer', $attributeCode, array(
    'type'             => 'text',
    'input'            => 'text',
    'label'            => 'Saved Store/Location',
    'position'         => 999,
    'required'         => false,
    'visible_on_front' => 0,
    'is_user_defined'  => 0,
    'is_system'        => 1,
));

$installer->endSetup();