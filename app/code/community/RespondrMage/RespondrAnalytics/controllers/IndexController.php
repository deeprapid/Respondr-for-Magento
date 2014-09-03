<?php
/**
 *
 * Respondr Extension for Magento created by Respondr Inc.
 * Based on Piwik Extension for Magento by Adrian Speyer
 *
 * @category   RespondrMage
 * @package    RespondrMage_RespondrAnalytics
 * @copyright  Copyright (c) 2014 Respondr Inc. (http://www.respondr.io)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

class RespondrMage_RespondrAnalytics_IndexController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {

     $this->loadLayout();
		
		$active = Mage::getStoreConfig(RespondrMage_RespondrAnalytics_Helper_Data::XML_PATH_ACTIVE);
		$siteId = Mage::getStoreConfig(RespondrMage_RespondrAnalytics_Helper_Data::XML_PATH_SITE);
		$pwtoken= Mage::getStoreConfig(RespondrMage_RespondrAnalytics_Helper_Data::XML_PATH_PWTOKEN);

		$installPath = RespondrMage_RespondrAnalytics_Helper_Data::SERVER_URL;
/*
      if (!empty($pwtoken)){
	  $block = $this->getLayout()->createBlock('core/text', 'respondr-block')->setText('<iframe src="'.$installPath.'/index.php?module=Widgetize&action=iframe&moduleToWidgetize=Dashboard&actionToWidgetize=index&idSite='.$siteId.'&period=week&date=yesterday&token_auth='.$pwtoken.'" frameborder="0" marginheight="0" marginwidth="0" width="100%" height="1000px"></iframe>');
       $this->_addContent($block);
	   $this->_setActiveMenu('respondr_menu')->renderLayout();
	   }
	   
	  	   
	if (empty($pwtoken)){ 
	  $block = $this->getLayout()->createBlock('core/text', 'respondr-block')->setText('You are missing your Piwik Token Auth Key. Get it from your API tab in your Piwik Install.');
       $this->_addContent($block);
	   $this->_setActiveMenu('respondr_menu')->renderLayout();
	   }
	
    }
 */
}
