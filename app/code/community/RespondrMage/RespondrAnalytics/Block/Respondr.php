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

    protected function _getPageview()
    {

        $currentproduct = Mage::registry('current_product');
        $currentcategory = Mage::registry('current_category');
        // is this a product view
        if ($currentproduct instanceof Mage_Catalog_Model_Product) {
            $product_id = $currentproduct->getId();
            $_product = Mage::getModel('catalog/product')->load($product_id);
            $cats = $_product->getCategoryIds();
            $category_id = $cats[0]; // just grab the first id
            $category = Mage::getModel('catalog/category')->load($category_id);
            $category_name = $category->getName();
            $product = $currentproduct->getName();
            //$description = str_replace('"', "", $_product->getDescription());
            $description = "";

            echo '_raq.push(["trackProductView", {sku: "' . $this->jsQuoteEscape($this->jsQuoteEscape($currentproduct->getSku()), '"') . '", name: "' . $this->jsQuoteEscape($this->jsQuoteEscape($product), '"') . '", categories: "' . $this->jsQuoteEscape($category_name) . '", price: ' . $currentproduct->getPrice() . ', imageUrl: "'. $_product->getImageUrl() . '", desc: "'. $this->jsQuoteEscape($description) .'"}]);';
            Mage::unregister('current_category');
        
        // is this a category view
        } elseif($currentcategory instanceof Mage_Catalog_Model_Category) {
            echo '_raq.push(["trackCategoryView", {name: "' . $currentcategory->getName() . '"}]);';
            Mage::unregister('current_product');
        
        // is this a search
        } elseif (false)  {

        // default to page view
        } else {
            $title = $this->getLayout()->getBlock('head')->getTitle();
            echo '_raq.push(["trackPageView", {pageTitle: "' . $title . '"}]);';
        }
        
    }

    protected function _getOrdersTrackingCode()
    {
        $orderIds = $this->getOrderIds();
        if (empty($orderIds) || !is_array($orderIds)) {
            return;
        }

        $collection = Mage::getResourceModel('sales/order_collection')
            ->addFieldToFilter('entity_id', array('in' => $orderIds));
        $obj = '';
        foreach ($collection as $order) {
            if ($order->getGrandTotal()) {
                $subtotal = $order->getGrandTotal() - $order->getShippingAmount() - $order->getShippingTaxAmount();
            } else {
                $subtotal = '0.00';
            }
            $obj = '_raq.push(["trackEcommerceOrder", {' .
                  'id: "' . $order->getIncrementId() .'",' .
                  'total: ' . $order->getBaseGrandTotal() .',' .
                  'subTotal: ' . $subtotal .',' .
                  'tax: ' . $order->getBaseTaxAmount() .',' .
                  'shipping: ' . $order->getBaseShippingAmount();
            $obj = $obj . '}]);';

            // get data for guest checkout
            $obj = $obj . '_raq.push(["saveContact", {' .
                  'email: "' . $order->getData("customer_email") .'",' .
                  'firstName: "' . $order->getData("customer_firstname") .'",' .
                  'lastName: "' . $order->getData("customer_lastname") .'"';
            $obj = $obj . '}]);';

            echo $obj;
        }
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
            echo '_raq.push(["updateEcommerceItem", {sku: "' . $cartitem->getSku() . '", name: "' . $nameofproduct . '", categories: "' . $category_name . '", price: ' . $cartitem->getPrice() . ', qty: ' . $cartitem->getQty() . '}]);';
            echo "\n";
        }
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

           $obj = '_raq.push(["saveContact", {' .
                  'email: "' . $customer->getData("email") .'",' .
                  'firstName: "' . $customer->getData("firstname") .'",' .
                  'lastName: "' . $customer->getData("lastname") .'"';

           if ($customerAddressId){
               $address = Mage::getModel('customer/address')->load($customerAddressId);
               $obj = $obj . ', company: "' . $address->getData("company") . '",' .
                             'phone: "' . $address->getData("telephone") . '"';
           }
           $obj = $obj . '}]);';
           echo $obj;
        }
        
    }

    /**
     * Render Respondr tracking scripts
     *
     * @return string
     */
    protected function _toHtml()
    {
        return parent::_toHtml();
    }
}
