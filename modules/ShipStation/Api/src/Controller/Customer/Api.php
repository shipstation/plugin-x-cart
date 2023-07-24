<?php
/*
 * Copyright 2015 ShipStation. All rights reserved.
 * This file and its content is copyright of ShipStation for use with the ShipStation software solution.
 * Any redistribution or reproduction of part or all of the contents in any form is strictly prohibited.
 * You may not, except with our express written permission, distribute or commercially exploit the content.
 * Nor may you transmit it or store it in any other website or other form of electronic retrieval system.
 */

namespace ShipStation\Api\Controller\Customer;

/**
 * ShipStation extension controller
 */
class Api extends \XLite\Controller\Customer\ACustomer 
{
    /**
     * Xml Data
     * @var String
     */
    protected $xmlData = '';
    
    /**
     * Get the action parameters from shipstation
     */
    public function getAction() 
    {
       /*
         * Check the username and password for authorized user
         */
        if (!isset($_SERVER['PHP_AUTH_USER'])) {
            if (!isset($_GET['SS-UserName'])) {
                header('WWW-Authenticate: Basic realm="ShipStation"');
                header('HTTP/1.0 401 Unauthorized');
                echo 'Unauthorized';
                exit;
            } else {
                $strUserEmail = $_GET['SS-UserName'];
                $userPassword = $_GET['SS-Password'];
            }
        } else {
            $strUserEmail = $_SERVER['PHP_AUTH_USER'];
            $userPassword = $_SERVER['PHP_AUTH_PW'];
        }
        /*
         * Encrypt the user password
         */
        $strShaPassword = \XLite\Core\Auth::getInstance()->encryptPassword($userPassword);
        /*
         * Get the User deatils by using the email address and password
         */
        $objProfileData = \XLite\Core\Database::getRepo('XLite\Model\Profile')->findByLoginPassword($strUserEmail, $strShaPassword, 0);
        if (empty($objProfileData)) {
            header('WWW-Authenticate: Basic realm="ShipStation"');
            header('HTTP/1.0 401 Unauthorized');
            echo 'Unauthorized';
            exit;
        } elseif (!$objProfileData->isAdmin()) {
            echo 'Unauthorized, no admin in database';
            exit;
        }
        /*
         * Get the action parameter
         */
        if ($_GET['action'] == 'export') {
            $this->actionExport(); //Call order export function
        } elseif ($_GET['action'] == 'verifystatus') {
            echo 'true'; //Setup the connection from shipstation
        } elseif ($_GET['action'] == 'update') {
            $this->actionStatusUpdate(); //Call status update function
        } else {
            echo 'No action parameter. Please contact software provider.';
        }
        exit;
    }

    /**
     * Function to update the order status
     */

    protected function actionStatusUpdate() 
    {
        if (!isset($_GET['order_number']) || !$_GET['order_number']) {
            echo 'No order number found in action';
            return;
        }

        /**
         * Check if the order is present in xcart
         */
        $objOrder = \XLite\Core\Database::getRepo('XLite\Model\Order')->findOneByOrderNumber(intval($_GET['order_number']));
        if (!$objOrder) {
            echo 'Order does not exist in database';
            return;
        }

        /**
         * Change the order shipping status to shipped
         * Change the order payment status to paid
         * Change the Shipping Method based on service code recived
         */
        $objOrder->setShippingStatus(\XLite\Model\Order\Status\Shipping::STATUS_SHIPPED);
        $objOrder->setPaymentStatus(\XLite\Model\Order\Status\Payment::STATUS_PAID);
        if (!isset($_GET['shipperServiceID']) || !$_GET['shipperServiceID']) {
            return;
        }

        // Check the shipstation service mapping with the X -Cart.
        $serviceCode = \XLite\Core\Database::getRepo('XLite\Model\Order')->getShippingServices($_GET['shipperServiceID']);
        
        if ($serviceCode) {
            $objShippingMethod = \XLite\Core\Database::getRepo('XLite\Model\Shipping\Method')->findOneBy(
                array('code' => $serviceCode)
            );
            if (!empty($objShippingMethod)) {
                if ($objShippingMethod->getMethodId()) {
                    $objOrder->setShippingId($objShippingMethod->getMethodId());
                }
                if ($objShippingMethod->getName()) {
                    $objOrder->setShippingMethodName($objShippingMethod->getName());
                }
            }
        }

        /**
         * Add tracking details
         */
        $arrTrackingValues = array();
        $trackingID = $_GET['comment'];
        if ($objOrder->getOrderId() && $trackingID) {
            foreach ($objOrder->getTrackingNumbers()as $objTrackingNumber) {
                $arrTrackingValues[] = $objTrackingNumber->getValue(); //get the tracking number of order
            }
            if (!in_array($trackingID, $arrTrackingValues))
                \XLite\Core\Database::getRepo('XLite\Model\Order')->addOrderTrackingNumber($objOrder, $trackingID);
        }

        \XLite\Core\Database::getEM()->flush(); //clear the cache

        echo 'Status updated successfully';
    }

