define([
    'jquery'
], function ($) {
    'use strict';

    return {
        getFreeShippingMessage: function() {
            if (window.checkout.freeShippingMessage) {
                return window.checkout.freeShippingMessage;
            } else {
                let response = '';
                $.ajax({
                    url: '/checkout/freeshippingmessage/getmessage',
                    async: false
                }).done(function(data) {
                    response = data;
                    window.checkout.freeShippingMessage = data;
                });
                return response;
            }
        }
    };
});
