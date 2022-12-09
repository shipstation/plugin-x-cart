<?php
/*
 * Copyright 2015 ShipStation. All rights reserved.
 * This file and its content is copyright of ShipStation for use with the ShipStation software solution.
 * Any redistribution or reproduction of part or all of the contents in any form is strictly prohibited.
 * You may not, except with our express written permission, distribute or commercially exploit the content.
 * Nor may you transmit it or store it in any other website or other form of electronic retrieval system.
 */

declare(strict_types=1);

namespace ShipStation\ShipStation;

use Symfony\Component\HttpKernel\Bundle\Bundle;
/**
 * Module description
 *
 * @package XLite
 */

final class ShipStationBundle extends Bundle
{

    /**
     * Author name
     *
     * @return string
     */
    public static function getAuthorName()
    {
        return 'ShipStation';
    }

    /**
     * Module name
     *
     * @return string
     */
    public static function getModuleName() 
    {
        return 'ShipStation';
    }

    /**
     * Get module major version
     *
     * @return string
     */
    public static function getMajorVersion() 
    {
        return '5.4';
    }

    /**
     * Module version
     *
     * @return string
     */
    public static function getMinorVersion() 
    {
        return '2';
    }

    /**
     * Module description
     *
     * @return string
     */
    public static function getDescription() 
    {
        return 'Wherever you sell, however you ship, ShipStation helps you create shipping labels easily and efficiently.';
    }
}
