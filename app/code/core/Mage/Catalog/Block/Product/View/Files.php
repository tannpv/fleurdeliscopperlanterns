<?php

/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Catalog
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Catalog product price block
 *
 * @category   Mage
 * @package    Mage_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Catalog_Block_Product_View_Files extends Mage_Catalog_Block_Product_View_Abstract {

    protected $_product;

    public function __construct() {
        parent::__construct();
    }

    /**
     * Retrieve product object
     *
     * @return Mage_Catalog_Model_Product
     */
    public function getProduct() {
        if (!$this->_product) {
            if (Mage::registry('current_product')) {
                $this->_product = Mage::registry('current_product');
            } else {
                $this->_product = Mage::getSingleton('catalog/product');
            }
        }
        return $this->_product;
    }

    /*
     * *    Author: tannpv
     * *    Description get pdf & cad file for fleur
     * *    Date date
     */

    public function getFiles() {
        $product = Mage::getModel('catalog/product')->load($this->getProduct()->getId());
        $childProducts = Mage::getModel('catalog/product_type_configurable')
                ->getUsedProducts(null, $product);
        $files = array();
        $parentFile = $this->getFile($product);
        $files[] = $parentFile;
        foreach ($childProducts as $child) {
            $file = $this->getFile($child, 'fleur_size');
            $files[] = $file;
        }
        return $files;
    }

    public function getFile($product, $attr = null) {
        $file = new stdClass();
        if ($attr) {
            $item_size = $product->getAttributeText($attr);
        } else {
            $item_size = "&nbsp;";
        }
        $collection = Mage::getModel('files/files')->getFileCollection($product->getId());
        foreach ($collection->getData() as $fileCollection) {
            $path = Mage::getUrl('files/index/download' . '/id/' . $fileCollection['file_timestamp']);
            if (strtolower ($fileCollection['file_type']) == 'pdf') {
                $file->pdf = array('name' => $fileCollection['file_title'], 'path' => $path);
            } elseif (strtolower ($fileCollection['file_type']) == 'dwg') {
                $file->cad = array('name' => $fileCollection['file_title'], 'path' => $path);
            }
        }
        $file->item_size = $item_size;
        return $file;
    }

}
