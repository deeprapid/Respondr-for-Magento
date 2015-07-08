<?php

class RespondrMage_RespondrAnalytics_Block_Respondr extends Mage_Core_Block_Template
{
    public function _trackSession()
    {
        $siteId = Mage::getStoreConfig(RespondrMage_RespondrAnalytics_Helper_Data::XML_PATH_SITE);

        echo "_raq.push(['trackSession', '$siteId']);";
    }

    public function trackProductView($sku, $name = '', $category = '', $price = '', $imageUrl = '', $desc = '')
    {
        echo <<<EOL
            _raq.push(['trackProductView', {
                sku: '$sku',
                name: '$name',
                category: '$category',
                price: '$price',
                imageUrl: '$imageUrl',
                desc: '$desc'
            }]);
EOL;
    }

    public function trackCategoryView($name)
    {
        echo <<<EOL
            _raq.push(['trackCategoryView', {
                name: '$name'
            }]);
EOL;
    }

    protected function _trackOrder()
    {
        $orderIds = $this->getOrderIds();

        if (empty($orderIds) || !is_array($orderIds)) {
            return;
        }

        $collection = Mage::getResourceModel('sales/order_collection')->addFieldToFilter('entity_id', array('in' => $orderIds));

        foreach ($collection as $order) {

            if ($order->getGrandTotal()) {
                $subtotal = $order->getGrandTotal() - $order->getShippingAmount() - $order->getShippingTaxAmount();
            } else {
                $subtotal = '0.00';
            }

            $data = array(
                'orderId'  => $order->getIncrementId(),
                'total'    => $order->getBaseGrandTotal(),
                'subTotal' => $subtotal,
                'tax'      => $order->getBaseTaxAmount(),
                'shipping' => $order->getBaseShippingAmount(),
                'discount' => 0
            );

            $json = json_encode($data);

            echo "_raq.push(['trackEcommerceOrder', $json]);";
        }
    }

    protected function _trackCartAdds()
    {
        $items = Mage::getSingleton('core/session')->getCartAdds();

        foreach ($items as $item) {
            $data = array(
                'sku'      => $item->getData('sku'),
                'name'     => $item->getData('name'),
                'category' => $item->getData('category'),
                'price'    => $item->getData('price'),
                'imageUrl' => $item->getData('imageUrl'),
                'desc'     => $item->getData('desc'),
                'qty'      => $item->getData('qty'),
            );

            $json = json_encode($data);

            echo "_raq.push(['addEcommerceItem', $json]);";
        }

        Mage::getSingleton('core/session')->unsCartAdds();
    }

    protected function _trackCartUpdates()
    {
        //$items = Mage::getModel('checkout/cart')->getQuote()->getAllVisibleItems();

        $items = Mage::getSingleton('core/session')->getCartUpdates();

        foreach ($items as $item) {
            $data = array(
                'sku' => $item->getData('sku'),
                'qty' => $item->getData('qty'),
            );

            $json = json_encode($data);

            echo "_raq.push(['updateEcommerceItem', $json]);";
        }

        Mage::getSingleton('core/session')->unsCartUpdates();
    }

    protected function _trackCartDeletes()
    {
        $items = Mage::getSingleton('core/session')->getCartDeletes();

        foreach ($items as $item) {
            $data = array(
                'sku' => $item->getData('sku'),
            );

            $json = json_encode($data);

            echo "_raq.push(['deleteEcommerceItem', $json]);";
        }

        Mage::getSingleton('core/session')->unsCartDeletes();
    }

    protected function _trackPageView()
    {
        $data = array(
            'pageTitle' => $this->getLayout()->getBlock('head')->getTitle(),
        );

        $json = json_encode($data);

        echo "_raq.push(['trackPageView', $json]);";
    }

    protected function _trackProductView()
    {
        $currentProduct = Mage::registry('current_product');

        if (!$currentProduct instanceof Mage_Catalog_Model_Product) {
            return;
        }

        $productId = $currentProduct->getId();
        $product = Mage::getModel('catalog/product')->load($productId);
        $categoryName = '';
        $categoryIds = $product->getCategoryIds();
        if (!empty($categoryIds)) {
            $categoryId = $categoryIds[0];
            $category = Mage::getModel('catalog/category')->load($categoryId);
            $categoryName = $category->getName();
        }

        $data = array(
            'sku'      => $currentProduct->getSku(),
            'name'     => $currentProduct->getName(),
            'category' => $categoryName,
            'price'    => $currentProduct->getPrice(),
            'imageUrl' => $product->getThumbnail(),
            'desc'     => $product->getDescription(),
        );

        $json = json_encode($data);

        echo "_raq.push(['trackProductView', $json]);";

        Mage::unregister('current_category');
    }

    protected function _trackCategoryView()
    {
        $currentCategory = Mage::registry('current_category');

        if (!$currentCategory instanceof Mage_Catalog_Model_Category) {
            return;
        }

        $data = array(
            'name' => $currentCategory->getName(),
        );

        $json = json_encode($data);

        echo "_raq.push(['trackCategoryView', $json]);";

        Mage::unregister('current_product');
    }

    protected function _saveContact()
    {
        $session = Mage::getSingleton('customer/session');

        if ($session->isLoggedIn()) {

            $customer = $session->getCustomer();

            $customerAddressId = $customer->getDefaultBilling();

            if ($customerAddressId) {
                $address = Mage::getModel('customer/address')->load($customerAddressId);
                $company = $address->getData('company');
                $phone = $address->getData('telephone');
            } else {
                $company = '';
                $phone = '';
            }

            $data = array(
                'email'     => $customer->getData('email'),
                'firstName' => $customer->getData('firstname'),
                'lastName'  => $customer->getData('lastname'),
                'company'   => $company,
                'phone'     => $phone,
            );

            $json = json_encode($data);

            echo "_raq.push(['saveContact', $json]);";
        }
    }

    protected function _trackSiteSearch()
    {
        if ($this->getRequest()->getControllerName() === 'result') {

            $data = array(
                'searchKeyword' => Mage::helper('catalogsearch')->getQuery()->getQueryText(),
            );

            $json = json_encode($data);

            echo "_raq.push(['trackSiteSearch', $json]);";
        }
    }

    protected function _toHtml()
    {
        if (!Mage::helper('respondranalytics')->isRespondrAnalyticsAvailable()) {
            return '';
        }

        return parent::_toHtml();
    }
}
