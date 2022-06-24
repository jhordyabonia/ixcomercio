<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Intcomex\InventorySourceSelectionRewrites\Model;

/**
 * @inheritdoc
 */
class Address extends \Magento\InventorySourceSelection\Model\Address
{

 /**
     * ItemRequestAddress constructor.
     *
     * @param string $country
     * @param $postcode
     * @param string $street
     * @param string $region
     * @param string $city
     */
    public function __construct(
        string $country,
        $postcode,
        string $street,
        string $region,
        string $city
    ) {
        $_postcode = is_string($postcode) ? $postcode : '';
        parent::__construct($country, $_postcode, $street, $region, $city);
    }
}