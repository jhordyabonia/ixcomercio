<?php

namespace Cdi\Custom\Model;

/**
 * Status
 * @category Magestore
 * @package  Magestore_Bannerslider
 * @module   Bannerslider
 * @author   Magestore Developer
 */
class Images
{
    
    public static function getAvailableImages()
    {
        return [
            'water.png' => __('Water'),
            'time.png' => __('Time'),
            'location.png' => __('Location'),
            'cable.png' => __('Cable'),
        ];
    }
}
