<?php

class Electronix_Slideshow_Block_Slideshow extends Mage_Core_Block_Template {

    public function getProductSlideShow() {
       
        $read = Mage::getSingleton('core/resource')->getConnection('core_read');
        $select = $read->select('*')->from('gallery')->order('created_time DESC')->limit(5);      
//        echo $select->__toString();
        $productCollection = $read->fetchAll($select); 
   
        return $productCollection;
                   
    }

}

?>