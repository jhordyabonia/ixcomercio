define([
    'uiComponent',
    'Intcomex_FreeShippingMessage/js/model/free-shipping-message'
], function (Component, freeShippingMessage) {
    'use strict';

    return function (Component) {
        return Component.extend({
            getFreeShippingBlock: freeShippingMessage.getFreeShippingMessage(),
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
