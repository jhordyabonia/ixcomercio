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
            'location.png' => __('Range'),
            'cable.png' => __('Cable'),
            'handsfree.png' => __('Hands Free'),
            'pairing.png' => __('Pairing'),
            'speakerphone.png' => __('Speaker Phone'),
            'anc.png' => __('ANC'),
            'convenientControls.png' => __('Convenient controls'),
            'CordManagement.png' => __('Cord Management'),
            'OnEarDesign.png' => __('On Ear Design'),
            'StickyPad.png' => __('Sticky Pad'),
            'TrulyWireless.png' => __('Truly Wireless'),
        ];
    }
}
