require([
    'jquery',
    'Intcomex_FreeShippingMessagev2/js/model/free-shipping-message'
], function ($, freeShippingMessage) {
    'use strict';

        var jquery = $;
        
        if(typeof window.checkout.lesspercent === 'undefined'){
            let response = freeShippingMessage.getFreeShippingMessage();
            window.checkout.lesspercent = response.bar_percent;
            console.log("percent: " + response.bar_percent);
        }

        let inputObserverCheckout = new MutationObserver(function (mutations) {
            let varDivClassBody = jquery('#free-shipping-icon-bar');
            if (varDivClassBody.length > 0) {
                inputObserverCheckout.disconnect();
                if (window.checkout.lesspercent < 100) {
                    varDivClassBody[0].style.cssText = '--width-bar: ' + window.checkout.lesspercent + '%';
                } else {
                    varDivClassBody[0].style.cssText = '--width-bar: 100%';
                }
            }
        });
        inputObserverCheckout.observe(document.body, { childList: true, subtree: true });

        let inputObserverMiniCart = new MutationObserver(function (mutations) {
            let varDivClassMiniCart = jquery('#free-shipping-icon-bar-mcart');
            if (varDivClassMiniCart.length > 0) {
                inputObserverMiniCart.disconnect();
                if (window.checkout.lesspercent < 100) {
                    varDivClassMiniCart[0].style.cssText = '--width-bar: ' + window.checkout.lesspercent + '%';
                } else {
                    varDivClassMiniCart[0].style.cssText = '--width-bar: 100%';
                }
            }
        });
        inputObserverMiniCart.observe(document.body, { childList: true, subtree: true });

        let inputObserverCart = new MutationObserver(function (mutations) {
            let varDivClassCart = jquery('#free-shipping-icon-bar-cart');
            if (varDivClassCart.length > 0) {
                inputObserverCart.disconnect();
                if (window.checkout.lesspercent < 100) {
                    varDivClassCart[0].style.cssText = '--width-bar: ' + window.checkout.lesspercent + '%';
                } else {
                    varDivClassCart[0].style.cssText = '--width-bar: 100%';
                }
            }
        });
        inputObserverCart.observe(document.body, { childList: true, subtree: true });

        let inputObserverpayment = new MutationObserver(function (mutations) {
            let varDivClasspayment = jquery('#free-shipping-icon-bar-payment');
            if (varDivClasspayment.length > 0) {
                inputObserverpayment.disconnect();
                if (window.checkout.lesspercent < 100) {
                    varDivClasspayment[0].style.cssText = '--width-bar: ' + window.checkout.lesspercent + '%';
                } else {
                    varDivClasspayment[0].style.cssText = '--width-bar: 100%';
                }
            }
        });
        inputObserverpayment.observe(document.body, { childList: true, subtree: true });
    }

);
