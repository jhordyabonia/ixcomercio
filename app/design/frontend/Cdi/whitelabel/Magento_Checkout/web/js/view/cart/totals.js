/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'uiComponent',
    'Magento_Checkout/js/model/totals',
    'Magento_Checkout/js/model/shipping-service'
], function ($, Component, totalsService, shippingService) {
    'use strict';

    return Component.extend({
        isLoading: totalsService.isLoading,

        /**
         * @override
         */
        initialize: function () {
            this._super();
            this._verifyTradeIn();
            totalsService.totals.subscribe(function () {
                $(window).trigger('resize');
            });
            shippingService.getShippingRates().subscribe(function () {
                $(window).trigger('resize');
            });
        },
        _verifyTradeIn: function(){
            var serviceUrl = url.build('intcomex/custom/tradein');  
            jQuery.post(serviceUrl,{alerta:'1'})
            .done(function(msg){
                //data = JSON.parse(msg);
                //console.log('data');
                var alertaDiv = '<div class="custom_alert" style="color:red"><img class="icon" src="'+msg.img+'">'+msg.alerta+'</div>';
                console.log(alertaDiv);
                jQuery("#cart-totals").after(alertaDiv);
            })
            .fail(function(msg){

            })
        }
    });
});
