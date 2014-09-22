<?php
/**
 * Magmodules.eu
 * http://www.magmodules.eu
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@magmodules.eu so we can send you a copy immediately.
 *
 * @category    Magmodules
 * @package     Magmodules_Colorbox
 * @author      Magmodules <info@magmodules.eu)
 * @copyright   Copyright (c) 2013 (http://www.magmodules.eu)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
 
class Magmodules_Colorbox_Helper_Data extends Mage_Core_Helper_Abstract
{

	public function getMainSize()
	{
		$size = Mage::getStoreConfig('colorbox/image/size_main'); 
	    if (strstr($size, '_')) {		
			$size = str_replace('_',',', $size); 
		} else {
			$size = '256,265';          			
		}
		return $size;
	}

	public function getPopupSize()
	{
		$size = Mage::getStoreConfig('colorbox/image/size_popup'); 
	    if (strstr($size, '_')) {		
			$size = str_replace('_',',', $size); 
		} else {
			$size = '500,500';          			
		}
		return $size;
	}


	public function getThumbSize()
	{
		$size = Mage::getStoreConfig('colorbox/image/size_thumb'); 
	    if (strstr($size, '_')) {		
			$size = str_replace('_',',', $size); 
		} else {
			$size = '60,60';          			
		}
		return $size;
	}
   
}
