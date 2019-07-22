/**
 * Pasarela_Bancomer Magento JS component
 *
 * @category    Bancomer
 * @package     Pasarela_Bancomer
 * @author      Valentina Aguirre
 * @license     http://www.apache.org/licenses/LICENSE-2.0  Apache License Version 2.0
 */
/*browser:true*/
/*global define*/
define(
    [
        'Magento_Checkout/js/view/payment/default'
    ],
    function (Component, $, quote, customer) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Pasarela_Bancomer/payment/bancomer-form'
            },

            context: function() {
                return this;
            },

            getCode: function() {
                return 'pasarela_bancomer';
            },

            isActive: function() {
                return true;
            }
        });
    }
);
