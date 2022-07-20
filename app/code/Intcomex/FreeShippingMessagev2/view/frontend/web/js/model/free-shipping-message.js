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
                    let _data = JSON.parse(data);
                    response = _data;
                    window.checkout.freeShippingMessage = _data.msg;
                    window.checkout.lesspercent= _data.bar_percent;
                    window.checkout.modulestatus= _data.module_status;
                });
                return response;
            }
        }
    };
});
