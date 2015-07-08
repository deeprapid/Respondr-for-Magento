<?php

class RespondrMage_RespondrAnalytics_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Config paths for using throughout the code
     */
    const XML_PATH_ACTIVE = 'respondr/analytics/active';
    const XML_PATH_SITE = 'respondr/analytics/site';
    const XML_PATH_PWTOKEN = 'respondr/analytics/pwtoken';

    // hard-code Respondr URL
    const SERVER_URL = 'http://dev.analytics.respondr.io';

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
    public function isCustomerSubscribed($email)
    {
        $isSubscribed = 0;
        $db = Mage::getSingleton('core/resource')->getConnection('core_read');
        $sql = "SELECT * FROM `newsletter_subscriber` WHERE `subscriber_email`='" . $email . "' AND `subscriber_status`='1' LIMIT 1";
        $result = $db->fetchAll($sql);
        if ($result) {
            $isSubscribed = 1;
        }

        return $isSubscribed;
    }
}
