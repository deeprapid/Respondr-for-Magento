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
class RespondrMage_RespondrAnalytics_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Config paths for using throughout the code
     */
    const XML_PATH_ACTIVE  = 'respondr/analytics/active';
    const XML_PATH_SITE =    'respondr/analytics/site';
	const XML_PATH_PWTOKEN = 'respondr/analytics/pwtoken';

    // hard-code Respondr URL
    const SERVER_URL = 'http://v2.analytics.respondr.io/';
	
    /**
     *
     * @param mixed $store
     * @return bool
     */
    public function isRespondrAnalyticsAvailable($store = null)
    {
        $siteId = Mage::getStoreConfig(self::XML_PATH_SITE, $store);
		return $siteId && Mage::getStoreConfigFlag(self::XML_PATH_ACTIVE, $store);
	}

    /**
     * Check the customer's newsletter optin status
     * @return string
     */
    public function isCustomerSubscribed($email) {
        $subscriber = Mage::getModel('newsletter/subscriber')->loadByEmail($email);
        if($subscriber->getId()) {
            return $subscriber->getData('subscriber_status') == Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED;
        } else {
            return 0;
        }
    }
}
