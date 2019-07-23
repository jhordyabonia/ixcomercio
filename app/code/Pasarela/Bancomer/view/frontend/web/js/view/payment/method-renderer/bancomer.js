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