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
    const SERVER_URL = 'http://analytics.respondr.io';
	
	
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
        $isSubscribed = 0;
        $db = Mage::getSingleton('core/resource')->getConnection('core_read');
        $sql = "SELECT * FROM `newsletter_subscriber` WHERE `subscriber_email`='".$email."' AND `subscriber_status`='1' LIMIT 1";
        $result = $db->fetchAll($sql);
        if($result){
            $isSubscribed = 1;
        }
        return $isSubscribed;   
    }
}
