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

class RespondrMage_RespondrAnalytics_Model_Observer
{
   

    /**
     * Add order information into Respondr block to render on checkout success pages
     *
     * @param Varien_Event_Observer $observer
     */
    public function setRespondrAnalyticsOnOrderSuccessPageView(Varien_Event_Observer $observer)
    {
        $orderIds = $observer->getEvent()->getOrderIds();
        if (empty($orderIds) || !is_array($orderIds)) {
            return;
        }
        $block = Mage::app()->getFrontController()->getAction()->getLayout()->getBlock('respondr_analytics');
        if ($block) {
            $block->setOrderIds($orderIds);
        }
    }


   
}
   
   