    /**
     * Function to generate order export xml
     * 
     */
    protected function actionExport() 
    {
        header('Content-Type: text/xml');
        $dtOrderUpdateStart = strtotime($_GET['start_date']);
        $dtOrderUpdateEnd = strtotime($_GET['end_date']);
        $this->xmlData = "<?xml version=\"1.0\" encoding=\"utf-16\"?>\n";
        $this->xmlData .= "<Orders>\n";
        /**
         * Get orders from start date and end date.
         * Call the getOrdersFromRenewDate model function
         */
        if ($dtOrderUpdateStart && $dtOrderUpdateEnd) {
            $arrOrderData = \XLite\Core\Database::getRepo('XLite\Model\Order')->getOrdersFromRenewDate($dtOrderUpdateStart, $dtOrderUpdateEnd);
            //\Includes\Utils\FileManager::write(LC_DIR_LOG . 'shipstation.log.php', 'Order Result ' . serialize($arrOrderData) . PHP_EOL, FILE_APPEND);
            if (!empty($arrOrderData)) {
                foreach ($arrOrderData as $objOrder) {
                    if ($objOrder) {
                        /**
                         * Get the coupon code used for order
                         */
                        $arrCouponCodes = array();
                        $objCoupons = $objOrder->getUsedCoupons();
                        if (!empty($objCoupons)) {
                            foreach ($objCoupons->toArray() as $objCoupon) {
                                $arrCouponCodes[] = $objCoupon->getCode();
                            }
                        }
                        $this->xmlData .= "\t<Order>\n";
                        /**
                         * Order details
                         */
                        $this->addFieldToXML("OrderNumber", $objOrder->getOrderNumber());
                        $this->addFieldToXML("OrderDate", date('Y-m-d H:i:s', $objOrder->getDate()));
                        $shipping = $objOrder->getShippingMethodName();
                        $shippingCode = $shippingName = '';
                        // get the shipping metjod name and code if available
                        if ($shipping) {
                            $status = $objOrder->getShippingStatus();
                            if(!empty($status)) {
                                $shippingCode = $objOrder->getShippingStatus()->getCode();
                                $shippingName = $objOrder->getShippingStatus()->getName();
                            }   
                        }
                        $this->addFieldToXML("OrderStatusCode", $objOrder->getPaymentStatus()->getCode() . '|' . $shippingCode);
                        $this->addFieldToXML("OrderStatusName", $objOrder->getPaymentStatus()->getName() . '|' . $shippingName);
                        $this->addFieldToXML("LastModified", date('Y-m-d H:i:s', $objOrder->getLastRenewDate()));
                        $this->addFieldToXML("PaymentMethod", $objOrder->getPaymentMethodName());
                        $this->addFieldToXML("ShippingMethod", $objOrder->getShippingMethodName());
                        $this->addFieldToXML("CouponCode", implode(',', $arrCouponCodes));
                        $this->addFieldToXML("Currency", $objOrder->getCurrency()->getCode());
                        $this->addFieldToXML("CurrencyValue", $objOrder->getCurrency()->roundValue(abs($objOrder->getOpenTotal())));
                        $this->addFieldToXML("OrderTotal", $objOrder->getTotal());
                        $this->addFieldToXML("TaxAmount", sprintf('%.02f', round(doubleval(abs($objOrder->getSurchargeSumByType(\XLite\Model\Base\Surcharge::TYPE_TAX))), 2)));
                        $this->addFieldToXML("ShippingAmount", sprintf('%.02f', round(doubleval(abs($objOrder->getSurchargeSumByType(\XLite\Model\Base\Surcharge::TYPE_SHIPPING))), 2)));
                        $this->addFieldToXML("CommentsFromBuyer", '<![CDATA[' . $objOrder->getNotes() . ']]>');

                        /**
                         * Customer details
                         */
                        $this->xmlData .= "\t<Customer>\n";
                        $this->addFieldToXML("CustomerNumber", $objOrder->getProfile()->getProfileId());
                        $this->getBillingInfo($objOrder); //call to the billing info function
                        $this->getShippingInfo($objOrder); //call to the shipping info function
                        $this->xmlData .= "\t</Customer>\n";
                        $this->xmlData .= "\t<Items>\n";
                        $this->getOrderItems($objOrder); //call to the order items function
                        $this->xmlData .= "\t</Items>\n";
                        $this->xmlData .= "\t</Order>\n";
                    }
                }
            }
        } else {
            $this->xmlData .= "<DateRequired>Start Date and End Date required</DateRequired>\n";
        }
        /**
         * finish outputing XML
         */
        $this->xmlData .= "</Orders>";
        echo $this->xmlData;
    }

