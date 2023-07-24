<?php
/*
 * Copyright 2015 ShipStation. All rights reserved.
 * This file and its content is copyright of ShipStation for use with the ShipStation software solution.
 * Any redistribution or reproduction of part or all of the contents in any form is strictly prohibited.
 * You may not, except with our express written permission, distribute or commercially exploit the content.
 * Nor may you transmit it or store it in any other website or other form of electronic retrieval system.
 */

namespace ShipStation\Api\Model\Repo;

use XCart\Extender\Mapping\Extender;
/**
 * The Order model repository
 */

/**
 * @Extender\Mixin
 */
abstract class Order extends \XLite\Model\Repo\Order
{
    /**
     * Get the order number from last updated dates
     *
     * @param $dtOrderUpdateStart integer
     * @param $dtOrderUpdateEnd integer
     * @return  array
     */
    public function getOrdersFromRenewDate($dtOrderUpdateStart = '', $dtOrderUpdateEnd = '') 
    {
        $objQueryBuilder = $this->createQueryBuilder('o')
                ->andWhere('o.lastRenewDate >= :lastRenewDateStart')
                ->andWhere('o.lastRenewDate <= :lastRenewDateEnd')
                ->setParameter('lastRenewDateStart', $dtOrderUpdateStart)
                ->setParameter('lastRenewDateEnd', $dtOrderUpdateEnd);
        return $objQueryBuilder->getResult();
    }

    /**
     * Add Tracking number to the order
     *
     * @param $order \XLite\Model\Order
     * @param $intTrackingNumber integer
     */
    public function addOrderTrackingNumber($order = null, $intTrackingNumber = 0)
    {
        $orderTrackingNumber = new \XLite\Model\OrderTrackingNumber();
        $orderTrackingNumber->setValue($intTrackingNumber);
        $orderTrackingNumber->setOrder($order);

        $em = $this->getEntityManager();
        $em->persist($orderTrackingNumber);

        $em->flush();
    }

