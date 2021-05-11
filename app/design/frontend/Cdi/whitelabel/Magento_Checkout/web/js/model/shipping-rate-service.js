/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/shipping-rate-processor/new-address',
    'Magento_Checkout/js/model/shipping-rate-processor/customer-address',
    'jquery',
    'mage/url'
], function (quote, defaultProcessor, customerAddressProcessor, $, url) {
    'use strict';

    var processors = [];

    processors.default = defaultProcessor;
    processors['customer-address'] = customerAddressProcessor;

    quote.shippingAddress.subscribe(function () {
        var type = quote.shippingAddress().getType();

        if (processors[type]) {
            processors[type].getRates(quote.shippingAddress());
        } else {
            processors.default.getRates(quote.shippingAddress());
        }

        var serviceUrl = url.build('intcomex/custom/tradein');
        jQuery.post(serviceUrl)
        .done(function (msg) {
            if (msg.status == 'success') {
                setTimeout(function () {
                    if (jQuery(".alert_payment").length == 0) {
                        jQuery("#checkout-shipping-method-load").after('<div class="row tradein_alert alert_payment" style="color:red"><div class="col-sm-1" ><img class="icon" src="' + window.mediaUrl + '/iconos_alerta/icono_' + window.currentWebsiteCode + '.png"></div><div class="col-sm-11" ><p>' + msg.alerta2 + '</p></div></div>');
                    }
                    if (jQuery(".alert_shipping").length == 0) {
                        jQuery("#checkout-payment-method-load").after('<div class="row tradein_alert alert_shipping" style="color:red"><div class="col-sm-1" ><img class="icon" src="' + window.mediaUrl + '/iconos_alerta/icono_' + window.currentWebsiteCode + '.png"></div><div class="col-sm-11" ><p>' + msg.alerta1 + '</p></div></div>');
                    }
                    if (msg.terms) {
                        console.log('print ckeckbox');
                        jQuery(".terms-tradein").remove();
                        if (jQuery(".terms-tradein").length == 0) {
                            jQuery(".checkout-agreements-block:second").after(msg.check);
                            jQuery(".checkout-agreements-block:first").after(msg.check);
                        }
                    }
                }, 2000);
            }
        })
        .fail(function (msg) {

        })

    });

    return {
        /**
         * @param {String} type
         * @param {*} processor
         */
        registerProcessor: function (type, processor) {
            processors[type] = processor;
        }
    };
});
