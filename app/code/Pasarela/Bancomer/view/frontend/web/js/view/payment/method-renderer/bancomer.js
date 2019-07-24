/**
 * Pasarela_Bancomer Magento JS component
 *
 * @category    Bancomer
 * @package     Pasarela_Bancomer
 * @author      Valentina Aguirre
 * @license     http://www.apache.org/licenses/LICENSE-2.0  Apache License Version 2.0
 */
define([
        'jquery',
        'Magento_Checkout/js/view/payment/default'
    ],
    function ($, Component) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Pasarela_Bancomer/payment/bancomer'
            },
        });
    }
);