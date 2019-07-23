define([
        'jquery',
        'Magento_Payment/js/view/payment/cc-form'
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