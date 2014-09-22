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
 * @package     Mage_Sales
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Sales_Model_Quote_Address_Total_Subtotal extends Mage_Sales_Model_Quote_Address_Total_Abstract {

    /**
     * Collect address subtotal
     *
     * @param   Mage_Sales_Model_Quote_Address $address
     * @return  Mage_Sales_Model_Quote_Address_Total_Subtotal
     */
    public function collect(Mage_Sales_Model_Quote_Address $address) {
        parent::collect($address);
        $address->setTotalQty(0);

        $baseVirtualAmount = $virtualAmount = 0;

        /**
         * Process address items
         */
        $items = $this->_getAddressItems($address);
        foreach ($items as $item) {
            if ($this->_initItem($address, $item) && $item->getQty() > 0) {
                /**
                 * Separatly calculate subtotal only for virtual products
                 */
                if ($item->getProduct()->isVirtual()) {
                    $virtualAmount += $item->getRowTotal();
                    $baseVirtualAmount += $item->getBaseRowTotal();
                }
            } else {
                $this->_removeItem($address, $item);
            }
        }

        $address->setBaseVirtualAmount($baseVirtualAmount);
        $address->setVirtualAmount($virtualAmount);

        /**
         * Initialize grand totals
         */
        Mage::helper('sales')->checkQuoteAmount($address->getQuote(), $address->getSubtotal());
        Mage::helper('sales')->checkQuoteAmount($address->getQuote(), $address->getBaseSubtotal());
        return $this;
    }

    /**
     * Address item initialization
     *
     * @param  $item
     * @return bool
     */
    protected function _initItem($address, $item) {
        if ($item instanceof Mage_Sales_Model_Quote_Address_Item) {
            $quoteItem = $item->getAddress()->getQuote()->getItemById($item->getQuoteItemId());
        } else {
            $quoteItem = $item;
        }
        $product = $quoteItem->getProduct();

        $product->setCustomerGroupId($quoteItem->getQuote()->getCustomerGroupId());

        /**
         * Quote super mode flag mean what we work with quote without restriction
         */
        if ($item->getQuote()->getIsSuperMode()) {
            if (!$product) {
                return false;
            }
        } else {
            if (!$product || !$product->isVisibleInCatalog()) {
                return false;
            }
        }

        if ($quoteItem->getParentItem() && $quoteItem->isChildrenCalculated()) {
            $finalPrice = $quoteItem->getParentItem()->getProduct()->getPriceModel()->getChildFinalPrice(
                    $quoteItem->getParentItem()->getProduct(), $quoteItem->getParentItem()->getQty(), $quoteItem->getProduct(), $quoteItem->getQty()
            );

            $item->setPrice($finalPrice)
                    ->setBaseOriginalPrice($finalPrice);
            $item->calcRowTotal();
        } else if (!$quoteItem->getParentItem()) {
            $finalPrice = $product->getFinalPrice($quoteItem->getQty());
            /**
             * *
             * *  Description:    get final price from simple product for associated product and option price
             * *  Author:         tannpv
             * *  Date:           2014/09/10     
             * */
           
            $optPrice = 0;
            if ($optionIds = $product->getCustomOption('option_ids')) {
                $basePrice = $optPrice;
                foreach (explode(',', $optionIds->getValue()) as $optionId) {
                    if ($option = $product->getOptionById($optionId)) {

                        $confItemOption = $product->getCustomOption('option_' . $option->getId());
                        $group = $option->groupFactory($option->getType())
                                ->setOption($option)
                                ->setConfigurationItemOption($confItemOption);

                        $optPrice += $group->getOptionPrice($confItemOption->getValue(), $basePrice);
                    }
                }
            }

            $price = 0;
            $simpleProductType = $item->getOptionByCode('simple_product');
            if ($simpleProductType) {
                $price = Mage::getModel('catalog/product')->load($simpleProductType->getProduct()->getId())->getPrice();
            }
            $finalPrice = $price+$optPrice;

            $item->setPrice($finalPrice)
                    ->setBaseOriginalPrice($finalPrice);
            $item->calcRowTotal();
            $this->_addAmount($item->getRowTotal());
            $this->_addBaseAmount($item->getBaseRowTotal());
            $address->setTotalQty($address->getTotalQty() + $item->getQty());
        }

        return true;
    }

    /**
     * Remove item
     *
     * @param  $address
     * @param  $item
     * @return Mage_Sales_Model_Quote_Address_Total_Subtotal
     */
    protected function _removeItem($address, $item) {
        if ($item instanceof Mage_Sales_Model_Quote_Item) {
            $address->removeItem($item->getId());
            if ($address->getQuote()) {
                $address->getQuote()->removeItem($item->getId());
            }
        } elseif ($item instanceof Mage_Sales_Model_Quote_Address_Item) {
            $address->removeItem($item->getId());
            if ($address->getQuote()) {
                $address->getQuote()->removeItem($item->getQuoteItemId());
            }
        }

        return $this;
    }

    /**
     * Assign subtotal amount and label to address object
     *
     * @param   Mage_Sales_Model_Quote_Address $address
     * @return  Mage_Sales_Model_Quote_Address_Total_Subtotal
     */
    public function fetch(Mage_Sales_Model_Quote_Address $address) {
        $address->addTotal(array(
            'code' => $this->getCode(),
            'title' => Mage::helper('sales')->__('Subtotal'),
            'value' => $address->getSubtotal()
        ));
        return $this;
    }

    /**
     * Get Subtotal label
     *
     * @return string
     */
    public function getLabel() {
        return Mage::helper('sales')->__('Subtotal');
    }

}
