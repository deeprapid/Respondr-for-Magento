<?php

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

    public function productView(Varien_Event_Observer $observer)
    {
        $product = $observer->getProduct();
        $sku = $product->getId();
        $productName = $product->getName();
        $category = $product->getCategory();
        if (is_null($category)) {
            $cats = $product->getCategoryIds();
            $categoryId = $cats[0]; // just grab the first id
            //$categoryId = if (isset($cats[0]) {$categoryId = $cats[0]} else $categoryId = null; potential fix when no categories
            $category = Mage::getModel('catalog/category')->load($categoryId);
        }
        $categoryName = $category->getName();
        $price = number_format($product->getPrice(), 2);
        $imageUrl = $product->getImageUrl();
        $desc = $product->getDescription();

        $data = array(
            'sku'      => $sku,
            'name'     => $productName,
            'category' => $categoryName,
            'price'    => $price,
            'imageUrl' => $imageUrl,
            'desc'     => $desc
        );

        $json = json_encode($data);

        echo <<<EOL
            <script type='text/javascript'>
                var _raq = _raq || [];
                _raq.push(['trackProductView', $json]);
            </script>
EOL;
    }

    public function categoryView(Varien_Event_Observer $observer)
    {
        $event = $observer->getEvent();
        $category = $event->getCategory();
        $name = $category->getName();

        $data = array('name' => $name);

        $json = json_encode($data);

        echo <<<EOL
            <script type='text/javascript'>
                var _raq = _raq || [];
                _raq.push(['trackCategoryView', $json]);
            </script>
EOL;
    }

    public function pageView(Varient_Event_Observer $observer)
    {
        $pageTitle = '';

        $routeName = Mage::app()->getFrontController()->getRequest()->getRouteName();

        if ($routeName === 'cms') {
            $pageTitle = Mage::getSingleton('cms/page')->getIdentifier();
        }

        if (empty($pageTitle)) {
            $pageTitle = $routeName;
        }

        $data = array('pageTitle' => $pageTitle);

        $json = json_encode($data);

        echo <<<EOL
            <script type='text/javascript'>
                var _raq = _raq || [];
                _raq.push(['trackPageView', $json]);
            </script>
EOL;
    }

    public function customerRegister(Varien_Event_Observer $observer)
    {
        $event = $observer->getEvent();
        $customer = $event->getCustomer();
        $email = $customer->getEmail();
        $firstName = $customer->getFirstName();
        $lastName = $customer->getLastName();
        $company = $customer->getCompany();
        $phone = $customer->getPhone();

        $data = array(
            'email'     => $email,
            'firstName' => $firstName,
            'lastName'  => $lastName,
            'company'   => $company,
            'phone'     => $phone
        );

        $json = json_encode($data);

        echo <<<EOL
            <script type='text/javascript'>
                var _raq = _raq || [];
                _raq.push(['saveContact', $json]);
            </script>
EOL;
        echo("<script type='text/javascript'>alert('customerRegister');</script>");
    }

    public function catalogSearch(Varien_Event_Observer $observer)
    {
        $request = Mage::app()->getFrontController()->getRequest();

        $query = $request->getQuery();

        $searchKeyword = $query['q'];

        $data = array('searchKeyword' => $searchKeyword);

        $json = json_encode($data);

        echo <<<EOL
            <script type='text/javascript'>
                var _raq = _raq || [];
                _raq.push(['trackSiteSearch', $json]);
            </script>
EOL;
    }

    public function cartProductAdd(Varien_Event_Observer $observer)
    {
        $request = $observer->getEvent()->getControllerAction()->getRequest();
        $productId = $request->getParam('product', 0);
        $product = Mage::getModel('catalog/product')->load($productId);

        $sku = $product->getSku();

        if (is_null($sku)) {
            return;
        }

        $categoryName = '';

        $categoryIds = $product->getCategoryIds();

        if (!empty($categoryIds)) {
            $categoryId = $categoryIds[0];
            $category = Mage::getModel('catalog/category')->load($categoryId);
            $categoryName = $category->getName();
        }

        $cartAdds[] = new Varien_Object(array(
            'sku'      => $sku,
            'name'     => $product->getName(),
            'category' => $categoryName,
            'price'    => $product->getPrice(),
            'imageUrl' => $product->getThumbnail(),
            'desc'     => $product->getDescription(),
            'qty'      => $request->getParam('qty', 1),
        ));

        Mage::getSingleton('core/session')->setCartAdds($cartAdds);
    }

    public function cartUpdateOrClear(Varien_Event_Observer $observer)
    {
        $request = $observer->getEvent()->getControllerAction()->getRequest();
        $updateAction = $request->getParam('update_cart_action');

        $cartUpdates = array();
        $cartDeletes = array();

        if ($updateAction === 'empty_cart') {
            $quote = Mage::helper('checkout/cart')->getQuote();
            $quoteItems = $quote->getAllItems();
            /** @var $quoteItem Mage_Sales_Model_Quote_Item */
            foreach ($quoteItems as $quoteItem) {
                $sku = $quoteItem->getProduct()->getSku();
                $cartDeletes[] = new Varien_Object(array(
                    'sku' => $sku,
                ));
            }
        } else {
            $cartItems = $request->getParam('cart');
            foreach ($cartItems as $itemId => $data) {
                $cartItem = Mage::getSingleton('checkout/cart')->getQuote()->getItemById($itemId);
                $product = $cartItem->getProduct();
                $sku = $product->getSku();
                $qty = $data['qty'];
                if ($qty == 0) {
                    $cartDeletes[] = new Varien_Object(array(
                        'sku' => $sku,
                    ));
                } else {
                    $cartUpdates[] = new Varien_Object(array(
                        'sku' => $sku,
                        'qty' => $qty
                    ));
                }
            }
        }

        Mage::getSingleton('core/session')->setCartUpdates($cartUpdates);
        Mage::getSingleton('core/session')->setCartDeletes($cartDeletes);
    }

    public function cartUpdateItems(Varien_Event_Observer $observer)
    {
        echo "<script type='text/javascript'>alert('cartUpdateItems');</script>";
        //die();
        $cart = $observer->getCart();
        $items = $cart->getItems();

        foreach ($items as $item) {
            /* @var $item Mage_Sales_Model_Quote_Item */
            //            $parentItem = $i  tem->getParentItem();
            //            if ($parentItem) {
            //                $item = $parentItem;
            //            }
            $sku = $item->getSku();
            $qty = $item->getTotalQty();

            $data = array(
                'sku' => $sku,
                'qty' => $qty
            );

            $json = json_encode($data);

            echo <<<EOL
            <script type='text/javascript'>
                var _raq = _raq || [];
                _raq.push(['updaateEcommerceItem', $json]);
            </script>
EOL;
            echo "<script type='text/javascript'>alert('cartUpdateItems: [$sku][$qty]');</script>";
        }
    }
}