    /**
     * Get the Billing information of order
     *
     * @param $objOrder Object
     */
    protected function getBillingInfo($objOrder = '') 
    {
        if ($objOrder) {
            $objBillingAddress = $objOrder->getProfile()->getBillingAddress();
            if (!empty($objBillingAddress)) {
                $strStateName = $objBillingAddress->getCustomState(); //Check for the manualy entered state name
                $strStateCode = '';
                if (!$strStateName) {
                    $strStateName = $objBillingAddress->getState()->getState();
                    $strStateCode = $objBillingAddress->getState()->getCode();
                }

                $this->xmlData .= "\t<BillTo>\n";
                $this->addFieldToXML("Name", '<![CDATA[' . $objBillingAddress->getFirstname() . ' ' . $objBillingAddress->getLastname() . ']]>');
                $this->addFieldToXML("Address1", '<![CDATA[' . $objBillingAddress->getStreet() . ']]>');
                $this->addFieldToXML("City", '<![CDATA[' . $objBillingAddress->getCity() . ']]>');
                $this->addFieldToXML("State", '<![CDATA[' . $strStateName . ']]>');
                $this->addFieldToXML("StateCode", $strStateCode);
                $this->addFieldToXML("PostalCode", $objBillingAddress->getZipcode());
                $country = $objBillingAddress->getCountry();
                if(!empty($country)) {
                    $this->addFieldToXML("Country", $objBillingAddress->getCountry()->getCountry());
                    $this->addFieldToXML("CountryCode", $objBillingAddress->getCountry()->getCode());
                }
                $this->addFieldToXML("Company", $objBillingAddress->getCompanyName());
                $this->addFieldToXML("Phone", $objBillingAddress->getPhone());
                $this->addFieldToXML("Email", $objOrder->getProfile()->getLogin());
                $this->xmlData .= "\t</BillTo>\n";
            }
        }
    }

