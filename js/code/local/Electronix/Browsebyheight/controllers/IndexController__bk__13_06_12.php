<?php

class Electronix_Browsebyheight_IndexController extends Mage_Core_Controller_Front_Action {
    public function indexAction() {
   
        $this->loadLayout();
        $block = $this->getLayout()->getBlock('browsebyheight');
    
        $hid = Mage::app()->getRequest()->getParam('height');     
        $collection = Mage::getModel('catalog/product')->getCollection();
        $collection->addAttributeToSelect('*');
        $collection->addFieldToFilter(array(
            array('attribute' => 'height', 'eq' => $hid),
        ));

        $block->setData('collection', $collection);     
        $this->renderLayout();
    }

}

?>