    /**
     * Set the Shipping Service Mappings
     *
     * @param $intShippingId integer
     * @return  array
     */
    public function getShippingServices($intShippingId = 0)
    {
        if (!$intShippingId)
            return 0;
        //Set the shipstation mapping with the X-Cart.
        $arrShippingServices = array(
            '5' => 'USPS_USPPM',//USPS Priority Mail
            '6' => 'USPS_USPEXP',//USPS Priority Mail Express
            '7' => 'USPS_USPEMI',//USPS Priority Mail Express Intl
            '8' => 'USPS_USPPMI',//USPS Priority Mail Intl
            '10' => 'USPS_USPFC',//USPS First Class Mail
            '11' => 'USPS_USPMM',//USPS Media Mail
            '13' => 'USPS_USPPM',//USPS Priority Mail
            '14' => 'USPS_USPEXP',//USPS Priority Mail Express
            '15' => 'USPS_USPEMI',//USPS Priority Mail Express Intl
            '16' => 'USPS_USPPMI',//USPS Priority Mail Intl
            '18' => 'USPS_USPFC',//USPS First Class Mail
            '19' => 'USPS_USPMM',//USPS Media Mail
            '21' => 'USPS_USPPM',//USPS Priority Mail
            '22' => 'USPS_USPEXP',//USPS Priority Mail Express
            '23' => 'USPS_USPEMI',//USPS Priority Mail Express Intl
            '24' => 'USPS_USPPMI',//USPS Priority Mail Intl
            '26' => 'UPS_UPSGND',//UPS® Ground
            '27' => 'UPS_UPS3DS',//UPS 3 Day Select®
            '28' => 'UPS_UPS2ND',//UPS 2nd Day Air®
            '29' => 'WEXPP',//UPS Worldwide Express Plus®
            '30' => 'UPS_UPSWEX',//UPS Worldwide Express®
            '31' => 'UPS_UPSNDS',//UPS Next Day Air Saver®
            '32' => 'UPS_UPSNDA',//UPS Next Day Air®
            '33' => 'UPS_UPSWEP',//UPS Worldwide Expedited®
            '34' => 'UPS_UPSWSV',//UPS Worldwide Saver®
            '35' => 'UPS_UPSCAN',//UPS Standard®
            '36' => 'UPS_UPS2DE',//UPS 2nd Day Air AM®
            '37' => 'UPS_UPSNDE',//UPS Next Day Air® Early
            '50' => 'FEDEX_GROUND',//FedEx Ground®
            '51' => 'GROUND_HOME_DELIVERY',//FedEx Home Delivery®
            '52' => 'FEDEX_2_DAY',//FedEx 2Day®
            '53' => 'FEDEX_2_DAY_AM',//FedEx 2Day® A.M.
            '54' => 'FEDEX_EXPRESS_SAVER',//FedEx Express Saver®
            '55' => 'STANDARD_OVERNIGHT',//FedEx Standard Overnight®
            '56' => 'PRIORITY_OVERNIGHT',//FedEx Priority Overnight®
            '57' => 'FIRST_OVERNIGHT',//FedEx First Overnight®
            '59' => 'INTERNATIONAL_ECONOMY',//FedEx International Economy®
            '60' => 'INTERNATIONAL_PRIORITY',//FedEx International Priority®
            '61' => 'INTERNATIONAL_FIRST',//FedEx International First®
            '67' => 'FEDEX_1_DAY_FREIGHT',//FedEx 1Day® Freight
            '68' => 'FEDEX_2_DAY_FREIGHT',//FedEx 2Day® Freight
            '69' => 'FEDEX_3_DAY_FREIGHT',//FedEx 3Day® Freight
            '70' => 'INTERNATIONAL_ECONOMY_FREIGHT',//FedEx International Economy® Freight
            '71' => 'INTERNATIONAL_PRIORITY_FREIGHT',//FedEx International Priority® Freight
            '73' => 'FEDEX_FDXIGND',//FedEx International Ground®
            '98' => 'DOM.RP',//Regular Parcel
            '99' => 'DOM.EP',//Expedited Parcel
            '100' => 'DOM.XP',//Xpresspost
            '101' => 'DOM.XP.CERT',//Xpresspost Certified
            '102' => 'DOM.PC',//Priority
            '103' => 'DOM.LIB',//Library Books
            '104' => 'USA.EP',//Expedited Parcel USA
            '105' => 'USA.PW.ENV',//Priority Worldwide Envelope USA
            '106' => 'USA.PW.PAK',//Priority Worldwide pak USA
            '107' => 'USA.PW.PARCEL',//Priority Worldwide Parcel USA
            '115' => 'INT.PW.ENV',//Priority Worldwide Envelope Intl
            '116' => 'INT.PW.PAK',//Priority Worldwide pak Intl
            '117' => 'INT.PW.PARCEL',//Priority Worldwide parcel Intl
            '118' => 'INT.SP.AIR',//Small Packet International Air
            '119' => 'INT.SP.SURF',//Small Packet International Surface
            '120' => 'INT.TP',//Tracked Packet - International
            '2700' => 'USPS_USPFC',//USPS First Class Mail
            '2701' => 'USPS_USPMM',//USPS Media Mail
            '2709' => 'USPS_USPPM',//USPS Priority Mail
            '2732' => 'USPS_USPPMI',//USPS Priority Mail International
            '2735' => 'USPS_USPEMI',//USPS Priority Mail Express International
            '2760' => 'UPS_UPSGND',//UPS Ground
            '2762' => 'UPS_UPS3DS',//UPS 3 Day Select
            '2763' => 'UPS_UPS2DE',//UPS 2nd Day Air
            '2765' => 'UPS_UPSNDAS',//UPS Next Day Air Saver
            '2766' => 'UPS_UPSNDA',//UPS Next Day Air
        );

        if (isset($arrShippingServices[$intShippingId])) {
          return $arrShippingServices[$intShippingId];  
        } else {
            return 0;
        }
    } 
}
