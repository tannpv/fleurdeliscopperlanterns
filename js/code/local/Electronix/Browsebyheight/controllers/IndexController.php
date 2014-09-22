<?php

class Electronix_Browsebyheight_IndexController extends Mage_Core_Controller_Front_Action {

    public function indexAction() {

        $this->loadLayout();
        $block = $this->getLayout()->getBlock('browsebyheight');

        $hid = Mage::app()->getRequest()->getParam('height');
        $dir = Mage::app()->getRequest()->getParam('dir');
        $order = Mage::app()->getRequest()->getParam('order');
        $collection = Mage::getModel('catalog/product')->getCollection();
        $collection->addAttributeToSelect('*');
        $collection->addFieldToFilter(array(
            array('attribute' => 'height', 'eq' => $hid),
        ));
        if( $dir && $order ) {
            $collection->addAttributeToSort($order, $dir);
        }
        //echo $collection->getSelect();

        $block->setData('collection', $collection);
        $this->renderLayout();
    }

}

?>