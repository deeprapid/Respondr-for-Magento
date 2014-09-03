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

class RespondrMage_RespondrAnalytics_Block_Respondr extends Mage_Core_Block_Template
{

    /**
     * Get a specific page name (may be customized via layout)
     *
     * @return string|null
     */
    public function getPageName()
    {
        return $this->_getData('page_name');
    }

    /**
     * Render information about specified orders and their items
     * http://piwik.org/docs/ecommerce-analytics/
     */
    protected function _getOrdersTrackingCode()
    {
        $orderIds = $this->getOrderIds();
        if (empty($orderIds) || !is_array($orderIds)) {
            return;
        }

        $collection = Mage::getResourceModel('sales/order_collection')
            ->addFieldToFilter('entity_id', array('in' => $orderIds));
        $result = array();

        foreach ($collection as $order) {
            foreach ($order->getAllVisibleItems() as $item) {

                //get category name
                $product_id = $item->product_id;
                $_product = Mage::getModel('catalog/product')->load($product_id);
                $cats = $_product->getCategoryIds();
                $category_id = $cats[0]; // just grab the first id
                $category = Mage::getModel('catalog/category')->load($category_id);
                $category_name = $category->getName();


                if ($item->getQtyOrdered()) {
                    $qty = number_format($item->getQtyOrdered(), 0, '.', '');
                } else {
                    $qty = '0';
                }
                $result[] = sprintf("respondrTracker.addEcommerceItem( '%s', '%s', '%s', %s, %s);",
                    $this->jsQuoteEscape($item->getSku()),
                    $this->jsQuoteEscape($item->getName()),
                    $category_name,
                    $item->getBasePrice(),
                    $qty
                );

            }
            foreach ($collection as $order) {
                if ($order->getGrandTotal()) {
                    $subtotal = $order->getGrandTotal() - $order->getShippingAmount() - $order->getShippingTaxAmount();
                } else {
                    $subtotal = '0.00';
                }
                $result[] = sprintf("respondrTracker.trackEcommerceOrder( '%s', %s, %s, %s, %s);",
                    $order->getIncrementId(),
                    $order->getBaseGrandTotal(),
                    $subtotal,
                    $order->getBaseTaxAmount(),
                    $order->getBaseShippingAmount()
                );


            }
        }
        return implode("\n", $result);
    }

    /**
     * Render information when cart updated
     * http://piwik.org/docs/ecommerce-analytics/
     */
    protected function _getEcommerceCartUpdate()
    {

        $cart = Mage::getModel('checkout/cart')->getQuote()->getAllVisibleItems();

        foreach ($cart as $cartitem) {

            //get category name
            $product_id = $cartitem->product_id;
            $_product = Mage::getModel('catalog/product')->load($product_id);
            $cats = $_product->getCategoryIds();
            if (isset($cats)) {
                $category_id = $cats[0];
            } // just grab the first id
            $category = Mage::getModel('catalog/category')->load($category_id);
            $category_name = $category->getName();
            $nameofproduct = $cartitem->getName();
            $nameofproduct = str_replace('"', "", $nameofproduct);

            if ($cartitem->getPrice() == 0 || $cartitem->getPrice() < 0.00001):
                continue;
            endif;
            echo 'respondrTracker.addEcommerceItem("' . $cartitem->getSku() . '","' . $nameofproduct . '","' . $category_name . '",' . $cartitem->getPrice() . ',' . $cartitem->getQty() . ');';
            echo "\n";
        }

        //total in cart
        $grandTotal = Mage::getModel('checkout/cart')->getQuote()->getGrandTotal();
        if ($grandTotal == 0) echo ''; else
            echo 'respondrTracker.trackEcommerceCartUpdate(' . $grandTotal . ');';
        echo "\n";
    }

    /**
     * Render information when product page view
     * http://piwik.org/docs/ecommerce-analytics/
     */
    protected function _getProductPageview()
    {

        $currentproduct = Mage::registry('current_product');

        if (!($currentproduct instanceof Mage_Catalog_Model_Product)) {
            return;
        }


        $product_id = $currentproduct->getId();
        $_product = Mage::getModel('catalog/product')->load($product_id);
        $cats = $_product->getCategoryIds();
        $category_id = $cats[0]; // just grab the first id
        //$category_id = if (isset($cats[0]) {$category_id = $cats[0]} else $category_id = null; potential fix when no catgeories
        $category = Mage::getModel('catalog/category')->load($category_id);
        $category_name = $category->getName();
        $product = $currentproduct->getName();
        //$product = str_replace('"', "", $product);
        //$description = str_replace('"', "", $_product->getDescription());
        $description = "";

        echo 'respondrTracker.setEcommerceView("' . $this->jsQuoteEscape($currentproduct->getSku()) . '", "' . $this->jsQuoteEscape($product) . '","' . $this->jsQuoteEscape($category_name) . '",' . $currentproduct->getPrice() . ',"'. $_product->getImageUrl() . '","'. $this->jsQuoteEscape($description) .'");';
        Mage::unregister('current_category');
    }

    /**
     * Render information of category view
     * http://piwik.org/docs/ecommerce-analytics/
     */
    protected function _getCategoryPageview()
    {
        $currentcategory = Mage::registry('current_category');

        if (!($currentcategory instanceof Mage_Catalog_Model_Category)) {
            return;
        }
        echo 'respondrTracker.setEcommerceView(false,false,"' . $currentcategory->getName() . '",false,false);';
        Mage::unregister('current_product');
    }

    /**
     * Respondr lead/customer capture...
     * Saves user's firstname, lastname, company, email, telephone 
     * and optin status as json-encoded custom variable
    */   
    protected function _getUser() {
        
        if (Mage::getSingleton('customer/session')->isLoggedIn()) {          
           $customer = Mage::getSingleton('customer/session')->getCustomer();                    
           $customerAddressId = Mage::getSingleton('customer/session')->getCustomer()->getDefaultBilling(); 
           $optin = Mage::helper('respondranalytics')->isCustomerSubscribed($customer->getData("email"));        
           echo 'respondrTracker.setCustomVariable (1, "email", "'.$customer->getData("email").'", scope = "visit");';   
           echo 'respondrTracker.setCustomVariable (2, "firstName", "'.$customer->getData("firstname").'", scope = "visit");'; 
           echo 'respondrTracker.setCustomVariable (3, "lastName", "'.$customer->getData("lastname").'", scope = "visit");'; 
           if ($customerAddressId){
               $address = Mage::getModel('customer/address')->load($customerAddressId);
               echo 'respondrTracker.setCustomVariable (4, "company", "'.$address->getData("company").'", scope = "visit");'; 
               echo 'respondrTracker.setCustomVariable (5, "phone", "'.$address->getData("telephone").'", scope = "visit");'; 
           }
        }
        
        
    }

    protected function _getOptinStatus() {
        
        if (Mage::getSingleton('customer/session')->isLoggedIn()) {          
           $customer = Mage::getSingleton('customer/session')->getCustomer();
           $optin = Mage::helper('respondranalytics')->isCustomerSubscribed($customer->getData("email"));        
           echo 'respondrTracker.setCustomVariable (10, "optin", "'.$optin.'", scope = "visit");';
        }
        
        
    }

    /**
     * Render Respondr tracking scripts
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!Mage::helper('respondranalytics')->isRespondrAnalyticsAvailable()) {
            return '';
        }

        return parent::_toHtml();
    }
}
