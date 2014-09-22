<?php

class Vovsky_Customerprice_Model_Observer
{
    public function setFormElemRenderer($observer)
    {
        $form = $observer->getForm();
        if ($customerPrice = $form->getElement('customer_price')) {
            $customerPrice->setRenderer(
                Mage::app()->getLayout()->createBlock('customerprice/adminhtml_catalog_product_edit_tab_price_customer')
            );
        }
    }

    public function joinCollection($observer)
    {
        $cId = Mage::getSingleton('customer/session')->getCustomerGroupId();
        $wId = Mage::app()->getWebsite()->getId();
        $connection = $observer->getCollection()->getConnection();
        
        $joinCond = join(' AND ', array(
            'customer_price.entity_id = e.entity_id',
            $connection->quoteInto('customer_price.customer_group_id = (?)', $cId),
            $connection->quoteInto('customer_price.website_id IN(?)', array(0, $wId)),
        ));

        $colls = array('customer_price' => 'value');
        $select = $observer->getCollection()->getSelect();
        $select->joinLeft(
            array('customer_price' => $observer->getCollection()->getTable('customerprice/customer_price')),
            $joinCond,
            $colls
        );
    }

    public function applyCustomerPrice($observer)
    {
        $customerPrice = $observer->getQuoteItem()->getProduct()->getCustomerPrice();
        if ($customerPrice) {
            $item = $observer->getQuoteItem();
            if ($item->getParentItem()) {
                $item = $item->getParentItem();
            }
            $item->setCustomPrice($customerPrice);
        }
    }

    public function joinSalesQuoteCollection($observer)
    {
        $collection  = $observer->getCollection();
        $customerPrices = array();
        $productIds = array();
        foreach ($collection->getItems() as $item) {
            $productIds[] = $item->getId();
            $customerPrices[$item->getId()] = array();
        }
        if (!$productIds) {
            return $this;
        }

        /** @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
        $attribute = $observer->getCollection()->getAttribute('customer_price');
        if ($attribute->isScopeGlobal()) {
            $websiteId = 0;
        } else if ($collection->getStoreId()) {
            $websiteId = Mage::app()->getStore($collection->getStoreId())->getWebsiteId();
        }

        $adapter   = $collection->getConnection();
        $columns   = array(
            'price_id'      => 'value_id',
            'website_id'    => 'website_id',
            'all_groups'    => 'all_groups',
            'cust_group'    => 'customer_group_id',
            'price'         => 'value',
            'product_id'    => 'entity_id'
        );
        $select  = $adapter->select()
            ->from($collection->getTable('customerprice/customer_price'), $columns)
            ->where('entity_id IN(?)', $productIds);

        if ($websiteId == '0') {
            $select->where('website_id = ?', $websiteId);
        } else {
            $select->where('website_id IN(?)', array('0', $websiteId));
        }

        foreach ($adapter->fetchAll($select) as $row) {
            $customerPrices[$row['product_id']][] = array(
                'website_id'    => $row['website_id'],
                'cust_group'    => $row['all_groups'] ? Mage_Customer_Model_Group::CUST_GROUP_ALL : $row['cust_group'],
                'price'         => $row['price'],
                'website_price' => $row['price'],

            );
        }

        $backend = $attribute->getBackend();

        foreach ($observer->getCollection()->getItems() as $item) {
            $data = $customerPrices[$item->getId()];

            $cId = Mage::getSingleton('customer/session')->getCustomerGroupId();
            $customerprice = 0;
            if (count($data)) {
                foreach ($data as $dataItem) {
                    if ($dataItem['cust_group'] == $cId) {
                        $customerprice = $dataItem['website_price'];
                    }
                }
            }

            if (!empty($data) && $websiteId) {
                $data = $backend->preparePriceData($data, $item->getTypeId(), $websiteId);
                $customerprice = $data;
            }
            $item->setData('customer_price', $customerprice);
        }
    }

    public function setProduct($observer)
    {
        $item = $observer->getQuoteItem();
        $product = $observer->getProduct();
        $item->setCustomerPrice($product->getCustomerPrice());

    }
    public function updateCustomerPrice($observer)
    {
        foreach ($observer->getCart()->getQuote()->getItemsCollection() as $item/* @var $item Mage_Sales_Model_Quote_Item */) {
            if ($item->getParentItem()) {
                $item = $item->getParentItem();
            }

            $customerPrice = $item->getCustomerPrice();
            if ($customerPrice) {
                $item->setCustomPrice($customerPrice);
            }
        }
    }
}