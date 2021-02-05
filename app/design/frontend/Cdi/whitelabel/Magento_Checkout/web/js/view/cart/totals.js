/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'uiComponent',
    'Magento_Checkout/js/model/totals',
    'Magento_Checkout/js/model/shipping-service',
    'mage/url'
], function ($, Component, totalsService, shippingService,url) {
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
                if(msg.status=='success'){
                    var alertaDiv = '<div class="row custom_alert" style="color:red"><div class="col-sm-2" ><img class="icon" src="'+msg.img+'"></div><div class="col-sm-10" >'+msg.alerta+'</div></div>';
                    jQuery("#cart-totals").after(alertaDiv);
                }
            })
            .fail(function(msg){

            })
        }
    });
});