    /**
     * Get the Shipping information of order
     *
     * @param $objOrder Object
     */
    protected function getShippingInfo($objOrder = '') 
    {
        if ($objOrder) {
            $objShippingAddress = $objOrder->getProfile()->getShippingAddress();
            if (!empty($objShippingAddress)) {
                $strShippingStateName = $objShippingAddress->getCustomState(); //Check for the manualy entered state name
                $strShippingStateCode = '';
                if (!$strShippingStateName) {
                    $strShippingStateName = $objShippingAddress->getState()->getState();
                    $strShippingStateCode = $objShippingAddress->getState()->getCode();
                }
                $this->xmlData .= "\t<ShipTo>\n";
                $this->addFieldToXML("Name", '<![CDATA[' . $objShippingAddress->getFirstname() . ' ' . $objShippingAddress->getLastname() . ']]>');
                $this->addFieldToXML("Address1", '<![CDATA[' . $objShippingAddress->getStreet() . ']]>');
                $this->addFieldToXML("City", '<![CDATA[' . $objShippingAddress->getCity() . ']]>');
                $this->addFieldToXML("State", '<![CDATA[' . $strShippingStateName . ']]>');
                $this->addFieldToXML("StateCode", $strShippingStateCode);
                $this->addFieldToXML("PostalCode", $objShippingAddress->getZipcode());
                $country = $objShippingAddress->getCountry();
                if (!empty($country)) {
                    $this->addFieldToXML("Country", $objShippingAddress->getCountry()->getCountry());
                    $this->addFieldToXML("CountryCode", $objShippingAddress->getCountry()->getCode());
                }
                $this->addFieldToXML("Company", $objShippingAddress->getCompanyName());
                $this->addFieldToXML("Phone", $objShippingAddress->getPhone());
                $this->addFieldToXML("Email", $objOrder->getProfile()->getLogin());
                $this->xmlData .= "\t</ShipTo>\n";
            }
        }
    }

    /**
     * Get the Order Items
     *
     * @param $objOrder Object
     */
    protected function getOrderItems($objOrder = '') 
    {
        if ($objOrder) {
            $objorderItems = $objOrder->getItems();
            foreach ($objorderItems as $objSingleOrderItem) {
                /*get weight of all line item and divide them in quantity */
                $totweight = $objSingleOrderItem->getWeight()/$objSingleOrderItem->getAmount();
                if (!empty($objSingleOrderItem)) {
                    $this->xmlData .= "\t<Item>\n";
                    $this->addFieldToXML("ProductID", $objSingleOrderItem->getProduct()->getProductId());
                    $this->addFieldToXML("SKU", $objSingleOrderItem->getSku());
                    $this->addFieldToXML("Name", $objSingleOrderItem->getName());
                    $this->addFieldToXML("ImageUrl", $objSingleOrderItem->getProduct()->getImageUrl());
                    $this->addFieldToXML("Weight", $totweight);
                    $this->addFieldToXML("UnitPrice", $objSingleOrderItem->getItemPrice());
                    $this->addFieldToXML("TaxAmount", sprintf('%.02f', round(doubleval(abs($objSingleOrderItem->getSurchargeSumByType(\XLite\Model\Base\Surcharge::TYPE_TAX))), 2)));
                    $this->addFieldToXML("Quantity", $objSingleOrderItem->getAmount());
                    /*
                     * Check for the order attribute
                     */
                    if ($objSingleOrderItem->hasAttributeValues()) {
                        /*
                         * Get all attributes from the order item
                         */
                        $this->xmlData .="\t<Attributes>\n";
                        foreach ($objSingleOrderItem->getAttributeValues() as $objAttrbuteValues) {
                            $this->xmlData .="\t<Attribute Name=\"" . htmlentities($objAttrbuteValues->getName(), ENT_QUOTES, "UTF-8") . "\" Value=\"" . htmlentities($objAttrbuteValues->getValue(), ENT_QUOTES, "UTF-8") . "\" />\n";
                        }
                        $this->xmlData .= "\t</Attributes>\n";
                    }
                    $this->xmlData .= "\t</Item>\n";
                }
            }
        }
    }

    /**
     * Function to add field to xml
     *
     * @param $strFieldName String
     * @param $strValue String
     */
    protected function addFieldToXML($strFieldName, $strValue) 
    {
        $strResult = mb_convert_encoding(str_replace('&', '&amp;', $strValue), 'UTF-8');
        $this->xmlData .= "\t\t<$strFieldName>$strResult</$strFieldName>\n";
    }
}
