/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/shipping-rate-processor/new-address',
    'Magento_Checkout/js/model/shipping-rate-processor/customer-address'
], function (quote, defaultProcessor, customerAddressProcessor) {
    'use strict';

    var processors = [];

    processors.default =  defaultProcessor;
    processors['customer-address'] = customerAddressProcessor;

    quote.shippingAddress.subscribe(function () {
        var type = quote.shippingAddress().getType();

        if (processors[type]) {
            processors[type].getRates(quote.shippingAddress());
        } else {
            processors.default.getRates(quote.shippingAddress());
        }
        setTimeout(function(){ 
            
            var serviceUrl = url.build('intcomex/custom/tradein'); 
            jQuery.post(serviceUrl)
            .done(function(msg){
                if(msg.status=='success'){
                    if(jQuery(".alert_shipping")==undefined){
                        jQuery("#checkout-shipping-method-load").after('<div class="row tradein_alert alert_shipping" style="color:red"><div class="col-sm-1" ><img class="icon" src="'+window.mediaUrl+'/iconos_alerta/icono_'+window.currentWebsiteCode+'.png"></div><div class="col-sm-11" ><p>'+window.alertaTradein2+'</p></div></div>');
                    }
                    if(jQuery(".alert_payment")==undefined){
                        jQuery("#checkout-payment-method-load").after('<div class="row tradein_alert alert_payment" style="color:red"><div class="col-sm-1" ><img class="icon" src="'+window.mediaUrl+'/iconos_alerta/icono_'+window.currentWebsiteCode+'.png"></div><div class="col-sm-11" ><p>'+window.alertaTradein1+'</p></div></div>');
                    }
                  }
              })
              .fail(function(msg){
                  
              })
        
     }, 3000);
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
