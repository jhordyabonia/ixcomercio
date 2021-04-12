/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Customer store credit(balance) application
 */
 define([ 
    'ko',
    'jquery',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/resource-url-manager',
    'Magento_Checkout/js/model/error-processor',
    'Magento_SalesRule/js/model/payment/discount-messages',
    'mage/storage',
    'mage/translate',
    'Magento_Checkout/js/action/get-payment-information',
    'Magento_Checkout/js/model/totals',
    'Magento_Checkout/js/model/full-screen-loader',
    'Magento_Ui/js/modal/modal',
], function (ko, $, quote, urlManager, errorProcessor, messageContainer, storage, $t, getPaymentInformationAction,
    totals, fullScreenLoader, modal
) {
   

    'use strict';

    var dataModifiers = [],
        successCallbacks = [],
        failCallbacks = [],
        action;


    /**
     * Apply provided coupon.
     *
     * @param {String} couponCode
     * @param {Boolean}isApplied
     * @returns {Deferred}
     */
    action = function (couponCode, isApplied) {
        var newcouponCode = couponCode;
        var newisApplied = isApplied;

        var customModal = jQuery("#modalTradeIn");
        var tradeinCancel = jQuery(".tradeinCancel");
        var tradeinCotinue = jQuery(".tradeinCotinue");

        tradeinCotinue.click(function(){
            setCupon();
            var cityCheckout = jQuery("#fieldCityCheckout option:selected").val();
            var zoneCheckout = jQuery("#fieldZoneCheckout option:selected").val();
            console.log('zoneCheckout');
            console.log(zoneCheckout);
            if(zoneCheckout==''){
                zoneCheckout = localStorage.getItem('fieldZoneCheckout');
            }
            if(cityCheckout==''){
                cityCheckout = localStorage.getItem('fieldCityCheckout');
            }
            console.log('New zoneCheckout');
            console.log(zoneCheckout);
            jQuery("ul.opc-progress-bar li:first-child span").trigger('click');
            jQuery("#fieldZoneCheckout").val('');
            jQuery("#fieldCityCheckout").val(cityCheckout);
            jQuery("#fieldZoneCheckout").trigger('change');
            setTimeout(function(){ 
                jQuery("#fieldZoneCheckout").val(zoneCheckout);
                jQuery("#fieldZoneCheckout").trigger('change');
            }, 2000);
            customModal.hide();
        });
        tradeinCancel.click(function(){
            customModal.hide();
        });

        var nTradeIn = couponCode.search("TRADE");
        console.log(nTradeIn);
        if(nTradeIn>=0){
            customModal.show();
        }else{
            setCupon(); 
        }  
        function setCupon(couponCode=newcouponCode,isApplied=newisApplied){
            var quoteId = quote.getQuoteId(),
            url = urlManager.getApplyCouponUrl(couponCode, quoteId),
            message = $t('Your coupon was successfully applied.'),
            data = {},
            headers = {};

            //Allowing to modify coupon-apply request
            dataModifiers.forEach(function (modifier) {
                modifier(headers, data);
            });
            fullScreenLoader.startLoader();
    
            return storage.put(
                url,
                data,
                false,
                null,
                headers
            ).done(function (response) {
                var deferred;
    
                if (response) {
                    deferred = $.Deferred();
    
                    isApplied(true);
                    totals.isLoading(true);
                    getPaymentInformationAction(deferred);
                    $.when(deferred).done(function () {
                        fullScreenLoader.stopLoader();
                        totals.isLoading(false);
                    });
                    messageContainer.addSuccessMessage({
                        'message': message
                    });
                    //Allowing to tap into apply-coupon process.
                    successCallbacks.forEach(function (callback) {
                        callback(response);
                    });
                }
            }).fail(function (response) {
                fullScreenLoader.stopLoader();
                totals.isLoading(false);
                errorProcessor.process(response, messageContainer);
                //Allowing to tap into apply-coupon process.
                failCallbacks.forEach(function (callback) {
                    callback(response);
                });
            });
        }
           
        
       
    };

    /**
     * Modifying data to be sent.
     *
     * @param {Function} modifier
     */
    action.registerDataModifier = function (modifier) {
        dataModifiers.push(modifier);
    };

    /**
     * When successfully added a coupon.
     *
     * @param {Function} callback
     */
    action.registerSuccessCallback = function (callback) {
        successCallbacks.push(callback);
    };

    /**
     * When failed to add a coupon.
     *
     * @param {Function} callback
     */
    action.registerFailCallback = function (callback) {
        failCallbacks.push(callback);
    };

    return action;
});
