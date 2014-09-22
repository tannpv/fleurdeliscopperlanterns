
<?php

class Mage_Catalog_Block_Product_Allproduct extends Mage_Catalog_Block_Product_List
{   
   function get_prod_count()
   {      
      Mage::getSingleton('catalog/session')->unsLimitPage();
      return (isset($_REQUEST['limit'])) ? intval($_REQUEST['limit']) : 5;
   }

   function get_cur_page()
   {
      return (isset($_REQUEST['p'])) ? intval($_REQUEST['p']) : 1;
   }
   protected function _getProductCollection()
   {
      $dir = Mage::app()->getRequest()->getParam('dir');
      $order = Mage::app()->getRequest()->getParam('order');
      $collection = Mage::getResourceModel('catalog/product_collection');
      $collection->addAttributeToSelect('*');
	  if( $dir && $order ) {
            $collection->addAttributeToSort($order, $dir);
      }
      $collection  ->setPageSize($this->get_prod_count());
      $collection  ->setCurPage($this->get_cur_page());
      $this->setProductCollection($collection);

      return $collection;
   }
}
?>