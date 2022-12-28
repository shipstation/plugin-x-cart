<?php
/*
 * Copyright 2015 ShipStation. All rights reserved.
 * This file and its content is copyright of ShipStation for use with the ShipStation software solution.
 * Any redistribution or reproduction of part or all of the contents in any form is strictly prohibited.
 * You may not, except with our express written permission, distribute or commercially exploit the content.
 * Nor may you transmit it or store it in any other website or other form of electronic retrieval system.
 */

declare(strict_types=1);

namespace ShipStation\Api;

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
     * Module description
     *
     * @return string
     */
    public static function getDescription() 
    {
        return 'Wherever you sell, however you ship, ShipStation helps you create shipping labels easily and efficiently.';
    }
}
