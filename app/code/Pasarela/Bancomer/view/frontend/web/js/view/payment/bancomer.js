define([
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (Component, rendererList) {
        'use strict';

        rendererList.push(
            {
                type: 'pasarela_bancomer',
                component: 'Pasarela_Bancomer/js/view/payment/method-renderer/bancomer'
            }
        );

        /** Add view logic here if needed */
        return Component.extend({});
    });
