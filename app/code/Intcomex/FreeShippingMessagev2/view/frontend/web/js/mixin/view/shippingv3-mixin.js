define([
    'uiComponent',
    'Intcomex_FreeShippingMessagev2/js/model/free-shipping-message'
], function (Component, freeShippingMessage) {
    'use strict';

    return function (Component) {
        return Component.extend({
            isFreeShippingMsg: function (){
                if(window.checkout.modulestatus){
                    return true;
                }else{
                    return false;
                }
            }
        }); 
    }
});
